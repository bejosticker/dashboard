<?php

namespace App\Http\Controllers;
use App\Models\Toko;
use Illuminate\Http\Request;
use App\Models\Karyawan;

class KaryawanController extends Controller
{
    public function index()
    {
        $karyawans = Karyawan::orderBy('name', 'asc')->paginate(10);
        $tokos = Toko::orderBy('name', 'asc')->get();
        return view('karyawan.index', compact('karyawans', 'tokos'));
    }

    public function store(Request $request)
    {
        Karyawan::create([
            'name' => $request->name,
            'month' => $request->month,
            'year' => $request->year,
            'gaji' => $request->gaji,
            'toko_id' => $request->toko_id,
        ]);

        return redirect()->back()->with('success', 'Karyawan berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        Karyawan::where('id', $id)->update([
            'name' => $request->name,
            'month' => $request->month,
            'year' => $request->year,
            'gaji' => $request->gaji,
            'toko_id' => $request->toko_id,
        ]);

        return redirect()->back()->with('success', 'Karyawan berhasil disimpan.');
    }

    public function destroy(Request $request, $id)
    {
        $Karyawan = Karyawan::where('id', $id)->first();
        Karyawan::where('id', $id)->delete();

        return redirect()->back()->with('success', 'Karyawan '.$Karyawan->name.' berhasil dihapus.');
    }
}
