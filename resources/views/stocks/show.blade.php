@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
        <div>
            <h4 class="mb-1">Kartu Stok</h4>
            <p class="text-muted mb-0">
                {{ $stock->item->code }} - {{ $stock->item->name }} di {{ $stock->warehouse->name }}
                @if($stock->lot_number)
                    | Lot {{ $stock->lot_number }}
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('stocks.index') }}" class="btn btn-label-secondary">
                <i class="icon-base bx bx-arrow-back me-1"></i> Kembali
            </a>
            <a
                href="{{ route('stocks.create', ['item_id' => $stock->item_id, 'warehouse_id' => $stock->warehouse_id, 'lot_number' => $stock->lot_number]) }}"
                class="btn btn-primary"
            >
                <i class="icon-base bx bx-edit me-1"></i> Penyesuaian
            </a>
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
                    <div class="text-muted mb-1">No Lot</div>
                    <h4 class="mb-0">{{ $stock->lot_number ?: '-' }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center border-bottom">
            <h5 class="mb-0">Riwayat Pergerakan Stok</h5>
            @if((float) $stock->quantity <= (float) $stock->item->minimum_stock)
                <span class="badge bg-label-warning">Stok Minimum</span>
            @else
                <span class="badge bg-label-success">Stok Aman</span>
            @endif
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Tipe</th>
                        <th class="text-end">Masuk</th>
                        <th class="text-end">Keluar</th>
                        <th class="text-end">Saldo</th>
                        <th>Referensi</th>
                        <th>Catatan</th>
                        <th>User</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @php
                        $movementTypes = [
                            'purchase' => ['label' => 'Pembelian', 'badge' => 'bg-label-success'],
                            'transfer_in' => ['label' => 'Mutasi Masuk', 'badge' => 'bg-label-success'],
                            'transfer_out' => ['label' => 'Mutasi Keluar', 'badge' => 'bg-label-danger'],
                            'adjustment' => ['label' => 'Penyesuaian', 'badge' => 'bg-label-warning'],
                            'production_in' => ['label' => 'Hasil Produksi', 'badge' => 'bg-label-success'],
                            'production_out' => ['label' => 'Pemakaian Produksi', 'badge' => 'bg-label-danger'],
                        ];
                    @endphp
                    @forelse($movements as $movement)
                        @php
                            $typeMeta = $movementTypes[$movement->type] ?? [
                                'label' => ucfirst(str_replace('_', ' ', $movement->type)),
                                'badge' => 'bg-label-info',
                            ];
                        @endphp
                        <tr>
                            <td>{{ $movement->movement_date?->format('d M Y') }}</td>
                            <td><span class="badge {{ $typeMeta['badge'] }}">{{ $typeMeta['label'] }}</span></td>
                            <td class="text-end">{{ number_format((float) $movement->quantity_in, 2, ',', '.') }}</td>
                            <td class="text-end">{{ number_format((float) $movement->quantity_out, 2, ',', '.') }}</td>
                            <td class="text-end fw-medium">{{ number_format((float) $movement->balance_after, 2, ',', '.') }}</td>
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

        <div class="card-footer d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <form method="GET" action="{{ route('stocks.show', $stock) }}" class="d-flex align-items-center gap-2">
                    <label for="per_page" class="text-muted small mb-0">Tampilkan</label>
                    <select id="per_page" name="per_page" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                        @foreach([10, 25, 50, 100] as $size)
                            <option value="{{ $size }}" @selected($perPage === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </form>
                <span class="text-muted small">
                    Menampilkan {{ $movements->firstItem() ?? 0 }} - {{ $movements->lastItem() ?? 0 }} dari {{ $movements->total() }} data
                </span>
            </div>
            <div>
                {{ $movements->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
@endsection
