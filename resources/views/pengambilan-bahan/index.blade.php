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
    function downloadPDF(el = 'to-print') {
        document.querySelectorAll('.no-print').forEach(el => el.style.display = 'none');
        const element = document.getElementById(el);
        html2pdf().from(element).save('Pengambilan Bahan.pdf').then(() => {
            document.querySelectorAll('.no-print').forEach(el => el.style.display = '');
        });
    }
</script>
@endsection

@php
    $total = 0;
@endphp

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
                    <th>Produk</th>
                    <th>Harga</th>
                    <th>Quantity (Roll)</th>
                    <th>Total</th>
                    <th>Tanggal Pengambilan</th>
                    <th class="no-print">Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($datas as $data)
                    @php
                        $total += $data->total;
                    @endphp
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$data->toko?->name ?? '-'}}</td>
                        <td>{{$data->product?->name ?? '-'}}</td>
                        <td>{{formatRupiah($data->price)}}</td>
                        <td>{{$data->quantity}} Roll</td>
                        <td>{{formatRupiah($data->total)}}</td>
                        <td>{{Carbon::parse($data->date)->locale('id')->translatedFormat('d F Y')}}</td>
                        <td class="no-print" style="width: 150px;">
                            <button class="btn btn-danger btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#deletedata{{ $data->id }}"><span class="menu-icon tf-icons bx bx-trash"></span> Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Belum ada data pengambilan bahan.</td>
                    </tr>
                @endforelse
                <tr style="background-color: #d8f3dc; color: white;">
                    <td colspan="3"><strong>Grand Total:</strong></td>
                    <td colspan="5"><strong>{{ formatRupiah($total) }}</strong></td>
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
@endforeach
<button type="button" style="bottom: 3rem; right: 2rem;" class="btn position-fixed btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#createdata"><span class="menu-icon tf-icons bx bx-plus-circle"></span> Tambah Pengambilan Bahan</button>
<div class="modal fade" id="createdata" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Tambah Pengambilan Bahan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    @csrf
                    <div class="row mt-4">
                        <div class="col mb-0">
                            <label class="form-label">Toko</label>
                            <select name="toko_id" class="form-control">
                                <option value="">Pilih Toko</option>
                                @foreach ($tokos as $toko)
                                    <option value="{{ $toko->id }}">{{ $toko->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col mb-0">
                            <label class="form-label">Produk</label>
                            <select name="product_id" class="form-control">
                                <option value="">Pilih Produk</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col mb-0">
                            <label class="form-label">Quantity (Roll)</label>
                            <input type="number" name="quantity" class="form-control" required placeholder="100">
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col mb-0">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="date" class="form-control" required placeholder="1000000">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
