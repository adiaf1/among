@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
        <div>
            <h4 class="mb-1">Detail Produksi Benih</h4>
            <p class="text-muted mb-0">{{ $seedProduction->number }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('seed-productions.index') }}" class="btn btn-label-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali
            </a>
            <a href="{{ route('seed-productions.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Tambah
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header border-bottom">
                    <h5 class="mb-0">Informasi Produksi</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Nomor</dt>
                        <dd class="col-sm-8">{{ $seedProduction->number }}</dd>

                        <dt class="col-sm-4">Tanggal Produksi</dt>
                        <dd class="col-sm-8">{{ $seedProduction->production_date?->format('d M Y') }}</dd>

                        <dt class="col-sm-4">No Lot</dt>
                        <dd class="col-sm-8">{{ $seedProduction->lot_number ?: '-' }}</dd>

                        <dt class="col-sm-4">Varietas</dt>
                        <dd class="col-sm-8">{{ $seedProduction->riceVariety?->name ?? '-' }}</dd>

                        <dt class="col-sm-4">Kelas Benih Tujuan</dt>
                        <dd class="col-sm-8">{{ $seedProduction->seedClass?->name ?? '-' }}</dd>

                        <dt class="col-sm-4">Gudang Hasil</dt>
                        <dd class="col-sm-8">{{ $seedProduction->outputWarehouse?->name ?? '-' }}</dd>

                        <dt class="col-sm-4">Target Hasil</dt>
                        <dd class="col-sm-8">
                            @if($seedProduction->target_quantity)
                                {{ number_format((float) $seedProduction->target_quantity, 2, ',', '.') }} {{ $seedProduction->unit }}
                            @else
                                -
                            @endif
                        </dd>

                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            @php($badge = match($seedProduction->status) {
                                'siap_salur' => 'success',
                                'batal' => 'danger',
                                default => 'primary',
                            })
                            <span class="badge bg-label-{{ $badge }}">{{ $statuses[$seedProduction->status] ?? $seedProduction->status }}</span>
                        </dd>

                        <dt class="col-sm-4">Dibuat Oleh</dt>
                        <dd class="col-sm-8">{{ $seedProduction->creator?->name ?? '-' }}</dd>

                        <dt class="col-sm-4">Catatan</dt>
                        <dd class="col-sm-8">{{ $seedProduction->notes ?: '-' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header border-bottom">
                    <h5 class="mb-0">Ringkasan Bahan</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Total Baris Bahan</dt>
                        <dd class="col-sm-7">{{ $seedProduction->inputs->count() }}</dd>

                        <dt class="col-sm-5">Bahan Utama</dt>
                        <dd class="col-sm-7">{{ $seedProduction->inputs->where('role', 'bahan_utama')->count() }} baris</dd>

                        <dt class="col-sm-5">Pendukung</dt>
                        <dd class="col-sm-7">{{ $seedProduction->inputs->where('role', 'pendukung')->count() }} baris</dd>

                        <dt class="col-sm-5">Total Biaya Tahap</dt>
                        <dd class="col-sm-7">Rp {{ number_format((float) $seedProduction->steps->sum('cost'), 2, ',', '.') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="mb-0">Bahan Produksi</h5>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Barang</th>
                                <th>Gudang</th>
                                <th>Peran</th>
                                <th class="text-end">Jumlah</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($seedProduction->inputs as $input)
                                <tr>
                                    <td>
                                        <span class="fw-medium">{{ $input->item?->name ?? '-' }}</span>
                                        <div class="text-muted small">{{ $input->item?->code ?? '-' }}</div>
                                    </td>
                                    <td>{{ $input->warehouse?->name ?? '-' }}</td>
                                    <td>{{ $inputRoles[$input->role] ?? $input->role }}</td>
                                    <td class="text-end">{{ number_format((float) $input->quantity, 2, ',', '.') }} {{ $input->unit }}</td>
                                    <td>{{ $input->notes ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="mb-0">Tahapan Produksi</h5>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th style="width: 72px;">Urut</th>
                                <th style="min-width: 180px;">Tahap</th>
                                <th style="min-width: 150px;">Tanggal Rencana</th>
                                <th style="min-width: 150px;">Tanggal Aktual</th>
                                <th style="min-width: 140px;">Jumlah Kg</th>
                                <th style="min-width: 160px;">Biaya / Kg</th>
                                <th style="min-width: 170px;">Total Biaya</th>
                                <th style="min-width: 140px;">Status</th>
                                <th style="min-width: 220px;">Catatan</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($seedProduction->steps as $step)
                                @php($formId = 'production-step-form-'.$step->id)
                                <tr>
                                    <td>{{ $step->sort_order }}</td>
                                    <td class="fw-medium">{{ $step->label }}</td>
                                    <td>
                                        <input
                                            type="date"
                                            name="planned_date"
                                            value="{{ old('planned_date', $step->planned_date?->toDateString()) }}"
                                            class="form-control form-control-sm"
                                            form="{{ $formId }}"
                                        >
                                    </td>
                                    <td>
                                        <input
                                            type="date"
                                            name="actual_date"
                                            value="{{ old('actual_date', $step->actual_date?->toDateString()) }}"
                                            class="form-control form-control-sm"
                                            form="{{ $formId }}"
                                        >
                                    </td>
                                    <td>
                                        <input
                                            type="number"
                                            name="quantity"
                                            value="{{ old('quantity', $step->quantity) }}"
                                            min="0.01"
                                            max="9999999999.99"
                                            step="0.01"
                                            class="form-control form-control-sm production-step-quantity"
                                            form="{{ $formId }}"
                                            placeholder="0"
                                        >
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Rp</span>
                                            <input
                                                type="text"
                                                inputmode="numeric"
                                                name="cost_per_kg"
                                                value="{{ old('cost_per_kg', $step->cost_per_kg !== null ? number_format((float) $step->cost_per_kg, 0, ',', '.') : '') }}"
                                                class="form-control rupiah-input production-step-cost-per-kg"
                                                form="{{ $formId }}"
                                                placeholder="0"
                                            >
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Rp</span>
                                            <input
                                                type="text"
                                                inputmode="numeric"
                                                name="cost"
                                                value="{{ old('cost', number_format((float) $step->cost, 0, ',', '.')) }}"
                                                class="form-control rupiah-input production-step-cost"
                                                form="{{ $formId }}"
                                                placeholder="0"
                                                readonly
                                            >
                                        </div>
                                    </td>
                                    <td>
                                        <select name="status" class="form-select form-select-sm" form="{{ $formId }}">
                                            @foreach($stepStatuses as $value => $label)
                                                <option value="{{ $value }}" @selected(old('status', $step->status) === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input
                                            type="text"
                                            name="notes"
                                            value="{{ old('notes', $step->notes) }}"
                                            class="form-control form-control-sm"
                                            form="{{ $formId }}"
                                            placeholder="Catatan"
                                        >
                                    </td>
                                    <td class="text-end">
                                        <form id="{{ $formId }}" method="POST" action="{{ route('seed-productions.steps.update', [$seedProduction, $step]) }}">
                                            @csrf
                                            @method('PATCH')
                                        </form>
                                        <button type="submit" class="btn btn-sm btn-label-primary" form="{{ $formId }}">
                                            <i class="bx bx-save me-1"></i> Simpan
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const rupiahInputs = document.querySelectorAll('.rupiah-input');

        function digits(value) {
            return String(value || '').replace(/\D/g, '');
        }

        function formatRupiah(value) {
            const cleanValue = digits(value);

            return cleanValue ? Number(cleanValue).toLocaleString('id-ID') : '';
        }

        function syncRowCost(row) {
            const quantityInput = row.querySelector('.production-step-quantity');
            const costPerKgInput = row.querySelector('.production-step-cost-per-kg');
            const costInput = row.querySelector('.production-step-cost');
            const quantity = Number(quantityInput?.value || 0);
            const costPerKg = Number(digits(costPerKgInput?.value || 0));

            if (!costInput || quantity <= 0 || costPerKg < 0) {
                return;
            }

            costInput.value = formatRupiah(Math.round(quantity * costPerKg));
        }

        rupiahInputs.forEach(function (input) {
            input.value = formatRupiah(input.value);

            input.addEventListener('input', function () {
                input.value = formatRupiah(input.value);
                syncRowCost(input.closest('tr'));
            });
        });

        document.querySelectorAll('.production-step-quantity').forEach(function (input) {
            input.addEventListener('input', function () {
                syncRowCost(input.closest('tr'));
            });
        });
    });
</script>
@endsection
