@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="py-4">
        <h4 class="mb-1">Tambah Pembelian Barang</h4>
        <p class="text-muted mb-0">Barang yang disimpan akan langsung menambah stok gudang tujuan.</p>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('purchases.store') }}">
                @csrf

                <div class="row g-4 mb-4">
                    <div class="col-md-4">
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

                    <div class="col-md-4">
                        <label for="purchase_date" class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input
                            type="date"
                            class="form-control @error('purchase_date') is-invalid @enderror"
                            id="purchase_date"
                            name="purchase_date"
                            value="{{ old('purchase_date', now()->toDateString()) }}"
                            required
                        >
                        @error('purchase_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="supplier_id" class="form-label">Supplier <span class="text-danger">*</span></label>
                        <select
                            class="form-select @error('supplier_id') is-invalid @enderror"
                            id="supplier_id"
                            name="supplier_id"
                            required
                        >
                            <option value="">Pilih supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected(old('supplier_id') === $supplier->id)>
                                    {{ $supplier->code }} - {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('supplier_id')
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
                            placeholder="Catatan pembelian"
                        >{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                @php
                    $oldItems = old('items', [
                        ['item_id' => '', 'warehouse_id' => '', 'quantity' => '', 'unit_price' => 0],
                    ]);
                @endphp

                <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                    <h6 class="mb-0">Detail Barang</h6>
                    <button type="button" class="btn btn-label-primary btn-sm" id="add-purchase-item-row">
                        <i class="bx bx-plus me-1"></i> Tambah Baris
                    </button>
                </div>

                <div class="table-responsive text-nowrap mb-4">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th style="min-width: 260px;">Barang</th>
                                <th style="min-width: 240px;">Gudang Tujuan</th>
                                <th style="min-width: 140px;">Jumlah</th>
                                <th style="min-width: 160px;">Harga Satuan</th>
                                <th style="width: 72px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="purchase-items-body">
                            @foreach($oldItems as $index => $oldItem)
                                <tr data-purchase-item-row>
                                    <td>
                                        <select
                                            data-field="item_id"
                                            class="form-select @error("items.$index.item_id") is-invalid @enderror"
                                            name="items[{{ $index }}][item_id]"
                                            required
                                        >
                                            <option value="">Pilih barang</option>
                                            @foreach($items as $item)
                                                <option value="{{ $item->id }}" @selected(($oldItem['item_id'] ?? '') === $item->id)>
                                                    {{ $item->code }} - {{ $item->name }} ({{ strtoupper($item->unit) }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("items.$index.item_id")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td>
                                        <select
                                            data-field="warehouse_id"
                                            class="form-select @error("items.$index.warehouse_id") is-invalid @enderror"
                                            name="items[{{ $index }}][warehouse_id]"
                                            required
                                        >
                                            <option value="">Pilih gudang</option>
                                            @foreach($warehouses as $warehouse)
                                                <option value="{{ $warehouse->id }}" @selected(($oldItem['warehouse_id'] ?? '') === $warehouse->id)>
                                                    {{ $warehouse->code }} - {{ $warehouse->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("items.$index.warehouse_id")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td>
                                        <input
                                            type="number"
                                            data-field="quantity"
                                            class="form-control @error("items.$index.quantity") is-invalid @enderror"
                                            name="items[{{ $index }}][quantity]"
                                            value="{{ $oldItem['quantity'] ?? '' }}"
                                            min="0.01"
                                            max="9999999999.99"
                                            step="0.01"
                                            required
                                        >
                                        @error("items.$index.quantity")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td>
                                        <input
                                            type="text"
                                            inputmode="numeric"
                                            data-field="unit_price"
                                            class="form-control @error("items.$index.unit_price") is-invalid @enderror"
                                            name="items[{{ $index }}][unit_price]"
                                            value="{{ ($oldItem['unit_price'] ?? '') !== '' ? number_format((float) $oldItem['unit_price'], 0, ',', '.') : '' }}"
                                            data-money-input
                                            required
                                        >
                                        @error("items.$index.unit_price")
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
                    <a href="{{ route('purchases.index') }}" class="btn btn-label-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Pembelian</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const itemOptions = @json($items->map(fn ($item) => [
            'value' => $item->id,
            'label' => "{$item->code} - {$item->name} (".strtoupper($item->unit).")",
        ])->values());
        const warehouseOptions = @json($warehouses->map(fn ($warehouse) => [
            'value' => $warehouse->id,
            'label' => "{$warehouse->code} - {$warehouse->name}",
        ])->values());
        const tableBody = document.getElementById('purchase-items-body');

        const formatNumber = function (value) {
            const numericValue = value.replace(/\D/g, '');

            if (!numericValue) {
                return '';
            }

            return numericValue.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        };

        const optionHtml = function (options, placeholder) {
            return [
                `<option value="">${placeholder}</option>`,
                ...options.map(function (option) {
                    return `<option value="${option.value}">${option.label}</option>`;
                })
            ].join('');
        };

        const bindMoneyInput = function (input) {
            input.value = formatNumber(input.value);

            input.addEventListener('input', function () {
                input.value = formatNumber(input.value);
            });
        };

        const syncRows = function () {
            tableBody.querySelectorAll('[data-purchase-item-row]').forEach(function (row, index) {
                row.querySelectorAll('[data-field]').forEach(function (field) {
                    field.name = `items[${index}][${field.dataset.field}]`;
                });
            });

            tableBody.querySelectorAll('[data-remove-row]').forEach(function (button) {
                button.disabled = tableBody.querySelectorAll('[data-purchase-item-row]').length === 1;
            });
        };

        const createRow = function () {
            const row = document.createElement('tr');
            row.dataset.purchaseItemRow = '';
            row.innerHTML = `
                <td>
                    <select class="form-select" data-field="item_id" required>
                        ${optionHtml(itemOptions, 'Pilih barang')}
                    </select>
                </td>
                <td>
                    <select class="form-select" data-field="warehouse_id" required>
                        ${optionHtml(warehouseOptions, 'Pilih gudang')}
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control" data-field="quantity" min="0.01" max="9999999999.99" step="0.01" required>
                </td>
                <td>
                    <input type="text" inputmode="numeric" class="form-control" data-field="unit_price" data-money-input required>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-icon btn-label-danger" data-remove-row title="Hapus baris">
                        <i class="bx bx-trash"></i>
                    </button>
                </td>
            `;

            bindMoneyInput(row.querySelector('[data-money-input]'));

            return row;
        };

        document.querySelectorAll('[data-money-input]').forEach(bindMoneyInput);

        document.getElementById('add-purchase-item-row')?.addEventListener('click', function () {
            tableBody.appendChild(createRow());
            syncRows();
        });

        tableBody.addEventListener('click', function (event) {
            const removeButton = event.target.closest('[data-remove-row]');

            if (!removeButton || tableBody.querySelectorAll('[data-purchase-item-row]').length === 1) {
                return;
            }

            removeButton.closest('[data-purchase-item-row]').remove();
            syncRows();
        });

        document.querySelector('form[action="{{ route('purchases.store') }}"]')?.addEventListener('submit', function () {
            document.querySelectorAll('[data-money-input]').forEach(function (input) {
                input.value = input.value.replace(/\./g, '');
            });
        });

        syncRows();
    });
</script>
@endsection
