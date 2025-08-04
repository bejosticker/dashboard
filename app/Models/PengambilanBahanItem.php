<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengambilanBahanItem extends Model
{
    protected $table = 'pengambilan_bahan_items';
    protected $primaryKey = 'id';

    protected $fillable = [
        'pengambilan_bahan_id', 'product_id', 'product_type', 'price', 'quantity', 'subtotal', 'created_at', 'updated_at'
    ];

    public function pengambilanBahan()
    {
        return $this->belongsTo(PengambilanBahan::class, 'pengambilan_bahan_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
