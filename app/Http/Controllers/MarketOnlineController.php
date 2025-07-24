<?php

namespace App\Http\Controllers;
use App\Models\Toko;
use Illuminate\Http\Request;
use App\Models\MarketOnline;

class MarketOnlineController extends Controller
{
    public function index()
    {
        $tokos = Toko::where('type', 'Online')->orderBy('name', 'asc')->get();
        $onlineTokos = MarketOnline::orderBy('name', 'asc')->with('toko')->get();

        return view('online-toko.index', compact('tokos', 'onlineTokos'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'toko_id' => 'required|exists:toko,id',
            'description' => 'nullable|string|max:1000',
            'vendor' => 'required|string|max:255',
        ]);

        $marketOnline = new MarketOnline();
        $marketOnline->name = $request->name;
        $marketOnline->toko_id = $request->toko_id;
        $marketOnline->description = $request->description ?? '-';
        $marketOnline->vendor = $request->vendor;
        $marketOnline->save();

        return redirect()->back()->with('success', 'Toko Market Online berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'toko_id' => 'required|exists:toko,id',
            'description' => 'nullable|string|max:1000',
            'vendor' => 'required|string|max:255',
        ]);

        $marketOnline = MarketOnline::findOrFail($id);
        $marketOnline->name = $request->name;
        $marketOnline->description = $request->description ?? '-';
        $marketOnline->vendor = $request->vendor;
        $marketOnline->save();

        return redirect()->back()->with('success', 'Toko Market Online berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $marketOnline = MarketOnline::findOrFail($id);
        $marketOnline->delete();

        return redirect()->back()->with('success', 'Toko Market Online berhasil dihapus.');
    }
}
