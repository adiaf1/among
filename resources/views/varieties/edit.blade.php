@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('master.varieties.index') }}">Varietas</a></li>
                    <li class="breadcrumb-item active">Edit Varietas</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Edit Varietas</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('master.varieties.update', $variety->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Varietas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $variety->name) }}" required 
                                   placeholder="Contoh: IR64, Ciherang, dll">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="code" class="form-label">Kode Varietas</label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                   id="code" name="code" value="{{ old('code', $variety->code) }}" 
                                   placeholder="Contoh: V001">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" 
                                      placeholder="Deskripsi singkat tentang varietas">{{ old('description', $variety->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="maturity_days" class="form-label">Umur Matang (Hari)</label>
                            <input type="number" class="form-control @error('maturity_days') is-invalid @enderror" 
                                   id="maturity_days" name="maturity_days" value="{{ old('maturity_days', $variety->maturity_days) }}" 
                                   placeholder="Contoh: 95">
                            @error('maturity_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('master.varieties.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Update
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
                        Edit informasi varietas di samping. Pastikan data yang dimasukkan sudah benar.
                    </p>
                    <hr>
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2">
                            <strong>ID:</strong><br>
                            {{ $variety->id }}
                        </li>
                        <li class="mb-2">
                            <strong>Dibuat:</strong><br>
                            {{ $variety->created_at->format('d M Y H:i') }}
                        </li>
                        <li class="mb-2">
                            <strong>Terakhir Diupdate:</strong><br>
                            {{ $variety->updated_at->format('d M Y H:i') }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
