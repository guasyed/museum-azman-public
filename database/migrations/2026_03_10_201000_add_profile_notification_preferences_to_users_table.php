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
            $columns = [
                'notification_movement_alerts',
                'notification_insurance_expiry',
                'notification_loan_return_due',
                'notification_restoration_due',
                'notification_valuation_updates',
                'notification_delivery_email',
                'notification_delivery_browser',
            ];

            foreach ($columns as $column) {
                if (! Schema::hasColumn('users', $column)) {
                    $table->boolean($column)->nullable();
                }
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
            $columns = [
                'notification_movement_alerts',
                'notification_insurance_expiry',
                'notification_loan_return_due',
                'notification_restoration_due',
                'notification_valuation_updates',
                'notification_delivery_email',
                'notification_delivery_browser',
            ];

            foreach ($columns as $column) {
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
