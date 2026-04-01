<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CountriesTableSeeder::class,
        ]);

        User::query()->updateOrCreate(
            ['email' => 'admin@museumazman.com'],
            [
                'name' => 'Admin User',
                'role' => 'admin',
                'password' => 'password',
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'user@museumazman.com'],
            [
                'name' => 'Standard User',
                'role' => 'user',
                'password' => 'password',
            ]
        );
    }
}
