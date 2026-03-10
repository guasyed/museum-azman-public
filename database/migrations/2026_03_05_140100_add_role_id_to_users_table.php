<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('role')->constrained('roles')->nullOnDelete();
        });

        $adminRoleId = DB::table('roles')->where('slug', 'admin')->value('id');
        $curatorRoleId = DB::table('roles')->where('slug', 'curator')->value('id');

        if ($adminRoleId) {
            DB::table('users')->where('role', 'admin')->update(['role_id' => $adminRoleId]);
        }

        if ($curatorRoleId) {
            DB::table('users')->where(function ($query) {
                $query->whereNull('role')->orWhere('role', '!=', 'admin');
            })->update(['role_id' => $curatorRoleId]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
        });
    }
};
