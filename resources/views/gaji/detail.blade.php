@php
    use Carbon\Carbon;
    $items = $gaji->items->toArray();
    $half = ceil(count($items) / 2);
    $left = array_slice($items, 0, $half);
    $right = array_slice($items, $half);
@endphp
<style>
  .columned {
    column-count: 2;
    column-gap: 32px;
  }
</style>
@extends('layouts/contentNavbarLayout')

@section('title', 'Detil Gaji Periode '. $gaji->month . ' '. $gaji->year)

@section('content')
@include('layouts/sections/message')

<div class="card p-4">
    <a href="/gaji" class="btn btn-primary btn-sm rounded-pill" style="width: 120px;"><span class="menu-icon tf-icons bx bx-left-arrow-alt"></span> Kembali</a>
    <div style="display: flex; gap: 32px; margin-top:32px;">
        <div class="w-100" style="border: 0.5px solid #999999">
            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama</th>
                            <th>Gaji</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($left as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item['karyawan']['name'] ?? '-' }}</td>
                                <td>{{ formatRupiah($item['amount']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="w-100" style="border: 0.5px solid #999999">
            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama</th>
                            <th>Gaji</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($right as $item)
                            <tr>
                                <td>{{ $loop->iteration + count($left) }}</td>
                                <td>{{ $item['karyawan']['name'] ?? '-' }}</td>
                                <td>{{ formatRupiah($item['amount']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
