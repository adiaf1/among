@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
        <div>
            <h4 class="mb-1">Penjualan Barang</h4>
            <p class="text-muted mb-0">Transaksi penjualan dan penyaluran benih/barang keluar ke pelanggan per periode tanggal.</p>
        </div>
        <a href="{{ route('sales.create') }}" class="btn btn-primary">
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
            <form method="GET" action="{{ route('sales.index') }}" class="row g-3 align-items-center">
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
                <div class="col-md-3 col-lg-2">
                    <label for="payment_status" class="form-label mb-1">Status Bayar</label>
                    <select
                        id="payment_status"
                        name="payment_status"
                        class="form-select"
                        onchange="this.form.submit()"
                    >
                        <option value="">Semua Status</option>
                        @foreach($paymentStatuses as $value => $label)
                            <option value="{{ $value }}" @selected($paymentStatus === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-lg-4">
                    <label for="search" class="form-label mb-1">Pencarian</label>
                    <input
                        type="search"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        class="form-control"
                        placeholder="Cari nomor atau nama/kode pelanggan"
                    >
                </div>
                @if($search || $paymentStatus || $startDate !== now()->toDateString() || $endDate !== now()->toDateString())
                    <div class="col-md-auto align-self-end">
                        <a href="{{ route('sales.index') }}" class="btn btn-label-secondary">Reset</a>
                    </div>
                @endif
            </form>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nomor</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th class="text-center">Item</th>
                        <th>Status Bayar</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($sales as $sale)
                        @php
                            $customerTypeLabel = $customerTypes[$sale->customer?->customer_type ?? ''] ?? ucfirst((string) ($sale->customer?->customer_type ?? '-'));
                            $isPaid = $sale->payment_status === 'lunas';
                            $paymentBadge = $isPaid ? 'bg-label-success' : 'bg-label-warning';
                            $paymentLabel = $paymentStatuses[$sale->payment_status] ?? ucfirst(str_replace('_', ' ', (string) $sale->payment_status));
                        @endphp
                        <tr>
                            <td><span class="fw-medium">{{ $sale->number }}</span></td>
                            <td>{{ $sale->sale_date?->format('d M Y') }}</td>
                            <td>
                                <span class="fw-medium">{{ $sale->customer?->name ?? '-' }}</span>
                                <div class="text-muted small">{{ $sale->customer?->code ?? '-' }} &middot; {{ $customerTypeLabel }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-label-primary">{{ $sale->items_count ?? 0 }} item</span>
                            </td>
                            <td>
                                <span class="badge {{ $paymentBadge }}">{{ $paymentLabel }}</span>
                            </td>
                            <td class="text-end fw-medium">Rp {{ number_format((float) $sale->total_amount, 2, ',', '.') }}</td>
                            <td class="text-end">
                                <a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-outline-primary">
                                    <span class="icon-base bx bx-show icon-sm me-1"></span>Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">Belum ada transaksi penjualan pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <form method="GET" action="{{ route('sales.index') }}" class="d-flex align-items-center gap-2">
                    <input type="hidden" name="start_date" value="{{ $startDate }}">
                    <input type="hidden" name="end_date" value="{{ $endDate }}">
                    @if($paymentStatus)
                        <input type="hidden" name="payment_status" value="{{ $paymentStatus }}">
                    @endif
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
                    Menampilkan {{ $sales->firstItem() ?? 0 }} - {{ $sales->lastItem() ?? 0 }} dari {{ $sales->total() }} data
                </span>
            </div>
            <div>
                {{ $sales->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
@endsection
