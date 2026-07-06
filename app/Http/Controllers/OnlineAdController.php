<?php

namespace App\Http\Controllers;

use App\Models\MarketOnline;
use App\Models\OnlineAd;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OnlineAdController extends Controller
{
    public function index(Request $request)
    {
        $online_market_id = $request->online_market_id;
        $from = $request->from;
        $to = $request->to;

        if (!$request->from || !$request->to) {
            $defaultFrom = now()->startOfMonth()->toDateString();
            $defaultTo = now()->toDateString();

            return redirect()->route('online-ads', array_merge($request->all(), [
                'from' => $request->from ?? $defaultFrom,
                'to' => $request->to ?? $defaultTo,
                'online_market_id' => $online_market_id ?? ''
            ]));
        }

        $adsQuery = OnlineAd::whereBetween('date', [$from, $to]);
        if ($online_market_id) {
            $adsQuery->where('online_market_id', $online_market_id);
        }

        $totalAd = (float) (clone $adsQuery)->sum('amount');

        $ads = $adsQuery->with('shop')
            ->orderBy('date', 'desc')
            ->paginate(10)
            ->withQueryString();

        $tokos = MarketOnline::orderBy('name', 'asc')->get(['id', 'name', 'vendor']);

        return Inertia::render('IklanOnline/Index', [
            'ads' => $ads,
            'tokos' => $tokos,
            'totalAd' => $totalAd,
            'filters' => [
                'online_market_id' => $online_market_id ?? '',
                'from' => $from,
                'to' => $to,
            ],
        ]);
    }

    public function store(Request $request)
    {
        // Port dari App\Livewire\IklanForm::save() — mendukung banyak baris toko sekaligus.
        $data = $request->validate([
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.online_market_id' => 'required|exists:online_markets,id',
            'items.*.amount' => 'required|numeric|gt:0',
        ]);

        foreach ($data['items'] as $item) {
            OnlineAd::create([
                'online_market_id' => $item['online_market_id'],
                'amount' => (float) $item['amount'],
                'date' => $data['date'],
            ]);
        }

        return redirect()->back()->with('success', 'Iklan berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'online_market_id' => 'required|exists:online_markets,id',
            'amount' => 'required|numeric|gt:0',
        ]);

        OnlineAd::where('id', $id)->update([
            'date' => $data['date'],
            'online_market_id' => $data['online_market_id'],
            'amount' => (float) $data['amount'],
        ]);

        return redirect()->back()->with('success', 'Iklan berhasil disimpan.');
    }

    public function destroy(Request $request, $id)
    {
        OnlineAd::where('id', $id)->delete();

        return redirect()->back()->with('success', 'Iklan berhasil dihapus.');
    }
}
