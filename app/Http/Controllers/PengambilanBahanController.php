<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\PengambilanBahan;
use App\Models\PengambilanBahanItem;
use App\Models\Toko;
use Inertia\Inertia;

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

        $datas = PengambilanBahan::with(['items.product', 'toko']);
        if ($toko_id) {
            $datas = $datas->where('toko_id', $toko_id);
        }

        $datas = $datas->whereBetween('date', [$from, $to])
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Hitung laba per baris (guard produk null dipertahankan).
        $datas->getCollection()->transform(function ($data) {
            $laba = 0;
            foreach ($data->items as $item) {
                if (!$item->product) {
                    continue;
                }
                if ($item->product_type == 'roll') {
                    $laba += ($item->price - $item->product->price_kulak) * $item->quantity;
                } else {
                    $kulakPerMeter = $item->product->price_kulak / $item->product->per_roll_cm * 100;
                    $laba += ($item->price - $kulakPerMeter) * $item->quantity;
                }
            }
            $data->laba = $laba;
            return $data;
        });

        $tokos = Toko::orderBy('name', 'asc')->get(['id', 'name']);

        // Opsi produk untuk modal (menggantikan session('products') komponen Livewire).
        $products = Product::select('id', 'name', 'price_agent', 'price_grosir_meter', 'per_roll_cm')
            ->where('stock_cm', '>', 0)
            ->orderBy('name', 'asc')
            ->get();

        $total = PengambilanBahan::whereBetween('date', [$from, $to]);
        if ($toko_id) {
            $total = $total->where('toko_id', $toko_id);
        }
        $total = $total->sum('total');

        $allPengambilanBahan = PengambilanBahan::whereBetween('date', [$from, $to]);
        if ($toko_id) {
            $allPengambilanBahan = $allPengambilanBahan->where('toko_id', $toko_id);
        }
        $allPengambilanBahan = $allPengambilanBahan->with('items.product')->get();

        $labaTotal = 0;
        foreach ($allPengambilanBahan as $data) {
            $laba = 0;
            foreach ($data->items as $item) {
                if (!$item->product) {
                    continue;
                }
                if ($item->product_type == 'roll') {
                    $laba += ($item->price - $item->product->price_kulak) * $item->quantity;
                } else {
                    $kulakPerMeter = $item->product->price_kulak / $item->product->per_roll_cm * 100;
                    $laba += ($item->price - $kulakPerMeter) * $item->quantity;
                }
            }
            $labaTotal += $laba;
        }

        return Inertia::render('PengambilanBahan/Index', [
            'datas' => $datas,
            'tokos' => $tokos,
            'products' => $products,
            'total' => $total,
            'labaTotal' => $labaTotal,
            'filters' => [
                'from' => $from,
                'to' => $to,
                'toko_id' => $toko_id ?? '',
            ],
        ]);
    }

    public function store(Request $request)
    {
        // Port dari App\Livewire\PengambilanBahanForm::save().
        // Jumlah pakai gt:0 agar nilai desimal seperti 0.5 diterima.
        $validated = $request->validate([
            'toko_id' => 'required|exists:toko,id',
            'date' => 'required',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_type' => 'required|in:roll,meter',
            'items.*.jumlah' => 'required|numeric|gt:0',
            'items.*.harga' => 'required|numeric|min:0',
        ]);

        $total = 0;
        foreach ($validated['items'] as $item) {
            $total += (float) $item['jumlah'] * (float) $item['harga'];
        }

        $pengambilan = PengambilanBahan::create([
            'toko_id' => $validated['toko_id'],
            'total' => $total,
            'date' => $validated['date'],
        ]);

        foreach ($validated['items'] as $item) {
            $product = Product::where('id', $item['product_id'])->first();
            $jumlah = (float) $item['jumlah'];
            $harga = (float) $item['harga'];
            $subtotal = $jumlah * $harga;

            PengambilanBahanItem::create([
                'pengambilan_bahan_id' => $pengambilan->id,
                'product_id' => $item['product_id'],
                'product_type' => $item['product_type'],
                'price' => $harga,
                'quantity' => $jumlah,
                'subtotal' => $subtotal,
            ]);

            $product->stock_cm = ($product->stock_cm ?? 0) - ($item['product_type'] == 'roll'
                ? $jumlah * $product->per_roll_cm
                : $jumlah * 100);
            $product->save();
        }

        return redirect()->back()->with('success', 'Data berhasil disimpan!');
    }

    public function destroy($id)
    {
        $data = PengambilanBahan::where('id', $id)->with('items')->first();
        if ($data->items) {
            foreach ($data->items as $item) {
                if (!$item->product) {
                    continue;
                }
                Product::where('id', $item->product_id)
                    ->update([
                        'stock_cm' => $item->product->stock_cm + ($item->quantity * ($item->product_type == 'roll' ? $item->product->per_roll_cm : 100))
                    ]);
            }
        }

        PengambilanBahan::where('id', $id)->delete();

        return redirect()->back()->with('success', 'Pengambilan bahan berhasil dihapus.');
    }
}
