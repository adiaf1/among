@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @php
        $sourceName = $purchase->supplier?->name ?? $purchase->farmer?->name ?? '-';
        $sourceLabel = $purchase->supplier ? 'Supplier' : ($purchase->farmer ? 'Petani' : '-');
        $sourcePhone = $purchase->supplier?->phone ?? $purchase->farmer?->phone;
        $sourceAddress = $purchase->supplier?->address ?? $purchase->farmer?->address;
        $sourceContact = $purchase->supplier?->contact_person;
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
                <p class="mb-0">Tanda Terima Pembelian Barang</p>
            </div>
            <div class="text-end">
                <div class="print-title">Invoice Pembelian</div>
                <div>{{ $purchase->number }}</div>
            </div>
        </div>

        <div class="print-meta">
            <table>
                <tr>
                    <td>Nomor</td>
                    <td>:</td>
                    <td>{{ $purchase->number }}</td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td>:</td>
                    <td>{{ $purchase->purchase_date?->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td>Asal Barang</td>
                    <td>:</td>
                    <td>{{ $sourceName }} ({{ $sourceLabel }})</td>
                </tr>
                @if($sourceContact)
                    <tr>
                        <td>Kontak</td>
                        <td>:</td>
                        <td>{{ $sourceContact }}</td>
                    </tr>
                @endif
                <tr>
                    <td>Telepon</td>
                    <td>:</td>
                    <td>{{ $sourcePhone ?: '-' }}</td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td>:</td>
                    <td>{{ $sourceAddress ?: '-' }}</td>
                </tr>
            </table>

            <table>
                <tr>
                    <td>Jenis Angkutan</td>
                    <td>:</td>
                    <td>{{ $purchase->transport_type ?: '-' }}</td>
                </tr>
                <tr>
                    <td>No Pol Kendaraan</td>
                    <td>:</td>
                    <td>{{ $purchase->vehicle_plate_number ?: '-' }}</td>
                </tr>
                <tr>
                    <td>Dibuat Oleh</td>
                    <td>:</td>
                    <td>{{ $purchase->creator?->name ?: '-' }}</td>
                </tr>
                <tr>
                    <td>Catatan</td>
                    <td>:</td>
                    <td>{{ $purchase->notes ?: '-' }}</td>
                </tr>
            </table>
        </div>

        <table class="print-table">
            <thead>
                <tr>
                    <th style="width: 40px;">No</th>
                    <th>Barang</th>
                    <th>Gudang Tujuan</th>
                    <th style="width: 110px;">Jumlah</th>
                    <th style="width: 130px;">Harga Satuan</th>
                    <th style="width: 140px;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->items as $detail)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>
                            @php
                                $categoryLabel = $itemCategories[$detail->item->category] ?? ucfirst(str_replace('_', ' ', $detail->item->category));
                                $stateLabel = $itemMaterialStates[$detail->item->material_state ?? 'none'] ?? ucfirst(str_replace('_', ' ', $detail->item->material_state ?? 'none'));
                                $itemLabel = ($detail->item->material_state ?? 'none') === 'none' ? $categoryLabel : "{$categoryLabel} - {$stateLabel}";
                            @endphp
                            <strong>{{ $detail->item->code }}</strong><br>
                            {{ $detail->item->name }}
                            <br><small>{{ $itemLabel }}</small>
                        </td>
                        <td>{{ $detail->warehouse->name }}</td>
                        <td class="text-end-print">{{ number_format((float) $detail->quantity, 2, ',', '.') }} {{ strtoupper($detail->item->unit) }}</td>
                        <td class="text-end-print">Rp {{ number_format((float) $detail->unit_price, 2, ',', '.') }}</td>
                        <td class="text-end-print">Rp {{ number_format((float) $detail->subtotal, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="5" class="text-end-print"><strong>Total</strong></td>
                    <td class="text-end-print"><strong>Rp {{ number_format((float) $purchase->total_amount, 2, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>

        <div class="print-signatures">
            <div class="signature-box">
                <div>Dibuat Oleh,</div>
                <div class="signature-line">{{ $purchase->creator?->name ?: 'Petugas' }}</div>
            </div>
            <div class="signature-box">
                <div>Penerima,</div>
                <div class="signature-line">{{ $sourceName }}</div>
            </div>
        </div>
    </div>

    <div class="screen-document">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
            <div>
                <h4 class="mb-1">Detail Pembelian</h4>
                <p class="text-muted mb-0">{{ $purchase->number }}</p>
            </div>
            <div class="d-flex gap-2 print-hidden">
                <a href="{{ route('purchases.index') }}" class="btn btn-label-secondary">
                    <i class="icon-base bx bx-arrow-back me-1"></i> Kembali
                </a>
                <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                    <i class="icon-base bx bx-printer me-1"></i> Print
                </button>
                <a href="{{ route('purchases.create') }}" class="btn btn-primary">
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
                <h5 class="mb-0">Informasi Pembelian</h5>
                <span class="badge bg-label-primary">{{ $sourceLabel }}</span>
            </div>
            <div class="card-body pt-4">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Nomor</dt>
                    <dd class="col-sm-9">{{ $purchase->number }}</dd>

                    <dt class="col-sm-3">Tanggal</dt>
                    <dd class="col-sm-9">{{ $purchase->purchase_date?->format('d M Y') }}</dd>

                    <dt class="col-sm-3">Asal Barang</dt>
                    <dd class="col-sm-9">
                        {{ $sourceName }}
                        <span class="text-muted">({{ $sourceLabel }})</span>
                    </dd>

                    <dt class="col-sm-3">Jenis Angkutan</dt>
                    <dd class="col-sm-9">{{ $purchase->transport_type ?: '-' }}</dd>

                    <dt class="col-sm-3">No Pol Kendaraan</dt>
                    <dd class="col-sm-9">{{ $purchase->vehicle_plate_number ?: '-' }}</dd>

                    <dt class="col-sm-3">Total</dt>
                    <dd class="col-sm-9">Rp {{ number_format((float) $purchase->total_amount, 2, ',', '.') }}</dd>

                    <dt class="col-sm-3">Catatan</dt>
                    <dd class="col-sm-9">{{ $purchase->notes ?: '-' }}</dd>

                    <dt class="col-sm-3">Dibuat Oleh</dt>
                    <dd class="col-sm-9">{{ $purchase->creator?->name ?: '-' }}</dd>
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
                            <th>Gudang Tujuan</th>
                            <th class="text-end">Jumlah</th>
                            <th class="text-end">Harga Satuan</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchase->items as $detail)
                            <tr>
                                <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                <td class="text-wrap" style="min-width: 240px;">
                                    @php
                                        $categoryLabel = $itemCategories[$detail->item->category] ?? ucfirst(str_replace('_', ' ', $detail->item->category));
                                        $stateLabel = $itemMaterialStates[$detail->item->material_state ?? 'none'] ?? ucfirst(str_replace('_', ' ', $detail->item->material_state ?? 'none'));
                                        $itemLabel = ($detail->item->material_state ?? 'none') === 'none' ? $categoryLabel : "{$categoryLabel} - {$stateLabel}";
                                    @endphp
                                    <span class="fw-medium">{{ $detail->item->code }}</span>
                                    <div class="text-muted small">{{ $detail->item->name }}</div>
                                    <div class="text-muted small">{{ $itemLabel }}</div>
                                </td>
                                <td>{{ $detail->warehouse->name }}</td>
                                <td class="text-end">{{ number_format((float) $detail->quantity, 2, ',', '.') }} {{ strtoupper($detail->item->unit) }}</td>
                                <td class="text-end">Rp {{ number_format((float) $detail->unit_price, 2, ',', '.') }}</td>
                                <td class="text-end fw-medium">Rp {{ number_format((float) $detail->subtotal, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-border-bottom-0">
                        <tr>
                            <th colspan="5" class="text-end">Total Pembelian</th>
                            <th class="text-end fw-bold">Rp {{ number_format((float) $purchase->total_amount, 2, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
