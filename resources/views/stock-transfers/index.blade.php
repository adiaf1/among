@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
        <div>
            <h4 class="mb-1">Mutasi Stok</h4>
            <p class="text-muted mb-0">Perpindahan stok barang dari satu gudang ke gudang lain.</p>
        </div>
        <a href="{{ route('stock-transfers.create') }}" class="btn btn-primary">
            <i class="icon-base bx bx-plus me-1"></i> Tambah
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <h5 class="card-title mb-0">Daftar Mutasi Stok</h5>
            <form method="GET" action="{{ route('stock-transfers.index') }}" class="d-flex align-items-center gap-2">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <div class="input-group input-group-merge" style="min-width: 260px;">
                    <span class="input-group-text"><i class="icon-base bx bx-search"></i></span>
                    <input
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        class="form-control"
                        placeholder="Cari nomor atau gudang..."
                    >
                </div>
                @if($search)
                    <a href="{{ route('stock-transfers.index', ['per_page' => $perPage]) }}" class="btn btn-label-secondary">Reset</a>
                @endif
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
                <tbody class="table-border-bottom-0">
                    @forelse($stockTransfers as $stockTransfer)
                        <tr>
                            <td><span class="fw-medium">{{ $stockTransfer->number }}</span></td>
                            <td>{{ $stockTransfer->transfer_date?->format('d M Y') }}</td>
                            <td>{{ $stockTransfer->sourceWarehouse->name }}</td>
                            <td>{{ $stockTransfer->destinationWarehouse->name }}</td>
                            <td class="text-end">
                                <a href="{{ route('stock-transfers.show', $stockTransfer) }}" class="btn btn-sm btn-outline-primary">
                                    <span class="icon-base bx bx-show icon-sm me-1"></span>Detail
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

        <div class="card-footer d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <form method="GET" action="{{ route('stock-transfers.index') }}" class="d-flex align-items-center gap-2">
                    @if($search)
                        <input type="hidden" name="search" value="{{ $search }}">
                    @endif
                    <label for="per_page" class="text-muted small mb-0">Tampilkan</label>
                    <select id="per_page" name="per_page" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                        @foreach([10, 25, 50, 100] as $size)
                            <option value="{{ $size }}" @selected($perPage === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </form>
                <span class="text-muted small">
                    Menampilkan {{ $stockTransfers->firstItem() ?? 0 }} - {{ $stockTransfers->lastItem() ?? 0 }} dari {{ $stockTransfers->total() }} data
                </span>
            </div>
            <div>
                {{ $stockTransfers->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
@endsection
