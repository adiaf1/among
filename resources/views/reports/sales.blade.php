@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @include('reports._print_styles')
    <div class="report-print-header">
        <div>
            <h4 class="mb-0">{{ config('app.name') }}</h4>
            <div>Laporan Penjualan / Penyaluran Benih & Piutang</div>
        </div>
        <div class="text-end">
            <div>Periode: {{ \Illuminate\Support\Carbon::parse($startDate)->format('d M Y') }} s/d {{ \Illuminate\Support\Carbon::parse($endDate)->format('d M Y') }}</div>
            <small>Dicetak: {{ now()->format('d M Y H:i') }}</small>
        </div>
    </div>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4 no-print">
        <div>
            <h4 class="mb-1">Laporan Penjualan & Piutang</h4>
            <p class="text-muted mb-0">Rekap penyaluran benih ke pelanggan beserta status pelunasan dan piutang berjalan.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reports.sales', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn btn-outline-success">
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
                    <small class="text-muted">{{ $summary['paid_count'] }} Lunas &middot; {{ $summary['unpaid_count'] }} Belum Lunas</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-heading">Total Volume Terjual</span>
                    <h4 class="my-1">{{ number_format($summary['total_qty'], 2, ',', '.') }}</h4>
                    <small class="text-muted">Total kuantitas keluar</small>
                </div>
            </div>
        </div>
        @if($canViewFinancial)
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <span class="text-heading">Total Omzet</span>
                        <h4 class="my-1 text-success">Rp {{ number_format($summary['total_amount'], 0, ',', '.') }}</h4>
                        <small class="text-muted">Lunas: Rp {{ number_format($summary['paid_amount'], 0, ',', '.') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <span class="text-heading">Total Piutang (Belum Lunas)</span>
                        <h4 class="my-1 text-warning">Rp {{ number_format($summary['unpaid_amount'], 0, ',', '.') }}</h4>
                        <small class="text-muted">{{ $summary['unpaid_count'] }} faktur menunggu bayar</small>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="card">
        <div class="card-header border-bottom no-print">
            <form method="GET" action="{{ route('reports.sales') }}" class="row g-3 align-items-end report-filter-form">
                <div class="col-md-2">
                    <label class="form-label mb-1">Tanggal Awal</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="form-control" onchange="this.form.submit()">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Tanggal Akhir</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="form-control" onchange="this.form.submit()">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Jenis Pelanggan</label>
                    <select name="customer_type" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Jenis</option>
                        @foreach($customerTypes as $key => $label)
                            <option value="{{ $key }}" @selected($customerType === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Status Bayar</label>
                    <select name="payment_status" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        @foreach($paymentStatuses as $key => $label)
                            <option value="{{ $key }}" @selected($paymentStatus === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Pelanggan</label>
                    <select name="customer_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Pelanggan</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" @selected($customerId === $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Pencarian</label>
                    <div class="d-flex gap-1">
                        <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Nomor / pelanggan">
                        @if($customerId || $customerType || $paymentStatus || $search)
                            <a href="{{ route('reports.sales') }}" class="btn btn-label-secondary">Reset</a>
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
                        <th>Pelanggan</th>
                        <th>Rincian Barang & Lot</th>
                        <th>Status Bayar</th>
                        <th class="text-end">Volume</th>
                        @if($canViewFinancial)
                            <th class="text-end">Total</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                        @php
                            $isPaid = $sale->payment_status === 'lunas';
                            $custType = $customerTypes[$sale->customer?->customer_type ?? ''] ?? '-';
                        @endphp
                        <tr>
                            <td><a href="{{ route('sales.show', $sale) }}" class="fw-medium">{{ $sale->number }}</a></td>
                            <td>{{ $sale->sale_date?->format('d M Y') }}</td>
                            <td>
                                <span class="fw-medium">{{ $sale->customer?->name ?? '-' }}</span>
                                <div class="text-muted small">{{ $custType }}</div>
                            </td>
                            <td class="text-wrap" style="min-width: 240px;">
                                @foreach($sale->items as $detail)
                                    <div class="small">
                                        {{ $detail->item?->name ?? '-' }}
                                        @if($detail->lot_number)
                                            <span class="text-muted">[Lot {{ $detail->lot_number }}]</span>
                                        @endif
                                        &mdash; {{ number_format((float) $detail->quantity, 2, ',', '.') }} {{ strtoupper((string) ($detail->item?->unit ?? '')) }}
                                    </div>
                                @endforeach
                            </td>
                            <td>
                                <span class="badge {{ $isPaid ? 'bg-label-success' : 'bg-label-warning' }}">
                                    {{ $paymentStatuses[$sale->payment_status] ?? $sale->payment_status }}
                                </span>
                            </td>
                            <td class="text-end fw-medium">{{ number_format((float) $sale->items->sum('quantity'), 2, ',', '.') }}</td>
                            @if($canViewFinancial)
                                <td class="text-end fw-medium">Rp {{ number_format((float) $sale->total_amount, 0, ',', '.') }}</td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canViewFinancial ? 7 : 6 }}" class="text-center text-muted py-5">Tidak ada transaksi penjualan pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if($sales->isNotEmpty())
                    <tfoot class="table-border-bottom-0">
                        <tr>
                            <th colspan="5" class="text-end">Total Keseluruhan</th>
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
