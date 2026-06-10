@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
        <div>
            <h4 class="mb-1">Detail Lahan</h4>
            <p class="text-muted mb-0">{{ $land->code }} - {{ $land->name }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('master.lands.index') }}" class="btn btn-label-secondary">Kembali</a>
            <a href="{{ route('master.lands.edit', $land) }}" class="btn btn-primary">Edit</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Kode</dt>
                <dd class="col-sm-9">{{ $land->code }}</dd>

                <dt class="col-sm-3">Nama Lahan</dt>
                <dd class="col-sm-9">{{ $land->name }}</dd>

                <dt class="col-sm-3">Petani</dt>
                <dd class="col-sm-9">{{ $land->farmer?->name ?? '-' }}</dd>

                <dt class="col-sm-3">Luas</dt>
                <dd class="col-sm-9">{{ $land->area_size ? number_format((float) $land->area_size, 2) . ' ha' : '-' }}</dd>

                <dt class="col-sm-3">Lokasi</dt>
                <dd class="col-sm-9">{{ $land->location ?: '-' }}</dd>

                <dt class="col-sm-3">Jenis Tanah</dt>
                <dd class="col-sm-9">{{ $land->soil_type ?: '-' }}</dd>

                <dt class="col-sm-3">Jenis Irigasi</dt>
                <dd class="col-sm-9">{{ $land->irrigation_type ?: '-' }}</dd>

                <dt class="col-sm-3">Status Kepemilikan</dt>
                <dd class="col-sm-9">{{ $land->ownership_status ?: '-' }}</dd>

                <dt class="col-sm-3">Catatan</dt>
                <dd class="col-sm-9">{{ $land->notes ?: '-' }}</dd>

                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9">
                    @if($land->is_active)
                        <span class="badge bg-label-success">Aktif</span>
                    @else
                        <span class="badge bg-label-secondary">Nonaktif</span>
                    @endif
                </dd>

                <dt class="col-sm-3">Dibuat</dt>
                <dd class="col-sm-9">{{ $land->created_at?->format('d M Y H:i') }}</dd>
            </dl>
        </div>
    </div>
</div>
@endsection
