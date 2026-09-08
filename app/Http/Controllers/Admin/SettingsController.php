<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.index', [
            'deadline' => Setting::get('submission_deadline'),
            'logoPath' => Setting::get('site_logo_path'),
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

    public function updateLogo(Request $request): RedirectResponse
    {
        $request->validate([
            'logo' => ['required', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
        ]);

        $previousPath = Setting::get('site_logo_path');

        $path = $request->file('logo')->store('branding', 'public');

        Setting::set('site_logo_path', $path);

        if ($previousPath) {
            Storage::disk('public')->delete($previousPath);
        }

        ActivityLogger::log(
            'admin.logo_updated',
            'Site logo was updated.',
            properties: ['path' => $path],
        );

        return back()->with('status', 'Site logo updated.');
    }

    public function removeLogo(): RedirectResponse
    {
        $path = Setting::get('site_logo_path');

        if ($path) {
            Storage::disk('public')->delete($path);
        }

        Setting::set('site_logo_path', null);

        ActivityLogger::log('admin.logo_removed', 'Site logo was reset to the default.');

        return back()->with('status', 'Site logo reset to the default.');
    }
}
