<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AwardWinnerController extends Controller
{
    public function __invoke(Request $request, Application $application): RedirectResponse
    {
        $application->loadMissing('user');

        if (! $application->isSubmitted() || $application->review_status !== 'approved') {
            throw ValidationException::withMessages([
                'award' => 'Only approved, submitted applications can be marked as award winners.',
            ]);
        }

        if ($application->award_winner_at !== null) {
            return back()->with('status', $application->user->name.' is already marked as an award winner.');
        }

        DB::transaction(function () use ($application, $request): void {
            $application->update([
                'award_winner_at' => now(),
                'award_winner_by' => $request->user()->id,
            ]);

            ActivityLogger::log(
                'admin.award_winner_selected',
                $application->user->name.' marked as an award winner.',
                $application,
                ['applicant_id' => $application->user_id],
            );
        });

        return back()->with('status', $application->user->name.' marked as an award winner. You can now send the award email.');
    }
}
