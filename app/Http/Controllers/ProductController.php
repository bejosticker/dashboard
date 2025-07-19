<?php

namespace App\Http\Controllers;
use App\Models\Product;
use Illuminate\Http\Request;


class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search') ?? '';
        $products = Product::where('name', 'like', "%{$search}%")
            ->orderByRaw('stock_cm < minimum_stock_cm DESC')
            ->orderBy('name', 'asc')
            ->paginate(10);

        return view('product.index', compact('products'));
    }

    public function store(Request $request)
    {
        try {
            $fileName = 'default.png';
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $fileName = time() . '-' . uniqid() . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('assets/img/products');
    
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
    
                $image->move($destinationPath, $fileName);
            }

            Product::create([
                'name' => $request->input('name'),
                'image' => $fileName,
                'price_agent' => $request->input('price_agent'),
                'price_grosir' => $request->input('price_grosir'),
                'price_ecer_roll' => $request->input('price_ecer_roll'),
                'price_ecer' => $request->input('price_ecer'),
                'price_kulak' => $request->input('price_kulak'),
                'stock_cm' => $request->input('stock_cm'),
                'per_roll_cm' => $request->input('per_roll_cm'),
                'minimum_stock_cm' => $request->input('minimum_stock_cm')
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
            $product = Product::where('id', $id)->first();
            $fileName = $product->image;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $fileName = time() . '-' . uniqid() . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('assets/img/products');
    
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
    
                $image->move($destinationPath, $fileName);
            }

            Product::where('id', $id)->update([
                'name' => $request->input('name'),
                'image' => $fileName,
                'price_agent' => $request->input('price_agent'),
                'price_grosir' => $request->input('price_grosir'),
                'price_ecer_roll' => $request->input('price_ecer_roll'),
                'price_ecer' => $request->input('price_ecer'),
                'price_kulak' => $request->input('price_kulak'),
                'stock_cm' => $request->input('stock_cm'),
                'per_roll_cm' => $request->input('per_roll_cm'),
                'minimum_stock_cm' => $request->input('minimum_stock_cm')
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
        $product = Product::where('id', $id)->first();
        Product::where('id', $id)->delete();
        return back()->with('success', 'Produk '.$product->name.' berhasil dihapus!');
    }

}
