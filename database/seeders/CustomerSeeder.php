<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Factory::create('id_ID');

        // Buat 1 customer utama
        $customer = Customer::create([
            'id'            => 1,
            'fullname'      => $faker->name(),
            'phone_number'  => $faker->unique()->numerify('08##########'),
            'customer_type' => 'member',
            'address'       => $faker->address,
        ]);

        User::create([
            'email'         => 'user@gmail.com',
            'password'      => Hash::make('123'), // bcrypt
            'role'          => 'customer',
            'staff_id'      => null,
            'customer_id'   => $customer->id,
            'verified_at'   => now(),
            'status'        => 'active'
        ]);

        // Buat customer tambahan mulai dari 2-30
        for ($i = 2; $i <= 30; $i++) {
            $fullname = $faker->name();
            $generatedEmail = strtolower(str_replace(' ', '.', $fullname)) . '@gmail.com';

            $customerType = $faker->randomElement(['member', 'non_member']);

            $customer = Customer::create([
                'fullname'      => $fullname,
                'phone_number'  => $faker->unique()->numerify('08##########'),
                'customer_type' => $customerType,
                'address'       => $faker->address,
            ]);

            // Buat akun user hanya jika customer_type adalah 'member' dan customer_id bukan 1
            if ($customerType === 'member') {
                User::create([
                    'email'         => $generatedEmail,
                    'password'      => Hash::make('123'), // bcrypt
                    'role'          => 'customer',
                    'staff_id'      => null,
                    'customer_id'   => $customer->id,
                    'verified_at'   => now(),
                    'status'        => $faker->randomElement(['active', 'non_active'])
                ]);
            }
        }
    }
}
