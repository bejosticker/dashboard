@extends('layouts/contentNavbarLayout')

@section('title', 'Pengguna')

@section('page-style')
@vite([
  'resources/assets/vendor/scss/pages/page-auth.scss'
])
@endsection

@section('content')
@include('layouts/sections/message')
<div class="card">
    <table class="table" id="users-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Username</th>
                <th>Level</th>
                <th style="width: 400px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->username }}</td>
                <td>{{ $user->level == 1 ? 'Super Admin' : 'Admin' }}</td>
                <td>
                    <button class="btn btn-success btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#editUser{{ $user->id }}"><span class="menu-icon tf-icons bx bx-edit"></span> Edit</button>
                    <button class="btn btn-info btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#editUserPassword{{ $user->id }}"><span class="menu-icon tf-icons bx bx-key"></span> Ubah Password</button>
                    @if (session('data')->id != $user->id)
                    <button class="btn btn-danger btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#deleteUser{{ $user->id }}"><span class="menu-icon tf-icons bx bx-trash"></span> Hapus</button>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<button type="button" style="bottom: 3rem; right: 2rem;" class="btn position-fixed btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#createUser"><span class="menu-icon tf-icons bx bx-plus-circle"></span> Tambah Pengguna</button>
<div class="modal fade" id="createUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Tambah Pengguna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/users" method="POST">
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="col">
                            <label class="form-label">Nama Pengguna</label>
                            <input type="text" name="name" class="form-control" required placeholder="Masukkan  nama...">
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required placeholder="Masukkan  username...">
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col mb-0">
                            <label class="form-label">Level</label>
                            <select name="level" class="form-control">
                                <option value="">Pilih Level</option>
                                <option value="1">Super Admin</option>
                                <option value="2">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col form-password-toggle">
                            <label class="form-label">Password</label>
                            <div class="input-group input-group-merge">
                                <input type="password" class="form-control" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" />
                                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col form-password-toggle">
                            <label class="form-label">Konfirmasi Password</label>
                            <div class="input-group input-group-merge">
                                <input type="password" class="form-control" name="password_confirmation" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password_confirmation" />
                                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
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
@foreach ($users as $user)
<div class="modal fade" id="editUser{{ $user->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Edit Pengguna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/users/update/{{ $user->id }}" method="POST">
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="col">
                            <label class="form-label">Nama Pengguna</label>
                            <input type="text" value="{{ $user->name }}" name="name" class="form-control" required placeholder="Masukkan  nama...">
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col">
                            <label class="form-label">Username</label>
                            <input type="text" value="{{ $user->username }}" name="username" class="form-control" required placeholder="Masukkan  username...">
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col mb-0">
                            <label class="form-label">Level</label>
                            <select name="level" class="form-control">
                                <option value="">Pilih Level</option>
                                <option value="1" {{ $user->level == 1 ? 'selected' : '' }}>Super Admin</option>
                                <option value="2" {{ $user->level == 2 ? 'selected' : '' }}>Admin</option>
                            </select>
                            </select>
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
<div class="modal fade" id="editUserPassword{{ $user->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Edit Password Pengguna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/users/update-password/{{ $user->id }}" method="POST">
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="col form-password-toggle">
                            <label class="form-label">Password Baru</label>
                            <div class="input-group input-group-merge">
                                <input type="password" class="form-control" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" />
                                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col form-password-toggle">
                            <label class="form-label">Konfirmasi Password Baru</label>
                            <div class="input-group input-group-merge">
                                <input type="password" class="form-control" name="password_confirmation" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password_confirmation" />
                                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
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
<div class="modal fade" id="deleteUser{{ $user->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Hapus Pengguna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah anda yakin menghapus pengguna {{ $user->name }}?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                <a href="/users/delete/{{ $user->id }}" class="btn btn-primary">Hapus</a>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection
