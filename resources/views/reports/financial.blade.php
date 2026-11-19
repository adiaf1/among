@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @include('reports._print_styles')
    <div class="report-print-header" style="display: none;">
        @include('partials.print-letterhead', [
            'wrapperClass' => 'w-100',
            'documentTitle' => 'Laporan Keuangan Operasional',
            'documentSubtitle' => 'Periode: '.\Illuminate\Support\Carbon::parse($startDate)->format('d M Y').' s/d '.\Illuminate\Support\Carbon::parse($endDate)->format('d M Y'),
            'documentMeta' => 'Dicetak: '.now()->format('d M Y H:i'),
        ])
    </div>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4 no-print">
        <div>
            <h4 class="mb-1">Laporan Keuangan Operasional</h4>
            <p class="text-muted mb-0">Perbandingan omzet penjualan (lunas & piutang) terhadap pembelian bahan, biaya pemeriksaan lapang, dan biaya produksi.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reports.financial', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn btn-outline-success">
                <i class="icon-base bx bx-download me-1"></i> Export CSV
            </a>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="icon-base bx bx-printer me-1"></i> Cetak Laporan
            </button>
        </div>
    </div>

    <div class="row g-4 mb-4 no-print">
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-heading">Total Penjualan</span>
                    <h4 class="my-1 text-success">Rp {{ number_format($summary['total_sales'], 0, ',', '.') }}</h4>
                    <small class="text-muted">Lunas: Rp {{ number_format($summary['sales_paid'], 0, ',', '.') }}</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-heading">Piutang Belum Lunas</span>
                    <h4 class="my-1 text-warning">Rp {{ number_format($summary['sales_unpaid'], 0, ',', '.') }}</h4>
                    <small class="text-muted">Dari penjualan periode ini</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-heading">Total Pengeluaran</span>
                    <h4 class="my-1 text-danger">Rp {{ number_format($summary['total_expense'], 0, ',', '.') }}</h4>
                    <small class="text-muted">Pembelian + Pemeriksaan + Produksi</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-heading">Selisih Operasional</span>
                    <h4 class="my-1 {{ $summary['net_margin'] >= 0 ? 'text-primary' : 'text-danger' }}">
                        Rp {{ number_format($summary['net_margin'], 0, ',', '.') }}
                    </h4>
                    <small class="text-muted">Penjualan &minus; Pengeluaran</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-bottom no-print">
            <form method="GET" action="{{ route('reports.financial') }}" class="row g-3 align-items-end report-filter-form">
                <div class="col-md-3 col-lg-2">
                    <label class="form-label mb-1">Tanggal Awal</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="form-control" onchange="this.form.submit()">
                </div>
                <div class="col-md-3 col-lg-2">
                    <label class="form-label mb-1">Tanggal Akhir</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="form-control" onchange="this.form.submit()">
                </div>
                <div class="col-md-auto">
                    <a href="{{ route('reports.financial') }}" class="btn btn-label-secondary">Reset Tahun Ini</a>
                </div>
            </form>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th class="text-end">Penjualan Lunas</th>
                        <th class="text-end">Piutang</th>
                        <th class="text-end">Total Penjualan</th>
                        <th class="text-end">Pembelian</th>
                        <th class="text-end">Biaya Pemeriksaan</th>
                        <th class="text-end">Biaya Produksi</th>
                        <th class="text-end">Total Pengeluaran</th>
                        <th class="text-end">Selisih Operasional</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($monthlyRows as $row)
                        @php
                            $monthLabel = $row['month'] !== 'Unknown'
                                ? \Illuminate\Support\Carbon::createFromFormat('Y-m', $row['month'])->translatedFormat('F Y')
                                : '-';
                        @endphp
                        <tr>
                            <td class="fw-medium">{{ $monthLabel }}</td>
                            <td class="text-end">Rp {{ number_format($row['sales_paid'], 0, ',', '.') }}</td>
                            <td class="text-end text-warning">Rp {{ number_format($row['sales_unpaid'], 0, ',', '.') }}</td>
                            <td class="text-end fw-medium text-success">Rp {{ number_format($row['total_sales'], 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($row['purchases'], 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($row['inspection_cost'], 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($row['production_cost'], 0, ',', '.') }}</td>
                            <td class="text-end fw-medium text-danger">Rp {{ number_format($row['total_expense'], 0, ',', '.') }}</td>
                            <td class="text-end fw-semibold {{ $row['net_margin'] >= 0 ? 'text-primary' : 'text-danger' }}">
                                Rp {{ number_format($row['net_margin'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">Belum ada transaksi keuangan pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if($monthlyRows->isNotEmpty())
                    <tfoot class="table-border-bottom-0">
                        <tr>
                            <th>Total Periode</th>
                            <th class="text-end">Rp {{ number_format($summary['sales_paid'], 0, ',', '.') }}</th>
                            <th class="text-end">Rp {{ number_format($summary['sales_unpaid'], 0, ',', '.') }}</th>
                            <th class="text-end">Rp {{ number_format($summary['total_sales'], 0, ',', '.') }}</th>
                            <th class="text-end">Rp {{ number_format($summary['purchases'], 0, ',', '.') }}</th>
                            <th class="text-end">Rp {{ number_format($summary['inspection_cost'], 0, ',', '.') }}</th>
                            <th class="text-end">Rp {{ number_format($summary['production_cost'], 0, ',', '.') }}</th>
                            <th class="text-end">Rp {{ number_format($summary['total_expense'], 0, ',', '.') }}</th>
                            <th class="text-end">Rp {{ number_format($summary['net_margin'], 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <div class="report-print-footer">
        <div class="report-signature-box">
            <div>Penanggung Jawab Keuangan,</div>
            <div class="report-signature-line">{{ auth()->user()?->name ?: 'Admin' }}</div>
        </div>
    </div>
</div>
@endsection
