@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="py-4">
        <h4 class="mb-1">Edit Kelas Benih</h4>
        <p class="text-muted mb-0">{{ $seedClass->code }} - {{ $seedClass->name }}</p>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Form Edit Kelas Benih</h5>
            <small class="text-body-secondary float-end">Master Data</small>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('master.seed-classes.update', $seedClass) }}">
                @method('PUT')
                @include('master.seed-classes._form')
            </form>
        </div>
    </div>
</div>
@endsection
