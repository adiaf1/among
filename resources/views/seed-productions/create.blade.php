@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="py-4">
        <h4 class="mb-1">Tambah Produksi Benih</h4>
        <p class="text-muted mb-0">Ambil bahan dari stok gudang untuk memulai proses produksi benih.</p>
    </div>

    <div class="card">
        <div class="card-body">
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
                            value="{{ old('number') }}"
                            maxlength="100"
                            placeholder="Kosongkan untuk otomatis"
                        >
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
                                <option value="{{ $variety->id }}" @selected(old('rice_variety_id') === $variety->id)>
                                    {{ $variety->code }} - {{ $variety->name }}
                                </option>
                            @endforeach
                        </select>
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
                        <i class="bx bx-plus me-1"></i> Tambah Baris
                    </button>
                </div>

                <div class="table-responsive text-nowrap mb-4">
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
                                            class="form-select @error("inputs.$index.stock_id") is-invalid @enderror"
                                            name="inputs[{{ $index }}][stock_id]"
                                            required
                                        >
                                            <option value="">Pilih stok bahan</option>
                                            @foreach($stocks as $stock)
                                                <option
                                                    value="{{ $stock->id }}"
                                                    data-rice-variety-id="{{ $stock->item?->rice_variety_id }}"
                                                    @selected(($oldInput['stock_id'] ?? '') === $stock->id)
                                                >
                                                    {{ $stock->item?->code }} - {{ $stock->item?->name }} | {{ $stock->warehouse?->name }} | Stok {{ number_format((float) $stock->quantity, 2, ',', '.') }} {{ strtoupper($stock->item?->unit) }}
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
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
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
        'label' => ($stock->item?->code ?? '-').' - '.($stock->item?->name ?? '-').' | '.($stock->warehouse?->name ?? '-').' | Stok '.number_format((float) $stock->quantity, 2, ',', '.').' '.strtoupper($stock->item?->unit ?? ''),
        'rice_variety_id' => $stock->item?->rice_variety_id,
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
        };

        const syncStockOptions = function () {
            tableBody.querySelectorAll('[data-field="stock_id"]').forEach(function (select) {
                renderStockSelect(select, select.value);
            });
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
                </td>
                <td>
                    <input type="text" class="form-control" data-field="notes" placeholder="Opsional">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-icon btn-label-danger" data-remove-row title="Hapus baris">
                        <i class="bx bx-trash"></i>
                    </button>
                </td>
            `;

            return row;
        };

        document.getElementById('add-production-input-row')?.addEventListener('click', function () {
            const row = createRow();
            tableBody.appendChild(row);
            syncRows();
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
            const roleSelect = event.target.closest('[data-field="role"]');

            if (!roleSelect) {
                return;
            }

            const row = roleSelect.closest('[data-production-input-row]');
            const stockSelect = row?.querySelector('[data-field="stock_id"]');

            if (stockSelect) {
                renderStockSelect(stockSelect, stockSelect.value);
            }
        });

        tableBody.addEventListener('click', function (event) {
            const removeButton = event.target.closest('[data-remove-row]');

            if (!removeButton || tableBody.querySelectorAll('[data-production-input-row]').length === 1) {
                return;
            }

            removeButton.closest('[data-production-input-row]').remove();
            syncRows();
        });

        syncRows();
        syncStockOptions();
    });
</script>
@endsection
