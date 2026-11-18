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
                            <span class="text-heading">Target Hasil</span>
                            <h4 class="my-1">
                                @if($seedProduction->target_quantity)
                                    {{ number_format((float) $seedProduction->target_quantity, 2, ',', '.') }} {{ strtoupper($seedProduction->unit) }}
                                @else
                                    -
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
