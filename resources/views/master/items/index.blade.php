@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
        <div>
            <h4 class="mb-1">Barang</h4>
            <p class="text-muted mb-0">Master data barang untuk stok, pembelian, mutasi, dan produksi.</p>
        </div>
        <a href="{{ route('master.items.create') }}" class="btn btn-primary">
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
            <form method="GET" action="{{ route('master.items.index') }}" class="row g-3 align-items-center">
                <div class="col-md-8 col-lg-6">
                    <input
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        class="form-control"
                        placeholder="Cari kode, nama, kategori, atau satuan"
                    >
                </div>
                <div class="col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-label-primary">Cari</button>
                    @if($search)
                        <a href="{{ route('master.items.index') }}" class="btn btn-label-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Satuan</th>
                        <th>Varietas / Kelas</th>
                        <th>Stok Minimum</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td><span class="fw-medium">{{ $item->code }}</span></td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $categories[$item->category] ?? ucfirst($item->category) }}</td>
                            <td>{{ strtoupper($item->unit) }}</td>
                            <td>
                                @if($item->riceVariety || $item->seedClass)
                                    {{ $item->riceVariety?->name ?: '-' }} / {{ $item->seedClass?->name ?: '-' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ number_format((float) $item->minimum_stock, 2, ',', '.') }}</td>
                            <td>
                                @if($item->is_active)
                                    <span class="badge bg-label-success">Aktif</span>
                                @else
                                    <span class="badge bg-label-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="{{ route('master.items.show', $item) }}">
                                            <i class="bx bx-show me-1"></i> Detail
                                        </a>
                                        <a class="dropdown-item" href="{{ route('master.items.edit', $item) }}">
                                            <i class="bx bx-edit-alt me-1"></i> Edit
                                        </a>
                                        <form
                                            method="POST"
                                            action="{{ route('master.items.destroy', $item) }}"
                                            data-confirm-delete
                                            data-confirm-title="Hapus data barang?"
                                            data-confirm-text="Data {{ $item->name }} akan dihapus dari master data."
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="bx bx-trash me-1"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">Belum ada data barang.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $items->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>
@endsection
