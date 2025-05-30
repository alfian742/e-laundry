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
        Schema::create('promos', function (Blueprint $table) {
            $table->id();
            $table->string('promo_name');                                                                                               // Promo Jumat Berkah, Promo Hari Raya, Promo Kemerdekaan
            $table->decimal('discount_percent', 5, 2)->default(0);                                                                      // Diskon promo
            $table->enum('promo_type', ['daily', 'date_range'])->nullable();                                                            // Tipe promo
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])->nullable();    // Hanya jika promo daily
            $table->date('start_date')->nullable();                                                                                     // Untuk type date_range
            $table->date('end_date')->nullable();                                                                                       // Untuk type date_range
            $table->enum('customer_scope', ['member', 'non_member'])->nullable();                                                       // Segmentasi pelanggan
            $table->text('description')->nullable();                                                                                    // Deskripsi promo
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promos');
    }
};
