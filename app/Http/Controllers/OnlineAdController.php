<?php

namespace App\Http\Controllers;

use App\Models\MarketOnline;
use App\Models\OnlineAd;
use Illuminate\Http\Request;

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

        $ads = OnlineAd::whereBetween('date', [$from, $to]);
        if ($online_market_id) {
            $ads = $ads->where('online_market_id', $online_market_id);
        }
        
        $ads = $ads->with('shop')->paginate(10)->withQueryString();

        $tokos = MarketOnline::orderBy('name', 'asc')->get();

        return view('online-ads.index', compact('ads', 'tokos'));
    }

    public function store(Request $request)
    {
        OnlineAd::create([
            'date' => $request->date,
            'online_market_id' => $request->online_market_id,
            'amount' => $request->amount,
        ]);

        return redirect()->back()->with('success', 'Iklan berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        OnlineAd::where('id', $id)->update([
            'date' => $request->date,
            'online_market_id' => $request->online_market_id,
            'amount' => $request->amount,
        ]);

        return redirect()->back()->with('success', 'Iklan berhasil disimpan.');
    }

    public function destroy(Request $request, $id)
    {
        OnlineAd::where('id', $id)->delete();

        return redirect()->back()->with('success', 'Iklan berhasil dihapus.');
    }
}
