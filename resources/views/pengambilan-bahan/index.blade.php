@php
use Carbon\Carbon;
@endphp
@extends('layouts/contentNavbarLayout')

@section('title', 'Pengambilan Bahan')

@section('vendor-script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
@endsection

@section('page-script')
<script>
    const opt = {
        margin:       0,
        filename:     'dokumen.pdf',
        image:        { type: 'jpeg', quality: 1 },
        html2canvas:  { scale: 2 },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };

    function downloadPDF(el = 'to-print') {
        document.querySelectorAll('.no-print').forEach(el => el.style.display = 'none');
        const element = document.getElementById(el);
        html2pdf().set(opt).from(element).save('Pengambilan Bahan.pdf').then(() => {
            document.querySelectorAll('.no-print').forEach(el => el.style.display = '');
        });
    }
</script>
@endsection

@section('content')
@include('layouts/sections/message')
<div class="card p-4">
    <form class="row d-flex-row align-items-end" method="GET">
        <div class="col-md-3">
            <label class="form-label">Toko</label>
            <select name="toko_id" class="form-control">
                <option value="">Pilih Toko</option>
                @foreach ($tokos as $toko)
                    <option value="{{ $toko->id }}" {{ ($_GET['toko_id'] ?? '') == $toko->id ? 'selected' : '' }}>{{ $toko->name }}</option>
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
                    <th>#</th>
                    <th>Toko</th>
                    <th>Total</th>
                    <th>Laba</th>
                    <th>Tanggal Pengambilan</th>
                    <th class="no-print">Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($datas as $data)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$data->toko?->name ?? '-'}}</td>
                        <td>{{formatRupiah($data->total)}}</td>
                        <td>{{formatRupiah($data->laba)}}</td>
                        <td>{{Carbon::parse($data->date)->locale('id')->translatedFormat('d F Y')}}</td>
                        <td class="no-print" style="width: 150px;">
                            <button class="btn btn-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#detaildata{{ $data->id }}"><span class="menu-icon tf-icons bx bx-edit"></span> Rincian</button>
                            <button class="btn btn-danger btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#deletedata{{ $data->id }}"><span class="menu-icon tf-icons bx bx-trash"></span> Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Belum ada data pengambilan bahan.</td>
                    </tr>
                @endforelse
                <tr style="background-color: #d8f3dc; color: white;">
                    <td colspan="2"><strong>Grand Total:</strong></td>
                    <td><strong>{{ formatRupiah($total) }}</strong></td>
                    <td colspan="5"><strong>{{ formatRupiah($laba) }}</strong></td>
                </tr>
            </tbody>
        </table>
        <div style="padding:2rem;">
            {{ $datas->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
</div>
@foreach ($datas as $data)
<div class="modal fade" id="deletedata{{ $data->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Hapus data pengambilan bahan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah anda yakin menghapus data ini?</p>
                <p>Stok akan kembali untuk produk terkait</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                <a href="/pengambilan-bahan/delete/{{ $data->id }}" class="btn btn-primary">Hapus</a>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="detaildata{{ $data->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Rincian Pengambilan Bahan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table" id="to-print{{ $data->id }}">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama Produk</th>
                            <th>Harga</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data->items as $i => $item)
                            <tr>
                                <td>{{$loop->iteration}}</td>
                                <td>{{$item->product?->name ?? '-'}}</td>
                                <td>{{formatRupiah($item->price)}}</td>
                                <td>{{$item->quantity}} {{ $item->product_type }}</td>
                                <td>{{formatRupiah($item->subtotal)}}</td>
                            </tr>
                        @endforeach
                        <tr style="background-color: #d8f3dc; color: white;">
                            <td colspan="4" class="text-end"><strong>Total:</strong></td>
                            <td><strong>{{ formatRupiah($data->total) }}</strong></td>
                        </tr>
                        <tr style="background-color: #d8f3dc; color: white;">
                            <td colspan="4" class="text-end"><strong>Tanggal:</strong></td>
                            <td><strong>{{ Carbon::parse($data->date)->locale('id')->translatedFormat('d F Y') }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="downloadPDF('to-print{{ $data->id }}')"><span class="tf-icons bx bx-cloud-download"></span> Unduh Pdf</button>
            </div>
        </div>
    </div>
</div>
@endforeach
<button type="button" style="bottom: 3rem; right: 2rem;" class="btn position-fixed btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#createdata"><span class="menu-icon tf-icons bx bx-plus-circle"></span> Tambah Pengambilan Bahan</button>
<div class="modal fade" id="createdata" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Tambah Pengambilan Bahan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    @livewire('pengambilan-bahan-form')
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
