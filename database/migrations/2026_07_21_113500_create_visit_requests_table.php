<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('email');
            $table->string('occupation');
            $table->string('company');
            $table->string('city');
            $table->string('social')->nullable();
            $table->string('purpose');
            $table->string('category');
            $table->date('preferred_date');
            $table->unsignedTinyInteger('guests')->default(1);
            $table->string('source');
            $table->text('message')->nullable();
            $table->json('preferences')->nullable();
            $table->timestamp('reviewed_at')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_requests');
    }
};
