@extends('layouts/contentNavbarLayout')

@section('title', 'Laporan Keuangan')

@section('vendor-script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
@endsection

@section('page-script')
<script>
    function downloadPDF(el = 'to-print') {
        document.querySelectorAll('.no-print').forEach(el => el.style.display = 'none');
        const element = document.getElementById(el);
        html2pdf().from(element).save('Laporan Keuangan.pdf').then(() => {
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
<div class="card p-4 mt-4">
    @if (count($results) == 0)
        <p>Tidak ada data laporan.</p>
    @else
        <div class="table-responsive text-nowrap">
            <table class="table" id="to-print">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama</th>
                        <th>Keterangan</th>
                        <th>Sumber</th>
                        <th>Debit</th>
                        <th>Credit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($results as $report)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $report['name'] }}</td>
                            <td>{{ $report['description'] }}</td>
                            <td>{{ $report['source'] }}</td>
                            <td class="text-danger">{{ $report['type'] == 'debit' ? formatRupiah($report['amount']) : formatRupiah(0) }}</td>
                            <td class="text-success">{{ $report['type'] == 'credit' ? formatRupiah($report['amount']) : formatRupiah(0) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="4" class="text-end fw-bold">TOTAL:</td>
                        <td class="text-danger fw-bold">{{ formatRupiah($totalDebit) }}</td>
                        <td class="text-success fw-bold">{{ formatRupiah($totalKredit) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
