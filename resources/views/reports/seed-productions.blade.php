@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @include('reports._print_styles')
    <div class="report-print-header" style="display: none;">
        @include('partials.print-letterhead', [
            'wrapperClass' => 'w-100',
            'documentTitle' => 'Laporan Produksi, Rendemen & HPP Benih',
            'documentSubtitle' => 'Periode: '.\Illuminate\Support\Carbon::parse($startDate)->format('d M Y').' s/d '.\Illuminate\Support\Carbon::parse($endDate)->format('d M Y'),
            'documentMeta' => 'Dicetak: '.now()->format('d M Y H:i'),
        ])
    </div>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4 no-print">
        <div>
            <h4 class="mb-1">Laporan Produksi, Rendemen & HPP Benih</h4>
            <p class="text-muted mb-0">Rekap pemakaian bahan baku, hasil bersih benih jadi, persentase rendemen, dan estimasi HPP per Kg.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reports.seed-productions', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn btn-outline-success">
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
                    <span class="text-heading">Total Batch Produksi</span>
                    <h4 class="my-1">{{ number_format($summary['count'], 0, ',', '.') }}</h4>
                    <small class="text-muted">Bahan utama: {{ number_format($summary['total_main_input'], 2, ',', '.') }} Kg</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-heading">Total Benih Jadi</span>
                    <h4 class="my-1 text-success">{{ number_format($summary['total_output'], 2, ',', '.') }} Kg</h4>
                    <small class="text-muted">Hasil bersih masuk gudang</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-heading">Rata-rata Rendemen</span>
                    <h4 class="my-1 text-info">{{ number_format($summary['avg_yield'], 1, ',', '.') }}%</h4>
                    <small class="text-muted">Hasil bersih &divide; bahan utama</small>
                </div>
            </div>
        </div>
        @if($canViewFinancial)
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <span class="text-heading">Rata-rata HPP / Kg</span>
                        <h4 class="my-1 text-primary">
                            {{ $summary['avg_hpp'] !== null ? 'Rp '.number_format($summary['avg_hpp'], 0, ',', '.') : '-' }}
                        </h4>
                        <small class="text-muted">Dari batch yang sudah ada hasil</small>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="card">
        <div class="card-header border-bottom no-print">
            <form method="GET" action="{{ route('reports.seed-productions') }}" class="row g-3 align-items-end report-filter-form">
                <div class="col-md-2">
                    <label class="form-label mb-1">Tanggal Awal</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="form-control" onchange="this.form.submit()">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Tanggal Akhir</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="form-control" onchange="this.form.submit()">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Varietas</label>
                    <select name="rice_variety_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Varietas</option>
                        @foreach($riceVarieties as $v)
                            <option value="{{ $v->id }}" @selected($riceVarietyId === $v->id)>{{ $v->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Kelas Benih</label>
                    <select name="seed_class_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Kelas</option>
                        @foreach($seedClasses as $c)
                            <option value="{{ $c->id }}" @selected($seedClassId === $c->id)>{{ $c->code }} - {{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Status</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Pencarian</label>
                    <div class="d-flex gap-1">
                        <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Nomor / lot">
                        @if($riceVarietyId || $seedClassId || $status || $search)
                            <a href="{{ route('reports.seed-productions') }}" class="btn btn-label-secondary">Reset</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Batch & Tanggal</th>
                        <th>Varietas & Lot</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Bahan Utama</th>
                        <th class="text-end">Benih Jadi</th>
                        <th class="text-end">Rendemen</th>
                        @if($canViewFinancial)
                            <th class="text-end">Estimasi Bahan</th>
                            <th class="text-end">Biaya Tahap</th>
                            <th class="text-end">HPP / Kg</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($rows as $row)
                        @php
                            $prod = $row['production'];
                            $statusBadge = match($prod->status) {
                                'siap_salur' => 'bg-label-success',
                                'batal' => 'bg-label-danger',
                                default => 'bg-label-primary',
                            };
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('seed-productions.show', $prod) }}" class="fw-medium">{{ $prod->number }}</a>
                                <div class="text-muted small">{{ $prod->production_date?->format('d M Y') }}</div>
                            </td>
                            <td>
                                <span class="fw-medium">{{ $prod->riceVariety?->name ?? '-' }}</span>
                                <div class="text-muted small">{{ $prod->seedClass?->code ?? '-' }} &middot; Lot: {{ $prod->lot_number ?: '-' }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $statusBadge }}">{{ $statuses[$prod->status] ?? $prod->status }}</span>
                            </td>
                            <td class="text-end">{{ number_format($row['main_input_qty'], 2, ',', '.') }} {{ strtoupper((string) $prod->unit) }}</td>
                            <td class="text-end fw-medium">
                                {{ $row['output_qty'] > 0 ? number_format($row['output_qty'], 2, ',', '.').' '.strtoupper((string) $prod->unit) : '-' }}
                            </td>
                            <td class="text-end">
                                {{ $row['yield_percent'] !== null ? number_format($row['yield_percent'], 1, ',', '.').'%' : '-' }}
                            </td>
                            @if($canViewFinancial)
                                <td class="text-end">
                                    Rp {{ number_format($row['material_cost'], 0, ',', '.') }}
                                    @if($row['unpriced_inputs'] > 0)
                                        <div class="text-warning small">{{ $row['unpriced_inputs'] }} bahan tanpa harga beli</div>
                                    @endif
                                </td>
                                <td class="text-end">Rp {{ number_format($row['step_cost'], 0, ',', '.') }}</td>
                                <td class="text-end fw-semibold">
                                    @if($row['hpp_per_kg'] !== null)
                                        <span class="badge bg-label-primary">Rp {{ number_format($row['hpp_per_kg'], 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canViewFinancial ? 9 : 6 }}" class="text-center text-muted py-5">Tidak ada data produksi pada periode ini.</td>
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
