<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Status extends Model
{
    use HasFactory;

    public const DEFAULT_NAMES = [
        'On Display',
        'In Stage',
        'On Loan',
        'Under Restoration',
    ];

    protected $fillable = [
        'name',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public static function ensureDefaultRows(): void
    {
        if (! Schema::hasTable('statuses')) {
            return;
        }

        if (self::query()->exists()) {
            return;
        }

        $now = now();

        foreach (self::DEFAULT_NAMES as $index => $name) {
            self::query()->create([
                'name' => $name,
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public static function allowedNames(): array
    {
        if (! Schema::hasTable('statuses')) {
            return self::DEFAULT_NAMES;
        }

        self::ensureDefaultRows();

        return self::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('name')
            ->all() ?: self::DEFAULT_NAMES;
    }
}
