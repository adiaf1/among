@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
        <div>
            <h4 class="mb-1">Detail Petani</h4>
            <p class="text-muted mb-0">{{ $farmer->code }} - {{ $farmer->name }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('master.farmers.index') }}" class="btn btn-label-secondary">Kembali</a>
            <a href="{{ route('master.farmers.edit', $farmer) }}" class="btn btn-primary">Edit</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Kode</dt>
                <dd class="col-sm-9">{{ $farmer->code }}</dd>

                <dt class="col-sm-3">Nama</dt>
                <dd class="col-sm-9">{{ $farmer->name }}</dd>

                <dt class="col-sm-3">Telepon</dt>
                <dd class="col-sm-9">{{ $farmer->phone ?: '-' }}</dd>

                <dt class="col-sm-3">NIK / Identitas</dt>
                <dd class="col-sm-9">{{ $farmer->identity_number ?: '-' }}</dd>

                <dt class="col-sm-3">Alamat</dt>
                <dd class="col-sm-9">{{ $farmer->address ?: '-' }}</dd>

                <dt class="col-sm-3">Catatan</dt>
                <dd class="col-sm-9">{{ $farmer->notes ?: '-' }}</dd>

                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9">
                    @if($farmer->is_active)
                        <span class="badge bg-label-success">Aktif</span>
                    @else
                        <span class="badge bg-label-secondary">Nonaktif</span>
                    @endif
                </dd>

                <dt class="col-sm-3">Dibuat</dt>
                <dd class="col-sm-9">{{ $farmer->created_at?->format('d M Y H:i') }}</dd>
            </dl>
        </div>
    </div>
</div>
@endsection
