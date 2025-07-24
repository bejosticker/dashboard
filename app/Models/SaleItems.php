<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItems extends Model
{
    protected $table = 'sales_items';
    protected $primaryKey = 'id';

    protected $fillable = [
        'product_id', 'sale_id', 'price', 'price_type', 'quantity', 'subtotal', 'created_at', 'updated_at'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
