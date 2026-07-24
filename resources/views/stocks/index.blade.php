@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
        <div>
            <h4 class="mb-1">Stok</h4>
            <p class="text-muted mb-0">Saldo stok barang per gudang dan riwayat pergerakannya.</p>
        </div>
        <a href="{{ route('stocks.create') }}" class="btn btn-primary">
            <i class="bx bx-edit me-1"></i> Penyesuaian Stok
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header border-bottom">
            <form method="GET" action="{{ route('stocks.index') }}" class="row g-3 align-items-center">
                <div class="col-md-4">
                    <input
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        class="form-control"
                        placeholder="Cari barang, gudang, atau no lot"
                    >
                </div>
                <div class="col-md-3">
                    <select name="item_id" class="form-select">
                        <option value="">Semua barang</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" @selected($itemId === $item->id)>
                                {{ $item->code }} - {{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="warehouse_id" class="form-select">
                        <option value="">Semua gudang</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected($warehouseId === $warehouse->id)>
                                {{ $warehouse->code }} - {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-label-primary">Filter</button>
                    @if($search || $itemId || $warehouseId)
                        <a href="{{ route('stocks.index') }}" class="btn btn-label-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Gudang</th>
                        <th>No Lot</th>
                        <th>Satuan</th>
                        <th>Stok Minimum</th>
                        <th>Saldo Stok</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $stock)
                        <tr>
                            <td>
                                <span class="fw-medium">{{ $stock->item->code }}</span>
                                <div class="text-muted small">{{ $stock->item->name }}</div>
                            </td>
                            <td>
                                <span class="fw-medium">{{ $stock->warehouse->code }}</span>
                                <div class="text-muted small">{{ $stock->warehouse->name }}</div>
                            </td>
                            <td>{{ $stock->lot_number ?: '-' }}</td>
                            <td>{{ strtoupper($stock->item->unit) }}</td>
                            <td>{{ number_format((float) $stock->item->minimum_stock, 2, ',', '.') }}</td>
                            <td>{{ number_format((float) $stock->quantity, 2, ',', '.') }}</td>
                            <td>
                                @if((float) $stock->quantity <= (float) $stock->item->minimum_stock)
                                    <span class="badge bg-label-warning">Minimum</span>
                                @else
                                    <span class="badge bg-label-success">Aman</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('stocks.show', $stock) }}" class="btn btn-sm btn-label-primary">
                                    <i class="bx bx-list-ul me-1"></i> Kartu Stok
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">Belum ada saldo stok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $stocks->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>
@endsection
