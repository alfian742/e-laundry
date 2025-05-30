<?php

namespace Database\Seeders;

use App\Models\Staff;
use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Factory::create('id_ID');

        // Data tetap untuk owner dan admin
        $staffs = [
            ['id' => 1, 'position' => 'owner', 'role' => 'owner', 'email' => 'owner@gmail.com'],
            ['id' => 2, 'position' => 'admin', 'role' => 'admin', 'email' => 'admin@gmail.com'],
            ['id' => 3, 'position' => 'employee', 'role' => 'employee', 'email' => 'employee@gmail.com'],
        ];

        // Buat staff owner & admin
        foreach ($staffs as $data) {
            $baseSalary         = $faker->numberBetween(6_000_000, 12_000_000);
            $bonusSalary        = $faker->optional(0.6)->numberBetween(300_000, 2_000_000);
            $deductionsSalary   = $faker->optional(0.4)->numberBetween(50_000, 500_000);

            $staff = Staff::create([
                'id'                => $data['id'],
                'fullname'          => $faker->name(),
                'phone_number'      => $faker->unique()->numerify('08##########'),
                'position'          => $data['position'],
                'base_salary'       => $baseSalary,
                'bonus_salary'      => $bonusSalary ?? 0,
                'deductions_salary' => $deductionsSalary ?? 0,
                'address'           => $faker->address,
            ]);

            User::create([
                'email'         => $data['email'],
                'password'      => Hash::make('123'), // bcrypt
                'role'          => $data['role'],
                'staff_id'      => $staff->id,
                'customer_id'   => null,
                'verified_at'   => now(),
                'status'        => 'active'
            ]);
        }

        // Buat 7 staff tambahan dengan posisi acak (id 3 - 10)
        for ($i = 4; $i <= 10; $i++) {
            $fullname           = $faker->name();
            $generatedEmail     = strtolower(str_replace(' ', '.', $fullname)) . '@gmail.com';
            $position           = $faker->randomElement(['admin', 'employee']);
            $baseSalary         = $position === 'admin' ? $faker->numberBetween(6_000_000, 12_000_000) : $faker->numberBetween(3_000_000, 8_000_000);
            $bonusSalary        = $faker->optional(0.6)->numberBetween(300_000, 2_000_000);
            $deductionsSalary   = $faker->optional(0.4)->numberBetween(50_000, 500_000);

            $staff = Staff::create([
                'fullname'          => $fullname,
                'phone_number'      => $faker->unique()->numerify('08##########'),
                'position'          => $position,
                'base_salary'       => $baseSalary,
                'bonus_salary'      => $bonusSalary ?? 0,
                'deductions_salary' => $deductionsSalary ?? 0,
                'address'           => $faker->address,
            ]);

            if (!in_array($staff->id, ['4', '5', '6'])) {
                User::create([
                    'email'         => $generatedEmail,
                    'password'      => Hash::make('123'), // bcrypt
                    'role'          => $position === 'admin' ? 'admin' : 'employee',
                    'staff_id'      => $staff->id,
                    'customer_id'   => null,
                    'verified_at'   => now(),
                    'status'        => $faker->randomElement(['active', 'non_active'])
                ]);
            }
        }
    }
}
