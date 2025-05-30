<?php

namespace Database\Seeders;

use App\Models\Promo;
use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PromoServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $promos = Promo::all();
        $services = Service::all();

        foreach ($promos as $promo) {
            $data = [];
            foreach ($services as $service) {
                $data[$service->id] = [
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // attach dengan timestamps
            $promo->services()->attach($data);
        }
    }
}
