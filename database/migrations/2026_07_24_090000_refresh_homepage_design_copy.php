<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $replacements = [
            'public_home_events_title' => ['Featured Events', 'Museum Programmes'],
            'public_home_events_description' => ['Exhibitions, talks, and special programming.', 'Tours, collection stories and cultural dialogue.'],
            'public_home_works_title' => ['Selected Works', 'Collection in Focus'],
            'public_home_works_description' => ['Highlights from our permanent collection.', 'Selected works and artists from the permanent collection.'],
        ];

        foreach ($replacements as $key => [$oldValue, $newValue]) {
            DB::table('settings')
                ->where('key', $key)
                ->where('value', $oldValue)
                ->update(['value' => $newValue, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $replacements = [
            'public_home_events_title' => ['Museum Programmes', 'Featured Events'],
            'public_home_events_description' => ['Tours, collection stories and cultural dialogue.', 'Exhibitions, talks, and special programming.'],
            'public_home_works_title' => ['Collection in Focus', 'Selected Works'],
            'public_home_works_description' => ['Selected works and artists from the permanent collection.', 'Highlights from our permanent collection.'],
        ];

        foreach ($replacements as $key => [$newValue, $oldValue]) {
            DB::table('settings')
                ->where('key', $key)
                ->where('value', $newValue)
                ->update(['value' => $oldValue, 'updated_at' => now()]);
        }
    }
};
