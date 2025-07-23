<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Toko;
use App\Models\Supplier;
use App\Models\MarketOnline;

class TokoSupplierController extends Controller
{
    public function index()
    {
        $tokos = Toko::orderBy('name', 'asc')->get();
        $suppliers = Supplier::orderBy('name', 'asc')->get();
        $onlineMarkets = MarketOnline::orderBy('name', 'asc')->get();

        return view('toko-supplier.index', compact('tokos', 'suppliers', 'onlineMarkets'));
    }
}
