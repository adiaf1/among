@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
        <div>
            <h4 class="mb-1">Mutasi Stok</h4>
            <p class="text-muted mb-0">Perpindahan stok barang dari satu gudang ke gudang lain.</p>
        </div>
        <a href="{{ route('stock-transfers.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> Tambah
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
            <form method="GET" action="{{ route('stock-transfers.index') }}" class="row g-3 align-items-center">
                <div class="col-md-8 col-lg-6">
                    <input
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        class="form-control"
                        placeholder="Cari nomor atau gudang"
                    >
                </div>
                <div class="col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-label-primary">Cari</button>
                    @if($search)
                        <a href="{{ route('stock-transfers.index') }}" class="btn btn-label-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nomor</th>
                        <th>Tanggal</th>
                        <th>Gudang Asal</th>
                        <th>Gudang Tujuan</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stockTransfers as $stockTransfer)
                        <tr>
                            <td><span class="fw-medium">{{ $stockTransfer->number }}</span></td>
                            <td>{{ $stockTransfer->transfer_date?->format('d M Y') }}</td>
                            <td>{{ $stockTransfer->sourceWarehouse->name }}</td>
                            <td>{{ $stockTransfer->destinationWarehouse->name }}</td>
                            <td class="text-end">
                                <a href="{{ route('stock-transfers.show', $stockTransfer) }}" class="btn btn-sm btn-label-primary">
                                    <i class="bx bx-show me-1"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">Belum ada mutasi stok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $stockTransfers->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>
@endsection
