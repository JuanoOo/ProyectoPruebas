<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Test user: ana@test.local / Abcd1234
        // Password satisfies: >= 8 chars, uppercase (A), lowercase (bcd), number (1234)
        User::firstOrCreate(
            ['email' => 'ana@test.local'],
            [
                'name'     => 'Ana Test',
                'email'    => 'ana@test.local',
                'password' => Hash::make('Abcd1234'),
            ]
        );
    }
}
