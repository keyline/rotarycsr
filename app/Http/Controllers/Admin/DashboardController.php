<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $applicants = User::where('role', 'applicant');

        $stats = [
            'total' => (clone $applicants)->count(),
            'corporate' => (clone $applicants)->where('applicant_type', 'corporate')->count(),
            'individual' => (clone $applicants)->where('applicant_type', 'individual')->count(),
            'verified' => (clone $applicants)->whereNotNull('email_verified_at')->count(),
            'pending' => (clone $applicants)->whereNull('email_verified_at')->count(),
        ];

        return view('admin.dashboard', [
            'stats' => $stats,
        ]);
    }
}
