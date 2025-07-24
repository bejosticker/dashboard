@extends('layouts/contentNavbarLayout')

@section('title', 'Produk')

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
                    <th>Nama</th>
                    <th>Stok</th>
                    <th>Kulak</th>
                    <th>Agen</th>
                    <th>Grosir</th>
                    <th>Ecer Roll</th>
                    <th>Ecer</th>
                    <th>Stok Minimal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($products as $product)
                    <tr @if($product->stock_cm < $product->minimum_stock_cm) style="background-color: #ffe5e5;" @endif>
                        <td>{{$loop->iteration + ((request('page', 1) - 1) * 10)}}</td>
                        <td>
                            <div class="rounded" style="width: 64px; height: 64px; border-radius: 8px; overflow: hidden;">
                                <img src="/assets/img/products/{{ $product->image }}"  class="w-100 h-100"/>
                            </div>
                            <p>{{$product->name}}</p>
                        </td>
                        <td>{{$product->stock_cm ? floor($product->stock_cm / $product->per_roll_cm) : 0}} Roll</td>
                        <td>{{formatRupiah($product->price_kulak)}}</td>
                        <td>{{formatRupiah($product->price_agent)}}</td>
                        <td>{{formatRupiah($product->price_grosir)}}</td>
                        <td>{{formatRupiah($product->price_ecer_roll)}}</td>
                        <td>{{formatRupiah($product->price_ecer)}}</td>
                        <td>{{$product->minimum_stock_cm/$product->per_roll_cm}} Roll</td>
                        <td>
                            <button class="btn btn-success btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#editproduct{{ $product->id }}"><span class="tf-icons bx bx-edit"></span></button>
                            <button class="btn btn-danger btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#deleteproduct{{ $product->id }}"><span class="tf-icons bx bx-trash"></span></button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center">Belum ada data produk.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div style="padding:2rem;">
            {{ $products->links('vendor.pagination.bootstrap-5') }}
        </div>
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
            <form action="/products/update/{{ $product->id }}" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="col mb-4">
                            <label class="form-label">Nama Produk</label>
                            <input type="text" value="{{ $product->name }}" name="name" class="form-control" required placeholder="Masukkan  nama...">
                        </div>
                    </div>
                    <div class="row g-4">
                        <div class="col mb-0">
                            <label class="form-label">Foto Produk</label>
                            <input class="form-control" type="file" name="image" accept="image/*">
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col">
                            <label class="form-label">Harga Kulak</label>
                            <input type="number" value="{{ $product->price_kulak }}" name="price_kulak" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-lg-6">
                            <label class="form-label">Harga Agen</label>
                            <input type="number" value="{{ $product->price_agent }}" name="price_agent" class="form-control" required>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">Harga Grosir</label>
                            <input type="number" value="{{ $product->price_grosir }}" name="price_grosir" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-lg-6">
                            <label class="form-label">Harga Ritel (per meter)</label>
                            <input type="number" value="{{ $product->price_ecer_roll }}" name="price_ecer_roll" class="form-control" required>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">Harga Ritel Max (per meter)</label>
                            <input type="number" value="{{ $product->price_ecer }}" name="price_ecer" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-lg-6">
                            <label class="form-label">Stok Minimal</label>
                            <div class="input-group input-group-merge">
                                <input type="number" value="{{ $product->minimum_stock_cm / $product->per_roll_cm / 100 }}" class="form-control" name="minimum_stock_cm"required placeholder="10">
                                <span class="input-group-text">Roll</span>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">Per Roll</label>
                             <div class="input-group input-group-merge">
                                <input type="number" class="form-control" value="{{ $product->per_roll_cm / 100 }}" name="per_roll_cm"required placeholder="10">
                                <span class="input-group-text">Meter</span>
                            </div>
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
                <a href="/products/delete/{{ $product->id }}" class="btn btn-primary">Hapus</a>
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
                    <div class="row g-4">
                        <div class="col mb-0">
                            <label class="form-label">Foto Produk</label>
                            <input class="form-control" type="file" name="image" accept="image/*">
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col">
                            <label class="form-label">Harga Kulak</label>
                            <input type="number" name="price_kulak" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-lg-6">
                            <label class="form-label">Harga Agen</label>
                            <input type="number" name="price_agent" class="form-control" required>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">Harga Grosir</label>
                            <input type="number" name="price_grosir" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-lg-6">
                            <label class="form-label">Harga Ritel (per meter)</label>
                            <input type="number" name="price_ecer_roll" class="form-control" required>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">Harga Ritel Max (per meter)</label>
                            <input type="number" name="price_ecer" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-lg-6">
                            <label class="form-label">Stok Minimal</label>
                            <div class="input-group input-group-merge">
                                <input type="number" class="form-control" name="minimum_stock_cm"required placeholder="10">
                                <span class="input-group-text">Roll</span>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">Per Roll</label>
                             <div class="input-group input-group-merge">
                                <input type="number" class="form-control" name="per_roll_cm"required placeholder="10">
                                <span class="input-group-text">Meter</span>
                            </div>
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
