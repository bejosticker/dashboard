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

        $karyawans = Karyawan::orderBy('name', 'asc')->get();

        return view('gaji.index', compact('gajis', 'karyawans'));
    }

    public function history(Request $request)
    {
        $karyawan_id = $request->karyawan_id;

        if (!$request->karyawan_id) {
            $karyawan = Karyawan::first();
            return redirect()->route('gaji-history', array_merge($request->all(), [
                'karyawan_id' => $karyawan_id ?? ($karyawan ? $karyawan->id : '')
            ]));
        }

        $gajis = GajiItem::with(['karyawan', 'gaji'])
            ->where('karyawan_id', $karyawan_id)
            ->orderBy(
                Gaji::select('date')
                    ->whereColumn('gaji.id', 'gaji_items.gaji_id'),
                'desc'
            )
            ->paginate(10);

        $karyawans = Karyawan::orderBy('name', 'asc')->get();

        return view('gaji.history', compact('gajis', 'karyawans'));
    }

    public function store(Request $request)
    {
        $gaji = Gaji::create([
            'month' => $request->month,
            'year' => $request->year,
            'date' => $request->date
        ]);

        $ids = $request->input('karyawan_id');
        $gajis = $request->input('gaji');

        // $karyawans = Karyawan::orderBy('name', 'asc')->get();

        // foreach ($karyawans as $karyawan) {
        //     GajiItem::create([
        //         'gaji_id' => $gaji->id,
        //         'karyawan_id' => $karyawan->id,
        //         'amount' => $karyawan->gaji
        //     ]);
        // }

        for ($i=0; $i < count($ids); $i++) { 
            GajiItem::create([
                'gaji_id' => $gaji->id,
                'karyawan_id' => $ids[$i],
                'amount' => $gajis[$i]
            ]);

            Karyawan::where('id', $ids[$i])->update(['gaji' => $gajis[$i]]);
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
