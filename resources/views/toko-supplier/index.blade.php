@php
use Carbon\Carbon;
@endphp
@extends('layouts/contentNavbarLayout')

@section('title', 'Toko & Supplier')

@section('content')
@include('layouts/sections/message')
<div class="card">
    <h5 class="card-header">Toko Market Online</h5>
    <div class="table-responsive text-nowrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Keterangan</th>
                    <th>Vendor</th>
                    <th style="width: 150px;">Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($onlineMarkets as $onlineMarket)
                    <tr>
                        <td>{{$onlineMarket->name}}</td>
                        <td>{{$onlineMarket->description}}</td>
                        <td>
                            <div style="width: auto; height: 24px; overflow: hidden;">
                                <img src="/assets/img/icons/brands/{{ $onlineMarket->vendor }}.png" alt="Vendor Image" class="h-100 w-auto" />
                            </div>
                        </td>
                        <td>
                            <button class="btn btn-success btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#edittoko{{ $onlineMarket->id }}"><span class="tf-icons bx bx-edit"></span></button>
                            <button class="btn btn-danger btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#deletetoko{{ $onlineMarket->id }}"><span class="tf-icons bx bx-trash"></span></button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Belum ada data toko market online.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <h5 class="card-header">Toko</h5>
            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Keterangan</th>
                            <th>Tipe</th>
                            <th style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse ($tokos as $toko)
                            <tr>
                                <td>{{$toko->name}}</td>
                                <td>{{$toko->description}}</td>
                                <td><span class="badge text-bg-{{ $toko->type == 'Online' ? 'success' : 'primary' }}">{{$toko->type}}</span></td>
                                <td>
                                    <button class="btn btn-success btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#edittoko{{ $toko->id }}"><span class="tf-icons bx bx-edit"></span></button>
                                    <button class="btn btn-danger btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#deletetoko{{ $toko->id }}"><span class="tf-icons bx bx-trash"></span></button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Belum ada data toko.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <h5 class="card-header">Supplier</h5>
            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Keterangan</th>
                            <th style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse ($suppliers as $supplier)
                            <tr>
                                <td>{{$supplier->name}}</td>
                                <td>{{$supplier->description}}</td>
                                <td>
                                    <button class="btn btn-success btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#editSupplier{{ $supplier->id }}"><span class="tf-icons bx bx-edit"></span></button>
                                    <button class="btn btn-danger btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#deleteSupplier{{ $supplier->id }}"><span class="tf-icons bx bx-trash"></span></button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Belum ada data supplier.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@foreach ($tokos as $toko)
<div class="modal fade" id="edittoko{{ $toko->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Edit Toko</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/toko/update/{{ $toko->id }}" method="POST">
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="col mb-4">
                            <label class="form-label">Nama Toko</label>
                            <input type="text" value="{{ $toko->name }}" name="name" class="form-control" required placeholder="Masukkan  nama...">
                        </div>
                    </div>
                    <div class="row g-4">
                        <div class="col mb-0">
                            <label class="form-label">Keterangan</label>
                            <input type="text" value="{{ $toko->description }}" name="description" class="form-control" required placeholder="Keterangan...">
                        </div>
                    </div>
                    <div class="col-md mt-4">
                        <label class="form-label">Tipe</label>
                        <br>
                        <div class="form-check form-check-inline mt-4">
                            <input class="form-check-input" type="radio" name="type" id="inlineRadio1" value="Offline" @if ($toko->type == 'Offline') checked @endif>
                            <label class="form-check-label" for="inlineRadio1">Offline</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="type" id="inlineRadio2" value="Online" @if ($toko->type == 'Online') checked @endif>
                            <label class="form-check-label" for="inlineRadio2">Online</label>
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
<div class="modal fade" id="deletetoko{{ $toko->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Hapus Toko</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah anda yakin menghapus toko {{ $toko->name }}?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                <a href="/toko/delete/{{ $toko->id }}" class="btn btn-primary">Hapus</a>
            </div>
        </div>
    </div>
