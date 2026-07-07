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
            // Harga eceran per centimeter (revisi klien) — idempotent: lewati bila kolom sudah ada
            if (!Schema::hasColumn('products', 'price_eceran_grosir_cm')) {
                $table->integer('price_eceran_grosir_cm')->default(0)->after('price_umum_meter');
            }
            if (!Schema::hasColumn('products', 'price_eceran_umum_cm')) {
                $table->integer('price_eceran_umum_cm')->default(0)->after('price_eceran_grosir_cm');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            foreach (['price_eceran_grosir_cm', 'price_eceran_umum_cm'] as $col) {
                if (Schema::hasColumn('products', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
