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
        Supplier::create([
            'name' => $request->name,
            'description' => $request->description
        ]);

        return redirect()->back()->with('success', 'Supplier berhasil disimpan.');
    }
}
