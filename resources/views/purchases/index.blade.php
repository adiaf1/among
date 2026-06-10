@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
        <div>
            <h4 class="mb-1">Pembelian Barang</h4>
            <p class="text-muted mb-0">Transaksi barang masuk dari supplier ke gudang.</p>
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
                <div class="col-md-8 col-lg-6">
                    <input
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        class="form-control"
                        placeholder="Cari nomor atau supplier"
                    >
                </div>
                <div class="col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-label-primary">Cari</button>
                    @if($search)
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
                        <th>Supplier</th>
                        <th>Total</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $purchase)
                        <tr>
                            <td><span class="fw-medium">{{ $purchase->number }}</span></td>
                            <td>{{ $purchase->purchase_date?->format('d M Y') }}</td>
                            <td>{{ $purchase->supplier->name }}</td>
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
