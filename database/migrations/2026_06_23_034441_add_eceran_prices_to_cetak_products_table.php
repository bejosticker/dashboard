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
        Schema::table('cetak_products', function (Blueprint $table) {
            // Harga eceran per lembar (revisi klien)
            $table->integer('price_eceran_grosir')->default(0)->after('price_umum'); // Harga eceran grosir per lembar
            $table->integer('price_eceran_umum')->default(0)->after('price_eceran_grosir'); // Harga eceran umum per lembar
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cetak_products', function (Blueprint $table) {
            $table->dropColumn(['price_eceran_grosir', 'price_eceran_umum']);
        });
    }
};
