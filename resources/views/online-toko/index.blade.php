@extends('layouts/contentNavbarLayout')

@section('title', 'Toko Market Online')

@section('content')
@include('layouts/sections/message')
<div class="card">
    <div class="table-responsive text-nowrap">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>Nama Toko</th>
                    <th>Keterangan</th>
                    <th>Vendor</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($onlineTokos as $toko)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$toko->name}}</td>
                        <td>{{$toko->toko->name}}</td>
                        <td>{{$toko->description}}</td>
                        <td>
                            <div style="width: auto; height: 24px; overflow: hidden;">
                                <img src="/assets/img/icons/brands/{{ $toko->vendor }}.png" alt="Vendor Image" class="h-100 w-auto" />
                            </div>
                        </td>
                        <td>
                            <button class="btn btn-success btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#edittoko{{ $toko->id }}"><span class="menu-icon tf-icons bx bx-edit"></span> Ubah</button>
                            <button class="btn btn-danger btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#deletetoko{{ $toko->id }}"><span class="menu-icon tf-icons bx bx-trash"></span> Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Belum ada data toko market online.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@foreach ($onlineTokos as $toko)
<div class="modal fade" id="edittoko{{ $toko->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Edit Toko</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/online-toko/update/{{ $toko->id }}" method="POST">
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="col mb-4">
                            <label class="form-label">Toko</label>
                            <select name="toko_id" class="form-control">
                                <option value="">Pilih Toko</option>
                                @foreach ($tokos as $toko_)
                                    <option value="{{ $toko_->id }}" {{ $toko_->id == $toko->toko_id ? 'selected' : '' }}>{{ $toko_->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-4">
                            <label class="form-label">Nama Toko</label>
                            <input type="text" name="name" class="form-control" value="{{ $toko->name }}" required placeholder="Masukkan  nama...">
                        </div>
                    </div>
                    <div class="row g-4">
                        <div class="col mb-0">
                            <label class="form-label">Keterangan (opsional)</label>
                            <input type="text" name="description" class="form-control" value="{{ $toko->description }}" placeholder="Keterangan...">
                        </div>
                    </div>
                    <div class="col-md mt-4">
                        <label class="form-label">Vendor</label>
                        <br>
                        <select name="vendor" class="form-select">
                            <option value="Tiktok" {{ $toko->vendor == 'Tiktok' ? 'selected' : '' }}>Tiktok</option>
                            <option value="Shopee" {{ $toko->vendor == 'Shopee' ? 'selected' : '' }}>Shopee</option>
                            <option value="Lazada" {{ $toko->vendor == 'Lazada' ? 'selected' : '' }}>Lazada</option>
                            <option value="Youtube" {{ $toko->vendor == 'Youtube' ? 'selected' : '' }}>Youtube</option>
                            <option value="Blibli" {{ $toko->vendor == 'Blibli' ? 'selected' : '' }}>Blibli</option>
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
<button type="button" style="bottom: 3rem; right: 2rem;" class="btn position-fixed btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#createtokoOnline"><span class="menu-icon tf-icons bx bx-plus-circle"></span> Tambah Toko</button>
<div class="modal fade" id="createtokoOnline" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Tambah Toko Market Online</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/online-toko" method="POST">
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="col mb-4">
                            <label class="form-label">Toko</label>
                            <select name="toko_id" class="form-control">
                                <option value="">Pilih Toko</option>
                                @foreach ($tokos as $toko)
                                    <option value="{{ $toko->id }}">{{ $toko->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-4">
                            <label class="form-label">Nama Toko</label>
                            <input type="text" name="name" class="form-control" required placeholder="Masukkan  nama...">
                        </div>
                    </div>
                    <div class="row g-4">
                        <div class="col mb-0">
                            <label class="form-label">Keterangan (opsional)</label>
                            <input type="text" name="description" class="form-control" placeholder="Keterangan...">
                        </div>
                    </div>
                    <div class="col-md mt-4">
                        <label class="form-label">Vendor</label>
                        <br>
                        <select name="vendor" class="form-select">
                            <option value="Tiktok">Tiktok</option>
                            <option value="Shopee">Shopee</option>
                            <option value="Lazada">Lazada</option>
                            <option value="Youtube">Youtube</option>
                            <option value="Blibli">Blibli</option>
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
@endsection
