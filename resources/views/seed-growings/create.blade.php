@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
        <div>
            <h4 class="mb-1">Tambah Penangkaran Benih</h4>
            <p class="text-muted mb-0">Satu data mewakili satu no lapangan, satu varietas, dan maksimal 5 hektar.</p>
        </div>
        <a href="{{ route('seed-growings.index') }}" class="btn btn-label-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <div class="fw-medium mb-1">Periksa kembali isian berikut:</div>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('seed-growings.store') }}" class="card">
        @csrf
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <label for="number" class="form-label">Nomor Dokumen</label>
                    <input type="text" id="number" name="number" value="{{ old('number') }}" class="form-control" placeholder="Auto generate jika kosong">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status Awal</label>
                    <input type="hidden" name="status" value="draft">
                    <div class="form-control bg-label-secondary">Draft</div>
                </div>

                <div class="col-md-6">
                    <label for="farmer_id" class="form-label">Petani</label>
                    <select id="farmer_id" name="farmer_id" class="form-select" required>
                        <option value="">Pilih petani</option>
                        @foreach($farmers as $farmer)
                            <option value="{{ $farmer->id }}" @selected(old('farmer_id') === $farmer->id)>{{ $farmer->code }} - {{ $farmer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="land_id" class="form-label">Lahan</label>
                    <select id="land_id" name="land_id" class="form-select" required>
                        <option value="">Pilih lahan</option>
                        @foreach($lands as $land)
                            <option
                                value="{{ $land->id }}"
                                data-farmer-id="{{ $land->farmer_id }}"
                                data-area-size="{{ $land->area_size }}"
                                @selected(old('land_id') === $land->id)
                            >
                                {{ $land->code }} - {{ $land->name }} ({{ $land->farmer?->name ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Lahan akan mengikuti petani yang dipilih.</div>
                </div>

                <div class="col-md-4">
                    <label for="field_number" class="form-label">No Lapangan</label>
                    <input type="text" id="field_number" name="field_number" value="{{ old('field_number') }}" class="form-control" placeholder="Contoh: LP-001A" required>
                </div>
                <div class="col-md-4">
                    <label for="lot_number" class="form-label">No Lot</label>
                    <input type="text" id="lot_number" name="lot_number" value="{{ old('lot_number') }}" class="form-control" placeholder="Boleh diisi saat panen">
                </div>
                <div class="col-md-4">
                    <label for="field_area" class="form-label">Luas No Lapangan</label>
                    <div class="input-group">
                        <input type="number" step="0.01" min="0.01" max="5" id="field_area" name="field_area" value="{{ old('field_area') }}" class="form-control" required>
                        <span class="input-group-text">ha</span>
                    </div>
                    <div class="form-text" id="field_area_help">Maksimal 5 ha per no lapangan.</div>
                </div>

                <div class="col-md-6">
                    <label for="rice_variety_id" class="form-label">Varietas</label>
                    <select id="rice_variety_id" name="rice_variety_id" class="form-select" required>
                        <option value="">Pilih varietas</option>
                        @foreach($riceVarieties as $variety)
                            <option value="{{ $variety->id }}" @selected(old('rice_variety_id') === $variety->id)>{{ $variety->code }} - {{ $variety->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="seed_class_id" class="form-label">Kelas Benih Tujuan</label>
                    <select id="seed_class_id" name="seed_class_id" class="form-select" required>
                        <option value="">Pilih kelas benih</option>
                        @foreach($seedClasses as $seedClass)
                            <option value="{{ $seedClass->id }}" @selected(old('seed_class_id') === $seedClass->id)>{{ $seedClass->code }} - {{ $seedClass->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-5">
                    <label for="source_seed_item_id" class="form-label">Benih Sumber</label>
                    <select id="source_seed_item_id" name="source_seed_item_id" class="form-select" required>
                        <option value="">Pilih barang benih</option>
                        @foreach($sourceSeedItems as $item)
                            <option
                                value="{{ $item->id }}"
                                data-rice-variety-id="{{ $item->rice_variety_id }}"
                                data-seed-class-id="{{ $item->seed_class_id }}"
                                data-unit="{{ $item->unit }}"
                                @selected(old('source_seed_item_id') === $item->id)
                            >
                                {{ $item->code }} - {{ $item->name }} ({{ $item->unit }})
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text" id="source_seed_item_help">Pilih varietas terlebih dahulu.</div>
                    <a
                        href="{{ route('master.items.create', [
                            'category' => 'benih',
                            'material_state' => 'benih_jadi',
                            'unit' => 'kg',
                        ]) }}"
                        class="btn btn-sm btn-label-primary mt-2 d-none"
                        id="add_source_seed_item_link"
                    >
                        <i class="bx bx-plus me-1"></i> Tambah Benih Sumber
                    </a>
                </div>
                <div class="col-md-4">
                    <label for="source_seed_warehouse_id" class="form-label">Gudang Benih Sumber</label>
                    <select id="source_seed_warehouse_id" name="source_seed_warehouse_id" class="form-select" required>
                        <option value="">Pilih gudang</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(old('source_seed_warehouse_id') === $warehouse->id)>{{ $warehouse->code }} - {{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                    <div class="form-text" id="source_seed_warehouse_help">Gudang mengikuti stok benih sumber yang dipilih.</div>
                    <a href="{{ route('stocks.create') }}" class="btn btn-sm btn-label-primary mt-2 d-none" id="adjust_source_seed_stock_link">
                        <i class="bx bx-edit me-1"></i> Penyesuaian Stok
                    </a>
                </div>
                <div class="col-md-3">
                    <label for="source_seed_quantity" class="form-label">Jumlah Benih Sumber</label>
                    <input type="number" step="0.01" min="0.01" id="source_seed_quantity" name="source_seed_quantity" value="{{ old('source_seed_quantity') }}" class="form-control" required>
                    <div class="form-text" id="source_seed_quantity_help">Pilih varietas, benih sumber, dan gudang.</div>
                </div>

                <div class="col-md-3">
                    <label for="sowing_date" class="form-label">Tanggal Sebar</label>
                    <input type="date" id="sowing_date" name="sowing_date" value="{{ old('sowing_date') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="planting_date" class="form-label">Tanggal Tanam</label>
                    <input type="date" id="planting_date" name="planting_date" value="{{ old('planting_date') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="planned_pendahuluan" class="form-label">Rencana Pendahuluan</label>
                    <input type="date" id="planned_pendahuluan" class="form-control" readonly>
                </div>
                <div class="col-md-3">
                    <label for="planned_pl1" class="form-label">Rencana PL1</label>
                    <input type="date" id="planned_pl1" class="form-control" readonly>
                </div>
                <div class="col-md-3">
                    <label for="planned_pl2" class="form-label">Rencana PL2</label>
                    <input type="date" id="planned_pl2" class="form-control" readonly>
                </div>
                <div class="col-md-3">
                    <label for="planned_pl3" class="form-label">Rencana PL3</label>
                    <input type="date" id="planned_pl3" class="form-control" readonly>
                </div>
                <div class="col-md-3">
                    <label for="harvest_date" class="form-label">Tanggal Panen</label>
                    <input type="date" id="harvest_date" name="harvest_date" value="{{ old('harvest_date') }}" class="form-control" readonly>
                </div>

                <div class="col-12">
                    <label for="notes" class="form-label">Catatan</label>
                    <textarea id="notes" name="notes" rows="3" class="form-control">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end gap-2">
            <a href="{{ route('seed-growings.index') }}" class="btn btn-label-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">
                <i class="bx bx-save me-1"></i> Simpan
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const farmerSelect = document.getElementById('farmer_id');
        const landSelect = document.getElementById('land_id');
        const fieldArea = document.getElementById('field_area');
        const fieldAreaHelp = document.getElementById('field_area_help');
        const riceVarietySelect = document.getElementById('rice_variety_id');
        const sourceSeedItemSelect = document.getElementById('source_seed_item_id');
        const sourceSeedItemHelp = document.getElementById('source_seed_item_help');
        const addSourceSeedItemLink = document.getElementById('add_source_seed_item_link');
        const sourceSeedWarehouseSelect = document.getElementById('source_seed_warehouse_id');
        const sourceSeedWarehouseHelp = document.getElementById('source_seed_warehouse_help');
        const adjustSourceSeedStockLink = document.getElementById('adjust_source_seed_stock_link');
        const sourceSeedQuantity = document.getElementById('source_seed_quantity');
        const sourceSeedQuantityHelp = document.getElementById('source_seed_quantity_help');
        const plantingDate = document.getElementById('planting_date');
        const usedFieldAreas = @json($usedFieldAreas);
        const sourceSeedStocks = @json($sourceSeedStocks);
        const scheduleFields = {
            planned_pendahuluan: 15,
            planned_pl1: 45,
            planned_pl2: 75,
            planned_pl3: 95,
            harvest_date: 105,
        };

        function syncLands() {
            const farmerId = farmerSelect.value;

            Array.from(landSelect.options).forEach(function (option) {
                if (! option.value) {
                    option.hidden = false;
                    return;
                }

                option.hidden = farmerId && option.dataset.farmerId !== farmerId;
            });

            const selected = landSelect.selectedOptions[0];
            if (selected && selected.hidden) {
                landSelect.value = '';
            }

            fillFieldArea();
        }

        function resolveSeasonYear() {
            if (! plantingDate.value) {
                return new Date().getFullYear();
            }

            return new Date(plantingDate.value + 'T00:00:00').getFullYear();
        }

        function formatNumber(value) {
            const rounded = Math.round(value * 100) / 100;

            return rounded.toFixed(2).replace(/\.?0+$/, '');
        }

        function fillFieldArea(force = false) {
            const selected = landSelect.selectedOptions[0];
            const landArea = selected && selected.dataset.areaSize ? Number(selected.dataset.areaSize) : 0;

            if (! selected || ! selected.value || ! landArea) {
                if (fieldAreaHelp) {
                    fieldAreaHelp.textContent = 'Maksimal 5 ha per no lapangan.';
                }
                return;
            }

            const seasonYear = resolveSeasonYear();
            const usedArea = Number(usedFieldAreas[selected.value]?.[seasonYear] || 0);
            const remainingArea = Math.max(landArea - usedArea, 0);
            const suggestedArea = Math.min(remainingArea || landArea, 5);

            if (force || ! fieldArea.value) {
                fieldArea.value = suggestedArea > 0 ? formatNumber(suggestedArea) : '';
            }

            if (fieldAreaHelp) {
                fieldAreaHelp.textContent = `Luas lahan ${formatNumber(landArea)} ha, terpakai ${formatNumber(usedArea)} ha, sisa ${formatNumber(remainingArea)} ha. Maksimal 5 ha per no lapangan.`;
            }
        }

        function syncSourceSeedItems() {
            const varietyId = riceVarietySelect.value;
            const seedClassId = document.getElementById('seed_class_id').value;
            let availableItems = 0;

            Array.from(sourceSeedItemSelect.options).forEach(function (option) {
                if (! option.value) {
                    option.hidden = false;
                    return;
                }

                option.hidden = ! varietyId || option.dataset.riceVarietyId !== varietyId;

                if (! option.hidden) {
                    availableItems += 1;
                }
            });

            const selected = sourceSeedItemSelect.selectedOptions[0];
            if (selected && selected.hidden) {
                sourceSeedItemSelect.value = '';
            }

            if (! sourceSeedItemSelect.value && availableItems === 1) {
                const onlyOption = Array.from(sourceSeedItemSelect.options).find(function (option) {
                    return option.value && ! option.hidden;
                });
                sourceSeedItemSelect.value = onlyOption.value;
            }

            if (sourceSeedItemHelp) {
                sourceSeedItemHelp.textContent = varietyId
                    ? (availableItems ? 'Benih sumber sudah difilter sesuai varietas.' : 'Belum ada benih sumber untuk varietas ini di master barang.')
                    : 'Pilih varietas terlebih dahulu.';
            }

            if (addSourceSeedItemLink) {
                addSourceSeedItemLink.classList.toggle('d-none', ! varietyId || availableItems > 0);
                const url = new URL(addSourceSeedItemLink.href, window.location.origin);
                url.searchParams.set('category', 'benih');
                url.searchParams.set('material_state', 'benih_jadi');
                url.searchParams.set('unit', 'kg');
                url.searchParams.set('rice_variety_id', varietyId || '');
                url.searchParams.set('seed_class_id', seedClassId || '');
                addSourceSeedItemLink.href = url.toString();
            }

            syncSourceSeedWarehouses();
            fillSourceSeedQuantity();
        }

        function syncSourceSeedWarehouses() {
            const itemId = sourceSeedItemSelect.value;
            let availableWarehouses = 0;

            Array.from(sourceSeedWarehouseSelect.options).forEach(function (option) {
                if (! option.value) {
                    option.hidden = false;
                    return;
                }

                const stock = Number(sourceSeedStocks[itemId]?.[option.value] || 0);
                option.hidden = ! itemId || stock <= 0;

                if (! option.hidden) {
                    availableWarehouses += 1;
                }
            });

            const selected = sourceSeedWarehouseSelect.selectedOptions[0];
            if (selected && selected.hidden) {
                sourceSeedWarehouseSelect.value = '';
            }

            if (! sourceSeedWarehouseSelect.value && availableWarehouses === 1) {
                const onlyOption = Array.from(sourceSeedWarehouseSelect.options).find(function (option) {
                    return option.value && ! option.hidden;
                });
                sourceSeedWarehouseSelect.value = onlyOption.value;
            }

            if (sourceSeedWarehouseHelp) {
                sourceSeedWarehouseHelp.textContent = itemId
                    ? (availableWarehouses ? 'Gudang sudah difilter berdasarkan stok benih sumber.' : 'Belum ada stok benih sumber ini di gudang.')
                    : 'Pilih benih sumber terlebih dahulu.';
            }

            if (adjustSourceSeedStockLink) {
                adjustSourceSeedStockLink.classList.toggle('d-none', ! itemId || availableWarehouses > 0);
            }
        }

        function fillSourceSeedQuantity(force = false) {
            const itemId = sourceSeedItemSelect.value;
            const warehouseId = sourceSeedWarehouseSelect.value;
            const selectedItem = sourceSeedItemSelect.selectedOptions[0];
            const unit = selectedItem?.dataset.unit || '';

            if (! itemId || ! warehouseId) {
                sourceSeedQuantity.removeAttribute('max');
                if (sourceSeedQuantityHelp) {
                    sourceSeedQuantityHelp.textContent = 'Pilih varietas, benih sumber, dan gudang.';
                }
                return;
            }

            const availableStock = Number(sourceSeedStocks[itemId]?.[warehouseId] || 0);
            sourceSeedQuantity.max = availableStock;

            if (force || ! sourceSeedQuantity.value || Number(sourceSeedQuantity.value) > availableStock) {
                sourceSeedQuantity.value = availableStock > 0 ? formatNumber(availableStock) : '';
            }

            if (sourceSeedQuantityHelp) {
                sourceSeedQuantityHelp.textContent = `Stok tersedia ${formatNumber(availableStock)} ${unit.toUpperCase()}.`;
            }
        }

        function formatDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');

            return `${year}-${month}-${day}`;
        }

        function fillSchedule() {
            if (! plantingDate.value) {
                return;
            }

            Object.entries(scheduleFields).forEach(function ([fieldId, offset]) {
                const field = document.getElementById(fieldId);
                if (! field) {
                    return;
                }

                const date = new Date(plantingDate.value + 'T00:00:00');
                date.setDate(date.getDate() + offset);
                field.value = formatDate(date);
            });
        }

        farmerSelect.addEventListener('change', syncLands);
        riceVarietySelect.addEventListener('change', syncSourceSeedItems);
        document.getElementById('seed_class_id').addEventListener('change', syncSourceSeedItems);
        sourceSeedItemSelect.addEventListener('change', function () {
            syncSourceSeedWarehouses();
            fillSourceSeedQuantity(true);
        });
        sourceSeedWarehouseSelect.addEventListener('change', function () {
            fillSourceSeedQuantity(true);
        });
        landSelect.addEventListener('change', function () {
            fillFieldArea(true);
        });
        plantingDate.addEventListener('change', function () {
            fillSchedule();
            fillFieldArea(! fieldArea.value);
        });

        syncLands();
        syncSourceSeedItems();
        fillSchedule();
        fillFieldArea(! fieldArea.value);
        fillSourceSeedQuantity(! sourceSeedQuantity.value);
    });
</script>
@endpush
