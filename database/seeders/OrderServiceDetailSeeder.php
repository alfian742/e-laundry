<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderServiceDetail;
use App\Models\Promo;
use App\Models\Service;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderServiceDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Factory::create('id_ID');

        $services = Service::all();
        $promos = Promo::whereNotNull('discount_percent')->where('discount_percent', '>', 0)->get(); // hanya promo valid

        foreach (Order::all() as $order) {
            $totalServicePrice = 0;

            $serviceCount = rand(1, 3);
            $selectedServices = $services->random($serviceCount);

            foreach ($selectedServices as $service) {
                $weight = $faker->numberBetween(1, 5); // 1–5 kg
                $pricePerKg = $service->price_per_kg;

                // Pilih promo (50% kemungkinan), dan ambil persis nilainya
                $promo = $faker->boolean(50) && $promos->count() > 0 ? $promos->random() : null;

                $discountPercent = $promo?->discount_percent ?? 0;
                $totalBeforeDiscount = $weight * $pricePerKg;
                $totalAfterDiscount = $totalBeforeDiscount * (1 - ($discountPercent / 100));

                OrderServiceDetail::create([
                    'order_id' => $order->id,
                    'service_id' => $service->id,
                    'weight_kg' => $weight,
                    'price_per_kg' => $pricePerKg,
                    'promo_id' => $promo?->id,
                    'discount_percent' => $discountPercent, // HARUS sama dengan promo
                ]);

                // Tambah ke total harga layanan
                $totalServicePrice += $totalAfterDiscount;
            }

            // Simpan total harga layanan ke order
            $order->update([
                'total_service_price' => $totalServicePrice,
            ]);
        }
    }
}
