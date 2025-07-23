<?php

namespace App\Http\Controllers;

use App\Models\MarketOnline;
use App\Models\OnlineIncome;
use Illuminate\Http\Request;

class OnlineIncomeController extends Controller
{
    public function index(Request $request)
    {
        $online_market_id = $request->online_market_id;
        $from = $request->from;
        $to = $request->to;

        if (!$request->from || !$request->to) {
            $defaultFrom = now()->startOfMonth()->toDateString();
            $defaultTo = now()->toDateString();

            return redirect()->route('online-incomes', array_merge($request->all(), [
                'from' => $request->from ?? $defaultFrom,
                'to' => $request->to ?? $defaultTo,
                'online_market_id' => $online_market_id ?? ''
            ]));
        }

        $incomes = OnlineIncome::whereBetween('date', [$from, $to]);
        if ($online_market_id) {
            $incomes = $incomes->where('online_market_id', $online_market_id);
        }
        
        $incomes = $incomes->with('shop')->paginate(10);

        $tokos = MarketOnline::orderBy('name', 'asc')->get();

        return view('online-incomes.index', compact('incomes', 'tokos'));
    }

    public function store(Request $request)
    {
        OnlineIncome::create([
            'date' => $request->date,
            'online_market_id' => $request->online_market_id,
            'amount' => $request->amount,
        ]);

        return redirect()->back()->with('success', 'Pemasukan berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        OnlineIncome::where('id', $id)->update([
            'date' => $request->date,
            'online_market_id' => $request->online_market_id,
            'amount' => $request->amount,
        ]);

        return redirect()->back()->with('success', 'Pemasukan berhasil disimpan.');
    }

    public function destroy(Request $request, $id)
    {
        OnlineIncome::where('id', $id)->delete();

        return redirect()->back()->with('success', 'Pemasukan berhasil dihapus.');
    }
}
