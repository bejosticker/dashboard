@extends('layouts/contentNavbarLayout')

@section('title', 'Laporan Market Online')

@section('vendor-script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
@endsection

@section('page-script')
<script>
    function downloadPDF(el = 'to-print') {
        document.querySelectorAll('.no-print').forEach(el => el.style.display = 'none');
        const element = document.getElementById(el);
        html2pdf().from(element).save('Laporan Market Online.pdf').then(() => {
            document.querySelectorAll('.no-print').forEach(el => el.style.display = '');
        });
    }
</script>
@endsection

@section('content')
@include('layouts/sections/message')
<div class="card p-4">
    <form class="row d-flex-row align-items-end" method="GET">
        <div class="col-md-3">
            <label class="form-label">Tanggal Awal:</label>
            <input type="date" name="from" class="form-control" value="{{ $_GET['from'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Tanggal Akhir:</label>
            <input type="date" name="to" class="form-control" value="{{ $_GET['to'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary"><span class="tf-icons bx bx-filter-alt"></span> Filter Laporan</button>
            <button type="button" class="btn btn-info" onclick="downloadPDF()"><span class="tf-icons bx bx-cloud-download"></span> Unduh PDF</button>
        </div>
    </form>
</div>
@if (count($reports) == 0)
    <div class="card p-4 mt-4">
        <p class="text-center">Tidak ada data laporan.</p>
    </div>
    @else
        <div id="to-print" class="card mt-4">
            @foreach ($reports as $report)
                <div class="p-4 mt-4">
                    <div class="card-header d-flex align-items-center justify-content-between" style="padding-bottom: 20px;">
                        <h5 class="card-title m-0 me-2">{{ $report->name }}</h5>
                    </div>
                    <div class="table-responsive text-nowrap">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Market</th>
                                    <th>Tanggal</th>
                                    <th>Kredit</th>
                                    <th>Total Per Market</th>
                                    <th>Iklan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($report->vendors as $vendor)
                                    @php
                                        $reports = collect($vendor->reports)->where('type', 'credit')->values();
                                        $totalPerMarket = $reports->sum('amount');
                                        $totalAd = collect($vendor->reports)->where('type', 'debit')->sum('amount');
                                        $rowspan = $reports->count();
                                    @endphp
    
                                    @if($reports->count())
                                        @foreach ($reports as $i => $item)
                                            <tr>
                                                {{-- Market Name only for first row --}}
                                                @if ($i === 0)
                                                    <td rowspan="{{ $rowspan }}">{{ $vendor->vendor }}</td>
                                                @endif
    
                                                <td>{{ \Carbon\Carbon::parse($item->date)->format('d F') }}</td>
                                                <td>Rp{{ formatRupiah($item->amount) }}</td>
    
                                                {{-- Total Per Market only for first row --}}
                                                @if ($i === 0)
                                                    <td rowspan="{{ $rowspan }}">
                                                        Rp{{ formatRupiah($totalPerMarket) }}
                                                    </td>
                                                    {{-- Ad --}}
                                                    <td rowspan="{{ $rowspan }}">
                                                        {{ formatRupiah($totalAd) }}
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    @endif
                                @endforeach
    
                                {{-- TOTAL --}}
                                <tr style="background-color: #d8f3dc; color: white;">
                                    <td colspan="3"><strong>Total</strong></td>
                                    <td>
                                        <strong>{{ formatRupiah(collect($report->vendors)->flatMap(fn($v) => $v->reports)->sum('amount'))}}</strong>
                                    </td>
                                    <td>
                                        <strong>{{ formatRupiah(collect($report->vendors)->flatMap(fn($v) => $v->reports)->where('type', 'debit')->sum('amount'))}}</strong>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
