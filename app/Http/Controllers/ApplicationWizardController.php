<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Services\ActivityLogger;
use App\Services\ApplicationOptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

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
        $validated = $request->validate($rules);

        if ($stepKey === 'projects') {
            $this->saveProjects($application, $validated);
        } else {
            $application->fill($validated);
        }

        $application->current_step = max($application->current_step, $step + 1);
        $application->save();

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

        $application->status = 'submitted';
        $application->submitted_at = now();
        $application->save();

        ActivityLogger::log(
            'application.submitted',
            "{$request->user()->email} submitted their {$type} application.",
            $application,
        );

        return redirect()->route('dashboard')->with('status', 'Your application has been submitted successfully.');
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
}
