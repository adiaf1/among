@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('packed-stocks.index') }}">Stok Terpack</a></li>
                    <li class="breadcrumb-item active">Tambah Stok Terpack</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Tambah Stok Terpack Baru</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('packed-stocks.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="packaging_process_id" class="form-label">Proses Packing <span class="text-danger">*</span></label>
                            <select class="form-select @error('packaging_process_id') is-invalid @enderror" 
                                    id="packaging_process_id" name="packaging_process_id" required>
                                <option value="">-- Pilih Proses Packing --</option>
                                @foreach($processes as $process)
                                    <option value="{{ $process->id }}" {{ old('packaging_process_id') == $process->id ? 'selected' : '' }}>
                                        {{ $process->process_number }} - {{ $process->dryRiceStock->stock_number ?? 'N/A' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('packaging_process_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="warehouse_id" class="form-label">Gudang <span class="text-danger">*</span></label>
                            <select class="form-select @error('warehouse_id') is-invalid @enderror" 
                                    id="warehouse_id" name="warehouse_id" required>
                                <option value="">-- Pilih Gudang --</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('warehouse_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="lot_number" class="form-label">No. Lot Benih</label>
                            <input type="text" class="form-control @error('lot_number') is-invalid @enderror" 
                                   id="lot_number" name="lot_number" value="{{ old('lot_number') }}" 
                                   placeholder="Contoh: LOT-2025-001">
                            <small class="text-muted">Nomor lot untuk setiap kemasan 5kg (opsional)</small>
                            @error('lot_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Catatan</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3" 
                                      placeholder="Catatan tambahan">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('packed-stocks.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Informasi</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small">
                        Isi form di samping untuk menambahkan stok terpack baru ke dalam sistem.
                        Setiap kemasan berisi 5kg benih padi.
                    </p>
                    <hr>
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2">
                            <strong>Proses Packing:</strong><br>
                            Pilih proses packing yang sudah selesai
                        </li>
                        <li class="mb-2">
                            <strong>No. Lot:</strong><br>
                            Nomor lot untuk traceability (opsional)
                        </li>
                        <li class="mb-2">
                            <strong>Gudang:</strong><br>
                            Lokasi penyimpanan stok terpack
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
