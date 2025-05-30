<?php

namespace Database\Seeders;

use App\Models\Service;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Factory::create('id_ID');

        $services = [
            'Cuci Kering (Per Kilogram)',
            'Cuci dan Setrika (Per Kilogram)',
            'Setrika (Per Kilogram)',
            'Layanan Ekspres (Per Kilogram)',
            'Layanan Kilat 4 Jam (Per Kilogram)',
            'Dry Cleaning (Per Item)',
            'Pencucian Sprei & Bed Cover (Per Item)',
            'Pakaian Bayi (Per Kilogram)',
            'Pencucian Karpet (Per Item)',
            'Pencucian Gordyn (Per Item)',
            'Layanan Premium (Per Kilogram / Item)',
            'Layanan Satuan (Per Item)',
            'Pencucian Sepatu (Per Pasang)',
            'Pencucian Tas (Per Item)',
        ];

        foreach ($services as $serviceName) {
            Service::create([
                'service_name'  => $serviceName,
                'price_per_kg'  => $faker->randomElement(range(5000, 15000, 100)), // [5000, 5100, 5200, ..., 20000]
                'description'   => $faker->paragraphs(3, true),
                'active'        => $faker->randomElement([true, false]),
                'img'           => 'default.jpg',
            ]);
        }
    }
}
