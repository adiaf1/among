@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="py-4">
        <h4 class="mb-1">Penyesuaian Stok</h4>
        <p class="text-muted mb-0">Isi saldo awal atau sesuaikan saldo stok barang di gudang.</p>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('stocks.store') }}">
                @csrf

                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="item_id" class="form-label">Barang <span class="text-danger">*</span></label>
                        <select
                            class="form-select @error('item_id') is-invalid @enderror"
                            id="item_id"
                            name="item_id"
                            required
                        >
                            <option value="">Pilih barang</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" @selected(old('item_id') === $item->id)>
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
                            class="form-select @error('warehouse_id') is-invalid @enderror"
                            id="warehouse_id"
                            name="warehouse_id"
                            required
                        >
                            <option value="">Pilih gudang</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" @selected(old('warehouse_id') === $warehouse->id)>
                                    {{ $warehouse->code }} - {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('warehouse_id')
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
@endsection
