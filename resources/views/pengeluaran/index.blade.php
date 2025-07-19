@php
use Carbon\Carbon;
@endphp
@extends('layouts/contentNavbarLayout')

@section('title', 'Pengeluaran')

@section('content')
@include('layouts/sections/message')
<div class="card p-4 mb-4">
    <div class="row">
        <div class="col-md-4"></div>
        <div class="col-md-4"></div>
        <div class="col-md-4">
            <form action="" method="GET">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" value="{{ $_GET['search'] ?? '' }}" placeholder="Cari pengeluaran..." aria-describedby="button-addon2">
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
                    <th>Toko</th>
                    <th>Nama</th>
                    <th>Keterangan</th>
                    <th>Nominal</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($pengeluarans as $pengeluaran)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$pengeluaran->toko?->name ?? '-'}}</td>
                        <td>{{$pengeluaran->name}}</td>
                        <td>{{$pengeluaran->description}}</td>
                        <td>{{formatRupiah($pengeluaran->amount)}}</td>
                        <td>{{Carbon::parse($pengeluaran->date)->locale('id')->translatedFormat('d F Y')}}</td>
                        <td>
                            <button class="btn btn-success btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#editpengeluaran{{ $pengeluaran->id }}"><span class="menu-icon tf-icons bx bx-edit"></span> Ubah</button>
                            <button class="btn btn-danger btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#deletepengeluaran{{ $pengeluaran->id }}"><span class="menu-icon tf-icons bx bx-trash"></span> Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Belum ada data pengeluaran.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div style="padding:2rem;">
            {{ $pengeluarans->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
</div>
@foreach ($pengeluarans as $pengeluaran)
<div class="modal fade" id="editpengeluaran{{ $pengeluaran->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Edit Pengeluaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/pengeluaran/update/{{ $pengeluaran->id }}" method="POST">
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="col mb-4">
                            <label class="form-label">Nama Pengeluaran</label>
                            <input type="text" value="{{ $pengeluaran->name }}" name="name" class="form-control" required placeholder="Masukkan  nama...">
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col mb-0">
                            <label class="form-label">Keterangan</label>
                            <input type="text" value="{{ $pengeluaran->description }}" name="description" class="form-control" required placeholder="Keterangan...">
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col mb-0">
                            <label class="form-label">Toko</label>
                            <select name="toko_id" class="form-control">
                                <option value="">Pilih Toko</option>
                                @foreach ($tokos as $toko)
                                    <option value="{{ $toko->id }}" {{ $pengeluaran->toko_id == $toko->id ? 'selected' : '' }}>{{ $toko->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col mb-0">
                            <label class="form-label">Nominal</label>
                            <input type="number" value="{{ $pengeluaran->amount }}" name="amount" class="form-control" required placeholder="1000000">
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col mb-0">
                            <label class="form-label">Tanggal</label>
                            <input type="date" value="{{ $pengeluaran->date }}" name="date" class="form-control" required placeholder="1000000">
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
<div class="modal fade" id="deletepengeluaran{{ $pengeluaran->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Hapus pengeluaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah anda yakin menghapus pengeluaran {{ $pengeluaran->name }}?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                <a href="/pengeluaran/delete/{{ $pengeluaran->id }}" class="btn btn-primary">Hapus</a>
            </div>
        </div>
    </div>
</div>
@endforeach
<button type="button" style="bottom: 3rem; right: 2rem;" class="btn position-fixed btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#createpengeluaran"><span class="menu-icon tf-icons bx bx-plus-circle"></span> Tambah Pengeluaran</button>
<div class="modal fade" id="createpengeluaran" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Tambah pengeluaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="col mb-4">
                            <label class="form-label">Nama Pengeluaran</label>
                            <input type="text" name="name" class="form-control" required placeholder="Masukkan  nama...">
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col mb-0">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="description" class="form-control" required placeholder="Keterangan...">
                        </div>
                    </div>
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
                            <label class="form-label">Nominal</label>
                            <input type="number" name="amount" class="form-control" required placeholder="1000000">
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
