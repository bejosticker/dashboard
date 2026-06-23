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
        Schema::table('cetak_product_sales_items', function (Blueprint $table) {
            // Satuan baru: Quantity tunggal + unit (cm / lembar) menggantikan panjang x lebar
            $table->decimal('quantity', 12, 2)->nullable()->after('lebar');
            $table->string('unit')->nullable()->after('quantity'); // 'cm' atau 'lembar'
            // panjang & lebar tidak lagi wajib untuk record baru
            $table->decimal('panjang', 10, 2)->nullable()->change();
            $table->decimal('lebar', 10, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cetak_product_sales_items', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'unit']);
        });
    }
};
