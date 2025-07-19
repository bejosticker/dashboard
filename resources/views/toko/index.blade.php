@php
use Carbon\Carbon;
@endphp
@extends('layouts/contentNavbarLayout')

@section('title', 'Toko')

@section('content')
@include('layouts/sections/message')
<div class="card">
    <div class="table-responsive text-nowrap">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>Keterangan</th>
                    <th>Tipe</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($tokos as $toko)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$toko->name}}</td>
                        <td>{{$toko->description}}</td>
                        <td><span class="badge text-bg-{{ $toko->type == 'Online' ? 'success' : 'primary' }}">{{$toko->type}}</span></td>
                        <td>{{Carbon::parse($toko->created_at)->locale('id')->translatedFormat('d F Y')}}</td>
                        <td>
                            <button class="btn btn-success btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#edittoko{{ $toko->id }}"><span class="menu-icon tf-icons bx bx-edit"></span> Ubah</button>
                            <button class="btn btn-danger btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#deletetoko{{ $toko->id }}"><span class="menu-icon tf-icons bx bx-trash"></span> Hapus</button>
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
<button type="button" style="bottom: 3rem; right: 2rem;" class="btn position-fixed btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#createtoko"><span class="menu-icon tf-icons bx bx-plus-circle"></span> Tambah Toko</button>
<div class="modal fade" id="createtoko" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Tambah Toko</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
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
@endsection
