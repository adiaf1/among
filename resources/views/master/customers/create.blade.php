@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="py-4">
        <h4 class="mb-1">Tambah Pelanggan</h4>
        <p class="text-muted mb-0">Tambahkan pembeli atau relasi penjualan benih.</p>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('master.customers.store') }}">
                @include('master.customers._form')
            </form>
        </div>
    </div>
</div>
@endsection
