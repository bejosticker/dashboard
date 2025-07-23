@php
    use Carbon\Carbon;
@endphp
@extends('layouts/contentNavbarLayout')

@section('title', 'Pembelian Bahan')

@section('content')
@include('layouts/sections/message')
<div class="card p-4">
    <form class="row d-flex-row align-items-end" method="GET">
        <div class="col-md-3">
            <label class="form-label">Supplier</label>
            <select name="supplier_id" class="form-control">
                <option value="">Pilih Supplier</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" {{ ($_GET['supplier_id'] ?? '') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
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
            <button type="submit" class="btn btn-primary">Filter Pengambilan Bahan</button>
        </div>
    </form>
</div>
<div class="card mt-4">
    <div class="table-responsive text-nowrap">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Supplier</th>
                    <th>Tanggal Pembelian</th>
                    <th>Total Nominal</th>
                    <th>Total Produk</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($kulaks as $pembelianBahan)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$pembelianBahan->supplier?->name ?? '-'}}</td>
                        <td>{{Carbon::parse($pembelianBahan->date)->locale('id')->translatedFormat('d F Y')}}</td>
                        <td>{{formatRupiah($pembelianBahan->total)}}</td>
                        <td>{{count($pembelianBahan->items)}} Produk</td>
                        <td>
                            <button class="btn btn-success btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#detailpembelianBahan{{ $pembelianBahan->id }}"><span class="menu-icon tf-icons bx bx-info-circle"></span> Rincian</button>
                            <button class="btn btn-danger btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#deletepembelianBahan{{ $pembelianBahan->id }}"><span class="menu-icon tf-icons bx bx-trash"></span> Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Belum ada data pembelian bahan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div style="padding:2rem;">
            {{ $kulaks->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
</div>
<button type="button" style="bottom: 3rem; right: 2rem;" class="btn position-fixed btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#createPembelianBahan">
    <span class="menu-icon tf-icons bx bx-plus-circle"></span> Tambah Pembelian Bahan
</button>
@foreach ($kulaks as $kulak)
<div class="modal fade" id="detailpembelianBahan{{ $kulak->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Rincian Pembelian Bahan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama Produk</th>
                            <th>Harga</th>
                            <th>Quantity (Roll)</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($kulak->items as $i => $item)
                            <tr>
                                <td>{{$loop->iteration}}</td>
                                <td>{{$item->product?->name ?? '-'}}</td>
                                <td>{{formatRupiah($item->price)}}</td>
                                <td>{{$item->rolls}}</td>
                                <td>{{formatRupiah($item->subtotal)}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Oke</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="deletekulak{{ $kulak->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Hapus kulak</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah anda yakin menghapus kulak ini ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                <a href="/kulak/delete/{{ $kulak->id }}" class="btn btn-primary">Hapus</a>
            </div>
        </div>
    </div>
</div>
@endforeach
<div class="modal fade" id="createPembelianBahan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Tambah Pembelian Bahan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @livewire('kulak-form')
            </div>
        </div>
    </div>
</div>
@endsection
