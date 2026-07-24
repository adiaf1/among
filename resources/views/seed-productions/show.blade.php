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
                <div class="card-header border-bottom d-flex flex-column gap-3">
                    <h5 class="mb-0">Bahan Produksi</h5>
                    @if($seedProduction->status === 'proses')
                        <form method="POST" action="{{ route('seed-productions.inputs.store', $seedProduction) }}" class="row g-2 align-items-end">
                            @csrf
                            <div class="col-6 col-md-2">
                                <label class="form-label small mb-1">Tanggal</label>
                                <input
                                    type="date"
                                    name="movement_date"
                                    value="{{ old('movement_date', now()->toDateString()) }}"
                                    class="form-control form-control-sm @error('movement_date') is-invalid @enderror"
                                    required
                                >
                                @error('movement_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small mb-1">Peran</label>
                                <select
                                    name="role"
                                    id="additional-production-input-role"
                                    class="form-select form-select-sm @error('role') is-invalid @enderror"
                                    required
                                >
                                    @foreach($inputRoles as $value => $label)
                                        <option value="{{ $value }}" @selected(old('role', 'pendukung') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label small mb-1">Stok Bahan</label>
                                <select
                                    name="stock_id"
                                    id="additional-production-input-stock"
                                    class="form-select form-select-sm @error('stock_id') is-invalid @enderror"
                                    data-selected="{{ old('stock_id') }}"
                                    required
                                >
                                    <option value="">Pilih stok bahan</option>
                                </select>
                                @error('stock_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small mb-1">Jumlah</label>
                                <input
                                    type="number"
                                    name="quantity"
                                    value="{{ old('quantity') }}"
                                    min="0.01"
                                    max="9999999999.99"
                                    step="0.01"
                                    class="form-control form-control-sm @error('quantity') is-invalid @enderror"
                                    placeholder="0"
                                    required
                                >
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6 col-md-1">
                                <label class="form-label small mb-1">Catatan</label>
                                <input
                                    type="text"
                                    name="notes"
                                    value="{{ old('notes') }}"
                                    class="form-control form-control-sm @error('notes') is-invalid @enderror"
                                    placeholder="-"
                                >
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 col-md-1 d-grid">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="bx bx-plus"></i>
                                </button>
                            </div>
                        </form>
                    @endif
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
                <div class="card-header border-bottom d-flex flex-column flex-lg-row justify-content-between gap-3">
                    <h5 class="mb-0">Tahapan Produksi</h5>
                    <form method="POST" action="{{ route('seed-productions.steps.store', $seedProduction) }}" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-12 col-md-3">
                            <label class="form-label small mb-1">Tahap</label>
                            <input
                                type="text"
                                name="label"
                                value="{{ old('label') }}"
                                class="form-control form-control-sm"
                                list="production-step-label-options"
                                placeholder="Nama tahap"
                                required
                            >
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-1">Tanggal</label>
                            <input
                                type="date"
                                name="planned_date"
                                value="{{ old('planned_date') }}"
                                class="form-control form-control-sm"
                            >
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-1">Posisi</label>
                            <select name="position" class="form-select form-select-sm">
                                @foreach($stepPositions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('position', 'end') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-1">Acuan</label>
                            <select name="reference_step_id" class="form-select form-select-sm">
                                <option value="">-</option>
                                @foreach($seedProduction->steps as $stepOption)
                                    <option value="{{ $stepOption->id }}" @selected(old('reference_step_id') === $stepOption->id)>
                                        {{ $stepOption->sort_order }}. {{ $stepOption->label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-1">Biaya</label>
                            <select name="cost_type" class="form-select form-select-sm">
                                @foreach($stepCostTypes as $value => $label)
                                    <option value="{{ $value }}" @selected(old('cost_type', 'per_kg') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-1 d-grid">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="bx bx-plus"></i>
                            </button>
                        </div>
                    </form>
                </div>
                <datalist id="production-step-label-options">
                    @foreach($stepSuggestions as $label)
                        <option value="{{ $label }}"></option>
                    @endforeach
                </datalist>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th style="width: 72px;">Urut</th>
                                <th style="min-width: 180px;">Tahap</th>
                                <th style="min-width: 190px;">Tanggal</th>
                                <th style="min-width: 140px;">Jenis Biaya</th>
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
                                @php($costType = old('cost_type', $step->cost_type ?? 'per_kg'))
                                <tr>
                                    <td>{{ $step->sort_order }}</td>
                                    <td>
                                        <input
                                            type="text"
                                            name="label"
                                            value="{{ old('label', $step->label) }}"
                                            class="form-control form-control-sm fw-medium"
                                            form="{{ $formId }}"
                                            list="production-step-label-options"
                                            required
                                        >
                                    </td>
                                    <td>
                                        <input
                                            type="date"
                                            name="planned_date"
                                            value="{{ old('planned_date', $step->actual_date?->toDateString() ?? $step->planned_date?->toDateString()) }}"
                                            class="form-control form-control-sm"
                                            form="{{ $formId }}"
                                        >
                                    </td>
                                    <td>
                                        <select
                                            name="cost_type"
                                            class="form-select form-select-sm production-step-cost-type"
                                            form="{{ $formId }}"
                                        >
                                            @foreach($stepCostTypes as $value => $label)
                                                <option value="{{ $value }}" @selected($costType === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
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
                                                @readonly($costType === 'langsung')
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
                                                @readonly($costType === 'per_kg')
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
        const additionalStockOptions = @json($additionalStockOptions);
        const productionRiceVarietyId = @json($seedProduction->rice_variety_id);
        const additionalRoleSelect = document.getElementById('additional-production-input-role');
        const additionalStockSelect = document.getElementById('additional-production-input-stock');

        function digits(value) {
            return String(value || '').replace(/\D/g, '');
        }

        function formatRupiah(value) {
            const cleanValue = digits(value);

            return cleanValue ? Number(cleanValue).toLocaleString('id-ID') : '';
        }

        function syncRowCost(row) {
            const quantityInput = row.querySelector('.production-step-quantity');
            const costTypeInput = row.querySelector('.production-step-cost-type');
            const costPerKgInput = row.querySelector('.production-step-cost-per-kg');
            const costInput = row.querySelector('.production-step-cost');
            const costType = costTypeInput?.value || 'per_kg';
            const quantity = Number(quantityInput?.value || 0);
            const costPerKg = Number(digits(costPerKgInput?.value || 0));

            if (!costInput) {
                return;
            }

            costInput.readOnly = costType === 'per_kg';

            if (costPerKgInput) {
                costPerKgInput.readOnly = costType === 'langsung';
            }

            if (costType === 'langsung') {
                return;
            }

            if (quantity <= 0 || costPerKg < 0) {
                costInput.value = '';
                return;
            }

            costInput.value = formatRupiah(Math.round(quantity * costPerKg));
        }

        function renderAdditionalStockOptions() {
            if (!additionalRoleSelect || !additionalStockSelect) {
                return;
            }

            const selectedValue = additionalStockSelect.dataset.selected || additionalStockSelect.value;
            const role = additionalRoleSelect.value;
            const options = role === 'bahan_utama' && productionRiceVarietyId
                ? additionalStockOptions.filter(function (option) {
                    return option.rice_variety_id === productionRiceVarietyId;
                })
                : additionalStockOptions;

            additionalStockSelect.innerHTML = [
                '<option value="">Pilih stok bahan</option>',
                ...options.map(function (option) {
                    return `<option value="${option.value}">${option.label}</option>`;
                })
            ].join('');

            if (options.some(function (option) { return option.value === selectedValue; })) {
                additionalStockSelect.value = selectedValue;
            }

            additionalStockSelect.dataset.selected = '';
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

        document.querySelectorAll('.production-step-cost-type').forEach(function (input) {
            syncRowCost(input.closest('tr'));

            input.addEventListener('change', function () {
                syncRowCost(input.closest('tr'));
            });
        });

        renderAdditionalStockOptions();
        additionalRoleSelect?.addEventListener('change', renderAdditionalStockOptions);
    });
</script>
@endsection
