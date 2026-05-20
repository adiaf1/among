@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Stok Terpack</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Stok Terpack</h5>
                    <a href="{{ route('packed-stocks.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Tambah Stok Terpack
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No. Stock</th>
                                    <th>No. Lot</th>
                                    <th>Tanggal</th>
                                    <th>Varietas</th>
                                    <th>Kemasan</th>
                                    <th>Jumlah</th>
                                    <th>Berat/Kemasan</th>
                                    <th>Total Berat</th>
                                    <th>Sisa</th>
                                    <th>Gudang</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stocks as $stock)
                                    <tr>
                                        <td>{{ $stock->stock_number }}</td>
                                        <td>{{ $stock->lot_number ?? '-' }}</td>
                                        <td>{{ $stock->stock_date->format('d/m/Y') }}</td>
                                        <td>{{ $stock->variety->name ?? '-' }}</td>
                                        <td>{{ $stock->unit->name ?? '5kg' }}</td>
                                        <td>{{ number_format($stock->quantity) }} kemasan</td>
                                        <td>{{ number_format($stock->weight_per_package, 2) }} kg</td>
                                        <td>{{ number_format($stock->total_weight, 2) }} kg</td>
                                        <td>{{ number_format($stock->remaining_quantity) }} kemasan</td>
                                        <td>{{ $stock->warehouse->name ?? '-' }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('packed-stocks.show', $stock->id) }}" class="btn btn-info">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('packed-stocks.edit', $stock->id) }}" class="btn btn-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form action="{{ route('packed-stocks.destroy', $stock->id) }}" method="POST" class="d-inline" 
                                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus stok terpack ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center py-4">
                                            <p class="text-muted mb-0">Belum ada stok terpack.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $stocks->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
