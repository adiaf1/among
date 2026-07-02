@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
        <div>
            <h4 class="mb-1">Detail Barang</h4>
            <p class="text-muted mb-0">{{ $item->code }} - {{ $item->name }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('master.items.index') }}" class="btn btn-label-secondary">Kembali</a>
            <a href="{{ route('master.items.edit', $item) }}" class="btn btn-primary">Edit</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Kode</dt>
                <dd class="col-sm-9">{{ $item->code }}</dd>

                <dt class="col-sm-3">Nama</dt>
                <dd class="col-sm-9">{{ $item->name }}</dd>

                <dt class="col-sm-3">Kategori</dt>
                <dd class="col-sm-9">{{ $categories[$item->category] ?? ucfirst(str_replace('_', ' ', $item->category)) }}</dd>

                <dt class="col-sm-3">Kondisi / Tahap Material</dt>
                <dd class="col-sm-9">{{ $materialStates[$item->material_state ?? 'none'] ?? ucfirst(str_replace('_', ' ', $item->material_state ?? 'none')) }}</dd>

                <dt class="col-sm-3">Satuan</dt>
                <dd class="col-sm-9">{{ strtoupper($item->unit) }}</dd>

                <dt class="col-sm-3">Varietas Padi</dt>
                <dd class="col-sm-9">{{ $item->riceVariety?->name ?: '-' }}</dd>

                <dt class="col-sm-3">Kelas Benih</dt>
                <dd class="col-sm-9">{{ $item->seedClass?->name ?: '-' }}</dd>

                <dt class="col-sm-3">Stok Minimum</dt>
                <dd class="col-sm-9">{{ number_format((float) $item->minimum_stock, 2, ',', '.') }}</dd>

                <dt class="col-sm-3">Deskripsi</dt>
                <dd class="col-sm-9">{{ $item->description ?: '-' }}</dd>

                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9">
                    @if($item->is_active)
                        <span class="badge bg-label-success">Aktif</span>
                    @else
                        <span class="badge bg-label-secondary">Nonaktif</span>
                    @endif
                </dd>

                <dt class="col-sm-3">Dibuat</dt>
                <dd class="col-sm-9">{{ $item->created_at?->format('d M Y H:i') }}</dd>
            </dl>
        </div>
    </div>
</div>
@endsection
