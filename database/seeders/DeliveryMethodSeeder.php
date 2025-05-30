<?php

namespace Database\Seeders;

use App\Models\DeliveryMethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeliveryMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['method_name' => 'Antar & Ambil Sendiri ke Tempat Laundry', 'cost' => 0, 'description' => null],
            ['method_name' => 'Ambil & Antar oleh Kurir', 'cost' => 10000, 'description' => 'Disekitar Kelurahan'],
            ['method_name' => 'Antar Kotor Sendiri & Antar Bersih oleh Kurir', 'cost' => 5000, 'description' => 'Disekitar Kelurahan'],
            ['method_name' => 'Ambil Kotor oleh Kurir & Ambil Bersih Sendiri', 'cost' => 5000, 'description' => 'Disekitar Kelurahan'],
            ['method_name' => 'Ambil & Antar oleh Kurir', 'cost' => 15000, 'description' => 'Diluar Kelurahan'],
            ['method_name' => 'Antar Kotor Sendiri & Antar Bersih oleh Kurir', 'cost' => 10000, 'description' => 'Diluar Kelurahan'],
            ['method_name' => 'Ambil Kotor oleh Kurir & Ambil Bersih Sendiri', 'cost' => 10000, 'description' => 'Diluar Kelurahan'],
        ];

        foreach ($data as $item) {
            DeliveryMethod::create([
                'method_name'   => $item['method_name'],
                'cost'          => $item['cost'],
                'description'   => $item['description'],
                'active'        => true,
            ]);
        }
    }
}
