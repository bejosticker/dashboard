<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Gaji;
use App\Models\Kulak;
use App\Models\OnlineAd;
use App\Models\OnlineIncome;
use App\Models\PengambilanBahan;
use App\Models\Pengeluaran;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Toko;
use App\Models\TokoIncome;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $tokos = Toko::orderBy('name', 'asc')->select('id', 'name')->get();
        foreach ($tokos as $toko) {
            $report = $this->tokoReport($toko->id);
            $toko->credit = $report['credit'];
            $toko->debit = $report['debit'];
            $toko->online = $report['online'];
        }
        $report = $this->report();

        $from = now()->startOfMonth()->toDateString();
        $to = now()->toDateString();

        $kulak = Kulak::whereBetween('date', [$from, $to])->sum('total');

        return view('content.dashboard.dashboards-analytics', compact('tokos', 'report', 'kulak'));
    }

    public function tokoReport($toko_id)
    {
        $from = now()->startOfMonth()->toDateString();
        $to = now()->toDateString();
        $reports = [];
        $results = [];
        $totalDebit = 0;
        $totalCredit = 0;

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

        $onlineReport = OnlineIncome::whereBetween('online_incomes.date', [$from, $to])
            ->join('online_markets', 'online_incomes.online_market_id', 'online_markets.id')
            ->where('online_markets.toko_id', $toko_id)
            ->sum('online_incomes.amount');

        $reports = array_merge($pengambilanBarang, $pemasukan);
        $results = collect($reports);
        $totalCredit = $results->where('type', 'credit')->sum('amount');
        $totalDebit = $results->where('type', 'debit')->sum('amount');

        return [
            'credit' => $totalCredit,
            'debit' => $totalDebit,
            'online' => $onlineReport
        ];
    }

    public function report()
    {
        $from = now()->startOfMonth()->toDateString();
        $to = now()->toDateString();
        $reports = [];
        $results = [];
        $totalDebit = 0;
        $totalCredit = 0;

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

        $pengeluaranLain = Pengeluaran::whereBetween('pengeluaran.date', [$from, $to])
            ->join('toko', 'pengeluaran.toko_id', 'toko.id')
            ->selectRaw(
                '"-" as description, pengeluaran.date, SUM(pengeluaran.amount) as amount, "debit" as type, "Pengeluaran Lain Toko" as source,CONCAT("Pengeluaran Lain ", toko.name) as name'
            )
            ->groupBy('toko.name')
            ->get()
            ->toArray();

        $reports = array_merge($kulak, $pengambilanBarang, $gaji, $pemasukan, $pengeluaranLain);
        $results = collect($reports);
        $totalCredit = $results->where('type', 'credit')->sum('amount');
        $totalDebit = $results->where('type', 'debit')->sum('amount');

        $products = Product::whereColumn('stock_cm', '<', 'minimum_stock_cm')->orderBy('name', 'asc')->get();

        return [
            'credit' => $totalCredit,
            'debit' => $totalDebit,
            'products' => $products
        ];
    }
}
