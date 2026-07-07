<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cetak_product_sales_items', function (Blueprint $table) {
            // Kolom "Lembaran" (jumlah lembar) untuk penjualan eceran — disimpan di 'quantity'. Idempotent.
            if (!Schema::hasColumn('cetak_product_sales_items', 'quantity')) {
                $table->decimal('quantity', 12, 2)->nullable()->after('lebar');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cetak_product_sales_items', function (Blueprint $table) {
            if (Schema::hasColumn('cetak_product_sales_items', 'quantity')) {
                $table->dropColumn('quantity');
            }
        });
    }
};
