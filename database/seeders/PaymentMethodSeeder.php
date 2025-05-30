<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'method_name' => 'Cash',
                'payment_type' => 'manual',
                'description' => 'Pembayaran tunai langsung di lokasi laundry.',
                'img' => null
            ],
            [
                'method_name' => 'COD',
                'payment_type' => 'manual',
                'description' => 'Bayar di tempat saat layanan antar-jemput laundry.',
                'img' => null
            ],
            [
                'method_name' => 'QRIS',
                'payment_type' => 'online',
                'description' => 'Pindai kode QRIS yang tersedia, kemudian lakukan pembayaran sesuai jumlah yang tertera.',
                'img' => 'default.jpg'
            ],
            [
                'method_name' => 'Dana (081234567890)',
                'payment_type' => 'online',
                'description' => 'Transfer pembayaran ke nomor Dana: 081234567890 sesuai jumlah yang tertera. Unggah bukti pembayaran diperlukan.',
                'img' => 'default.jpg'
            ],
            [
                'method_name' => 'BRI (123456789012345)',
                'payment_type' => 'bank_transfer',
                'description' => 'Transfer pembayaran ke rekening BRI: 123456789012345 sesuai jumlah yang tertera. Unggah bukti pembayaran diperlukan.',
                'img' => null
            ],

        ];

        foreach ($data as $item) {
            PaymentMethod::create([
                'method_name'   => $item['method_name'],
                'payment_type'  => $item['payment_type'],
                'description'   => $item['description'],
                'img'           => $item['img'],
                'active'        => true,
            ]);
        }
    }
}
