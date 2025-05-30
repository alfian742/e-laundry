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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code', 27)->nullable()->unique();                                                                 // Kode order unik
            $table->foreignId('staff_id')->nullable()->constrained('staffs')->onDelete('set null');                                 // Staff yang menangani
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');                           // Customer yang memesan
            $table->foreignId('delivery_method_id')->nullable()->constrained('delivery_methods')->onDelete('set null');             // Metode antar/jemput
            $table->decimal('delivery_cost', 15, 2)->default(0);                                                                    // Biaya antar jemput
            $table->decimal('total_service_price', 15, 2)->default(0);                                                              // Total harga semua layanan
            $table->decimal('final_price', 15, 2)->virtualAs('total_service_price + delivery_cost');                                // Harga total setelah delivery
            $table->enum('order_status', ['new', 'pending', 'in_progress', 'pickup', 'delivery', 'done', 'canceled'])->nullable();  // Status laundry
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->nullable();                                              // Status pembayaran
            $table->date('pickup_date')->nullable();                                                                                // Tanggal jemput
            $table->time('pickup_time')->nullable();                                                                                // Waktu jemput
            $table->date('delivery_date')->nullable();                                                                              // Tanggal antar
            $table->time('delivery_time')->nullable();                                                                              // Waktu antar
            $table->text('notes')->nullable();                                                                                      // Catatan tambahan
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
