<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengambilanBahan extends Model
{
    protected $table = 'pengambilan_bahans';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name', 'image', 'created_at', 'updated_at'
    ];
}
