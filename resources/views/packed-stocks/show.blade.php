@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('packed-stocks.index') }}">Stok Terpack</a></li>
                    <li class="breadcrumb-item active">Detail Stok Terpack</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Detail Stok Terpack</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">No. Stock</th>
                            <td>{{ $stock->stock_number }}</td>
                        </tr>
                        <tr>
                            <th>No. Lot</th>
                            <td>{{ $stock->lot_number ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Stock</th>
                            <td>{{ $stock->stock_date->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th>Varietas</th>
                            <td>{{ $stock->variety->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Kemasan</th>
                            <td>{{ $stock->unit->name ?? '5kg' }}</td>
                        </tr>
                        <tr>
                            <th>Jumlah Kemasan</th>
                            <td>{{ number_format($stock->quantity) }} kemasan</td>
                        </tr>
                        <tr>
                            <th>Berat per Kemasan</th>
                            <td>{{ number_format($stock->weight_per_package, 2) }} kg</td>
                        </tr>
                        <tr>
                            <th>Total Berat</th>
                            <td>{{ number_format($stock->total_weight, 2) }} kg</td>
                        </tr>
                        <tr>
                            <th>Sisa Kemasan</th>
                            <td>{{ number_format($stock->remaining_quantity) }} kemasan</td>
                        </tr>
                        <tr>
                            <th>Gudang</th>
                            <td>{{ $stock->warehouse->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Catatan</th>
                            <td>{{ $stock->notes ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Dibuat</th>
                            <td>{{ $stock->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Terakhir Diupdate</th>
                            <td>{{ $stock->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="{{ route('packed-stocks.edit', $stock->id) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <form action="{{ route('packed-stocks.destroy', $stock->id) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus stok terpack ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    </form>
                    <a href="{{ route('packed-stocks.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Informasi Kemasan</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <i class="bi bi-box-seam display-4 text-primary"></i>
                    </div>
                    <p class="text-muted small text-center">
                        Setiap kemasan berisi <strong>5kg</strong> benih padi berkualitas tinggi.
                    </p>
                    <hr>
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2">
                            <strong>No. Lot:</strong><br>
                            {{ $stock->lot_number ?? 'Tidak ada nomor lot' }}
                        </li>
                        <li class="mb-2">
                            <strong>Total Berat:</strong><br>
                            {{ number_format($stock->total_weight, 2) }} kg
                        </li>
                        <li class="mb-2">
                            <strong>Sisa Stok:</strong><br>
                            {{ number_format($stock->remaining_quantity * $stock->weight_per_package, 2) }} kg 
                            ({{ number_format($stock->remaining_quantity) }} kemasan)
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
