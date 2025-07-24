<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Supplier;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::all();
        return view('supplier.index', [
            'suppliers' => $suppliers
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        Supplier::create([
            'name' => $request->name,
            'description' => $request->description ?? '-'
        ]);

        return redirect()->back()->with('success', 'Supplier berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        Supplier::where('id', $id)->update([
            'name' => $request->name,
            'description' => $request->description ?? '-'
        ]);

        return redirect()->back()->with('success', 'Supplier berhasil disimpan.');
    }

    public function destroy(Request $request, $id)
    {
        $supplier = Supplier::where('id', $id)->first();
        Supplier::where('id', $id)->delete();

        return redirect()->back()->with('success', 'Supplier '.$supplier->name.' berhasil dihapus.');
    }
}
