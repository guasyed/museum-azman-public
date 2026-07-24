<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

class DatabaseSmtpConfigurator
{
    public function configure(): array
    {
        $settings = Schema::hasTable('settings')
            ? Setting::query()->whereIn('key', [
                'smtp_enabled', 'smtp_host', 'smtp_port', 'smtp_encryption',
                'smtp_username', 'smtp_password', 'smtp_from_address',
                'smtp_from_name', 'visit_request_recipient',
            ])->pluck('value', 'key')
            : collect();

        $enabled = ($settings['smtp_enabled'] ?? '1') === '1';
        $recipient = trim((string) ($settings['visit_request_recipient'] ?? 'faiz@museumazman.com'));
        $host = trim((string) ($settings['smtp_host'] ?? ''));

        if ($host !== '') {
            $password = null;
            if (filled($settings['smtp_password'] ?? null)) {
                try {
                    $password = Crypt::decryptString((string) $settings['smtp_password']);
                } catch (\Throwable) {
                    $password = null;
                }
            }

            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $host,
                'mail.mailers.smtp.port' => (int) ($settings['smtp_port'] ?? 587),
                'mail.mailers.smtp.scheme' => ($settings['smtp_encryption'] ?? 'tls') === 'ssl' ? 'smtps' : 'smtp',
                'mail.mailers.smtp.username' => blank($settings['smtp_username'] ?? null) ? null : (string) $settings['smtp_username'],
                'mail.mailers.smtp.password' => $password,
                'mail.from.address' => (string) ($settings['smtp_from_address'] ?? 'noreply@museumazman.com'),
                'mail.from.name' => (string) ($settings['smtp_from_name'] ?? 'Museum Azman'),
            ]);

            app('mail.manager')->purge('smtp');
        }

        return ['enabled' => $enabled, 'recipient' => $recipient ?: 'faiz@museumazman.com'];
    }
}
