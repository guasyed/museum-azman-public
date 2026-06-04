<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('artworks', function (Blueprint $table) {
            if (! Schema::hasColumn('artworks', 'inventory_code')) {
                $table->string('inventory_code')->nullable()->unique()->after('id');
            }
        });

        Schema::table('locations', function (Blueprint $table) {
            if (! Schema::hasColumn('locations', 'code')) {
                $table->string('code')->nullable()->unique()->after('id');
            }
        });

        Schema::table('movements', function (Blueprint $table) {
            if (! Schema::hasColumn('movements', 'external_movement_id')) {
                $table->string('external_movement_id')->nullable()->unique()->after('id');
            }
        });

        if (Schema::hasColumn('artworks', 'inventory_code')) {
            $usedCodes = DB::table('artworks')
                ->whereNotNull('inventory_code')
                ->pluck('inventory_code')
                ->mapWithKeys(fn ($code) => [(string) $code => true])
                ->all();

            $nextNumber = collect(array_keys($usedCodes))
                ->map(function ($code) {
                    preg_match('/^ART-(\d+)$/', (string) $code, $matches);

                    return isset($matches[1]) ? (int) $matches[1] : 0;
                })
                ->max() ?? 0;

            DB::table('artworks')
                ->whereNull('inventory_code')
                ->orderBy('id')
                ->select('id')
                ->chunkById(200, function ($artworks) use (&$usedCodes, &$nextNumber): void {
                    foreach ($artworks as $artwork) {
                        $code = 'ART-'.str_pad((string) $artwork->id, 4, '0', STR_PAD_LEFT);
                        while (isset($usedCodes[$code])) {
                            $nextNumber++;
                            $code = 'ART-'.str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
                        }

                        $usedCodes[$code] = true;

                        DB::table('artworks')
                            ->where('id', $artwork->id)
                            ->update([
                                'inventory_code' => $code,
                            ]);
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            if (Schema::hasColumn('movements', 'external_movement_id')) {
                $table->dropUnique(['external_movement_id']);
                $table->dropColumn('external_movement_id');
            }
        });

        Schema::table('locations', function (Blueprint $table) {
            if (Schema::hasColumn('locations', 'code')) {
                $table->dropUnique(['code']);
                $table->dropColumn('code');
            }
        });

        Schema::table('artworks', function (Blueprint $table) {
            if (Schema::hasColumn('artworks', 'inventory_code')) {
                $table->dropUnique(['inventory_code']);
                $table->dropColumn('inventory_code');
            }
        });
    }
};
