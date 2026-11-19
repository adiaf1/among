@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @include('reports._print_styles')
    <div class="report-print-header" style="display: none;">
        @include('partials.print-letterhead', [
            'wrapperClass' => 'w-100',
            'documentTitle' => 'Laporan Penangkaran & Realisasi Panen',
            'documentSubtitle' => 'Musim/Tahun: '.($seasonYear ?: 'Semua Tahun'),
            'documentMeta' => 'Dicetak: '.now()->format('d M Y H:i'),
        ])
    </div>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4 no-print">
        <div>
            <h4 class="mb-1">Laporan Penangkaran & Realisasi Panen</h4>
            <p class="text-muted mb-0">Rekap luas lahan penangkaran, progres pemeriksaan lapang, hasil panen, dan produktivitas (Kg/Ha).</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reports.seed-growings', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn btn-outline-success">
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
                    <span class="text-heading">Total Penangkaran</span>
                    <h4 class="my-1">{{ number_format($summary['count'], 0, ',', '.') }}</h4>
                    <small class="text-muted">Lahan / nomor penangkaran</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-heading">Total Luas Lahan</span>
                    <h4 class="my-1">{{ number_format($summary['total_area'], 2, ',', '.') }} Ha</h4>
                    <small class="text-muted">Akumulasi luas tanam</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-heading">Total Hasil Panen</span>
                    <h4 class="my-1 text-success">{{ number_format($summary['total_harvest'], 2, ',', '.') }} Kg</h4>
                    <small class="text-muted">Realisasi panen tercatat</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-heading">Rata-rata Produktivitas</span>
                    <h4 class="my-1 text-primary">{{ number_format($summary['avg_productivity'], 2, ',', '.') }} Kg/Ha</h4>
                    <small class="text-muted">Total panen &divide; total luas</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-bottom no-print">
            <form method="GET" action="{{ route('reports.seed-growings') }}" class="row g-3 align-items-end report-filter-form">
                <div class="col-md-2">
                    <label class="form-label mb-1">Tahun Musim</label>
                    <select name="season_year" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Tahun</option>
                        @foreach($seasonYears as $year)
                            <option value="{{ $year }}" @selected((string) $seasonYear === (string) $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
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
                <div class="col-md-3">
                    <label class="form-label mb-1">Pencarian</label>
                    <div class="d-flex gap-1">
                        <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Nomor / lot / petani">
                        @if($seasonYear || $riceVarietyId || $seedClassId || $status || $search)
                            <a href="{{ route('reports.seed-growings') }}" class="btn btn-label-secondary">Reset</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nomor & Tahun</th>
                        <th>Petani & Lahan</th>
                        <th>Varietas & Kelas</th>
                        <th class="text-end">Luas (Ha)</th>
                        <th class="text-center">Pemeriksaan</th>
                        <th class="text-end">Hasil Panen (Kg)</th>
                        <th class="text-end">Produktivitas</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($growings as $growing)
                        @php
                            $area = (float) ($growing->field_area ?? 0);
                            $harvestQty = (float) ($growing->harvest?->harvested_quantity ?? 0);
                            $productivity = ($area > 0 && $harvestQty > 0) ? ($harvestQty / $area) : null;
                            $doneInspections = $growing->inspections->where('status', 'selesai')->count();
                            $totalInspections = $growing->inspections->count();
                            $statusBadge = match($growing->status) {
                                'panen' => 'bg-label-success',
                                'batal' => 'bg-label-danger',
                                default => 'bg-label-primary',
                            };
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('seed-growings.show', $growing) }}" class="fw-medium">{{ $growing->number }}</a>
                                <div class="text-muted small">Tahun {{ $growing->season_year ?? '-' }} &middot; Lot: {{ $growing->lot_number ?: '-' }}</div>
                            </td>
                            <td>
                                <span class="fw-medium">{{ $growing->farmer?->name ?? '-' }}</span>
                                <div class="text-muted small">{{ $growing->land?->name ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="fw-medium">{{ $growing->riceVariety?->name ?? '-' }}</span>
                                <div class="text-muted small">{{ $growing->seedClass?->name ?? '-' }}</div>
                            </td>
                            <td class="text-end">{{ number_format($area, 2, ',', '.') }}</td>
                            <td class="text-center">
                                <span class="badge {{ $doneInspections === $totalInspections && $totalInspections > 0 ? 'bg-label-success' : 'bg-label-info' }}">
                                    {{ $doneInspections }} / {{ $totalInspections }}
                                </span>
                            </td>
                            <td class="text-end fw-medium">
                                {{ $harvestQty > 0 ? number_format($harvestQty, 2, ',', '.') : '-' }}
                            </td>
                            <td class="text-end">
                                {{ $productivity !== null ? number_format($productivity, 2, ',', '.').' Kg/Ha' : '-' }}
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $statusBadge }}">{{ $statuses[$growing->status] ?? $growing->status }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">Tidak ada data penangkaran sesuai filter.</td>
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
