<?php

namespace App\Providers;

use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(function (Login $event) {
            ActivityLogger::log(
                'auth.login',
                "{$event->user->name} logged in.",
                $event->user,
                causer: $event->user,
            );
        });

        Event::listen(function (Logout $event) {
            if ($event->user instanceof User) {
                ActivityLogger::log(
                    'auth.logout',
                    "{$event->user->name} logged out.",
                    $event->user,
                    causer: $event->user,
                );
            }
        });

        Event::listen(function (Failed $event) {
            ActivityLogger::log(
                'auth.login_failed',
                'Failed login attempt for '.($event->credentials['email'] ?? 'unknown email').'.',
                $event->user,
                ['email' => $event->credentials['email'] ?? null],
            );
        });

        Event::listen(function (PasswordReset $event) {
            ActivityLogger::log(
                'auth.password_reset',
                "{$event->user->name} reset their password.",
                $event->user,
                causer: $event->user,
            );
        });
    }
}
