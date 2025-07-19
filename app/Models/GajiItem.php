<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GajiItem extends Model
{
    protected $table = 'gaji_items';
    protected $primaryKey = 'id';

    protected $fillable = [
        'gaji_id', 'karyawan_id', 'created_at', 'updated_at'
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id', 'id');
    }
}
