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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image')->default('default.png');

            // Harga jual berdasarkan jenis pembeli / metode jual
            $table->integer('price_agent')->default(0);        // Harga agen (per roll)
            $table->integer('price_grosir')->default(0);       // Harga grosir (per roll)
            $table->integer('price_umum_roll')->default(0);    // Harga roll umum (per roll)
            $table->integer('price_grosir_meter')->default(0); // Harga grosir per meter
            $table->integer('price_umum_meter')->default(0);   // Harga umum per meter
            $table->integer('price_kulak')->default(0);        // Harga kulak per roll

            // Panjang 1 roll dalam satuan sentimeter
            $table->integer('stock_cm')->nullable(); // dalam cm

            $table->integer('per_roll_cm')->default(1500);
            $table->integer('minimum_stock_cm')->default(1500);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
