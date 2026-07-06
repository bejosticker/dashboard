<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItems;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SalesController extends Controller
{
    /** Jenis harga yang memakai satuan Roll (per_roll_cm), selain itu Meter. */
    private array $rollPriceTypes = ['price_agent', 'price_grosir', 'price_umum_roll'];

    public function index(Request $request)
    {
        $from = $request->from;
        $to = $request->to;
        $payment_method_id = $request->payment_method_id;
        if (!$request->from || !$request->to) {
            $defaultFrom = now()->startOfMonth()->toDateString();
            $defaultTo = now()->toDateString();

            return redirect()->route('sales', array_merge($request->all(), [
                'from' => $request->from ?? $defaultFrom,
                'to' => $request->to ?? $defaultTo,
                'payment_method_id' => $payment_method_id ?? ''
            ]));
        }

        $paymentMethods = PaymentMethod::orderBy('name', 'asc')->select('id', 'name')->get();

        $query = Sale::with(['items.product', 'paymentMethod'])
            ->whereBetween('date', [$from, $to]);

        if ($payment_method_id) {
            $query->where('payment_method_id', $payment_method_id);
        }

        // Agregat seluruh rentang (bukan hanya halaman aktif).
        $allSales = (clone $query)->get();
        $labaTotal = 0;
        $total = 0;

        foreach ($allSales as $sale) {
            $labaTotal += $this->calculateLaba($sale);
            $total += $sale->total;
        }

        $sales = $query->withCount('items')
            ->withSum('items', 'subtotal')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        $sales->getCollection()->transform(function ($sale) {
            return [
                'id' => $sale->id,
                'customer' => $sale->customer,
                'customer_phone' => $sale->customer_phone,
                'date' => $sale->date,
                'total' => $sale->total,
                'discount' => $sale->discount,
                'laba' => $this->calculateLaba($sale),
                'items_count' => $sale->items_count,
                'payment_method' => $sale->paymentMethod?->name,
            ];
        });

        // Opsi dropdown untuk modal Tambah Penjualan.
        $products = Product::where('stock_cm', '>', 0)
            ->orderBy('name', 'asc')
            ->get([
                'id', 'name', 'price_agent', 'price_grosir', 'price_umum_roll',
                'price_grosir_meter', 'price_umum_meter',
            ]);
        $customers = Customer::orderBy('name', 'asc')->get(['name', 'phone']);

        return Inertia::render('Penjualan/Index', [
            'sales' => $sales,
            'paymentMethods' => $paymentMethods,
            'products' => $products,
            'customers' => $customers,
            'labaTotal' => $labaTotal,
            'total' => $total,
            'filters' => [
                'from' => $from,
                'to' => $to,
                'payment_method_id' => $payment_method_id ?? '',
            ],
        ]);
    }

    /** Port dari save() komponen Livewire SalesForm. */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer' => 'nullable',
            'customer_phone' => 'nullable|regex:/^08[0-9]{7,13}$/',
            'date' => 'required',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'discount' => 'nullable|numeric',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.jumlah' => 'required|numeric|gt:0',
            'items.*.price' => 'required|numeric|min:1',
            'items.*.price_type' => 'required',
        ], [
            'customer_phone.regex' => 'Nomor WA harus berformat 08xxxxxxxxx.',
        ]);

        $customer = $validated['customer'] ?? null;
        $customerPhone = $validated['customer_phone'] ?? null;
        $discount = (float) ($validated['discount'] ?? 0);

        // Simpan / perbarui database pelanggan (nomor WA sebagai identitas unik).
        if (!empty($customerPhone)) {
            Customer::updateOrCreate(
                ['phone' => $customerPhone],
                ['name' => $customer ?: null]
            );
        }

        // Hitung subtotal/total di server (mirror logika Livewire) — jangan percaya klien.
        $total = 0;
        foreach ($validated['items'] as $item) {
            $total += ceil((float) $item['jumlah'] * (float) $item['price']);
        }
        $total -= $discount;

        $sale = Sale::create([
            'payment_method_id' => $validated['payment_method_id'],
            'customer' => $customer ?: '-',
            'customer_phone' => $customerPhone ?: null,
            'discount' => $discount,
            'total' => $total,
            'date' => $validated['date'],
        ]);

        foreach ($validated['items'] as $item) {
            $product = Product::where('id', $item['product_id'])->first();
            $jumlah = (float) $item['jumlah'];
            $subtotal = ceil($jumlah * (float) $item['price']);

            SaleItems::create([
                'sale_id' => $sale->id,
                'product_id' => $item['product_id'],
                'price' => $item['price'],
                'price_type' => $item['price_type'],
                'quantity' => $jumlah,
                'subtotal' => $subtotal,
            ]);

            if ($product) {
                if (in_array($item['price_type'], $this->rollPriceTypes)) {
                    $product->stock_cm = ($product->stock_cm ?? 0) - $jumlah * $product->per_roll_cm;
                } else {
                    $product->stock_cm = ($product->stock_cm ?? 0) - $jumlah * 100;
                }
                $product->save();
            }
        }

        return redirect()->back()->with('success', 'Data berhasil disimpan!');
    }

    public function destroy($id)
    {
        $data = Sale::where('id', $id)->with('items')->first();
        foreach ($data->items as $item) {
            if ($item->product) {
                $product = Product::where('id', $item->product->id)->first();
                if ($product) {
                    $product->stock_cm = $product->stock_cm + $item->quantity;
                    $product->save();
                }
            }
        }

        Sale::where('id', $id)->delete();

        return redirect()->back()->with('success', 'Penjualan berhasil dihapus.');
    }

    /** Hitung laba satu penjualan (port logika blade/controller lama). */
    private function calculateLaba(Sale $sale): float
    {
        $laba = 0;
        foreach ($sale->items as $item) {
            if (!$item->product) {
                continue;
            }
            if (in_array($item->price_type, $this->rollPriceTypes)) {
                $laba += ($item->price - $item->product->price_kulak) * $item->quantity;
            } else {
                $kulakPerMeter = $item->product->price_kulak / $item->product->per_roll_cm * 100;
                $laba += ($item->price - $kulakPerMeter) * $item->quantity;
            }
        }

        return $laba;
    }
}
