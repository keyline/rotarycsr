<?php

namespace App\Http\Middleware;

use App\Services\ActivityLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Generic safety-net audit log for state-changing requests.
 *
 * Auth/registration flows already log rich, purpose-built entries via
 * ActivityLogger calls in their controllers — those routes are excluded here
 * to avoid duplicate noise. Everything else that mutates state (future admin
 * actions, application form submissions, etc.) is captured automatically.
 */
class LogUserActivity
{
    private const EXCLUDED_PATHS = [
        'login',
        'logout',
        'register',
        'register/*',
        'forgot-password',
        'reset-password*',
        'confirm-password',
        'password',
        'email/verification-notification',
        // Application wizard steps/autosave/submit already log rich, purpose-built
        // entries from ApplicationWizardController — logging them here too would
        // just duplicate that (and autosave fires on every debounced keystroke).
        'application/*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (
            in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)
            && Auth::check()
            && ! $request->is(...self::EXCLUDED_PATHS)
            && $response->getStatusCode() < 400
        ) {
            $routeName = $request->route()?->getName();

            ActivityLogger::log(
                'http.'.strtolower($request->method()).'_request',
                ($routeName ?? $request->path()).' action performed.',
                properties: ['route' => $routeName, 'path' => $request->path()],
            );
        }

        return $response;
    }
}
