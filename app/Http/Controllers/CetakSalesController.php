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

        $sales = CetakProductSale::with(['items.product'])
            ->whereBetween('date', [$from, $to])
            ->withCount('items')
            ->withSum('items', 'subtotal')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('cetak-sales.index', compact('sales'));
    }

    public function destroy($id)
    {
        CetakProductSale::where('id', $id)->delete();

        return redirect()->back()->with('success', 'Penjualan berhasil dihapus.');
    }
}
