<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('artworks')) {
            return;
        }

        Schema::create('artworks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->unsignedSmallInteger('year')->nullable();
            $table->text('description')->nullable();
            $table->string('medium')->nullable();
            $table->decimal('size_from_cm', 8, 2)->nullable();
            $table->decimal('size_to_cm', 8, 2)->nullable();
            $table->date('acquisition_date')->nullable();
            $table->decimal('acquisition_price', 14, 2)->nullable();
            $table->decimal('current_valuation', 14, 2)->nullable();
            $table->text('provenance')->nullable();
            $table->string('status')->default('On Display');
            $table->string('primary_image_path')->nullable();
            $table->string('source_image_url')->nullable();
            $table->timestamps();

            $table->index(['artist_id', 'title']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artworks');
    }
};
