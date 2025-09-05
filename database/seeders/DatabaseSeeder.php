<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'supabase_id' => (string) Str::uuid(),
                'name' => 'Super Admin',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'role' => UserRole::ADMIN,
            ]
        );
    }
}
