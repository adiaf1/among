@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="py-4">
        <h4 class="mb-1">Tambah Barang</h4>
        <p class="text-muted mb-0">Tambahkan barang untuk kebutuhan stok, pembelian, dan produksi.</p>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('master.items.store') }}">
                @include('master.items._form')
            </form>
        </div>
    </div>
</div>
@endsection
