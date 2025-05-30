<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_service_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');                                              // Relasi ke orders
            $table->foreignId('service_id')->nullable()->constrained('services')->onDelete('set null');                             // Relasi ke services
            $table->decimal('weight_kg', 15, 2)->default(0);                                                                        // Berat cucian
            $table->decimal('price_per_kg', 15, 2)->default(0);                                                                     // Harga per kg (snapshot)
            $table->decimal('total_price', 15, 2)->virtualAs('weight_kg * price_per_kg');                                           // Hitung total harga
            $table->foreignId('promo_id')->nullable()->constrained('promos')->onDelete('set null');                                 // Promo jika ada
            $table->decimal('discount_percent', 5, 2)->default(0);                                                                  // Diskon persentase
            $table->decimal('final_service_price', 15, 2)->virtualAs('(total_price - (total_price * discount_percent / 100))');     // Harga final setelah diskon
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_service_details');
    }
};
