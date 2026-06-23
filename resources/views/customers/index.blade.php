@php
    use Carbon\Carbon;
@endphp
@extends('layouts/contentNavbarLayout')

@section('title', 'Pelanggan')

@section('content')
@include('layouts/sections/message')

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card p-4 mb-4">
    <div class="row">
        <div class="col-md-8 mb-2 mb-md-0">
            <a href="{{ route('customers.export', request()->only('search')) }}" class="btn btn-info">
                <span class="tf-icons bx bx-cloud-download"></span> Unduh Data Pelanggan (Excel)
            </a>
        </div>
        <div class="col-md-4">
            <form action="" method="GET">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" value="{{ $_GET['search'] ?? '' }}" placeholder="Cari nama / nomor WA..." aria-describedby="button-addon2">
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
                    <th>Nama Pelanggan</th>
                    <th>Nomor WA</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($customers as $customer)
                    <tr>
                        <td>{{ $loop->iteration + ($customers->currentPage() - 1) * $customers->perPage() }}</td>
                        <td>{{ $customer->name ?: '-' }}</td>
                        <td>{{ $customer->phone }}</td>
                        <td>{{ Carbon::parse($customer->created_at)->locale('id')->translatedFormat('d F Y') }}</td>
                        <td>
                            <button class="btn btn-success btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#editCustomer{{ $customer->id }}"><span class="menu-icon tf-icons bx bx-edit"></span> Ubah</button>
                            <button class="btn btn-danger btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#deleteCustomer{{ $customer->id }}"><span class="menu-icon tf-icons bx bx-trash"></span> Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Belum ada data pelanggan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div style="padding:2rem;">
            {{ $customers->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
</div>

@foreach ($customers as $customer)
<div class="modal fade" id="editCustomer{{ $customer->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ubah Pelanggan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/customers/update/{{ $customer->id }}" method="POST">
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="col mb-4">
                            <label class="form-label">Nama Pelanggan</label>
                            <input type="text" value="{{ $customer->name }}" name="name" class="form-control" placeholder="Masukkan nama...">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-0">
                            <label class="form-label">Nomor WA</label>
                            <input type="text" value="{{ $customer->phone }}" name="phone" class="form-control" required placeholder="08xxxxxxxxx">
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
<div class="modal fade" id="deleteCustomer{{ $customer->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hapus Pelanggan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah anda yakin menghapus pelanggan {{ $customer->name ?: $customer->phone }}?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                <a href="/customers/delete/{{ $customer->id }}" class="btn btn-primary">Hapus</a>
            </div>
        </div>
    </div>
</div>
@endforeach

<button type="button" style="bottom: 3rem; right: 2rem;" class="btn position-fixed btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#createCustomer"><span class="menu-icon tf-icons bx bx-plus-circle"></span> Tambah Pelanggan</button>
<div class="modal fade" id="createCustomer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Pelanggan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="col mb-4">
                            <label class="form-label">Nama Pelanggan</label>
                            <input type="text" name="name" class="form-control" placeholder="Masukkan nama...">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-0">
                            <label class="form-label">Nomor WA</label>
                            <input type="text" name="phone" class="form-control" required placeholder="08xxxxxxxxx">
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
