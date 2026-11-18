@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
        <div>
            <h4 class="mb-1">Kelas Benih</h4>
            <p class="text-muted mb-0">Master data kelas benih untuk alur produksi dan sertifikasi.</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#masterOffcanvas" data-mode="create">
            <i class="icon-base bx bx-plus me-1"></i> Tambah
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <h5 class="card-title mb-0">Daftar Kelas Benih</h5>
            <form method="GET" action="{{ route('master.seed-classes.index') }}" class="d-flex align-items-center gap-2">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <div class="input-group input-group-merge" style="min-width: 260px;">
                    <span class="input-group-text"><i class="icon-base bx bx-search"></i></span>
                    <input
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        class="form-control"
                        placeholder="Cari kode atau nama kelas benih..."
                    >
                </div>
                @if($search)
                    <a href="{{ route('master.seed-classes.index', ['per_page' => $perPage]) }}" class="btn btn-label-secondary">Reset</a>
                @endif
            </form>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Kelas</th>
                        <th>Status</th>
                        <th>Dibuat</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($seedClasses as $seedClass)
                        <tr>
                            <td><span class="fw-medium">{{ $seedClass->code }}</span></td>
                            <td>{{ $seedClass->name }}</td>
                            <td>
                                @if($seedClass->is_active)
                                    <span class="badge bg-label-success">Aktif</span>
                                @else
                                    <span class="badge bg-label-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>{{ $seedClass->created_at?->format('d M Y') }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2 justify-content-end">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-icon btn-outline-primary"
                                        title="Edit"
                                        aria-label="Edit"
                                        data-bs-toggle="offcanvas"
                                        data-bs-target="#masterOffcanvas"
                                        data-mode="edit"
                                        data-action="{{ route('master.seed-classes.update', $seedClass) }}"
                                        data-record="{{ json_encode(['code' => $seedClass->code, 'name' => $seedClass->name, 'description' => $seedClass->description, 'is_active' => $seedClass->is_active]) }}"
                                    >
                                        <span class="icon-base bx bx-edit-alt icon-sm"></span>
                                    </button>
                                    <form
                                        method="POST"
                                        action="{{ route('master.seed-classes.destroy', $seedClass) }}"
                                        class="d-inline"
                                        data-confirm-delete
                                        data-confirm-title="Hapus kelas benih?"
                                        data-confirm-text="Data {{ $seedClass->name }} akan dihapus dari master data."
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Hapus" aria-label="Hapus">
                                            <span class="icon-base bx bx-trash icon-sm"></span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">Belum ada data kelas benih.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <form method="GET" action="{{ route('master.seed-classes.index') }}" class="d-flex align-items-center gap-2">
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
                    Menampilkan {{ $seedClasses->firstItem() ?? 0 }} - {{ $seedClasses->lastItem() ?? 0 }} dari {{ $seedClasses->total() }} data
                </span>
            </div>
            <div>
                {{ $seedClasses->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="masterOffcanvas" aria-labelledby="masterOffcanvasLabel">
        <div class="offcanvas-header border-bottom">
            <h5 id="masterOffcanvasLabel" class="offcanvas-title">Tambah Kelas Benih</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
            <form
                id="masterOffcanvasForm"
                method="POST"
                action="{{ old('_form_action', route('master.seed-classes.store')) }}"
                data-store-url="{{ route('master.seed-classes.store') }}"
                data-create-title="Tambah Kelas Benih"
                data-edit-title="Edit Kelas Benih"
            >
                @csrf
                <input type="hidden" name="_method" id="masterOffcanvasMethod" value="{{ old('_method', 'POST') }}">
                <input type="hidden" name="_form_action" id="masterOffcanvasActionInput" value="{{ old('_form_action', route('master.seed-classes.store')) }}">
                <input type="hidden" name="_form_mode" id="masterOffcanvasModeInput" value="{{ old('_form_mode', 'create') }}">

                <div class="mb-6">
                    <label for="code" class="form-label">Kode <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code') }}" required maxlength="50" placeholder="Contoh: BS">
                    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-6">
                    <label for="name" class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required maxlength="255" placeholder="Contoh: Benih Penjenis (BS)">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-6">
                    <label for="description" class="form-label">Deskripsi</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" placeholder="Keterangan kelas benih">{{ old('description') }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" data-default-checked="true" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Aktif</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary me-3">Simpan</button>
                <button type="button" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Batal</button>
            </form>
        </div>
    </div>
</div>
@include('master._offcanvas_script')
@endsection
