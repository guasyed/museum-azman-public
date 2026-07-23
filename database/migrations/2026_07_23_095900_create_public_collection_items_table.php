<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_collection_items', function (Blueprint $table) {
            $table->id();
            // Kept without a foreign key for compatibility with older INT artwork IDs.
            $table->unsignedBigInteger('artwork_id')->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_collection_items');
    }
};
