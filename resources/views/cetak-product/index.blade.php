@extends('layouts/contentNavbarLayout')

@section('title', 'Produk Cetak')

@section('content')
@include('layouts/sections/message')
<div class="card p-4 mb-4">
    <div class="row">
        <div class="col-md-4"></div>
        <div class="col-md-4"></div>
        <div class="col-md-4">
            <form action="" method="GET">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" value="{{ $_GET['search'] ?? '' }}" placeholder="Cari produk..." aria-describedby="button-addon2">
                    <button class="btn btn-primary" type="submit" id="button-addon2">Cari</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="card">
    <div class="table-responsive text-nowrap">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Produk Cetak</th>
                    <th>Harga Grosir</th>
                    <th>Harga Umum</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($products as $product)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><p>{{$product->name}}</p></td>
                        <td>{{formatRupiah($product->price_grosir)}}</td>
                        <td>{{formatRupiah($product->price_umum)}}</td>
                        <td>
                            <button class="btn btn-success btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#editproduct{{ $product->id }}"><span class="tf-icons bx bx-edit"></span></button>
                            <button class="btn btn-danger btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#deleteproduct{{ $product->id }}"><span class="tf-icons bx bx-trash"></span></button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Belum ada data produk.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@foreach ($products as $product)
<div class="modal fade" id="editproduct{{ $product->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Ubah produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/cetak-products/update/{{ $product->id }}" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="col mb-4">
                            <label class="form-label">Nama Produk</label>
                            <input type="text" name="name" class="form-control" value="{{ $product->name }}" required placeholder="Masukkan  nama...">
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label">Harga Grosir (per meter)</label>
                            <input type="number" name="price_grosir" value="{{ $product->price_grosir }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Harga Umum (per meter)</label>
                            <input type="number" name="price_umum" value="{{ $product->price_umum }}" class="form-control">
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
<div class="modal fade" id="deleteproduct{{ $product->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Hapus product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah anda yakin menghapus produk {{ $product->name }}?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                <a href="/cetak-products/delete/{{ $product->id }}" class="btn btn-primary">Hapus</a>
            </div>
        </div>
    </div>
</div>
@endforeach
<button type="button" style="bottom: 3rem; right: 2rem;" class="btn position-fixed btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#createproduct"><span class="menu-icon tf-icons bx bx-plus-circle"></span> Tambah Produk</button>
<div class="modal fade" id="createproduct" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Tambah produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="col mb-4">
                            <label class="form-label">Nama Produk</label>
                            <input type="text" name="name" class="form-control" required placeholder="Masukkan  nama...">
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label">Harga Grosir (per meter)</label>
                            <input type="number" name="price_grosir" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Harga Umum (per meter)</label>
                            <input type="number" name="price_umum" class="form-control">
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
