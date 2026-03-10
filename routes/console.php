<?php

use App\Models\Setting;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

$backupTime = '03:00';
$backupTimezone = (string) config('app.timezone', 'UTC');

if (Schema::hasTable('settings')) {
    $settings = Setting::query()
        ->whereIn('key', ['backup_auto_time', 'timezone'])
        ->pluck('value', 'key');

    $configuredTime = (string) ($settings->get('backup_auto_time') ?? '03:00');
    if (preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $configuredTime) === 1) {
        $backupTime = $configuredTime;
    }

    $configuredTimezone = (string) ($settings->get('timezone') ?? $backupTimezone);
    try {
        new DateTimeZone($configuredTimezone);
        $backupTimezone = $configuredTimezone;
    } catch (Throwable) {
        // Keep default app timezone.
    }
}

Schedule::command('backup:auto')
    ->dailyAt($backupTime)
    ->timezone($backupTimezone)
    ->withoutOverlapping();
