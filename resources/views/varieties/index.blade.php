@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Data Varietas Benih Padi</h3>
                    <div class="card-tools">
                        <a href="{{ route('master.varieties.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Tambah Varietas
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Nama Varietas</th>
                                <th>Deskripsi</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($varieties as $variety)
                                <tr>
                                    <td>{{ $loop->iteration + ($varieties->currentPage() - 1) * $varieties->perPage() }}</td>
                                    <td>{{ $variety->code }}</td>
                                    <td>{{ $variety->name }}</td>
                                    <td>{{ $variety->description ?? '-' }}</td>
                                    <td>
                                        <span class="badge {{ $variety->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $variety->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td>
                                        <form action="{{ route('master.varieties.destroy', $variety) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus varietas ini?');">
                                            <a href="{{ route('master.varieties.edit', $variety) }}" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="{{ route('master.varieties.show', $variety) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">Belum ada data varietas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-end">
                        {{ $varieties->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
