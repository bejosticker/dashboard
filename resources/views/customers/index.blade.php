@php
    use Carbon\Carbon;
@endphp
@extends('layouts/contentNavbarLayout')

@section('title', 'Pelanggan')

@section('content')
@include('layouts/sections/message')
<div class="card p-4 mb-4">
    <form class="row g-3 align-items-end" method="GET">
        <div class="col-md-8">
            <label class="form-label">Cari Pelanggan</label>
            <div class="input-group">
                <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Cari nama atau nomor WA...">
                <button class="btn btn-primary" type="submit"><span class="tf-icons bx bx-search"></span> Cari</button>
            </div>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="{{ url('customers/export') }}" class="btn btn-success"><span class="tf-icons bx bx-download"></span> Export Excel (CSV)</a>
        </div>
    </form>
</div>
<div class="card">
    <div class="table-responsive text-nowrap">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>Nomor WA</th>
                    <th>Terdaftar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($customers as $customer)
                    <tr>
                        <td>{{ ($customers->currentPage() - 1) * $customers->perPage() + $loop->iteration }}</td>
                        <td>{{ $customer->name ?: '-' }}</td>
                        <td>{{ $customer->phone }}</td>
                        <td>{{ $customer->created_at ? Carbon::parse($customer->created_at)->locale('id')->translatedFormat('d F Y') : '-' }}</td>
                        <td>
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
<div class="modal fade" id="deleteCustomer{{ $customer->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Hapus Pelanggan</h5>
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
@endsection
