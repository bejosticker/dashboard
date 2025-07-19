<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name', 'image', 'price_agent', 'price_grosir', 'price_ecer_roll', 'price_ecer', 'price_kulak', 'stock_cm', 'per_roll_cm', 'minimum_stock_cm', 'created_at', 'updated_at'
    ];
}
