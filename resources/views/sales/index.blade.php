@php
    use Carbon\Carbon;
@endphp
@extends('layouts/contentNavbarLayout')

@section('title', 'Penjualan')

@section('content')
@include('layouts/sections/message')

@section('vendor-script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
@endsection

@section('page-script')
<script>
    const opt = {
        margin:       0,
        filename:     'dokumen.pdf',
        image:        { type: 'jpeg', quality: 1 },
        html2canvas:  { scale: 4 },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };

    function downloadPDF(el = 'to-print') {
        document.querySelectorAll('.no-print').forEach(el => el.style.display = 'none');
        const element = document.getElementById(el);
        html2pdf().set(opt).from(element).save('Penjualan.pdf').then(() => {
            document.querySelectorAll('.no-print').forEach(el => el.style.display = '');
        });
    }
</script>
@endsection

@php
    $total = 0;
@endphp

<div class="card p-4">
    <form class="row d-flex-row align-items-end" method="GET">
        <div class="col-md-3">
            <label class="form-label">Metode Pembayaran</label>
            <select name="payment_method_id" class="form-control">
                <option value="">Pilih Metode Pembayaran</option>
                @foreach ($paymentMethods as $payment_method)
                    <option value="{{ $payment_method->id }}" {{ ($_GET['payment_method_id'] ?? '') == $payment_method->id ? 'selected' : '' }}>{{ $payment_method->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Tanggal Awal:</label>
            <input type="date" name="from" class="form-control" value="{{ $_GET['from'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Tanggal Akhir:</label>
            <input type="date" name="to" class="form-control" value="{{ $_GET['to'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary"><span class="tf-icons bx bx-filter-alt"></span> Filter</button>
            <button type="button" class="btn btn-info" onclick="downloadPDF()"><span class="tf-icons bx bx-cloud-download"></span> Unduh PDF</button>
        </div>
    </form>
</div>
<div class="card mt-4">
    <div class="table-responsive text-nowrap">
        <table class="table" id="to-print">
            <thead>
                <tr>
                    <th>Nama Customer</th>
                    <th>Tanggal Penjualan</th>
                    <th>Total Nominal</th>
                    <th>Laba</th>
                    <th>Diskon</th>
                    <th>Total Produk</th>
                    <th>Metode Pembayaran</th>
                    <th class="no-print">Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($sales as $sale)
                    @php
                        $total += $sale->total;
                        $laba = 0;
                        foreach ($sale->items as $item) {
                            if (in_array($item->price_type, ['price_agent', 'price_grosir', 'price_umum_roll'])) {
                                $laba += ($item->price - $item->product->price_kulak) * $item->quantity;
                            }else{
                                $kulakPerMeter = $item->product->price_kulak / $item->product->per_roll_cm * 100;
                                $laba += ($item->price - $kulakPerMeter) * $item->quantity;
                            }
                        }
                    @endphp
                    <tr>
                        <td>{{$sale->customer == '' || $sale->customer == NULL ? '-' : $sale->customer}}</td>
                        <td>{{Carbon::parse($sale->date)->locale('id')->translatedFormat('d F Y')}}</td>
                        <td>{{formatRupiah($sale->total)}}</td>
                        <td>{{formatRupiah($laba)}}</td>
                        <td>{{formatRupiah($sale->discount)}}</td>
                        <td>{{count($sale->items)}} Produk</td>
                        <td>{{ $sale->paymentMethod?->name ?? '-' }}</td>
                        <td class="no-print" style="width: 150px;">
                            <button class="btn btn-success btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#detailsale{{ $sale->id }}"><span class="menu-icon tf-icons bx bx-info-circle"></span> Rincian</button>
                            <button class="btn btn-danger btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#deletesale{{ $sale->id }}"><span class="menu-icon tf-icons bx bx-trash"></span> Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Belum ada data penjualan.</td>
                    </tr>
                @endforelse
                <tr style="background-color: #d8f3dc; color: white;">
                    <td colspan="2"><strong>Grand Total:</strong></td>
                    <td colspan="6"><strong>{{ formatRupiah($total) }}</strong></td>
                </tr>
            </tbody>
        </table>
        <div style="padding:2rem;">
            {{ $sales->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
</div>
<button type="button" style="bottom: 3rem; right: 2rem;" class="btn position-fixed btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#createsale">
    <span class="menu-icon tf-icons bx bx-plus-circle"></span> Tambah Penjualan
</button>
@foreach ($sales as $sale)
<div class="modal fade" id="detailsale{{ $sale->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Rincian Penjualan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table" id="to-print{{ $sale->id }}">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama Produk</th>
                            <th>Harga</th>
                            <th class="no-print">Jenis Harga</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sale->items as $i => $item)
                            <tr>
                                <td>{{$loop->iteration}}</td>
                                <td>{{$item->product?->name ?? '-'}}</td>
                                <td>{{formatRupiah($item->price)}}</td>
                                <td class="no-print">{{convertPriceType($item->price_type)}}</td>
                                <td>{{$item->quantity}} {{ in_array($item['price_type'], ['price_agent', 'price_grosir', 'price_umum_roll']) ? 'Roll' : 'Meter' }}</td>
                                <td>{{formatRupiah($item->subtotal)}}</td>
                            </tr>
                        @endforeach
                        <tr style="background-color: #d8f3dc; color: white;">
                            <td class="no-print"></td>
                            <td colspan="4" class="text-end"><strong>Total Subtotal:</strong></td>
                            <td><strong>{{ formatRupiah($sale->items->sum('subtotal')) }}</strong></td>
                        </tr>
                        <tr style="background-color: #d8f3dc; color: white;">
                            <td class="no-print"></td>
                            <td colspan="4" class="text-end"><strong>Diskon:</strong></td>
                            <td><strong>{{ formatRupiah($sale->discount) }}</strong></td>
                        </tr>
                        <tr style="background-color: #d8f3dc; color: white;">
                            <td class="no-print"></td>
                            <td colspan="4" class="text-end"><strong>Total Subtotal:</strong></td>
                            <td><strong>{{ formatRupiah($sale->total) }}</strong></td>
                        </tr>
                        <tr style="background-color: #d8f3dc; color: white;">
                            <td class="no-print"></td>
                            <td colspan="4" class="text-end"><strong>Tanggal:</strong></td>
                            <td><strong>{{ Carbon::parse($sale->date)->locale('id')->translatedFormat('d F Y') }}</strong></td>
                        </tr>
                        <tr style="background-color: #d8f3dc; color: white;">
                            <td class="no-print"></td>
                            <td colspan="4" class="text-end"><strong>Metode Pembayaran:</strong></td>
                            <td><strong>{{ $sale->paymentMethod?->name ?? '-' }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="downloadPDF('to-print{{ $sale->id }}')"><span class="tf-icons bx bx-cloud-download"></span> Unduh Pdf</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="deletesale{{ $sale->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Hapus Penjualan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah anda yakin menghapus penjualan ini ?</p>
                <p>Stok produk terkait akan kembali</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                <a href="/sales/delete/{{ $sale->id }}" class="btn btn-primary">Hapus</a>
            </div>
        </div>
    </div>
</div>
@endforeach
<div class="modal fade" id="createsale" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Tambah Penjualan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @livewire('sales-form')
            </div>
        </div>
    </div>
</div>
@endsection
