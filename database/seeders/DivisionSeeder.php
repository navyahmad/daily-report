<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisions = [
            ['name' => 'Daily Report Teknisi', 'code' => 'teknisi'],
            ['name' => 'Daily Report Admin Sales', 'code' => 'admin_sales'],
            ['name' => 'Daily Report Admin Project', 'code' => 'admin_project'],
            ['name' => 'Daily Report Admin Procurement', 'code' => 'admin_procurement'],
            ['name' => 'Daily Report System Informasi', 'code' => 'system_informasi'],
            ['name' => 'Daily Report Finance', 'code' => 'finance'],
        ];

        foreach ($divisions as $division) {
            Division::firstOrCreate(['code' => $division['code']], $division);
        }
    }
}
