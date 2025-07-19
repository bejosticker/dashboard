<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\PengambilanBahan;
use App\Models\Toko;

class PengambilanBahanController extends Controller
{
    public function index()
    {
        $datas = PengambilanBahan::with(['product', 'toko'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        $tokos = Toko::orderBy('name', 'asc')->get();

        $products = Product::whereColumn('stock_cm', '<', 'minimum_stock_cm')
            ->orderBy('name', 'asc')
            ->get();
        return view('pengambilan-bahan.index', compact('datas', 'tokos', 'products'));
    }
}
