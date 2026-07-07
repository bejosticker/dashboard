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
            // Harga eceran per lembar (revisi klien) — idempotent: lewati bila kolom sudah ada
            if (!Schema::hasColumn('cetak_products', 'price_eceran_grosir')) {
                $table->integer('price_eceran_grosir')->default(0)->after('price_umum');
            }
            if (!Schema::hasColumn('cetak_products', 'price_eceran_umum')) {
                $table->integer('price_eceran_umum')->default(0)->after('price_eceran_grosir');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cetak_products', function (Blueprint $table) {
            foreach (['price_eceran_grosir', 'price_eceran_umum'] as $col) {
                if (Schema::hasColumn('cetak_products', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
