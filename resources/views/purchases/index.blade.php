@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
        <div>
            <h4 class="mb-1">Pembelian Barang</h4>
            <p class="text-muted mb-0">Transaksi barang masuk dari petani atau supplier ke gudang per periode tanggal.</p>
        </div>
        <a href="{{ route('purchases.create') }}" class="btn btn-primary">
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
            <form method="GET" action="{{ route('purchases.index') }}" class="row g-3 align-items-center">
                <div class="col-md-3 col-lg-2">
                    <label for="start_date" class="form-label mb-1">Tanggal Awal</label>
                    <input
                        type="date"
                        id="start_date"
                        name="start_date"
                        value="{{ $startDate }}"
                        class="form-control"
                    >
                </div>
                <div class="col-md-3 col-lg-2">
                    <label for="end_date" class="form-label mb-1">Tanggal Akhir</label>
                    <input
                        type="date"
                        id="end_date"
                        name="end_date"
                        value="{{ $endDate }}"
                        class="form-control"
                    >
                </div>
                <div class="col-md-6 col-lg-4">
                    <label for="search" class="form-label mb-1">Pencarian</label>
                    <input
                        type="search"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        class="form-control"
                        placeholder="Cari nomor, supplier, atau petani"
                    >
                </div>
                <div class="col-md-auto d-flex gap-2 align-self-end">
                    <button type="submit" class="btn btn-label-primary">Filter</button>
                    @if($search || $startDate !== now()->toDateString() || $endDate !== now()->toDateString())
                        <a href="{{ route('purchases.index') }}" class="btn btn-label-secondary">Reset</a>
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
                        <th>Asal Barang</th>
                        <th>Total</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $purchase)
                        @php
                            $sourceName = $purchase->supplier?->name ?? $purchase->farmer?->name ?? '-';
                            $sourceLabel = $purchase->supplier ? 'Supplier' : ($purchase->farmer ? 'Petani' : '-');
                        @endphp
                        <tr>
                            <td><span class="fw-medium">{{ $purchase->number }}</span></td>
                            <td>{{ $purchase->purchase_date?->format('d M Y') }}</td>
                            <td>
                                <span class="fw-medium">{{ $sourceName }}</span>
                                <div class="text-muted small">{{ $sourceLabel }}</div>
                            </td>
                            <td>Rp {{ number_format((float) $purchase->total_amount, 2, ',', '.') }}</td>
                            <td class="text-end">
                                <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-sm btn-label-primary">
                                    <i class="bx bx-show me-1"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">Belum ada transaksi pembelian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $purchases->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>
@endsection
