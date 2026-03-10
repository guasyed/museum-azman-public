<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('movements')) {
            return;
        }

        Schema::create('movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artwork_id')->constrained()->cascadeOnDelete();
            $table->string('from_location');
            $table->string('to_location');
            $table->date('date_out');
            $table->date('expected_return_date')->nullable();
            $table->string('responsible_handler');
            $table->string('reason');
            $table->string('status')->default('Scheduled');
            $table->text('notes')->nullable();
            $table->text('condition_report')->nullable();
            $table->timestamps();

            $table->index(['status', 'date_out']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movements');
    }
};
