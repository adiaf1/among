<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SeedGrowing;
use App\Models\SeedGrowingInspection;
use App\Models\SeedProduction;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    private const INSPECTION_STAGE_LABELS = [
        'pendahuluan' => 'Pemeriksaan Pendahuluan',
        'pemeriksaan_1' => 'Pemeriksaan Lapang I (Vegetatif)',
        'pemeriksaan_2' => 'Pemeriksaan Lapang II (Berbunga)',
        'pemeriksaan_3' => 'Pemeriksaan Lapang III (Masak)',
    ];

    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $permissions = [
            'stocks' => $user->canAccessModule('stocks.manage'),
            'purchases' => $user->canAccessModule('purchases.manage'),
            'sales' => $user->canAccessModule('sales.manage'),
            'growings' => $user->canAccessModule('seed-growings.manage'),
            'productions' => $user->canAccessModule('seed-productions.manage'),
            'financial' => $user->canAccessModule('reports.financial.view'),
            'super_admin' => $user->isSuperAdmin(),
        ];

        $data = [
            'user' => $user,
            'permissions' => $permissions,
            'stageLabels' => self::INSPECTION_STAGE_LABELS,
            'hasAnyOperationalWidget' => $permissions['stocks']
                || $permissions['purchases']
                || $permissions['sales']
                || $permissions['growings']
                || $permissions['productions']
                || $permissions['super_admin'],
        ];

        if ($permissions['stocks']) {
            $data['finishedSeedStockQty'] = (float) Stock::query()
                ->join('items', 'items.id', '=', 'stocks.item_id')
                ->where('items.is_active', true)
                ->where('items.material_state', 'benih_jadi')
                ->sum('stocks.quantity');

            $stockTotalsSub = DB::table('stocks')
                ->select('item_id', DB::raw('SUM(quantity) as total_quantity'))
                ->groupBy('item_id');

            $lowStockQuery = Item::query()
                ->leftJoinSub($stockTotalsSub, 'stock_totals', 'items.id', '=', 'stock_totals.item_id')
                ->select([
                    'items.id',
                    'items.code',
                    'items.name',
                    'items.unit',
                    'items.minimum_stock',
                    DB::raw('COALESCE(stock_totals.total_quantity, 0) as current_stock'),
                ])
                ->where('items.is_active', true)
                ->where('items.minimum_stock', '>', 0)
                ->whereRaw('COALESCE(stock_totals.total_quantity, 0) <= items.minimum_stock');

            $data['lowStockCount'] = (clone $lowStockQuery)->count();
            $data['lowStockItems'] = $lowStockQuery
                ->orderByRaw('(COALESCE(stock_totals.total_quantity, 0) - items.minimum_stock) ASC')
                ->limit(8)
                ->get();
        }

        if ($permissions['sales']) {
            $monthSalesQuery = Sale::query()->whereBetween('sale_date', [$monthStart, $monthEnd]);
            $unpaidSalesQuery = Sale::query()->where('payment_status', 'belum_lunas');

            $data['salesThisMonthCount'] = (clone $monthSalesQuery)->count();
            $data['unpaidSalesCount'] = (clone $unpaidSalesQuery)->count();
            $data['unpaidSales'] = (clone $unpaidSalesQuery)
                ->with('customer')
                ->orderBy('sale_date')
                ->limit(6)
                ->get();

            if ($permissions['financial']) {
                $data['salesThisMonthAmount'] = (float) (clone $monthSalesQuery)->sum('total_amount');
                $data['unpaidSalesAmount'] = (float) (clone $unpaidSalesQuery)->sum('total_amount');
            }
        }

        if ($permissions['purchases']) {
            $monthPurchasesQuery = Purchase::query()->whereBetween('purchase_date', [$monthStart, $monthEnd]);
            $data['purchasesThisMonthCount'] = (clone $monthPurchasesQuery)->count();

            if ($permissions['financial']) {
                $data['purchasesThisMonthAmount'] = (float) (clone $monthPurchasesQuery)->sum('total_amount');
            }
        }

        if ($permissions['growings']) {
            $data['activeGrowingsCount'] = SeedGrowing::query()
                ->where('status', 'berjalan')
                ->count();

            $pendingInspectionsBase = SeedGrowingInspection::query()
                ->where('status', '!=', 'selesai')
                ->whereHas('seedGrowing', fn ($q) => $q->where('status', 'berjalan'))
                ->whereNotNull('planned_date');

            $data['overdueInspectionsCount'] = (clone $pendingInspectionsBase)
                ->whereDate('planned_date', '<', $today)
                ->count();

            $data['upcomingInspections'] = (clone $pendingInspectionsBase)
                ->with(['seedGrowing.farmer', 'seedGrowing.riceVariety', 'seedGrowing.land'])
                ->orderBy('planned_date')
                ->limit(8)
                ->get();
        }

        if ($permissions['productions']) {
            $data['activeProductionsCount'] = SeedProduction::query()
                ->where('status', 'proses')
                ->count();

            $data['readyProductionsCount'] = SeedProduction::query()
                ->where('status', 'siap_salur')
                ->count();

            $data['pendingLabCount'] = SeedProduction::query()
                ->where('status', 'proses')
                ->where(function ($q) {
                    $q->whereNull('lab_result')->orWhere('lab_result', 'menunggu');
                })
                ->count();

            $data['expiringLabels'] = SeedProduction::query()
                ->with(['riceVariety', 'seedClass'])
                ->where('status', '!=', 'batal')
                ->whereNotNull('label_expiry_date')
                ->whereDate('label_expiry_date', '<=', now()->addDays(30)->toDateString())
                ->orderBy('label_expiry_date')
                ->limit(6)
                ->get();

            if ($permissions['financial']) {
                $hppBatches = SeedProduction::query()
                    ->with(['riceVariety', 'seedClass', 'outputItem', 'inputs.item', 'steps'])
                    ->where('status', '!=', 'batal')
                    ->whereNotNull('output_quantity')
                    ->where('output_quantity', '>', 0)
                    ->orderByDesc('output_date')
                    ->orderByDesc('production_date')
                    ->limit(10)
                    ->get();

                $itemIds = $hppBatches
                    ->flatMap(fn (SeedProduction $prod) => $prod->inputs->pluck('item_id'))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                $avgUnitPrices = [];
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

                $hppRows = $hppBatches->map(function (SeedProduction $production) use ($avgUnitPrices) {
                    $stepCost = (float) $production->steps->sum('cost');
                    $materialCost = 0.0;
                    $unpricedInputsCount = 0;

                    foreach ($production->inputs as $input) {
                        $unitPrice = $avgUnitPrices[$input->item_id] ?? null;
                        if ($unitPrice !== null && $unitPrice > 0) {
                            $materialCost += (float) $input->quantity * $unitPrice;
                        } else {
                            $unpricedInputsCount++;
                        }
                    }

                    $mainInputQty = (float) $production->inputs
                        ->where('role', 'bahan_utama')
                        ->sum('quantity');
                    $outputQty = (float) $production->output_quantity;
                    $totalCost = $materialCost + $stepCost;
                    $hppPerKg = $outputQty > 0 ? ($totalCost / $outputQty) : 0.0;
                    $yieldPercent = $mainInputQty > 0 ? (($outputQty / $mainInputQty) * 100) : null;

                    return [
                        'production' => $production,
                        'material_cost' => $materialCost,
                        'step_cost' => $stepCost,
                        'total_cost' => $totalCost,
                        'output_quantity' => $outputQty,
                        'hpp_per_kg' => $hppPerKg,
                        'unpriced_inputs_count' => $unpricedInputsCount,
                        'yield_percent' => $yieldPercent,
                    ];
                });

                $totalOutputAll = (float) $hppRows->sum('output_quantity');
                $totalCostAll = (float) $hppRows->sum('total_cost');

                $data['hppRows'] = $hppRows;
                $data['averageHppPerKg'] = $totalOutputAll > 0 ? ($totalCostAll / $totalOutputAll) : null;
            }
        }

        if ($permissions['super_admin']) {
            $data['totalUsersCount'] = User::query()->count();
            $data['totalRolesCount'] = Role::query()->count();
        }

        return view('dashboard.index', $data);
    }
}
