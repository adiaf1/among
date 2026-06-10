@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
        <div>
            <h4 class="mb-1">Petani</h4>
            <p class="text-muted mb-0">Master data petani atau mitra penangkar benih.</p>
        </div>
        <a href="{{ route('master.farmers.create') }}" class="btn btn-primary">
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
            <form method="GET" action="{{ route('master.farmers.index') }}" class="row g-3 align-items-center">
                <div class="col-md-8 col-lg-6">
                    <input
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        class="form-control"
                        placeholder="Cari kode, nama, telepon, atau identitas"
                    >
                </div>
                <div class="col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-label-primary">Cari</button>
                    @if($search)
                        <a href="{{ route('master.farmers.index') }}" class="btn btn-label-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Petani</th>
                        <th>Telepon</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($farmers as $farmer)
                        <tr>
                            <td><span class="fw-medium">{{ $farmer->code }}</span></td>
                            <td>{{ $farmer->name }}</td>
                            <td>{{ $farmer->phone ?: '-' }}</td>
                            <td>
                                @if($farmer->is_active)
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
                                        <a class="dropdown-item" href="{{ route('master.farmers.show', $farmer) }}">
                                            <i class="bx bx-show me-1"></i> Detail
                                        </a>
                                        <a class="dropdown-item" href="{{ route('master.farmers.edit', $farmer) }}">
                                            <i class="bx bx-edit-alt me-1"></i> Edit
                                        </a>
                                        <form
                                            method="POST"
                                            action="{{ route('master.farmers.destroy', $farmer) }}"
                                            data-confirm-delete
                                            data-confirm-title="Hapus data petani?"
                                            data-confirm-text="Data {{ $farmer->name }} akan dihapus dari master data."
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
                            <td colspan="5" class="text-center text-muted py-5">Belum ada data petani.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $farmers->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>
@endsection
