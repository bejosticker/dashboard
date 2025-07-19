<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kulak extends Model
{
    protected $table = 'kulak';
    protected $primaryKey = 'id';

    protected $fillable = [
        'supplier_id', 'total', 'created_at', 'updated_at'
    ];

    public function items()
    {
        return $this->hasMany(KulakItem::class, 'kulak_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }
}
