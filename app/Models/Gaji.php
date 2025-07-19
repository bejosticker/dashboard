<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gaji extends Model
{
    protected $table = 'gaji';
    protected $primaryKey = 'id';

    protected $fillable = [
        'month', 'year', 'created_at', 'updated_at'
    ];

    public function items()
    {
        return $this->hasMany(GajiItem::class, 'gaji_id', 'id');
    }
}
