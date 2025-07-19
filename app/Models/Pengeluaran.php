<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    protected $table = 'pengeluaran';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name', 'description', 'date', 'amount', 'created_at', 'updated_at'
    ];
}
