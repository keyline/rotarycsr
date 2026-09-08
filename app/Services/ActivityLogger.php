<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request as RequestFacade;

class ActivityLogger
{
    /**
     * Record a single audit entry.
     *
     * @param  string  $event  Dot-namespaced identifier, e.g. "auth.login", "application.submitted".
     * @param  array<string, mixed>  $properties  Arbitrary structured context (kept out of $description).
     */
    public static function log(
        string $event,
        ?string $description = null,
        ?Model $subject = null,
        array $properties = [],
        ?User $causer = null,
    ): ActivityLog {
        $causer ??= Auth::user();

        return ActivityLog::create([
            'user_id' => $causer?->id,
            'causer_name' => $causer?->name,
            'causer_email' => $causer?->email,
            'event' => $event,
            'description' => $description,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'properties' => $properties,
            'method' => RequestFacade::method(),
            'url' => RequestFacade::fullUrl(),
            'ip_address' => RequestFacade::ip(),
            'user_agent' => RequestFacade::userAgent(),
            'created_at' => now(),
        ]);
    }
}
