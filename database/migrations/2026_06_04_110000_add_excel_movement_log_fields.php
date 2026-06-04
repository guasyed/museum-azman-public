<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            if (! Schema::hasColumn('movements', 'from_location_code')) {
                $table->string('from_location_code')->nullable()->after('from_location');
            }

            if (! Schema::hasColumn('movements', 'to_location_code')) {
                $table->string('to_location_code')->nullable()->after('to_location');
            }

            if (! Schema::hasColumn('movements', 'movement_type')) {
                $table->string('movement_type')->nullable()->after('expected_return_date');
            }

            if (! Schema::hasColumn('movements', 'external_reason')) {
                $table->string('external_reason')->nullable()->after('movement_type');
            }

            if (! Schema::hasColumn('movements', 'external_party')) {
                $table->string('external_party')->nullable()->after('external_reason');
            }

            if (! Schema::hasColumn('movements', 'approved_by')) {
                $table->string('approved_by')->nullable()->after('responsible_handler');
            }

            if (! Schema::hasColumn('movements', 'completed_date')) {
                $table->date('completed_date')->nullable()->after('expected_return_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            foreach ([
                'completed_date',
                'approved_by',
                'external_party',
                'external_reason',
                'movement_type',
                'to_location_code',
                'from_location_code',
            ] as $column) {
                if (Schema::hasColumn('movements', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
