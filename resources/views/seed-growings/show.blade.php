@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
        <div>
            <h4 class="mb-1">Detail Penangkaran Benih</h4>
            <p class="text-muted mb-0">{{ $seedGrowing->number }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('seed-growings.index') }}" class="btn btn-label-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali
            </a>
            <a href="{{ route('seed-growings.create') }}" class="btn btn-primary">
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

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header border-bottom">
                    <h5 class="mb-0">Informasi Lapangan</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Nomor</dt>
                        <dd class="col-sm-8">{{ $seedGrowing->number }}</dd>

                        <dt class="col-sm-4">Petani</dt>
                        <dd class="col-sm-8">{{ $seedGrowing->farmer?->name ?? '-' }}</dd>

                        <dt class="col-sm-4">Lahan</dt>
                        <dd class="col-sm-8">{{ $seedGrowing->land?->name ?? '-' }}</dd>

                        <dt class="col-sm-4">No Lapangan</dt>
                        <dd class="col-sm-8">{{ $seedGrowing->field_number }}</dd>

                        <dt class="col-sm-4">No Lot</dt>
                        <dd class="col-sm-8">{{ $seedGrowing->lot_number ?: '-' }}</dd>

                        <dt class="col-sm-4">Tahun Musim</dt>
                        <dd class="col-sm-8">{{ $seedGrowing->season_year }}</dd>

                        <dt class="col-sm-4">Luas</dt>
                        <dd class="col-sm-8">{{ number_format((float) $seedGrowing->field_area, 2, ',', '.') }} ha</dd>

                        <dt class="col-sm-4">Varietas</dt>
                        <dd class="col-sm-8">{{ $seedGrowing->riceVariety?->name ?? '-' }}</dd>

                        <dt class="col-sm-4">Kelas Benih Tujuan</dt>
                        <dd class="col-sm-8">{{ $seedGrowing->seedClass?->name ?? '-' }}</dd>

                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            @php($badge = match($seedGrowing->status) {
                                'berjalan' => 'primary',
                                'panen' => 'info',
                                'selesai' => 'success',
                                'batal' => 'danger',
                                default => 'secondary',
                            })
                            <span class="badge bg-label-{{ $badge }}">{{ $statuses[$seedGrowing->status] ?? $seedGrowing->status }}</span>
                        </dd>

                        <dt class="col-sm-4">Pembatalan</dt>
                        <dd class="col-sm-8">
                            @if($seedGrowing->status === 'batal')
                                <span class="text-muted">Penangkaran ini sudah dibatalkan.</span>
                            @elseif($seedGrowing->status !== 'draft')
                                <span class="text-muted">Pembatalan hanya bisa dilakukan saat status masih Draft.</span>
                            @else
                                <form method="POST" action="{{ route('seed-growings.status.update', $seedGrowing) }}" data-confirm-delete data-confirm-title="Batalkan penangkaran?" data-confirm-text="Status akan berubah menjadi Batal.">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="batal">
                                    <button type="submit" class="btn btn-sm btn-label-danger">
                                        <i class="bx bx-x-circle me-1"></i> Batalkan
                                    </button>
                                </form>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Dibuat Oleh</dt>
                        <dd class="col-sm-8">{{ $seedGrowing->creator?->name ?? '-' }}</dd>

                        <dt class="col-sm-4">Catatan</dt>
                        <dd class="col-sm-8">{{ $seedGrowing->notes ?: '-' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header border-bottom">
                    <h5 class="mb-0">Benih Sumber</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Barang</dt>
                        <dd class="col-sm-7">{{ $seedGrowing->sourceSeedItem?->name ?? '-' }}</dd>

                        <dt class="col-sm-5">Kode</dt>
                        <dd class="col-sm-7">{{ $seedGrowing->sourceSeedItem?->code ?? '-' }}</dd>

                        <dt class="col-sm-5">Gudang</dt>
                        <dd class="col-sm-7">{{ $seedGrowing->sourceSeedWarehouse?->name ?? '-' }}</dd>

                        <dt class="col-sm-5">Jumlah</dt>
                        <dd class="col-sm-7">
                            {{ number_format((float) $seedGrowing->source_seed_quantity, 2, ',', '.') }}
                            {{ $seedGrowing->sourceSeedItem?->unit }}
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="mb-0">Jadwal & Biaya Pemeriksaan Lapang</h5>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Tahap</th>
                                <th style="min-width: 160px;">Tanggal Rencana</th>
                                <th style="min-width: 160px;">Tanggal Aktual</th>
                                <th style="min-width: 180px;">Biaya</th>
                                <th style="min-width: 150px;">Status</th>
                                <th style="min-width: 220px;">Catatan</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inspectionStages as $stage => $config)
                                @php($inspection = $seedGrowing->inspections->firstWhere('stage', $stage))
                                @if($inspection)
                                    @php($formId = 'inspection-form-'.$inspection->id)
                                    <tr>
                                        <td class="fw-medium">{{ $config['label'] }}</td>
                                        <td>
                                            <input
                                                type="date"
                                                name="planned_date"
                                                value="{{ old('planned_date', $inspection->planned_date?->toDateString()) }}"
                                                class="form-control form-control-sm"
                                                form="{{ $formId }}"
                                                @disabled($seedGrowing->status === 'batal')
                                            >
                                        </td>
                                        <td>
                                            <input
                                                type="date"
                                                name="actual_date"
                                                value="{{ old('actual_date', $inspection->actual_date?->toDateString()) }}"
                                                class="form-control form-control-sm"
                                                form="{{ $formId }}"
                                                @disabled($seedGrowing->status === 'batal')
                                            >
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">Rp</span>
                                                <input
                                                    type="text"
                                                    inputmode="numeric"
                                                    name="cost"
                                                    value="{{ old('cost', number_format((float) $inspection->cost, 0, ',', '.')) }}"
                                                    class="form-control rupiah-input"
                                                    form="{{ $formId }}"
                                                    placeholder="0"
                                                    @disabled($seedGrowing->status === 'batal')
                                                >
                                            </div>
                                        </td>
                                        <td>
                                            <select name="status" class="form-select form-select-sm" form="{{ $formId }}" @disabled($seedGrowing->status === 'batal')>
                                                @foreach($inspectionStatuses as $value => $label)
                                                    <option value="{{ $value }}" @selected(old('status', $inspection->status) === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input
                                                type="text"
                                                name="notes"
                                                value="{{ old('notes', $inspection->notes) }}"
                                                class="form-control form-control-sm"
                                                placeholder="Catatan"
                                                form="{{ $formId }}"
                                                @disabled($seedGrowing->status === 'batal')
                                            >
                                        </td>
                                        <td class="text-end">
                                            <form id="{{ $formId }}" method="POST" action="{{ route('seed-growings.inspections.update', [$seedGrowing, $inspection]) }}">
                                                @csrf
                                                @method('PATCH')
                                            </form>
                                            <button type="submit" class="btn btn-sm btn-label-primary" form="{{ $formId }}" @disabled($seedGrowing->status === 'batal')>
                                                <i class="bx bx-save me-1"></i> Simpan
                                            </button>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Total Biaya</th>
                                <th colspan="4">Rp {{ number_format((float) $seedGrowing->inspections->sum('cost'), 2, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="mb-0">Panen</h5>
                </div>
                <form method="POST" action="{{ route('seed-growings.harvest.update', $seedGrowing) }}">
                    @csrf
                    @method('PATCH')
                    <div class="card-body">
                        @if($seedGrowing->status === 'batal')
                            <div class="alert alert-warning">
                                Panen tidak bisa disimpan karena penangkaran sudah batal.
                            </div>
                        @endif
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label for="harvest_item_id" class="form-label">Barang Hasil Panen</label>
                                @if($harvestItems->isEmpty())
                                    <div class="alert alert-warning mb-2 py-2">
                                        Belum ada barang hasil panen untuk varietas {{ $seedGrowing->riceVariety?->name ?? '-' }}.
                                    </div>
                                    <a
                                        href="{{ route('master.items.create', [
                                            'return_to' => 'seed-growings.show',
                                            'seed_growing_id' => $seedGrowing->id,
                                            'category' => 'gabah',
                                            'material_state' => $seedGrowing->harvest?->material_state ?? 'basah',
                                            'unit' => $seedGrowing->harvest?->unit ?? 'kg',
                                            'rice_variety_id' => $seedGrowing->rice_variety_id,
                                            'seed_class_id' => $seedGrowing->seed_class_id,
                                        ]) }}"
                                        class="btn btn-sm btn-label-primary"
                                    >
                                        <i class="bx bx-plus me-1"></i> Tambah Barang
                                    </a>
                                @else
                                    <select id="harvest_item_id" name="harvest_item_id" class="form-select" required @disabled($seedGrowing->status === 'batal')>
                                        <option value="">Pilih barang</option>
                                        @foreach($harvestItems as $item)
                                            <option
                                                value="{{ $item->id }}"
                                                @selected(old('harvest_item_id', $seedGrowing->harvest?->harvest_item_id ?? ($harvestItems->count() === 1 ? $item->id : '')) === $item->id)
                                            >
                                                {{ $item->code }} - {{ $item->name }} ({{ strtoupper($item->unit) }})
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                            <div class="col-md-4">
                                <label for="harvest_warehouse_id" class="form-label">Gudang Tujuan</label>
                                <select id="harvest_warehouse_id" name="harvest_warehouse_id" class="form-select" required @disabled($seedGrowing->status === 'batal')>
                                    <option value="">Pilih gudang</option>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" @selected(old('harvest_warehouse_id', $seedGrowing->harvest?->harvest_warehouse_id) === $warehouse->id)>
                                            {{ $warehouse->code }} - {{ $warehouse->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="harvest_date" class="form-label">Tanggal Panen Aktual</label>
                                <input
                                    type="date"
                                    id="harvest_date"
                                    name="harvest_date"
                                    value="{{ old('harvest_date', $seedGrowing->harvest?->harvest_date?->toDateString() ?? $seedGrowing->harvest_date?->toDateString()) }}"
                                    class="form-control"
                                    required
                                    @disabled($seedGrowing->status === 'batal')
                                >
                            </div>
                            <div class="col-md-4">
                                <label for="harvested_quantity" class="form-label">Jumlah Panen</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    id="harvested_quantity"
                                    name="harvested_quantity"
                                    value="{{ old('harvested_quantity', $seedGrowing->harvest?->harvested_quantity) }}"
                                    class="form-control"
                                    required
                                    @disabled($seedGrowing->status === 'batal')
                                >
                            </div>
                            <div class="col-md-2">
                                <label for="unit" class="form-label">Satuan</label>
                                <input
                                    type="text"
                                    id="unit"
                                    name="unit"
                                    value="{{ old('unit', $seedGrowing->harvest?->unit ?? 'kg') }}"
                                    class="form-control"
                                    required
                                    @disabled($seedGrowing->status === 'batal')
                                >
                            </div>
                            <div class="col-md-2">
                                <label for="material_state" class="form-label">Kondisi</label>
                                <select id="material_state" name="material_state" class="form-select" required @disabled($seedGrowing->status === 'batal')>
                                    @foreach($harvestMaterialStates as $value => $label)
                                        <option value="{{ $value }}" @selected(old('material_state', $seedGrowing->harvest?->material_state ?? 'basah') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="harvest_status" class="form-label">Status Panen</label>
                                <select id="harvest_status" name="status" class="form-select" required @disabled($seedGrowing->status === 'batal')>
                                    @foreach($harvestStatuses as $value => $label)
                                        <option value="{{ $value }}" @selected(old('status', $seedGrowing->harvest?->status ?? 'panen') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="harvest_notes" class="form-label">Catatan Panen</label>
                                <textarea id="harvest_notes" name="notes" rows="3" class="form-control" @disabled($seedGrowing->status === 'batal')>{{ old('notes', $seedGrowing->harvest?->notes) }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary" @disabled($seedGrowing->status === 'batal')>
                            <i class="bx bx-save me-1"></i> Simpan Panen
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="mb-0">Tanggal Dasar Penangkaran</h5>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td class="fw-medium">Tanggal Sebar</td>
                                <td>{{ $seedGrowing->sowing_date?->format('d M Y') ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-medium">Tanggal Tanam</td>
                                <td>{{ $seedGrowing->planting_date?->format('d M Y') ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-medium">Tanggal Panen</td>
                                <td>{{ $seedGrowing->harvest_date?->format('d M Y') ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const rupiahInputs = document.querySelectorAll('.rupiah-input');

        function formatRupiah(value) {
            const digits = String(value || '').replace(/\D/g, '');

            return digits ? Number(digits).toLocaleString('id-ID') : '';
        }

        rupiahInputs.forEach(function (input) {
            input.value = formatRupiah(input.value);

            input.addEventListener('input', function () {
                input.value = formatRupiah(input.value);
            });
        });
    });
</script>
@endpush
