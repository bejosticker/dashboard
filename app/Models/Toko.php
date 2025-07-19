<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Toko extends Model
{
    protected $table = 'toko';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name', 'description', 'created_at', 'updated_at'
    ];
}
