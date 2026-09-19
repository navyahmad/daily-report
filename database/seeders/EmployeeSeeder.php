<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employeeData = [
            'teknisi' => [
                'Budi Santoso',
                'Eko Prasetyo',
                'Ahmad Fauzi',
                'Dedi Supriyadi',
            ],
            'admin_sales' => [
                'Siti Rahmawati',
                'Rina Oktaviani',
                'Dewi Lestari',
            ],
            'admin_project' => [
                'Fajar Nugroho',
                'Andi Wijaya',
            ],
            'admin_procurement' => [
                'Hendra Gunawan',
                'Maya Indah',
            ],
            'system_informasi' => [
                'Rizky Pratama',
                'Bambang Pamungkas',
            ],
            'finance' => [
                'Tri Wahyuni',
                'Sri Handayani',
            ],
        ];

        foreach ($employeeData as $divisionCode => $names) {
            $division = Division::where('code', $divisionCode)->first();
            if ($division) {
                foreach ($names as $name) {
                    Employee::firstOrCreate(
                        ['name' => $name, 'division_id' => $division->id],
                        ['is_active' => true]
                    );
                }
            }
        }
    }
}
