<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengambilanBahan extends Model
{
    protected $table = 'pengambilan_bahans';
    protected $primaryKey = 'id';

    protected $fillable = [
        'toko_id', 'product_id', 'price', 'quantity', 'total', 'date', 'created_at', 'updated_at'
    ];

    public function toko()
    {
        return $this->belongsTo(Toko::class, 'toko_id', 'id');
    }

    public function items()
    {
        return $this->hasMany(PengambilanBahanItem::class, 'pengambilan_bahan_id', 'id');
    }
}
