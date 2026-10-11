<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@capcutstore.local'],
            [
                'name' => 'Store Administrator',
                'password' => Hash::make('Admin@CapCut2026!'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
