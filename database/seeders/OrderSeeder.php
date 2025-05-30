<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\DeliveryMethod;
use App\Models\Order;
use App\Models\Staff;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Factory::create('id_ID');

        $customers = Customer::pluck('id')->toArray();
        $staffs = Staff::pluck('id')->toArray();
        $deliveryMethods = DeliveryMethod::all();

        $year = now()->year;
        $startDate = "{$year}-01-01";
        $endDate = "{$year}-12-31";

        for ($i = 0; $i < 50; $i++) {
            $delivery = $faker->randomElement($deliveryMethods);

            // Logika pickup
            if ($faker->boolean(70)) { // 70% kemungkinan ada tanggal pickup
                $pickupDate = $faker->dateTimeBetween($startDate, $endDate)->format('Y-m-d');
                $pickupTime = $faker->time('H:i');
            } else {
                $pickupDate = null;
                $pickupTime = null;
            }

            // Logika delivery
            if ($faker->boolean(70)) { // 70% kemungkinan ada tanggal delivery
                $deliveryDate = $faker->dateTimeBetween($startDate, $endDate)->format('Y-m-d');
                $deliveryTime = $faker->time('H:i');
            } else {
                $deliveryDate = null;
                $deliveryTime = null;
            }

            $order = Order::create([
                'staff_id' => $faker->optional()->randomElement($staffs),
                'customer_id' => $faker->randomElement($customers),
                'delivery_method_id' => $delivery->id,
                'delivery_cost' => $delivery->cost,
                'total_service_price' => 0, // Akan diupdate oleh OrderServiceDetailSeeder
                'order_status' => $faker->randomElement(['new', 'pending', 'in_progress', 'pickup', 'delivery', 'done', 'canceled']),
                'payment_status' => 'unpaid', // default unpaid
                'pickup_date' => $pickupDate,
                'pickup_time' => $pickupTime,
                'delivery_date' => $deliveryDate,
                'delivery_time' => $deliveryTime,
                'notes' => $faker->optional()->text(150),
            ]);

            $order->update([
                'order_code' => 'ORD-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(5)),
            ]);
        }
    }
}
