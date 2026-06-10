@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
        <div>
            <h4 class="mb-1">Kartu Stok</h4>
            <p class="text-muted mb-0">{{ $stock->item->code }} - {{ $stock->item->name }} di {{ $stock->warehouse->name }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('stocks.index') }}" class="btn btn-label-secondary">Kembali</a>
            <a href="{{ route('stocks.create') }}" class="btn btn-primary">Penyesuaian</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted mb-1">Saldo Saat Ini</div>
                    <h4 class="mb-0">{{ number_format((float) $stock->quantity, 2, ',', '.') }} {{ strtoupper($stock->item->unit) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted mb-1">Stok Minimum</div>
                    <h4 class="mb-0">{{ number_format((float) $stock->item->minimum_stock, 2, ',', '.') }} {{ strtoupper($stock->item->unit) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted mb-1">Status</div>
                    @if((float) $stock->quantity <= (float) $stock->item->minimum_stock)
                        <span class="badge bg-label-warning">Minimum</span>
                    @else
                        <span class="badge bg-label-success">Aman</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Tipe</th>
                        <th>Masuk</th>
                        <th>Keluar</th>
                        <th>Saldo</th>
                        <th>Referensi</th>
                        <th>Catatan</th>
                        <th>User</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $movement)
                        <tr>
                            <td>{{ $movement->movement_date?->format('d M Y') }}</td>
                            <td><span class="badge bg-label-info">{{ ucfirst(str_replace('_', ' ', $movement->type)) }}</span></td>
                            <td>{{ number_format((float) $movement->quantity_in, 2, ',', '.') }}</td>
                            <td>{{ number_format((float) $movement->quantity_out, 2, ',', '.') }}</td>
                            <td>{{ number_format((float) $movement->balance_after, 2, ',', '.') }}</td>
                            <td>{{ $movement->reference_number ?: '-' }}</td>
                            <td>{{ $movement->notes ?: '-' }}</td>
                            <td>{{ $movement->creator?->name ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">Belum ada riwayat stok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $movements->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>
@endsection
