<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('artworks')) {
            return;
        }

        Schema::table('artworks', function (Blueprint $table) {
            $table->unsignedInteger('artist_id')->nullable()->change();
            $table->unsignedInteger('location_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('artworks')) {
            return;
        }

        Schema::table('artworks', function (Blueprint $table) {
            $table->unsignedTinyInteger('artist_id')->nullable()->change();
            $table->unsignedTinyInteger('location_id')->nullable()->change();
        });
    }
};
