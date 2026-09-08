<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\ApplicantController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\ApplicationWizardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/apply', function () {
    return view('apply');
})->name('apply');

Route::get('/dashboard', function (\Illuminate\Http\Request $request) {
    if (Auth::user()->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    return app(DashboardController::class)->index($request);
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->prefix('application')->name('application.')->group(function () {
    Route::get('/step/{step}', [ApplicationWizardController::class, 'show'])->whereNumber('step')->name('step');
    Route::post('/step/{step}', [ApplicationWizardController::class, 'store'])->whereNumber('step');
    Route::post('/autosave', [ApplicationWizardController::class, 'autosave'])->name('autosave');
    Route::get('/review', [ApplicationWizardController::class, 'review'])->name('review');
    Route::post('/submit', [ApplicationWizardController::class, 'submit'])->name('submit');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/applicants', [ApplicantController::class, 'index'])->name('applicants.index');
    Route::get('/applicants/export', [ApplicantController::class, 'export'])->name('applicants.export');
    Route::get('/applicants/{applicant}', [ApplicantController::class, 'show'])->name('applicants.show');
    Route::get('/activity', [ActivityLogController::class, 'index'])->name('activity.index');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/deadline', [SettingsController::class, 'updateDeadline'])->name('settings.deadline');
    Route::post('/settings/logo', [SettingsController::class, 'updateLogo'])->name('settings.logo.update');
    Route::delete('/settings/logo', [SettingsController::class, 'removeLogo'])->name('settings.logo.remove');
});

require __DIR__.'/auth.php';
