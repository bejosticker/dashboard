<?php

namespace App\Http\Controllers;

use App\Models\Kulak;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;

class KulakController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->from;
        $to = $request->to;
        $supplier_id = $request->supplier_id;

        if (!$request->from || !$request->to) {
            $defaultFrom = now()->startOfMonth()->toDateString();
            $defaultTo = now()->toDateString();

            return redirect()->route('kulak', array_merge($request->all(), [
                'from' => $request->from ?? $defaultFrom,
                'to' => $request->to ?? $defaultTo,
                'supplier_id' => $supplier_id ?? ''
            ]));
        }

        $kulaks = Kulak::with(['items.product', 'supplier']);
        if ($supplier_id) {
            $kulaks = $kulaks->where('supplier_id', $supplier_id);
        }
        
        $kulaks = $kulaks->whereBetween('date', [$from, $to])
            ->withCount('items')
            ->withSum('items', 'subtotal')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        $suppliers = Supplier::orderBy('name', 'asc')->select('id', 'name')->get();

        $products = Product::select('id', 'name', 'price_kulak as harga')->orderBy('name', 'asc')->get()->toArray();
        session(['kulak_products' => $products]);

        $total = Kulak::whereBetween('date', [$from, $to]);
        if ($supplier_id) {
            $total = $total->where('supplier_id', $supplier_id);
        }
        $total = $total->sum('total');

        return view('kulak.index', compact('kulaks', 'suppliers', 'total'));
    }

    public function destroy($id)
    {
        Kulak::where('id', $id)->delete();

        return redirect()->back()->with('success', 'Kulak berhasil dihapus.');
    }
}
