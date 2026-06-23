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
        Schema::table('products', function (Blueprint $table) {
            // Harga eceran per sentimeter (revisi klien)
            $table->integer('price_eceran_grosir_cm')->default(0)->after('price_umum_meter'); // Harga eceran grosir per cm
            $table->integer('price_eceran_umum_cm')->default(0)->after('price_eceran_grosir_cm'); // Harga eceran umum per cm
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['price_eceran_grosir_cm', 'price_eceran_umum_cm']);
        });
    }
};
