<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('artists')) {
            return;
        }

        Schema::table('artists', function (Blueprint $table) {
            if (! Schema::hasColumn('artists', 'country_id')) {
                $table->foreignId('country_id')->nullable()->after('name')->constrained('countries')->nullOnDelete();
                $table->index('country_id');
            }
        });

        if (! Schema::hasTable('countries')) {
            return;
        }

        $artists = DB::table('artists')
            ->select('id', 'country')
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->get();

        foreach ($artists as $artist) {
            $countryName = trim((string) $artist->country);
            if ($countryName === '') {
                continue;
            }

            $countryId = DB::table('countries')->where('name', $countryName)->value('id');

            if ($countryId === null) {
                $countryId = DB::table('countries')->insertGetId([
                    'name' => $countryName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('artists')
                ->where('id', $artist->id)
                ->update(['country_id' => $countryId]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('artists') || ! Schema::hasColumn('artists', 'country_id')) {
            return;
        }

        Schema::table('artists', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropIndex(['country_id']);
            $table->dropColumn('country_id');
        });
    }
};
