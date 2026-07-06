<?php

namespace App\Http\Controllers;

use App\Models\CetakProduct;
use App\Models\CetakProductSale;
use App\Models\CetakProductSaleItem;
use App\Models\Customer;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CetakSalesController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->from;
        $to = $request->to;
        if (!$request->from || !$request->to) {
            $defaultFrom = now()->startOfMonth()->toDateString();
            $defaultTo = now()->toDateString();

            return redirect()->route('cetak-sales', array_merge($request->all(), [
                'from' => $request->from ?? $defaultFrom,
                'to' => $request->to ?? $defaultTo,
            ]));
        }

        $sales = CetakProductSale::with(['items.product', 'paymentMethod'])
            ->whereBetween('date', [$from, $to])
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        $sales->through(function ($sale) {
            $laba = 0;
            foreach ($sale->items as $item) {
                $laba += cetakItemLaba($item);
            }

            return [
                'id' => $sale->id,
                'customer' => $sale->customer ?: '-',
                'customer_phone' => $sale->customer_phone ?: '-',
                'date' => $sale->date,
                'total' => $sale->total,
                'laba' => $laba,
                'payment_method' => $sale->paymentMethod->name ?? '-',
                'items_count' => $sale->items->count(),
                'items' => $sale->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product' => $item->product?->name ?? '-',
                    'price' => $item->price,
                    'price_type' => convertPriceType($item->price_type),
                    'qty_label' => cetakItemQtyLabel($item),
                    'subtotal' => $item->subtotal,
                ])->values(),
            ];
        });

        $allSales = CetakProductSale::with(['items.product'])
            ->whereBetween('date', [$from, $to])
            ->get();

        $allLaba = 0;
        $allTotal = 0;

        foreach ($allSales as $sale) {
            $laba = 0;
            foreach ($sale->items as $item) {
                $laba += cetakItemLaba($item);
            }
            $allLaba += $laba;
            $allTotal += $sale->total;
        }

        return Inertia::render('PenjualanCetak/Index', [
            'sales' => $sales,
            'products' => CetakProduct::orderBy('name', 'asc')
                ->get(['id', 'name', 'price_grosir', 'price_umum', 'price_eceran_grosir', 'price_eceran_umum']),
            'paymentMethods' => PaymentMethod::orderBy('name', 'asc')->get(['id', 'name']),
            'customers' => Customer::orderBy('name', 'asc')->get(['name', 'phone']),
            'allLaba' => $allLaba,
            'allTotal' => $allTotal,
            'filters' => ['from' => $from, 'to' => $to],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer' => 'nullable',
            'customer_phone' => 'nullable|regex:/^08[0-9]{7,13}$/',
            'date' => 'required',
            'discount' => 'nullable|numeric',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:cetak_products,id',
            'items.*.quantity' => 'required|numeric|gt:0',
            'items.*.price' => 'required|numeric|min:1',
            'items.*.price_type' => 'required',
        ], [
            'customer_phone.regex' => 'Nomor WA harus berformat 08xxxxxxxxx.',
        ]);

        $discount = (float) ($request->discount ?? 0);
        $items = $request->items;

        $subtotalSum = 0;
        foreach ($items as $item) {
            $subtotalSum += ceil(((float) $item['quantity']) * ((float) $item['price']));
        }
        $total = $subtotalSum - $discount;

        // Simpan / perbarui database pelanggan (nomor WA sebagai identitas unik)
        if (!empty($request->customer_phone)) {
            Customer::updateOrCreate(
                ['phone' => $request->customer_phone],
                ['name' => $request->customer ?: null]
            );
        }

        $sale = CetakProductSale::create([
            'customer' => $request->customer ?: '-',
            'customer_phone' => $request->customer_phone ?: null,
            'discount' => $discount,
            'total' => $total,
            'payment_method_id' => $request->payment_method_id,
            'date' => $request->date,
        ]);

        foreach ($items as $item) {
            // Satuan otomatis mengikuti jenis harga: per lembar (eceran) atau per cm
            $unit = in_array($item['price_type'], ['price_eceran_grosir', 'price_eceran_umum']) ? 'lembar' : 'cm';
            $subtotal = ceil(((float) $item['quantity']) * ((float) $item['price']));

            CetakProductSaleItem::create([
                'cetak_product_sale_id' => $sale->id,
                'cetak_product_id' => $item['product_id'],
                'price' => $item['price'],
                'price_type' => $item['price_type'],
                'quantity' => $item['quantity'],
                'unit' => $unit,
                'subtotal' => $subtotal,
            ]);

            // Kurangi stok bahan (dalam cm) hanya untuk penjualan per cm
            if ($unit === 'cm') {
                $product = CetakProduct::find($item['product_id']);
                if ($product) {
                    $product->stock = max(0, ($product->stock ?? 0) - $item['quantity']);
                    $product->save();
                }
            }
        }

        return redirect()->back()->with('success', 'Data berhasil disimpan!');
    }

    public function destroy($id)
    {
        CetakProductSale::where('id', $id)->delete();

        return redirect()->back()->with('success', 'Penjualan berhasil dihapus.');
    }
}
