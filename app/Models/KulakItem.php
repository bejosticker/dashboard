<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KulakItem extends Model
{
    protected $table = 'kulak_item';
    protected $primaryKey = 'id';

    protected $fillable = [
        'product_id', 'price', 'rolls', 'per_roll_cm', 'subtotal', 'created_at', 'updated_at'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
