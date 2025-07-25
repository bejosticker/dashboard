<?php

namespace App\Models;
use App\Models\CetakProduct;
use Illuminate\Database\Eloquent\Model;

class CetakProductSaleItem extends Model
{
    protected $table = 'cetak_product_sales_items';
    protected $primaryKey = 'id';

    protected $fillable = [
        'cetak_product_sale_id', 'cetak_product_id', 'panjang', 'lebar', 'price_type', 'price', 'subtotal', 'created_at', 'updated_at'
    ];

    public function product()
    {
        return $this->belongsTo(CetakProduct::class, 'cetak_product_id', 'id');
    }
}
