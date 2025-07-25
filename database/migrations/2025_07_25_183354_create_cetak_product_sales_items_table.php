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
        Schema::create('cetak_product_sales_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cetak_product_sale_id')->nullable()->constrained('cetak_product_sales')->nullOnDelete()->nullOnUpdate();
            $table->foreignId('cetak_product_id')->nullable()->constrained('cetak_products')->nullOnDelete()->nullOnUpdate();
            $table->integer('panjang');
            $table->integer('lebar');
            $table->string('price_type');
            $table->integer('price');
            $table->integer('subtotal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cetak_product_sales_items');
    }
};
