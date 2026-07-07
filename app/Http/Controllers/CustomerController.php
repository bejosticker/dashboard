<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search') ?? '';

        $customers = Customer::when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            })
            ->orderBy('name', 'asc')
            ->paginate(15)
            ->withQueryString();

        return view('customers.index', compact('customers', 'search'));
    }

    public function export()
    {
        $customers = Customer::orderBy('name', 'asc')->get();
        $filename = 'database-pelanggan-' . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($customers) {
            $out = fopen('php://output', 'w');
            // BOM UTF-8 agar Excel membaca karakter dengan benar
            fwrite($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['No', 'Nama', 'Nomor WA', 'Terdaftar']);
            foreach ($customers as $i => $c) {
                fputcsv($out, [
                    $i + 1,
                    $c->name ?: '-',
                    $c->phone,
                    optional($c->created_at)->format('Y-m-d H:i'),
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function destroy($id)
    {
        Customer::where('id', $id)->delete();
        return back()->with('success', 'Pelanggan berhasil dihapus!');
    }
}
