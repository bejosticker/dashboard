<?php

namespace App\Http\Controllers;

use App\Models\Gaji;
use App\Models\GajiItem;
use App\Models\Karyawan;
use Illuminate\Http\Request;

class GajiController extends Controller
{
    public function index()
    {
        $gajis = Gaji::with('items.karyawan')
            ->withCount('items')
            ->withSum('items', 'amount')
            ->orderBy('date', 'desc')
            ->paginate(10);

        return view('gaji.index', compact('gajis'));
    }

    public function store(Request $request)
    {
        $gaji = Gaji::create([
            'month' => $request->month,
            'year' => $request->year,
            'date' => $request->date
        ]);

        $karyawans = Karyawan::orderBy('name', 'asc')->get();

        foreach ($karyawans as $karyawan) {
            GajiItem::create([
                'gaji_id' => $gaji->id,
                'karyawan_id' => $karyawan->id,
                'amount' => $karyawan->gaji
            ]);
        }

        return redirect()->back()->with('success', 'Gaji berhasil disimpan.');
    }

    public function detail($id)
    {
        $gaji = Gaji::where('id', $id)
            ->with('items.karyawan')
            ->withCount('items')
            ->withSum('items', 'amount')
            ->first();

        return view('gaji.detail', compact('gaji'));
    }

    public function destroy(Request $request, $id)
    {
        Gaji::where('id', $id)->delete();

        return redirect()->back()->with('success', 'Gaji berhasil dihapus.');
    }
}
