@php
    use Carbon\Carbon;
@endphp
@extends('layouts/contentNavbarLayout')

@section('title', 'Penyesuaian Stok')

@section('content')
@include('layouts/sections/message')

<div class="card p-4">
    @livewire('stock-adjustment-form')
</div>

<div class="card mt-4">
    <h5 class="card-header">Riwayat Penyesuaian Stok</h5>
    <div class="table-responsive text-nowrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Produk</th>
                    <th>Jenis</th>
                    <th>Penyesuaian</th>
                    <th>Stok Sebelum</th>
                    <th>Stok Sesudah</th>
                    <th>Alasan</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($adjustments as $adj)
                    <tr>
                        <td>{{ Carbon::parse($adj->date)->locale('id')->translatedFormat('d M Y') }}</td>
                        <td>{{ $adj->product_name ?? '-' }}</td>
                        <td>
                            @if ($adj->product_type === 'product')
                                <span class="badge bg-label-secondary">Bahan</span>
                            @else
                                <span class="badge bg-label-info">Produk Cetak</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $modeLabel = ['set' => 'Set Aktual', 'add' => 'Tambah', 'sub' => 'Kurangi'][$adj->mode] ?? $adj->mode;
                            @endphp
                            {{ $modeLabel }}
                        </td>
                        <td>{{ stockLabel($adj->product_type, $adj->stock_before, $adj->per_roll_cm) }}</td>
                        <td><strong>{{ stockLabel($adj->product_type, $adj->stock_after, $adj->per_roll_cm) }}</strong></td>
                        <td>{{ $adj->note ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Belum ada penyesuaian stok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div style="padding:2rem;">
            {{ $adjustments->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
