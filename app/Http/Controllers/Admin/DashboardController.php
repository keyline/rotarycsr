<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $applicants = User::where('role', 'applicant');
        $applications = Application::query();

        $stats = [
            'total' => (clone $applicants)->count(),
            'corporate' => (clone $applicants)->where('applicant_type', 'corporate')->count(),
            'individual' => (clone $applicants)->where('applicant_type', 'individual')->count(),
            'verified' => (clone $applicants)->whereNotNull('email_verified_at')->count(),
            'pending' => (clone $applicants)->whereNull('email_verified_at')->count(),
        ];

        $applicationStats = [
            'total' => (clone $applications)->count(),
            'submitted' => (clone $applications)->where('status', 'submitted')->count(),
            'in_progress' => (clone $applications)->where('status', 'draft')->count(),
            'approved' => (clone $applications)->where('review_status', 'approved')->count(),
            'not_approved' => (clone $applications)->whereIn('review_status', ['rejected', 'blacklisted'])->count(),
            'pending_review' => (clone $applications)
                ->where('status', 'submitted')
                ->where('review_status', 'pending')
                ->count(),
        ];

        $applicationPercentages = [];

        foreach (['submitted', 'in_progress', 'approved', 'not_approved', 'pending_review'] as $status) {
            $applicationPercentages[$status] = $this->percentage(
                $applicationStats[$status],
                $applicationStats['total'],
            );
        }

        return view('admin.dashboard', [
            'stats' => $stats,
            'applicationStats' => $applicationStats,
            'applicationPercentages' => $applicationPercentages,
        ]);
    }

    private function percentage(int $count, int $total): float
    {
        if ($total === 0) {
            return 0.0;
        }

        return round(($count / $total) * 100, 1);
    }
}
