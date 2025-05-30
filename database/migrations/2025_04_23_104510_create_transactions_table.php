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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_id', 27)->nullable()->unique();                                       // Invoice ID
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');                    // Relasi ke orders
            $table->foreignId('payment_method_id')->constrained('payment_methods')->onDelete('restrict'); // Metode pembayaran
            $table->decimal('amount_paid', 15, 2);                                                        // Jumlah dibayar
            $table->enum('status', ['pending', 'success', 'failed', 'rejected'])->default('pending');     // Status transaksi
            $table->string('notes')->nullable();                                                          // Pesan transaksi (jika ada)
            $table->timestamp('paid_at')->nullable();                                                     // Waktu pembayaran sukses
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
