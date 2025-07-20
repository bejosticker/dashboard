<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function index()
    {
        $sales = Sale::with(['items.product', 'paymentMethod'])
            ->withCount('items')
            ->withSum('items', 'subtotal')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('sales.index', compact('sales'));
    }

    public function destroy($id)
    {
        $data = Sale::where('id', $id)->with('items')->first();
        foreach ($data->items as $item) {
            $product = Product::where('id', $item->product->id)->first();
            $product->stock_cm = $product->stock_cm + $item->quantity;
            $product->save();
        }

        Sale::where('id', $id)->delete();

        return redirect()->back()->with('success', 'Penjualan berhasil dihapus.');
    }
}
