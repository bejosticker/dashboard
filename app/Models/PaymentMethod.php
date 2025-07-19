<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $table = 'payment_methods';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name', 'image', 'created_at', 'updated_at'
    ];
}
