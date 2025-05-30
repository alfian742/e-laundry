<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Default
        // // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Custom
        $this->call(SiteIdentitySeeder::class);
        $this->call(StaffSeeder::class);
        $this->call(CustomerSeeder::class);
        $this->call(ServiceSeeder::class);
        $this->call(PromoSeeder::class);
        $this->call(PromoServiceSeeder::class);
        $this->call(DeliveryMethodSeeder::class);
        $this->call(PaymentMethodSeeder::class);
        $this->call(OrderSeeder::class);
        $this->call(OrderServiceDetailSeeder::class);
        $this->call(TransactionSeeder::class);
        $this->call(ExpenseSeeder::class);
        $this->call(CustomerReviewSeeder::class);
    }
}
