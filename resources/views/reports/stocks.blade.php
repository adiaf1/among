@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @include('reports._print_styles')
    <div class="report-print-header" style="display: none;">
        @include('partials.print-letterhead', [
            'wrapperClass' => 'w-100',
            'documentTitle' => 'Laporan Rekap & Mutasi Stok',
            'documentSubtitle' => 'Periode: '.\Illuminate\Support\Carbon::parse($startDate)->format('d M Y').' s/d '.\Illuminate\Support\Carbon::parse($endDate)->format('d M Y'),
            'documentMeta' => 'Dicetak: '.now()->format('d M Y H:i'),
        ])
    </div>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4 no-print">
        <div>
            <h4 class="mb-1">Laporan Rekap & Mutasi Stok</h4>
            <p class="text-muted mb-0">Posisi saldo awal, mutasi masuk, keluar, dan saldo akhir stok gudang per periode.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reports.stocks', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn btn-outline-success">
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
                    <span class="text-heading">Total Baris Stok</span>
                    <h4 class="my-1">{{ number_format($summary['total_rows'], 0, ',', '.') }}</h4>
                    <small class="text-muted">Item &times; Gudang &times; Lot</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-heading">Total Masuk Periode</span>
                    <h4 class="my-1 text-success">+{{ number_format($summary['total_in'], 2, ',', '.') }}</h4>
                    <small class="text-muted">Seluruh satuan barang</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-heading">Total Keluar Periode</span>
                    <h4 class="my-1 text-danger">-{{ number_format($summary['total_out'], 2, ',', '.') }}</h4>
                    <small class="text-muted">Seluruh satuan barang</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-heading">Stok Minimum</span>
                    <h4 class="my-1 {{ $summary['low_stock_count'] > 0 ? 'text-danger' : 'text-success' }}">{{ $summary['low_stock_count'] }}</h4>
                    <small class="text-muted">Baris &le; batas minimum</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-bottom no-print">
            <form method="GET" action="{{ route('reports.stocks') }}" class="row g-3 align-items-end report-filter-form">
                <div class="col-md-2">
                    <label class="form-label mb-1">Tanggal Awal</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="form-control" onchange="this.form.submit()">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Tanggal Akhir</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="form-control" onchange="this.form.submit()">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Gudang</label>
                    <select name="warehouse_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Gudang</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" @selected($warehouseId === $w->id)>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Kategori</label>
                    <select name="category" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" @selected($category === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Status Bahan</label>
                    <select name="material_state" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        @foreach($materialStates as $key => $label)
                            <option value="{{ $key }}" @selected($materialState === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Pencarian</label>
                    <div class="d-flex gap-1">
                        <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Barang / lot">
                        @if($warehouseId || $category || $materialState || $search)
                            <a href="{{ route('reports.stocks') }}" class="btn btn-label-secondary" title="Reset">Reset</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Gudang</th>
                        <th>No Lot</th>
                        <th class="text-end">Saldo Awal</th>
                        <th class="text-end">Masuk</th>
                        <th class="text-end">Keluar</th>
                        <th class="text-end">Saldo Akhir</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($rows as $row)
                        @php
                            $stock = $row['stock'];
                            $unit = strtoupper((string) ($stock->item?->unit ?? ''));
                        @endphp
                        <tr>
                            <td>
                                <span class="fw-medium">{{ $stock->item?->name ?? '-' }}</span>
                                <div class="text-muted small">{{ $stock->item?->code ?? '-' }} &middot; {{ $categories[$stock->item?->category ?? ''] ?? '-' }}</div>
                            </td>
                            <td>{{ $stock->warehouse?->name ?? '-' }}</td>
                            <td>{{ $stock->lot_number ?: '-' }}</td>
                            <td class="text-end">{{ number_format($row['opening_balance'], 2, ',', '.') }} {{ $unit }}</td>
                            <td class="text-end text-success">{{ number_format($row['qty_in'], 2, ',', '.') }}</td>
                            <td class="text-end text-danger">{{ number_format($row['qty_out'], 2, ',', '.') }}</td>
                            <td class="text-end fw-semibold">{{ number_format($row['closing_balance'], 2, ',', '.') }} {{ $unit }}</td>
                            <td class="text-center">
                                @if($row['is_low'])
                                    <span class="badge bg-label-danger">Minimum</span>
                                @else
                                    <span class="badge bg-label-success">Aman</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">Tidak ada data stok sesuai filter.</td>
                        </tr>
                    @endforelse
                </tbody>
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
