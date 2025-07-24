<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name', 'image', 'price_agent', 'price_grosir', 'price_umum_roll', 'price_grosir_meter', 'price_umum_meter', 'price_kulak', 'stock_cm', 'per_roll_cm', 'minimum_stock_cm', 'created_at', 'updated_at'
    ];
}
