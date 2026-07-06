<?php

namespace App\Http\Controllers;
use App\Models\Toko;
use Illuminate\Http\Request;
use App\Models\Karyawan;
use Inertia\Inertia;

class KaryawanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search') ?? '';
        $karyawans = Karyawan::with('toko')
            ->where('name', 'like', "%{$search}%")
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->withQueryString();
        $tokos = Toko::orderBy('name', 'asc')->get(['id', 'name']);
        return Inertia::render('Karyawan/Index', [
            'karyawans' => $karyawans,
            'tokos' => $tokos,
            'filters' => ['search' => $search],
        ]);
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
