<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    protected $table = 'karyawan';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name', 'month', 'year', 'gaji', 'toko_id', 'created_at', 'updated_at'
    ];
}
