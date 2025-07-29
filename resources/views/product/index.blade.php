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
@php
    $totalKulak = 0;
    $totalAgen = 0;
    $totalGrosir = 0;
    $totalRollUmum = 0;
    $totalMeteranGrosir = 0;
    $totalMeteranUmum = 0;
@endphp
<div class="card">
    <div class="table-responsive text-nowrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Stok</th>
                    <th>Kulak</th>
                    <th>Agen</th>
                    <th>Grosir</th>
                    <th>Roll Umum</th>
                    <th>Meteran Grosir</th>
                    <th>Meteran Umum</th>
                    <th>Stok Minimal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($products as $product)
                    <tr @if($product->stock_cm < $product->minimum_stock_cm) style="background-color: #ffe5e5;" @endif>
                        <td>
                            <div class="rounded" style="width: 64px; height: 64px; border-radius: 8px; overflow: hidden;">
                                <img src="/assets/img/products/{{ $product->image }}"  class="w-100 h-100"/>
                            </div>
                            <p>{{$product->name}}</p>
                        </td>
                        @php
                            $stockCm = $product->stock_cm;
                            $rollCm = $product->per_roll_cm;
                            $meterCm = 100;

                            // Cek agar tidak terjadi division by zero
                            $roll = ($rollCm > 0) ? intdiv($stockCm, $rollCm) : 0;
                            $sisaCm = ($rollCm > 0) ? ($stockCm % $rollCm) : $stockCm;

                            $meter = ($meterCm > 0) ? intdiv($sisaCm, $meterCm) : 0;

                            $quantity = $meter > 0 ? "{$roll} Roll {$meter} Meter" : "{$roll} Roll";
                        @endphp
                        <td>{{$quantity}}</td>
                        <td>
                            {{formatRupiah($product->price_kulak)}}
                            @php
                                $rollCm = $product->per_roll_cm;
                                $stockCm = $product->stock_cm;

                                $totalKulak += ($rollCm > 0) ? ($product->price_kulak * $stockCm / $rollCm) : 0;
                                $totalAgen += ($rollCm > 0) ? ($product->price_agent * $stockCm / $rollCm) : 0;
                                $totalGrosir += ($rollCm > 0) ? ($product->price_grosir * $stockCm / $rollCm) : 0;
                                $totalRollUmum += ($rollCm > 0) ? ($product->price_umum_roll * $stockCm / $rollCm) : 0;

                                $totalMeteranGrosir += ($stockCm > 0) ? ($product->price_grosir_meter * $stockCm / 100) : 0;
                                $totalMeteranUmum += ($stockCm > 0) ? ($product->price_umum_meter * $stockCm / 100) : 0;
                            @endphp
                            <p class="text-success fw-bold" style="font-size:11px;">
                                Total: {{ formatRupiah(($product->per_roll_cm > 0) ? $product->price_kulak * $product->stock_cm / $product->per_roll_cm : 0) }}
                            </p>
                        </td>
                        <td>{{formatRupiah($product->price_agent)}}</td>
                        <td>{{formatRupiah($product->price_grosir)}}</td>
                        <td>{{formatRupiah($product->price_umum_roll)}}</td>
                        <td>{{formatRupiah($product->price_grosir_meter)}}</td>
                        <td>{{formatRupiah($product->price_umum_meter)}}</td>
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
                <tr style="background-color: #d8f3dc; color: white;">
                    <td colspan="2"><strong>Grand Total:</strong></td>
                    <td><strong>{{ formatRupiah($totalKulak) }}</strong></td>
                    <td><strong>{{ formatRupiah($totalAgen) }}</strong></td>
                    <td><strong>{{ formatRupiah($totalGrosir) }}</strong></td>
                    <td><strong>{{ formatRupiah($totalRollUmum) }}</strong></td>
                    <td><strong>{{ formatRupiah($totalMeteranGrosir) }}</strong></td>
                    <td colspan="4"><strong>{{ formatRupiah($totalMeteranUmum) }}</strong></td>
                </tr>
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
                            <input type="text" name="name" class="form-control" value="{{ $product->name }}" required placeholder="Masukkan  nama...">
                        </div>
                    </div>
                    <div class="row g-4">
                        <div class="col mb-0">
                            <label class="form-label">Foto Produk</label>
                            <input class="form-control" type="file" name="image" accept="image/*">
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label">Harga Kulak (per roll)</label>
                            <input type="number" name="price_kulak" value="{{ $product->price_kulak }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Harga Agen (per roll)</label>
                            <input type="number" name="price_agent" value="{{ $product->price_agent }}" class="form-control">
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label">Harga Grosir (per roll)</label>
                            <input type="number" name="price_grosir" value="{{ $product->price_grosir }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Harga Roll Umum (per roll)</label>
                            <input type="number" name="price_umum_roll" value="{{ $product->price_umum_roll }}" class="form-control">
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label">Harga Meteran Grosir (per meter)</label>
                            <input type="number" name="price_grosir_meter" value="{{ $product->price_grosir_meter }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Harga Meteran Umum (per meter)</label>
                            <input type="number" name="price_umum_meter" value="{{ $product->price_umum_meter }}" class="form-control">
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label">Stok Minimal</label>
                            <div class="input-group input-group-merge">
                                <input type="number" class="form-control" value="{{ $product->minimum_stock_cm / $product->per_roll_cm }}" name="minimum_stock_cm"required placeholder="10">
                                <span class="input-group-text">Roll</span>
                            </div>
                        </div>
                        <div class="col-md-6">
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
                        <div class="col-md-6">
                            <label class="form-label">Harga Kulak (per roll)</label>
                            <input type="number" name="price_kulak" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Harga Agen (per roll)</label>
                            <input type="number" name="price_agent" class="form-control">
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label">Harga Grosir (per roll)</label>
                            <input type="number" name="price_grosir" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Harga Roll Umum (per roll)</label>
                            <input type="number" name="price_umum_roll" class="form-control">
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label">Harga Meteran Grosir (per meter)</label>
                            <input type="number" name="price_grosir_meter" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Harga Meteran Umum (per meter)</label>
                            <input type="number" name="price_umum_meter" class="form-control">
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label">Stok Minimal</label>
                            <div class="input-group input-group-merge">
                                <input type="number" class="form-control" name="minimum_stock_cm"required placeholder="10">
                                <span class="input-group-text">Roll</span>
                            </div>
                        </div>
                        <div class="col-md-6">
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
