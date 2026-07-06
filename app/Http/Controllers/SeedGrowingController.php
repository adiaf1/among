<?php

namespace App\Http\Controllers;

use App\Models\Farmer;
use App\Models\Item;
use App\Models\Land;
use App\Models\RiceVariety;
use App\Models\SeedClass;
use App\Models\SeedGrowing;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Http\Controllers\SeedGrowingHarvestController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SeedGrowingController extends Controller
{
    private const STATUSES = [
        'draft' => 'Draft',
        'berjalan' => 'Berjalan',
        'panen' => 'Panen',
        'selesai' => 'Selesai',
        'batal' => 'Batal',
    ];

    private const INSPECTION_STAGES = [
        'pendahuluan' => [
            'label' => 'Pendahuluan',
            'offset' => 15,
        ],
        'pl1' => [
            'label' => 'Pemeriksaan Lapang 1',
            'offset' => 45,
        ],
        'pl2' => [
            'label' => 'Pemeriksaan Lapang 2',
            'offset' => 75,
        ],
        'pl3' => [
            'label' => 'Pemeriksaan Lapang 3',
            'offset' => 95,
        ],
    ];

    private const INSPECTION_STATUSES = [
        'terjadwal' => 'Terjadwal',
        'selesai' => 'Selesai',
    ];

    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();

        $seedGrowings = SeedGrowing::query()
            ->with(['farmer', 'land', 'riceVariety', 'seedClass'])
            ->when($status && array_key_exists($status, self::STATUSES), fn ($query) => $query->where('status', $status))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('number', 'like', "%{$search}%")
                        ->orWhere('field_number', 'like', "%{$search}%")
                        ->orWhere('lot_number', 'like', "%{$search}%")
                        ->orWhereHas('farmer', fn ($query) => $query->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                        ->orWhereHas('land', fn ($query) => $query->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                        ->orWhereHas('riceVariety', fn ($query) => $query->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('seed-growings.index', [
            'seedGrowings' => $seedGrowings,
            'search' => $search,
            'status' => $status,
            'statuses' => self::STATUSES,
        ]);
    }

    public function create(): View
    {
        $usedFieldAreas = SeedGrowing::query()
            ->select('land_id', 'season_year', DB::raw('SUM(field_area) as used_area'))
            ->groupBy('land_id', 'season_year')
            ->get()
            ->groupBy('land_id')
            ->map(fn ($rows) => $rows->pluck('used_area', 'season_year'))
            ->toArray();
        $sourceSeedStocks = Stock::query()
            ->whereHas('item', fn ($query) => $query
                ->where('is_active', true)
                ->where('category', 'benih'))
            ->get()
            ->groupBy('item_id')
            ->map(fn ($rows) => $rows->pluck('quantity', 'warehouse_id'))
            ->toArray();

        return view('seed-growings.create', [
            'farmers' => Farmer::where('is_active', true)->orderBy('name')->get(),
            'lands' => Land::with('farmer')->where('is_active', true)->orderBy('name')->get(),
            'riceVarieties' => RiceVariety::where('is_active', true)->orderBy('name')->get(),
            'seedClasses' => SeedClass::where('is_active', true)->orderBy('name')->get(),
            'sourceSeedItems' => Item::with(['riceVariety', 'seedClass'])
                ->where('is_active', true)
                ->where('category', 'benih')
                ->whereNotNull('rice_variety_id')
                ->orderBy('name')
                ->get(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'statuses' => self::STATUSES,
            'usedFieldAreas' => $usedFieldAreas,
            'sourceSeedStocks' => $sourceSeedStocks,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $seasonYear = $this->resolveSeasonYear($request);

        $request->merge([
            'field_number' => $request->filled('field_number')
                ? Str::upper(preg_replace('/\s+/', ' ', trim((string) $request->input('field_number'))))
                : null,
            'lot_number' => $request->filled('lot_number')
                ? Str::upper(preg_replace('/\s+/', ' ', trim((string) $request->input('lot_number'))))
                : null,
            'season_year' => $seasonYear,
        ]);

        $validator = Validator::make($request->all(), [
            'number' => ['nullable', 'string', 'max:100', 'unique:seed_growings,number'],
            'farmer_id' => ['required', 'uuid', 'exists:farmers,id'],
            'land_id' => ['required', 'uuid', 'exists:lands,id'],
            'field_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('seed_growings', 'field_number')
                    ->where(fn ($query) => $query
                        ->where('land_id', $request->input('land_id'))
                        ->where('season_year', $seasonYear)),
            ],
            'lot_number' => ['nullable', 'string', 'max:100'],
            'season_year' => ['required', 'integer', 'between:2000,2100'],
            'rice_variety_id' => ['required', 'uuid', 'exists:rice_varieties,id'],
            'seed_class_id' => ['required', 'uuid', 'exists:seed_classes,id'],
            'source_seed_item_id' => ['required', 'uuid', 'exists:items,id'],
            'source_seed_warehouse_id' => ['required', 'uuid', 'exists:warehouses,id'],
            'source_seed_quantity' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'field_area' => ['required', 'numeric', 'min:0.01', 'max:5'],
            'sowing_date' => ['nullable', 'date'],
            'planting_date' => ['nullable', 'date'],
            'harvest_date' => ['nullable', 'date'],
            'status' => ['required', 'string', Rule::in(array_keys(self::STATUSES))],
            'notes' => ['nullable', 'string'],
        ], [
            'field_number.unique' => 'No lapangan ini sudah digunakan pada lahan dan tahun musim yang sama.',
            'field_area.max' => 'Luas no lapangan maksimal 5 hektar.',
        ]);

        $validator->after(function ($validator) use ($request, $seasonYear) {
            if ($request->filled(['farmer_id', 'land_id'])) {
                $land = Land::whereKey($request->input('land_id'))->first();
                $landMatchesFarmer = $land?->farmer_id === $request->input('farmer_id');

                if (! $landMatchesFarmer) {
                    $validator->errors()->add('land_id', 'Lahan yang dipilih tidak sesuai dengan petani.');
                }

                if ($land && $request->filled('field_area') && $land->area_size !== null) {
                    $usedArea = SeedGrowing::where('land_id', $land->id)
                        ->where('season_year', $seasonYear)
                        ->sum('field_area');
                    $requestedArea = (float) $request->input('field_area');

                    if (($usedArea + $requestedArea) > (float) $land->area_size) {
                        $validator->errors()->add(
                            'field_area',
                            'Total luas no lapangan pada lahan dan tahun musim ini tidak boleh melebihi luas lahan.'
                        );
                    }
                }
            }

            if ($request->filled(['rice_variety_id', 'source_seed_item_id'])) {
                $item = Item::whereKey($request->input('source_seed_item_id'))->first();

                if (! $item || $item->category !== 'benih' || $item->rice_variety_id !== $request->input('rice_variety_id')) {
                    $validator->errors()->add('source_seed_item_id', 'Benih sumber tidak sesuai dengan varietas yang dipilih.');
                }
            }

            if ($request->filled(['source_seed_item_id', 'source_seed_warehouse_id', 'source_seed_quantity'])) {
                $stockQuantity = Stock::where('item_id', $request->input('source_seed_item_id'))
                    ->where('warehouse_id', $request->input('source_seed_warehouse_id'))
                    ->value('quantity') ?? 0;

                if ((float) $request->input('source_seed_quantity') > (float) $stockQuantity) {
                    $validator->errors()->add(
                        'source_seed_quantity',
                        'Jumlah benih sumber tidak boleh melebihi stok tersedia.'
                    );
                }
            }
        });

        $validated = $validator->validate();

        $seedGrowing = DB::transaction(function () use ($validated, $request) {
            $plantingDate = filled($validated['planting_date'] ?? null)
                ? Carbon::parse($validated['planting_date'])
                : null;
            $harvestDate = $validated['harvest_date'] ?? $plantingDate?->copy()->addDays(105)->toDateString();
            $sourceSeedQuantity = (float) $validated['source_seed_quantity'];
            $stock = Stock::lockForUpdate()
                ->where('item_id', $validated['source_seed_item_id'])
                ->where('warehouse_id', $validated['source_seed_warehouse_id'])
                ->first();

            if (! $stock || (float) $stock->quantity < $sourceSeedQuantity) {
                abort(422, 'Stok benih sumber tidak mencukupi.');
            }

            $seedGrowing = SeedGrowing::create([
                ...$validated,
                'number' => $validated['number'] ?: $this->generateNumber(),
                'status' => 'draft',
                'preliminary_date' => $plantingDate?->copy()->addDays(self::INSPECTION_STAGES['pendahuluan']['offset'])->toDateString(),
                'field_inspection_1_date' => $plantingDate?->copy()->addDays(self::INSPECTION_STAGES['pl1']['offset'])->toDateString(),
                'field_inspection_2_date' => $plantingDate?->copy()->addDays(self::INSPECTION_STAGES['pl2']['offset'])->toDateString(),
                'field_inspection_3_date' => $plantingDate?->copy()->addDays(self::INSPECTION_STAGES['pl3']['offset'])->toDateString(),
                'harvest_date' => $harvestDate,
                'created_by' => $request->user()?->id,
            ]);

            foreach (self::INSPECTION_STAGES as $stage => $config) {
                $seedGrowing->inspections()->create([
                    'stage' => $stage,
                    'planned_date' => $plantingDate?->copy()->addDays($config['offset'])->toDateString(),
                    'status' => 'terjadwal',
                    'cost' => 0,
                    'created_by' => $request->user()?->id,
                ]);
            }

            $balanceAfter = (float) $stock->quantity - $sourceSeedQuantity;
            $stock->update(['quantity' => $balanceAfter]);

            StockMovement::create([
                'stock_id' => $stock->id,
                'item_id' => $stock->item_id,
                'warehouse_id' => $stock->warehouse_id,
                'movement_date' => $validated['sowing_date']
                    ?? $validated['planting_date']
                    ?? now()->toDateString(),
                'type' => 'seed_growing_usage',
                'quantity_in' => 0,
                'quantity_out' => $sourceSeedQuantity,
                'balance_after' => $balanceAfter,
                'reference_type' => 'seed_growing',
                'reference_id' => $seedGrowing->id,
                'reference_number' => $seedGrowing->number,
                'notes' => 'Pemakaian benih sumber untuk penangkaran '.$seedGrowing->field_number,
                'created_by' => $request->user()?->id,
            ]);

            return $seedGrowing;
        });

        return redirect()
            ->route('seed-growings.show', $seedGrowing)
            ->with('success', 'Data penangkaran benih berhasil disimpan.');
    }

    public function show(SeedGrowing $seedGrowing): View
    {
        $seedGrowing->load([
            'farmer',
            'land',
            'riceVariety',
            'seedClass',
            'sourceSeedItem.riceVariety',
            'sourceSeedItem.seedClass',
            'sourceSeedWarehouse',
            'inspections',
            'harvest.harvestItem',
            'harvest.harvestWarehouse',
            'creator',
        ]);

        return view('seed-growings.show', [
            'seedGrowing' => $seedGrowing,
            'statuses' => self::STATUSES,
            'inspectionStages' => self::INSPECTION_STAGES,
            'inspectionStatuses' => self::INSPECTION_STATUSES,
            'harvestStatuses' => SeedGrowingHarvestController::statuses(),
            'harvestMaterialStates' => SeedGrowingHarvestController::materialStates(),
            'harvestItems' => Item::where('is_active', true)
                ->whereIn('category', ['gabah', 'benih'])
                ->where('rice_variety_id', $seedGrowing->rice_variety_id)
                ->orderBy('name')
                ->get(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function updateStatus(Request $request, SeedGrowing $seedGrowing): RedirectResponse
    {
        if ($seedGrowing->status !== 'draft') {
            return redirect()
                ->route('seed-growings.show', $seedGrowing)
                ->withErrors(['status' => 'Penangkaran hanya bisa dibatalkan saat status masih Draft.']);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(['batal'])],
        ]);

        DB::transaction(function () use ($request, $seedGrowing, $validated) {
            $stock = Stock::lockForUpdate()->firstOrCreate(
                [
                    'item_id' => $seedGrowing->source_seed_item_id,
                    'warehouse_id' => $seedGrowing->source_seed_warehouse_id,
                ],
                ['quantity' => 0]
            );
            $sourceSeedQuantity = (float) $seedGrowing->source_seed_quantity;
            $balanceAfter = (float) $stock->quantity + $sourceSeedQuantity;

            $stock->update(['quantity' => $balanceAfter]);

            StockMovement::create([
                'stock_id' => $stock->id,
                'item_id' => $stock->item_id,
                'warehouse_id' => $stock->warehouse_id,
                'movement_date' => now()->toDateString(),
                'type' => 'seed_growing_cancel',
                'quantity_in' => $sourceSeedQuantity,
                'quantity_out' => 0,
                'balance_after' => $balanceAfter,
                'reference_type' => 'seed_growing',
                'reference_id' => $seedGrowing->id,
                'reference_number' => $seedGrowing->number,
                'notes' => 'Pengembalian benih sumber dari pembatalan penangkaran '.$seedGrowing->field_number,
                'created_by' => $request->user()?->id,
            ]);

            $seedGrowing->update([
                'status' => $validated['status'],
            ]);
        });

        return redirect()
            ->route('seed-growings.show', $seedGrowing)
            ->with('success', 'Status penangkaran benih berhasil diperbarui.');
    }

    public static function inspectionStatuses(): array
    {
        return self::INSPECTION_STATUSES;
    }

    public static function syncStatusFromProcess(SeedGrowing $seedGrowing): void
    {
        if ($seedGrowing->status === 'batal') {
            return;
        }

        $seedGrowing->loadMissing(['inspections', 'harvest']);
        $harvest = $seedGrowing->harvest;
        $status = 'draft';

        if ($harvest) {
            $status = $harvest->status === 'selesai' ? 'selesai' : 'panen';
        } elseif ($seedGrowing->inspections->contains(fn ($inspection) => $inspection->status === 'selesai')) {
            $status = 'berjalan';
        }

        if ($seedGrowing->status !== $status) {
            $seedGrowing->forceFill(['status' => $status])->save();
        }
    }

    private function resolveSeasonYear(Request $request): int
    {
        $date = $request->input('planting_date')
            ?: $request->input('sowing_date')
            ?: $request->input('harvest_date');

        return $date ? Carbon::parse($date)->year : now()->year;
    }

    private function generateNumber(): string
    {
        do {
            $number = 'PKR-'.now()->format('Ymd').'-'.Str::upper(Str::random(4));
        } while (SeedGrowing::where('number', $number)->exists());

        return $number;
    }
}
