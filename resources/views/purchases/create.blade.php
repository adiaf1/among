@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="py-4">
        <h4 class="mb-1">Tambah Pembelian Barang</h4>
        <p class="text-muted mb-0">Barang yang disimpan akan langsung menambah stok gudang tujuan.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('purchases.store') }}">
                @csrf

                @php
                    $selectedSourceType = old('source_type', request('source_type', request('farmer_id') ? 'farmer' : 'supplier'));
                    $selectedSupplierId = old('supplier_id', request('supplier_id'));
                    $selectedFarmerId = old('farmer_id', request('farmer_id'));
                    $selectedSupplier = $suppliers->firstWhere('id', $selectedSupplierId);
                    $selectedFarmer = $farmers->firstWhere('id', $selectedFarmerId);
                    $supplierOptions = $suppliers->map(fn ($supplier) => [
                        'value' => $supplier->id,
                        'code' => $supplier->code,
                        'name' => $supplier->name,
                        'label' => "{$supplier->code} - {$supplier->name}",
                        'details' => [
                            ['label' => 'Kontak', 'value' => $supplier->contact_person],
                            ['label' => 'Telepon', 'value' => $supplier->phone],
                            ['label' => 'Alamat', 'value' => $supplier->address],
                        ],
                    ])->values();
                    $farmerOptions = $farmers->map(fn ($farmer) => [
                        'value' => $farmer->id,
                        'code' => $farmer->code,
                        'name' => $farmer->name,
                        'label' => "{$farmer->code} - {$farmer->name}",
                        'details' => [
                            ['label' => 'Telepon', 'value' => $farmer->phone],
                            ['label' => 'Alamat', 'value' => $farmer->address],
                        ],
                    ])->values();
                    $transportTypeOptions = $transportTypes->map(fn ($transportType) => [
                        'value' => $transportType,
                        'label' => $transportType,
                    ])->values();
                    $vehiclePlateOptions = $vehiclePlateNumbers->map(fn ($vehiclePlateNumber) => [
                        'value' => $vehiclePlateNumber,
                        'label' => $vehiclePlateNumber,
                    ])->values();
                @endphp

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label for="number" class="form-label">Nomor</label>
                        <input
                            type="text"
                            class="form-control @error('number') is-invalid @enderror"
                            id="number"
                            name="number"
                            value="{{ old('number') }}"
                            maxlength="100"
                            placeholder="Kosongkan untuk otomatis"
                        >
                        @error('number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="purchase_date" class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input
                            type="date"
                            class="form-control @error('purchase_date') is-invalid @enderror"
                            id="purchase_date"
                            name="purchase_date"
                            value="{{ old('purchase_date', now()->toDateString()) }}"
                            required
                        >
                        @error('purchase_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="transport_type" class="form-label">Jenis Angkutan</label>
                        <div class="source-combobox position-relative" data-free-combobox="transport_type">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bxs-truck"></i></span>
                                <input
                                    type="search"
                                    class="form-control @error('transport_type') is-invalid @enderror"
                                    id="transport_type"
                                    name="transport_type"
                                    value="{{ old('transport_type') }}"
                                    maxlength="100"
                                    placeholder="Contoh: Truk Engkel"
                                    autocomplete="off"
                                    data-combobox-input
                                >
                                <button type="button" class="btn btn-outline-secondary" data-combobox-clear title="Bersihkan isian">
                                    <i class="bx bx-x"></i>
                                </button>
                            </div>
                            <div class="source-combobox-menu d-none" data-combobox-menu></div>
                        </div>
                        @error('transport_type')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="vehicle_plate_number" class="form-label">No Pol Kendaraan</label>
                        <div class="source-combobox position-relative" data-free-combobox="vehicle_plate_number">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-id-card"></i></span>
                                <input
                                    type="search"
                                    class="form-control @error('vehicle_plate_number') is-invalid @enderror"
                                    id="vehicle_plate_number"
                                    name="vehicle_plate_number"
                                    value="{{ old('vehicle_plate_number') }}"
                                    maxlength="20"
                                    placeholder="Contoh: BE 1234 AB"
                                    autocomplete="off"
                                    data-combobox-input
                                    data-uppercase
                                >
                                <button type="button" class="btn btn-outline-secondary" data-combobox-clear title="Bersihkan isian">
                                    <i class="bx bx-x"></i>
                                </button>
                            </div>
                            <div class="source-combobox-menu d-none" data-combobox-menu></div>
                        </div>
                        @error('vehicle_plate_number')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="source_type" class="form-label">Asal Barang <span class="text-danger">*</span></label>
                        <select
                            class="form-select @error('source_type') is-invalid @enderror"
                            id="source_type"
                            name="source_type"
                            required
                        >
                            <option value="supplier" @selected($selectedSourceType === 'supplier')>Supplier</option>
                            <option value="farmer" @selected($selectedSourceType === 'farmer')>Petani</option>
                        </select>
                        @error('source_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6" data-source-field="supplier">
                        <label for="supplier_id" class="form-label">Supplier <span class="text-danger">*</span></label>
                        <div class="source-combobox position-relative" data-source-combobox="supplier">
                            <input type="hidden" id="supplier_id" name="supplier_id" value="{{ $selectedSupplierId }}">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-search"></i></span>
                                <input
                                    type="search"
                                    class="form-control @error('supplier_id') is-invalid @enderror"
                                    value="{{ $selectedSupplier ? "{$selectedSupplier->code} - {$selectedSupplier->name}" : '' }}"
                                    placeholder="Cari supplier"
                                    autocomplete="off"
                                    data-combobox-input
                                >
                                <button type="button" class="btn btn-outline-secondary" data-combobox-clear title="Bersihkan pilihan">
                                    <i class="bx bx-x"></i>
                                </button>
                            </div>
                            <div class="source-combobox-menu d-none" data-combobox-menu></div>
                        </div>
                        <div class="source-identity d-none" data-source-identity="supplier"></div>
                        @error('supplier_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6" data-source-field="farmer">
                        <label for="farmer_id" class="form-label">Petani <span class="text-danger">*</span></label>
                        <div class="source-combobox position-relative" data-source-combobox="farmer">
                            <input type="hidden" id="farmer_id" name="farmer_id" value="{{ $selectedFarmerId }}">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-search"></i></span>
                                <input
                                    type="search"
                                    class="form-control @error('farmer_id') is-invalid @enderror"
                                    value="{{ $selectedFarmer ? "{$selectedFarmer->code} - {$selectedFarmer->name}" : '' }}"
                                    placeholder="Cari petani"
                                    autocomplete="off"
                                    data-combobox-input
                                >
                                <button type="button" class="btn btn-outline-secondary" data-combobox-clear title="Bersihkan pilihan">
                                    <i class="bx bx-x"></i>
                                </button>
                            </div>
                            <div class="source-combobox-menu d-none" data-combobox-menu></div>
                        </div>
                        <div class="source-identity d-none" data-source-identity="farmer"></div>
                        @error('farmer_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="notes" class="form-label">Catatan</label>
                        <textarea
                            class="form-control @error('notes') is-invalid @enderror"
                            id="notes"
                            name="notes"
                            rows="2"
                            placeholder="Catatan pembelian"
                        >{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                @php
                    $oldItems = old('items', [
                        ['item_id' => request('item_id', ''), 'warehouse_id' => '', 'quantity' => '', 'unit_price' => 0],
                    ]);
                @endphp

                <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                    <h6 class="mb-0">Detail Barang</h6>
                    <button type="button" class="btn btn-label-primary btn-sm" id="add-purchase-item-row">
                        <i class="bx bx-plus me-1"></i> Tambah Baris
                    </button>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th style="min-width: 260px;">Barang</th>
                                <th style="min-width: 240px;">Gudang Tujuan</th>
                                <th style="min-width: 140px;">Jumlah</th>
                                <th style="min-width: 160px;">Harga Satuan</th>
                                <th style="width: 72px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="purchase-items-body">
                            @foreach($oldItems as $index => $oldItem)
                                @php
                                    $selectedItem = $items->firstWhere('id', $oldItem['item_id'] ?? '');
                                    $selectedItemLabel = '';
                                    $selectedItemUnit = '';

                                    if ($selectedItem) {
                                        $categoryLabel = $itemCategories[$selectedItem->category] ?? ucfirst(str_replace('_', ' ', $selectedItem->category));
                                        $stateLabel = $itemMaterialStates[$selectedItem->material_state ?? 'none'] ?? ucfirst(str_replace('_', ' ', $selectedItem->material_state ?? 'none'));
                                        $itemLabel = ($selectedItem->material_state ?? 'none') === 'none' ? $categoryLabel : "{$categoryLabel} - {$stateLabel}";
                                        $selectedItemLabel = "{$selectedItem->code} - {$selectedItem->name} [{$itemLabel}] (".strtoupper($selectedItem->unit).")";
                                        $selectedItemUnit = strtoupper($selectedItem->unit);
                                    }
                                @endphp
                                <tr data-purchase-item-row>
                                    <td>
                                        <div class="source-combobox position-relative" data-item-combobox>
                                            <input type="hidden" data-field="item_id" name="items[{{ $index }}][item_id]" value="{{ $oldItem['item_id'] ?? '' }}">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bx bx-search"></i></span>
                                                <input
                                                    type="search"
                                                    class="form-control @error("items.$index.item_id") is-invalid @enderror"
                                                    value="{{ $selectedItemLabel }}"
                                                    placeholder="Cari barang"
                                                    autocomplete="off"
                                                    data-combobox-input
                                                >
                                                <button type="button" class="btn btn-outline-secondary" data-combobox-clear title="Bersihkan pilihan">
                                                    <i class="bx bx-x"></i>
                                                </button>
                                            </div>
                                            <div class="source-combobox-menu d-none" data-combobox-menu></div>
                                        </div>
                                        @error("items.$index.item_id")
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td>
                                        <select
                                            data-field="warehouse_id"
                                            class="form-select @error("items.$index.warehouse_id") is-invalid @enderror"
                                            name="items[{{ $index }}][warehouse_id]"
                                            required
                                        >
                                            <option value="">Pilih gudang</option>
                                            @foreach($warehouses as $warehouse)
                                                <option value="{{ $warehouse->id }}" @selected(($oldItem['warehouse_id'] ?? '') === $warehouse->id)>
                                                    {{ $warehouse->code }} - {{ $warehouse->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("items.$index.warehouse_id")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td>
                                        <input
                                            type="number"
                                            data-field="quantity"
                                            class="form-control @error("items.$index.quantity") is-invalid @enderror"
                                            name="items[{{ $index }}][quantity]"
                                            value="{{ $oldItem['quantity'] ?? '' }}"
                                            min="0.01"
                                            max="9999999999.99"
                                            step="0.01"
                                            required
                                        >
                                        <div class="form-text" data-unit-label>{{ $selectedItemUnit ? "Satuan: {$selectedItemUnit}" : 'Pilih barang untuk melihat satuan.' }}</div>
                                        @error("items.$index.quantity")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td>
                                        <input
                                            type="text"
                                            inputmode="numeric"
                                            data-field="unit_price"
                                            class="form-control @error("items.$index.unit_price") is-invalid @enderror"
                                            name="items[{{ $index }}][unit_price]"
                                            value="{{ ($oldItem['unit_price'] ?? '') !== '' ? number_format((float) $oldItem['unit_price'], 0, ',', '.') : '' }}"
                                            data-money-input
                                            required
                                        >
                                        <div class="form-text" data-unit-price-label>{{ $selectedItemUnit ? "Per {$selectedItemUnit}" : 'Harga per satuan barang.' }}</div>
                                        @error("items.$index.unit_price")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-icon btn-label-danger" data-remove-row title="Hapus baris">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
                    <a href="{{ route('purchases.index') }}" class="btn btn-label-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Pembelian</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .source-combobox-menu {
        position: absolute;
        z-index: 1080;
        width: 100%;
        max-height: 260px;
        overflow-y: auto;
        white-space: normal;
        margin-top: 0.25rem;
        padding: 0.35rem;
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 0.375rem;
        box-shadow: 0 0.5rem 1.25rem rgba(34, 48, 62, 0.12);
    }

    .source-combobox-option {
        display: block;
        width: 100%;
        padding: 0.55rem 0.65rem;
        border: 0;
        border-radius: 0.25rem;
        background: transparent;
        color: var(--bs-body-color);
        text-align: left;
        white-space: normal;
    }

    .source-combobox-option:hover,
    .source-combobox-option:focus {
        background: var(--bs-primary-bg-subtle);
        color: var(--bs-primary);
        outline: 0;
    }

    .source-identity {
        margin-top: 0.75rem;
        padding: 0.75rem;
        border: 1px solid var(--bs-border-color);
        border-radius: 0.375rem;
        background: var(--bs-light-bg-subtle);
    }

    .source-identity-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.5rem 0.75rem;
    }

    .source-identity-value {
        overflow-wrap: anywhere;
    }

    @media (max-width: 575.98px) {
        .source-identity-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

@php
    $itemOptionData = $items->map(function ($item) use ($itemCategories, $itemMaterialStates) {
        $categoryLabel = $itemCategories[$item->category] ?? ucfirst(str_replace('_', ' ', $item->category));
        $stateLabel = $itemMaterialStates[$item->material_state ?? 'none'] ?? ucfirst(str_replace('_', ' ', $item->material_state ?? 'none'));
        $itemLabel = ($item->material_state ?? 'none') === 'none' ? $categoryLabel : "{$categoryLabel} - {$stateLabel}";

        return [
            'value' => $item->id,
            'code' => $item->code,
            'name' => $item->name,
            'unit' => strtoupper($item->unit),
            'label' => sprintf('%s - %s [%s] (%s)', $item->code, $item->name, $itemLabel, strtoupper($item->unit)),
        ];
    })->values();
@endphp

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const itemOptions = @json($itemOptionData);
        const warehouseOptions = @json($warehouses->map(fn ($warehouse) => [
            'value' => $warehouse->id,
            'label' => "{$warehouse->code} - {$warehouse->name}",
        ])->values());
        const sourceData = {
            supplier: @json($supplierOptions),
            farmer: @json($farmerOptions),
        };
        const sourceCreateUrls = {
            supplier: @json(route('master.suppliers.create', ['return_to' => 'purchases.create'])),
            farmer: @json(route('master.farmers.create', ['return_to' => 'purchases.create'])),
        };
        const itemCreateUrl = @json(route('master.items.create', ['return_to' => 'purchases.create']));
        const freeComboboxData = {
            transport_type: @json($transportTypeOptions),
            vehicle_plate_number: @json($vehiclePlateOptions),
        };
        const tableBody = document.getElementById('purchase-items-body');
        const sourceType = document.getElementById('source_type');
        const supplierField = document.querySelector('[data-source-field="supplier"]');
        const farmerField = document.querySelector('[data-source-field="farmer"]');
        const supplierInput = document.getElementById('supplier_id');
        const farmerInput = document.getElementById('farmer_id');

        const renderSourceIdentity = function (type, value) {
            const panel = document.querySelector(`[data-source-identity="${type}"]`);
            const option = (sourceData[type] || []).find(function (sourceOption) {
                return sourceOption.value === value;
            });

            if (!panel) {
                return;
            }

            panel.innerHTML = '';

            if (!option) {
                panel.classList.add('d-none');
                return;
            }

            const title = document.createElement('div');
            const meta = document.createElement('div');
            const grid = document.createElement('div');

            title.className = 'fw-medium mb-1';
            title.textContent = option.name;
            meta.className = 'text-muted small mb-2';
            meta.textContent = option.code;
            grid.className = 'source-identity-grid';

            option.details
                .filter(function (detail) {
                    return detail.value;
                })
                .forEach(function (detail) {
                    const item = document.createElement('div');
                    const label = document.createElement('div');
                    const value = document.createElement('div');

                    label.className = 'text-muted small';
                    label.textContent = detail.label;
                    value.className = 'source-identity-value small fw-medium';
                    value.textContent = detail.value;

                    item.appendChild(label);
                    item.appendChild(value);
                    grid.appendChild(item);
                });

            panel.appendChild(title);
            panel.appendChild(meta);

            if (grid.children.length) {
                panel.appendChild(grid);
            }

            panel.classList.remove('d-none');
        };

        const initCombobox = function (root) {
            const type = root.dataset.sourceCombobox;
            const hiddenInput = root.querySelector('input[type="hidden"]');
            const searchInput = root.querySelector('[data-combobox-input]');
            const clearButton = root.querySelector('[data-combobox-clear]');
            const menu = root.querySelector('[data-combobox-menu]');
            const options = sourceData[type] || [];

            const closeMenu = function () {
                menu.classList.add('d-none');
            };

            const renderOptions = function () {
                const term = searchInput.value.toLowerCase();
                const filteredOptions = options.filter(function (option) {
                    return option.label.toLowerCase().includes(term)
                        || option.code.toLowerCase().includes(term)
                        || option.name.toLowerCase().includes(term);
                }).slice(0, 30);

                menu.innerHTML = '';

                if (!filteredOptions.length) {
                    const empty = document.createElement('div');
                    const createLink = document.createElement('a');

                    empty.className = 'text-muted small px-2 py-2';
                    empty.textContent = type === 'supplier' ? 'Supplier tidak ditemukan' : 'Petani tidak ditemukan';
                    menu.appendChild(empty);

                    createLink.href = sourceCreateUrls[type];
                    createLink.className = 'btn btn-sm btn-primary w-100 mt-1';
                    createLink.innerHTML = type === 'supplier'
                        ? '<i class="bx bx-plus me-1"></i> Tambah Supplier'
                        : '<i class="bx bx-plus me-1"></i> Tambah Petani';
                    menu.appendChild(createLink);

                    menu.classList.remove('d-none');
                    return;
                }

                filteredOptions.forEach(function (option) {
                    const button = document.createElement('button');
                    const code = document.createElement('span');
                    const name = document.createElement('span');

                    button.type = 'button';
                    button.className = 'source-combobox-option';
                    code.className = 'fw-medium';
                    code.textContent = option.code;
                    name.className = 'text-muted';
                    name.textContent = ` - ${option.name}`;

                    button.appendChild(code);
                    button.appendChild(name);

                    button.addEventListener('click', function () {
                        hiddenInput.value = option.value;
                        searchInput.value = option.label;
                        renderSourceIdentity(type, option.value);
                        closeMenu();
                    });

                    menu.appendChild(button);
                });

                menu.classList.remove('d-none');
            };

            searchInput.addEventListener('focus', renderOptions);
            searchInput.addEventListener('input', function () {
                hiddenInput.value = '';
                renderSourceIdentity(type, '');
                renderOptions();
            });
            searchInput.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeMenu();
                }
            });
            clearButton.addEventListener('click', function () {
                hiddenInput.value = '';
                searchInput.value = '';
                renderSourceIdentity(type, '');
                searchInput.focus();
                renderOptions();
            });
            document.addEventListener('click', function (event) {
                if (!root.contains(event.target)) {
                    closeMenu();
                }
            });

            renderSourceIdentity(type, hiddenInput.value);
        };

        document.querySelectorAll('[data-source-combobox]').forEach(initCombobox);

        const initFreeCombobox = function (root) {
            const name = root.dataset.freeCombobox;
            const input = root.querySelector('[data-combobox-input]');
            const clearButton = root.querySelector('[data-combobox-clear]');
            const menu = root.querySelector('[data-combobox-menu]');
            const options = freeComboboxData[name] || [];

            const closeMenu = function () {
                menu.classList.add('d-none');
            };

            const normalize = function (value) {
                return (value || '').toString().toLowerCase();
            };

            const renderOptions = function () {
                const term = normalize(input.value);
                const filteredOptions = options.filter(function (option) {
                    return normalize(option.label).includes(term);
                }).slice(0, 20);

                menu.innerHTML = '';

                if (!filteredOptions.length) {
                    const empty = document.createElement('div');
                    empty.className = 'text-muted small px-2 py-2';
                    empty.textContent = term ? 'Belum ada riwayat yang cocok' : 'Belum ada riwayat';
                    menu.appendChild(empty);
                    menu.classList.remove('d-none');
                    return;
                }

                filteredOptions.forEach(function (option) {
                    const button = document.createElement('button');

                    button.type = 'button';
                    button.className = 'source-combobox-option';
                    button.textContent = option.label;
                    button.addEventListener('click', function () {
                        input.value = option.value;
                        closeMenu();
                    });

                    menu.appendChild(button);
                });

                menu.classList.remove('d-none');
            };

            input.addEventListener('focus', renderOptions);
            input.addEventListener('input', function () {
                if (input.dataset.uppercase !== undefined) {
                    input.value = input.value.toUpperCase();
                }

                renderOptions();
            });
            input.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeMenu();
                }
            });
            clearButton.addEventListener('click', function () {
                input.value = '';
                input.focus();
                renderOptions();
            });
            document.addEventListener('click', function (event) {
                if (!root.contains(event.target)) {
                    closeMenu();
                }
            });
        };

        document.querySelectorAll('[data-free-combobox]').forEach(initFreeCombobox);

        const syncSourceFields = function () {
            const isSupplier = sourceType?.value === 'supplier';

            supplierField?.classList.toggle('d-none', !isSupplier);
            farmerField?.classList.toggle('d-none', isSupplier);

            if (supplierInput) {
                supplierInput.disabled = !isSupplier;
                supplierField?.querySelector('[data-combobox-input]')?.toggleAttribute('disabled', !isSupplier);
                supplierField?.querySelector('[data-combobox-clear]')?.toggleAttribute('disabled', !isSupplier);
            }

            if (farmerInput) {
                farmerInput.disabled = isSupplier;
                farmerField?.querySelector('[data-combobox-input]')?.toggleAttribute('disabled', isSupplier);
                farmerField?.querySelector('[data-combobox-clear]')?.toggleAttribute('disabled', isSupplier);
            }
        };

        const formatNumber = function (value) {
            const numericValue = value.replace(/\D/g, '');

            if (!numericValue) {
                return '';
            }

            return numericValue.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        };

        const optionHtml = function (options, placeholder) {
            return [
                `<option value="">${placeholder}</option>`,
                ...options.map(function (option) {
                    return `<option value="${option.value}">${option.label}</option>`;
                })
            ].join('');
        };

        const itemByValue = function (value) {
            return itemOptions.find(function (option) {
                return option.value === value;
            });
        };

        const updateRowUnit = function (row, item) {
            const quantityUnit = row.querySelector('[data-unit-label]');
            const priceUnit = row.querySelector('[data-unit-price-label]');

            if (!quantityUnit || !priceUnit) {
                return;
            }

            if (!item) {
                quantityUnit.textContent = 'Pilih barang untuk melihat satuan.';
                priceUnit.textContent = 'Harga per satuan barang.';
                return;
            }

            quantityUnit.textContent = `Satuan: ${item.unit}`;
            priceUnit.textContent = `Per ${item.unit}`;
        };

        const initItemCombobox = function (root) {
            if (root.dataset.initialized === 'true') {
                return;
            }

            root.dataset.initialized = 'true';

            const row = root.closest('[data-purchase-item-row]');
            const hiddenInput = root.querySelector('input[type="hidden"]');
            const searchInput = root.querySelector('[data-combobox-input]');
            const clearButton = root.querySelector('[data-combobox-clear]');
            const menu = root.querySelector('[data-combobox-menu]');

            const closeMenu = function () {
                menu.classList.add('d-none');
            };

            const normalize = function (value) {
                return (value || '').toString().toLowerCase();
            };

            const renderOptions = function () {
                const term = normalize(searchInput.value);
                const filteredOptions = itemOptions.filter(function (option) {
                    return normalize(option.label).includes(term)
                        || normalize(option.code).includes(term)
                        || normalize(option.name).includes(term);
                }).slice(0, 30);

                menu.innerHTML = '';

                if (!filteredOptions.length) {
                    const empty = document.createElement('div');
                    const createLink = document.createElement('a');

                    empty.className = 'text-muted small px-2 py-2';
                    empty.textContent = 'Barang tidak ditemukan';
                    menu.appendChild(empty);

                    createLink.href = itemCreateUrl;
                    createLink.className = 'btn btn-sm btn-primary w-100 mt-1';
                    createLink.innerHTML = '<i class="bx bx-plus me-1"></i> Tambah Barang';
                    menu.appendChild(createLink);

                    menu.classList.remove('d-none');
                    return;
                }

                filteredOptions.forEach(function (option) {
                    const button = document.createElement('button');
                    const title = document.createElement('div');
                    const meta = document.createElement('div');

                    button.type = 'button';
                    button.className = 'source-combobox-option';
                    title.className = 'fw-medium';
                    title.textContent = `${option.code} - ${option.name}`;
                    meta.className = 'text-muted small';
                    meta.textContent = option.label.replace(`${option.code} - ${option.name} `, '');

                    button.appendChild(title);
                    button.appendChild(meta);

                    button.addEventListener('click', function () {
                        hiddenInput.value = option.value;
                        searchInput.value = option.label;
                        updateRowUnit(row, option);
                        closeMenu();
                    });

                    menu.appendChild(button);
                });

                menu.classList.remove('d-none');
            };

            searchInput.addEventListener('focus', renderOptions);
            searchInput.addEventListener('input', function () {
                hiddenInput.value = '';
                updateRowUnit(row, null);
                renderOptions();
            });
            searchInput.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeMenu();
                }
            });
            clearButton.addEventListener('click', function () {
                hiddenInput.value = '';
                searchInput.value = '';
                updateRowUnit(row, null);
                searchInput.focus();
                renderOptions();
            });
            document.addEventListener('click', function (event) {
                if (!root.contains(event.target)) {
                    closeMenu();
                }
            });

            updateRowUnit(row, itemByValue(hiddenInput.value));
        };

        const bindMoneyInput = function (input) {
            input.value = formatNumber(input.value);

            input.addEventListener('input', function () {
                input.value = formatNumber(input.value);
            });
        };

        const syncRows = function () {
            tableBody.querySelectorAll('[data-purchase-item-row]').forEach(function (row, index) {
                row.querySelectorAll('[data-field]').forEach(function (field) {
                    field.name = `items[${index}][${field.dataset.field}]`;
                });
            });

            tableBody.querySelectorAll('[data-remove-row]').forEach(function (button) {
                button.disabled = tableBody.querySelectorAll('[data-purchase-item-row]').length === 1;
            });
        };

        const createRow = function () {
            const row = document.createElement('tr');
            row.dataset.purchaseItemRow = '';
            row.innerHTML = `
                <td>
                    <div class="source-combobox position-relative" data-item-combobox>
                        <input type="hidden" data-field="item_id">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="search" class="form-control" placeholder="Cari barang" autocomplete="off" data-combobox-input>
                            <button type="button" class="btn btn-outline-secondary" data-combobox-clear title="Bersihkan pilihan">
                                <i class="bx bx-x"></i>
                            </button>
                        </div>
                        <div class="source-combobox-menu d-none" data-combobox-menu></div>
                    </div>
                </td>
                <td>
                    <select class="form-select" data-field="warehouse_id" required>
                        ${optionHtml(warehouseOptions, 'Pilih gudang')}
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control" data-field="quantity" min="0.01" max="9999999999.99" step="0.01" required>
                    <div class="form-text" data-unit-label>Pilih barang untuk melihat satuan.</div>
                </td>
                <td>
                    <input type="text" inputmode="numeric" class="form-control" data-field="unit_price" data-money-input required>
                    <div class="form-text" data-unit-price-label>Harga per satuan barang.</div>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-icon btn-label-danger" data-remove-row title="Hapus baris">
                        <i class="bx bx-trash"></i>
                    </button>
                </td>
            `;

            bindMoneyInput(row.querySelector('[data-money-input]'));
            initItemCombobox(row.querySelector('[data-item-combobox]'));

            return row;
        };

        document.querySelectorAll('[data-item-combobox]').forEach(initItemCombobox);
        document.querySelectorAll('[data-money-input]').forEach(bindMoneyInput);
        sourceType?.addEventListener('change', syncSourceFields);

        document.getElementById('add-purchase-item-row')?.addEventListener('click', function () {
            tableBody.appendChild(createRow());
            syncRows();
        });

        tableBody.addEventListener('click', function (event) {
            const removeButton = event.target.closest('[data-remove-row]');

            if (!removeButton || tableBody.querySelectorAll('[data-purchase-item-row]').length === 1) {
                return;
            }

            removeButton.closest('[data-purchase-item-row]').remove();
            syncRows();
        });

        document.querySelector('form[action="{{ route('purchases.store') }}"]')?.addEventListener('submit', function () {
            document.querySelectorAll('[data-money-input]').forEach(function (input) {
                input.value = input.value.replace(/\./g, '');
            });
        });

        syncSourceFields();
        syncRows();
    });
</script>
@endsection
