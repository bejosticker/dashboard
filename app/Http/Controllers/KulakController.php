<?php

namespace App\Http\Controllers;

use App\Models\Kulak;
use Illuminate\Http\Request;

class KulakController extends Controller
{
    public function index()
    {
        $kulaks = Kulak::with('items.product')
            ->withCount('items')
            ->withSum('items', 'subtotal')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('kulak.index', compact('kulaks'));
    }
}
