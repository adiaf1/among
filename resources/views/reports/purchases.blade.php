@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @include('reports._print_styles')
    <div class="report-print-header" style="display: none;">
        @include('partials.print-letterhead', [
            'wrapperClass' => 'w-100',
            'documentTitle' => 'Laporan Pembelian Bahan / Gabah',
            'documentSubtitle' => 'Periode: '.\Illuminate\Support\Carbon::parse($startDate)->format('d M Y').' s/d '.\Illuminate\Support\Carbon::parse($endDate)->format('d M Y'),
            'documentMeta' => 'Dicetak: '.now()->format('d M Y H:i'),
        ])
    </div>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4 no-print">
        <div>
            <h4 class="mb-1">Laporan Pembelian Bahan / Gabah</h4>
            <p class="text-muted mb-0">Rekap transaksi pembelian masuk dari petani maupun supplier per periode.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reports.purchases', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn btn-outline-success">
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
                    <span class="text-heading">Total Transaksi</span>
                    <h4 class="my-1">{{ number_format($summary['count'], 0, ',', '.') }}</h4>
                    <small class="text-muted">{{ $summary['supplier_count'] }} Supplier &middot; {{ $summary['farmer_count'] }} Petani</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-heading">Total Volume Masuk</span>
                    <h4 class="my-1">{{ number_format($summary['total_qty'], 2, ',', '.') }}</h4>
                    <small class="text-muted">Akumulasi kuantitas item</small>
                </div>
            </div>
        </div>
        @if($canViewFinancial)
            <div class="col-sm-6 col-xl-6">
                <div class="card h-100">
                    <div class="card-body">
                        <span class="text-heading">Total Nilai Pembelian</span>
                        <h4 class="my-1 text-primary">Rp {{ number_format($summary['total_amount'], 0, ',', '.') }}</h4>
                        <small class="text-muted">Periode {{ \Illuminate\Support\Carbon::parse($startDate)->format('d M Y') }} s/d {{ \Illuminate\Support\Carbon::parse($endDate)->format('d M Y') }}</small>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="card">
        <div class="card-header border-bottom no-print">
            <form method="GET" action="{{ route('reports.purchases') }}" class="row g-3 align-items-end report-filter-form">
                <div class="col-md-2">
                    <label class="form-label mb-1">Tanggal Awal</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="form-control" onchange="this.form.submit()">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Tanggal Akhir</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="form-control" onchange="this.form.submit()">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Asal Barang</label>
                    <select name="source_type" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Asal</option>
                        <option value="supplier" @selected($sourceType === 'supplier')>Supplier</option>
                        <option value="farmer" @selected($sourceType === 'farmer')>Petani</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Gudang Tujuan</label>
                    <select name="warehouse_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Gudang</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" @selected($warehouseId === $w->id)>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Pencarian</label>
                    <div class="d-flex gap-1">
                        <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Nomor / supplier / petani">
                        @if($sourceType || $warehouseId || $search)
                            <a href="{{ route('reports.purchases') }}" class="btn btn-label-secondary">Reset</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nomor</th>
                        <th>Tanggal</th>
                        <th>Asal Barang</th>
                        <th>Rincian Barang</th>
                        <th class="text-end">Total Volume</th>
                        @if($canViewFinancial)
                            <th class="text-end">Total Nilai</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $purchase)
                        @php
                            $sourceName = $purchase->supplier?->name ?? $purchase->farmer?->name ?? '-';
                            $sourceLabel = $purchase->supplier ? 'Supplier' : ($purchase->farmer ? 'Petani' : '-');
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('purchases.show', $purchase) }}" class="fw-medium">{{ $purchase->number }}</a>
                            </td>
                            <td>{{ $purchase->purchase_date?->format('d M Y') }}</td>
                            <td>
                                <span class="fw-medium">{{ $sourceName }}</span>
                                <div class="text-muted small">{{ $sourceLabel }}</div>
                            </td>
                            <td class="text-wrap" style="min-width: 240px;">
                                @foreach($purchase->items as $detail)
                                    <div class="small">
                                        {{ $detail->item?->name ?? '-' }} ({{ number_format((float) $detail->quantity, 2, ',', '.') }} {{ strtoupper((string) ($detail->item?->unit ?? '')) }})
                                    </div>
                                @endforeach
                            </td>
                            <td class="text-end fw-medium">{{ number_format((float) $purchase->items->sum('quantity'), 2, ',', '.') }}</td>
                            @if($canViewFinancial)
                                <td class="text-end fw-medium">Rp {{ number_format((float) $purchase->total_amount, 0, ',', '.') }}</td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canViewFinancial ? 6 : 5 }}" class="text-center text-muted py-5">Tidak ada transaksi pembelian pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if($purchases->isNotEmpty())
                    <tfoot class="table-border-bottom-0">
                        <tr>
                            <th colspan="4" class="text-end">Total Keseluruhan</th>
                            <th class="text-end">{{ number_format($summary['total_qty'], 2, ',', '.') }}</th>
                            @if($canViewFinancial)
                                <th class="text-end">Rp {{ number_format($summary['total_amount'], 0, ',', '.') }}</th>
                            @endif
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <div class="report-print-footer">
        <div class="report-signature-box">
            <div>Penanggung Jawab,</div>
            <div class="report-signature-line">{{ auth()->user()?->name ?: 'Admin' }}</div>
        </div>
    </div>
</div>
@endsection
