@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
        <div>
            <h4 class="mb-1">Lahan</h4>
            <p class="text-muted mb-0">Master data lahan milik petani atau mitra penangkar.</p>
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
            <h5 class="card-title mb-0">Daftar Lahan</h5>
            <form method="GET" action="{{ route('master.lands.index') }}" class="d-flex align-items-center gap-2">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <div class="input-group input-group-merge" style="min-width: 260px;">
                    <span class="input-group-text"><i class="icon-base bx bx-search"></i></span>
                    <input
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        class="form-control"
                        placeholder="Cari kode, nama lahan, lokasi, status, atau petani..."
                    >
                </div>
                @if($search)
                    <a href="{{ route('master.lands.index', ['per_page' => $perPage]) }}" class="btn btn-label-secondary">Reset</a>
                @endif
            </form>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Lahan</th>
                        <th>Petani</th>
                        <th>Luas</th>
                        <th>Kelayakan</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($lands as $land)
                        <tr>
                            <td><span class="fw-medium">{{ $land->code }}</span></td>
                            <td>{{ $land->name }}</td>
                            <td>{{ $land->farmer?->name ?? '-' }}</td>
                            <td>{{ $land->area_size ? number_format((float) $land->area_size, 2) . ' ha' : '-' }}</td>
                            <td>
                                @php($certificationStatus = $land->certification_status ?? 'belum_ditinjau')
                                @if($certificationStatus === 'layak')
                                    <span class="badge bg-label-success">{{ $certificationStatuses[$certificationStatus] }}</span>
                                @elseif($certificationStatus === 'perlu_perbaikan')
                                    <span class="badge bg-label-warning">{{ $certificationStatuses[$certificationStatus] }}</span>
                                @elseif($certificationStatus === 'tidak_layak')
                                    <span class="badge bg-label-danger">{{ $certificationStatuses[$certificationStatus] }}</span>
                                @else
                                    <span class="badge bg-label-secondary">{{ $certificationStatuses[$certificationStatus] ?? 'Belum Ditinjau' }}</span>
                                @endif
                            </td>
                            <td>
                                @if($land->is_active)
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
                                        data-action="{{ route('master.lands.update', $land) }}"
                                        data-record="{{ json_encode(['code' => $land->code, 'name' => $land->name, 'farmer_id' => $land->farmer_id, 'area_size' => $land->area_size, 'location' => $land->location, 'latitude' => $land->latitude, 'longitude' => $land->longitude, 'certification_status' => $land->certification_status ?? 'belum_ditinjau', 'notes' => $land->notes, 'is_active' => $land->is_active]) }}"
                                    >
                                        <span class="icon-base bx bx-edit-alt icon-sm"></span>
                                    </button>
                                    <form
                                        method="POST"
                                        action="{{ route('master.lands.destroy', $land) }}"
                                        class="d-inline"
                                        data-confirm-delete
                                        data-confirm-title="Hapus data lahan?"
                                        data-confirm-text="Data {{ $land->name }} akan dihapus dari master data."
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
                            <td colspan="7" class="text-center text-muted py-5">Belum ada data lahan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <form method="GET" action="{{ route('master.lands.index') }}" class="d-flex align-items-center gap-2">
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
                    Menampilkan {{ $lands->firstItem() ?? 0 }} - {{ $lands->lastItem() ?? 0 }} dari {{ $lands->total() }} data
                </span>
            </div>
            <div>
                {{ $lands->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="masterOffcanvas" aria-labelledby="masterOffcanvasLabel">
        <div class="offcanvas-header border-bottom">
            <h5 id="masterOffcanvasLabel" class="offcanvas-title">Tambah Lahan</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
            <form
                id="masterOffcanvasForm"
                method="POST"
                action="{{ old('_form_action', route('master.lands.store')) }}"
                data-store-url="{{ route('master.lands.store') }}"
                data-create-title="Tambah Lahan"
                data-edit-title="Edit Lahan"
            >
                @csrf
                <input type="hidden" name="_method" id="masterOffcanvasMethod" value="{{ old('_method', 'POST') }}">
                <input type="hidden" name="_form_action" id="masterOffcanvasActionInput" value="{{ old('_form_action', route('master.lands.store')) }}">
                <input type="hidden" name="_form_mode" id="masterOffcanvasModeInput" value="{{ old('_form_mode', 'create') }}">

                <div class="mb-6">
                    <label for="code" class="form-label">Kode <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code') }}" required maxlength="50" placeholder="Contoh: LHN001">
                    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-6">
                    <label for="name" class="form-label">Nama Lahan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required maxlength="255" placeholder="Contoh: Sawah Blok Timur">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-6">
                    <label for="farmer_id" class="form-label">Petani <span class="text-danger">*</span></label>
                    <select class="form-select @error('farmer_id') is-invalid @enderror" id="farmer_id" name="farmer_id" required>
                        <option value="">Pilih petani</option>
                        @foreach($farmers as $farmer)
                            <option value="{{ $farmer->id }}" @selected(old('farmer_id') === $farmer->id)>{{ $farmer->code }} - {{ $farmer->name }}</option>
                        @endforeach
                    </select>
                    @error('farmer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-6">
                    <label for="area_size" class="form-label">Luas Lahan</label>
                    <div class="input-group">
                        <input type="number" step="0.01" min="0" class="form-control @error('area_size') is-invalid @enderror" id="area_size" name="area_size" value="{{ old('area_size') }}" placeholder="Contoh: 2.50">
                        <span class="input-group-text">ha</span>
                        @error('area_size') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-6">
                    <label for="location" class="form-label">Alamat / Lokasi Lahan</label>
                    <textarea class="form-control @error('location') is-invalid @enderror" id="location" name="location" rows="3" placeholder="Alamat, blok, desa, patok lokasi, atau deskripsi akses lahan">{{ old('location') }}</textarea>
                    @error('location') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row g-3 mb-6">
                    <div class="col-6">
                        <label for="latitude" class="form-label">Latitude</label>
                        <input type="number" step="0.0000001" min="-90" max="90" class="form-control @error('latitude') is-invalid @enderror" id="latitude" name="latitude" value="{{ old('latitude') }}" placeholder="-6.5601234">
                        @error('latitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-6">
                        <label for="longitude" class="form-label">Longitude</label>
                        <input type="number" step="0.0000001" min="-180" max="180" class="form-control @error('longitude') is-invalid @enderror" id="longitude" name="longitude" value="{{ old('longitude') }}" placeholder="107.7601234">
                        @error('longitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-6">
                    <label for="certification_status" class="form-label">Status Kelayakan</label>
                    <select class="form-select @error('certification_status') is-invalid @enderror" id="certification_status" name="certification_status" data-default-value="belum_ditinjau">
                        @foreach($certificationStatuses as $value => $label)
                            <option value="{{ $value }}" @selected(old('certification_status', 'belum_ditinjau') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('certification_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-6">
                    <label for="notes" class="form-label">Catatan</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="2" placeholder="Catatan tambahan lahan">{{ old('notes') }}</textarea>
                    @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
