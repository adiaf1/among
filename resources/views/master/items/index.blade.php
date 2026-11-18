@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
        <div>
            <h4 class="mb-1">Barang</h4>
            <p class="text-muted mb-0">Master data barang untuk stok, pembelian, mutasi, dan produksi.</p>
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
            <h5 class="card-title mb-0">Daftar Barang</h5>
            <form method="GET" action="{{ route('master.items.index') }}" class="d-flex align-items-center gap-2">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <div class="input-group input-group-merge" style="min-width: 260px;">
                    <span class="input-group-text"><i class="icon-base bx bx-search"></i></span>
                    <input
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        class="form-control"
                        placeholder="Cari kode, nama, kategori, kondisi, atau satuan..."
                    >
                </div>
                @if($search)
                    <a href="{{ route('master.items.index', ['per_page' => $perPage]) }}" class="btn btn-label-secondary">Reset</a>
                @endif
            </form>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Kategori / Kondisi</th>
                        <th>Satuan</th>
                        <th>Varietas / Kelas</th>
                        <th>Stok Minimum</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($items as $item)
                        <tr>
                            <td><span class="fw-medium">{{ $item->code }}</span></td>
                            <td>{{ $item->name }}</td>
                            <td>
                                {{ $categories[$item->category] ?? ucfirst(str_replace('_', ' ', $item->category)) }}
                                @if(($item->material_state ?? 'none') !== 'none')
                                    <div class="text-muted small">{{ $materialStates[$item->material_state] ?? ucfirst(str_replace('_', ' ', $item->material_state)) }}</div>
                                @endif
                            </td>
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
                                <div class="d-inline-flex gap-2 justify-content-end">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-icon btn-outline-primary"
                                        title="Edit"
                                        aria-label="Edit"
                                        data-bs-toggle="offcanvas"
                                        data-bs-target="#masterOffcanvas"
                                        data-mode="edit"
                                        data-action="{{ route('master.items.update', $item) }}"
                                        data-record="{{ json_encode(['code' => $item->code, 'name' => $item->name, 'category' => $item->category, 'material_state' => $item->material_state ?? 'none', 'unit' => $item->unit, 'minimum_stock' => $item->minimum_stock, 'rice_variety_id' => $item->rice_variety_id, 'seed_class_id' => $item->seed_class_id, 'description' => $item->description, 'is_active' => $item->is_active]) }}"
                                    >
                                        <span class="icon-base bx bx-edit-alt icon-sm"></span>
                                    </button>
                                    <form
                                        method="POST"
                                        action="{{ route('master.items.destroy', $item) }}"
                                        class="d-inline"
                                        data-confirm-delete
                                        data-confirm-title="Hapus data barang?"
                                        data-confirm-text="Data {{ $item->name }} akan dihapus dari master data."
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
                            <td colspan="8" class="text-center text-muted py-5">Belum ada data barang.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <form method="GET" action="{{ route('master.items.index') }}" class="d-flex align-items-center gap-2">
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
                    Menampilkan {{ $items->firstItem() ?? 0 }} - {{ $items->lastItem() ?? 0 }} dari {{ $items->total() }} data
                </span>
            </div>
            <div>
                {{ $items->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="masterOffcanvas" aria-labelledby="masterOffcanvasLabel">
        <div class="offcanvas-header border-bottom">
            <h5 id="masterOffcanvasLabel" class="offcanvas-title">Tambah Barang</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
            <form
                id="masterOffcanvasForm"
                method="POST"
                action="{{ old('_form_action', route('master.items.store')) }}"
                data-store-url="{{ route('master.items.store') }}"
                data-create-title="Tambah Barang"
                data-edit-title="Edit Barang"
            >
                @csrf
                <input type="hidden" name="_method" id="masterOffcanvasMethod" value="{{ old('_method', 'POST') }}">
                <input type="hidden" name="_form_action" id="masterOffcanvasActionInput" value="{{ old('_form_action', route('master.items.store')) }}">
                <input type="hidden" name="_form_mode" id="masterOffcanvasModeInput" value="{{ old('_form_mode', 'create') }}">

                <div class="mb-6">
                    <label for="code" class="form-label">Kode <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code') }}" required maxlength="50" placeholder="Contoh: BRG001">
                    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-6">
                    <label for="name" class="form-label">Nama Barang <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required maxlength="255" placeholder="Contoh: Benih Padi Ciherang ES">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-6">
                    <label for="category" class="form-label">Kategori <span class="text-danger">*</span></label>
                    <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" data-default-value="lainnya" required>
                        @foreach($categories as $value => $label)
                            <option value="{{ $value }}" @selected(old('category', 'lainnya') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-6">
                    <label for="material_state" class="form-label">Kondisi / Tahap Material <span class="text-danger">*</span></label>
                    <select class="form-select @error('material_state') is-invalid @enderror" id="material_state" name="material_state" data-default-value="none" required>
                        @foreach($materialStates as $value => $label)
                            <option value="{{ $value }}" @selected(old('material_state', 'none') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('material_state') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-6">
                    <label for="unit" class="form-label">Satuan <span class="text-danger">*</span></label>
                    <input type="search" class="form-control @error('unit') is-invalid @enderror" id="unit" name="unit" value="{{ old('unit', 'kg') }}" data-default-value="kg" list="unit-options" required maxlength="50" placeholder="Contoh: kg, pcs, roll" autocomplete="off">
                    <datalist id="unit-options">
                        @foreach($unitOptions as $unitOption)
                            <option value="{{ $unitOption }}"></option>
                        @endforeach
                    </datalist>
                    @error('unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-6">
                    <label for="minimum_stock" class="form-label">Stok Minimum</label>
                    <input type="number" class="form-control @error('minimum_stock') is-invalid @enderror" id="minimum_stock" name="minimum_stock" value="{{ old('minimum_stock', 0) }}" data-default-value="0" min="0" max="9999999999.99" step="0.01" placeholder="Contoh: 500">
                    @error('minimum_stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-6">
                    <label for="rice_variety_id" class="form-label">Varietas Padi</label>
                    <select class="form-select @error('rice_variety_id') is-invalid @enderror" id="rice_variety_id" name="rice_variety_id">
                        <option value="">Tidak terkait varietas</option>
                        @foreach($riceVarieties as $riceVariety)
                            <option value="{{ $riceVariety->id }}" @selected(old('rice_variety_id') === $riceVariety->id)>{{ $riceVariety->code }} - {{ $riceVariety->name }}</option>
                        @endforeach
                    </select>
                    @error('rice_variety_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-6">
                    <label for="seed_class_id" class="form-label">Kelas Benih</label>
                    <select class="form-select @error('seed_class_id') is-invalid @enderror" id="seed_class_id" name="seed_class_id">
                        <option value="">Tidak terkait kelas benih</option>
                        @foreach($seedClasses as $seedClass)
                            <option value="{{ $seedClass->id }}" @selected(old('seed_class_id') === $seedClass->id)>{{ $seedClass->code }} - {{ $seedClass->name }}</option>
                        @endforeach
                    </select>
                    @error('seed_class_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-6">
                    <label for="description" class="form-label">Deskripsi</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Keterangan barang">{{ old('description') }}</textarea>
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
