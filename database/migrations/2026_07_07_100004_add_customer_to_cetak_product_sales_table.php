<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cetak_product_sales', function (Blueprint $table) {
            // Nama + No. Telp (WA) customer pada penjualan cetak (revisi klien) — idempotent
            if (!Schema::hasColumn('cetak_product_sales', 'customer')) {
                $table->string('customer')->nullable()->after('id');
            }
            if (!Schema::hasColumn('cetak_product_sales', 'customer_phone')) {
                $table->string('customer_phone')->nullable()->after('customer');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cetak_product_sales', function (Blueprint $table) {
            foreach (['customer', 'customer_phone'] as $col) {
                if (Schema::hasColumn('cetak_product_sales', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
