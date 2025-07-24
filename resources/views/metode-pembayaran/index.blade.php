@php
use Carbon\Carbon;
@endphp
@extends('layouts/contentNavbarLayout')

@section('title', 'Metode Pembayaran')

@section('content')
@include('layouts/sections/message')
<div class="card">
    <div class="table-responsive text-nowrap">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>Icon</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($paymentMethods as $paymentMethod)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$paymentMethod->name}}</td>
                        <td>
                            <div class="rounded" style="width: 64px; height: 64px; border-radius: 8px; overflow: hidden;">
                                <img src="/assets/img/payment-methods/{{ $paymentMethod->image }}"  class="w-100 h-100"/>
                            </div>
                        </td>
                        <td>{{Carbon::parse($paymentMethod->created_at)->locale('id')->translatedFormat('d F Y')}}</td>
                        <td>
                            <button class="btn btn-success btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#editpaymentMethod{{ $paymentMethod->id }}"><span class="menu-icon tf-icons bx bx-edit"></span> Ubah</button>
                            <button class="btn btn-danger btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#deletepaymentMethod{{ $paymentMethod->id }}"><span class="menu-icon tf-icons bx bx-trash"></span> Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Belum ada data metode pembayaran.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@foreach ($paymentMethods as $paymentMethod)
<div class="modal fade" id="editpaymentMethod{{ $paymentMethod->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Edit Metode Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/metode-pembayaran/update/{{ $paymentMethod->id }}" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="col mb-4">
                            <label class="form-label">Nama paymentMethod</label>
                            <input type="text" value="{{ $paymentMethod->name }}" name="name" class="form-control" required placeholder="Masukkan  nama...">
                        </div>
                    </div>
                    <div class="row g-4">
                        <div class="col mb-0">
                            <label class="form-label">Icon</label>
                            <input class="form-control" type="file" name="image" accept="image/*">
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
<div class="modal fade" id="deletepaymentMethod{{ $paymentMethod->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Hapus Metode Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah anda yakin menghapus Metode Pembayaran {{ $paymentMethod->name }}?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                <a href="/metode-pembayaran/delete/{{ $paymentMethod->id }}" class="btn btn-primary">Hapus</a>
            </div>
        </div>
    </div>
</div>
@endforeach
<button type="button" style="bottom: 3rem; right: 2rem;" class="btn position-fixed btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#createpaymentMethod"><span class="menu-icon tf-icons bx bx-plus-circle"></span> Tambah Metode Pembayaran</button>
<div class="modal fade" id="createpaymentMethod" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Tambah Metode Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="col mb-4">
                            <label class="form-label">Nama Metode Pembayaran</label>
                            <input type="text" name="name" class="form-control" required placeholder="Masukkan  nama...">
                        </div>
                    </div>
                    <div class="row g-4">
                        <div class="col mb-0">
                            <label class="form-label">Icon</label>
                            <input class="form-control" type="file" name="image" accept="image/*">
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
