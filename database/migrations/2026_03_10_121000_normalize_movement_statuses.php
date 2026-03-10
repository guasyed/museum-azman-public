<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Normalize legacy movement workflow statuses into canonical artwork statuses.
        DB::table('movements')
            ->whereIn('status', ['Scheduled', 'In Transit', 'Overdue'])
            ->where('reason', 'Restoration')
            ->update(['status' => 'Under Restoration']);

        DB::table('movements')
            ->whereIn('status', ['Scheduled', 'In Transit', 'Overdue'])
            ->where('reason', 'Loan')
            ->update(['status' => 'On Loan']);

        DB::table('movements')
            ->whereIn('status', ['Scheduled', 'In Transit', 'Overdue'])
            ->where('reason', 'Exhibition')
            ->update(['status' => 'In Stage']);

        DB::table('movements')->where('status', 'Completed')->update(['status' => 'On Display']);
        DB::table('movements')->where('status', 'In Storage')->update(['status' => 'In Stage']);
        DB::table('movements')->where('status', 'Available')->update(['status' => 'On Display']);
        DB::table('movements')->whereNull('status')->update(['status' => 'On Display']);
        DB::table('movements')->where('status', '')->update(['status' => 'On Display']);

        DB::table('movements')
            ->whereIn('status', ['Scheduled', 'In Transit', 'Overdue'])
            ->update(['status' => 'On Display']);
    }

    public function down(): void
    {
        // No down migration because mapping is lossy.
    }
};
