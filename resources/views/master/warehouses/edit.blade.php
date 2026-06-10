@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="py-4">
        <h4 class="mb-1">Edit Gudang</h4>
        <p class="text-muted mb-0">{{ $warehouse->code }} - {{ $warehouse->name }}</p>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('master.warehouses.update', $warehouse) }}">
                @method('PUT')
                @include('master.warehouses._form')
            </form>
        </div>
    </div>
</div>
@endsection
