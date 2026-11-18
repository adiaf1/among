@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
        <div>
            <h4 class="mb-1">Tambah Produksi Benih</h4>
            <p class="text-muted mb-0">Ambil bahan dari stok gudang untuk memulai proses pengolahan dan sertifikasi benih.</p>
        </div>
        <a href="{{ route('seed-productions.index') }}" class="btn btn-label-secondary">
            <i class="icon-base bx bx-arrow-back me-1"></i> Kembali
        </a>
    </div>

    @php
        $varietyStockTotals = $stocks
            ->filter(fn ($stock) => filled($stock->item?->rice_variety_id))
            ->groupBy(fn ($stock) => $stock->item->rice_variety_id)
            ->map(fn ($group) => [
                'count' => $group->count(),
                'quantity' => (float) $group->sum('quantity'),
                'unit' => strtoupper($group->first()?->item?->unit ?? 'KG'),
            ]);
    @endphp

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <span class="text-heading">Stok Varietas Terpilih</span>
                            <h4 class="my-1" data-summary-variety-qty>Pilih Varietas</h4>
                            <small class="text-muted" data-summary-variety-lots>{{ $varietyStockTotals->count() }} varietas memiliki stok aktif di gudang</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="icon-base bx bx-leaf icon-lg"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <span class="text-heading">Total Bahan Utama Dipakai</span>
                            <h4 class="my-1" data-summary-main-used>0,00 KG</h4>
                            <small class="text-muted" data-summary-yield-info>Isi jumlah bahan utama & target hasil</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="icon-base bx bx-package icon-lg"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <span class="text-heading">Referensi Lot Panen</span>
                            <h4 class="my-1">{{ $harvestLots->count() }} Lot</h4>
                            <small class="text-muted" data-summary-lot-info>Pilih No Lot untuk autofill varietas & kelas</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="icon-base bx bx-check-shield icon-lg"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center border-bottom">
            <div>
                <h5 class="mb-0">Form Produksi Benih</h5>
                <small class="text-muted">Pastikan minimal satu baris menggunakan peran <strong>Bahan Utama</strong> sesuai varietas yang dipilih.</small>
            </div>
            <span class="badge bg-label-primary">Status Awal: Proses</span>
        </div>
        <div class="card-body pt-4">
            <form method="POST" action="{{ route('seed-productions.store') }}">
                @csrf

                @error('inputs')
                    <div class="alert alert-danger" role="alert">{{ $message }}</div>
                @enderror

                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <label for="number" class="form-label">Nomor</label>
                        <input
                            type="text"
                            class="form-control @error('number') is-invalid @enderror"
                            id="number"
                            name="number"
                            value="{{ old('number', $nextNumber) }}"
                            maxlength="100"
                            placeholder="Kosongkan untuk otomatis"
                        >
                        <div class="form-text">Otomatis (<code>PRD-YYYYMMDD-XXXX</code>), bisa diubah.</div>
                        @error('number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="production_date" class="form-label">Tanggal Produksi <span class="text-danger">*</span></label>
                        <input
                            type="date"
                            class="form-control @error('production_date') is-invalid @enderror"
                            id="production_date"
                            name="production_date"
                            value="{{ old('production_date', now()->toDateString()) }}"
                            required
                        >
                        @error('production_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="lot_number" class="form-label">No Lot</label>
                        <input
                            type="text"
                            class="form-control @error('lot_number') is-invalid @enderror"
                            id="lot_number"
                            name="lot_number"
                            value="{{ old('lot_number') }}"
                            maxlength="100"
                            list="harvest-lot-options"
                            placeholder="Contoh: LOT-IR64-001"
                        >
                        <datalist id="harvest-lot-options">
                            @foreach($harvestLots as $seedGrowing)
                                <option
                                    value="{{ $seedGrowing->lot_number }}"
                                    label="{{ $seedGrowing->field_number }} - {{ $seedGrowing->riceVariety?->name ?? '-' }} - {{ number_format((float) ($seedGrowing->harvest?->harvested_quantity ?? 0), 2, ',', '.') }} {{ $seedGrowing->harvest?->unit ?? 'kg' }}"
                                ></option>
                            @endforeach
                        </datalist>
                        @error('lot_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="output_warehouse_id" class="form-label">Gudang Hasil</label>
                        <select
                            class="form-select @error('output_warehouse_id') is-invalid @enderror"
                            id="output_warehouse_id"
                            name="output_warehouse_id"
                        >
                            <option value="">Pilih gudang</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" @selected(old('output_warehouse_id') === $warehouse->id)>
                                    {{ $warehouse->code }} - {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('output_warehouse_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="rice_variety_id" class="form-label">Varietas</label>
                        <select
                            class="form-select @error('rice_variety_id') is-invalid @enderror"
                            id="rice_variety_id"
                            name="rice_variety_id"
                        >
                            <option value="">Pilih varietas</option>
                            @foreach($riceVarieties as $variety)
                                @php
                                    $vStock = $varietyStockTotals->get($variety->id);
                                @endphp
                                <option
                                    value="{{ $variety->id }}"
                                    data-stock-qty="{{ $vStock['quantity'] ?? 0 }}"
                                    data-stock-count="{{ $vStock['count'] ?? 0 }}"
                                    data-stock-unit="{{ $vStock['unit'] ?? 'KG' }}"
                                    @selected(old('rice_variety_id') === $variety->id)
                                >
                                    {{ $variety->code }} - {{ $variety->name }} ({{ $vStock ? 'Stok: '.number_format($vStock['quantity'], 2, ',', '.').' '.$vStock['unit'] : 'Stok: 0' }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text" data-variety-stock-info>Pilih varietas untuk melihat ringkasan stok bahan.</div>
                        <div class="d-none mt-2" data-variety-empty-actions>
                            <a href="{{ route('master.items.index') }}" class="btn btn-xs btn-outline-primary me-1">
                                <i class="icon-base bx bx-package me-1"></i> Atur Barang
                            </a>
                            <a href="{{ route('stocks.create') }}" class="btn btn-xs btn-outline-secondary">
                                <i class="icon-base bx bx-plus me-1"></i> Isi Stok
                            </a>
                        </div>
                        @error('rice_variety_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="seed_class_id" class="form-label">Kelas Benih Tujuan</label>
                        <select
                            class="form-select @error('seed_class_id') is-invalid @enderror"
                            id="seed_class_id"
                            name="seed_class_id"
                        >
                            <option value="">Pilih kelas</option>
                            @foreach($seedClasses as $seedClass)
                                <option value="{{ $seedClass->id }}" @selected(old('seed_class_id') === $seedClass->id)>
                                    {{ $seedClass->code }} - {{ $seedClass->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('seed_class_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="target_quantity" class="form-label">Target Hasil</label>
                        <input
                            type="number"
                            class="form-control @error('target_quantity') is-invalid @enderror"
                            id="target_quantity"
                            name="target_quantity"
                            value="{{ old('target_quantity') }}"
                            min="0.01"
                            max="9999999999.99"
                            step="0.01"
                            placeholder="Opsional"
                        >
                        @error('target_quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="unit" class="form-label">Satuan <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            class="form-control @error('unit') is-invalid @enderror"
                            id="unit"
                            name="unit"
                            value="{{ old('unit', 'kg') }}"
                            maxlength="50"
                            required
                        >
                        @error('unit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="notes" class="form-label">Catatan</label>
                        <textarea
                            class="form-control @error('notes') is-invalid @enderror"
                            id="notes"
                            name="notes"
                            rows="2"
                            placeholder="Catatan produksi"
                        >{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                @php
                    $oldInputs = old('inputs', [
                        ['stock_id' => '', 'role' => 'bahan_utama', 'quantity' => '', 'notes' => ''],
                    ]);
                @endphp

                <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                    <h6 class="mb-0">Bahan Produksi</h6>
                    <button type="button" class="btn btn-label-primary btn-sm" id="add-production-input-row">
                        <i class="icon-base bx bx-plus me-1"></i> Tambah Baris
                    </button>
                </div>

                <div class="table-responsive production-inputs-table-wrap text-nowrap mb-4">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th style="min-width: 170px;">Peran</th>
                                <th style="min-width: 360px;">Stok Bahan</th>
                                <th style="min-width: 160px;">Jumlah</th>
                                <th style="min-width: 220px;">Catatan</th>
                                <th style="width: 72px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="production-inputs-body">
                            @foreach($oldInputs as $index => $oldInput)
                                <tr data-production-input-row>
                                    <td>
                                        <select
                                            data-field="role"
                                            class="form-select @error("inputs.$index.role") is-invalid @enderror"
                                            name="inputs[{{ $index }}][role]"
                                            required
                                        >
                                            @foreach($inputRoles as $value => $label)
                                                <option value="{{ $value }}" @selected(($oldInput['role'] ?? 'bahan_utama') === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error("inputs.$index.role")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td>
                                        <select
                                            data-field="stock_id"
                                            class="select2 form-select @error("inputs.$index.stock_id") is-invalid @enderror"
                                            name="inputs[{{ $index }}][stock_id]"
                                            data-placeholder="Pilih stok bahan"
                                            required
                                        >
                                            <option value="">Pilih stok bahan</option>
                                            @foreach($stocks as $stock)
                                                <option
                                                    value="{{ $stock->id }}"
                                                    data-rice-variety-id="{{ $stock->item?->rice_variety_id }}"
                                                    @selected(($oldInput['stock_id'] ?? '') === $stock->id)
                                                >
                                                    {{ $stock->item?->code }} - {{ $stock->item?->name }} | {{ $stock->warehouse?->name }} | Lot {{ $stock->lot_number ?: '-' }} | Stok {{ number_format((float) $stock->quantity, 2, ',', '.') }} {{ strtoupper($stock->item?->unit) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("inputs.$index.stock_id")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td>
                                        <input
                                            type="number"
                                            data-field="quantity"
                                            class="form-control @error("inputs.$index.quantity") is-invalid @enderror"
                                            name="inputs[{{ $index }}][quantity]"
                                            value="{{ $oldInput['quantity'] ?? '' }}"
                                            min="0.01"
                                            max="9999999999.99"
                                            step="0.01"
                                            required
                                        >
                                        <div class="form-text" data-row-stock-help>Pilih stok bahan untuk melihat batas saldo.</div>
                                        @error("inputs.$index.quantity")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td>
                                        <input
                                            type="text"
                                            data-field="notes"
                                            class="form-control @error("inputs.$index.notes") is-invalid @enderror"
                                            name="inputs[{{ $index }}][notes]"
                                            value="{{ $oldInput['notes'] ?? '' }}"
                                            placeholder="Opsional"
                                        >
                                        @error("inputs.$index.notes")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-icon btn-label-danger" data-remove-row title="Hapus baris">
                                            <i class="icon-base bx bx-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-border-bottom-0">
                            <tr>
                                <th colspan="2" class="text-end">Total Bahan Utama Dipakai</th>
                                <th class="fw-bold" data-table-main-total>0,00 KG</th>
                                <th colspan="2" class="text-muted small" data-table-row-count>1 baris bahan</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
                    <a href="{{ route('seed-productions.index') }}" class="btn btn-label-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Produksi</button>
                </div>
            </form>
        </div>
    </div>
</div>

@php
    $stockOptions = $stocks->map(fn ($stock) => [
        'value' => $stock->id,
        'label' => ($stock->item?->code ?? '-').' - '.($stock->item?->name ?? '-').' | '.($stock->warehouse?->name ?? '-').' | Lot '.($stock->lot_number ?: '-').' | Stok '.number_format((float) $stock->quantity, 2, ',', '.').' '.strtoupper($stock->item?->unit ?? ''),
        'rice_variety_id' => $stock->item?->rice_variety_id,
        'quantity' => (float) $stock->quantity,
        'unit' => strtoupper($stock->item?->unit ?? 'KG'),
    ])->values();
    $harvestLotOptions = $harvestLots->map(fn ($seedGrowing) => [
        'lot_number' => $seedGrowing->lot_number,
        'rice_variety_id' => $seedGrowing->rice_variety_id,
        'seed_class_id' => $seedGrowing->seed_class_id,
        'label' => $seedGrowing->field_number.' - '.($seedGrowing->riceVariety?->name ?? '-'),
    ])->values();
    $roleOptions = collect($inputRoles)->map(fn ($label, $value) => [
        'value' => $value,
        'label' => $label,
    ])->values();
@endphp

<style>
    @media (min-width: 768px) {
        .production-inputs-table-wrap {
            overflow: visible;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const stockOptions = @json($stockOptions);
        const harvestLotOptions = @json($harvestLotOptions);
        const roleOptions = @json($roleOptions);
        const tableBody = document.getElementById('production-inputs-body');
        const lotInput = document.getElementById('lot_number');
        const riceVarietySelect = document.getElementById('rice_variety_id');
        const seedClassSelect = document.getElementById('seed_class_id');

        const stockOptionsForRole = function (role) {
            if (role !== 'bahan_utama') {
                return stockOptions;
            }

            if (!riceVarietySelect?.value) {
                return [];
            }

            return stockOptions.filter(function (option) {
                return option.rice_variety_id === riceVarietySelect.value;
            });
        };

        const optionHtml = function (options, placeholder) {
            return [
                `<option value="">${placeholder}</option>`,
                ...options.map(function (option) {
                    return `<option value="${option.value}">${option.label}</option>`;
                })
            ].join('');
        };

        const formatQty = function (val) {
            return Number(val || 0).toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        };

        const updateRowStockHelp = function (row) {
            if (!row) return;
            const stockSelect = row.querySelector('[data-field="stock_id"]');
            const qtyInput = row.querySelector('[data-field="quantity"]');
            const helpEl = row.querySelector('[data-row-stock-help]');
            if (!stockSelect || !qtyInput || !helpEl) return;

            const stock = stockOptions.find(function (opt) {
                return opt.value === stockSelect.value;
            });

            if (!stock) {
                qtyInput.max = '9999999999.99';
                helpEl.textContent = 'Pilih stok bahan untuk melihat batas saldo.';
                return;
            }

            qtyInput.max = String(stock.quantity);
            helpEl.textContent = `Saldo tersedia: ${formatQty(stock.quantity)} ${stock.unit}`;
        };

        const updateProductionSummary = function () {
            let mainTotal = 0;
            const rows = tableBody.querySelectorAll('[data-production-input-row]');
            const unitInput = (document.getElementById('unit')?.value || 'KG').toUpperCase();

            rows.forEach(function (row) {
                const role = row.querySelector('[data-field="role"]')?.value || 'bahan_utama';
                const qty = parseFloat(row.querySelector('[data-field="quantity"]')?.value || '0') || 0;
                if (role === 'bahan_utama') {
                    mainTotal += qty;
                }
            });

            const mainUsedText = `${formatQty(mainTotal)} ${unitInput}`;
            const summaryMainEl = document.querySelector('[data-summary-main-used]');
            const tableMainEl = document.querySelector('[data-table-main-total]');
            const rowCountEl = document.querySelector('[data-table-row-count]');
            const yieldInfoEl = document.querySelector('[data-summary-yield-info]');
            const targetQty = parseFloat(document.getElementById('target_quantity')?.value || '0') || 0;

            if (summaryMainEl) summaryMainEl.textContent = mainUsedText;
            if (tableMainEl) tableMainEl.textContent = mainUsedText;
            if (rowCountEl) rowCountEl.textContent = `${rows.length} baris bahan`;

            if (yieldInfoEl) {
                if (mainTotal > 0 && targetQty > 0) {
                    const ratio = Math.round((targetQty / mainTotal) * 100);
                    yieldInfoEl.textContent = `Target ${formatQty(targetQty)} ${unitInput} (estimasi rendemen ~${ratio}%)`;
                } else if (targetQty > 0) {
                    yieldInfoEl.textContent = `Target hasil: ${formatQty(targetQty)} ${unitInput}`;
                } else {
                    yieldInfoEl.textContent = 'Isi jumlah bahan utama & target hasil';
                }
            }
        };

        const renderStockSelect = function (select, selectedValue) {
            const row = select.closest('[data-production-input-row]');
            const role = row?.querySelector('[data-field="role"]')?.value || 'bahan_utama';
            const filteredOptions = stockOptionsForRole(role);
            const canKeepSelected = filteredOptions.some(function (option) {
                return option.value === selectedValue;
            });

            select.innerHTML = optionHtml(filteredOptions, role === 'bahan_utama' && !riceVarietySelect?.value
                ? 'Pilih varietas dulu'
                : 'Pilih stok bahan'
            );
            select.value = canKeepSelected ? selectedValue : '';

            if (window.jQuery && window.jQuery(select).hasClass('select2-hidden-accessible')) {
                window.jQuery(select).trigger('change.select2');
            }

            updateRowStockHelp(row);
        };

        const initRowSelect2 = function (row) {
            if (!window.jQuery || !window.jQuery.fn.select2) return;

            const stockSelect = row.querySelector('[data-field="stock_id"]');
            if (!stockSelect) return;

            const $select = window.jQuery(stockSelect);
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.on('change.prodStock', function () {
                    updateRowStockHelp(row);
                });
                return;
            }

            $select.wrap('<div class="position-relative"></div>').select2({
                placeholder: 'Pilih stok bahan',
                dropdownParent: $select.parent()
            }).on('change.prodStock', function () {
                updateRowStockHelp(row);
            });
        };

        const syncStockOptions = function () {
            tableBody.querySelectorAll('[data-field="stock_id"]').forEach(function (select) {
                renderStockSelect(select, select.value);
            });

            const varietyInfoEl = document.querySelector('[data-variety-stock-info]');
            const emptyActionsEl = document.querySelector('[data-variety-empty-actions]');
            const summaryVarietyQtyEl = document.querySelector('[data-summary-variety-qty]');
            const summaryVarietyLotsEl = document.querySelector('[data-summary-variety-lots]');
            const varietyId = riceVarietySelect?.value || '';

            if (!varietyId) {
                if (varietyInfoEl) varietyInfoEl.textContent = 'Pilih varietas untuk melihat ringkasan stok bahan.';
                emptyActionsEl?.classList.add('d-none');
                if (summaryVarietyQtyEl) summaryVarietyQtyEl.textContent = 'Pilih Varietas';
                if (summaryVarietyLotsEl) summaryVarietyLotsEl.textContent = 'Pilih varietas untuk melihat ketersediaan stok';
            } else {
                const matching = stockOptions.filter(function (option) {
                    return option.rice_variety_id === varietyId;
                });
                const totalQty = matching.reduce(function (acc, item) { return acc + Number(item.quantity || 0); }, 0);
                const unit = matching[0]?.unit || 'KG';

                if (!matching.length) {
                    if (varietyInfoEl) varietyInfoEl.textContent = 'Belum ada stok bahan tersedia untuk varietas ini.';
                    emptyActionsEl?.classList.remove('d-none');
                    if (summaryVarietyQtyEl) summaryVarietyQtyEl.textContent = '0,00 KG';
                    if (summaryVarietyLotsEl) summaryVarietyLotsEl.textContent = 'Belum ada stok terhubung ke varietas ini';
                } else {
                    if (varietyInfoEl) varietyInfoEl.textContent = `Tersedia ${matching.length} lot/stok bahan (total ${formatQty(totalQty)} ${unit}).`;
                    emptyActionsEl?.classList.add('d-none');
                    if (summaryVarietyQtyEl) summaryVarietyQtyEl.textContent = `${formatQty(totalQty)} ${unit}`;
                    if (summaryVarietyLotsEl) summaryVarietyLotsEl.textContent = `Tersedia di ${matching.length} lot/stok gudang`;
                }
            }

            updateProductionSummary();
        };

        const syncRows = function () {
            tableBody.querySelectorAll('[data-production-input-row]').forEach(function (row, index) {
                row.querySelectorAll('[data-field]').forEach(function (field) {
                    field.name = `inputs[${index}][${field.dataset.field}]`;
                });
            });

            tableBody.querySelectorAll('[data-remove-row]').forEach(function (button) {
                button.disabled = tableBody.querySelectorAll('[data-production-input-row]').length === 1;
            });

            updateProductionSummary();
        };

        const createRow = function () {
            const row = document.createElement('tr');
            row.dataset.productionInputRow = '';
            row.innerHTML = `
                <td>
                    <select class="form-select" data-field="role" required>
                        ${roleOptions.map(function (option) {
                            return `<option value="${option.value}">${option.label}</option>`;
                        }).join('')}
                    </select>
                </td>
                <td>
                    <select class="form-select" data-field="stock_id" required>
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control" data-field="quantity" min="0.01" max="9999999999.99" step="0.01" required>
                    <div class="form-text" data-row-stock-help>Pilih stok bahan untuk melihat batas saldo.</div>
                </td>
                <td>
                    <input type="text" class="form-control" data-field="notes" placeholder="Opsional">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-icon btn-label-danger" data-remove-row title="Hapus baris">
                        <i class="icon-base bx bx-trash"></i>
                    </button>
                </td>
            `;

            return row;
        };

        document.getElementById('add-production-input-row')?.addEventListener('click', function () {
            const row = createRow();
            tableBody.appendChild(row);
            syncRows();
            initRowSelect2(row);
            renderStockSelect(row.querySelector('[data-field="stock_id"]'), '');
        });

        lotInput?.addEventListener('change', function () {
            const lot = harvestLotOptions.find(function (option) {
                return option.lot_number === lotInput.value;
            });

            if (!lot) {
                return;
            }

            if (riceVarietySelect && lot.rice_variety_id) {
                riceVarietySelect.value = lot.rice_variety_id;
            }

            if (seedClassSelect && lot.seed_class_id) {
                seedClassSelect.value = lot.seed_class_id;
            }

            syncStockOptions();
        });

        riceVarietySelect?.addEventListener('change', syncStockOptions);

        tableBody.addEventListener('change', function (event) {
            const row = event.target.closest('[data-production-input-row]');
            if (event.target.matches('[data-field="role"]')) {
                const stockSelect = row?.querySelector('[data-field="stock_id"]');
                if (stockSelect) {
                    renderStockSelect(stockSelect, stockSelect.value);
                }
                updateProductionSummary();
            } else if (event.target.matches('[data-field="stock_id"]')) {
                updateRowStockHelp(row);
            }
        });

        tableBody.addEventListener('input', function (event) {
            if (event.target.matches('[data-field="quantity"]')) {
                updateProductionSummary();
            }
        });

        document.getElementById('target_quantity')?.addEventListener('input', updateProductionSummary);
        document.getElementById('unit')?.addEventListener('input', updateProductionSummary);

        tableBody.addEventListener('click', function (event) {
            const removeButton = event.target.closest('[data-remove-row]');

            if (!removeButton || tableBody.querySelectorAll('[data-production-input-row]').length === 1) {
                return;
            }

            removeButton.closest('[data-production-input-row]').remove();
            syncRows();
        });

        syncRows();
        tableBody.querySelectorAll('[data-production-input-row]').forEach(initRowSelect2);
        syncStockOptions();
    });
</script>
@endsection
