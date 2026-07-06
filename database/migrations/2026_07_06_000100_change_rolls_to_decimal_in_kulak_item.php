<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ubah kolom rolls dari integer ke decimal supaya pembelian jumlah pecahan
     * (mis. 0.5 roll) bisa disimpan tanpa dibulatkan ke 0.
     */
    public function up(): void
    {
        Schema::table('kulak_item', function (Blueprint $table) {
            $table->decimal('rolls', 10, 2)->change();
        });
    }

    public function down(): void
    {
        Schema::table('kulak_item', function (Blueprint $table) {
            $table->integer('rolls')->change();
        });
    }
};
