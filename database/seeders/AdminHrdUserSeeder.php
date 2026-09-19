<?php

namespace Database\Seeders;

use App\Models\AdminHrdUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminHrdUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Default Administrator Account
        AdminHrdUser::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator Kantor',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // 2. Default HRD Account
        AdminHrdUser::firstOrCreate(
            ['username' => 'hrd'],
            [
                'name' => 'Tim HRD Kantor',
                'password' => Hash::make('Gsindonesi4'),
                'role' => 'hrd',
                'is_active' => true,
            ]
        );

        // 3. Default Manager Account
        AdminHrdUser::firstOrCreate(
            ['username' => 'manager'],
            [
                'name' => 'Manager Kantor',
                'password' => Hash::make('Gsindonesi4'),
                'role' => 'manager',
                'is_active' => true,
            ]
        );
    }
}
