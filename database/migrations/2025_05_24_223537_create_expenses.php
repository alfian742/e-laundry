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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_category');                                                     // Kategori pengeluaran
            $table->decimal('total_amount', 15, 2)->default(0);                                     // Total tagihan
            $table->decimal('paid_amount', 15, 2)->default(0);;                                     // Jumlah yang dibayar
            $table->decimal('outstanding_amount', 15, 2)->virtualAs('total_amount - paid_amount');  // Sisa yang belum dibayar
            $table->enum('status', ['unpaid', 'partial', 'paid']);                                  // Status pembayaran
            $table->text('notes')->nullable();                                                      // Catatan atau deskripsi tambahan
            $table->date('paid_at')->nullable();                                                    // Tanggal pembayaran
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
