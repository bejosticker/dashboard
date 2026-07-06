<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ubah kolom quantity dari integer ke decimal supaya jumlah pecahan
     * (mis. 0.5 roll / meter) bisa disimpan tanpa dibulatkan ke 0.
     */
    public function up(): void
    {
        Schema::table('sales_items', function (Blueprint $table) {
            $table->decimal('quantity', 10, 2)->comment('jumlah roll/meter')->change();
        });
    }

    public function down(): void
    {
        Schema::table('sales_items', function (Blueprint $table) {
            $table->integer('quantity')->comment('in cm')->change();
        });
    }
};
