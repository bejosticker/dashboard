@php
    use Carbon\Carbon;
@endphp
@extends('layouts/contentNavbarLayout')

@section('title', 'Gaji')

@section('content')
@include('layouts/sections/message')
<div class="card">
    <div class="table-responsive text-nowrap">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Bulan</th>
                    <th>Tahun</th>
                    <th>Tanggal Gaji</th>
                    <th>Karyawan</th>
                    <th>Total Gaji</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($gajis as $gaji)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$gaji->month}}</td>
                        <td>{{$gaji->year}}</td>
                        <td>{{Carbon::parse($gaji->date)->locale('id')->translatedFormat('d F Y')}}</td>
                        <td>{{$gaji->items_count}}</td>
                        <td>{{formatRupiah($gaji->items_sum_amount)}}</td>
                        <td>
                            <a href="/gaji/detail/{{ $gaji->id }}" class="btn btn-primary btn-sm rounded-pill"><span class="menu-icon tf-icons bx bx-info-circle"></span> Lihat Detail</a>
                            <button class="btn btn-danger btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#deletegaji{{ $gaji->id }}"><span class="menu-icon tf-icons bx bx-trash"></span> Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Belum ada data gaji.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div style="padding:2rem;">
            {{ $gajis->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
</div>
@foreach ($gajis as $gaji)
<div class="modal fade" id="deletegaji{{ $gaji->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Hapus Gaji</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah anda yakin menghapus gaji {{ $gaji->name }}?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                <a href="/gaji/delete/{{ $gaji->id }}" class="btn btn-primary">Hapus</a>
            </div>
        </div>
    </div>
</div>
@endforeach
<button type="button" style="bottom: 3rem; right: 2rem;" class="btn position-fixed btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#creategaji"><span class="menu-icon tf-icons bx bx-plus-circle"></span> Tambah Gaji</button>
<div class="modal fade" id="creategaji" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Tambah gaji</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    @csrf
                    <div class="row g-4">
                        <div class="col mb-0">
                            <label class="form-label">Bulan</label>
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
                            <label class="form-label">Tahun</label>
                            <input type="number" name="year" class="form-control" required placeholder="2019">
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col mb-0">
                            <label class="form-label">Tanggal Gaji:</label>
                            <input type="date" name="date" class="form-control" required>
                        </div>
                    </div>
                    <div class="alert alert-info d-flex align-items-center gap-2 mt-4" role="alert">
                        <span class="alert-icon rounded"><i class="icon-base bx icon-xs bx-info-circle"></i></span>
                        Data karyawan akan terinput otomatis sesuai data gaji masing-masing
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
