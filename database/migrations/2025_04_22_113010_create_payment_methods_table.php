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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('method_name')->unique(); // Nama metode pembayaran (unik)

            // Jenis pembayaran:
            // - manual: Pembayaran langsung seperti tunai, COD, dan sejenisnya.
            // - online: Pembayaran digital seperti Dana, Gopay, OVO, QRIS, dll (wajib unggah bukti pembayaran).
            // - bank_transfer: Pembayaran melalui transfer bank (wajib unggah bukti pembayaran).
            // - midtrans: Pembayaran otomatis melalui Midtrans (tidak perlu unggah bukti pembayaran).
            $table->enum('payment_type', ['manual', 'online', 'bank_transfer', 'midtrans']);

            $table->text('description')->nullable(); // Deskripsi tambahan tentang metode pembayaran (opsional)
            $table->string('img')->nullable(); // Nama file gambar pendukung, misal QR Code untuk pembayaran online (opsional)
            $table->boolean('active')->default(true); // Menentukan apakah metode pembayaran ini aktif atau tidak
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
