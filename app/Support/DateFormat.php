<?php

namespace App\Support;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class DateFormat
{
    private static ?string $cachedFormat = null;

    public static function clearCache(): void
    {
        self::$cachedFormat = null;
    }

    public static function format(): string
    {
        if (self::$cachedFormat !== null) {
            return self::$cachedFormat;
        }

        if (! Schema::hasTable('settings')) {
            return self::$cachedFormat = 'Y-m-d';
        }

        $value = Setting::query()->where('key', 'date_format')->value('value');
        $allowed = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'd M Y'];

        return self::$cachedFormat = (is_string($value) && in_array($value, $allowed, true))
            ? $value
            : 'Y-m-d';
    }

    public static function display(mixed $date, string $fallback = '-'): string
    {
        if (empty($date)) {
            return $fallback;
        }

        try {
            $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
            return $carbon->format(self::format());
        } catch (\Throwable) {
            return $fallback;
        }
    }
}
