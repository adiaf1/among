@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
        <div>
            <h4 class="mb-1">Detail Mutasi Stok</h4>
            <p class="text-muted mb-0">{{ $stockTransfer->number }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('stock-transfers.index') }}" class="btn btn-label-secondary">Kembali</a>
            <a href="{{ route('stock-transfers.create') }}" class="btn btn-primary">Tambah</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Nomor</dt>
                <dd class="col-sm-9">{{ $stockTransfer->number }}</dd>

                <dt class="col-sm-3">Tanggal</dt>
                <dd class="col-sm-9">{{ $stockTransfer->transfer_date?->format('d M Y') }}</dd>

                <dt class="col-sm-3">Gudang Asal</dt>
                <dd class="col-sm-9">{{ $stockTransfer->sourceWarehouse->name }}</dd>

                <dt class="col-sm-3">Gudang Tujuan</dt>
                <dd class="col-sm-9">{{ $stockTransfer->destinationWarehouse->name }}</dd>

                <dt class="col-sm-3">Catatan</dt>
                <dd class="col-sm-9">{{ $stockTransfer->notes ?: '-' }}</dd>

                <dt class="col-sm-3">Dibuat Oleh</dt>
                <dd class="col-sm-9">{{ $stockTransfer->creator?->name ?: '-' }}</dd>
            </dl>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stockTransfer->items as $detail)
                        <tr>
                            <td>
                                <span class="fw-medium">{{ $detail->item->code }}</span>
                                <div class="text-muted small">{{ $detail->item->name }}</div>
                            </td>
                            <td>{{ number_format((float) $detail->quantity, 2, ',', '.') }} {{ strtoupper($detail->item->unit) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
