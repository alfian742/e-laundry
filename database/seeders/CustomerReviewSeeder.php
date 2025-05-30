<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerReview;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Factory::create('id_ID');

        $customerIds = Customer::pluck('id')->toArray();

        // Ambil tanggal awal dan akhir bulan sekarang
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        // Maksimal jumlah review = jumlah customer
        $maxReviews = min(50, count($customerIds));

        // Acak urutan ID untuk pemilihan yang acak
        shuffle($customerIds);

        for ($i = 0; $i < $maxReviews; $i++) {
            CustomerReview::create([
                'customer_id' => $customerIds[$i], // tidak akan terduplikasi
                'rating' => $faker->numberBetween(1, 5),
                'review' => $faker->sentence(10),
                'review_at' => $faker->dateTimeBetween($startOfMonth, $endOfMonth)->format('Y-m-d H:i:s'),
                'is_read' => true,
            ]);
        }
    }
}
