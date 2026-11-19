@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @include('reports._print_styles')
    <div class="report-print-header" style="display: none;">
        @include('partials.print-letterhead', [
            'wrapperClass' => 'w-100',
            'documentTitle' => 'Laporan Uji Lab & Sertifikasi BPSB',
            'documentSubtitle' => 'Total Batch Aktif: '.$summary['total'],
            'documentMeta' => 'Dicetak: '.now()->format('d M Y H:i'),
        ])
    </div>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4 no-print">
        <div>
            <h4 class="mb-1">Laporan Uji Lab & Sertifikasi BPSB</h4>
            <p class="text-muted mb-0">Rekap hasil pengujian mutu laboratorium (KA, Kemurnian, Daya Berkecambah), nomor sertifikat LHU, seri label, dan masa edar.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reports.certifications', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn btn-outline-success">
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
                    <span class="text-heading">Total Batch Aktif</span>
                    <h4 class="my-1">{{ $summary['total'] }}</h4>
                    <small class="text-muted">Tidak termasuk batch batal</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-heading">Lulus Uji Lab</span>
                    <h4 class="my-1 text-success">{{ $summary['passed'] }}</h4>
                    <small class="text-muted">Memenuhi standar mutu BPSB</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-heading">Menunggu / Belum Diuji</span>
                    <h4 class="my-1 text-warning">{{ $summary['pending'] }}</h4>
                    <small class="text-muted">Tidak lulus: {{ $summary['failed'] }}</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-heading">Label &le; 30 Hari / Lewat</span>
                    <h4 class="my-1 {{ $summary['expiring'] > 0 ? 'text-danger' : 'text-success' }}">{{ $summary['expiring'] }}</h4>
                    <small class="text-muted">Perlu perhatian masa edar</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-bottom no-print">
            <form method="GET" action="{{ route('reports.certifications') }}" class="row g-3 align-items-end report-filter-form">
                <div class="col-md-2">
                    <label class="form-label mb-1">Status Uji Lab</label>
                    <select name="lab_result" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="belum_diuji" @selected($labResult === 'belum_diuji')>Belum Diuji</option>
                        @foreach($labResults as $key => $label)
                            <option value="{{ $key }}" @selected($labResult === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Masa Edar Label</label>
                    <select name="expiry_status" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Masa Edar</option>
                        <option value="aktif" @selected($expiryStatus === 'aktif')>Aktif (&gt; 30 hari)</option>
                        <option value="segera_kedaluwarsa" @selected($expiryStatus === 'segera_kedaluwarsa')>Segera Kedaluwarsa (&le; 30 hari)</option>
                        <option value="kedaluwarsa" @selected($expiryStatus === 'kedaluwarsa')>Sudah Kedaluwarsa</option>
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
                <div class="col-md-3">
                    <label class="form-label mb-1">Pencarian</label>
                    <div class="d-flex gap-1">
                        <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Lot / LHU / seri label">
                        @if($labResult || $expiryStatus || $riceVarietyId || $seedClassId || $search)
                            <a href="{{ route('reports.certifications') }}" class="btn btn-label-secondary">Reset</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Produksi & Lot</th>
                        <th>Varietas & Kelas</th>
                        <th>Tgl Uji Lab</th>
                        <th class="text-end">KA (%)</th>
                        <th class="text-end">Kemurnian (%)</th>
                        <th class="text-end">DB (%)</th>
                        <th class="text-center">Hasil Lab</th>
                        <th>No Sertifikat / LHU</th>
                        <th>Seri & Jml Label</th>
                        <th>Akhir Masa Edar</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($productions as $prod)
                        @php
                            $labBadge = match($prod->lab_result) {
                                'lulus' => 'bg-label-success',
                                'tidak_lulus' => 'bg-label-danger',
                                'menunggu' => 'bg-label-warning',
                                default => 'bg-label-secondary',
                            };
                            $labLabel = $prod->lab_result
                                ? ($labResults[$prod->lab_result] ?? $prod->lab_result)
                                : 'Belum Diuji';
                            $isExpired = $prod->label_expiry_date && $prod->label_expiry_date->isPast();
                            $isExpiringSoon = $prod->label_expiry_date && ! $isExpired && $prod->label_expiry_date->lte(now()->addDays(30));
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('seed-productions.show', $prod) }}" class="fw-medium">{{ $prod->number }}</a>
                                <div class="text-muted small">Lot: {{ $prod->lot_number ?: '-' }}</div>
                            </td>
                            <td>
                                <span class="fw-medium">{{ $prod->riceVariety?->name ?? '-' }}</span>
                                <div class="text-muted small">{{ $prod->seedClass?->name ?? '-' }}</div>
                            </td>
                            <td>{{ $prod->lab_test_date?->format('d M Y') ?? '-' }}</td>
                            <td class="text-end">{{ $prod->moisture_content !== null ? number_format((float) $prod->moisture_content, 2, ',', '.') : '-' }}</td>
                            <td class="text-end">{{ $prod->purity_percentage !== null ? number_format((float) $prod->purity_percentage, 2, ',', '.') : '-' }}</td>
                            <td class="text-end">{{ $prod->germination_rate !== null ? number_format((float) $prod->germination_rate, 2, ',', '.') : '-' }}</td>
                            <td class="text-center">
                                <span class="badge {{ $labBadge }}">{{ $labLabel }}</span>
                            </td>
                            <td>{{ $prod->certificate_number ?: '-' }}</td>
                            <td>
                                <div>{{ $prod->label_serial_range ?: '-' }}</div>
                                @if($prod->label_count)
                                    <small class="text-muted">{{ number_format((int) $prod->label_count, 0, ',', '.') }} keping</small>
                                @endif
                            </td>
                            <td>
                                @if($prod->label_expiry_date)
                                    <span class="badge {{ $isExpired ? 'bg-label-danger' : ($isExpiringSoon ? 'bg-label-warning' : 'bg-label-success') }}">
                                        {{ $prod->label_expiry_date->format('d M Y') }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-5">Tidak ada data sertifikasi sesuai filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="report-print-footer">
        <div class="report-signature-box">
            <div>Penanggung Jawab Mutu,</div>
            <div class="report-signature-line">{{ auth()->user()?->name ?: 'Admin' }}</div>
        </div>
    </div>
</div>
@endsection
