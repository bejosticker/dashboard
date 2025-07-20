<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $table = 'sales';
    protected $primaryKey = 'id';

    protected $fillable = [
        'customer', 'total', 'payment_method_id', 'price_type', 'discount', 'date', 'created_at', 'updated_at'
    ];

    public function items()
    {
        return $this->hasMany(SaleItems::class, 'sale_id', 'id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id', 'id');
    }
}