</div>

@endforeach
<button type="button" style="bottom: 8.5rem; right: 2rem;" class="btn position-fixed btn-success rounded-pill" data-bs-toggle="modal" data-bs-target="#createtokoOnline"><span class="menu-icon tf-icons bx bx-plus-circle"></span> Tambah Toko Market Online</button>
<button type="button" style="bottom: 5rem; right: 2rem;" class="btn position-fixed btn-info rounded-pill" data-bs-toggle="modal" data-bs-target="#createtoko"><span class="menu-icon tf-icons bx bx-plus-circle"></span> Tambah Toko</button>
<button type="button" style="bottom: 1.5rem; right: 2rem;" class="btn position-fixed btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#createSupplier"><span class="menu-icon tf-icons bx bx-plus-circle"></span> Tambah Supplier</button>
<div class="modal fade" id="createtoko" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Tambah Toko</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/toko" method="POST">
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="col mb-4">
                            <label class="form-label">Nama Toko</label>
                            <input type="text" name="name" class="form-control" required placeholder="Masukkan  nama...">
                        </div>
                    </div>
                    <div class="row g-4">
                        <div class="col mb-0">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="description" class="form-control" required placeholder="Keterangan...">
                        </div>
                    </div>
                    <div class="col-md mt-4">
                        <label class="form-label">Tipe</label>
                        <br>
                        <div class="form-check form-check-inline mt-4">
                            <input class="form-check-input" type="radio" name="type" id="inlineRadio1" value="Offline">
                            <label class="form-check-label" for="inlineRadio1">Offline</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="type" id="inlineRadio2" value="Online">
                            <label class="form-check-label" for="inlineRadio2">Online</label>
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
<div class="modal fade" id="createtokoOnline" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Tambah Toko Market Online</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/toko-online" method="POST">
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="col mb-4">
                            <label class="form-label">Nama Toko</label>
                            <input type="text" name="name" class="form-control" required placeholder="Masukkan  nama...">
                        </div>
                    </div>
                    <div class="row g-4">
                        <div class="col mb-0">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="description" class="form-control" required placeholder="Keterangan...">
                        </div>
                    </div>
                    <div class="col-md mt-4">
                        <label class="form-label">Vendor</label>
                        <br>
                        <select name="vendor" class="form-select">
                            <option value="Tiktok">Tiktok</option>
                            <option value="Shopee">Shopee</option>
                            <option value="Lazada">Lazada</option>
                        </select>
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
@foreach ($suppliers as $supplier)
<div class="modal fade" id="editSupplier{{ $supplier->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Edit Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/suppliers/update/{{ $supplier->id }}" method="POST">
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="col mb-4">
                            <label class="form-label">Nama Supplier</label>
                            <input type="text" value="{{ $supplier->name }}" name="name" class="form-control" required placeholder="Masukkan  nama...">
                        </div>
                    </div>
                    <div class="row g-4">
                        <div class="col mb-0">
                            <label class="form-label">Keterangan</label>
                            <input type="text" value="{{ $supplier->description }}" name="description" class="form-control" required placeholder="Keterangan...">
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
<div class="modal fade" id="deleteSupplier{{ $supplier->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Hapus Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah anda yakin menghapus supplier {{ $supplier->name }}?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                <a href="/suppliers/delete/{{ $supplier->id }}" class="btn btn-primary">Hapus</a>
            </div>
        </div>
    </div>
</div>
@endforeach
<div class="modal fade" id="createSupplier" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Tambah Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/suppliers" method="POST">
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="col mb-4">
                            <label class="form-label">Nama Supplier</label>
                            <input type="text" name="name" class="form-control" required placeholder="Masukkan  nama...">
                        </div>
                    </div>
                    <div class="row g-4">
                        <div class="col mb-0">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="description" class="form-control" required placeholder="Keterangan...">
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
