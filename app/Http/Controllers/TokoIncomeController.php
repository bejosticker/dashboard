<?php

namespace App\Http\Controllers;

use App\Models\Toko;
use App\Models\TokoIncome;
use Illuminate\Http\Request;

class TokoIncomeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search') ?? '';
        $incomes = TokoIncome::where('name', 'like', "%{$search}%")
            ->with('toko')
            ->paginate(10);

        $tokos = Toko::orderBy('name', 'asc')->get();

        return view('toko-income.index', compact('incomes', 'tokos'));
    }

    public function store(Request $request)
    {
        TokoIncome::create([
            'name' => $request->name,
            'description' => $request->description,
            'date' => $request->date,
            'toko_id' => $request->toko_id,
            'amount' => $request->amount,
        ]);

        return redirect()->back()->with('success', 'Pemasukan toko berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        TokoIncome::where('id', $id)->update([
            'name' => $request->name,
            'description' => $request->description,
            'date' => $request->date,
            'toko_id' => $request->toko_id,
            'amount' => $request->amount,
        ]);

        return redirect()->back()->with('success', 'Pemasukan berhasil disimpan.');
    }

    public function destroy(Request $request, $id)
    {
        TokoIncome::where('id', $id)->delete();

        return redirect()->back()->with('success', 'Pemasukan berhasil dihapus.');
    }
}
