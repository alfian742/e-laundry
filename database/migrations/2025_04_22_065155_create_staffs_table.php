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
        Schema::create('staffs', function (Blueprint $table) {
            $table->id();
            $table->string('fullname');
            $table->string('phone_number', 15);
            $table->enum('position', ['owner', 'admin', 'employee'])->nullable();
            $table->decimal('base_salary', 15, 2)->default(0);
            $table->decimal('bonus_salary', 15, 2)->default(0)->nullable();
            $table->decimal('deductions_salary', 15, 2)->default(0)->nullable();
            $table->decimal('total_salary', 15, 2)->virtualAs('base_salary + IFNULL(bonus_salary, 0) - IFNULL(deductions_salary, 0)');
            $table->string('address')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staffs');
    }
};
