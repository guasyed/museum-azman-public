<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Setting;
use App\Services\ImageOptimizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(private readonly ImageOptimizer $imageOptimizer)
    {
    }

    public function generateBackup(): RedirectResponse
    {
        $currentUser = request()->user();
        if (! $currentUser || ! $currentUser->isAdmin()) {
            return redirect()
                ->route('settings.index', ['tab' => 'general'])
                ->withErrors(['backup' => 'Only admin users can generate database backups.']);
        }

        if (! Schema::hasTable('migrations')) {
            return redirect()
                ->route('settings.index', ['tab' => 'general'])
                ->withErrors(['backup' => 'Database schema is unavailable for backup.']);
        }

        $timezone = $this->getConfiguredTimezone();
        $relativePath = $this->createBackupFile($timezone);
        if ($relativePath === null) {
            return redirect()
                ->route('settings.index', ['tab' => 'general'])
                ->withErrors(['backup' => 'Backup generation failed. Please try again.']);
        }

        $now = Carbon::now($timezone);
        $this->saveSettings([
            'backup_last_file' => $relativePath,
            'backup_last_generated_at' => $now->toIso8601String(),
        ]);

        return redirect()
            ->route('settings.index', ['tab' => 'general'])
            ->with('success', 'Backup generated and saved to storage successfully.');
    }

    public function downloadBackup(Request $request): BinaryFileResponse|RedirectResponse
    {
        $currentUser = request()->user();
        if (! $currentUser || ! $currentUser->isAdmin()) {
            return redirect()
                ->route('settings.index', ['tab' => 'general'])
                ->withErrors(['backup' => 'Only admin users can download database backups.']);
        }

        if (! Schema::hasTable('migrations')) {
            return redirect()
                ->route('settings.index', ['tab' => 'general'])
                ->withErrors(['backup' => 'Database schema is unavailable for backup.']);
        }

        $settings = $this->getSettingsMap();
        $requestedFile = basename((string) $request->string('file'));
        $relativePath = $requestedFile !== ''
            ? 'private/backups/'.$requestedFile
            : (string) ($settings['backup_last_file'] ?? '');

        if ($relativePath === '' || ! Storage::disk('local')->exists($relativePath)) {
            return redirect()
                ->route('settings.index', ['tab' => 'general'])
                ->withErrors(['backup' => 'No stored backup found. Please generate a backup first.']);
        }

        $absolutePath = Storage::disk('local')->path($relativePath);
        $fileName = basename($relativePath);

        return response()->download($absolutePath, $fileName, [
            'Content-Type' => 'application/json',
        ]);
    }

    public function deleteBackup(Request $request): RedirectResponse
    {
        $currentUser = $request->user();
        if (! $currentUser || ! $currentUser->isAdmin()) {
            return redirect()
                ->route('settings.index', ['tab' => 'general'])
                ->withErrors(['backup' => 'Only admin users can delete database backups.']);
        }

        $fileName = basename((string) $request->string('file'));
        if ($fileName === '') {
            return redirect()
                ->route('settings.index', ['tab' => 'general'])
                ->withErrors(['backup' => 'Invalid backup file selected.']);
        }

        $relativePath = 'private/backups/'.$fileName;
        if (! Storage::disk('local')->exists($relativePath)) {
            return redirect()
                ->route('settings.index', ['tab' => 'general'])
                ->withErrors(['backup' => 'Backup file not found.']);
        }

        Storage::disk('local')->delete($relativePath);

        $settings = $this->getSettingsMap();
        $lastFile = (string) ($settings['backup_last_file'] ?? '');
        if ($lastFile === $relativePath) {
            $latest = $this->listBackups($this->getConfiguredTimezone())[0] ?? null;
            $this->saveSettings([
                'backup_last_file' => $latest['relative_path'] ?? '',
                'backup_last_generated_at' => $latest['generated_at_iso'] ?? '',
            ]);
        }

        return redirect()
            ->route('settings.index', ['tab' => 'general'])
            ->with('success', 'Backup deleted successfully.');
    }

    public function index(Request $request): View
    {
        $currentUser = $request->user();
        $canManageSettings = (bool) ($currentUser?->isAdmin());
        $canAccessAdminTabs = $canManageSettings;

        $allowedTabs = $canAccessAdminTabs
            ? ['general', 'users-roles', 'notifications', 'appearance']
            : ['notifications', 'appearance'];
        $activeTab = (string) $request->string('tab', 'general');

        if (! in_array($activeTab, $allowedTabs, true)) {
            $activeTab = $canAccessAdminTabs ? 'general' : 'notifications';
        }

        $settings = $this->getSettingsMap();
        $configuredTimezone = $this->getConfiguredTimezone($settings);

        $generalSettings = [
            'organization_name' => $settings['organization_name'] ?? 'Private Collection - Family Office',
            'phone_number' => $settings['phone_number'] ?? '+60 3-2161 0000',
            'address' => $settings['address'] ?? 'Bangsar, Kuala Lumpur, Malaysia',
            'contact_email' => $settings['contact_email'] ?? 'contact@collection.com',
            'timezone' => $configuredTimezone,
            'organization_logo_path' => $settings['organization_logo_path'] ?? null,
            'organization_logo_url' => isset($settings['organization_logo_path']) && Storage::disk('public')->exists((string) $settings['organization_logo_path'])
                ? asset('storage/'.ltrim((string) $settings['organization_logo_path'], '/'))
                : null,
        ];

        $regionalSettings = [
            'currency' => $settings['currency'] ?? 'USD',
            'date_format' => $settings['date_format'] ?? 'Y-m-d',
        ];

        $notificationSettings = [
            'movement_alerts' => ($settings['movement_alerts'] ?? '1') === '1',
            'insurance_expiry' => ($settings['insurance_expiry'] ?? '1') === '1',
            'loan_return_due' => ($settings['loan_return_due'] ?? '1') === '1',
            'restoration_due' => ($settings['restoration_due'] ?? '0') === '1',
            'valuation_updates' => ($settings['valuation_updates'] ?? '1') === '1',
            'delivery_email' => ($settings['delivery_email'] ?? '1') === '1',
            'delivery_browser' => ($settings['delivery_browser'] ?? '0') === '1',
        ];

        if ($currentUser) {
            $notificationSettings = [
                'movement_alerts' => $currentUser->notification_movement_alerts ?? $notificationSettings['movement_alerts'],
                'insurance_expiry' => $currentUser->notification_insurance_expiry ?? $notificationSettings['insurance_expiry'],
                'loan_return_due' => $currentUser->notification_loan_return_due ?? $notificationSettings['loan_return_due'],
                'restoration_due' => $currentUser->notification_restoration_due ?? $notificationSettings['restoration_due'],
                'valuation_updates' => $currentUser->notification_valuation_updates ?? $notificationSettings['valuation_updates'],
                'delivery_email' => $currentUser->notification_delivery_email ?? $notificationSettings['delivery_email'],
                'delivery_browser' => $currentUser->notification_delivery_browser ?? $notificationSettings['delivery_browser'],
            ];
        }

        $appearanceSettings = [
            'theme' => $settings['theme'] ?? 'light',
            'density' => $settings['density'] ?? 'comfortable',
            'accent_color' => $settings['accent_color'] ?? '#1c1917',
            'heading_font' => $settings['heading_font'] ?? 'cormorant',
            'body_font' => $settings['body_font'] ?? 'inter',
        ];

        $backupList = $this->listBackups($configuredTimezone);
        $backupSettings = [
            'enabled' => ($settings['backup_auto_enabled'] ?? '1') === '1',
            'time' => $settings['backup_auto_time'] ?? '03:00',
        ];

        $backupMeta = [
            'last_generated_at' => $settings['backup_last_generated_at'] ?? null,
            'last_file' => $settings['backup_last_file'] ?? null,
            'has_file' => isset($settings['backup_last_file']) && Storage::disk('local')->exists((string) $settings['backup_last_file']),
            'timezone' => $configuredTimezone,
        ];

        $userSortColumn = in_array((string) $request->string('user_sort', 'name'), ['name', 'email', 'role', 'status'], true)
            ? (string) $request->string('user_sort', 'name')
            : 'name';
        $userDirection = strtolower((string) $request->string('user_direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        $users = $activeTab === 'users-roles'
            ? (function () use ($userSortColumn, $userDirection) {
                $query = User::query()->with('roleRelation');

                if ($userSortColumn === 'role') {
                    $query
                        ->leftJoin('roles as sort_roles', 'sort_roles.id', '=', 'users.role_id')
                        ->select('users.*')
                        ->orderBy('sort_roles.name', $userDirection)
                        ->orderBy('users.name');
                } elseif ($userSortColumn === 'status') {
                    $query
                        ->orderBy('users.is_approved', $userDirection)
                        ->orderBy('users.name');
                } else {
                    $query->orderBy($userSortColumn, $userDirection);
                }

                return $query->get();
            })()
            : collect();

        $roles = $activeTab === 'users-roles'
            ? Role::query()->orderByRaw("CASE slug WHEN 'owner' THEN 1 WHEN 'curator' THEN 2 WHEN 'admin' THEN 3 WHEN 'logistics-handler' THEN 4 ELSE 99 END")->get()
            : collect();

        return view('settings.index', compact(
            'activeTab',
            'canManageSettings',
            'canAccessAdminTabs',
            'generalSettings',
            'regionalSettings',
            'notificationSettings',
            'appearanceSettings',
            'backupSettings',
            'backupMeta',
            'backupList',
            'users',
            'roles',
            'userSortColumn',
            'userDirection'
        ));
    }

    public function update(Request $request, string $section): RedirectResponse
    {
        $currentUser = $request->user();
        $isAdmin = (bool) ($currentUser?->isAdmin());

        $adminOnlySections = ['general', 'regional', 'backup'];
        if (! $isAdmin && in_array($section, $adminOnlySections, true)) {
            return redirect()
                ->route('settings.index', ['tab' => 'appearance'])
                ->withErrors(['settings' => 'Only admin users can change this settings section.']);
        }

        if (! $request->user()?->isAdmin()) {
            if (! in_array($section, ['notifications', 'appearance'], true)) {
                return redirect()
                    ->route('settings.index', ['tab' => 'appearance'])
                    ->withErrors(['settings' => 'Only admin users can change this settings section.']);
            }
        }

        if (! Schema::hasTable('settings')) {
            return redirect()
                ->route('settings.index', ['tab' => 'general'])
                ->withErrors(['settings' => 'Settings table is missing. Please run database migrations.']);
        }

        switch ($section) {
            case 'general':
                $validated = $request->validate([
                    'organization_name' => ['required', 'string', 'max:255'],
                    'phone_number' => ['nullable', 'string', 'max:255'],
                    'address' => ['nullable', 'string', 'max:255'],
                    'contact_email' => ['required', 'email', 'max:255'],
                    'timezone' => ['required', 'string', 'max:100'],
                    'organization_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
                ]);

                if ($request->hasFile('organization_logo')) {
                    $settings = $this->getSettingsMap();
                    $oldLogoPath = (string) ($settings['organization_logo_path'] ?? '');

                    $stored = $this->imageOptimizer->storeUploaded($request->file('organization_logo'), 'branding');
                    if ($stored && isset($stored['path'])) {
                        $validated['organization_logo_path'] = (string) $stored['path'];

                        if ($oldLogoPath !== '') {
                            Storage::disk('public')->delete($oldLogoPath);
                        }
                    }
                }

                unset($validated['organization_logo']);

                $this->saveSettings($validated);

                return redirect()
                    ->route('settings.index', ['tab' => 'general'])
                    ->with('success', 'General settings updated successfully.');

            case 'regional':
                $validated = $request->validate([
                    'currency' => ['required', 'in:USD,MYR,EUR,GBP,SGD'],
                    'date_format' => ['required', 'in:Y-m-d,d/m/Y,m/d/Y,d M Y'],
                ]);

                $this->saveSettings($validated);

                return redirect()
                    ->route('settings.index', ['tab' => 'general'])
                    ->with('success', 'Regional settings updated successfully.');

            case 'notifications':
                $validated = $request->validate([
                    'movement_alerts' => ['sometimes', 'boolean'],
                    'insurance_expiry' => ['sometimes', 'boolean'],
                    'loan_return_due' => ['sometimes', 'boolean'],
                    'restoration_due' => ['sometimes', 'boolean'],
                    'valuation_updates' => ['sometimes', 'boolean'],
                    'delivery_email' => ['sometimes', 'boolean'],
                    'delivery_browser' => ['sometimes', 'boolean'],
                ]);

                $payload = [
                    'movement_alerts' => $request->boolean('movement_alerts') ? '1' : '0',
                    'insurance_expiry' => $request->boolean('insurance_expiry') ? '1' : '0',
                    'loan_return_due' => $request->boolean('loan_return_due') ? '1' : '0',
                    'restoration_due' => $request->boolean('restoration_due') ? '1' : '0',
                    'valuation_updates' => $request->boolean('valuation_updates') ? '1' : '0',
                    'delivery_email' => $request->boolean('delivery_email') ? '1' : '0',
                    'delivery_browser' => $request->boolean('delivery_browser') ? '1' : '0',
                ];

                if ($isAdmin) {
                    $this->saveSettings($payload);
                }

                if ($currentUser) {
                    $currentUser->update([
                        'notification_movement_alerts' => $request->boolean('movement_alerts'),
                        'notification_insurance_expiry' => $request->boolean('insurance_expiry'),
                        'notification_loan_return_due' => $request->boolean('loan_return_due'),
                        'notification_restoration_due' => $request->boolean('restoration_due'),
                        'notification_valuation_updates' => $request->boolean('valuation_updates'),
                        'notification_delivery_email' => $request->boolean('delivery_email'),
                        'notification_delivery_browser' => $request->boolean('delivery_browser'),
                    ]);
                }

                return redirect()
                    ->route('settings.index', ['tab' => 'notifications'])
                    ->with('success', 'Notification preferences updated successfully.');

            case 'appearance':
                $validated = $request->validate([
                    'theme' => ['required', 'in:light,dark'],
                    'density' => ['required', 'in:comfortable,compact,spacious'],
                    'accent_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
                    'heading_font' => ['required', 'in:cormorant,playfair,lora,inter,manrope'],
                    'body_font' => ['required', 'in:inter,manrope,lora,playfair,cormorant'],
                ]);

                $validated['accent_color'] = strtolower((string) $validated['accent_color']);

                $this->saveSettings($validated);

                return redirect()
                    ->route('settings.index', ['tab' => 'appearance'])
                    ->with('success', 'Appearance preferences updated successfully.');

            case 'backup':
                $validated = $request->validate([
                    'backup_auto_enabled' => ['sometimes', 'boolean'],
                    'backup_auto_time' => ['required', 'date_format:H:i'],
                ]);

                $this->saveSettings([
                    'backup_auto_enabled' => $request->boolean('backup_auto_enabled') ? '1' : '0',
                    'backup_auto_time' => (string) ($validated['backup_auto_time'] ?? '03:00'),
                ]);

                return redirect()
                    ->route('settings.index', ['tab' => 'general'])
                    ->with('success', 'Backup automation settings updated successfully.');
        }

        return redirect()
            ->route('settings.index', ['tab' => 'general'])
            ->withErrors(['settings' => 'Invalid settings section.']);
    }

    /**
     * @return array<string, string>
     */
    private function getSettingsMap(): array
    {
        if (! Schema::hasTable('settings')) {
            return [];
        }

        return Setting::query()
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * @param array<string, string> $settings
     */
    private function saveSettings(array $settings): void
    {
        foreach ($settings as $key => $value) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => (string) $value]
            );
        }

        \App\Support\Currency::clearCache();
        \App\Support\DateFormat::clearCache();
    }

    private function createBackupFile(string $timezone): ?string
    {
        $now = Carbon::now($timezone);
        $timestamp = $now->format('Ymd-His');
        $relativeDir = 'private/backups';
        $relativePath = $relativeDir.'/database-backup-'.$timestamp.'.json';

        if (! Storage::disk('local')->exists($relativeDir)) {
            Storage::disk('local')->makeDirectory($relativeDir);
        }

        $payload = [
            'generated_at' => $now->toIso8601String(),
            'timezone' => $timezone,
            'connection' => DB::getDefaultConnection(),
            'tables' => $this->snapshotDatabaseTables(),
        ];

        $written = Storage::disk('local')->put(
            $relativePath,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        return $written ? $relativePath : null;
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function snapshotDatabaseTables(): array
    {
        $driver = DB::getDriverName();
        $tables = [];

        if ($driver === 'pgsql') {
            $rows = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename");
            $tables = array_map(static fn ($row) => (string) $row->tablename, $rows);
        } elseif ($driver === 'mysql') {
            $rows = DB::select('SHOW TABLES');
            foreach ($rows as $row) {
                $tables[] = (string) array_values((array) $row)[0];
            }
        } elseif ($driver === 'sqlite') {
            $rows = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name");
            $tables = array_map(static fn ($row) => (string) $row->name, $rows);
        }

        $snapshot = [];
        foreach ($tables as $table) {
            $snapshot[$table] = DB::table($table)->get()->map(static fn ($row) => (array) $row)->all();
        }

        return $snapshot;
    }

    /**
     * @param array<string, string>|null $settings
     */
    private function getConfiguredTimezone(?array $settings = null): string
    {
        $settings = $settings ?? $this->getSettingsMap();
        $timezone = (string) ($settings['timezone'] ?? config('app.timezone', 'UTC'));

        try {
            new \DateTimeZone($timezone);
            return $timezone;
        } catch (\Throwable) {
            return (string) config('app.timezone', 'UTC');
        }
    }

    /**
     * @return array<int, array<string, string|int|null>>
     */
    private function listBackups(string $timezone): array
    {
        $relativeDir = 'private/backups';
        if (! Storage::disk('local')->exists($relativeDir)) {
            return [];
        }

        $items = collect(Storage::disk('local')->files($relativeDir))
            ->filter(fn (string $path) => str_ends_with(strtolower($path), '.json'))
            ->map(function (string $path) use ($timezone) {
                $fileName = basename($path);
                $lastModified = Storage::disk('local')->lastModified($path);
                $generatedAt = Carbon::createFromTimestamp($lastModified, 'UTC')->setTimezone($timezone);

                return [
                    'file_name' => $fileName,
                    'relative_path' => $path,
                    'size_kb' => (int) ceil((Storage::disk('local')->size($path) ?: 0) / 1024),
                    'generated_at_iso' => $generatedAt->toIso8601String(),
                    'generated_at_display' => $generatedAt->format('M j, Y \a\t g:i A'),
                ];
            })
            ->sortByDesc('generated_at_iso')
            ->values();

        return $items->all();
    }
}
