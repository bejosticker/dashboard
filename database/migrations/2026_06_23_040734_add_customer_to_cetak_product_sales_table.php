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
        Schema::table('cetak_product_sales', function (Blueprint $table) {
            $table->string('customer')->nullable()->after('id');        // Nama pelanggan
            $table->string('customer_phone')->nullable()->after('customer'); // Nomor WA pelanggan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cetak_product_sales', function (Blueprint $table) {
            $table->dropColumn(['customer', 'customer_phone']);
        });
    }
};
