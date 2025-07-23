<?php

namespace App\Http\Controllers;

use App\Models\Gaji;
use App\Models\Kulak;
use App\Models\MarketOnline;
use App\Models\OnlineAd;
use App\Models\OnlineIncome;
use App\Models\PengambilanBahan;
use App\Models\Pengeluaran;
use App\Models\Sale;
use App\Models\Toko;
use App\Models\TokoIncome;
use Illuminate\Http\Request;
use DB;

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
            ->selectRaw('"Gaji" as name, "-" as description, gaji.date, SUM(gaji_items.amount) as amount, "debit" as type, CONCAT("Gaji Periode ", gaji.month, " ", gaji.year) as source')
            ->groupBy('gaji.id', 'gaji.date')
            ->get()
            ->toArray();

        $pemasukan = Sale::whereBetween('date', [$from, $to])
            ->selectRaw('customer as name, "-" as description, date, total as amount, "credit" as type, "Penjualan" as source')
            ->get()
            ->toArray();

        $pemasukanOnline = OnlineIncome::whereBetween('online_incomes.date', [$from, $to])
            ->leftJoin('online_markets', 'online_incomes.online_market_id', 'online_markets.id')
            ->selectRaw('CONCAT(online_markets.name, " - ", online_markets.vendor) as name, "-" as description, online_incomes.amount, online_incomes.date, "Pemasukan Market Online" as source, "credit" as type')
            ->get()
            ->toArray();

        $iklanOnline = OnlineAd::whereBetween('online_ads.date', [$from, $to])
            ->leftJoin('online_markets', 'online_ads.online_market_id', 'online_markets.id')
            ->selectRaw('CONCAT("Iklan ",online_markets.name, " - ", online_markets.vendor) as name, "-" as description, online_ads.amount, online_ads.date, "Iklan Market Online" as source, "debit" as type')
            ->get()
            ->toArray();

        $reports = array_merge($kulak, $pengambilanBarang, $gaji, $pemasukan, $pemasukanOnline, $iklanOnline);
        $results = collect($reports)->sortByDesc('date');
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

    public function onlineReport(Request $request)
    {
        $tokos = Toko::orderBy('name', 'asc')->get();
        $from = $request->from;
        $to = $request->to;

        if (!$request->from || !$request->to) {
            $defaultFrom = now()->startOfMonth()->toDateString();
            $defaultTo = now()->toDateString();

            return redirect()->route('online-reports', array_merge($request->all(), [
                'from' => $request->from ?? $defaultFrom,
                'to' => $request->to ?? $defaultTo,
            ]));
        }

        $onlineNames = MarketOnline::groupBy('name')->select('name')->orderBy('name', 'asc')->get();
        foreach ($onlineNames as $name) {
            $vendors = DB::table('online_markets')
                ->where('online_markets.name', $name->name)
                ->get();

            foreach ($vendors as $vendor) {
                $incomes = DB::table('online_markets')
                    ->where('online_markets.name', $name->name)
                    ->where('online_markets.vendor', $vendor->vendor)
                    ->join('online_incomes', 'online_markets.id', 'online_incomes.online_market_id')
                    ->selectRaw('online_incomes.*, "credit" as type')
                    ->orderBy('online_incomes.date', 'desc')
                    ->get();

                $ads = DB::table('online_markets')
                    ->where('online_markets.name', $name->name)
                    ->where('online_markets.vendor', $vendor->vendor)
                    ->join('online_ads', 'online_markets.id', 'online_ads.online_market_id')
                    ->selectRaw('online_ads.*, "debit" as type')
                    ->orderBy('online_ads.date', 'desc')
                    ->get();

                $vendor->reports = $incomes->merge($ads)->sortByDesc('date')->values();
            }

            $name->vendors = $vendors;
        }
        
        $reports = $onlineNames;
        // return $reports;
        return view('reports.online', compact('reports'));
    }
}
