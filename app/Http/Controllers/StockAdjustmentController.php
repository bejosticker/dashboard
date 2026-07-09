<?php

namespace App\Http\Controllers;

use App\Models\StockAdjustment;
use Illuminate\Http\Request;

class StockAdjustmentController extends Controller
{
    public function index(Request $request)
    {
        $adjustments = StockAdjustment::orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('stock-adjustments.index', compact('adjustments'));
    }
}
