<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Toko;

class TokoController extends Controller
{
    public function index()
    {
        $tokos = Toko::all();
        return view('toko.index', compact('tokos'));
    }

    public function store(Request $request)
    {
        \Log::info($request->all());
        Toko::create([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type
        ]);

        return redirect()->back()->with('success', 'Toko berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        Toko::where('id', $id)->update([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type
        ]);

        return redirect()->back()->with('success', 'Toko berhasil disimpan.');
    }

    public function destroy(Request $request, $id)
    {
        $Toko = Toko::where('id', $id)->first();
        Toko::where('id', $id)->delete();

        return redirect()->back()->with('success', 'Toko '.$Toko->name.' berhasil dihapus.');
    }
}
