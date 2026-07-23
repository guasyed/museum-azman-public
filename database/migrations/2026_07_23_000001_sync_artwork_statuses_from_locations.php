<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            ! Schema::hasTable('artworks')
            || ! Schema::hasTable('locations')
            || ! Schema::hasColumn('artworks', 'location_id')
            || ! Schema::hasColumn('artworks', 'status')
            || ! Schema::hasColumn('locations', 'name')
        ) {
            return;
        }

        DB::transaction(function (): void {
            $storeLocationIds = DB::table('locations')
                ->whereRaw('LOWER(TRIM(name)) = ?', ['store'])
                ->pluck('id');

            $soldLocationIds = DB::table('locations')
                ->whereRaw('LOWER(TRIM(name)) = ?', ['sold or left'])
                ->pluck('id');

            $externalLocationIds = DB::table('locations')
                ->whereRaw('LOWER(TRIM(name)) = ?', ['external'])
                ->pluck('id');

            if ($storeLocationIds->isNotEmpty()) {
                DB::table('artworks')
                    ->whereIn('location_id', $storeLocationIds)
                    ->update(['status' => 'In Storage']);
            }

            if ($soldLocationIds->isNotEmpty()) {
                DB::table('artworks')
                    ->whereIn('location_id', $soldLocationIds)
                    ->update(['status' => 'Sold or Left']);
            }

            if ($externalLocationIds->isNotEmpty()) {
                DB::table('artworks')
                    ->whereIn('location_id', $externalLocationIds)
                    ->whereNotIn('status', [
                        'Under Restoration',
                        'Loaned Out',
                        'Under Evaluation',
                    ])
                    ->update(['status' => 'Under Restoration']);
            }

            $specialLocationIds = $storeLocationIds
                ->merge($soldLocationIds)
                ->merge($externalLocationIds)
                ->unique()
                ->values();

            DB::table('artworks')
                ->when(
                    $specialLocationIds->isNotEmpty(),
                    fn ($query) => $query->where(function ($query) use ($specialLocationIds) {
                        $query->whereNull('location_id')
                            ->orWhereNotIn('location_id', $specialLocationIds);
                    })
                )
                ->update(['status' => 'On Display']);
        });
    }

    public function down(): void
    {
        // Status normalization is intentionally irreversible because the
        // previous values cannot be reconstructed reliably from location data.
    }
};
