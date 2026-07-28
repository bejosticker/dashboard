<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    // Soft delete: produk yang dihapus tetap tersimpan supaya price_kulak-nya
    // masih bisa dibaca oleh item penjualan/pengambilan/kulak yang lama.
    use SoftDeletes;

    protected $table = 'products';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name', 'image', 'price_agent', 'price_grosir', 'price_umum_roll', 'price_grosir_meter', 'price_umum_meter', 'price_eceran_grosir_cm', 'price_eceran_umum_cm', 'price_kulak', 'stock_cm', 'per_roll_cm', 'minimum_stock_cm', 'created_at', 'updated_at'
    ];
}
