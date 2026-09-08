<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegistrationPasswordController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $user = $this->verifiedPendingUser($request);

        if (! $user) {
            return redirect()->route('apply');
        }

        return view('auth.set-password', ['email' => $user->email]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $this->verifiedPendingUser($request);

        if (! $user) {
            return redirect()->route('apply');
        }

        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->forceFill([
            'password' => Hash::make($request->password),
        ])->save();

        $request->session()->forget('pending_registration_user_id');
        $request->session()->forget('debug_otp');

        ActivityLogger::log(
            'auth.register.completed',
            "{$user->email} completed registration ({$user->applicant_type}).",
            $user,
            ['applicant_type' => $user->applicant_type],
            causer: $user,
        );

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    private function verifiedPendingUser(Request $request): ?User
    {
        $userId = $request->session()->get('pending_registration_user_id');

        if (! $userId) {
            return null;
        }

        $user = User::find($userId);

        return $user && $user->hasVerifiedEmail() ? $user : null;
    }
}
