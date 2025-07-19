@php
use Carbon\Carbon;
@endphp
@extends('layouts/contentNavbarLayout')

@section('title', 'Karyawan')

@section('content')
@include('layouts/sections/message')
<div class="card p-4 mb-4">
    <div class="row">
        <div class="col-md-4"></div>
        <div class="col-md-4"></div>
        <div class="col-md-4">
            <form action="" method="GET">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" value="{{ $_GET['search'] ?? '' }}" placeholder="Cari karyawan..." aria-describedby="button-addon2">
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
                    <th>Bulan Masuk</th>
                    <th>Tahun Masuk</th>
                    <th>Gaji</th>
                    <th>Toko</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($karyawans as $karyawan)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$karyawan->name}}</td>
                        <td>{{$karyawan->month}}</td>
                        <td>{{$karyawan->year}}</td>
                        <td>{{formatRupiah($karyawan->gaji)}}</td>
                        <td>{{$karyawan->toko?->name ?? '-'}}</td>
                        <td>
                            <button class="btn btn-success btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#editkaryawan{{ $karyawan->id }}"><span class="menu-icon tf-icons bx bx-edit"></span> Ubah</button>
                            <button class="btn btn-danger btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#deletekaryawan{{ $karyawan->id }}"><span class="menu-icon tf-icons bx bx-trash"></span> Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Belum ada data karyawan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@foreach ($karyawans as $karyawan)
<div class="modal fade" id="editkaryawan{{ $karyawan->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Edit karyawan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/karyawan/update/{{ $karyawan->id }}" method="POST">
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="col mb-4">
                            <label class="form-label">Nama karyawan</label>
                            <input type="text" value="{{ $karyawan->name }}" name="name" class="form-control" required placeholder="Masukkan  nama...">
                        </div>
                    </div>
                    <div class="row g-4">
                        <div class="col mb-0">
                            <label class="form-label">Bulan Masuk</label>
                            <select name="month" class="form-control" value="{{ $karyawan->month }}">
                                <option value="Januari" {{ $karyawan->month == 'Januari' ? 'selected' : '' }}>Januari</option>
                                <option value="Februari"{{ $karyawan->month == 'Februari' ? 'selected' : '' }}>Februari</option>
                                <option value="Maret"{{ $karyawan->month == 'Maret' ? 'selected' : '' }}>Maret</option>
                                <option value="April"{{ $karyawan->month == 'April' ? 'selected' : '' }}>April</option>
                                <option value="Mei"{{ $karyawan->month == 'Mei' ? 'selected' : '' }}>Mei</option>
                                <option value="Juni"{{ $karyawan->month == 'Juni' ? 'selected' : '' }}>Juni</option>
                                <option value="Juli"{{ $karyawan->month == 'Juli' ? 'selected' : '' }}>Juli</option>
                                <option value="Agustus"{{ $karyawan->month == 'Agustus' ? 'selected' : '' }}>Agustus</option>
                                <option value="September"{{ $karyawan->month == 'September' ? 'selected' : '' }}>September</option>
                                <option value="Oktober"{{ $karyawan->month == 'Oktober' ? 'selected' : '' }}>Oktober</option>
                                <option value="November"{{ $karyawan->month == 'November' ? 'selected' : '' }}>November</option>
                                <option value="Desember"{{ $karyawan->month == 'Desember' ? 'selected' : '' }}>Desember</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col mb-0">
                            <label class="form-label">Tahun Masuk</label>
                            <input type="number" value="{{ $karyawan->year }}" name="year" class="form-control" required placeholder="2019">
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col mb-0">
                            <label class="form-label">Toko</label>
                            <select name="toko_id" class="form-control">
                                <option value="">Pilih Toko</option>
                                @foreach ($tokos as $toko)
                                    <option value="{{ $toko->id }}" {{ $karyawan->toko_id == $toko->id ? 'selected' : '' }}>{{ $toko->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col mb-0">
                            <label class="form-label">Gaji</label>
                            <input type="number" value="{{ $karyawan->gaji }}" name="gaji" class="form-control" required placeholder="1000000">
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
<div class="modal fade" id="deletekaryawan{{ $karyawan->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Hapus karyawan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah anda yakin menghapus karyawan {{ $karyawan->name }}?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                <a href="/karyawan/delete/{{ $karyawan->id }}" class="btn btn-primary">Hapus</a>
            </div>
        </div>
    </div>
</div>
@endforeach
<button type="button" style="bottom: 3rem; right: 2rem;" class="btn position-fixed btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#createkaryawan"><span class="menu-icon tf-icons bx bx-plus-circle"></span> Tambah karyawan</button>
<div class="modal fade" id="createkaryawan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Tambah karyawan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="col mb-4">
                            <label class="form-label">Nama karyawan</label>
                            <input type="text" name="name" class="form-control" required placeholder="Masukkan  nama...">
                        </div>
                    </div>
                    <div class="row g-4">
                        <div class="col mb-0">
                            <label class="form-label">Bulan Masuk</label>
                            <select name="month" class="form-control" value="Januari">
                                <option value="Januari">Januari</option>
                                <option value="Februari">Februari</option>
                                <option value="Maret">Maret</option>
                                <option value="April">April</option>
                                <option value="Mei">Mei</option>
                                <option value="Juni">Juni</option>
                                <option value="Juli">Juli</option>
                                <option value="Agustus">Agustus</option>
                                <option value="September">September</option>
                                <option value="Oktober">Oktober</option>
                                <option value="November">November</option>
                                <option value="Desember">Desember</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col mb-0">
                            <label class="form-label">Tahun Masuk</label>
                            <input type="number" name="year" class="form-control" required placeholder="2019">
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
                            <label class="form-label">Gaji</label>
                            <input type="number" name="gaji" class="form-control" required placeholder="1000000">
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
