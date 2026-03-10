<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artwork_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artwork_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->unsignedTinyInteger('position')->default(0);
            $table->string('original_name')->nullable();
            $table->timestamps();

            $table->index(['artwork_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artwork_images');
    }
};
