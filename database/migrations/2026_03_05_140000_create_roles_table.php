<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('description')->nullable();
            $table->json('permissions')->nullable();
            $table->timestamps();
        });

        $now = now();

        DB::table('roles')->insert([
            [
                'name' => 'Owner',
                'slug' => 'owner',
                'description' => 'Full access to all features and settings',
                'permissions' => json_encode([
                    'View all artworks and reports',
                    'Manage movements and locations',
                    'Manage users and permissions',
                    'Configure system settings',
                    'Add, edit, and delete artworks',
                    'Access financial data',
                    'Export reports',
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Curator',
                'slug' => 'curator',
                'description' => 'Manage collection and artwork information',
                'permissions' => json_encode([
                    'View all artworks',
                    'Manage artwork documentation',
                    'Request movements',
                    'Add and edit artwork metadata',
                    'View reports (no financial)',
                    'Export collection data',
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'System administration and user management',
                'permissions' => json_encode([
                    'View all artworks',
                    'Configure system settings',
                    'Manage locations',
                    'Manage users (except Owner role)',
                    'View audit logs',
                    'Export reports',
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Logistics Handler',
                'slug' => 'logistics-handler',
                'description' => 'Movement tracking and logistics management',
                'permissions' => json_encode([
                    'View artworks',
                    'Update artwork locations',
                    'View movement history',
                    'Create and manage movements',
                    'Upload condition reports',
                    'Limited export access',
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
