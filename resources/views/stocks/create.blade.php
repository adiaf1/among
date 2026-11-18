@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
        <div>
            <h4 class="mb-1">Penyesuaian Stok</h4>
            <p class="text-muted mb-0">Isi saldo awal atau sesuaikan saldo stok barang di gudang.</p>
        </div>
        <a href="{{ route('stocks.index') }}" class="btn btn-label-secondary">
            <i class="icon-base bx bx-arrow-back me-1"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center border-bottom">
            <h5 class="mb-0">Form Penyesuaian Stok</h5>
            <small class="text-body-secondary float-end">Persediaan</small>
        </div>
        <div class="card-body pt-4">
            <form method="POST" action="{{ route('stocks.store') }}">
                @csrf

                @php
                    $selectedItemId = old('item_id', request('item_id', ''));
                    $selectedWarehouseId = old('warehouse_id', request('warehouse_id', ''));
                    $selectedLotNumber = old('lot_number', request('lot_number', ''));
                @endphp

                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="item_id" class="form-label">Barang <span class="text-danger">*</span></label>
                        <select
                            class="select2 form-select @error('item_id') is-invalid @enderror"
                            id="item_id"
                            name="item_id"
                            data-placeholder="Pilih barang"
                            required
                        >
                            <option value="">Pilih barang</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" data-unit="{{ strtoupper($item->unit) }}" @selected($selectedItemId === $item->id)>
                                    {{ $item->code }} - {{ $item->name }} ({{ strtoupper($item->unit) }})
                                </option>
                            @endforeach
                        </select>
                        @error('item_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="warehouse_id" class="form-label">Gudang <span class="text-danger">*</span></label>
                        <select
                            class="select2 form-select @error('warehouse_id') is-invalid @enderror"
                            id="warehouse_id"
                            name="warehouse_id"
                            data-placeholder="Pilih gudang"
                            required
                        >
                            <option value="">Pilih gudang</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" @selected($selectedWarehouseId === $warehouse->id)>
                                    {{ $warehouse->code }} - {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('warehouse_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="lot_number" class="form-label">No Lot</label>
                        <input
                            type="text"
                            class="form-control @error('lot_number') is-invalid @enderror"
                            id="lot_number"
                            name="lot_number"
                            value="{{ $selectedLotNumber }}"
                            maxlength="100"
                            placeholder="Kosongkan jika tidak memakai lot"
                        >
                        @error('lot_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="quantity" class="form-label">Saldo Stok Baru <span class="text-danger">*</span></label>
                        <input
                            type="number"
                            class="form-control @error('quantity') is-invalid @enderror"
                            id="quantity"
                            name="quantity"
                            value="{{ old('quantity', 0) }}"
                            min="0"
                            max="9999999999.99"
                            step="0.01"
                            required
                        >
                        <div class="form-text" data-current-stock-info>Pilih barang dan gudang untuk melihat saldo saat ini.</div>
                        @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="movement_date" class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input
                            type="date"
                            class="form-control @error('movement_date') is-invalid @enderror"
                            id="movement_date"
                            name="movement_date"
                            value="{{ old('movement_date', now()->toDateString()) }}"
                            required
                        >
                        @error('movement_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="notes" class="form-label">Catatan</label>
                        <textarea
                            class="form-control @error('notes') is-invalid @enderror"
                            id="notes"
                            name="notes"
                            rows="3"
                            placeholder="Contoh: stok awal, hasil opname, atau koreksi saldo"
                        >{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end mt-5">
                    <a href="{{ route('stocks.index') }}" class="btn btn-label-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Penyesuaian</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const existingStocks = @json($existingStocks);
        const itemSelect = document.getElementById('item_id');
        const warehouseSelect = document.getElementById('warehouse_id');
        const lotInput = document.getElementById('lot_number');
        const infoEl = document.querySelector('[data-current-stock-info]');

        const normalizeLot = function (value) {
            return (value || '').trim().replace(/\s+/g, ' ').toUpperCase();
        };

        const updateCurrentStockInfo = function () {
            if (!infoEl) return;

            const itemId = itemSelect?.value || '';
            const warehouseId = warehouseSelect?.value || '';
            if (!itemId || !warehouseId) {
                infoEl.textContent = 'Pilih barang dan gudang untuk melihat saldo saat ini.';
                return;
            }

            const unit = itemSelect.options[itemSelect.selectedIndex]?.dataset.unit || '';
            const lot = normalizeLot(lotInput?.value || '');
            const match = existingStocks.find(function (s) {
                return s.item_id === itemId && s.warehouse_id === warehouseId && s.lot_number === lot;
            });

            const qty = match ? match.quantity : 0;
            const formattedQty = Number(qty).toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
            const statusSuffix = match ? '' : ' (stok baru)';
            infoEl.textContent = `Saldo saat ini: ${formattedQty} ${unit}${statusSuffix}`;
        };

        itemSelect?.addEventListener('change', updateCurrentStockInfo);
        warehouseSelect?.addEventListener('change', updateCurrentStockInfo);
        lotInput?.addEventListener('input', updateCurrentStockInfo);

        if (window.jQuery) {
            window.jQuery(itemSelect).on('change', updateCurrentStockInfo);
            window.jQuery(warehouseSelect).on('change', updateCurrentStockInfo);
        }

        updateCurrentStockInfo();
    });
</script>
@endsection
