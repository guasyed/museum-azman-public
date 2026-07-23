<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_artist_profiles', function (Blueprint $table) {
            $table->id();
            // Do not add a database foreign key here: older installations use an
            // INT artist primary key while fresh Laravel installations use BIGINT.
            $table->unsignedBigInteger('artist_id')->unique();
            $table->string('image_path')->nullable();
            $table->text('biography')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_artist_profiles');
    }
};
