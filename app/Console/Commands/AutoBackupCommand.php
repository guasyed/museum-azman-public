<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AutoBackupCommand extends Command
{
    protected $signature = 'backup:auto';

    protected $description = 'Generate database backup automatically based on settings';

    public function handle(): int
    {
        if (! Schema::hasTable('settings') || ! Schema::hasTable('migrations')) {
            $this->warn('Settings or migrations table not available. Skipping auto backup.');
            return self::SUCCESS;
        }

        $settings = Setting::query()->pluck('value', 'key')->toArray();
        $enabled = ($settings['backup_auto_enabled'] ?? '1') === '1';

        if (! $enabled) {
            $this->info('Auto backup is disabled.');
            return self::SUCCESS;
        }

        $timezone = $this->resolveTimezone((string) ($settings['timezone'] ?? config('app.timezone', 'UTC')));
        $now = Carbon::now($timezone);
        $relativePath = $this->createBackupFile($timezone, $now);

        if ($relativePath === null) {
            $this->error('Auto backup failed.');
            return self::FAILURE;
        }

        Setting::query()->updateOrCreate(['key' => 'backup_last_file'], ['value' => $relativePath]);
        Setting::query()->updateOrCreate(['key' => 'backup_last_generated_at'], ['value' => $now->toIso8601String()]);

        $this->info('Auto backup generated: '.$relativePath);

        return self::SUCCESS;
    }

    private function createBackupFile(string $timezone, Carbon $now): ?string
    {
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

    private function resolveTimezone(string $timezone): string
    {
        try {
            new \DateTimeZone($timezone);
            return $timezone;
        } catch (\Throwable) {
            return (string) config('app.timezone', 'UTC');
        }
    }
}
