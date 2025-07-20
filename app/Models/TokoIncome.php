<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokoIncome extends Model
{
    protected $table = 'toko_incomes';
    protected $primaryKey = 'id';

    protected $fillable = [
        'toko_id', 'name', 'amount', 'description', 'date', 'created_at', 'updated_at'
    ];

    public function toko()
    {
        return $this->belongsTo(Toko::class, 'toko_id', 'id');
    }
}
