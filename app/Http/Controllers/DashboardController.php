<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Services\ApplicationOptions;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $application = Application::firstOrCreate(
            ['user_id' => $user->id],
            ['applicant_type' => $user->applicant_type, 'status' => 'draft', 'current_step' => 1]
        );

        return view('dashboard', [
            'application' => $application,
            'deadline' => ApplicationOptions::deadline(),
            'deadlinePassed' => ApplicationOptions::deadlineHasPassed(),
        ]);
    }
}
