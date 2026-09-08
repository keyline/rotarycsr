<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OtpVerificationController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $user = $this->pendingUser($request);

        if (! $user) {
            return redirect()->route('apply');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('register.password');
        }

        return view('auth.verify-otp', [
            'email' => $user->email,
            'debugOtp' => session('debug_otp'),
        ]);
    }

    public function store(Request $request, OtpService $otpService): RedirectResponse
    {
        $user = $this->pendingUser($request);

        if (! $user) {
            return redirect()->route('apply');
        }

        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        if (! $otpService->verify($user, $request->otp)) {
            ActivityLogger::log(
                'auth.register.otp_failed',
                "Incorrect/expired OTP attempt for {$user->email}.",
                $user,
                causer: $user,
            );

            throw \Illuminate\Validation\ValidationException::withMessages([
                'otp' => 'That code is incorrect or has expired. Please try again or request a new one.',
            ]);
        }

        ActivityLogger::log(
            'auth.register.otp_verified',
            "{$user->email} verified their email address.",
            $user,
            causer: $user,
        );

        return redirect()->route('register.password');
    }

    public function resend(Request $request, OtpService $otpService): RedirectResponse
    {
        $user = $this->pendingUser($request);

        if (! $user) {
            return redirect()->route('apply');
        }

        $debugOtp = $otpService->issue($user);

        ActivityLogger::log(
            'auth.register.otp_resent',
            "Verification code resent to {$user->email}.",
            $user,
            causer: $user,
        );

        return redirect()->route('verification.otp')
            ->with('debug_otp', $debugOtp)
            ->with('status', 'A new verification code has been sent to your email.');
    }

    private function pendingUser(Request $request): ?User
    {
        $userId = $request->session()->get('pending_registration_user_id');

        if (! $userId) {
            return null;
        }

        return User::find($userId);
    }
}
