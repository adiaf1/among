<?php

namespace App\Http\Controllers;

use App\Models\RiceVariety;
use App\Models\SeedClass;
use App\Models\SeedGrowing;
use App\Models\SeedProduction;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SeedProductionController extends Controller
{
    private const STATUSES = [
        'proses' => 'Proses',
        'siap_salur' => 'Siap Salur',
        'batal' => 'Batal',
    ];

    private const INPUT_ROLES = [
        'bahan_utama' => 'Bahan Utama',
        'pendukung' => 'Bahan Pendukung',
    ];

    private const STEPS = [
        ['stage' => 'pengovenan', 'label' => 'Pengovenan', 'offset' => 0],
        ['stage' => 'pendinginan_benih', 'label' => 'Pendinginan Benih', 'offset' => 1],
        ['stage' => 'pengipasan_blower', 'label' => 'Pengipasan / Blower', 'offset' => 1],
        ['stage' => 'penyusunan_lot', 'label' => 'Penyusunan Barang per Lot', 'offset' => 2],
        ['stage' => 'penyimpanan_barang', 'label' => 'Penyimpanan Barang', 'offset' => 2],
        ['stage' => 'pengambilan_sampel', 'label' => 'Pengambilan Sampel Uji Lab', 'offset' => 3],
        ['stage' => 'cetak_label', 'label' => 'Cetak Label', 'offset' => 7],
        ['stage' => 'packing', 'label' => 'Packing', 'offset' => 8],
        ['stage' => 'siap_salur', 'label' => 'Siap Salur', 'offset' => 9],
    ];

    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();

        $seedProductions = SeedProduction::query()
            ->with(['riceVariety', 'seedClass', 'outputWarehouse'])
            ->when($status && array_key_exists($status, self::STATUSES), fn ($query) => $query->where('status', $status))
            ->when($search, function ($query) use ($search) {
                $query->where('number', 'like', "%{$search}%")
                    ->orWhere('lot_number', 'like', "%{$search}%")
                    ->orWhereHas('riceVariety', fn ($query) => $query->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                    ->orWhereHas('seedClass', fn ($query) => $query->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
            })
            ->latest('production_date')
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('seed-productions.index', [
            'seedProductions' => $seedProductions,
            'search' => $search,
            'status' => $status,
            'statuses' => self::STATUSES,
        ]);
    }

    public function create(): View
    {
        return view('seed-productions.create', [
            'riceVarieties' => RiceVariety::where('is_active', true)->orderBy('name')->get(),
            'seedClasses' => SeedClass::where('is_active', true)->orderBy('name')->get(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'stocks' => $this->availableStocks(),
            'harvestLots' => $this->harvestLots(),
            'inputRoles' => self::INPUT_ROLES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'lot_number' => $request->filled('lot_number')
                ? Str::upper(preg_replace('/\s+/', ' ', trim((string) $request->input('lot_number'))))
                : null,
            'inputs' => collect($request->input('inputs', []))
                ->filter(fn (array $input) => filled($input['stock_id'] ?? null) || filled($input['quantity'] ?? null))
                ->values()
                ->all(),
        ]);

        $validated = $request->validate([
            'number' => ['nullable', 'string', 'max:100', 'unique:seed_productions,number'],
            'production_date' => ['required', 'date'],
            'lot_number' => ['nullable', 'string', 'max:100'],
            'rice_variety_id' => ['nullable', 'uuid', 'exists:rice_varieties,id'],
            'seed_class_id' => ['nullable', 'uuid', 'exists:seed_classes,id'],
            'output_warehouse_id' => ['nullable', 'uuid', 'exists:warehouses,id'],
            'target_quantity' => ['nullable', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'unit' => ['required', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'inputs' => ['required', 'array', 'min:1'],
            'inputs.*.stock_id' => ['required', 'uuid', 'exists:stocks,id'],
            'inputs.*.role' => ['required', 'string', Rule::in(array_keys(self::INPUT_ROLES))],
            'inputs.*.quantity' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'inputs.*.notes' => ['nullable', 'string'],
        ]);

        if (! collect($validated['inputs'])->contains(fn ($input) => $input['role'] === 'bahan_utama')) {
            throw ValidationException::withMessages([
                'inputs' => 'Minimal satu bahan utama harus dipilih.',
            ]);
        }

        if (filled($validated['rice_variety_id'] ?? null)) {
            $stocks = Stock::with('item')
                ->whereIn('id', collect($validated['inputs'])->pluck('stock_id')->all())
                ->get()
                ->keyBy('id');

            foreach ($validated['inputs'] as $detail) {
                $stock = $stocks->get($detail['stock_id']);

                if ($detail['role'] === 'bahan_utama' && $stock?->item?->rice_variety_id !== $validated['rice_variety_id']) {
                    throw ValidationException::withMessages([
                        'inputs' => 'Bahan utama harus sesuai dengan varietas produksi yang dipilih.',
                    ]);
                }
            }
        }

        $seedProduction = DB::transaction(function () use ($validated, $request) {
            $number = ($validated['number'] ?? null) ?: $this->generateNumber();

            $seedProduction = SeedProduction::create([
                'number' => $number,
                'production_date' => $validated['production_date'],
                'lot_number' => $validated['lot_number'] ?? null,
                'rice_variety_id' => $validated['rice_variety_id'] ?? null,
                'seed_class_id' => $validated['seed_class_id'] ?? null,
                'output_warehouse_id' => $validated['output_warehouse_id'] ?? null,
                'target_quantity' => $validated['target_quantity'] ?? null,
                'unit' => $validated['unit'],
                'status' => 'proses',
                'notes' => $validated['notes'] ?? null,
                'created_by' => $request->user()?->id,
            ]);

            foreach ($validated['inputs'] as $detail) {
                $stock = Stock::with('item')
                    ->whereKey($detail['stock_id'])
                    ->lockForUpdate()
                    ->firstOrFail();
                $quantity = (float) $detail['quantity'];

                if ((float) $stock->quantity < $quantity) {
                    throw ValidationException::withMessages([
                        'inputs' => "Stok {$stock->item?->name} tidak mencukupi.",
                    ]);
                }

                $input = $seedProduction->inputs()->create([
                    'stock_id' => $stock->id,
                    'item_id' => $stock->item_id,
                    'warehouse_id' => $stock->warehouse_id,
                    'role' => $detail['role'],
                    'quantity' => $quantity,
                    'unit' => $stock->item?->unit ?? $validated['unit'],
                    'notes' => $detail['notes'] ?? null,
                ]);

                $balanceAfter = (float) $stock->quantity - $quantity;
                $stock->update(['quantity' => $balanceAfter]);

                StockMovement::create([
                    'stock_id' => $stock->id,
                    'item_id' => $stock->item_id,
                    'warehouse_id' => $stock->warehouse_id,
                    'movement_date' => $validated['production_date'],
                    'type' => 'seed_production_input',
                    'quantity_in' => 0,
                    'quantity_out' => $quantity,
                    'balance_after' => $balanceAfter,
                    'reference_type' => 'seed_production',
                    'reference_id' => $input->id,
                    'reference_number' => $number,
                    'notes' => 'Pemakaian bahan produksi '.$number,
                    'created_by' => $request->user()?->id,
                ]);
            }

            $productionDate = Carbon::parse($validated['production_date']);

            foreach (self::STEPS as $index => $step) {
                $seedProduction->steps()->create([
                    'sort_order' => $index + 1,
                    'stage' => $step['stage'],
                    'label' => $step['label'],
                    'planned_date' => $productionDate->copy()->addDays($step['offset'])->toDateString(),
                    'status' => 'terjadwal',
                ]);
            }

            return $seedProduction;
        });

        return redirect()
            ->route('seed-productions.show', $seedProduction)
            ->with('success', 'Produksi benih berhasil dibuat.');
    }

    public function show(SeedProduction $seedProduction): View
    {
        $seedProduction->load([
            'riceVariety',
            'seedClass',
            'outputWarehouse',
            'inputs.item',
            'inputs.warehouse',
            'steps',
            'creator',
        ]);

        return view('seed-productions.show', [
            'seedProduction' => $seedProduction,
            'statuses' => self::STATUSES,
            'inputRoles' => self::INPUT_ROLES,
            'stepStatuses' => SeedProductionStepController::statuses(),
        ]);
    }

    private function availableStocks()
    {
        return Stock::query()
            ->with(['item.riceVariety', 'item.seedClass', 'warehouse'])
            ->where('quantity', '>', 0)
            ->whereHas('item', fn ($query) => $query->where('is_active', true))
            ->orderByDesc('quantity')
            ->get();
    }

    private function harvestLots()
    {
        return SeedGrowing::query()
            ->with(['riceVariety', 'seedClass', 'harvest'])
            ->whereNotNull('lot_number')
            ->where('lot_number', '!=', '')
            ->whereHas('harvest')
            ->orderByDesc('harvest_date')
            ->orderBy('lot_number')
            ->get()
            ->unique('lot_number')
            ->values();
    }

    private function generateNumber(): string
    {
        do {
            $number = 'PRD-'.now()->format('Ymd').'-'.Str::upper(Str::random(4));
        } while (SeedProduction::where('number', $number)->exists());

        return $number;
    }
}
