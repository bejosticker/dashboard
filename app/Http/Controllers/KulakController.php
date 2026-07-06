<?php

namespace App\Http\Controllers;

use App\Models\Kulak;
use App\Models\KulakItem;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;

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

        $kulaks = Kulak::with(['items.product', 'supplier'])
            ->when($supplier_id, fn ($q) => $q->where('supplier_id', $supplier_id))
            ->whereBetween('date', [$from, $to])
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(fn ($kulak) => [
                'id' => $kulak->id,
                'supplier_name' => $kulak->supplier?->name ?? '-',
                'date' => $kulak->date,
                'total' => (float) $kulak->total,
                'items_count' => $kulak->items->count(),
                'items' => $kulak->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product_name' => $item->product?->name ?? '-',
                    'price' => (float) $item->price,
                    'rolls' => (float) $item->rolls,
                    'subtotal' => (float) $item->subtotal,
                ])->values(),
            ]);

        $suppliers = Supplier::orderBy('name', 'asc')->get(['id', 'name']);

        $products = Product::orderBy('name', 'asc')
            ->get(['id', 'name', 'price_kulak'])
            ->map(fn ($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'harga' => (float) $product->price_kulak,
            ]);

        $total = (float) Kulak::when($supplier_id, fn ($q) => $q->where('supplier_id', $supplier_id))
            ->whereBetween('date', [$from, $to])
            ->sum('total');

        return Inertia::render('PembelianBahan/Index', [
            'kulaks' => $kulaks,
            'suppliers' => $suppliers,
            'products' => $products,
            'total' => $total,
            'filters' => [
                'from' => $from,
                'to' => $to,
                'supplier_id' => $supplier_id ?? '',
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.jumlah' => 'required|numeric|gt:0',
            'items.*.harga' => 'required|numeric|min:0',
        ]);

        $total = collect($data['items'])
            ->sum(fn ($item) => (float) $item['jumlah'] * (float) $item['harga']);

        $kulak = Kulak::create([
            'supplier_id' => $data['supplier_id'],
            'total' => $total,
            'date' => $data['date'],
        ]);

        foreach ($data['items'] as $item) {
            $product = Product::find($item['product_id']);
            $subtotal = (float) $item['jumlah'] * (float) $item['harga'];

            KulakItem::create([
                'kulak_id' => $kulak->id,
                'product_id' => $item['product_id'],
                'price' => $item['harga'],
                'rolls' => $item['jumlah'],
                'per_roll_cm' => $product->per_roll_cm,
                'subtotal' => $subtotal,
            ]);

            $product->stock_cm = $product->stock_cm + ((float) $item['jumlah'] * (float) $product->per_roll_cm);
            $product->save();
        }

        return redirect()->back()->with('success', 'Data berhasil disimpan!');
    }

    public function destroy($id)
    {
        Kulak::where('id', $id)->delete();

        return redirect()->back()->with('success', 'Kulak berhasil dihapus.');
    }
}
