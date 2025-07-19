<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    protected $table = 'pengeluaran';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name', 'toko_id', 'description', 'date', 'amount', 'created_at', 'updated_at'
    ];

    public function toko()
    {
        return $this->belongsTo(Toko::class, 'toko_id', 'id');
    }
}
