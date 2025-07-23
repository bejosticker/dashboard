<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\MarketOnline;

class MarketOnlineController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'vendor' => 'required|string|max:255',
        ]);

        $marketOnline = new MarketOnline();
        $marketOnline->name = $request->name;
        $marketOnline->description = $request->description;
        $marketOnline->vendor = $request->vendor;
        $marketOnline->save();

        return redirect()->back()->with('success', 'Toko Market Online berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'vendor' => 'required|string|max:255',
        ]);

        $marketOnline = MarketOnline::findOrFail($id);
        $marketOnline->name = $request->name;
        $marketOnline->description = $request->description;
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
