<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('artworks')->where('status', 'In Storage')->update(['status' => 'In Stage']);
        DB::table('artworks')->where('status', 'In Transit')->update(['status' => 'On Loan']);
        DB::table('artworks')->where('status', 'Scheduled')->update(['status' => 'On Display']);
        DB::table('artworks')->where('status', 'Completed')->update(['status' => 'On Display']);
        DB::table('artworks')->where('status', 'Overdue')->update(['status' => 'On Loan']);
        DB::table('artworks')->where('status', 'Available')->update(['status' => 'On Display']);
        DB::table('artworks')->whereNull('status')->update(['status' => 'On Display']);
        DB::table('artworks')->where('status', '')->update(['status' => 'On Display']);
    }

    public function down(): void
    {
        // Intentionally left empty because previous status values are lossy after normalization.
    }
};
