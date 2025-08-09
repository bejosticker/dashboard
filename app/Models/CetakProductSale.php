<?php

namespace App\Models;
use App\Models\CetakProductSaleItem;
use Illuminate\Database\Eloquent\Model;

class CetakProductSale extends Model
{
    protected $table = 'cetak_product_sales';
    protected $primaryKey = 'id';

    protected $fillable = [
        'date', 'discount', 'total', 'created_at', 'updated_at', 'payment_method_id'
    ];

    public function items()
    {
        return $this->hasMany(CetakProductSaleItem::class, 'cetak_product_sale_id', 'id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id', 'id');
    }
}
