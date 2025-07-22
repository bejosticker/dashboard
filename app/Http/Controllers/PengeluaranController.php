<?php

namespace App\Http\Controllers;

use App\Models\Pengeluaran;
use App\Models\Toko;
use Illuminate\Http\Request;

class PengeluaranController extends Controller
{
    public function index(Request $request)
    {
        $toko_id = $request->toko_id;
        $from = $request->from;
        $to = $request->to;

        if (!$request->from || !$request->to) {
            $defaultFrom = now()->startOfMonth()->toDateString();
            $defaultTo = now()->toDateString();

            return redirect()->route('pengeluaran', array_merge($request->all(), [
                'from' => $request->from ?? $defaultFrom,
                'to' => $request->to ?? $defaultTo,
                'toko_id' => $toko_id ?? ''
            ]));
        }

        $pengeluarans = Pengeluaran::whereBetween('date', [$from, $to]);
        if ($toko_id) {
            $pengeluarans = $pengeluarans->where('toko_id', $toko_id);
        }
        
        $pengeluarans = $pengeluarans->with('toko')
            ->paginate(10);

        $tokos = Toko::orderBy('name', 'asc')->get();

        return view('pengeluaran.index', compact('pengeluarans', 'tokos'));
    }

    public function store(Request $request)
    {
        Pengeluaran::create([
            'name' => $request->name,
            'description' => $request->description,
            'date' => $request->date,
            'toko_id' => $request->toko_id,
            'amount' => $request->amount,
        ]);

        return redirect()->back()->with('success', 'Pengeluaran berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        Pengeluaran::where('id', $id)->update([
            'name' => $request->name,
            'description' => $request->description,
            'date' => $request->date,
            'toko_id' => $request->toko_id,
            'amount' => $request->amount,
        ]);

        return redirect()->back()->with('success', 'Pengeluaran berhasil disimpan.');
    }

    public function destroy(Request $request, $id)
    {
        Pengeluaran::where('id', $id)->delete();

        return redirect()->back()->with('success', 'Pengeluaran berhasil dihapus.');
    }
}
