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

    public static function allowedNames(): array
    {
        if (! Schema::hasTable('statuses')) {
            return self::DEFAULT_NAMES;
        }

        return self::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('name')
            ->all() ?: self::DEFAULT_NAMES;
    }
}
