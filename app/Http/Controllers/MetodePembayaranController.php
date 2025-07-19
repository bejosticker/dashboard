<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;

class MetodePembayaranController extends Controller
{
    public function index()
    {
        $paymentMethods = PaymentMethod::all();
        return view('metode-pembayaran.index', compact('paymentMethods'));
    }

    public function store(Request $request)
    {
        try {
            $fileName = 'default.jpg';
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $fileName = time() . '-' . uniqid() . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('assets/img/payment-methods');
    
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
    
                $image->move($destinationPath, $fileName);
            }

            PaymentMethod::create([
                'name' => $request->input('name'),
                'image' => $fileName
            ]);

            return back()->with('success', 'Metode pembayaran berhasil disimpan!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menyimpan metode pembayaran: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $paymentMethod = PaymentMethod::where('id', $id)->first();
            $fileName = $paymentMethod->image;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $fileName = time() . '-' . uniqid() . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('assets/img/payment-methods');
    
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
    
                $image->move($destinationPath, $fileName);
            }

            PaymentMethod::where('id', $id)->update([
                'name' => $request->input('name'),
                'image' => $fileName,
            ]);

            return back()->with('success', 'Metode pembayaran berhasil disimpan!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menyimpan Metode pembayaran: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $paymentMethod = PaymentMethod::where('id', $id)->first();
        PaymentMethod::where('id', $id)->delete();
        return back()->with('success', 'Metode pembayaran '.$paymentMethod->name.' berhasil dihapus!');
    }
}
