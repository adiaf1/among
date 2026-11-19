@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @php
        $userRolesLabel = $user->roles->pluck('name')->join(', ') ?: 'Tanpa Grup Permission';
    @endphp

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
        <div>
            <h4 class="mb-1">Dashboard Operasional</h4>
            <p class="text-muted mb-0">
                Selamat datang, <span class="fw-medium text-heading">{{ $user->name }}</span>
                &middot; Grup: <span class="badge bg-label-primary">{{ $userRolesLabel }}</span>
                &middot; {{ now()->translatedFormat('d F Y') }}
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if($permissions['sales'])
                <a href="{{ route('sales.create') }}" class="btn btn-sm btn-primary">
                    <i class="icon-base bx bx-shopping-bag me-1"></i> Catat Penjualan
                </a>
            @endif
            @if($permissions['productions'])
                <a href="{{ route('seed-productions.create') }}" class="btn btn-sm btn-outline-primary">
                    <i class="icon-base bx bx-cog me-1"></i> Produksi Benih
                </a>
            @endif
            @if($permissions['super_admin'])
                <a href="{{ route('permission-groups.index') }}" class="btn btn-sm btn-label-secondary">
                    <i class="icon-base bx bx-check-shield me-1"></i> Grup Permission
                </a>
            @endif
        </div>
    </div>

    @if(! $hasAnyOperationalWidget)
        <div class="card">
            <div class="card-body py-5 text-center">
                <div class="avatar avatar-lg mx-auto mb-3">
                    <span class="avatar-initial rounded-circle bg-label-primary">
                        <i class="icon-base bx bx-user-check icon-lg"></i>
                    </span>
                </div>
                <h5 class="mb-2">Akun Anda Aktif</h5>
                <p class="text-muted mb-0">
                    Saat ini grup permission Anda (<strong>{{ $userRolesLabel }}</strong>) belum memiliki akses ke modul operasional.
                    Silakan hubungi Super Admin untuk mengatur hak akses modul Anda.
                </p>
            </div>
        </div>
    @else
        <!-- Row 1: KPI Summary Cards (Permission-Driven) -->
        <div class="row g-4 mb-4">
            @if($permissions['stocks'])
                <div class="col-sm-6 col-xl-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <span class="text-heading">Stok Benih Jadi</span>
                                    <h4 class="my-1">{{ number_format((float) ($finishedSeedStockQty ?? 0), 2, ',', '.') }} KG</h4>
                                    @if(($lowStockCount ?? 0) > 0)
                                        <small class="text-danger fw-medium">
                                            <i class="icon-base bx bx-error-circle me-1"></i>{{ $lowStockCount }} barang stok minimum
                                        </small>
                                    @else
                                        <small class="text-success">Seluruh stok aman</small>
                                    @endif
                                </div>
                                <div class="avatar">
                                    <span class="avatar-initial rounded bg-label-primary">
                                        <i class="icon-base bx bx-package icon-lg"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($permissions['growings'])
                <div class="col-sm-6 col-xl-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <span class="text-heading">Penangkaran Aktif</span>
                                    <h4 class="my-1">{{ number_format((int) ($activeGrowingsCount ?? 0), 0, ',', '.') }} Lahan</h4>
                                    @if(($overdueInspectionsCount ?? 0) > 0)
                                        <small class="text-warning fw-medium">
                                            <i class="icon-base bx bx-calendar-exclamation me-1"></i>{{ $overdueInspectionsCount }} pemeriksaan lewat jadwal
                                        </small>
                                    @else
                                        <small class="text-muted">Jadwal pemeriksaan terkendali</small>
                                    @endif
                                </div>
                                <div class="avatar">
                                    <span class="avatar-initial rounded bg-label-success">
                                        <i class="icon-base bx bx-leaf icon-lg"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($permissions['productions'])
                <div class="col-sm-6 col-xl-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <span class="text-heading">Produksi Benih</span>
                                    <h4 class="my-1">{{ number_format((int) ($activeProductionsCount ?? 0), 0, ',', '.') }} Proses</h4>
                                    <small class="text-muted">
                                        {{ $readyProductionsCount ?? 0 }} siap salur &middot; {{ $pendingLabCount ?? 0 }} menunggu lab
                                    </small>
                                </div>
                                <div class="avatar">
                                    <span class="avatar-initial rounded bg-label-info">
                                        <i class="icon-base bx bx-cog icon-lg"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($permissions['sales'])
                <div class="col-sm-6 col-xl-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <span class="text-heading">Penjualan Bulan Ini</span>
                                    @if($permissions['financial'])
                                        <h4 class="my-1">Rp {{ number_format((float) ($salesThisMonthAmount ?? 0), 0, ',', '.') }}</h4>
                                        <small class="text-muted">
                                            {{ $salesThisMonthCount ?? 0 }} transaksi &middot;
                                            @if(($unpaidSalesCount ?? 0) > 0)
                                                <span class="text-warning fw-medium">{{ $unpaidSalesCount }} belum lunas</span>
                                            @else
                                                Semua lunas
                                            @endif
                                        </small>
                                    @else
                                        <h4 class="my-1">{{ number_format((int) ($salesThisMonthCount ?? 0), 0, ',', '.') }} Transaksi</h4>
                                        <small class="text-muted">{{ $unpaidSalesCount ?? 0 }} transaksi belum lunas</small>
                                    @endif
                                </div>
                                <div class="avatar">
                                    <span class="avatar-initial rounded bg-label-warning">
                                        <i class="icon-base bx bx-shopping-bag icon-lg"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($permissions['purchases'])
                <div class="col-sm-6 col-xl-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <span class="text-heading">Pembelian Bulan Ini</span>
                                    @if($permissions['financial'])
                                        <h4 class="my-1">Rp {{ number_format((float) ($purchasesThisMonthAmount ?? 0), 0, ',', '.') }}</h4>
                                        <small class="text-muted">{{ $purchasesThisMonthCount ?? 0 }} transaksi masuk</small>
                                    @else
                                        <h4 class="my-1">{{ number_format((int) ($purchasesThisMonthCount ?? 0), 0, ',', '.') }} Transaksi</h4>
                                        <small class="text-muted">Barang masuk bulan ini</small>
                                    @endif
                                </div>
                                <div class="avatar">
                                    <span class="avatar-initial rounded bg-label-secondary">
                                        <i class="icon-base bx bx-cart-add icon-lg"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($permissions['productions'] && $permissions['financial'])
                <div class="col-sm-6 col-xl-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <span class="text-heading">Rata-rata HPP Benih</span>
                                    <h4 class="my-1">
                                        @if(isset($averageHppPerKg) && $averageHppPerKg !== null)
                                            Rp {{ number_format((float) $averageHppPerKg, 0, ',', '.') }} / kg
                                        @else
                                            -
                                        @endif
                                    </h4>
                                    <small class="text-muted">Dari {{ isset($hppRows) ? $hppRows->count() : 0 }} batch hasil terbaru</small>
                                </div>
                                <div class="avatar">
                                    <span class="avatar-initial rounded bg-label-success">
                                        <i class="icon-base bx bx-calculator icon-lg"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($permissions['super_admin'])
                <div class="col-sm-6 col-xl-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <span class="text-heading">Pengguna & Akses</span>
                                    <h4 class="my-1">{{ $totalUsersCount ?? 0 }} User</h4>
                                    <small class="text-muted">{{ $totalRolesCount ?? 0 }} grup permission aktif</small>
                                </div>
                                <div class="avatar">
                                    <span class="avatar-initial rounded bg-label-danger">
                                        <i class="icon-base bx bx-check-shield icon-lg"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Row 2: Actionable Operational Alerts -->
        <div class="row g-4 mb-4">
            @if($permissions['stocks'])
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center border-bottom">
                            <div>
                                <h5 class="mb-0">Peringatan Stok Minimum</h5>
                                <small class="text-muted">Barang dengan total saldo &le; batas minimum stok.</small>
                            </div>
                            <a href="{{ route('stocks.index') }}" class="btn btn-sm btn-outline-primary">Lihat Stok</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Barang</th>
                                        <th class="text-end">Saldo Saat Ini</th>
                                        <th class="text-end">Minimum</th>
                                    </tr>
                                </thead>
                                <tbody class="table-border-bottom-0">
                                    @forelse($lowStockItems ?? [] as $lowItem)
                                        <tr>
                                            <td>
                                                <span class="fw-medium">{{ $lowItem->name }}</span>
                                                <div class="text-muted small">{{ $lowItem->code }}</div>
                                            </td>
                                            <td class="text-end">
                                                <span class="badge bg-label-danger">
                                                    {{ number_format((float) $lowItem->current_stock, 2, ',', '.') }} {{ strtoupper((string) $lowItem->unit) }}
                                                </span>
                                            </td>
                                            <td class="text-end text-muted">
                                                {{ number_format((float) $lowItem->minimum_stock, 2, ',', '.') }} {{ strtoupper((string) $lowItem->unit) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">
                                                <i class="icon-base bx bx-check-circle text-success me-1"></i> Tidak ada barang di bawah stok minimum.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            @if($permissions['growings'])
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center border-bottom">
                            <div>
                                <h5 class="mb-0">Jadwal Pemeriksaan Lapang Terdekat</h5>
                                <small class="text-muted">Pemeriksaan penangkaran aktif yang belum selesai.</small>
                            </div>
                            <a href="{{ route('seed-growings.index') }}" class="btn btn-sm btn-outline-primary">Penangkaran</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Penangkaran / Petani</th>
                                        <th>Tahap</th>
                                        <th class="text-end">Jadwal</th>
                                    </tr>
                                </thead>
                                <tbody class="table-border-bottom-0">
                                    @forelse($upcomingInspections ?? [] as $inspection)
                                        @php
                                            $isOverdue = $inspection->planned_date && $inspection->planned_date->isPast() && ! $inspection->planned_date->isToday();
                                            $stageLabel = $stageLabels[$inspection->stage] ?? ucfirst(str_replace('_', ' ', (string) $inspection->stage));
                                        @endphp
                                        <tr>
                                            <td>
                                                <a href="{{ route('seed-growings.show', $inspection->seed_growing_id) }}" class="fw-medium">
                                                    {{ $inspection->seedGrowing?->number ?? '-' }}
                                                </a>
                                                <div class="text-muted small">
                                                    {{ $inspection->seedGrowing?->farmer?->name ?? '-' }} &middot; {{ $inspection->seedGrowing?->riceVariety?->name ?? '-' }}
                                                </div>
                                            </td>
                                            <td>{{ $stageLabel }}</td>
                                            <td class="text-end">
                                                <span class="badge {{ $isOverdue ? 'bg-label-danger' : 'bg-label-warning' }}">
                                                    {{ $inspection->planned_date?->format('d M Y') }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">
                                                <i class="icon-base bx bx-check-circle text-success me-1"></i> Tidak ada jadwal pemeriksaan yang tertunda.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            @if($permissions['sales'])
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center border-bottom">
                            <div>
                                <h5 class="mb-0">Penjualan Belum Lunas (Piutang)</h5>
                                <small class="text-muted">
                                    @if($permissions['financial'])
                                        Total piutang aktif: <strong>Rp {{ number_format((float) ($unpaidSalesAmount ?? 0), 0, ',', '.') }}</strong>
                                    @else
                                        Daftar transaksi penjualan yang menunggu pelunasan.
                                    @endif
                                </small>
                            </div>
                            <a href="{{ route('sales.index', ['payment_status' => 'belum_lunas']) }}" class="btn btn-sm btn-outline-primary">Semua</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Nomor</th>
                                        <th>Pelanggan</th>
                                        <th>Tanggal</th>
                                        @if($permissions['financial'])
                                            <th class="text-end">Nominal</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="table-border-bottom-0">
                                    @forelse($unpaidSales ?? [] as $unpaidSale)
                                        <tr>
                                            <td>
                                                <a href="{{ route('sales.show', $unpaidSale) }}" class="fw-medium">{{ $unpaidSale->number }}</a>
                                            </td>
                                            <td>{{ $unpaidSale->customer?->name ?? '-' }}</td>
                                            <td>{{ $unpaidSale->sale_date?->format('d M Y') }}</td>
                                            @if($permissions['financial'])
                                                <td class="text-end fw-medium">Rp {{ number_format((float) $unpaidSale->total_amount, 0, ',', '.') }}</td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $permissions['financial'] ? 4 : 3 }}" class="text-center text-muted py-4">
                                                <i class="icon-base bx bx-check-circle text-success me-1"></i> Semua transaksi penjualan sudah lunas.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            @if($permissions['productions'])
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center border-bottom">
                            <div>
                                <h5 class="mb-0">Peringatan Masa Edar Label Benih</h5>
                                <small class="text-muted">Batch produksi dengan label kedaluwarsa dalam 30 hari ke depan atau sudah lewat.</small>
                            </div>
                            <a href="{{ route('seed-productions.index') }}" class="btn btn-sm btn-outline-primary">Produksi</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Nomor / Lot</th>
                                        <th>Varietas</th>
                                        <th class="text-end">Akhir Masa Edar</th>
                                    </tr>
                                </thead>
                                <tbody class="table-border-bottom-0">
                                    @forelse($expiringLabels ?? [] as $labelBatch)
                                        @php
                                            $isExpired = $labelBatch->label_expiry_date && $labelBatch->label_expiry_date->isPast();
                                        @endphp
                                        <tr>
                                            <td>
                                                <a href="{{ route('seed-productions.show', $labelBatch) }}" class="fw-medium">{{ $labelBatch->number }}</a>
                                                <div class="text-muted small">Lot: {{ $labelBatch->lot_number ?: '-' }}</div>
                                            </td>
                                            <td>
                                                {{ $labelBatch->riceVariety?->name ?? '-' }}
                                                @if($labelBatch->seedClass)
                                                    <span class="text-muted small">({{ $labelBatch->seedClass->code }})</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <span class="badge {{ $isExpired ? 'bg-label-danger' : 'bg-label-warning' }}">
                                                    {{ $labelBatch->label_expiry_date?->format('d M Y') }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">
                                                <i class="icon-base bx bx-check-circle text-success me-1"></i> Tidak ada label benih yang mendekati masa kedaluwarsa.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Row 3: Ringkasan Kalkulasi HPP Benih Jadi (Requires seed-productions.manage + reports.financial.view) -->
        @if($permissions['productions'] && $permissions['financial'])
            <div class="card">
                <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 border-bottom">
                    <div>
                        <h5 class="mb-0">Ringkasan Kalkulasi HPP per Kg Benih Jadi</h5>
                        <small class="text-muted">
                            Estimasi HPP dihitung dari <strong>(Nilai Bahan Rata-rata Pembelian + Total Biaya Tahapan) &divide; Jumlah Bersih Benih Jadi</strong>.
                        </small>
                    </div>
                    <span class="badge bg-label-success">Khusus Akses Keuangan & HPP</span>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Batch Produksi</th>
                                <th>Varietas & Lot</th>
                                <th class="text-end">Hasil Bersih</th>
                                <th class="text-end">Estimasi Bahan</th>
                                <th class="text-end">Biaya Tahapan</th>
                                <th class="text-end">Total Biaya</th>
                                <th class="text-end">HPP / Kg</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse($hppRows ?? [] as $row)
                                @php
                                    $prod = $row['production'];
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('seed-productions.show', $prod) }}" class="fw-medium">{{ $prod->number }}</a>
                                        <div class="text-muted small">{{ $prod->output_date?->format('d M Y') ?? $prod->production_date?->format('d M Y') }}</div>
                                    </td>
                                    <td>
                                        <span class="fw-medium">{{ $prod->riceVariety?->name ?? '-' }}</span>
                                        <div class="text-muted small">
                                            Lot: {{ $prod->lot_number ?: '-' }}
                                            @if($row['yield_percent'] !== null)
                                                &middot; Rendemen {{ number_format((float) $row['yield_percent'], 1, ',', '.') }}%
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-end fw-medium">
                                        {{ number_format((float) $row['output_quantity'], 2, ',', '.') }} {{ strtoupper((string) $prod->unit) }}
                                    </td>
                                    <td class="text-end">
                                        Rp {{ number_format((float) $row['material_cost'], 0, ',', '.') }}
                                        @if($row['unpriced_inputs_count'] > 0)
                                            <div class="text-warning small" title="Sebagian bahan belum memiliki riwayat harga beli">
                                                {{ $row['unpriced_inputs_count'] }} bahan tanpa harga beli
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-end">Rp {{ number_format((float) $row['step_cost'], 0, ',', '.') }}</td>
                                    <td class="text-end fw-medium">Rp {{ number_format((float) $row['total_cost'], 0, ',', '.') }}</td>
                                    <td class="text-end">
                                        <span class="badge bg-label-primary fs-6">
                                            Rp {{ number_format((float) $row['hpp_per_kg'], 0, ',', '.') }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        Belum ada batch produksi dengan catatan hasil akhir benih jadi untuk dikalkulasi HPP-nya.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif
</div>
@endsection
