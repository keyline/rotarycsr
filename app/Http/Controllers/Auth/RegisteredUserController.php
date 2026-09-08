<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $applicantType = $request->query('type');

        if (! in_array($applicantType, ['corporate', 'individual'], true)) {
            return redirect()->route('apply');
        }

        return view('auth.register', ['applicantType' => $applicantType]);
    }

    /**
     * Handle an incoming registration request: create (or reuse an abandoned,
     * unverified) account and send an OTP to verify the email address.
     *
     * @throws ValidationException
     */
    public function store(Request $request, OtpService $otpService): RedirectResponse
    {
        $applicantType = $request->input('applicant_type');

        if (! in_array($applicantType, ['corporate', 'individual'], true)) {
            return redirect()->route('apply');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_name' => [Rule::requiredIf($applicantType === 'corporate'), 'nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
        ]);

        $existing = User::where('email', $request->email)->first();

        if ($existing && ($existing->hasVerifiedEmail() || $existing->password)) {
            throw ValidationException::withMessages([
                'email' => 'This email is already registered. Please log in instead.',
            ]);
        }

        $attributes = [
            'name' => $request->name,
            'email' => $request->email,
            'applicant_type' => $applicantType,
            'company_name' => $applicantType === 'corporate' ? $request->company_name : null,
        ];

        if ($existing) {
            $existing->fill($attributes)->save();
            $user = $existing;
        } else {
            $user = User::create($attributes);
        }

        $debugOtp = $otpService->issue($user);

        ActivityLogger::log(
            'auth.register.otp_sent',
            "Registration started for {$user->email} ({$applicantType}).",
            $user,
            ['applicant_type' => $applicantType, 'reused_abandoned_record' => (bool) $existing],
            causer: $user,
        );

        session(['pending_registration_user_id' => $user->id]);

        return redirect()->route('verification.otp')
            ->with('debug_otp', $debugOtp);
    }
}
