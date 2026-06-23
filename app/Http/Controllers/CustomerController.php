<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search') ?? '';
        $customers = Customer::where('name', 'like', "%{$search}%")
            ->orWhere('phone', 'like', "%{$search}%")
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'phone' => 'required|regex:/^08[0-9]{7,13}$/|unique:customers,phone',
        ], [
            'phone.regex' => 'Nomor WA harus berformat 08xxxxxxxxx.',
            'phone.unique' => 'Nomor WA sudah terdaftar.',
        ]);

        Customer::create([
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
        ]);

        return back()->with('success', 'Pelanggan berhasil disimpan!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'phone' => 'required|regex:/^08[0-9]{7,13}$/|unique:customers,phone,' . $id,
        ], [
            'phone.regex' => 'Nomor WA harus berformat 08xxxxxxxxx.',
            'phone.unique' => 'Nomor WA sudah terdaftar.',
        ]);

        Customer::where('id', $id)->update([
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
        ]);

        return back()->with('success', 'Pelanggan berhasil diperbarui!');
    }

    public function destroy($id)
    {
        Customer::where('id', $id)->delete();
        return back()->with('success', 'Pelanggan berhasil dihapus!');
    }

    /**
     * Unduh seluruh data pelanggan sebagai file Excel (CSV — bisa langsung dibuka di Excel).
     */
    public function export(Request $request): StreamedResponse
    {
        $search = $request->get('search') ?? '';
        $customers = Customer::where('name', 'like', "%{$search}%")
            ->orWhere('phone', 'like', "%{$search}%")
            ->orderBy('name', 'asc')
            ->get();

        $fileName = 'Data Pelanggan ' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($customers) {
            $handle = fopen('php://output', 'w');
            // BOM agar karakter UTF-8 (mis. nama) tampil benar di Excel
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['No', 'Nama Pelanggan', 'Nomor WA', 'Tanggal Dibuat']);

            foreach ($customers as $i => $customer) {
                fputcsv($handle, [
                    $i + 1,
                    $customer->name ?? '-',
                    $customer->phone,
                    optional($customer->created_at)->format('d-m-Y'),
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
