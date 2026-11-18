@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @php
        $badge = match($seedGrowing->status) {
            'berjalan' => 'primary',
            'panen' => 'info',
            'selesai' => 'success',
            'batal' => 'danger',
            default => 'secondary',
        };
        $totalInspections = count($inspectionStages);
        $completedInspections = $seedGrowing->inspections->where('status', 'selesai')->count();
        $inspectionProgress = $totalInspections > 0 ? (int) round(($completedInspections / $totalInspections) * 100) : 0;
        $totalInspectionCost = (float) $seedGrowing->inspections->sum('cost');
    @endphp

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
        <div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                <h4 class="mb-0">Detail Penangkaran Benih</h4>
                <span class="badge bg-label-{{ $badge }}">{{ $statuses[$seedGrowing->status] ?? $seedGrowing->status }}</span>
                <span class="badge bg-label-primary">{{ $seedGrowing->field_number }}</span>
                <span class="badge bg-label-secondary">Musim {{ $seedGrowing->season_year }}</span>
            </div>
            <p class="text-muted mb-0">
                {{ $seedGrowing->number }} • {{ $seedGrowing->riceVariety?->name ?? '-' }} ({{ $seedGrowing->seedClass?->name ?? '-' }})
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('seed-growings.index') }}" class="btn btn-label-secondary">
                <i class="icon-base bx bx-arrow-back me-1"></i> Kembali
            </a>
            @if($seedGrowing->status === 'draft')
                <form method="POST" action="{{ route('seed-growings.status.update', $seedGrowing) }}" class="d-inline" data-confirm-delete data-confirm-title="Batalkan penangkaran?" data-confirm-text="Status akan berubah menjadi Batal dan stok benih sumber dikembalikan.">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="batal">
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="icon-base bx bx-x-circle me-1"></i> Batalkan
                    </button>
                </form>
            @endif
            <a href="{{ route('seed-growings.create') }}" class="btn btn-primary">
                <i class="icon-base bx bx-plus me-1"></i> Tambah
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="fw-medium mb-1">Periksa kembali isian berikut:</div>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">Luas Penangkaran</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ number_format((float) $seedGrowing->field_area, 2, ',', '.') }} ha</h4>
                            </div>
                            <small class="mb-0 text-muted">{{ $seedGrowing->farmer?->name ?? '-' }} • {{ $seedGrowing->land?->name ?? '-' }}</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="icon-base bx bx-map-alt icon-lg"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">Benih Sumber</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ number_format((float) $seedGrowing->source_seed_quantity, 2, ',', '.') }} {{ strtoupper($seedGrowing->sourceSeedItem?->unit ?? '') }}</h4>
                            </div>
                            <small class="mb-0 text-muted">{{ $seedGrowing->sourceSeedItem?->name ?? '-' }}</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="icon-base bx bx-leaf icon-lg"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left w-100 me-3">
                            <span class="text-heading">Pemeriksaan Lapang</span>
                            <div class="d-flex align-items-center justify-content-between my-1">
                                <h4 class="mb-0">{{ $completedInspections }} / {{ $totalInspections }}</h4>
                                <small class="text-muted">{{ $inspectionProgress }}%</small>
                            </div>
                            <div class="progress mb-1" style="height: 6px;">
                                <div class="progress-bar bg-info" role="progressbar" style="width: {{ $inspectionProgress }}%" aria-valuenow="{{ $inspectionProgress }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small class="mb-0 text-muted">Biaya: Rp {{ number_format($totalInspectionCost, 0, ',', '.') }}</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="icon-base bx bx-check-shield icon-lg"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">Hasil Panen</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">
                                    @if($seedGrowing->harvest)
                                        {{ number_format((float) $seedGrowing->harvest->harvested_quantity, 2, ',', '.') }} {{ strtoupper($seedGrowing->harvest->unit) }}
                                    @else
                                        Belum Panen
                                    @endif
                                </h4>
                            </div>
                            <small class="mb-0 text-muted">Lot: {{ $seedGrowing->lot_number ?: '-' }}</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="icon-base bx bx-package icon-lg"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="mb-0">Informasi Lapangan</h5>
                    <span class="badge bg-label-{{ $badge }}">{{ $statuses[$seedGrowing->status] ?? $seedGrowing->status }}</span>
                </div>
                <div class="card-body pt-4">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Nomor Dokumen</small>
                            <span class="fw-medium">{{ $seedGrowing->number }}</span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">No Lapangan & Lot</small>
                            <span class="fw-medium">{{ $seedGrowing->field_number }}</span>
                            <span class="text-muted">• Lot {{ $seedGrowing->lot_number ?: '-' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Petani</small>
                            <span class="fw-medium">{{ $seedGrowing->farmer?->name ?? '-' }}</span>
                            @if($seedGrowing->farmer?->code)
                                <div class="text-muted small">{{ $seedGrowing->farmer->code }}</div>
                            @endif
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Lahan & Luas</small>
                            <span class="fw-medium">{{ $seedGrowing->land?->name ?? '-' }}</span>
                            <div class="text-muted small">{{ number_format((float) $seedGrowing->field_area, 2, ',', '.') }} ha (Musim {{ $seedGrowing->season_year }})</div>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Varietas Padi</small>
                            <span class="fw-medium">{{ $seedGrowing->riceVariety?->name ?? '-' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Kelas Benih Tujuan</small>
                            <span class="fw-medium">{{ $seedGrowing->seedClass?->name ?? '-' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Dibuat Oleh</small>
                            <span class="fw-medium">{{ $seedGrowing->creator?->name ?? '-' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Keterangan Status</small>
                            @if($seedGrowing->status === 'batal')
                                <span class="text-danger small">Penangkaran ini sudah dibatalkan.</span>
                            @elseif($seedGrowing->status !== 'draft')
                                <span class="text-muted small">Penangkaran sedang berjalan / selesai.</span>
                            @else
                                <span class="text-muted small">Masih berstatus Draft (dapat dibatalkan).</span>
                            @endif
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block">Catatan</small>
                            <span>{{ $seedGrowing->notes ?: '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header border-bottom">
                    <h5 class="mb-0">Benih Sumber & Tanggal Dasar</h5>
                </div>
                <div class="card-body pt-4">
                    <div class="p-3 rounded bg-lighter mb-4 border">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-medium">{{ $seedGrowing->sourceSeedItem?->name ?? '-' }}</span>
                            <span class="badge bg-label-warning">{{ $seedGrowing->sourceSeedItem?->code ?? '-' }}</span>
                        </div>
                        <div class="d-flex justify-content-between text-muted small">
                            <span>Gudang: <strong class="text-body">{{ $seedGrowing->sourceSeedWarehouse?->name ?? '-' }}</strong></span>
                            <span>Jumlah: <strong class="text-body">{{ number_format((float) $seedGrowing->source_seed_quantity, 2, ',', '.') }} {{ strtoupper($seedGrowing->sourceSeedItem?->unit ?? '') }}</strong></span>
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-secondary">
                                    <i class="icon-base bx bx-calendar"></i>
                                </span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div>
                                    <h6 class="mb-0">Tanggal Sebar</h6>
                                    <small class="text-muted">Persemaian benih</small>
                                </div>
                                <span class="fw-medium">{{ $seedGrowing->sowing_date?->format('d M Y') ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="icon-base bx bx-spa"></i>
                                </span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div>
                                    <h6 class="mb-0">Tanggal Tanam</h6>
                                    <small class="text-muted">Acuan jadwal pemeriksaan</small>
                                </div>
                                <span class="fw-medium">{{ $seedGrowing->planting_date?->format('d M Y') ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="icon-base bx bx-check-circle"></i>
                                </span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div>
                                    <h6 class="mb-0">Tanggal Panen</h6>
                                    <small class="text-muted">{{ $seedGrowing->harvest ? 'Realisasi panen' : 'Estimasi (+105 hari)' }}</small>
                                </div>
                                <span class="fw-medium">{{ $seedGrowing->harvest_date?->format('d M Y') ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 border-bottom">
                    <div>
                        <h5 class="mb-0">Jadwal & Biaya Pemeriksaan Lapang</h5>
                        <small class="text-muted">Perbarui tanggal aktual, biaya, dan status pada setiap tahapan pemeriksaan.</small>
                    </div>
                    <div class="d-flex gap-2">
                        <span class="badge bg-label-info">{{ $completedInspections }}/{{ $totalInspections }} Selesai</span>
                        <span class="badge bg-label-primary">Total: Rp {{ number_format($totalInspectionCost, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Tahap</th>
                                <th style="min-width: 160px;">Tanggal Rencana</th>
                                <th style="min-width: 160px;">Tanggal Aktual</th>
                                <th style="min-width: 180px;">Biaya</th>
                                <th style="min-width: 150px;">Status</th>
                                <th style="min-width: 220px;">Catatan</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inspectionStages as $stage => $config)
                                @php
                                    $inspection = $seedGrowing->inspections->firstWhere('stage', $stage);
                                @endphp
                                @if($inspection)
                                    @php
                                        $formId = 'inspection-form-'.$inspection->id;
                                        $isOldRow = old('inspection_id') === $inspection->id;
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge badge-center rounded-pill {{ $inspection->status === 'selesai' ? 'bg-label-success' : 'bg-label-secondary' }}">
                                                    {{ $loop->iteration }}
                                                </span>
                                                <div>
                                                    <span class="fw-medium d-block">{{ $config['label'] }}</span>
                                                    <small class="text-muted">H+{{ $config['offset'] }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <input
                                                type="date"
                                                name="planned_date"
                                                value="{{ $isOldRow ? old('planned_date') : $inspection->planned_date?->toDateString() }}"
                                                class="form-control form-control-sm"
                                                form="{{ $formId }}"
                                                @disabled($seedGrowing->status === 'batal')
                                            >
                                        </td>
                                        <td>
                                            <input
                                                type="date"
                                                name="actual_date"
                                                value="{{ $isOldRow ? old('actual_date') : $inspection->actual_date?->toDateString() }}"
                                                class="form-control form-control-sm"
                                                form="{{ $formId }}"
                                                @disabled($seedGrowing->status === 'batal')
                                            >
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">Rp</span>
                                                <input
                                                    type="text"
                                                    inputmode="numeric"
                                                    name="cost"
                                                    value="{{ $isOldRow ? old('cost') : number_format((float) $inspection->cost, 0, ',', '.') }}"
                                                    class="form-control rupiah-input"
                                                    form="{{ $formId }}"
                                                    placeholder="0"
                                                    @disabled($seedGrowing->status === 'batal')
                                                >
                                            </div>
                                        </td>
                                        <td>
                                            <select name="status" class="form-select form-select-sm" form="{{ $formId }}" @disabled($seedGrowing->status === 'batal')>
                                                @foreach($inspectionStatuses as $value => $label)
                                                    <option value="{{ $value }}" @selected(($isOldRow ? old('status') : $inspection->status) === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input
                                                type="text"
                                                name="notes"
                                                value="{{ $isOldRow ? old('notes') : $inspection->notes }}"
                                                class="form-control form-control-sm"
                                                placeholder="Catatan"
                                                form="{{ $formId }}"
                                                @disabled($seedGrowing->status === 'batal')
                                            >
                                        </td>
                                        <td class="text-end">
                                            <form id="{{ $formId }}" method="POST" action="{{ route('seed-growings.inspections.update', [$seedGrowing, $inspection]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="inspection_id" value="{{ $inspection->id }}">
                                            </form>
                                            <button type="submit" class="btn btn-sm btn-outline-primary" form="{{ $formId }}" @disabled($seedGrowing->status === 'batal')>
                                                <span class="icon-base bx bx-save icon-sm me-1"></span>Simpan
                                            </button>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                        <tfoot class="table-border-bottom-0">
                            <tr>
                                <th colspan="3" class="text-end">Total Biaya</th>
                                <th colspan="4">Rp {{ number_format((float) $seedGrowing->inspections->sum('cost'), 2, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 border-bottom">
                    <div>
                        <h5 class="mb-0">Panen</h5>
                        <small class="text-muted">Hasil panen yang disimpan akan otomatis masuk ke saldo stok gudang tujuan.</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @if($seedGrowing->harvest)
                            <span class="badge bg-label-success">Sudah Tercatat ({{ $harvestStatuses[$seedGrowing->harvest->status] ?? $seedGrowing->harvest->status }})</span>
                            @if($seedGrowing->harvest->stock_id)
                                <a href="{{ route('stocks.show', $seedGrowing->harvest->stock_id) }}" class="btn btn-sm btn-outline-primary">
                                    <span class="icon-base bx bx-list-ul icon-sm me-1"></span>Kartu Stok
                                </a>
                            @endif
                        @else
                            <span class="badge bg-label-secondary">Belum Tercatat</span>
                        @endif
                    </div>
                </div>
                <form method="POST" action="{{ route('seed-growings.harvest.update', $seedGrowing) }}">
                    @csrf
                    @method('PATCH')
                    <div class="card-body">
                        @if($seedGrowing->status === 'batal')
                            <div class="alert alert-warning">
                                Panen tidak bisa disimpan karena penangkaran sudah batal.
                            </div>
                        @endif
                        @php
                            $isHarvestOld = ! old('inspection_id');
                        @endphp
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label for="harvest_item_id" class="form-label">Barang Hasil Panen <span class="text-danger">*</span></label>
                                @if($harvestItems->isEmpty())
                                    <div class="alert alert-warning mb-2 py-2">
                                        Belum ada barang hasil panen untuk varietas {{ $seedGrowing->riceVariety?->name ?? '-' }}.
                                    </div>
                                    <a
                                        href="{{ route('master.items.create', [
                                            'return_to' => 'seed-growings.show',
                                            'seed_growing_id' => $seedGrowing->id,
                                            'category' => 'gabah',
                                            'material_state' => $seedGrowing->harvest?->material_state ?? 'basah',
                                            'unit' => $seedGrowing->harvest?->unit ?? 'kg',
                                            'rice_variety_id' => $seedGrowing->rice_variety_id,
                                            'seed_class_id' => $seedGrowing->seed_class_id,
                                        ]) }}"
                                        class="btn btn-sm btn-label-primary"
                                    >
                                        <i class="icon-base bx bx-plus me-1"></i> Tambah Barang
                                    </a>
                                @else
                                    <select id="harvest_item_id" name="harvest_item_id" class="form-select @error('harvest_item_id') is-invalid @enderror" required @disabled($seedGrowing->status === 'batal')>
                                        <option value="">Pilih barang</option>
                                        @foreach($harvestItems as $item)
                                            <option
                                                value="{{ $item->id }}"
                                                @selected(old('harvest_item_id', $seedGrowing->harvest?->harvest_item_id ?? ($harvestItems->count() === 1 ? $item->id : '')) === $item->id)
                                            >
                                                {{ $item->code }} - {{ $item->name }} ({{ strtoupper($item->unit) }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('harvest_item_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @endif
                            </div>
                            <div class="col-md-4">
                                <label for="harvest_warehouse_id" class="form-label">Gudang Tujuan <span class="text-danger">*</span></label>
                                <select id="harvest_warehouse_id" name="harvest_warehouse_id" class="form-select @error('harvest_warehouse_id') is-invalid @enderror" required @disabled($seedGrowing->status === 'batal')>
                                    <option value="">Pilih gudang</option>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" @selected(old('harvest_warehouse_id', $seedGrowing->harvest?->harvest_warehouse_id) === $warehouse->id)>
                                            {{ $warehouse->code }} - {{ $warehouse->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('harvest_warehouse_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="harvest_lot_number" class="form-label">No Lot</label>
                                <input
                                    type="text"
                                    id="harvest_lot_number"
                                    name="lot_number"
                                    value="{{ old('lot_number', $seedGrowing->lot_number) }}"
                                    class="form-control @error('lot_number') is-invalid @enderror"
                                    maxlength="100"
                                    placeholder="Contoh: LOT-IR64-001"
                                    @disabled($seedGrowing->status === 'batal')
                                >
                                @error('lot_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="harvest_date" class="form-label">Tanggal Panen Aktual <span class="text-danger">*</span></label>
                                <input
                                    type="date"
                                    id="harvest_date"
                                    name="harvest_date"
                                    value="{{ old('harvest_date', $seedGrowing->harvest?->harvest_date?->toDateString() ?? $seedGrowing->harvest_date?->toDateString()) }}"
                                    class="form-control @error('harvest_date') is-invalid @enderror"
                                    required
                                    @disabled($seedGrowing->status === 'batal')
                                >
                                @error('harvest_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="harvested_quantity" class="form-label">Jumlah Panen <span class="text-danger">*</span></label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    id="harvested_quantity"
                                    name="harvested_quantity"
                                    value="{{ old('harvested_quantity', $seedGrowing->harvest?->harvested_quantity) }}"
                                    class="form-control @error('harvested_quantity') is-invalid @enderror"
                                    required
                                    @disabled($seedGrowing->status === 'batal')
                                >
                                @error('harvested_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-2">
                                <label for="unit" class="form-label">Satuan <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    id="unit"
                                    name="unit"
                                    value="{{ old('unit', $seedGrowing->harvest?->unit ?? 'kg') }}"
                                    class="form-control @error('unit') is-invalid @enderror"
                                    required
                                    @disabled($seedGrowing->status === 'batal')
                                >
                                @error('unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-2">
                                <label for="material_state" class="form-label">Kondisi <span class="text-danger">*</span></label>
                                <select id="material_state" name="material_state" class="form-select @error('material_state') is-invalid @enderror" required @disabled($seedGrowing->status === 'batal')>
                                    @foreach($harvestMaterialStates as $value => $label)
                                        <option value="{{ $value }}" @selected(old('material_state', $seedGrowing->harvest?->material_state ?? 'basah') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('material_state') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-2">
                                <label for="harvest_status" class="form-label">Status Panen <span class="text-danger">*</span></label>
                                <select id="harvest_status" name="status" class="form-select" required @disabled($seedGrowing->status === 'batal')>
                                    @foreach($harvestStatuses as $value => $label)
                                        <option value="{{ $value }}" @selected(($isHarvestOld ? old('status', $seedGrowing->harvest?->status ?? 'panen') : ($seedGrowing->harvest?->status ?? 'panen')) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="harvest_notes" class="form-label">Catatan Panen</label>
                                <textarea id="harvest_notes" name="notes" rows="3" class="form-control" @disabled($seedGrowing->status === 'batal')>{{ $isHarvestOld ? old('notes', $seedGrowing->harvest?->notes) : $seedGrowing->harvest?->notes }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary" @disabled($seedGrowing->status === 'batal')>
                            <i class="icon-base bx bx-save me-1"></i> Simpan Panen
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const rupiahInputs = document.querySelectorAll('.rupiah-input');

        function formatRupiah(value) {
            const digits = String(value || '').replace(/\D/g, '');

            return digits ? Number(digits).toLocaleString('id-ID') : '';
        }

        rupiahInputs.forEach(function (input) {
            input.value = formatRupiah(input.value);

            input.addEventListener('input', function () {
                input.value = formatRupiah(input.value);
            });
        });
    });
</script>
@endpush
