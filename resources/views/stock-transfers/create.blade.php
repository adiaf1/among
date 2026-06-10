@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="py-4">
        <h4 class="mb-1">Tambah Mutasi Stok</h4>
        <p class="text-muted mb-0">Pindahkan stok dari gudang asal ke gudang tujuan.</p>
    </div>

    <div class="card">
        <div class="card-body">
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
                            value="{{ old('number') }}"
                            maxlength="100"
                            placeholder="Kosongkan untuk otomatis"
                        >
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
                        ['item_id' => '', 'quantity' => ''],
                    ]);
                @endphp

                <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                    <h6 class="mb-0">Detail Barang</h6>
                    <button type="button" class="btn btn-label-primary btn-sm" id="add-transfer-item-row">
                        <i class="bx bx-plus me-1"></i> Tambah Baris
                    </button>
                </div>

                <div class="table-responsive text-nowrap mb-4">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th style="min-width: 300px;">Barang</th>
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
                                            <i class="bx bx-trash"></i>
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const itemOptions = @json($items->map(fn ($item) => [
            'value' => $item->id,
            'label' => "{$item->code} - {$item->name} (".strtoupper($item->unit).")",
        ])->values());
        const tableBody = document.getElementById('transfer-items-body');

        const optionHtml = function (options, placeholder) {
            return [
                `<option value="">${placeholder}</option>`,
                ...options.map(function (option) {
                    return `<option value="${option.value}">${option.label}</option>`;
                })
            ].join('');
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
                    <input type="number" class="form-control" data-field="quantity" min="0.01" max="9999999999.99" step="0.01" required>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-icon btn-label-danger" data-remove-row title="Hapus baris">
                        <i class="bx bx-trash"></i>
                    </button>
                </td>
            `;

            return row;
        };

        document.getElementById('add-transfer-item-row')?.addEventListener('click', function () {
            tableBody.appendChild(createRow());
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
    });
</script>
@endsection
