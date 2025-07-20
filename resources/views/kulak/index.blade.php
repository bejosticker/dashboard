@extends('layouts/contentNavbarLayout')

@section('title', 'Pembelian Bahan')

@section('content')
<button type="button" style="bottom: 3rem; right: 2rem;" class="btn position-fixed btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#createPembelianBahan">
    <span class="menu-icon tf-icons bx bx-plus-circle"></span> Tambah Pembelian Bahan
</button>

<div class="modal fade" id="createPembelianBahan" tabindex="-1" aria-hidden="true"> {{-- wire:ignore.self DIHAPUS --}}
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Tambah Pembelian Bahan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @livewire('kulak-form')
            </div>
        </div>
    </div>
</div>
@endsection
