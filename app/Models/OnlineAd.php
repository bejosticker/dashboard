<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnlineAd extends Model
{
    protected $table = 'online_ads';
    protected $primaryKey = 'id';

    protected $fillable = [
        'online_market_id', 'amount', 'date', 'created_at', 'updated_at'
    ];

    public function shop()
    {
        return $this->belongsTo(MarketOnline::class, 'online_market_id', 'id');
    }
}
