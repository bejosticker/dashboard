<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    protected $table = 'stock_adjustments';
    protected $primaryKey = 'id';

    protected $fillable = [
        'product_type', 'product_id', 'product_name', 'per_roll_cm',
        'mode', 'stock_before', 'stock_after', 'note', 'date',
    ];
}
