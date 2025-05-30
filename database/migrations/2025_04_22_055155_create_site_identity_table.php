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
        Schema::create('site_identity', function (Blueprint $table) {
            $table->id();
            $table->string('site_name');                    // Nama laundry
            $table->string('tagline')->nullable();          // Slogan/tagline
            $table->text('about_us')->nullable();           // Tentang usaha laundry
            $table->text('address')->nullable();            // Alamat lengkap
            $table->text('operational_hours')->nullable();  // Waktu operasional
            $table->string('phone_number', 15)->nullable(); // Nomor telepon/WA
            $table->string('email')->nullable();            // Email layanan
            $table->string('facebook')->nullable();         // Facebook url
            $table->string('instagram')->nullable();        // Instagran url
            $table->string('tiktok')->nullable();           // Tiktok url
            $table->string('logo')->nullable();             // Logo situs
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_identity');
    }
};
