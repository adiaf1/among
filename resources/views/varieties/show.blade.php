@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('master.varieties.index') }}">Varietas</a></li>
                    <li class="breadcrumb-item active">{{ $variety->name }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detail Varietas</h5>
                    <div>
                        <a href="{{ route('master.varieties.edit', $variety->id) }}" class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <form action="{{ route('master.varieties.destroy', $variety->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus varietas ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="bi bi-trash"></i> Hapus
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th width="30%">Nama Varietas</th>
                                <td>{{ $variety->name }}</td>
                            </tr>
                            <tr>
                                <th>Kode</th>
                                <td>{{ $variety->code ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Deskripsi</th>
                                <td>{{ $variety->description ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Umur Matang</th>
                                <td>{{ $variety->maturity_days ?? '-' }} hari</td>
                            </tr>
                            <tr>
                                <th>Dibuat</th>
                                <td>{{ $variety->created_at->format('d M Y, H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Terakhir Diupdate</th>
                                <td>{{ $variety->updated_at->format('d M Y, H:i') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="{{ route('master.varieties.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali ke Daftar
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Statistik</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <i class="bi bi-seedling" style="font-size: 3rem; color: #28a745;"></i>
                    </div>
                    <p class="text-muted small text-center">
                        Varietas ini digunakan dalam proses produksi benih padi dari barang masuk basah hingga packing.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
