<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;

class SalesController extends Controller
{
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
        $sales = Sale::with(['items.product', 'paymentMethod'])
            ->whereBetween('date', [$from, $to]);

        if ($payment_method_id) {
            $sales = $sales->where('payment_method_id', $payment_method_id);
        }

        $allSales = $sales->get();
        $labaTotal = 0;

        foreach ($allSales as $sale) {
            $laba = 0;
            foreach ($sale->items as $item) {
                if (in_array($item->price_type, ['price_agent', 'price_grosir', 'price_umum_roll'])) {
                    $laba += ($item->price - $item->product->price_kulak) * $item->quantity;
                }else{
                    $kulakPerMeter = $item->product->price_kulak / $item->product->per_roll_cm * 100;
                    $laba += ($item->price - $kulakPerMeter) * $item->quantity;
                }
            }
            $labaTotal += $laba;
        }

        $sales = $sales->withCount('items')
            ->withSum('items', 'subtotal')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('sales.index', compact('sales', 'paymentMethods', 'labaTotal'));
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
}
