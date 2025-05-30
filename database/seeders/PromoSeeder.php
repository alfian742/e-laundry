<?php

namespace Database\Seeders;

use App\Models\Promo;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PromoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Factory::create('id_ID');

        $promoNames     = ['Promo Jumat Berkah', 'Promo Hari Raya', 'Promo Kemerdekaan', 'Promo Akhir Tahun', 'Promo Hari Libur'];
        $promoTypes     = ['daily', 'date_range'];
        $daysOfWeek     = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $customerScopes = ['member', 'non_member'];

        for ($i = 0; $i < 5; $i++) {
            $promoName = $faker->randomElement($promoNames);
            $promoType = $faker->randomElement($promoTypes);

            $startDate  = null;
            $endDate    = null;
            if ($promoType === 'date_range') {
                $startDateObj   = $faker->dateTimeBetween('-1 month', 'now');
                $endDateObj     = $faker->dateTimeBetween($startDateObj, '+1 month');
                $startDate      = $startDateObj->format('Y-m-d');
                $endDate        = $endDateObj->format('Y-m-d');
            }

            Promo::create([
                'promo_name'        => $promoName,
                'discount_percent'  => $faker->numberBetween(1, 30),
                'promo_type'        => $promoType,
                'day_of_week'       => $promoType === 'daily' ? $faker->randomElement($daysOfWeek) : null,
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'customer_scope'    => $faker->randomElement($customerScopes),
                'active'            => $faker->randomElement([true, false]),
                'description'       => $faker->sentence(),
            ]);
        }
    }
}
