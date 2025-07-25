<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CetakProduct extends Model
{
    protected $table = 'cetak_products';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name', 'price_grosir', 'price_umum', 'created_at', 'updated_at'
    ];
}
