@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
        <div>
            <h4 class="mb-1">Tambah Penjualan Barang</h4>
            <p class="text-muted mb-0">Barang/benih yang dijual akan langsung mengurangi saldo stok gudang asal.</p>
        </div>
        <a href="{{ route('sales.index') }}" class="btn btn-label-secondary">
            <i class="icon-base bx bx-arrow-back me-1"></i> Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($availableStocks->isEmpty())
        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="icon-base bx bx-error-circle me-2"></i>
            <div>
                Belum ada stok barang dengan saldo tersedia (&gt; 0) di gudang. Silakan catat pembelian atau hasil produksi terlebih dahulu.
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center border-bottom">
            <h5 class="mb-0">Form Penjualan / Penyaluran Barang</h5>
            <small class="text-body-secondary float-end">Persediaan</small>
        </div>
        <div class="card-body pt-4">
            <form method="POST" action="{{ route('sales.store') }}">
                @csrf

                @php
                    $selectedCustomerId = old('customer_id', request('customer_id'));
                    $selectedCustomer = $customers->firstWhere('id', $selectedCustomerId);
                    $selectedPaymentStatus = old('payment_status', 'lunas');

                    $customerOptions = $customers->map(function ($customer) use ($customerTypes) {
                        $typeLabel = $customerTypes[$customer->customer_type] ?? ucfirst((string) $customer->customer_type);

                        return [
                            'value' => $customer->id,
                            'code' => $customer->code,
                            'name' => $customer->name,
                            'type_label' => $typeLabel,
                            'label' => "{$customer->code} - {$customer->name} ({$typeLabel})",
                            'details' => [
                                ['label' => 'Jenis', 'value' => $typeLabel],
                                ['label' => 'Kontak', 'value' => $customer->contact_person],
                                ['label' => 'Telepon', 'value' => $customer->phone],
                                ['label' => 'Alamat', 'value' => $customer->address],
                            ],
                        ];
                    })->values();

                    $transportTypeOptions = $transportTypes->map(fn ($transportType) => [
                        'value' => $transportType,
                        'label' => $transportType,
                    ])->values();

                    $vehiclePlateOptions = $vehiclePlateNumbers->map(fn ($vehiclePlateNumber) => [
                        'value' => $vehiclePlateNumber,
                        'label' => $vehiclePlateNumber,
                    ])->values();

                    $stockOptionData = $availableStocks->map(function ($stock) use ($itemCategories, $itemMaterialStates) {
                        $item = $stock->item;
                        $warehouse = $stock->warehouse;
                        $categoryLabel = $itemCategories[$item->category] ?? ucfirst(str_replace('_', ' ', (string) $item->category));
                        $stateLabel = $itemMaterialStates[$item->material_state ?? 'none'] ?? ucfirst(str_replace('_', ' ', (string) ($item->material_state ?? 'none')));
                        $itemTypeLabel = ($item->material_state ?? 'none') === 'none' ? $categoryLabel : "{$categoryLabel} - {$stateLabel}";
                        $unit = strtoupper((string) $item->unit);
                        $qtyFormatted = number_format((float) $stock->quantity, 2, ',', '.');
                        $lotLabel = $stock->lot_number ?: 'Tanpa Lot';

                        return [
                            'value' => $stock->id,
                            'item_code' => $item->code,
                            'item_name' => $item->name,
                            'item_type' => $itemTypeLabel,
                            'warehouse_name' => $warehouse?->name ?? '-',
                            'warehouse_code' => $warehouse?->code ?? '-',
                            'lot_number' => $stock->lot_number,
                            'lot_label' => $lotLabel,
                            'quantity' => (float) $stock->quantity,
                            'quantity_formatted' => $qtyFormatted,
                            'unit' => $unit,
                            'label' => sprintf(
                                '%s - %s | %s (%s) — Stok: %s %s',
                                $item->code,
                                $item->name,
                                $warehouse?->name ?? '-',
                                $lotLabel,
                                $qtyFormatted,
                                $unit
                            ),
                        ];
                    })->values();
                @endphp

                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <label for="number" class="form-label">Nomor Transaksi</label>
                        <input
                            type="text"
                            class="form-control @error('number') is-invalid @enderror"
                            id="number"
                            name="number"
                            value="{{ old('number', $nextNumber) }}"
                            maxlength="100"
                            placeholder="Kosongkan untuk otomatis"
                        >
                        <div class="form-text">Format otomatis <code>SO-YYYYMMDD-XXXX</code>, dapat disesuaikan.</div>
                        @error('number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="sale_date" class="form-label">Tanggal Penjualan <span class="text-danger">*</span></label>
                        <input
                            type="date"
                            class="form-control @error('sale_date') is-invalid @enderror"
                            id="sale_date"
                            name="sale_date"
                            value="{{ old('sale_date', now()->toDateString()) }}"
                            required
                        >
                        @error('sale_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="payment_status" class="form-label">Status Pembayaran <span class="text-danger">*</span></label>
                        <select
                            id="payment_status"
                            name="payment_status"
                            class="form-select @error('payment_status') is-invalid @enderror"
                            required
                        >
                            @foreach($paymentStatuses as $value => $label)
                                <option value="{{ $value }}" @selected($selectedPaymentStatus === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('payment_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="customer_search" class="form-label">Pelanggan / Tujuan Penyaluran <span class="text-danger">*</span></label>
                        @php
                            $selectedCustomerLabel = '';
                            if ($selectedCustomer) {
                                $selectedCustType = $customerTypes[$selectedCustomer->customer_type] ?? ucfirst((string) $selectedCustomer->customer_type);
                                $selectedCustomerLabel = "{$selectedCustomer->code} - {$selectedCustomer->name} ({$selectedCustType})";
                            }
                        @endphp
                        <div class="source-combobox position-relative" data-customer-combobox>
                            <input type="hidden" id="customer_id" name="customer_id" value="{{ $selectedCustomerId }}">
                            <div class="input-group">
                                <span class="input-group-text"><i class="icon-base bx bx-search"></i></span>
                                <input
                                    type="search"
                                    id="customer_search"
                                    class="form-control @error('customer_id') is-invalid @enderror"
                                    value="{{ $selectedCustomerLabel }}"
                                    placeholder="Cari pelanggan (kode, nama, kios/distributor)"
                                    autocomplete="off"
                                    data-combobox-input
                                >
                                <button type="button" class="btn btn-outline-secondary" data-combobox-clear title="Bersihkan pilihan">
                                    <i class="icon-base bx bx-x"></i>
                                </button>
                            </div>
                            <div class="source-combobox-menu d-none" data-combobox-menu></div>
                        </div>
                        <div class="source-identity d-none" id="customer-identity-panel"></div>
                        @error('customer_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="transport_type" class="form-label">Jenis Angkutan</label>
                        <div class="source-combobox position-relative" data-free-combobox="transport_type">
                            <div class="input-group">
                                <span class="input-group-text"><i class="icon-base bx bxs-truck"></i></span>
                                <input
                                    type="search"
                                    class="form-control @error('transport_type') is-invalid @enderror"
                                    id="transport_type"
                                    name="transport_type"
                                    value="{{ old('transport_type') }}"
                                    maxlength="100"
                                    placeholder="Contoh: Pick Up / Truk"
                                    autocomplete="off"
                                    data-combobox-input
                                >
                                <button type="button" class="btn btn-outline-secondary" data-combobox-clear title="Bersihkan isian">
                                    <i class="icon-base bx bx-x"></i>
                                </button>
                            </div>
                            <div class="source-combobox-menu d-none" data-combobox-menu></div>
                        </div>
                        @error('transport_type')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="vehicle_plate_number" class="form-label">No Pol Kendaraan</label>
                        <div class="source-combobox position-relative" data-free-combobox="vehicle_plate_number">
                            <div class="input-group">
                                <span class="input-group-text"><i class="icon-base bx bx-id-card"></i></span>
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
                                    <i class="icon-base bx bx-x"></i>
                                </button>
                            </div>
                            <div class="source-combobox-menu d-none" data-combobox-menu></div>
                        </div>
                        @error('vehicle_plate_number')
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
                            placeholder="Catatan penjualan atau pengiriman"
                        >{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                @php
                    $oldItems = old('items', [
                        ['stock_id' => request('stock_id', ''), 'quantity' => '', 'unit_price' => 0],
                    ]);
                @endphp

                <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                    <h6 class="mb-0">Rincian Barang yang Dijual</h6>
                    <button type="button" class="btn btn-label-primary btn-sm" id="add-sale-item-row">
                        <i class="icon-base bx bx-plus me-1"></i> Tambah Baris
                    </button>
                </div>

                @error('items')
                    <div class="alert alert-danger" role="alert">{{ $message }}</div>
                @enderror

                <div class="table-responsive sale-items-table-wrap mb-4">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th style="min-width: 300px;">Stok Barang (Gudang & Lot)</th>
                                <th style="min-width: 190px;">Info Gudang & Lot</th>
                                <th style="min-width: 140px;">Jumlah</th>
                                <th style="min-width: 160px;">Harga Satuan</th>
                                <th style="min-width: 150px;" class="text-end">Subtotal</th>
                                <th style="width: 72px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="sale-items-body">
                            @foreach($oldItems as $index => $oldItem)
                                @php
                                    $selectedStockOption = $stockOptionData->firstWhere('value', $oldItem['stock_id'] ?? '');
                                    $selectedStockLabel = $selectedStockOption['label'] ?? '';
                                    $selectedStockUnit = $selectedStockOption['unit'] ?? '';
                                    $selectedStockMax = $selectedStockOption['quantity'] ?? 9999999999.99;
                                @endphp
                                <tr data-sale-item-row>
                                    <td>
                                        <div class="source-combobox position-relative" data-stock-combobox>
                                            <input type="hidden" data-field="stock_id" name="items[{{ $index }}][stock_id]" value="{{ $oldItem['stock_id'] ?? '' }}">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="icon-base bx bx-search"></i></span>
                                                <input
                                                    type="search"
                                                    class="form-control @error("items.$index.stock_id") is-invalid @enderror"
                                                    value="{{ $selectedStockLabel }}"
                                                    placeholder="Cari barang, gudang, atau nomor lot"
                                                    autocomplete="off"
                                                    data-combobox-input
                                                >
                                                <button type="button" class="btn btn-outline-secondary" data-combobox-clear title="Bersihkan pilihan">
                                                    <i class="icon-base bx bx-x"></i>
                                                </button>
                                            </div>
                                            <div class="source-combobox-menu d-none" data-combobox-menu></div>
                                        </div>
                                        @error("items.$index.stock_id")
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td data-stock-meta>
                                        @if($selectedStockOption)
                                            <div class="fw-medium">{{ $selectedStockOption['warehouse_name'] }}</div>
                                            <div class="text-muted small">Lot: {{ $selectedStockOption['lot_label'] }}</div>
                                            <span class="badge bg-label-info mt-1">Tersedia: {{ $selectedStockOption['quantity_formatted'] }} {{ $selectedStockOption['unit'] }}</span>
                                        @else
                                            <span class="text-muted small">Pilih stok barang terlebih dahulu</span>
                                        @endif
                                    </td>
                                    <td>
                                        <input
                                            type="number"
                                            data-field="quantity"
                                            class="form-control @error("items.$index.quantity") is-invalid @enderror"
                                            name="items[{{ $index }}][quantity]"
                                            value="{{ $oldItem['quantity'] ?? '' }}"
                                            min="0.01"
                                            max="{{ $selectedStockMax }}"
                                            step="0.01"
                                            required
                                        >
                                        <div class="form-text" data-unit-label>
                                            {{ $selectedStockOption ? "Maks: {$selectedStockOption['quantity_formatted']} {$selectedStockUnit}" : 'Pilih stok untuk melihat batas saldo.' }}
                                        </div>
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
                                            value="{{ ($oldItem['unit_price'] ?? '') !== '' ? number_format((float) str_replace('.', '', (string) $oldItem['unit_price']), 0, ',', '.') : '' }}"
                                            data-money-input
                                            required
                                        >
                                        <div class="form-text" data-unit-price-label>{{ $selectedStockUnit ? "Per {$selectedStockUnit}" : 'Harga jual per satuan.' }}</div>
                                        @error("items.$index.unit_price")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td class="text-end fw-medium text-nowrap" data-row-subtotal>Rp 0</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-icon btn-label-danger" data-remove-row title="Hapus baris">
                                            <i class="icon-base bx bx-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-end">Total Penjualan</th>
                                <th class="text-end fw-bold text-nowrap" id="sale-grand-total">Rp 0</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
                    <a href="{{ route('sales.index') }}" class="btn btn-label-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Penjualan</button>
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

    @media (min-width: 768px) {
        .sale-items-table-wrap {
            overflow: visible;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const customerOptions = @json($customerOptions);
        const stockOptions = @json($stockOptionData);
        const customerManageUrl = @json(route('master.customers.index'));
        const stockManageUrl = @json(route('stocks.index'));
        const freeComboboxData = {
            transport_type: @json($transportTypeOptions),
            vehicle_plate_number: @json($vehiclePlateOptions),
        };
        const tableBody = document.getElementById('sale-items-body');
        const customerIdentityPanel = document.getElementById('customer-identity-panel');

        const renderCustomerIdentity = function (value) {
            const option = customerOptions.find(function (item) {
                return item.value === value;
            });

            if (!customerIdentityPanel) {
                return;
            }

            customerIdentityPanel.innerHTML = '';

            if (!option) {
                customerIdentityPanel.classList.add('d-none');
                return;
            }

            const title = document.createElement('div');
            const meta = document.createElement('div');
            const grid = document.createElement('div');

            title.className = 'fw-medium mb-1';
            title.textContent = option.name;
            meta.className = 'text-muted small mb-2';
            meta.textContent = `${option.code} (${option.type_label})`;
            grid.className = 'source-identity-grid';

            option.details
                .filter(function (detail) {
                    return detail.value;
                })
                .forEach(function (detail) {
                    const item = document.createElement('div');
                    const label = document.createElement('div');
                    const val = document.createElement('div');

                    label.className = 'text-muted small';
                    label.textContent = detail.label;
                    val.className = 'source-identity-value small fw-medium';
                    val.textContent = detail.value;

                    item.appendChild(label);
                    item.appendChild(val);
                    grid.appendChild(item);
                });

            customerIdentityPanel.appendChild(title);
            customerIdentityPanel.appendChild(meta);

            if (grid.children.length) {
                customerIdentityPanel.appendChild(grid);
            }

            customerIdentityPanel.classList.remove('d-none');
        };

        const initCustomerCombobox = function (root) {
            const hiddenInput = root.querySelector('input[type="hidden"]');
            const searchInput = root.querySelector('[data-combobox-input]');
            const clearButton = root.querySelector('[data-combobox-clear]');
            const menu = root.querySelector('[data-combobox-menu]');

            const closeMenu = function () {
                menu.classList.add('d-none');
            };

            const renderOptions = function () {
                const term = (searchInput.value || '').toLowerCase();
                const filteredOptions = customerOptions.filter(function (option) {
                    return option.label.toLowerCase().includes(term)
                        || option.code.toLowerCase().includes(term)
                        || option.name.toLowerCase().includes(term);
                }).slice(0, 30);

                menu.innerHTML = '';

                if (!filteredOptions.length) {
                    const empty = document.createElement('div');
                    const manageLink = document.createElement('a');

                    empty.className = 'text-muted small px-2 py-2';
                    empty.textContent = 'Pelanggan tidak ditemukan';
                    menu.appendChild(empty);

                    manageLink.href = customerManageUrl;
                    manageLink.className = 'btn btn-sm btn-primary w-100 mt-1';
                    manageLink.innerHTML = '<i class="bx bx-plus me-1"></i> Kelola Pelanggan';
                    menu.appendChild(manageLink);

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
                    name.textContent = ` - ${option.name} (${option.type_label})`;

                    button.appendChild(code);
                    button.appendChild(name);

                    button.addEventListener('click', function () {
                        hiddenInput.value = option.value;
                        searchInput.value = option.label;
                        renderCustomerIdentity(option.value);
                        closeMenu();
                    });

                    menu.appendChild(button);
                });

                menu.classList.remove('d-none');
            };

            searchInput.addEventListener('focus', renderOptions);
            searchInput.addEventListener('input', function () {
                hiddenInput.value = '';
                renderCustomerIdentity('');
                renderOptions();
            });
            searchInput.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                } else if (event.key === 'Escape') {
                    closeMenu();
                }
            });
            clearButton.addEventListener('click', function () {
                hiddenInput.value = '';
                searchInput.value = '';
                renderCustomerIdentity('');
                searchInput.focus();
                renderOptions();
            });
            document.addEventListener('click', function (event) {
                if (!root.contains(event.target)) {
                    closeMenu();
                }
            });

            renderCustomerIdentity(hiddenInput.value);
        };

        document.querySelectorAll('[data-customer-combobox]').forEach(initCustomerCombobox);

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
                if (event.key === 'Enter') {
                    event.preventDefault();
                } else if (event.key === 'Escape') {
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

        const formatNumber = function (value) {
            const numericValue = (value || '').toString().replace(/\D/g, '');

            if (!numericValue) {
                return '';
            }

            return numericValue.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        };

        const stockByValue = function (value) {
            return stockOptions.find(function (option) {
                return option.value === value;
            });
        };

        const updateRowStockMeta = function (row, stock) {
            const metaCell = row.querySelector('[data-stock-meta]');
            const quantityInput = row.querySelector('[data-field="quantity"]');
            const quantityUnit = row.querySelector('[data-unit-label]');
            const priceUnit = row.querySelector('[data-unit-price-label]');

            if (!metaCell || !quantityUnit || !priceUnit) {
                return;
            }

            if (!stock) {
                metaCell.innerHTML = '<span class="text-muted small">Pilih stok barang terlebih dahulu</span>';
                quantityUnit.textContent = 'Pilih stok untuk melihat batas saldo.';
                priceUnit.textContent = 'Harga jual per satuan.';
                if (quantityInput) {
                    quantityInput.max = '9999999999.99';
                }
                return;
            }

            metaCell.innerHTML = `
                <div class="fw-medium">${stock.warehouse_name}</div>
                <div class="text-muted small">Lot: ${stock.lot_label}</div>
                <span class="badge bg-label-info mt-1">Tersedia: ${stock.quantity_formatted} ${stock.unit}</span>
            `;
            quantityUnit.textContent = `Maks: ${stock.quantity_formatted} ${stock.unit}`;
            priceUnit.textContent = `Per ${stock.unit}`;
            if (quantityInput) {
                quantityInput.max = String(stock.quantity);
            }
        };

        const initStockCombobox = function (root) {
            if (root.dataset.initialized === 'true') {
                return;
            }

            root.dataset.initialized = 'true';

            const row = root.closest('[data-sale-item-row]');
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
                const filteredOptions = stockOptions.filter(function (option) {
                    return normalize(option.label).includes(term)
                        || normalize(option.item_code).includes(term)
                        || normalize(option.item_name).includes(term)
                        || normalize(option.warehouse_name).includes(term)
                        || normalize(option.lot_label).includes(term);
                }).slice(0, 30);

                menu.innerHTML = '';

                if (!filteredOptions.length) {
                    const empty = document.createElement('div');
                    const manageLink = document.createElement('a');

                    empty.className = 'text-muted small px-2 py-2';
                    empty.textContent = 'Stok barang tidak ditemukan';
                    menu.appendChild(empty);

                    manageLink.href = stockManageUrl;
                    manageLink.className = 'btn btn-sm btn-primary w-100 mt-1';
                    manageLink.innerHTML = '<i class="bx bx-layer me-1"></i> Lihat Daftar Stok';
                    menu.appendChild(manageLink);

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
                    title.textContent = `${option.item_code} - ${option.item_name} [${option.item_type}]`;
                    meta.className = 'text-muted small';
                    meta.textContent = `${option.warehouse_name} | Lot: ${option.lot_label} | Tersedia: ${option.quantity_formatted} ${option.unit}`;

                    button.appendChild(title);
                    button.appendChild(meta);

                    button.addEventListener('click', function () {
                        hiddenInput.value = option.value;
                        searchInput.value = option.label;
                        updateRowStockMeta(row, option);
                        closeMenu();
                    });

                    menu.appendChild(button);
                });

                menu.classList.remove('d-none');
            };

            searchInput.addEventListener('focus', renderOptions);
            searchInput.addEventListener('input', function () {
                hiddenInput.value = '';
                updateRowStockMeta(row, null);
                renderOptions();
            });
            searchInput.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                } else if (event.key === 'Escape') {
                    closeMenu();
                }
            });
            clearButton.addEventListener('click', function () {
                hiddenInput.value = '';
                searchInput.value = '';
                updateRowStockMeta(row, null);
                searchInput.focus();
                renderOptions();
            });
            document.addEventListener('click', function (event) {
                if (!root.contains(event.target)) {
                    closeMenu();
                }
            });

            updateRowStockMeta(row, stockByValue(hiddenInput.value));
        };

        const formatCurrency = function (amount) {
            return 'Rp ' + Number(amount || 0).toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2,
            });
        };

        const updateTotals = function () {
            let grandTotal = 0;

            tableBody.querySelectorAll('[data-sale-item-row]').forEach(function (row) {
                const qty = parseFloat(row.querySelector('[data-field="quantity"]')?.value || '0') || 0;
                const priceRaw = (row.querySelector('[data-field="unit_price"]')?.value || '').replace(/\./g, '');
                const price = parseFloat(priceRaw) || 0;
                const subtotal = qty * price;
                grandTotal += subtotal;

                const subtotalEl = row.querySelector('[data-row-subtotal]');
                if (subtotalEl) {
                    subtotalEl.textContent = formatCurrency(subtotal);
                }
            });

            const grandTotalEl = document.getElementById('sale-grand-total');
            if (grandTotalEl) {
                grandTotalEl.textContent = formatCurrency(grandTotal);
            }
        };

        const bindMoneyInput = function (input) {
            input.value = formatNumber(input.value);

            input.addEventListener('input', function () {
                input.value = formatNumber(input.value);
                updateTotals();
            });
        };

        const syncRows = function () {
            tableBody.querySelectorAll('[data-sale-item-row]').forEach(function (row, index) {
                row.querySelectorAll('[data-field]').forEach(function (field) {
                    field.name = `items[${index}][${field.dataset.field}]`;
                });
            });

            tableBody.querySelectorAll('[data-remove-row]').forEach(function (button) {
                button.disabled = tableBody.querySelectorAll('[data-sale-item-row]').length === 1;
            });

            updateTotals();
        };

        const createRow = function () {
            const row = document.createElement('tr');
            row.dataset.saleItemRow = '';
            row.innerHTML = `
                <td>
                    <div class="source-combobox position-relative" data-stock-combobox>
                        <input type="hidden" data-field="stock_id">
                        <div class="input-group">
                            <span class="input-group-text"><i class="icon-base bx bx-search"></i></span>
                            <input type="search" class="form-control" placeholder="Cari barang, gudang, atau nomor lot" autocomplete="off" data-combobox-input>
                            <button type="button" class="btn btn-outline-secondary" data-combobox-clear title="Bersihkan pilihan">
                                <i class="icon-base bx bx-x"></i>
                            </button>
                        </div>
                        <div class="source-combobox-menu d-none" data-combobox-menu></div>
                    </div>
                </td>
                <td data-stock-meta>
                    <span class="text-muted small">Pilih stok barang terlebih dahulu</span>
                </td>
                <td>
                    <input type="number" class="form-control" data-field="quantity" min="0.01" max="9999999999.99" step="0.01" required>
                    <div class="form-text" data-unit-label>Pilih stok untuk melihat batas saldo.</div>
                </td>
                <td>
                    <input type="text" inputmode="numeric" class="form-control" data-field="unit_price" data-money-input required>
                    <div class="form-text" data-unit-price-label>Harga jual per satuan.</div>
                </td>
                <td class="text-end fw-medium text-nowrap" data-row-subtotal>Rp 0</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-icon btn-label-danger" data-remove-row title="Hapus baris">
                        <i class="icon-base bx bx-trash"></i>
                    </button>
                </td>
            `;

            bindMoneyInput(row.querySelector('[data-money-input]'));
            initStockCombobox(row.querySelector('[data-stock-combobox]'));

            return row;
        };

        tableBody.addEventListener('input', function (event) {
            if (event.target.matches('[data-field="quantity"]')) {
                updateTotals();
            }
        });

        document.querySelectorAll('[data-stock-combobox]').forEach(initStockCombobox);
        document.querySelectorAll('[data-money-input]').forEach(bindMoneyInput);

        document.getElementById('add-sale-item-row')?.addEventListener('click', function () {
            tableBody.appendChild(createRow());
            syncRows();
        });

        tableBody.addEventListener('click', function (event) {
            const removeButton = event.target.closest('[data-remove-row]');

            if (!removeButton || tableBody.querySelectorAll('[data-sale-item-row]').length === 1) {
                return;
            }

            removeButton.closest('[data-sale-item-row]').remove();
            syncRows();
        });

        document.querySelector('form[action="{{ route('sales.store') }}"]')?.addEventListener('submit', function () {
            document.querySelectorAll('[data-money-input]').forEach(function (input) {
                input.value = input.value.replace(/\./g, '');
            });
        });

        syncRows();
    });
</script>
@endsection
