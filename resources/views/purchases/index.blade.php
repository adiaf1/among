@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
        <div>
            <h4 class="mb-1">Pembelian Barang</h4>
            <p class="text-muted mb-0">Transaksi barang masuk dari petani atau supplier ke gudang per periode tanggal.</p>
        </div>
        <a href="{{ route('purchases.create') }}" class="btn btn-primary">
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
        <div class="card-header border-bottom">
            <form method="GET" action="{{ route('purchases.index') }}" class="row g-3 align-items-center">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <div class="col-md-3 col-lg-2">
                    <label for="start_date" class="form-label mb-1">Tanggal Awal</label>
                    <input
                        type="date"
                        id="start_date"
                        name="start_date"
                        value="{{ $startDate }}"
                        class="form-control"
                        onchange="this.form.submit()"
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
                        onchange="this.form.submit()"
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
                @if($search || $startDate !== now()->toDateString() || $endDate !== now()->toDateString())
                    <div class="col-md-auto align-self-end">
                        <a href="{{ route('purchases.index') }}" class="btn btn-label-secondary">Reset</a>
                    </div>
                @endif
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
                <tbody class="table-border-bottom-0">
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
                                <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-sm btn-outline-primary">
                                    <span class="icon-base bx bx-show icon-sm me-1"></span>Detail
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

        <div class="card-footer d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <form method="GET" action="{{ route('purchases.index') }}" class="d-flex align-items-center gap-2">
                    <input type="hidden" name="start_date" value="{{ $startDate }}">
                    <input type="hidden" name="end_date" value="{{ $endDate }}">
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
                    Menampilkan {{ $purchases->firstItem() ?? 0 }} - {{ $purchases->lastItem() ?? 0 }} dari {{ $purchases->total() }} data
                </span>
            </div>
            <div>
                {{ $purchases->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
@endsection
