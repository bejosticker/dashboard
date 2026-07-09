<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('stock_adjustments')) {
            Schema::create('stock_adjustments', function (Blueprint $table) {
                $table->id();
                $table->string('product_type'); // 'product' (bahan) | 'cetak_product'
                $table->unsignedBigInteger('product_id');
                $table->string('product_name')->nullable(); // snapshot nama saat penyesuaian
                $table->decimal('per_roll_cm', 12, 2)->nullable(); // snapshot utk format bahan (Roll/Meter)
                $table->string('mode'); // 'set' | 'add' | 'sub'
                $table->decimal('stock_before', 14, 2)->default(0); // satuan native: cm (bahan) / m (cetak)
                $table->decimal('stock_after', 14, 2)->default(0);
                $table->string('note')->nullable(); // alasan
                $table->date('date');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
