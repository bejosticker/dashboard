@extends('layouts/contentNavbarLayout')

@section('title', 'Beranda')

@section('content')
    <div class="row">
        <div class="col-md-8 mb-6 order-0">
            <div class="card">
                <div class="d-flex align-items-start row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary mb-3">Selamat Datang {{ session('data')->name }}! 🎉</h5>
                            <p class="mb-6">Selamat datang di dashboard Bejosticker</p>
                            <p style="margin-top:54px; font-size: 18px; font-weight: bold;">Total Pembelian Bahan Bulan Ini: {{ formatRupiah($kulak) }}</p>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-6">
                            <img src="{{asset('assets/img/illustrations/man-with-laptop.png')}}" height="175"
                                class="scaleX-n1-rtl" alt="View Badge User">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between" style="padding-bottom: 20px;">
                    <h5 class="card-title m-0 me-2">Keuangan Bulan Ini</h5>
                </div>
                <div class="card-body pt-0 pb-0">
                    <ul class="p-0 m-0">
                        <li class="d-flex align-items-center mb-6">
                            <div class="avatar flex-shrink-0 me-3">
                                <img src="/assets/img/icons/unicons/wallet-info.png"
                                    alt="User" class="rounded">
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <small class="d-block">Total</small>
                                    <h6 class="fw-normal mb-0">Uang Masuk (Credit)</h6>
                                </div>
                                <div class="user-progress d-flex align-items-center gap-2">
                                    <h6 class="fw-bold mb-0">{{ formatRupiah($report['credit']) }}</h6>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex align-items-center mb-6">
                            <div class="avatar flex-shrink-0 me-3">
                                <img src="/assets/img/icons/unicons/wallet.png"
                                    alt="User" class="rounded">
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <small class="d-block">Total</small>
                                    <h6 class="fw-normal mb-0">Uang Keluar (Debit)</h6>
                                </div>
                                <div class="user-progress d-flex align-items-center gap-2">
                                    <h6 class="fw-bold mb-0">{{ formatRupiah($report['debit']) }}</h6>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 mb-6 order-0">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between" style="padding-bottom: 20px;">
                    <h5 class="card-title m-0 me-2">Keuangan Toko Bulan Ini</h5>
                </div>
                @if (count($tokos) == 0)
                    <div class="card-body text-center">
                        <p class="mb-0">Tidak ada data keuangan toko bulan ini.</p>
                    </div>
                @else
                    <div class="card-body pt-0 pb-4">
                        <div class="table-responsive text-nowrap">
                            <table class="table">
                                <thead style="border-color: transparent;">
                                    <tr>
                                        <th>Nama Toko</th>
                                        <th>Pengambilan Bahan</th>
                                        <th>Pemasukan Offline</th>
                                        <th>Pemasukan Online</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($tokos as $toko)
                                        <tr style="border-color: transparent;">
                                            <td>{{ $toko->name }}</td>
                                            <td>{{ formatRupiah($toko->debit) }}</td>
                                            <td>{{ formatRupiah($toko->credit) }}</td>
                                            <td>{{ formatRupiah($toko->online) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between" style="padding-bottom: 20px;">
                    <h5 class="card-title m-0 me-2">Produk Perlu Restock</h5>
                </div>
                @if (count($report['products']) > 0)
                    <div class="table-responsive text-nowrap">
                        <table class="table">
                            <thead style="border-color: transparent;">
                                <tr>
                                    <th>#</th>
                                    <th>Nama Produk</th>
                                    <th>Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($report['products'] as $product)
                                    <tr style="border-color: transparent;">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $product->name }}</td>
                                        @php
                                            $stockCm = $product->stock_cm;
                                            $rollCm = $product->per_roll_cm;
                                            $meterCm = 100;

                                            $roll = intdiv($stockCm, $rollCm);
                                            $sisaCm = $stockCm % $rollCm;

                                            $meter = intdiv($sisaCm, $meterCm);

                                            $quantity = $meter > 0 ? "{$roll} Roll {$meter} Meter" : "{$roll} Roll";
                                        @endphp
                                        <td>{{ $quantity }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-center">Tidak ada produk perlu restock.</p>
                @endif
            </div>
        </div>
    </div>
@endsection