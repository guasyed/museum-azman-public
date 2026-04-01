<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * Record an activity log entry.
     *
     * @param  string  $action       Short machine-readable key (e.g. "artwork.created")
     * @param  string  $description  Human-readable sentence
     * @param  object|null  $subject  The Eloquent model being acted upon (optional)
     */
    public static function log(string $action, string $description, ?object $subject = null): void
    {
        try {
            $user = Auth::user();

            $payload = [
                'action'       => $action,
                'description'  => $description,
                'user_id'      => $user?->id,
                'user_name'    => $user?->name,
                'user_role'    => $user?->role ?? null,
                'ip_address'   => Request::ip(),
                'user_agent'   => mb_substr((string) Request::userAgent(), 0, 500),
            ];

            if ($subject !== null) {
                $payload['subject_type']  = class_basename($subject);
                $payload['subject_id']    = method_exists($subject, 'getKey') ? $subject->getKey() : null;
                $payload['subject_label'] = static::resolveLabel($subject);
            }

            ActivityLog::create($payload);
        } catch (\Throwable) {
            // Never let logging failures bubble up to the user.
        }
    }

    private static function resolveLabel(object $subject): ?string
    {
        foreach (['title', 'name', 'email'] as $field) {
            if (isset($subject->{$field}) && is_string($subject->{$field}) && trim($subject->{$field}) !== '') {
                return mb_substr(trim($subject->{$field}), 0, 255);
            }
        }

        return null;
    }
}
