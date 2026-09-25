<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ActivityLogger;
use App\Services\ApplicationDecisionMailer;
use App\Services\DashboardContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.index', [
            'deadline' => Setting::get('submission_deadline'),
            'howItWorks' => DashboardContent::howItWorks(),
            'decisionMailTemplates' => collect(ApplicationDecisionMailer::DEFAULT_TEMPLATES)
                ->map(fn (array $template, string $decision): array => [
                    'subject' => Setting::get("decision_mail_{$decision}_subject", $template['subject']),
                    'body' => Setting::get("decision_mail_{$decision}_body", $template['body']),
                ]),
        ]);
    }

    public function updateDeadline(Request $request): RedirectResponse
    {
        $request->validate([
            'submission_deadline' => ['required', 'date'],
        ]);

        $previous = Setting::get('submission_deadline');

        Setting::set('submission_deadline', $request->submission_deadline);

        ActivityLogger::log(
            'admin.deadline_updated',
            "Global submission deadline set to {$request->submission_deadline}.",
            properties: ['previous' => $previous, 'new' => $request->submission_deadline],
        );

        return back()->with('status', 'Submission deadline updated.');
    }

    public function updateDecisionEmails(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'templates' => ['required', 'array'],
            'templates.approved.subject' => ['required', 'string', 'max:255'],
            'templates.approved.body' => ['required', 'string', 'max:10000'],
            'templates.rejected.subject' => ['required', 'string', 'max:255'],
            'templates.rejected.body' => ['required', 'string', 'max:10000'],
            'templates.blacklisted.subject' => ['required', 'string', 'max:255'],
            'templates.blacklisted.body' => ['required', 'string', 'max:10000'],
        ]);

        foreach ($validated['templates'] as $decision => $template) {
            Setting::set("decision_mail_{$decision}_subject", $template['subject']);
            Setting::set("decision_mail_{$decision}_body", $template['body']);
        }

        ActivityLogger::log('admin.decision_emails_updated', 'Application decision email templates were updated.');

        return back()->with('status', 'Decision email templates updated.');
    }

    public function updateDashboardContent(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'how_it_works' => ['required', 'string', 'max:10000'],
        ]);

        Setting::set(DashboardContent::SETTING_KEY, trim($validated['how_it_works']));

        ActivityLogger::log(
            'admin.dashboard_content_updated',
            'Applicant dashboard How It Works content was updated.',
        );

        return back()->with('status', 'Dashboard How It Works content updated.');
    }
}
