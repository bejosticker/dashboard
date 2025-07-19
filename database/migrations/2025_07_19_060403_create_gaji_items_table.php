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
        Schema::create('gaji_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gaji_id')->nullable()->constrained('gaji')->nullOnDelete()->nullOnUpdate();
            $table->foreignId('karyawan_id')->nullable()->constrained('karyawan')->nullOnDelete()->nullOnUpdate();
            $table->integer('amount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gaji_items');
    }
};
