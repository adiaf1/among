<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\RiceVariety;
use App\Models\Sale;
use App\Models\SeedClass;
use App\Models\SeedGrowing;
use App\Models\SeedGrowingInspection;
use App\Models\SeedProduction;
use App\Models\SeedProductionStep;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private const ITEM_CATEGORIES = [
        'benih' => 'Benih',
        'gabah' => 'Gabah',
        'karung' => 'Karung',
        'plastik' => 'Plastik',
        'benang_karung' => 'Benang Karung',
        'kemasan' => 'Kemasan',
        'bahan_produksi' => 'Bahan Produksi',
        'lainnya' => 'Lainnya',
    ];

    private const ITEM_MATERIAL_STATES = [
        'none' => 'Tidak Ada',
        'basah' => 'Basah',
        'kering' => 'Kering',
        'benih_jadi' => 'Benih Jadi',
        'bahan_pendukung' => 'Bahan Pendukung',
    ];

    public function stocks(Request $request): View|StreamedResponse
    {
        $startDate = $request->date('start_date')?->toDateString() ?? now()->startOfMonth()->toDateString();
        $endDate = $request->date('end_date')?->toDateString() ?? now()->toDateString();
        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $warehouseId = $request->string('warehouse_id')->toString();
        $category = $request->string('category')->toString();
        $materialState = $request->string('material_state')->toString();
        $search = $request->string('search')->toString();

        $stocks = Stock::query()
            ->with(['item', 'warehouse'])
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->whereHas('item', function ($q) use ($category, $materialState) {
                $q->where('is_active', true)
                    ->when($category, fn ($iq) => $iq->where('category', $category))
                    ->when($materialState, fn ($iq) => $iq->where('material_state', $materialState));
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('lot_number', 'like', "%{$search}%")
                        ->orWhereHas('item', fn ($iq) => $iq->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                        ->orWhereHas('warehouse', fn ($wq) => $wq->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('quantity')
            ->get();

        $stockIds = $stocks->pluck('id')->all();

        $periodMovements = empty($stockIds)
            ? collect()
            : StockMovement::query()
                ->select([
                    'stock_id',
                    DB::raw('SUM(quantity_in) as total_in'),
                    DB::raw('SUM(quantity_out) as total_out'),
                ])
                ->whereIn('stock_id', $stockIds)
                ->whereBetween('movement_date', [$startDate, $endDate])
                ->groupBy('stock_id')
                ->get()
                ->keyBy('stock_id');

        $afterMovements = empty($stockIds)
            ? collect()
            : StockMovement::query()
                ->select([
                    'stock_id',
                    DB::raw('SUM(quantity_in) as after_in'),
                    DB::raw('SUM(quantity_out) as after_out'),
                ])
                ->whereIn('stock_id', $stockIds)
                ->whereDate('movement_date', '>', $endDate)
                ->groupBy('stock_id')
                ->get()
                ->keyBy('stock_id');

        $rows = $stocks->map(function (Stock $stock) use ($periodMovements, $afterMovements) {
            $period = $periodMovements->get($stock->id);
            $after = $afterMovements->get($stock->id);

            $qtyIn = (float) ($period->total_in ?? 0);
            $qtyOut = (float) ($period->total_out ?? 0);
            $afterIn = (float) ($after->after_in ?? 0);
            $afterOut = (float) ($after->after_out ?? 0);

            $closingBalance = (float) $stock->quantity - $afterIn + $afterOut;
            $openingBalance = $closingBalance - $qtyIn + $qtyOut;
            $minStock = (float) ($stock->item?->minimum_stock ?? 0);

            return [
                'stock' => $stock,
                'opening_balance' => $openingBalance,
                'qty_in' => $qtyIn,
                'qty_out' => $qtyOut,
                'closing_balance' => $closingBalance,
                'is_low' => $minStock > 0 && $closingBalance <= $minStock,
            ];
        });

        if ($request->string('export')->toString() === 'csv') {
            return $this->streamCsv(
                "laporan-stok-{$startDate}-sd-{$endDate}.csv",
                ['Kode Barang', 'Nama Barang', 'Kategori', 'Status Bahan', 'Gudang', 'No Lot', 'Satuan', 'Saldo Awal', 'Masuk', 'Keluar', 'Saldo Akhir'],
                $rows->map(fn (array $row) => [
                    $row['stock']->item?->code ?? '-',
                    $row['stock']->item?->name ?? '-',
                    self::ITEM_CATEGORIES[$row['stock']->item?->category ?? ''] ?? ($row['stock']->item?->category ?? '-'),
                    self::ITEM_MATERIAL_STATES[$row['stock']->item?->material_state ?? 'none'] ?? '-',
                    $row['stock']->warehouse?->name ?? '-',
                    $row['stock']->lot_number ?: '-',
                    strtoupper((string) ($row['stock']->item?->unit ?? '')),
                    number_format($row['opening_balance'], 2, '.', ''),
                    number_format($row['qty_in'], 2, '.', ''),
                    number_format($row['qty_out'], 2, '.', ''),
                    number_format($row['closing_balance'], 2, '.', ''),
                ])->all()
            );
        }

        return view('reports.stocks', [
            'rows' => $rows,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'warehouseId' => $warehouseId,
            'category' => $category,
            'materialState' => $materialState,
            'search' => $search,
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'categories' => self::ITEM_CATEGORIES,
            'materialStates' => self::ITEM_MATERIAL_STATES,
            'summary' => [
                'total_rows' => $rows->count(),
                'total_in' => (float) $rows->sum('qty_in'),
                'total_out' => (float) $rows->sum('qty_out'),
                'total_closing' => (float) $rows->sum('closing_balance'),
                'low_stock_count' => $rows->where('is_low', true)->count(),
            ],
        ]);
    }

    public function purchases(Request $request): View|StreamedResponse
    {
        $startDate = $request->date('start_date')?->toDateString() ?? now()->startOfMonth()->toDateString();
        $endDate = $request->date('end_date')?->toDateString() ?? now()->toDateString();
        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $sourceType = $request->string('source_type')->toString();
        $warehouseId = $request->string('warehouse_id')->toString();
        $search = $request->string('search')->toString();
        $canViewFinancial = (bool) $request->user()?->canAccessModule('reports.financial.view');

        $purchases = Purchase::query()
            ->with(['supplier', 'farmer', 'items.item', 'items.warehouse'])
            ->whereBetween('purchase_date', [$startDate, $endDate])
            ->when($sourceType === 'supplier', fn ($q) => $q->whereNotNull('supplier_id'))
            ->when($sourceType === 'farmer', fn ($q) => $q->whereNotNull('farmer_id'))
            ->when($warehouseId, fn ($q) => $q->whereHas('items', fn ($iq) => $iq->where('warehouse_id', $warehouseId)))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('number', 'like', "%{$search}%")
                        ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('farmer', fn ($fq) => $fq->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('purchase_date')
            ->latest('created_at')
            ->get();

        if ($request->string('export')->toString() === 'csv') {
            $headers = ['Nomor', 'Tanggal', 'Jenis Asal', 'Nama Supplier / Petani', 'Jumlah Baris', 'Total Volume'];
            if ($canViewFinancial) {
                $headers[] = 'Total Nilai (Rp)';
            }

            return $this->streamCsv(
                "laporan-pembelian-{$startDate}-sd-{$endDate}.csv",
                $headers,
                $purchases->map(function (Purchase $purchase) use ($canViewFinancial) {
                    $row = [
                        $purchase->number,
                        $purchase->purchase_date?->format('Y-m-d') ?? '-',
                        $purchase->supplier ? 'Supplier' : ($purchase->farmer ? 'Petani' : '-'),
                        $purchase->supplier?->name ?? $purchase->farmer?->name ?? '-',
                        $purchase->items->count(),
                        number_format((float) $purchase->items->sum('quantity'), 2, '.', ''),
                    ];
                    if ($canViewFinancial) {
                        $row[] = number_format((float) $purchase->total_amount, 2, '.', '');
                    }

                    return $row;
                })->all()
            );
        }

        return view('reports.purchases', [
            'purchases' => $purchases,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'sourceType' => $sourceType,
            'warehouseId' => $warehouseId,
            'search' => $search,
            'canViewFinancial' => $canViewFinancial,
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'summary' => [
                'count' => $purchases->count(),
                'total_qty' => (float) $purchases->sum(fn (Purchase $p) => (float) $p->items->sum('quantity')),
                'total_amount' => (float) $purchases->sum('total_amount'),
                'supplier_count' => $purchases->whereNotNull('supplier_id')->count(),
                'farmer_count' => $purchases->whereNotNull('farmer_id')->count(),
            ],
        ]);
    }

    public function sales(Request $request): View|StreamedResponse
    {
        $startDate = $request->date('start_date')?->toDateString() ?? now()->startOfMonth()->toDateString();
        $endDate = $request->date('end_date')?->toDateString() ?? now()->toDateString();
        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $customerId = $request->string('customer_id')->toString();
        $customerType = $request->string('customer_type')->toString();
        $paymentStatus = $request->string('payment_status')->toString();
        $search = $request->string('search')->toString();
        $canViewFinancial = (bool) $request->user()?->canAccessModule('reports.financial.view');

        $sales = Sale::query()
            ->with(['customer', 'items.item', 'items.warehouse'])
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->when($customerId, fn ($q) => $q->where('customer_id', $customerId))
            ->when($customerType, fn ($q) => $q->whereHas('customer', fn ($cq) => $cq->where('customer_type', $customerType)))
            ->when($paymentStatus && array_key_exists($paymentStatus, SaleController::PAYMENT_STATUSES), fn ($q) => $q->where('payment_status', $paymentStatus))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('number', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('sale_date')
            ->latest('created_at')
            ->get();

        if ($request->string('export')->toString() === 'csv') {
            $headers = ['Nomor', 'Tanggal', 'Pelanggan', 'Jenis Pelanggan', 'Status Bayar', 'Jumlah Baris', 'Total Volume'];
            if ($canViewFinancial) {
                $headers[] = 'Total Penjualan (Rp)';
            }

            return $this->streamCsv(
                "laporan-penjualan-{$startDate}-sd-{$endDate}.csv",
                $headers,
                $sales->map(function (Sale $sale) use ($canViewFinancial) {
                    $row = [
                        $sale->number,
                        $sale->sale_date?->format('Y-m-d') ?? '-',
                        $sale->customer?->name ?? '-',
                        SaleController::CUSTOMER_TYPES[$sale->customer?->customer_type ?? ''] ?? '-',
                        SaleController::PAYMENT_STATUSES[$sale->payment_status] ?? $sale->payment_status,
                        $sale->items->count(),
                        number_format((float) $sale->items->sum('quantity'), 2, '.', ''),
                    ];
                    if ($canViewFinancial) {
                        $row[] = number_format((float) $sale->total_amount, 2, '.', '');
                    }

                    return $row;
                })->all()
            );
        }

        $paidSales = $sales->where('payment_status', 'lunas');
        $unpaidSales = $sales->where('payment_status', 'belum_lunas');

        return view('reports.sales', [
            'sales' => $sales,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'customerId' => $customerId,
            'customerType' => $customerType,
            'paymentStatus' => $paymentStatus,
            'search' => $search,
            'canViewFinancial' => $canViewFinancial,
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
            'customerTypes' => SaleController::CUSTOMER_TYPES,
            'paymentStatuses' => SaleController::PAYMENT_STATUSES,
            'summary' => [
                'count' => $sales->count(),
                'total_qty' => (float) $sales->sum(fn (Sale $s) => (float) $s->items->sum('quantity')),
                'total_amount' => (float) $sales->sum('total_amount'),
                'paid_count' => $paidSales->count(),
                'paid_amount' => (float) $paidSales->sum('total_amount'),
                'unpaid_count' => $unpaidSales->count(),
                'unpaid_amount' => (float) $unpaidSales->sum('total_amount'),
            ],
        ]);
    }

    public function seedGrowings(Request $request): View|StreamedResponse
    {
        $seasonYear = $request->string('season_year')->toString();
        $riceVarietyId = $request->string('rice_variety_id')->toString();
        $seedClassId = $request->string('seed_class_id')->toString();
        $status = $request->string('status')->toString();
        $search = $request->string('search')->toString();

        $statuses = [
            'berjalan' => 'Berjalan',
            'panen' => 'Panen',
            'batal' => 'Batal',
        ];

        $growings = SeedGrowing::query()
            ->with(['farmer', 'land', 'riceVariety', 'seedClass', 'inspections', 'harvest'])
            ->when($seasonYear, fn ($q) => $q->where('season_year', (int) $seasonYear))
            ->when($riceVarietyId, fn ($q) => $q->where('rice_variety_id', $riceVarietyId))
            ->when($seedClassId, fn ($q) => $q->where('seed_class_id', $seedClassId))
            ->when($status && array_key_exists($status, $statuses), fn ($q) => $q->where('status', $status))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('number', 'like', "%{$search}%")
                        ->orWhere('lot_number', 'like', "%{$search}%")
                        ->orWhere('field_number', 'like', "%{$search}%")
                        ->orWhereHas('farmer', fn ($fq) => $fq->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('season_year')
            ->orderByDesc('planting_date')
            ->get();

        if ($request->string('export')->toString() === 'csv') {
            return $this->streamCsv(
                'laporan-penangkaran-benih.csv',
                ['Nomor', 'Musim/Tahun', 'No Lot', 'Petani', 'Lahan', 'Varietas', 'Kelas Benih', 'Luas (Ha)', 'Benih Sumber (Kg)', 'Pemeriksaan Selesai', 'Hasil Panen (Kg)', 'Produktivitas (Kg/Ha)', 'Status'],
                $growings->map(function (SeedGrowing $growing) use ($statuses) {
                    $area = (float) ($growing->field_area ?? 0);
                    $harvestQty = (float) ($growing->harvest?->harvested_quantity ?? 0);
                    $productivity = ($area > 0 && $harvestQty > 0) ? ($harvestQty / $area) : 0;
                    $doneInspections = $growing->inspections->where('status', 'selesai')->count();
                    $totalInspections = $growing->inspections->count();

                    return [
                        $growing->number,
                        $growing->season_year ?? '-',
                        $growing->lot_number ?: '-',
                        $growing->farmer?->name ?? '-',
                        $growing->land?->name ?? '-',
                        $growing->riceVariety?->name ?? '-',
                        $growing->seedClass?->name ?? '-',
                        number_format($area, 2, '.', ''),
                        number_format((float) ($growing->source_seed_quantity ?? 0), 2, '.', ''),
                        "{$doneInspections}/{$totalInspections}",
                        number_format($harvestQty, 2, '.', ''),
                        number_format($productivity, 2, '.', ''),
                        $statuses[$growing->status] ?? $growing->status,
                    ];
                })->all()
            );
        }

        $totalArea = (float) $growings->sum('field_area');
        $totalHarvest = (float) $growings->sum(fn (SeedGrowing $g) => (float) ($g->harvest?->harvested_quantity ?? 0));

        return view('reports.seed-growings', [
            'growings' => $growings,
            'seasonYear' => $seasonYear,
            'riceVarietyId' => $riceVarietyId,
            'seedClassId' => $seedClassId,
            'status' => $status,
            'search' => $search,
            'statuses' => $statuses,
            'seasonYears' => SeedGrowing::query()->whereNotNull('season_year')->distinct()->orderByDesc('season_year')->pluck('season_year'),
            'riceVarieties' => RiceVariety::where('is_active', true)->orderBy('name')->get(),
            'seedClasses' => SeedClass::where('is_active', true)->orderBy('name')->get(),
            'summary' => [
                'count' => $growings->count(),
                'total_area' => $totalArea,
                'total_harvest' => $totalHarvest,
                'avg_productivity' => $totalArea > 0 ? ($totalHarvest / $totalArea) : 0,
            ],
        ]);
    }

    public function seedProductions(Request $request): View|StreamedResponse
    {
        $startDate = $request->date('start_date')?->toDateString() ?? now()->startOfYear()->toDateString();
        $endDate = $request->date('end_date')?->toDateString() ?? now()->toDateString();
        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $riceVarietyId = $request->string('rice_variety_id')->toString();
        $seedClassId = $request->string('seed_class_id')->toString();
        $status = $request->string('status')->toString();
        $search = $request->string('search')->toString();
        $canViewFinancial = (bool) $request->user()?->canAccessModule('reports.financial.view');

        $statuses = [
            'proses' => 'Proses',
            'siap_salur' => 'Siap Salur',
            'batal' => 'Batal',
        ];

        $productions = SeedProduction::query()
            ->with(['riceVariety', 'seedClass', 'outputWarehouse', 'inputs.item', 'steps'])
            ->whereBetween('production_date', [$startDate, $endDate])
            ->when($riceVarietyId, fn ($q) => $q->where('rice_variety_id', $riceVarietyId))
            ->when($seedClassId, fn ($q) => $q->where('seed_class_id', $seedClassId))
            ->when($status && array_key_exists($status, $statuses), fn ($q) => $q->where('status', $status))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('number', 'like', "%{$search}%")
                        ->orWhere('lot_number', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('production_date')
            ->latest('created_at')
            ->get();

        $avgUnitPrices = [];
        if ($canViewFinancial && $productions->isNotEmpty()) {
            $itemIds = $productions
                ->flatMap(fn (SeedProduction $p) => $p->inputs->pluck('item_id'))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (! empty($itemIds)) {
                $avgUnitPrices = PurchaseItem::query()
                    ->select([
                        'item_id',
                        DB::raw('SUM(subtotal) / NULLIF(SUM(quantity), 0) as avg_unit_price'),
                    ])
                    ->whereIn('item_id', $itemIds)
                    ->groupBy('item_id')
                    ->pluck('avg_unit_price', 'item_id')
                    ->map(fn ($price) => (float) $price)
                    ->all();
            }
        }

        $rows = $productions->map(function (SeedProduction $production) use ($canViewFinancial, $avgUnitPrices) {
            $mainInputQty = (float) $production->inputs->where('role', 'bahan_utama')->sum('quantity');
            $outputQty = (float) ($production->output_quantity ?? 0);
            $yieldPercent = ($mainInputQty > 0 && $outputQty > 0) ? (($outputQty / $mainInputQty) * 100) : null;
            $stepCost = (float) $production->steps->sum('cost');
            $materialCost = 0.0;
            $unpricedInputs = 0;

            if ($canViewFinancial) {
                foreach ($production->inputs as $input) {
                    $unitPrice = $avgUnitPrices[$input->item_id] ?? null;
                    if ($unitPrice !== null && $unitPrice > 0) {
                        $materialCost += (float) $input->quantity * $unitPrice;
                    } else {
                        $unpricedInputs++;
                    }
                }
            }

            $totalCost = $materialCost + $stepCost;
            $hppPerKg = ($outputQty > 0) ? ($totalCost / $outputQty) : null;

            return [
                'production' => $production,
                'main_input_qty' => $mainInputQty,
                'output_qty' => $outputQty,
                'yield_percent' => $yieldPercent,
                'material_cost' => $materialCost,
                'step_cost' => $stepCost,
                'total_cost' => $totalCost,
                'hpp_per_kg' => $hppPerKg,
                'unpriced_inputs' => $unpricedInputs,
            ];
        });

        if ($request->string('export')->toString() === 'csv') {
            $headers = ['Nomor', 'Tanggal Produksi', 'No Lot', 'Varietas', 'Kelas Benih', 'Status', 'Bahan Utama (Kg)', 'Hasil Benih Jadi (Kg)', 'Rendemen (%)'];
            if ($canViewFinancial) {
                $headers = array_merge($headers, ['Estimasi Bahan (Rp)', 'Biaya Tahapan (Rp)', 'Total Biaya (Rp)', 'HPP per Kg (Rp)']);
            }

            return $this->streamCsv(
                "laporan-produksi-hpp-{$startDate}-sd-{$endDate}.csv",
                $headers,
                $rows->map(function (array $row) use ($statuses, $canViewFinancial) {
                    $prod = $row['production'];
                    $csvRow = [
                        $prod->number,
                        $prod->production_date?->format('Y-m-d') ?? '-',
                        $prod->lot_number ?: '-',
                        $prod->riceVariety?->name ?? '-',
                        $prod->seedClass?->name ?? '-',
                        $statuses[$prod->status] ?? $prod->status,
                        number_format($row['main_input_qty'], 2, '.', ''),
                        number_format($row['output_qty'], 2, '.', ''),
                        $row['yield_percent'] !== null ? number_format($row['yield_percent'], 2, '.', '') : '-',
                    ];
                    if ($canViewFinancial) {
                        $csvRow[] = number_format($row['material_cost'], 2, '.', '');
                        $csvRow[] = number_format($row['step_cost'], 2, '.', '');
                        $csvRow[] = number_format($row['total_cost'], 2, '.', '');
                        $csvRow[] = $row['hpp_per_kg'] !== null ? number_format($row['hpp_per_kg'], 2, '.', '') : '-';
                    }

                    return $csvRow;
                })->all()
            );
        }

        $totalMainInput = (float) $rows->sum('main_input_qty');
        $totalOutput = (float) $rows->sum('output_qty');
        $completedRows = $rows->filter(fn (array $r) => $r['output_qty'] > 0);
        $totalCostCompleted = (float) $completedRows->sum('total_cost');
        $totalOutputCompleted = (float) $completedRows->sum('output_qty');

        return view('reports.seed-productions', [
            'rows' => $rows,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'riceVarietyId' => $riceVarietyId,
            'seedClassId' => $seedClassId,
            'status' => $status,
            'search' => $search,
            'statuses' => $statuses,
            'canViewFinancial' => $canViewFinancial,
            'riceVarieties' => RiceVariety::where('is_active', true)->orderBy('name')->get(),
            'seedClasses' => SeedClass::where('is_active', true)->orderBy('name')->get(),
            'summary' => [
                'count' => $rows->count(),
                'total_main_input' => $totalMainInput,
                'total_output' => $totalOutput,
                'avg_yield' => $totalMainInput > 0 ? (($totalOutput / $totalMainInput) * 100) : 0,
                'avg_hpp' => $totalOutputCompleted > 0 ? ($totalCostCompleted / $totalOutputCompleted) : null,
            ],
        ]);
    }

    public function certifications(Request $request): View|StreamedResponse
    {
        $labResult = $request->string('lab_result')->toString();
        $riceVarietyId = $request->string('rice_variety_id')->toString();
        $seedClassId = $request->string('seed_class_id')->toString();
        $expiryStatus = $request->string('expiry_status')->toString();
        $search = $request->string('search')->toString();
        $today = now()->toDateString();
        $soonDate = now()->addDays(30)->toDateString();

        $productions = SeedProduction::query()
            ->with(['riceVariety', 'seedClass'])
            ->where('status', '!=', 'batal')
            ->when($labResult === 'belum_diuji', fn ($q) => $q->whereNull('lab_result'))
            ->when($labResult && $labResult !== 'belum_diuji' && array_key_exists($labResult, SeedProductionController::LAB_RESULTS), fn ($q) => $q->where('lab_result', $labResult))
            ->when($riceVarietyId, fn ($q) => $q->where('rice_variety_id', $riceVarietyId))
            ->when($seedClassId, fn ($q) => $q->where('seed_class_id', $seedClassId))
            ->when($expiryStatus === 'kedaluwarsa', fn ($q) => $q->whereNotNull('label_expiry_date')->whereDate('label_expiry_date', '<', $today))
            ->when($expiryStatus === 'segera_kedaluwarsa', fn ($q) => $q->whereNotNull('label_expiry_date')->whereDate('label_expiry_date', '>=', $today)->whereDate('label_expiry_date', '<=', $soonDate))
            ->when($expiryStatus === 'aktif', fn ($q) => $q->whereNotNull('label_expiry_date')->whereDate('label_expiry_date', '>', $soonDate))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('number', 'like', "%{$search}%")
                        ->orWhere('lot_number', 'like', "%{$search}%")
                        ->orWhere('certificate_number', 'like', "%{$search}%")
                        ->orWhere('label_serial_range', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('lab_test_date')
            ->orderByDesc('production_date')
            ->get();

        if ($request->string('export')->toString() === 'csv') {
            return $this->streamCsv(
                'laporan-sertifikasi-bpsb.csv',
                ['Nomor Produksi', 'No Lot', 'Varietas', 'Kelas Benih', 'Tanggal Uji Lab', 'KA (%)', 'Kemurnian (%)', 'DB (%)', 'Status Uji Lab', 'No Induk Sertifikasi / LHU', 'No Seri Label', 'Jumlah Keping', 'Akhir Masa Edar'],
                $productions->map(fn (SeedProduction $p) => [
                    $p->number,
                    $p->lot_number ?: '-',
                    $p->riceVariety?->name ?? '-',
                    $p->seedClass?->name ?? '-',
                    $p->lab_test_date?->format('Y-m-d') ?? '-',
                    $p->moisture_content !== null ? number_format((float) $p->moisture_content, 2, '.', '') : '-',
                    $p->purity_percentage !== null ? number_format((float) $p->purity_percentage, 2, '.', '') : '-',
                    $p->germination_rate !== null ? number_format((float) $p->germination_rate, 2, '.', '') : '-',
                    $p->lab_result ? (SeedProductionController::LAB_RESULTS[$p->lab_result] ?? $p->lab_result) : 'Belum Diuji',
                    $p->certificate_number ?: '-',
                    $p->label_serial_range ?: '-',
                    $p->label_count ?? '-',
                    $p->label_expiry_date?->format('Y-m-d') ?? '-',
                ])->all()
            );
        }

        $expiringCount = $productions->filter(fn (SeedProduction $p) => $p->label_expiry_date && $p->label_expiry_date->lte(now()->addDays(30)))->count();

        return view('reports.certifications', [
            'productions' => $productions,
            'labResult' => $labResult,
            'riceVarietyId' => $riceVarietyId,
            'seedClassId' => $seedClassId,
            'expiryStatus' => $expiryStatus,
            'search' => $search,
            'labResults' => SeedProductionController::LAB_RESULTS,
            'riceVarieties' => RiceVariety::where('is_active', true)->orderBy('name')->get(),
            'seedClasses' => SeedClass::where('is_active', true)->orderBy('name')->get(),
            'summary' => [
                'total' => $productions->count(),
                'passed' => $productions->where('lab_result', 'lulus')->count(),
                'pending' => $productions->filter(fn (SeedProduction $p) => $p->lab_result === null || $p->lab_result === 'menunggu')->count(),
                'failed' => $productions->where('lab_result', 'tidak_lulus')->count(),
                'expiring' => $expiringCount,
            ],
        ]);
    }

    public function financial(Request $request): View|StreamedResponse
    {
        $startDate = $request->date('start_date')?->toDateString() ?? now()->startOfYear()->toDateString();
        $endDate = $request->date('end_date')?->toDateString() ?? now()->toDateString();
        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $sales = Sale::query()
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->get(['sale_date', 'payment_status', 'total_amount']);

        $purchases = Purchase::query()
            ->whereBetween('purchase_date', [$startDate, $endDate])
            ->get(['purchase_date', 'total_amount']);

        $inspectionCosts = SeedGrowingInspection::query()
            ->where('cost', '>', 0)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('actual_date', [$startDate, $endDate])
                    ->orWhere(fn ($sq) => $sq->whereNull('actual_date')->whereBetween('planned_date', [$startDate, $endDate]));
            })
            ->get(['actual_date', 'planned_date', 'cost']);

        $productionStepCosts = SeedProductionStep::query()
            ->where('cost', '>', 0)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('actual_date', [$startDate, $endDate])
                    ->orWhere(fn ($sq) => $sq->whereNull('actual_date')->whereBetween('planned_date', [$startDate, $endDate]));
            })
            ->get(['actual_date', 'planned_date', 'cost']);

        $monthly = [];
        $ensureMonth = function (string $ym) use (&$monthly): void {
            if (! isset($monthly[$ym])) {
                $monthly[$ym] = [
                    'month' => $ym,
                    'sales_paid' => 0.0,
                    'sales_unpaid' => 0.0,
                    'purchases' => 0.0,
                    'inspection_cost' => 0.0,
                    'production_cost' => 0.0,
                ];
            }
        };

        foreach ($sales as $sale) {
            $ym = $sale->sale_date?->format('Y-m') ?? 'Unknown';
            $ensureMonth($ym);
            if ($sale->payment_status === 'lunas') {
                $monthly[$ym]['sales_paid'] += (float) $sale->total_amount;
            } else {
                $monthly[$ym]['sales_unpaid'] += (float) $sale->total_amount;
            }
        }

        foreach ($purchases as $purchase) {
            $ym = $purchase->purchase_date?->format('Y-m') ?? 'Unknown';
            $ensureMonth($ym);
            $monthly[$ym]['purchases'] += (float) $purchase->total_amount;
        }

        foreach ($inspectionCosts as $insp) {
            $ym = ($insp->actual_date ?? $insp->planned_date)?->format('Y-m') ?? 'Unknown';
            $ensureMonth($ym);
            $monthly[$ym]['inspection_cost'] += (float) $insp->cost;
        }

        foreach ($productionStepCosts as $step) {
            $ym = ($step->actual_date ?? $step->planned_date)?->format('Y-m') ?? 'Unknown';
            $ensureMonth($ym);
            $monthly[$ym]['production_cost'] += (float) $step->cost;
        }

        krsort($monthly);

        $monthlyRows = collect($monthly)->map(function (array $row) {
            $totalSales = $row['sales_paid'] + $row['sales_unpaid'];
            $totalExpense = $row['purchases'] + $row['inspection_cost'] + $row['production_cost'];
            $row['total_sales'] = $totalSales;
            $row['total_expense'] = $totalExpense;
            $row['net_margin'] = $totalSales - $totalExpense;

            return $row;
        })->values();

        if ($request->string('export')->toString() === 'csv') {
            return $this->streamCsv(
                "laporan-keuangan-{$startDate}-sd-{$endDate}.csv",
                ['Bulan', 'Penjualan Lunas (Rp)', 'Piutang Belum Lunas (Rp)', 'Total Penjualan (Rp)', 'Pembelian (Rp)', 'Biaya Pemeriksaan (Rp)', 'Biaya Produksi (Rp)', 'Total Pengeluaran (Rp)', 'Selisih Operasional (Rp)'],
                $monthlyRows->map(fn (array $r) => [
                    $r['month'],
                    number_format($r['sales_paid'], 2, '.', ''),
                    number_format($r['sales_unpaid'], 2, '.', ''),
                    number_format($r['total_sales'], 2, '.', ''),
                    number_format($r['purchases'], 2, '.', ''),
                    number_format($r['inspection_cost'], 2, '.', ''),
                    number_format($r['production_cost'], 2, '.', ''),
                    number_format($r['total_expense'], 2, '.', ''),
                    number_format($r['net_margin'], 2, '.', ''),
                ])->all()
            );
        }

        $totalSalesPaid = (float) $monthlyRows->sum('sales_paid');
        $totalSalesUnpaid = (float) $monthlyRows->sum('sales_unpaid');
        $totalSales = $totalSalesPaid + $totalSalesUnpaid;
        $totalPurchases = (float) $monthlyRows->sum('purchases');
        $totalInspection = (float) $monthlyRows->sum('inspection_cost');
        $totalProduction = (float) $monthlyRows->sum('production_cost');
        $totalExpense = $totalPurchases + $totalInspection + $totalProduction;

        return view('reports.financial', [
            'monthlyRows' => $monthlyRows,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'summary' => [
                'sales_paid' => $totalSalesPaid,
                'sales_unpaid' => $totalSalesUnpaid,
                'total_sales' => $totalSales,
                'purchases' => $totalPurchases,
                'inspection_cost' => $totalInspection,
                'production_cost' => $totalProduction,
                'total_expense' => $totalExpense,
                'net_margin' => $totalSales - $totalExpense,
            ],
        ]);
    }

    private function streamCsv(string $filename, array $headers, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM agar Excel langsung membaca karakter & pemisah dengan benar
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
