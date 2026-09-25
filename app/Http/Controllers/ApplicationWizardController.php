<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ApplicationSupportingDocument;
use App\Rules\SupportingMediaFile;
use App\Services\ActivityLogger;
use App\Services\ApplicationOptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\File;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicationWizardController extends Controller
{
    public function show(Request $request, int $step): View|RedirectResponse
    {
        $application = $this->applicationFor($request->user());
        $type = $application->applicant_type;
        $stepKey = ApplicationOptions::stepKeyForNumber($type, $step);

        if (! $stepKey) {
            abort(404);
        }

        // Don't let applicants skip ahead of where they've actually progressed.
        if ($step > $application->current_step && $stepKey !== 'review') {
            return redirect()->route('application.step', $application->current_step);
        }

        return view("application.{$type}.{$stepKey}", [
            'application' => $application,
            'step' => $step,
            'stepKey' => $stepKey,
            'totalSteps' => $application->totalSteps(),
            'locked' => $application->isLocked(),
        ]);
    }

    public function store(Request $request, int $step): RedirectResponse
    {
        $application = $this->applicationFor($request->user());

        if ($application->isLocked()) {
            return redirect()->route('dashboard')->with('status', 'This application is locked and can no longer be edited.');
        }

        $type = $application->applicant_type;
        $stepKey = ApplicationOptions::stepKeyForNumber($type, $step);

        if (! $stepKey || $stepKey === 'review') {
            abort(404);
        }

        $rules = ApplicationOptions::rulesForStep($type, $stepKey);

        if ($type === 'corporate' && $stepKey === 'assessment') {
            $remainingDocumentSlots = max(0, 10 - $application->supportingDocuments()->count());
            $rules['supporting_documents'] = ['nullable', 'array', 'max:'.$remainingDocumentSlots];
            $rules['supporting_documents.*'] = [
                'bail',
                File::types([])
                    ->extensions(['jpg', 'jpeg', 'png', 'webp', 'mp4', 'mov', 'webm'])
                    ->max('5mb'),
                new SupportingMediaFile,
            ];
        }

        $validated = $request->validate($rules);
        $supportingDocuments = $validated['supporting_documents'] ?? [];
        unset($validated['supporting_documents']);

        if (($validated['project_completion_status'] ?? null) === 'continuing') {
            $validated['project_completion_date'] = null;
        }

        if ($stepKey === 'projects') {
            $this->saveProjects($application, $validated);
        } else {
            $application->fill($validated);
        }

        $application->current_step = max($application->current_step, $step + 1);
        $application->save();

        foreach ($supportingDocuments as $supportingDocument) {
            $path = $supportingDocument->store("application-supporting-documents/{$application->id}", 'local');

            if ($path === false) {
                throw new \RuntimeException('Unable to store the supporting document.');
            }

            $mimeType = $supportingDocument->getMimeType() ?: 'application/octet-stream';

            $application->supportingDocuments()->create([
                'path' => $path,
                'original_name' => $supportingDocument->getClientOriginalName(),
                'mime_type' => $mimeType,
                'size' => $supportingDocument->getSize(),
                'media_type' => str_starts_with($mimeType, 'image/') ? 'image' : 'video',
            ]);
        }

        ActivityLogger::log(
            'application.step_completed',
            "Completed step \"{$stepKey}\" of the {$type} application.",
            $application,
            ['step' => $step, 'step_key' => $stepKey],
        );

        $nextStep = min($step + 1, $application->totalSteps());

        return redirect()->route('application.step', $nextStep);
    }

    public function autosave(Request $request): JsonResponse
    {
        $application = $this->applicationFor($request->user());

        if ($application->isLocked()) {
            return response()->json(['saved' => false, 'reason' => 'locked'], 423);
        }

        $stepKey = $request->string('step_key')->toString();
        $allowedFields = ApplicationOptions::fieldsForStep($application->applicant_type, $stepKey);

        if (empty($allowedFields)) {
            return response()->json(['saved' => false], 422);
        }

        $data = $request->input('data', []);
        $data = array_intersect_key($data, array_flip($allowedFields));

        if ($stepKey === 'projects') {
            $this->saveProjects($application, $data);
        } else {
            $application->fill($data);
        }

        $application->save();

        return response()->json(['saved' => true, 'at' => now()->toIso8601String()]);
    }

    public function review(Request $request): View
    {
        $application = $this->applicationFor($request->user());

        return view("application.{$application->applicant_type}.review", [
            'application' => $application,
            'step' => $application->totalSteps(),
            'totalSteps' => $application->totalSteps(),
            'locked' => $application->isLocked(),
        ]);
    }

    public function submit(Request $request): RedirectResponse
    {
        $application = $this->applicationFor($request->user());

        if ($application->isLocked()) {
            return redirect()->route('dashboard')->with('status', 'This application is already locked.');
        }

        $type = $application->applicant_type;

        foreach (ApplicationOptions::steps($type) as $stepKey) {
            if ($stepKey === 'review') {
                continue;
            }

            $rules = ApplicationOptions::rulesForStep($type, $stepKey);

            $values = $stepKey === 'projects'
                ? $this->projectsAsFlatFields($application)
                : $application->only(array_keys($rules));

            Validator::make($values, $rules)->validate();
        }

        $didSubmit = false;

        DB::transaction(function () use ($application, &$didSubmit): void {
            $lockedApplication = Application::query()->lockForUpdate()->findOrFail($application->id);

            if ($lockedApplication->isSubmitted()) {
                return;
            }

            if ($lockedApplication->reference_number === null) {
                $sequence = DB::table('application_number_sequences')
                    ->where('applicant_type', $lockedApplication->applicant_type)
                    ->lockForUpdate()
                    ->first();

                if ($sequence === null) {
                    throw new \RuntimeException('The application number sequence is not initialized.');
                }

                $nextNumber = $sequence->last_number + 1;
                $prefix = $lockedApplication->applicant_type === 'corporate' ? 'CP' : 'IN';

                DB::table('application_number_sequences')
                    ->where('applicant_type', $lockedApplication->applicant_type)
                    ->update(['last_number' => $nextNumber]);

                $lockedApplication->reference_number = sprintf('RICSR/%s/%04d', $prefix, $nextNumber);
            }

            $lockedApplication->status = 'submitted';
            $lockedApplication->submitted_at = now();
            $lockedApplication->save();
            $didSubmit = true;
        });

        $application->refresh();

        if (! $didSubmit) {
            return redirect()->route('dashboard')->with('status', 'This application is already locked.');
        }

        ActivityLogger::log(
            'application.submitted',
            "{$request->user()->email} submitted their {$type} application.",
            $application,
        );

        return redirect()->route('dashboard')->with(
            'status',
            "Your application has been submitted successfully. Application ID: {$application->reference_number}",
        );
    }

    public function downloadSupportingDocument(
        Request $request,
        ApplicationSupportingDocument $document,
    ): StreamedResponse {
        $this->authorizeSupportingDocument($request, $document);

        return Storage::disk('local')->download(
            $document->path,
            $document->original_name,
            ['Content-Type' => $document->mime_type],
        );
    }

    public function previewSupportingDocument(
        Request $request,
        ApplicationSupportingDocument $document,
    ): StreamedResponse {
        $this->authorizeSupportingDocument($request, $document);

        return Storage::disk('local')->response(
            $document->path,
            $document->original_name,
            ['Content-Type' => $document->mime_type],
            'inline',
        );
    }

    /**
     * Take the flat, index-suffixed form fields (problem_1, problem_2, ...) and
     * write them into the application's ind_projects JSON as three slots.
     */
    private function saveProjects(Application $application, array $data): void
    {
        $projects = [];

        foreach ([1, 2, 3] as $slot) {
            $values = [];

            foreach (ApplicationOptions::PROJECT_FIELDS as $field) {
                $values[$field] = $data["{$field}_{$slot}"] ?? null;
            }

            $projects[$slot - 1] = array_filter($values, fn ($v) => $v !== null && $v !== '');
        }

        $application->ind_projects = $projects;
    }

    /** Reverse of saveProjects(): expand ind_projects back into flat field names for validation. */
    private function projectsAsFlatFields(Application $application): array
    {
        $flat = [];
        $projects = $application->ind_projects ?? [];

        foreach ([1, 2, 3] as $slot) {
            $slotData = $projects[$slot - 1] ?? [];

            foreach (ApplicationOptions::PROJECT_FIELDS as $field) {
                $flat["{$field}_{$slot}"] = $slotData[$field] ?? null;
            }
        }

        return $flat;
    }

    private function applicationFor($user): Application
    {
        return Application::firstOrCreate(
            ['user_id' => $user->id],
            ['applicant_type' => $user->applicant_type, 'status' => 'draft', 'current_step' => 1]
        );
    }

    private function authorizeSupportingDocument(
        Request $request,
        ApplicationSupportingDocument $document,
    ): void {
        abort_unless(
            $request->user()->isAdmin() || $document->application()->where('user_id', $request->user()->id)->exists(),
            404,
        );
        abort_unless(Storage::disk('local')->exists($document->path), 404);
    }
}
