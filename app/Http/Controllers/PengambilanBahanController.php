<?php

namespace App\Http\Controllers;

use App\Models\PengambilanBahan;
use Illuminate\Http\Request;

class PengambilanBahanController extends Controller
{
    public function index()
    {
        $datas = PengambilanBahan::orderBy('id', 'desc')->paginate(10);
        return view('pengambilan-bahan.index', compact('datas'));
    }
}
