<?php

namespace App\Http\Controllers;

use App\Models\CetakProduct;
use Illuminate\Http\Request;

class CetakProductController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search') ?? '';
        $products = CetakProduct::where('name', 'like', "%{$search}%")
            ->orderBy('name', 'asc')
            ->get();

        return view('cetak-product.index', compact('products'));
    }

    public function store(Request $request)
    {
        try {
            CetakProduct::create([
                'name' => $request->input('name'),
                'price_grosir' => $request->input('price_grosir',0),
                'price_umum' => $request->input('price_umum',0)
            ]);

            return back()->with('success', 'Produk berhasil disimpan!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menyimpan produk: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            CetakProduct::where('id', $id)->update([
                'name' => $request->input('name'),
                'price_grosir' => $request->input('price_grosir',0),
                'price_umum' => $request->input('price_umum',0)
            ]);

            return back()->with('success', 'Produk berhasil disimpan!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menyimpan produk: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $product = CetakProduct::where('id', $id)->first();
        CetakProduct::where('id', $id)->delete();
        return back()->with('success', 'Produk '.$product->name.' berhasil dihapus!');
    }
}
