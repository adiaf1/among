@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @php
        $badge = match($seedProduction->status) {
            'siap_salur' => 'success',
            'batal' => 'danger',
            default => 'primary',
        };
        $mainInputs = $seedProduction->inputs->where('role', 'bahan_utama');
        $supportInputs = $seedProduction->inputs->where('role', '!=', 'bahan_utama');
        $mainInputsQty = (float) $mainInputs->sum('quantity');
        $totalSteps = $seedProduction->steps->count();
        $completedSteps = $seedProduction->steps->where('status', 'selesai')->count();
        $stepProgress = $totalSteps > 0 ? (int) round(($completedSteps / $totalSteps) * 100) : 0;
        $totalStepCost = (float) $seedProduction->steps->sum('cost');
        $isAddInputOld = old('_form_type') === 'add_input';
        $isAddStepOld = old('_form_type') === 'add_step';
    @endphp

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
        <div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                <h4 class="mb-0">Detail Produksi Benih</h4>
                <span class="badge bg-label-{{ $badge }}">{{ $statuses[$seedProduction->status] ?? $seedProduction->status }}</span>
                @if($seedProduction->lot_number)
                    <span class="badge bg-label-info">Lot {{ $seedProduction->lot_number }}</span>
                @endif
            </div>
            <p class="text-muted mb-0">
                {{ $seedProduction->number }} • {{ $seedProduction->riceVariety?->name ?? 'Semua Varietas' }}
                @if($seedProduction->seedClass)
                    ({{ $seedProduction->seedClass->name }})
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('seed-productions.index') }}" class="btn btn-label-secondary">
                <i class="icon-base bx bx-arrow-back me-1"></i> Kembali
            </a>
            <a href="{{ route('seed-productions.create') }}" class="btn btn-primary">
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
                        <div>
                            <span class="text-heading">Bahan Utama Dipakai</span>
                            <h4 class="my-1">{{ number_format($mainInputsQty, 2, ',', '.') }} {{ strtoupper($seedProduction->unit) }}</h4>
                            <small class="text-muted">{{ $mainInputs->count() }} bahan utama • {{ $supportInputs->count() }} pendukung</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="icon-base bx bx-package icon-lg"></i>
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
                        <div>
                            <span class="text-heading">Hasil Benih Jadi</span>
                            <h4 class="my-1">
                                @if($seedProduction->output_quantity)
                                    {{ number_format((float) $seedProduction->output_quantity, 2, ',', '.') }} {{ strtoupper($seedProduction->unit) }}
                                @elseif($seedProduction->target_quantity)
                                    Target {{ number_format((float) $seedProduction->target_quantity, 2, ',', '.') }} {{ strtoupper($seedProduction->unit) }}
                                @else
                                    Belum Dicatat
                                @endif
                            </h4>
                            <small class="text-muted">Gudang: {{ $seedProduction->outputWarehouse?->name ?? '-' }}</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="icon-base bx bx-bullseye icon-lg"></i>
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
                        <div class="w-100 me-3">
                            <span class="text-heading">Progres Tahapan</span>
                            <div class="d-flex align-items-center justify-content-between my-1">
                                <h4 class="mb-0">{{ $completedSteps }} / {{ $totalSteps }}</h4>
                                <small class="text-muted">{{ $stepProgress }}%</small>
                            </div>
                            <div class="progress mb-1" style="height: 6px;">
                                <div class="progress-bar bg-info" role="progressbar" style="width: {{ $stepProgress }}%" aria-valuenow="{{ $stepProgress }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small class="text-muted">Tahap selesai dikerjakan</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="icon-base bx bx-list-check icon-lg"></i>
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
                        <div>
                            <span class="text-heading">Total Biaya Tahap</span>
                            <h4 class="my-1">Rp {{ number_format($totalStepCost, 0, ',', '.') }}</h4>
                            <small class="text-muted">Akumulasi {{ $totalSteps }} tahapan produksi</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="icon-base bx bx-wallet icon-lg"></i>
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
                    <h5 class="mb-0">Informasi Produksi</h5>
                    <span class="badge bg-label-{{ $badge }}">{{ $statuses[$seedProduction->status] ?? $seedProduction->status }}</span>
                </div>
                <div class="card-body pt-4">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Nomor Dokumen</small>
                            <span class="fw-medium">{{ $seedProduction->number }}</span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Tanggal Produksi</small>
                            <span class="fw-medium">{{ $seedProduction->production_date?->format('d M Y') ?? '-' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">No Lot</small>
                            <span class="fw-medium">{{ $seedProduction->lot_number ?: '-' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Gudang Hasil</small>
                            <span class="fw-medium">{{ $seedProduction->outputWarehouse?->name ?? '-' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Varietas Padi</small>
                            <span class="fw-medium">{{ $seedProduction->riceVariety?->name ?? '-' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Kelas Benih Tujuan</small>
                            <span class="fw-medium">{{ $seedProduction->seedClass?->name ?? '-' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Target Hasil</small>
                            <span class="fw-medium">
                                @if($seedProduction->target_quantity)
                                    {{ number_format((float) $seedProduction->target_quantity, 2, ',', '.') }} {{ strtoupper($seedProduction->unit) }}
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Dibuat Oleh</small>
                            <span class="fw-medium">{{ $seedProduction->creator?->name ?? '-' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">No Induk Sertifikasi / LHU</small>
                            <span class="fw-medium">{{ $seedProduction->certificate_number ?: '-' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Masa Edar Label</small>
                            <span class="fw-medium">
                                @if($seedProduction->label_expiry_date)
                                    s/d {{ $seedProduction->label_expiry_date->format('d M Y') }}
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block">Catatan</small>
                            <span>{{ $seedProduction->notes ?: '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header border-bottom">
                    <h5 class="mb-0">Ringkasan Bahan & Biaya</h5>
                </div>
                <div class="card-body pt-4">
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="icon-base bx bx-leaf"></i>
                                </span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div>
                                    <h6 class="mb-0">Bahan Utama</h6>
                                    <small class="text-muted">{{ $mainInputs->count() }} baris bahan</small>
                                </div>
                                <span class="fw-medium">{{ number_format($mainInputsQty, 2, ',', '.') }} {{ strtoupper($seedProduction->unit) }}</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-secondary">
                                    <i class="icon-base bx bx-box"></i>
                                </span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div>
                                    <h6 class="mb-0">Bahan Pendukung / Kemasan</h6>
                                    <small class="text-muted">Karung, plastik, benang, dll.</small>
                                </div>
                                <span class="fw-medium">{{ $supportInputs->count() }} baris</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class="icon-base bx bx-layer"></i>
                                </span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div>
                                    <h6 class="mb-0">Total Baris Bahan</h6>
                                    <small class="text-muted">Seluruh input produksi</small>
                                </div>
                                <span class="fw-medium">{{ $seedProduction->inputs->count() }} baris</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="icon-base bx bx-money"></i>
                                </span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div>
                                    <h6 class="mb-0">Total Biaya Tahapan</h6>
                                    <small class="text-muted">{{ $completedSteps }} dari {{ $totalSteps }} tahap selesai</small>
                                </div>
                                <span class="fw-medium">Rp {{ number_format($totalStepCost, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom d-flex flex-column gap-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Bahan Produksi</h5>
                            <small class="text-muted">Daftar bahan baku dan bahan pendukung yang diambil dari stok gudang.</small>
                        </div>
                        <span class="badge bg-label-primary">{{ $seedProduction->inputs->count() }} Baris</span>
                    </div>
                    @if($seedProduction->status === 'proses')
                        <form method="POST" action="{{ route('seed-productions.inputs.store', $seedProduction) }}" class="row g-2 align-items-end pt-2 border-top">
                            @csrf
                            <input type="hidden" name="_form_type" value="add_input">
                            <div class="col-6 col-md-2">
                                <label class="form-label small mb-1">Tanggal</label>
                                <input
                                    type="date"
                                    name="movement_date"
                                    value="{{ $isAddInputOld ? old('movement_date', now()->toDateString()) : now()->toDateString() }}"
                                    class="form-control form-control-sm @error('movement_date') is-invalid @enderror"
                                    required
                                >
                                @error('movement_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small mb-1">Peran</label>
                                <select
                                    name="role"
                                    id="additional-production-input-role"
                                    class="form-select form-select-sm @error('role') is-invalid @enderror"
                                    required
                                >
                                    @foreach($inputRoles as $value => $label)
                                        <option value="{{ $value }}" @selected(($isAddInputOld ? old('role', 'pendukung') : 'pendukung') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label small mb-1">Stok Bahan</label>
                                <select
                                    name="stock_id"
                                    id="additional-production-input-stock"
                                    class="form-select form-select-sm @error('stock_id') is-invalid @enderror"
                                    data-selected="{{ $isAddInputOld ? old('stock_id') : '' }}"
                                    required
                                >
                                    <option value="">Pilih stok bahan</option>
                                </select>
                                @error('stock_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small mb-1">Jumlah</label>
                                <input
                                    type="number"
                                    name="quantity"
                                    value="{{ $isAddInputOld ? old('quantity') : '' }}"
                                    min="0.01"
                                    max="9999999999.99"
                                    step="0.01"
                                    class="form-control form-control-sm @error('quantity') is-invalid @enderror"
                                    placeholder="0"
                                    required
                                >
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6 col-md-1">
                                <label class="form-label small mb-1">Catatan</label>
                                <input
                                    type="text"
                                    name="notes"
                                    value="{{ $isAddInputOld ? old('notes') : '' }}"
                                    class="form-control form-control-sm @error('notes') is-invalid @enderror"
                                    placeholder="-"
                                >
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 col-md-1 d-grid">
                                <button type="submit" class="btn btn-sm btn-primary" title="Tambah Bahan">
                                    <i class="icon-base bx bx-plus"></i>
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width: 48px;" class="text-center">No</th>
                                <th>Barang</th>
                                <th>Gudang</th>
                                <th>Peran</th>
                                <th class="text-end">Jumlah</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @foreach($seedProduction->inputs as $input)
                                <tr>
                                    <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                    <td>
                                        <span class="fw-medium">{{ $input->item?->name ?? '-' }}</span>
                                        <div class="text-muted small">{{ $input->item?->code ?? '-' }}</div>
                                    </td>
                                    <td>{{ $input->warehouse?->name ?? '-' }}</td>
                                    <td>
                                        <span class="badge {{ $input->role === 'bahan_utama' ? 'bg-label-primary' : 'bg-label-secondary' }}">
                                            {{ $inputRoles[$input->role] ?? $input->role }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-medium">{{ number_format((float) $input->quantity, 2, ',', '.') }} {{ strtoupper($input->unit) }}</td>
                                    <td>{{ $input->notes ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom d-flex flex-column flex-lg-row justify-content-between gap-3">
                    <div>
                        <h5 class="mb-0">Tahapan Produksi</h5>
                        <small class="text-muted">Kelola urutan tahapan pengolahan, realisasi berat (kg), dan biaya per tahap.</small>
                    </div>
                    <form method="POST" action="{{ route('seed-productions.steps.store', $seedProduction) }}" class="row g-2 align-items-end">
                        @csrf
                        <input type="hidden" name="_form_type" value="add_step">
                        <div class="col-12 col-md-3">
                            <label class="form-label small mb-1">Tahap</label>
                            <input
                                type="text"
                                name="label"
                                value="{{ $isAddStepOld ? old('label') : '' }}"
                                class="form-control form-control-sm"
                                list="production-step-label-options"
                                placeholder="Nama tahap"
                                required
                            >
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-1">Tanggal</label>
                            <input
                                type="date"
                                name="planned_date"
                                value="{{ $isAddStepOld ? old('planned_date') : '' }}"
                                class="form-control form-control-sm"
                            >
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-1">Posisi</label>
                            <select name="position" class="form-select form-select-sm">
                                @foreach($stepPositions as $value => $label)
                                    <option value="{{ $value }}" @selected(($isAddStepOld ? old('position', 'end') : 'end') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-1">Acuan</label>
                            <select name="reference_step_id" class="form-select form-select-sm">
                                <option value="">-</option>
                                @foreach($seedProduction->steps as $stepOption)
                                    <option value="{{ $stepOption->id }}" @selected(($isAddStepOld ? old('reference_step_id') : '') === $stepOption->id)>
                                        {{ $stepOption->sort_order }}. {{ $stepOption->label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-1">Biaya</label>
                            <select name="cost_type" class="form-select form-select-sm">
                                @foreach($stepCostTypes as $value => $label)
                                    <option value="{{ $value }}" @selected(($isAddStepOld ? old('cost_type', 'per_kg') : 'per_kg') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-1 d-grid">
                            <button type="submit" class="btn btn-sm btn-primary" title="Tambah Tahap">
                                <i class="icon-base bx bx-plus"></i>
                            </button>
                        </div>
                    </form>
                </div>
                <datalist id="production-step-label-options">
                    @foreach($stepSuggestions as $label)
                        <option value="{{ $label }}"></option>
                    @endforeach
                </datalist>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width: 72px;">Urut</th>
                                <th style="min-width: 180px;">Tahap</th>
                                <th style="min-width: 170px;">Tanggal</th>
                                <th style="min-width: 140px;">Jenis Biaya</th>
                                <th style="min-width: 140px;">Jumlah Kg</th>
                                <th style="min-width: 160px;">Biaya / Kg</th>
                                <th style="min-width: 170px;">Total Biaya</th>
                                <th style="min-width: 140px;">Status</th>
                                <th style="min-width: 200px;">Catatan</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($seedProduction->steps as $step)
                                @php
                                    $formId = 'production-step-form-'.$step->id;
                                    $isOldStep = old('step_id') === $step->id;
                                    $costType = $isOldStep ? old('cost_type', $step->cost_type ?? 'per_kg') : ($step->cost_type ?? 'per_kg');
                                @endphp
                                <tr>
                                    <td>
                                        <span class="badge badge-center rounded-pill {{ $step->status === 'selesai' ? 'bg-label-success' : 'bg-label-secondary' }}">
                                            {{ $step->sort_order }}
                                        </span>
                                    </td>
                                    <td>
                                        <input
                                            type="text"
                                            name="label"
                                            value="{{ $isOldStep ? old('label') : $step->label }}"
                                            class="form-control form-control-sm fw-medium"
                                            form="{{ $formId }}"
                                            list="production-step-label-options"
                                            required
                                        >
                                    </td>
                                    <td>
                                        <input
                                            type="date"
                                            name="planned_date"
                                            value="{{ $isOldStep ? old('planned_date') : ($step->actual_date?->toDateString() ?? $step->planned_date?->toDateString()) }}"
                                            class="form-control form-control-sm"
                                            form="{{ $formId }}"
                                        >
                                    </td>
                                    <td>
                                        <select
                                            name="cost_type"
                                            class="form-select form-select-sm production-step-cost-type"
                                            form="{{ $formId }}"
                                        >
                                            @foreach($stepCostTypes as $value => $label)
                                                <option value="{{ $value }}" @selected($costType === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input
                                            type="number"
                                            name="quantity"
                                            value="{{ $isOldStep ? old('quantity') : $step->quantity }}"
                                            min="0.01"
                                            max="9999999999.99"
                                            step="0.01"
                                            class="form-control form-control-sm production-step-quantity"
                                            form="{{ $formId }}"
                                            placeholder="0"
                                        >
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Rp</span>
                                            <input
                                                type="text"
                                                inputmode="numeric"
                                                name="cost_per_kg"
                                                value="{{ $isOldStep ? old('cost_per_kg') : ($step->cost_per_kg !== null ? number_format((float) $step->cost_per_kg, 0, ',', '.') : '') }}"
                                                class="form-control rupiah-input production-step-cost-per-kg"
                                                form="{{ $formId }}"
                                                placeholder="0"
                                                @readonly($costType === 'langsung')
                                            >
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Rp</span>
                                            <input
                                                type="text"
                                                inputmode="numeric"
                                                name="cost"
                                                value="{{ $isOldStep ? old('cost') : number_format((float) $step->cost, 0, ',', '.') }}"
                                                class="form-control rupiah-input production-step-cost"
                                                form="{{ $formId }}"
                                                placeholder="0"
                                                @readonly($costType === 'per_kg')
                                            >
                                        </div>
                                    </td>
                                    <td>
                                        <select name="status" class="form-select form-select-sm" form="{{ $formId }}">
                                            @foreach($stepStatuses as $value => $label)
                                                <option value="{{ $value }}" @selected(($isOldStep ? old('status') : $step->status) === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input
                                            type="text"
                                            name="notes"
                                            value="{{ $isOldStep ? old('notes') : $step->notes }}"
                                            class="form-control form-control-sm"
                                            form="{{ $formId }}"
                                            placeholder="Catatan"
                                        >
                                    </td>
                                    <td class="text-end">
                                        <form id="{{ $formId }}" method="POST" action="{{ route('seed-productions.steps.update', [$seedProduction, $step]) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="step_id" value="{{ $step->id }}">
                                        </form>
                                        <button type="submit" class="btn btn-sm btn-outline-primary" form="{{ $formId }}">
                                            <span class="icon-base bx bx-save icon-sm me-1"></span>Simpan
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-border-bottom-0">
                            <tr>
                                <th colspan="6" class="text-end">Total Biaya Tahapan</th>
                                <th colspan="4" class="fw-bold">Rp {{ number_format($totalStepCost, 2, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12">
            @php
                $isCertificationOld = old('_form_type') === 'save_certification';
                $currentLabResult = $isCertificationOld ? old('lab_result') : $seedProduction->lab_result;
                $labResultBadge = match($seedProduction->lab_result) {
                    'lulus' => 'bg-label-success',
                    'tidak_lulus' => 'bg-label-danger',
                    'menunggu' => 'bg-label-warning',
                    default => 'bg-label-secondary',
                };
                $labResultLabel = $seedProduction->lab_result
                    ? ($labResults[$seedProduction->lab_result] ?? ucfirst(str_replace('_', ' ', (string) $seedProduction->lab_result)))
                    : 'Belum Diuji';
            @endphp
            <div class="card">
                <div class="card-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 border-bottom">
                    <div>
                        <h5 class="mb-0">Uji Laboratorium & Label Sertifikasi (Standar BPSB)</h5>
                        <small class="text-muted">Catat hasil uji mutu laboratorium (Kadar Air, Kemurnian, Daya Berkecambah) serta nomor sertifikat dan seri label benih.</small>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="badge {{ $labResultBadge }}">{{ $labResultLabel }}</span>
                        @if($seedProduction->certificate_number)
                            <span class="badge bg-label-info">{{ $seedProduction->certificate_number }}</span>
                        @endif
                    </div>
                </div>
                <form method="POST" action="{{ route('seed-productions.certification.update', $seedProduction) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="_form_type" value="save_certification">
                    <div class="card-body pt-4">
                        <div class="row g-4">
                            <div class="col-md-3">
                                <label for="lab_test_date" class="form-label">Tanggal Uji Lab</label>
                                <input
                                    type="date"
                                    id="lab_test_date"
                                    name="lab_test_date"
                                    value="{{ $isCertificationOld ? old('lab_test_date') : $seedProduction->lab_test_date?->toDateString() }}"
                                    class="form-control @error('lab_test_date') is-invalid @enderror"
                                    @disabled($seedProduction->status === 'batal')
                                >
                                @error('lab_test_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="moisture_content" class="form-label">Kadar Air / KA (%)</label>
                                <div class="input-group">
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        max="100"
                                        id="moisture_content"
                                        name="moisture_content"
                                        value="{{ $isCertificationOld ? old('moisture_content') : $seedProduction->moisture_content }}"
                                        class="form-control @error('moisture_content') is-invalid @enderror"
                                        placeholder="Contoh: 12.00"
                                        @disabled($seedProduction->status === 'batal')
                                    >
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="form-text">Standar BPSB: Maks 13,0%</div>
                                @error('moisture_content') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="purity_percentage" class="form-label">Kemurnian Benih (%)</label>
                                <div class="input-group">
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        max="100"
                                        id="purity_percentage"
                                        name="purity_percentage"
                                        value="{{ $isCertificationOld ? old('purity_percentage') : $seedProduction->purity_percentage }}"
                                        class="form-control @error('purity_percentage') is-invalid @enderror"
                                        placeholder="Contoh: 99.20"
                                        @disabled($seedProduction->status === 'batal')
                                    >
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="form-text">Standar BPSB: Min 98,0%</div>
                                @error('purity_percentage') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="germination_rate" class="form-label">Daya Berkecambah / DB (%)</label>
                                <div class="input-group">
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        max="100"
                                        id="germination_rate"
                                        name="germination_rate"
                                        value="{{ $isCertificationOld ? old('germination_rate') : $seedProduction->germination_rate }}"
                                        class="form-control @error('germination_rate') is-invalid @enderror"
                                        placeholder="Contoh: 88.00"
                                        @disabled($seedProduction->status === 'batal')
                                    >
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="form-text">Standar BPSB: Min 80,0%</div>
                                @error('germination_rate') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="lab_result" class="form-label">Status Hasil Uji Lab</label>
                                <select id="lab_result" name="lab_result" class="form-select @error('lab_result') is-invalid @enderror" @disabled($seedProduction->status === 'batal')>
                                    <option value="">Belum Diuji</option>
                                    @foreach($labResults as $value => $label)
                                        <option value="{{ $value }}" @selected($currentLabResult === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('lab_result') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="certificate_number" class="form-label">No Induk Sertifikasi / LHU</label>
                                <input
                                    type="text"
                                    id="certificate_number"
                                    name="certificate_number"
                                    value="{{ $isCertificationOld ? old('certificate_number') : $seedProduction->certificate_number }}"
                                    class="form-control @error('certificate_number') is-invalid @enderror"
                                    maxlength="100"
                                    placeholder="Contoh: LHU/BPSB/2026/0012"
                                    @disabled($seedProduction->status === 'batal')
                                >
                                @error('certificate_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="label_serial_range" class="form-label">Nomor Seri Label</label>
                                <input
                                    type="text"
                                    id="label_serial_range"
                                    name="label_serial_range"
                                    value="{{ $isCertificationOld ? old('label_serial_range') : $seedProduction->label_serial_range }}"
                                    class="form-control @error('label_serial_range') is-invalid @enderror"
                                    maxlength="150"
                                    placeholder="Contoh: BR-00101 s/d BR-00500"
                                    @disabled($seedProduction->status === 'batal')
                                >
                                @error('label_serial_range') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="label_count" class="form-label">Jumlah Keping Label</label>
                                <input
                                    type="number"
                                    min="1"
                                    step="1"
                                    id="label_count"
                                    name="label_count"
                                    value="{{ $isCertificationOld ? old('label_count') : $seedProduction->label_count }}"
                                    class="form-control @error('label_count') is-invalid @enderror"
                                    placeholder="Contoh: 400"
                                    @disabled($seedProduction->status === 'batal')
                                >
                                @error('label_count') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="label_expiry_date" class="form-label">Tanggal Akhir Masa Edar Label</label>
                                <input
                                    type="date"
                                    id="label_expiry_date"
                                    name="label_expiry_date"
                                    value="{{ $isCertificationOld ? old('label_expiry_date') : $seedProduction->label_expiry_date?->toDateString() }}"
                                    class="form-control @error('label_expiry_date') is-invalid @enderror"
                                    @disabled($seedProduction->status === 'batal')
                                >
                                @error('label_expiry_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-8">
                                <label for="certification_notes" class="form-label">Catatan Uji Lab / Sertifikasi</label>
                                <input
                                    type="text"
                                    id="certification_notes"
                                    name="certification_notes"
                                    value="{{ $isCertificationOld ? old('certification_notes') : $seedProduction->certification_notes }}"
                                    class="form-control @error('certification_notes') is-invalid @enderror"
                                    placeholder="Catatan hasil pengujian laboratorium atau penerbitan label"
                                    @disabled($seedProduction->status === 'batal')
                                >
                                @error('certification_notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary" @disabled($seedProduction->status === 'batal')>
                            <i class="icon-base bx bx-check-shield me-1"></i> Simpan Data Uji Lab & Sertifikasi
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 border-bottom">
                    <div>
                        <h5 class="mb-0">Hasil Akhir Produksi (Benih Jadi)</h5>
                        <small class="text-muted">Simpan hasil akhir benih jadi agar otomatis masuk ke saldo stok gudang hasil.</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @if($seedProduction->output_quantity)
                            <span class="badge bg-label-success">
                                Sudah Masuk Stok ({{ number_format((float) $seedProduction->output_quantity, 2, ',', '.') }} {{ strtoupper($seedProduction->unit) }})
                            </span>
                            @if($seedProduction->output_stock_id)
                                <a href="{{ route('stocks.show', $seedProduction->output_stock_id) }}" class="btn btn-sm btn-outline-primary">
                                    <span class="icon-base bx bx-list-ul icon-sm me-1"></span>Kartu Stok
                                </a>
                            @endif
                        @else
                            <span class="badge bg-label-secondary">Belum Masuk Stok</span>
                        @endif
                    </div>
                </div>
                <form method="POST" action="{{ route('seed-productions.output.update', $seedProduction) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="_form_type" value="save_output">
                    @php
                        $isOutputOld = old('_form_type') === 'save_output';
                    @endphp
                    <div class="card-body pt-4">
                        @if($seedProduction->status === 'batal')
                            <div class="alert alert-warning">
                                Hasil produksi tidak bisa disimpan karena produksi sudah dibatalkan.
                            </div>
                        @endif
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label for="output_item_id" class="form-label">Barang Benih Jadi <span class="text-danger">*</span></label>
                                @if($outputItems->isEmpty())
                                    <div class="alert alert-warning mb-2 py-2">
                                        Belum ada master barang kategori <strong>Benih</strong> untuk varietas {{ $seedProduction->riceVariety?->name ?? 'ini' }}.
                                    </div>
                                    <a href="{{ route('master.items.index') }}" class="btn btn-sm btn-outline-primary">
                                        <i class="icon-base bx bx-plus me-1"></i> Kelola Master Barang
                                    </a>
                                @else
                                    <select id="output_item_id" name="output_item_id" class="form-select @error('output_item_id') is-invalid @enderror" required @disabled($seedProduction->status === 'batal')>
                                        <option value="">Pilih barang benih jadi</option>
                                        @foreach($outputItems as $item)
                                            <option
                                                value="{{ $item->id }}"
                                                @selected(($isOutputOld ? old('output_item_id') : ($seedProduction->output_item_id ?? ($outputItems->count() === 1 ? $item->id : ''))) === $item->id)
                                            >
                                                {{ $item->code }} - {{ $item->name }} ({{ strtoupper($item->unit) }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('output_item_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @endif
                            </div>

                            <div class="col-md-4">
                                <label for="output_warehouse_id" class="form-label">Gudang Hasil <span class="text-danger">*</span></label>
                                <select id="output_warehouse_id" name="output_warehouse_id" class="form-select @error('output_warehouse_id') is-invalid @enderror" required @disabled($seedProduction->status === 'batal')>
                                    <option value="">Pilih gudang hasil</option>
                                    @foreach($warehouses as $warehouse)
                                        <option
                                            value="{{ $warehouse->id }}"
                                            @selected(($isOutputOld ? old('output_warehouse_id') : $seedProduction->output_warehouse_id) === $warehouse->id)
                                        >
                                            {{ $warehouse->code }} - {{ $warehouse->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('output_warehouse_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="output_lot_number" class="form-label">No Lot Benih Jadi</label>
                                <input
                                    type="text"
                                    id="output_lot_number"
                                    name="lot_number"
                                    value="{{ $isOutputOld ? old('lot_number') : $seedProduction->lot_number }}"
                                    class="form-control @error('lot_number') is-invalid @enderror"
                                    maxlength="100"
                                    placeholder="Contoh: LOT-IR64-001"
                                    @disabled($seedProduction->status === 'batal')
                                >
                                @error('lot_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="output_date" class="form-label">Tanggal Selesai / Masuk Stok <span class="text-danger">*</span></label>
                                <input
                                    type="date"
                                    id="output_date"
                                    name="output_date"
                                    value="{{ $isOutputOld ? old('output_date') : ($seedProduction->output_date?->toDateString() ?? now()->toDateString()) }}"
                                    class="form-control @error('output_date') is-invalid @enderror"
                                    required
                                    @disabled($seedProduction->status === 'batal')
                                >
                                @error('output_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="output_quantity" class="form-label">Jumlah Bersih Benih Jadi ({{ strtoupper($seedProduction->unit) }}) <span class="text-danger">*</span></label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    id="output_quantity"
                                    name="output_quantity"
                                    value="{{ $isOutputOld ? old('output_quantity') : ($seedProduction->output_quantity ?? $seedProduction->target_quantity) }}"
                                    class="form-control @error('output_quantity') is-invalid @enderror"
                                    required
                                    @disabled($seedProduction->status === 'batal')
                                >
                                @error('output_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="output_status" class="form-label">Status Produksi <span class="text-danger">*</span></label>
                                <select id="output_status" name="status" class="form-select" required @disabled($seedProduction->status === 'batal')>
                                    <option value="siap_salur" @selected(($isOutputOld ? old('status', 'siap_salur') : ($seedProduction->output_quantity ? $seedProduction->status : 'siap_salur')) === 'siap_salur')>Siap Salur (Selesai)</option>
                                    <option value="proses" @selected(($isOutputOld ? old('status') : ($seedProduction->output_quantity ? $seedProduction->status : '')) === 'proses')>Masih Proses</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary" @disabled($seedProduction->status === 'batal' || $outputItems->isEmpty())>
                            <i class="icon-base bx bx-save me-1"></i> Simpan Hasil ke Stok Gudang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const rupiahInputs = document.querySelectorAll('.rupiah-input');
        const additionalStockOptions = @json($additionalStockOptions);
        const productionRiceVarietyId = @json($seedProduction->rice_variety_id);
        const additionalRoleSelect = document.getElementById('additional-production-input-role');
        const additionalStockSelect = document.getElementById('additional-production-input-stock');

        function digits(value) {
            return String(value || '').replace(/\D/g, '');
        }

        function formatRupiah(value) {
            const cleanValue = digits(value);

            return cleanValue ? Number(cleanValue).toLocaleString('id-ID') : '';
        }

        function syncRowCost(row) {
            const quantityInput = row.querySelector('.production-step-quantity');
            const costTypeInput = row.querySelector('.production-step-cost-type');
            const costPerKgInput = row.querySelector('.production-step-cost-per-kg');
            const costInput = row.querySelector('.production-step-cost');
            const costType = costTypeInput?.value || 'per_kg';
            const quantity = Number(quantityInput?.value || 0);
            const costPerKg = Number(digits(costPerKgInput?.value || 0));

            if (!costInput) {
                return;
            }

            costInput.readOnly = costType === 'per_kg';

            if (costPerKgInput) {
                costPerKgInput.readOnly = costType === 'langsung';
            }

            if (costType === 'langsung') {
                return;
            }

            if (quantity <= 0 || costPerKg < 0) {
                costInput.value = '';
                return;
            }

            costInput.value = formatRupiah(Math.round(quantity * costPerKg));
        }

        function renderAdditionalStockOptions() {
            if (!additionalRoleSelect || !additionalStockSelect) {
                return;
            }

            const selectedValue = additionalStockSelect.dataset.selected || additionalStockSelect.value;
            const role = additionalRoleSelect.value;
            const options = role === 'bahan_utama' && productionRiceVarietyId
                ? additionalStockOptions.filter(function (option) {
                    return option.rice_variety_id === productionRiceVarietyId;
                })
                : additionalStockOptions;

            additionalStockSelect.innerHTML = [
                '<option value="">Pilih stok bahan</option>',
                ...options.map(function (option) {
                    return `<option value="${option.value}">${option.label}</option>`;
                })
            ].join('');

            if (options.some(function (option) { return option.value === selectedValue; })) {
                additionalStockSelect.value = selectedValue;
            }

            additionalStockSelect.dataset.selected = '';
        }

        rupiahInputs.forEach(function (input) {
            input.value = formatRupiah(input.value);

            input.addEventListener('input', function () {
                input.value = formatRupiah(input.value);
                syncRowCost(input.closest('tr'));
            });
        });

        document.querySelectorAll('.production-step-quantity').forEach(function (input) {
            input.addEventListener('input', function () {
                syncRowCost(input.closest('tr'));
            });
        });

        document.querySelectorAll('.production-step-cost-type').forEach(function (input) {
            syncRowCost(input.closest('tr'));

            input.addEventListener('change', function () {
                syncRowCost(input.closest('tr'));
            });
        });

        renderAdditionalStockOptions();
        additionalRoleSelect?.addEventListener('change', renderAdditionalStockOptions);
    });
</script>
@endsection
