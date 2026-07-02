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
                margin: 14mm;
            }

            body {
                background: #fff !important;
                color: #111 !important;
                font-size: 12px;
            }

            #layout-navbar,
            #layout-menu,
            .content-footer,
            .screen-document,
            .alert {
                display: none !important;
            }

            .container-xxl {
                max-width: 100% !important;
                padding: 0 !important;
            }

            .print-document {
                display: block !important;
            }

            .print-document h3,
            .print-document h5,
            .print-document p {
                color: #111 !important;
            }

            .print-header {
                border-bottom: 2px solid #111;
                margin-bottom: 18px;
                padding-bottom: 12px;
            }

            .print-title {
                border-bottom: 1px solid #111;
                display: inline-block;
                font-size: 18px;
                font-weight: 700;
                margin: 8px 0 18px;
                padding-bottom: 2px;
                text-transform: uppercase;
            }

            .print-meta {
                display: grid;
                gap: 18px;
                grid-template-columns: 1fr 1fr;
                margin-bottom: 18px;
            }

            .print-meta table,
            .print-summary table {
                width: 100%;
            }

            .print-meta td,
            .print-summary td {
                padding: 2px 0;
                vertical-align: top;
            }

            .print-meta td:first-child,
            .print-summary td:first-child {
                width: 120px;
            }

            .print-table {
                border-collapse: collapse;
                width: 100%;
            }

            .print-table th,
            .print-table td {
                border: 1px solid #111;
                padding: 7px;
                vertical-align: top;
            }

            .print-table th {
                background: #f2f2f2 !important;
                font-weight: 700;
                text-align: center;
            }

            .print-signatures {
                display: grid;
                gap: 28px;
                grid-template-columns: repeat(3, 1fr);
                margin-top: 36px;
                text-align: center;
            }

            .signature-box {
                min-height: 130px;
                position: relative;
            }

            .stamp-box {
                border: 1px dashed #777;
                border-radius: 50%;
                color: #555 !important;
                font-size: 11px;
                height: 82px;
                left: 50%;
                line-height: 1.2;
                margin-left: -41px;
                padding-top: 24px;
                position: absolute;
                top: 28px;
                width: 82px;
            }

            .signature-line {
                border-top: 1px solid #111;
                bottom: 0;
                left: 10%;
                padding-top: 6px;
                position: absolute;
                right: 10%;
            }

            .text-end-print {
                text-align: right;
            }
        }
    </style>

    <div class="print-document">
        <div class="print-header">
            <h3 class="mb-1">{{ config('app.name') }}</h3>
            <p class="mb-0">Tanda Terima Pembelian Barang</p>
        </div>

        <div class="text-center">
            <div class="print-title">Tanda Terima / Invoice Pembelian</div>
        </div>

        <div class="print-meta">
            <table>
                <tr>
                    <td>Nomor</td>
                    <td>: {{ $purchase->number }}</td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td>: {{ $purchase->purchase_date?->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td>Asal Barang</td>
                    <td>: {{ $sourceName }} ({{ $sourceLabel }})</td>
                </tr>
                @if($sourceContact)
                    <tr>
                        <td>Kontak</td>
                        <td>: {{ $sourceContact }}</td>
                    </tr>
                @endif
                <tr>
                    <td>Telepon</td>
                    <td>: {{ $sourcePhone ?: '-' }}</td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td>: {{ $sourceAddress ?: '-' }}</td>
                </tr>
            </table>

            <table>
                <tr>
                    <td>Jenis Angkutan</td>
                    <td>: {{ $purchase->transport_type ?: '-' }}</td>
                </tr>
                <tr>
                    <td>No Pol Kendaraan</td>
                    <td>: {{ $purchase->vehicle_plate_number ?: '-' }}</td>
                </tr>
                <tr>
                    <td>Dibuat Oleh</td>
                    <td>: {{ $purchase->creator?->name ?: '-' }}</td>
                </tr>
                <tr>
                    <td>Catatan</td>
                    <td>: {{ $purchase->notes ?: '-' }}</td>
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
                <div>Cap Perusahaan,</div>
                <div class="stamp-box">CAP<br>PERUSAHAAN</div>
                <div class="signature-line">{{ config('app.name') }}</div>
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
                <a href="{{ route('purchases.index') }}" class="btn btn-label-secondary">Kembali</a>
                <button type="button" class="btn btn-label-primary" onclick="window.print()">
                    <i class="bx bx-printer me-1"></i> Print
                </button>
                <a href="{{ route('purchases.create') }}" class="btn btn-primary">Tambah</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-body">
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
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Barang</th>
                            <th>Gudang Tujuan</th>
                            <th>Jumlah</th>
                            <th>Harga Satuan</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchase->items as $detail)
                            <tr>
                                <td>
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
                                <td>{{ number_format((float) $detail->quantity, 2, ',', '.') }} {{ strtoupper($detail->item->unit) }}</td>
                                <td>Rp {{ number_format((float) $detail->unit_price, 2, ',', '.') }}</td>
                                <td>Rp {{ number_format((float) $detail->subtotal, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
