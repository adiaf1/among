@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
        <div>
            <h4 class="mb-1">Produksi Benih</h4>
            <p class="text-muted mb-0">Proses pasca panen dari bahan stok sampai siap salur.</p>
        </div>
        <a href="{{ route('seed-productions.create') }}" class="btn btn-primary">
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
            <form method="GET" action="{{ route('seed-productions.index') }}" class="row g-3 align-items-center">
                <div class="col-md-5 col-lg-4">
                    <label for="search" class="form-label mb-1">Pencarian</label>
                    <input
                        type="search"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        class="form-control"
                        placeholder="Cari nomor, lot, varietas, atau kelas benih"
                    >
                </div>
                <div class="col-md-3 col-lg-2">
                    <label for="status" class="form-label mb-1">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">Semua Status</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-auto d-flex gap-2 align-self-end">
                    <button type="submit" class="btn btn-label-primary">Filter</button>
                    @if($search || $status)
                        <a href="{{ route('seed-productions.index') }}" class="btn btn-label-secondary">Reset</a>
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
                        <th>Lot</th>
                        <th>Varietas</th>
                        <th>Target</th>
                        <th>Gudang Hasil</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($seedProductions as $seedProduction)
                        <tr>
                            <td><span class="fw-medium">{{ $seedProduction->number }}</span></td>
                            <td>{{ $seedProduction->production_date?->format('d M Y') }}</td>
                            <td>{{ $seedProduction->lot_number ?: '-' }}</td>
                            <td>
                                <span class="fw-medium">{{ $seedProduction->riceVariety?->name ?? '-' }}</span>
                                <div class="text-muted small">{{ $seedProduction->seedClass?->name ?? '-' }}</div>
                            </td>
                            <td>
                                @if($seedProduction->target_quantity)
                                    {{ number_format((float) $seedProduction->target_quantity, 2, ',', '.') }} {{ $seedProduction->unit }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $seedProduction->outputWarehouse?->name ?? '-' }}</td>
                            <td>
                                @php($badge = match($seedProduction->status) {
                                    'siap_salur' => 'success',
                                    'batal' => 'danger',
                                    default => 'primary',
                                })
                                <span class="badge bg-label-{{ $badge }}">{{ $statuses[$seedProduction->status] ?? $seedProduction->status }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('seed-productions.show', $seedProduction) }}" class="btn btn-sm btn-label-primary">
                                    <i class="bx bx-show me-1"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">Belum ada data produksi benih.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $seedProductions->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>
@endsection
