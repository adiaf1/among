@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
        <div>
            <h4 class="mb-1">Detail Gudang</h4>
            <p class="text-muted mb-0">{{ $warehouse->code }} - {{ $warehouse->name }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('master.warehouses.index') }}" class="btn btn-label-secondary">Kembali</a>
            <a href="{{ route('master.warehouses.edit', $warehouse) }}" class="btn btn-primary">Edit</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Kode</dt>
                <dd class="col-sm-9">{{ $warehouse->code }}</dd>

                <dt class="col-sm-3">Nama</dt>
                <dd class="col-sm-9">{{ $warehouse->name }}</dd>

                <dt class="col-sm-3">Penanggung Jawab</dt>
                <dd class="col-sm-9">{{ $warehouse->person_in_charge ?: '-' }}</dd>

                <dt class="col-sm-3">Telepon</dt>
                <dd class="col-sm-9">{{ $warehouse->phone ?: '-' }}</dd>

                <dt class="col-sm-3">Kapasitas</dt>
                <dd class="col-sm-9">{{ $warehouse->capacity_kg ? number_format((float) $warehouse->capacity_kg, 2, ',', '.') . ' Kg' : '-' }}</dd>

                <dt class="col-sm-3">Alamat / Lokasi</dt>
                <dd class="col-sm-9">{{ $warehouse->address ?: '-' }}</dd>

                <dt class="col-sm-3">Catatan</dt>
                <dd class="col-sm-9">{{ $warehouse->notes ?: '-' }}</dd>

                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9">
                    @if($warehouse->is_active)
                        <span class="badge bg-label-success">Aktif</span>
                    @else
                        <span class="badge bg-label-secondary">Nonaktif</span>
                    @endif
                </dd>

                <dt class="col-sm-3">Dibuat</dt>
                <dd class="col-sm-9">{{ $warehouse->created_at?->format('d M Y H:i') }}</dd>
            </dl>
        </div>
    </div>
</div>
@endsection
