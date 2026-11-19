@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @php
        $customerName = $sale->customer?->name ?? '-';
        $customerCode = $sale->customer?->code ?? '-';
        $customerTypeLabel = $customerTypes[$sale->customer?->customer_type ?? ''] ?? ucfirst((string) ($sale->customer?->customer_type ?? '-'));
        $customerPhone = $sale->customer?->phone;
        $customerAddress = $sale->customer?->address;
        $customerContact = $sale->customer?->contact_person;
        $isPaid = $sale->payment_status === 'lunas';
        $paymentBadge = $isPaid ? 'bg-label-success' : 'bg-label-warning';
        $paymentLabel = $paymentStatuses[$sale->payment_status] ?? ucfirst(str_replace('_', ' ', (string) $sale->payment_status));
    @endphp

    <style>
        .print-document {
            display: none;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm;
            }

            html,
            body {
                background: #fff !important;
                color: #111 !important;
                font-size: 12px;
                line-height: 1.45;
            }

            #layout-navbar,
            #layout-menu,
            #template-customizer,
            .content-footer,
            .screen-document,
            .alert {
                display: none !important;
            }

            .layout-wrapper,
            .layout-container,
            .layout-page,
            .content-wrapper,
            .container-xxl,
            .container-p-y {
                display: block !important;
                margin: 0 !important;
                max-width: 100% !important;
                min-height: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }

            .print-document {
                display: block !important;
            }

            .print-document h3,
            .print-document h4,
            .print-document h5,
            .print-document p {
                color: #111 !important;
            }

            .print-header {
                align-items: flex-end;
                border-bottom: 2px solid #111;
                display: flex;
                justify-content: space-between;
                margin-bottom: 16px;
                padding-bottom: 10px;
            }

            .print-title {
                font-size: 16px;
                font-weight: 700;
                letter-spacing: 0.4px;
                text-transform: uppercase;
            }

            .print-meta {
                display: grid;
                gap: 24px;
                grid-template-columns: 1fr 1fr;
                margin-bottom: 16px;
            }

            .print-meta table {
                border-collapse: collapse;
                width: 100%;
            }

            .print-meta td {
                padding: 2px 0;
                vertical-align: top;
            }

            .print-meta td:first-child {
                font-weight: 600;
                padding-right: 8px;
                white-space: nowrap;
                width: 120px;
            }

            .print-meta td:nth-child(2) {
                padding-right: 6px;
                width: 10px;
            }

            .print-table {
                border-collapse: collapse;
                width: 100%;
            }

            .print-table tr {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .print-table th,
            .print-table td {
                border: 1px solid #111;
                padding: 6px 8px;
                vertical-align: top;
            }

            .print-table th {
                -webkit-print-color-adjust: exact;
                background: #f2f2f2 !important;
                font-weight: 700;
                print-color-adjust: exact;
                text-align: center;
            }

            .print-signatures {
                break-inside: avoid;
                display: grid;
                gap: 48px;
                grid-template-columns: repeat(2, 1fr);
                margin-top: 32px;
                page-break-inside: avoid;
                text-align: center;
            }

            .signature-box {
                align-items: center;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                min-height: 110px;
            }

            .signature-line {
                border-top: 1px solid #111;
                margin-top: auto;
                padding-top: 5px;
                width: 75%;
            }

            .text-end-print {
                text-align: right;
                white-space: nowrap;
            }
        }
    </style>

    <div class="print-document">
        <div class="print-header">
            <div>
                <h3 class="mb-1">{{ config('app.name') }}</h3>
                <p class="mb-0">Faktur & Surat Jalan Penjualan Barang</p>
            </div>
            <div class="text-end">
                <div class="print-title">Invoice Penjualan</div>
                <div>{{ $sale->number }}</div>
            </div>
        </div>

        <div class="print-meta">
            <table>
                <tr>
                    <td>Nomor</td>
                    <td>:</td>
                    <td>{{ $sale->number }}</td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td>:</td>
                    <td>{{ $sale->sale_date?->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td>Pelanggan</td>
                    <td>:</td>
                    <td>{{ $customerName }} ({{ $customerCode }} - {{ $customerTypeLabel }})</td>
                </tr>
                @if($customerContact)
                    <tr>
                        <td>Kontak</td>
                        <td>:</td>
                        <td>{{ $customerContact }}</td>
                    </tr>
                @endif
                <tr>
                    <td>Telepon</td>
                    <td>:</td>
                    <td>{{ $customerPhone ?: '-' }}</td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td>:</td>
                    <td>{{ $customerAddress ?: '-' }}</td>
                </tr>
            </table>

            <table>
                <tr>
                    <td>Status Bayar</td>
                    <td>:</td>
                    <td>{{ $paymentLabel }}</td>
                </tr>
                <tr>
                    <td>Jenis Angkutan</td>
                    <td>:</td>
                    <td>{{ $sale->transport_type ?: '-' }}</td>
                </tr>
                <tr>
                    <td>No Pol Kendaraan</td>
                    <td>:</td>
                    <td>{{ $sale->vehicle_plate_number ?: '-' }}</td>
                </tr>
                <tr>
                    <td>Dibuat Oleh</td>
                    <td>:</td>
                    <td>{{ $sale->creator?->name ?: '-' }}</td>
                </tr>
                <tr>
                    <td>Catatan</td>
                    <td>:</td>
                    <td>{{ $sale->notes ?: '-' }}</td>
                </tr>
            </table>
        </div>

        <table class="print-table">
            <thead>
                <tr>
                    <th style="width: 36px;">No</th>
                    <th>Barang</th>
                    <th>Gudang Asal</th>
                    <th>No Lot</th>
                    <th style="width: 110px;">Jumlah</th>
                    <th style="width: 125px;">Harga Satuan</th>
                    <th style="width: 135px;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $detail)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>
                            @php
                                $categoryLabel = $itemCategories[$detail->item->category] ?? ucfirst(str_replace('_', ' ', (string) $detail->item->category));
                                $stateLabel = $itemMaterialStates[$detail->item->material_state ?? 'none'] ?? ucfirst(str_replace('_', ' ', (string) ($detail->item->material_state ?? 'none')));
                                $itemLabel = ($detail->item->material_state ?? 'none') === 'none' ? $categoryLabel : "{$categoryLabel} - {$stateLabel}";
                            @endphp
                            <strong>{{ $detail->item->code }}</strong><br>
                            {{ $detail->item->name }}
                            <br><small>{{ $itemLabel }}</small>
                        </td>
                        <td>{{ $detail->warehouse?->name ?? '-' }}</td>
                        <td>{{ $detail->lot_number ?: '-' }}</td>
                        <td class="text-end-print">{{ number_format((float) $detail->quantity, 2, ',', '.') }} {{ strtoupper((string) $detail->item->unit) }}</td>
                        <td class="text-end-print">Rp {{ number_format((float) $detail->unit_price, 2, ',', '.') }}</td>
                        <td class="text-end-print">Rp {{ number_format((float) $detail->subtotal, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="6" class="text-end-print"><strong>Total Penjualan</strong></td>
                    <td class="text-end-print"><strong>Rp {{ number_format((float) $sale->total_amount, 2, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>

        <div class="print-signatures">
            <div class="signature-box">
                <div>Dibuat Oleh,</div>
                <div class="signature-line">{{ $sale->creator?->name ?: 'Petugas' }}</div>
            </div>
            <div class="signature-box">
                <div>Penerima / Pelanggan,</div>
                <div class="signature-line">{{ $customerName }}</div>
            </div>
        </div>
    </div>

    <div class="screen-document">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
            <div>
                <h4 class="mb-1">Detail Penjualan</h4>
                <p class="text-muted mb-0">{{ $sale->number }}</p>
            </div>
            <div class="d-flex flex-wrap gap-2 print-hidden">
                <a href="{{ route('sales.index') }}" class="btn btn-label-secondary">
                    <i class="icon-base bx bx-arrow-back me-1"></i> Kembali
                </a>
                <form method="POST" action="{{ route('sales.payment-status.update', $sale) }}" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="payment_status" value="{{ $isPaid ? 'belum_lunas' : 'lunas' }}">
                    @if($isPaid)
                        <button type="submit" class="btn btn-outline-warning">
                            <i class="icon-base bx bx-time-five me-1"></i> Tandai Belum Lunas
                        </button>
                    @else
                        <button type="submit" class="btn btn-success">
                            <i class="icon-base bx bx-check-circle me-1"></i> Tandai Lunas
                        </button>
                    @endif
                </form>
                <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                    <i class="icon-base bx bx-printer me-1"></i> Print
                </button>
                <a href="{{ route('sales.create') }}" class="btn btn-primary">
                    <i class="icon-base bx bx-plus me-1"></i> Tambah
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center border-bottom">
                <h5 class="mb-0">Informasi Penjualan</h5>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-label-primary">{{ $customerTypeLabel }}</span>
                    <span class="badge {{ $paymentBadge }}">{{ $paymentLabel }}</span>
                </div>
            </div>
            <div class="card-body pt-4">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Nomor</dt>
                    <dd class="col-sm-9"><span class="fw-medium">{{ $sale->number }}</span></dd>

                    <dt class="col-sm-3">Tanggal</dt>
                    <dd class="col-sm-9">{{ $sale->sale_date?->format('d M Y') }}</dd>

                    <dt class="col-sm-3">Pelanggan</dt>
                    <dd class="col-sm-9">
                        <span class="fw-medium">{{ $customerName }}</span>
                        <span class="text-muted">({{ $customerCode }} &middot; {{ $customerTypeLabel }})</span>
                        @if($customerPhone || $customerAddress)
                            <div class="text-muted small mt-1">
                                {{ $customerPhone ?: '-' }}
                                @if($customerAddress)
                                    &mdash; {{ $customerAddress }}
                                @endif
                            </div>
                        @endif
                    </dd>

                    <dt class="col-sm-3">Status Pembayaran</dt>
                    <dd class="col-sm-9">
                        <span class="badge {{ $paymentBadge }}">{{ $paymentLabel }}</span>
                    </dd>

                    <dt class="col-sm-3">Jenis Angkutan</dt>
                    <dd class="col-sm-9">{{ $sale->transport_type ?: '-' }}</dd>

                    <dt class="col-sm-3">No Pol Kendaraan</dt>
                    <dd class="col-sm-9">{{ $sale->vehicle_plate_number ?: '-' }}</dd>

                    <dt class="col-sm-3">Total Penjualan</dt>
                    <dd class="col-sm-9 fw-semibold">Rp {{ number_format((float) $sale->total_amount, 2, ',', '.') }}</dd>

                    <dt class="col-sm-3">Catatan</dt>
                    <dd class="col-sm-9">{{ $sale->notes ?: '-' }}</dd>

                    <dt class="col-sm-3">Dibuat Oleh</dt>
                    <dd class="col-sm-9">{{ $sale->creator?->name ?: '-' }}</dd>
                </dl>
            </div>
        </div>

        <div class="card">
            <div class="card-header border-bottom">
                <h5 class="mb-0">Rincian Barang</h5>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width: 48px;" class="text-center">No</th>
                            <th>Barang</th>
                            <th>Gudang Asal</th>
                            <th>No Lot</th>
                            <th class="text-end">Jumlah</th>
                            <th class="text-end">Harga Satuan</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $detail)
                            <tr>
                                <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                <td class="text-wrap" style="min-width: 240px;">
                                    @php
                                        $categoryLabel = $itemCategories[$detail->item->category] ?? ucfirst(str_replace('_', ' ', (string) $detail->item->category));
                                        $stateLabel = $itemMaterialStates[$detail->item->material_state ?? 'none'] ?? ucfirst(str_replace('_', ' ', (string) ($detail->item->material_state ?? 'none')));
                                        $itemLabel = ($detail->item->material_state ?? 'none') === 'none' ? $categoryLabel : "{$categoryLabel} - {$stateLabel}";
                                    @endphp
                                    <span class="fw-medium">{{ $detail->item->code }}</span>
                                    <div class="text-muted small">{{ $detail->item->name }}</div>
                                    <div class="text-muted small">{{ $itemLabel }}</div>
                                </td>
                                <td>{{ $detail->warehouse?->name ?? '-' }}</td>
                                <td>
                                    @if($detail->lot_number)
                                        <span class="badge bg-label-info">{{ $detail->lot_number }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">{{ number_format((float) $detail->quantity, 2, ',', '.') }} {{ strtoupper((string) $detail->item->unit) }}</td>
                                <td class="text-end">Rp {{ number_format((float) $detail->unit_price, 2, ',', '.') }}</td>
                                <td class="text-end fw-medium">Rp {{ number_format((float) $detail->subtotal, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-border-bottom-0">
                        <tr>
                            <th colspan="6" class="text-end">Total Penjualan</th>
                            <th class="text-end fw-bold">Rp {{ number_format((float) $sale->total_amount, 2, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
