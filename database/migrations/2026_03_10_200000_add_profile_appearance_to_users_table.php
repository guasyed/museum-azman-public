<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'appearance_theme')) {
                $table->string('appearance_theme', 16)->nullable()->after('approved_at');
            }

            if (! Schema::hasColumn('users', 'appearance_density')) {
                $table->string('appearance_density', 16)->nullable()->after('appearance_theme');
            }

            if (! Schema::hasColumn('users', 'appearance_accent_color')) {
                $table->string('appearance_accent_color', 7)->nullable()->after('appearance_density');
            }

            if (! Schema::hasColumn('users', 'appearance_heading_font')) {
                $table->string('appearance_heading_font', 32)->nullable()->after('appearance_accent_color');
            }

            if (! Schema::hasColumn('users', 'appearance_body_font')) {
                $table->string('appearance_body_font', 32)->nullable()->after('appearance_heading_font');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $toDrop = [];

            foreach ([
                'appearance_theme',
                'appearance_density',
                'appearance_accent_color',
                'appearance_heading_font',
                'appearance_body_font',
            ] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $toDrop[] = $column;
                }
            }

            if (! empty($toDrop)) {
                $table->dropColumn($toDrop);
            }
        });
    }
};
