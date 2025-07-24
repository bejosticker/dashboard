<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketOnline extends Model
{
    protected $table = 'online_markets';

    protected $fillable = [
        'name',
        'toko_id',
        'description',
        'vendor',
        'created_at',
        'updated_at',
    ];

    public function toko()
    {
        return $this->belongsTo(Toko::class, 'toko_id', 'id');
    }
}
