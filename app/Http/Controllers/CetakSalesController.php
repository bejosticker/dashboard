<?php

namespace App\Http\Controllers;

use App\Models\CetakProductSale;
use Illuminate\Http\Request;

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
            ->withCount('items')
            ->withSum('items', 'subtotal')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        $allSales = CetakProductSale::with(['items.product'])
            ->whereBetween('date', [$from, $to])
            ->get();

        $allLaba = 0;
        $allTotal = 0;

        foreach ($allSales as $sale) {
            $laba = 0;
            foreach ($sale->items as $item) {
                foreach ($sale->items as $item) {
                    $laba += ($item->panjang * $item->lebar) * ($item->price - $item->product->kulak_price);
                }
            }
            $allLaba += $laba;
            $allTotal += $sale->total;
        }

        return view('cetak-sales.index', compact('sales', 'allLaba', 'allTotal'));
    }

    public function destroy($id)
    {
        CetakProductSale::where('id', $id)->delete();

        return redirect()->back()->with('success', 'Penjualan berhasil dihapus.');
    }
}
