@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
        <div>
            <h4 class="mb-1">Tambah Mutasi Stok</h4>
            <p class="text-muted mb-0">Pindahkan stok dari gudang asal ke gudang tujuan.</p>
        </div>
        <a href="{{ route('stock-transfers.index') }}" class="btn btn-label-secondary">
            <i class="icon-base bx bx-arrow-back me-1"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center border-bottom">
            <h5 class="mb-0">Form Mutasi Stok</h5>
            <small class="text-body-secondary float-end">Persediaan</small>
        </div>
        <div class="card-body pt-4">
            <form method="POST" action="{{ route('stock-transfers.store') }}">
                @csrf

                @error('items')
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
                        <div class="form-text">Otomatis (<code>MT-YYYYMMDD-XXXX</code>), bisa diubah.</div>
                        @error('number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="transfer_date" class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input
                            type="date"
                            class="form-control @error('transfer_date') is-invalid @enderror"
                            id="transfer_date"
                            name="transfer_date"
                            value="{{ old('transfer_date', now()->toDateString()) }}"
                            required
                        >
                        @error('transfer_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="source_warehouse_id" class="form-label">Gudang Asal <span class="text-danger">*</span></label>
                        <select
                            class="form-select @error('source_warehouse_id') is-invalid @enderror"
                            id="source_warehouse_id"
                            name="source_warehouse_id"
                            required
                        >
                            <option value="">Pilih gudang</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" @selected(old('source_warehouse_id') === $warehouse->id)>
                                    {{ $warehouse->code }} - {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('source_warehouse_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="destination_warehouse_id" class="form-label">Gudang Tujuan <span class="text-danger">*</span></label>
                        <select
                            class="form-select @error('destination_warehouse_id') is-invalid @enderror"
                            id="destination_warehouse_id"
                            name="destination_warehouse_id"
                            required
                        >
                            <option value="">Pilih gudang</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" @selected(old('destination_warehouse_id') === $warehouse->id)>
                                    {{ $warehouse->code }} - {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('destination_warehouse_id')
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
                            placeholder="Catatan mutasi stok"
                        >{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                @php
                    $oldItems = old('items', [
                        ['item_id' => '', 'stock_id' => '', 'quantity' => ''],
                    ]);
                @endphp

                <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                    <h6 class="mb-0">Detail Barang</h6>
                    <button type="button" class="btn btn-label-primary btn-sm" id="add-transfer-item-row">
                        <i class="icon-base bx bx-plus me-1"></i> Tambah Baris
                    </button>
                </div>

                <div class="table-responsive transfer-items-table-wrap text-nowrap mb-4">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th style="min-width: 260px;">Barang</th>
                                <th style="min-width: 240px;">Lot / Stok Asal</th>
                                <th style="min-width: 160px;">Jumlah</th>
                                <th style="width: 72px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="transfer-items-body">
                            @foreach($oldItems as $index => $oldItem)
                                <tr data-transfer-item-row>
                                    <td>
                                        <select
                                            data-field="item_id"
                                            data-selected="{{ $oldItem['item_id'] ?? '' }}"
                                            class="select2 form-select @error("items.$index.item_id") is-invalid @enderror"
                                            name="items[{{ $index }}][item_id]"
                                            data-placeholder="Pilih barang"
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
                                            data-field="stock_id"
                                            data-selected="{{ $oldItem['stock_id'] ?? '' }}"
                                            class="form-select @error("items.$index.stock_id") is-invalid @enderror"
                                            name="items[{{ $index }}][stock_id]"
                                        >
                                            <option value="">Otomatis / Pilih lot</option>
                                        </select>
                                        @error("items.$index.stock_id")
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
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-icon btn-label-danger" data-remove-row title="Hapus baris">
                                            <i class="icon-base bx bx-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
                    <a href="{{ route('stock-transfers.index') }}" class="btn btn-label-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Mutasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    @media (min-width: 768px) {
        .transfer-items-table-wrap {
            overflow: visible;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const itemOptions = @json($items->map(fn ($item) => [
            'value' => $item->id,
            'label' => "{$item->code} - {$item->name} (".strtoupper($item->unit).")",
        ])->values());
        const availableStocks = @json($availableStocks);
        const sourceWarehouseSelect = document.getElementById('source_warehouse_id');
        const tableBody = document.getElementById('transfer-items-body');

        const optionHtml = function (options, placeholder) {
            return [
                `<option value="">${placeholder}</option>`,
                ...options.map(function (option) {
                    return `<option value="${option.value}">${option.label}</option>`;
                })
            ].join('');
        };

        const formatQty = function (qty) {
            return Number(qty).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        };

        const syncStockOptions = function (row) {
            const warehouseId = sourceWarehouseSelect?.value || '';
            const itemSelect = row.querySelector('[data-field="item_id"]');
            const stockSelect = row.querySelector('[data-field="stock_id"]');
            if (!itemSelect || !stockSelect) return;

            const itemId = itemSelect.value;
            const previousValue = stockSelect.value || stockSelect.dataset.selected || '';
            stockSelect.dataset.selected = '';

            if (!warehouseId || !itemId) {
                stockSelect.innerHTML = '<option value="">Pilih gudang asal & barang</option>';
                return;
            }

            const matches = availableStocks.filter(function (s) {
                return s.warehouse_id === warehouseId && s.item_id === itemId;
            });

            if (!matches.length) {
                stockSelect.innerHTML = '<option value="">Stok kosong di gudang asal</option>';
                return;
            }

            stockSelect.innerHTML = [
                '<option value="">Pilih lot / stok</option>',
                ...matches.map(function (s) {
                    const lotLabel = s.lot_number ? `Lot ${s.lot_number}` : 'Tanpa Lot';
                    return `<option value="${s.id}">${lotLabel} (Saldo: ${formatQty(s.quantity)} ${s.unit})</option>`;
                })
            ].join('');

            if (previousValue && matches.some(function (s) { return s.id === previousValue; })) {
                stockSelect.value = previousValue;
            } else if (matches.length === 1) {
                stockSelect.value = matches[0].id;
            }
        };

        const syncItemOptions = function (row) {
            const warehouseId = sourceWarehouseSelect?.value || '';
            const itemSelect = row.querySelector('[data-field="item_id"]');
            if (!itemSelect) return;

            const previousValue = itemSelect.value || itemSelect.dataset.selected || '';
            itemSelect.dataset.selected = '';

            if (!warehouseId) {
                itemSelect.innerHTML = '<option value="">Pilih gudang asal terlebih dahulu</option>';
                itemSelect.value = '';
            } else {
                const availableItemIds = new Set(
                    availableStocks
                        .filter(function (s) { return s.warehouse_id === warehouseId; })
                        .map(function (s) { return s.item_id; })
                );

                const filteredItems = itemOptions.filter(function (option) {
                    return availableItemIds.has(option.value);
                });

                if (!filteredItems.length) {
                    itemSelect.innerHTML = '<option value="">Tidak ada stok di gudang asal</option>';
                    itemSelect.value = '';
                } else {
                    itemSelect.innerHTML = optionHtml(filteredItems, 'Pilih barang');
                    itemSelect.value = availableItemIds.has(previousValue) ? previousValue : '';
                }
            }

            if (window.jQuery && window.jQuery(itemSelect).hasClass('select2-hidden-accessible')) {
                window.jQuery(itemSelect).trigger('change.select2');
            }

            syncStockOptions(row);
        };

        const syncAllRowOptions = function () {
            tableBody.querySelectorAll('[data-transfer-item-row]').forEach(syncItemOptions);
        };

        const syncRows = function () {
            tableBody.querySelectorAll('[data-transfer-item-row]').forEach(function (row, index) {
                row.querySelectorAll('[data-field]').forEach(function (field) {
                    field.name = `items[${index}][${field.dataset.field}]`;
                });
            });

            tableBody.querySelectorAll('[data-remove-row]').forEach(function (button) {
                button.disabled = tableBody.querySelectorAll('[data-transfer-item-row]').length === 1;
            });
        };

        const initRowSelect2 = function (row) {
            if (!window.jQuery || !window.jQuery.fn.select2) return;

            const itemSelect = row.querySelector('[data-field="item_id"]');
            if (!itemSelect) return;

            const $select = window.jQuery(itemSelect);
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.on('change.stockSync', function () {
                    syncStockOptions(row);
                });
                return;
            }

            $select.wrap('<div class="position-relative"></div>').select2({
                placeholder: 'Pilih barang',
                dropdownParent: $select.parent()
            }).on('change.stockSync', function () {
                syncStockOptions(row);
            });
        };

        const createRow = function () {
            const row = document.createElement('tr');
            row.dataset.transferItemRow = '';
            row.innerHTML = `
                <td>
                    <select class="form-select" data-field="item_id" required>
                        ${optionHtml(itemOptions, 'Pilih barang')}
                    </select>
                </td>
                <td>
                    <select class="form-select" data-field="stock_id">
                        <option value="">Pilih gudang asal & barang</option>
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control" data-field="quantity" min="0.01" max="9999999999.99" step="0.01" required>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-icon btn-label-danger" data-remove-row title="Hapus baris">
                        <i class="icon-base bx bx-trash"></i>
                    </button>
                </td>
            `;

            syncItemOptions(row);

            return row;
        };

        sourceWarehouseSelect?.addEventListener('change', syncAllRowOptions);

        tableBody.addEventListener('change', function (event) {
            if (event.target.matches('[data-field="item_id"]')) {
                const row = event.target.closest('[data-transfer-item-row]');
                if (row) syncStockOptions(row);
            }
        });

        document.getElementById('add-transfer-item-row')?.addEventListener('click', function () {
            const row = createRow();
            tableBody.appendChild(row);
            initRowSelect2(row);
            syncRows();
        });

        tableBody.addEventListener('click', function (event) {
            const removeButton = event.target.closest('[data-remove-row]');

            if (!removeButton || tableBody.querySelectorAll('[data-transfer-item-row]').length === 1) {
                return;
            }

            removeButton.closest('[data-transfer-item-row]').remove();
            syncRows();
        });

        syncRows();
        tableBody.querySelectorAll('[data-transfer-item-row]').forEach(initRowSelect2);
        syncAllRowOptions();
    });
</script>
@endsection
