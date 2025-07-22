<?php

namespace App\Http\Controllers;

use App\Models\Gaji;
use App\Models\Kulak;
use App\Models\PengambilanBahan;
use App\Models\Pengeluaran;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Toko;
use App\Models\TokoIncome;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $tokos = Toko::orderBy('name', 'asc')->get();
        $from = $request->from;
        $to = $request->to;
        $reports = [];
        $results = [];
        $totalDebit = 0;
        $totalCredit = 0;

        if (!$request->from || !$request->to) {
            $defaultFrom = now()->startOfMonth()->toDateString();
            $defaultTo = now()->toDateString();

            return redirect()->route('reports', array_merge($request->all(), [
                'from' => $request->from ?? $defaultFrom,
                'to' => $request->to ?? $defaultTo,
            ]));
        }

        $kulak = Kulak::whereBetween('date', [$from, $to])
            ->selectRaw('"-" as name, "-" as description, date, total as amount, "debit" as type, "Pembelian Bahan" as source')
            ->get()
            ->toArray();

        $pengambilanBarang = PengambilanBahan::whereBetween('pengambilan_bahans.date', [$from, $to])
            ->leftJoin('toko', 'pengambilan_bahans.toko_id', 'toko.id')
            ->selectRaw(
                '"-" as description, pengambilan_bahans.date, pengambilan_bahans.total as amount, "credit" as type, "Pengambilan Bahan" as source,toko.name'
            )
            ->get()
            ->toArray();

        $gaji = Gaji::whereBetween('date', [$from, $to])
            ->join('gaji_items', 'gaji.id', 'gaji_items.gaji_id')
            // ->leftJoin('toko', 'gaji.toko_id', 'tokos.id')
            // ->selectRaw('CONCAT("")')
            ->selectRaw('"Gaji" as name, "-" as description, gaji.date, SUM(gaji_items.amount) as amount, "debit" as type, CONCAT("Gaji Periode ", gaji.month, " ", gaji.year) as source')
            ->groupBy('gaji.id', 'gaji.date')
            ->get()
            ->toArray();

        $pemasukan = Sale::whereBetween('date', [$from, $to])
            ->selectRaw('customer as name, "-" as description, date, total as amount, "credit" as type, "Penjualan" as source')
            ->get()
            ->toArray();

        $reports = array_merge($kulak, $pengambilanBarang, $gaji, $pemasukan);
        $results = collect($reports);
        $totalKredit = $results->where('type', 'credit')->sum('amount');
        $totalDebit = $results->where('type', 'debit')->sum('amount');
        return view('reports.index', compact('tokos', 'results', 'totalKredit', 'totalDebit'));
    }

    public function tokoReport(Request $request)
    {
        $tokos = Toko::orderBy('name', 'asc')->get();
        $from = $request->from;
        $to = $request->to;
        $toko_id = $request->toko_id;
        $reports = [];
        $results = [];
        $totalDebit = 0;
        $totalCredit = 0;

        if (!$request->from || !$request->to || !$toko_id) {
            $defaultToko = Toko::first();
            $defaultFrom = now()->startOfMonth()->toDateString();
            $defaultTo = now()->toDateString();

            return redirect()->route('toko-reports', array_merge($request->all(), [
                'from' => $request->from ?? $defaultFrom,
                'to' => $request->to ?? $defaultTo,
                'toko_id' => $defaultToko ? $defaultToko->id : ''
            ]));
        }

        $pengeluaran = Pengeluaran::where('toko_id', $toko_id)
            ->whereBetween('date', [$from, $to])
            ->selectRaw('name, description, date, amount, "debit" as type, "Pengeluaran Lain" as source')
            ->get()
            ->toArray();

        $pengambilanBarang = PengambilanBahan::where('toko_id', $toko_id)
            ->whereBetween('pengambilan_bahans.date', [$from, $to])
            ->leftJoin('products', 'pengambilan_bahans.product_id', 'products.id')
            ->selectRaw(
                '"-" as description, pengambilan_bahans.date, pengambilan_bahans.total as amount, "debit" as type, "Pengambilan Bahan" as source,pengambilan_bahans.product_id, products.name'
            )
            ->get()
            ->toArray();

        $pemasukan = TokoIncome::where('toko_id', $toko_id)
            ->whereBetween('date', [$from, $to])
            ->selectRaw('name, description, date, amount, "credit" as type, "Pemasukan Toko" as source')
            ->get()
            ->toArray();

        $reports = array_merge($pengeluaran, $pengambilanBarang, $pemasukan);
        $results = collect($reports);
        $totalKredit = $results->where('type', 'credit')->sum('amount');
        $totalDebit = $results->where('type', 'debit')->sum('amount');
        return view('reports.toko', compact('tokos', 'results', 'totalKredit', 'totalDebit'));
    }
}
