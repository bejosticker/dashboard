<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\PengambilanBahan;
use App\Models\Toko;

class PengambilanBahanController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->from;
        $to = $request->to;
        $toko_id = $request->toko_id;

        if (!$request->from || !$request->to) {
            $defaultFrom = now()->startOfMonth()->toDateString();
            $defaultTo = now()->toDateString();

            return redirect()->route('pengambilan-bahan', array_merge($request->all(), [
                'from' => $request->from ?? $defaultFrom,
                'to' => $request->to ?? $defaultTo,
                'toko_id' => $toko_id ?? ''
            ]));
        }

        $datas = PengambilanBahan::with(['product', 'toko']);
        if ($toko_id) {
            $datas = $datas->where('toko_id', $toko_id);
        }
        
        $datas = $datas->whereBetween('date', [$from, $to])
            ->orderBy('id', 'desc')
            ->paginate(10);

        $tokos = Toko::orderBy('name', 'asc')->get();

        $products = Product::where('stock_cm', '>', 0)
            ->orderBy('name', 'asc')
            ->get();

        return view('pengambilan-bahan.index', compact('datas', 'tokos', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'toko_id' => 'nullable|exists:toko,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required',
            'date' => 'required'
        ]);

        $product = Product::where('id', $request->input('product_id'))->first();
        if ($request->input('quantity') > ($product->stock_cm / $product->per_roll_cm)) {
            return back()->withErrors(['Quantity melebihi stock']);
        }

        PengambilanBahan::create([
            'toko_id' => $request->input('toko_id'),
            'product_id' => $request->input('product_id'),
            'price' => $product->price_agent,
            'quantity' => $request->input('quantity'),
            'total' => $product->price_agent * $request->input('quantity'),
            'date' => $request->input('date')
        ]);

        $product->stock_cm = $product->stock_cm - ($request->input('quantity') * $product->per_roll_cm);
        $product->save();

        return back()->with('success', 'Pengambilan barang berhasil disimpan');
    }

    public function destroy($id)
    {
        $data = PengambilanBahan::where('id', $id)->with('product')->first();
        if ($data->product) {
            Product::where('id', $data->product->id)
                ->update([
                    'stock_cm' => $data->product->stock_cm + ($data->quantity * $data->product->per_roll_cm)
                ]);
        }

        PengambilanBahan::where('id', $id)->delete();

        return redirect()->back()->with('success', 'Pengambilan bahan berhasil dihapus.');
    }
}
