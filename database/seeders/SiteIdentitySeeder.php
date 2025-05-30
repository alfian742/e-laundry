<?php

namespace Database\Seeders;

use App\Models\SiteIdentity;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SiteIdentitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $abouUs = 'SkyLaundry adalah layanan laundry profesional yang hadir untuk memenuhi kebutuhan kebersihan pakaian Anda dengan standar kualitas terbaik. Kami berkomitmen untuk memberikan layanan yang cepat, bersih, dan terpercaya, demi memastikan kenyamanan dan kepuasan pelanggan di setiap prosesnya. Didukung oleh tim yang berpengalaman dan peralatan modern, kami melayani berbagai jenis cucian — mulai dari laundry kiloan, satuan (per item), hingga layanan khusus seperti pencucian pakaian berbahan sensitif. Setiap pakaian Anda kami tangani dengan penuh perhatian dan kehati-hatian, karena kami percaya bahwa kebersihan dan perawatan pakaian adalah bagian dari gaya hidup sehat.';

        SiteIdentity::create([
            'site_name'         => 'SkyLaundry',
            'tagline'           => 'Bersih, Wangi, Cepat!',
            'logo'              => 'default.jpg',
            'email'             => 'mail@example.com',
            'phone_number'      => '081234567890',
            'address'           => 'Jl. Merdeka No. 123, Jakarta',
            'operational_hours' => 'Senin - Minggu, 08.00 - 20.00 WITA',
            'facebook'          => 'https://facebook.com/',
            'instagram'         => 'https://instagram.com/',
            'tiktok'            => 'https://tiktok.com/',
            'about_us'          => $abouUs,
        ]);
    }
}
