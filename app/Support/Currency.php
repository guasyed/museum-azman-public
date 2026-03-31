<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

class Currency
{
    private static ?string $cachedCode = null;

    public static function clearCache(): void
    {
        self::$cachedCode = null;
    }

    public static function code(): string
    {
        if (self::$cachedCode !== null) {
            return self::$cachedCode;
        }

        if (! Schema::hasTable('settings')) {
            return self::$cachedCode = 'USD';
        }

        $value = Setting::query()->where('key', 'currency')->value('value');
        $code = is_string($value) ? strtoupper(trim($value)) : 'USD';

        return self::$cachedCode = in_array($code, ['USD', 'MYR', 'EUR', 'GBP', 'SGD'], true)
            ? $code
            : 'USD';
    }

    public static function symbol(): string
    {
        return match (self::code()) {
            'MYR' => 'RM',
            'EUR' => '€',
            'GBP' => '£',
            'SGD' => 'S$',
            default => '$',
        };
    }

    public static function short(float|int|string|null $amount, int $precision = 2): string
    {
        $value = (float) ($amount ?? 0);
        $absolute = abs($value);

        if ($absolute < 1000) {
            return self::symbol() . number_format($value, 0);
        }

        $units = [
            12 => 'T',
            9 => 'B',
            6 => 'M',
            3 => 'K',
        ];

        foreach ($units as $power => $suffix) {
            $divisor = 10 ** $power;
            if ($absolute >= $divisor) {
                $scaled = $value / $divisor;
                $formatted = number_format($scaled, $precision, '.', '');
                $trimmed = rtrim(rtrim($formatted, '0'), '.');

                return self::symbol() . $trimmed . $suffix;
            }
        }

        return self::symbol() . number_format($value, 0);
    }
}
