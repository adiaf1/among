@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
        <div>
            <h4 class="mb-1">Penangkaran Benih</h4>
            <p class="text-muted mb-0">Daftar no lapangan penangkaran, varietas, jadwal pemeriksaan, dan panen.</p>
        </div>
        <a href="{{ route('seed-growings.create') }}" class="btn btn-primary">
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
            <form method="GET" action="{{ route('seed-growings.index') }}" class="row g-3 align-items-center">
                <div class="col-md-5 col-lg-4">
                    <label for="search" class="form-label mb-1">Pencarian</label>
                    <input
                        type="search"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        class="form-control"
                        placeholder="Cari nomor, no lapangan, no lot, petani, lahan, atau varietas"
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
                        <a href="{{ route('seed-growings.index') }}" class="btn btn-label-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nomor</th>
                        <th>No Lapangan</th>
                        <th>Petani / Lahan</th>
                        <th>Varietas</th>
                        <th>Luas</th>
                        <th>Tanam / Panen</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($seedGrowings as $seedGrowing)
                        <tr>
                            <td><span class="fw-medium">{{ $seedGrowing->number }}</span></td>
                            <td>
                                <span class="fw-medium">{{ $seedGrowing->field_number }}</span>
                                <div class="text-muted small">Lot: {{ $seedGrowing->lot_number ?: '-' }}</div>
                            </td>
                            <td>
                                <span class="fw-medium">{{ $seedGrowing->farmer?->name ?? '-' }}</span>
                                <div class="text-muted small">{{ $seedGrowing->land?->name ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="fw-medium">{{ $seedGrowing->riceVariety?->name ?? '-' }}</span>
                                <div class="text-muted small">{{ $seedGrowing->seedClass?->name ?? '-' }}</div>
                            </td>
                            <td>{{ number_format((float) $seedGrowing->field_area, 2, ',', '.') }} ha</td>
                            <td>
                                <span>{{ $seedGrowing->planting_date?->format('d M Y') ?? '-' }}</span>
                                <div class="text-muted small">Panen: {{ $seedGrowing->harvest_date?->format('d M Y') ?? '-' }}</div>
                            </td>
                            <td>
                                @php($badge = match($seedGrowing->status) {
                                    'berjalan' => 'primary',
                                    'panen' => 'info',
                                    'selesai' => 'success',
                                    'batal' => 'danger',
                                    default => 'secondary',
                                })
                                <span class="badge bg-label-{{ $badge }}">{{ $statuses[$seedGrowing->status] ?? $seedGrowing->status }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('seed-growings.show', $seedGrowing) }}" class="btn btn-sm btn-label-primary">
                                    <i class="bx bx-show me-1"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">Belum ada data penangkaran benih.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $seedGrowings->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>
@endsection
