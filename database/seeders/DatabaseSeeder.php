<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Default Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@dftm.com'],
            [
                'name' => 'System Administrator',
                'username' => 'admin',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'phone' => '09171234567',
                'status' => 'active',
            ]
        );

        // 2. Create Default Encoder User
        $encoder = User::firstOrCreate(
            ['email' => 'encoder@dftm.com'],
            [
                'name' => 'Lead Data Encoder',
                'username' => 'encoder',
                'password' => Hash::make('encoder123'),
                'role' => 'encoder',
                'phone' => '09187654321',
                'status' => 'active',
            ]
        );

        ActivityLog::log('SYSTEM_INIT', 'System initialized with default Administrator and Encoder accounts.');
    }
}
