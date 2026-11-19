<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\RiceVariety;
use App\Models\SeedClass;
use App\Models\SeedGrowing;
use App\Models\SeedProduction;
use App\Models\SeedProductionStep;
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

    public const LAB_RESULTS = [
        'menunggu' => 'Menunggu Hasil',
        'lulus' => 'Lulus Uji Lab',
        'tidak_lulus' => 'Tidak Lulus',
    ];

    private const STEPS = [
        ['stage' => 'pengovenan', 'label' => 'Pengovenan', 'offset' => 0],
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
            'nextNumber' => $this->generateNumber(),
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
        $stocks = $this->availableStocks();

        $seedProduction->load([
            'riceVariety',
            'seedClass',
            'outputItem',
            'outputWarehouse',
            'outputStock',
            'inputs.item',
            'inputs.warehouse',
            'steps',
            'creator',
        ]);

        return view('seed-productions.show', [
            'seedProduction' => $seedProduction,
            'statuses' => self::STATUSES,
            'labResults' => self::LAB_RESULTS,
            'inputRoles' => self::INPUT_ROLES,
            'stocks' => $stocks,
            'additionalStockOptions' => $stocks->map(fn ($stock) => [
                'value' => $stock->id,
                'label' => ($stock->item?->code ?? '-').' - '.($stock->item?->name ?? '-').' | '.($stock->warehouse?->name ?? '-').' | Lot '.($stock->lot_number ?: '-').' | Stok '.number_format((float) $stock->quantity, 2, ',', '.').' '.strtoupper($stock->item?->unit ?? ''),
                'rice_variety_id' => $stock->item?->rice_variety_id,
            ])->values(),
            'outputItems' => Item::where('is_active', true)
                ->where('category', 'benih')
                ->when($seedProduction->rice_variety_id, fn ($q) => $q->where('rice_variety_id', $seedProduction->rice_variety_id))
                ->orderBy('name')
                ->get(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'stepStatuses' => SeedProductionStepController::statuses(),
            'stepCostTypes' => SeedProductionStepController::costTypes(),
            'stepPositions' => SeedProductionStepController::positions(),
            'stepSuggestions' => SeedProductionStep::query()
                ->select('label')
                ->distinct()
                ->orderBy('label')
                ->pluck('label'),
        ]);
    }

    public function updateCertification(Request $request, SeedProduction $seedProduction): RedirectResponse
    {
        if ($seedProduction->status === 'batal') {
            return redirect()
                ->route('seed-productions.show', $seedProduction)
                ->withErrors(['lab_result' => 'Data uji lab & sertifikasi tidak dapat disimpan karena produksi sudah dibatalkan.']);
        }

        $validated = $request->validate([
            'lab_test_date' => ['nullable', 'date'],
            'moisture_content' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'purity_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'germination_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lab_result' => ['nullable', 'string', Rule::in(array_keys(self::LAB_RESULTS))],
            'certificate_number' => ['nullable', 'string', 'max:100'],
            'label_serial_range' => ['nullable', 'string', 'max:150'],
            'label_count' => ['nullable', 'integer', 'min:1', 'max:10000000'],
            'label_expiry_date' => ['nullable', 'date'],
            'certification_notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($seedProduction, $validated, $request) {
            $seedProduction->update([
                'lab_test_date' => $validated['lab_test_date'] ?? null,
                'moisture_content' => $validated['moisture_content'] ?? null,
                'purity_percentage' => $validated['purity_percentage'] ?? null,
                'germination_rate' => $validated['germination_rate'] ?? null,
                'lab_result' => $validated['lab_result'] ?? null,
                'certificate_number' => $validated['certificate_number'] ? trim((string) $validated['certificate_number']) : null,
                'label_serial_range' => $validated['label_serial_range'] ? trim((string) $validated['label_serial_range']) : null,
                'label_count' => $validated['label_count'] ?? null,
                'label_expiry_date' => $validated['label_expiry_date'] ?? null,
                'certification_notes' => $validated['certification_notes'] ?? null,
            ]);

            if (($validated['lab_result'] ?? null) === 'lulus') {
                $sampleStep = $seedProduction->steps()->where('stage', 'pengambilan_sampel')->first();
                if ($sampleStep && $sampleStep->status !== 'selesai') {
                    $sampleStep->update([
                        'actual_date' => $sampleStep->actual_date ?? ($validated['lab_test_date'] ?? now()->toDateString()),
                        'status' => 'selesai',
                        'updated_by' => $request->user()?->id,
                    ]);
                }
            }

            if (filled($validated['label_serial_range'] ?? null) || filled($validated['label_count'] ?? null)) {
                $labelStep = $seedProduction->steps()->where('stage', 'cetak_label')->first();
                if ($labelStep && $labelStep->status !== 'selesai') {
                    $labelStep->update([
                        'actual_date' => $labelStep->actual_date ?? ($validated['lab_test_date'] ?? now()->toDateString()),
                        'status' => 'selesai',
                        'updated_by' => $request->user()?->id,
                    ]);
                }
            }
        });

        return redirect()
            ->route('seed-productions.show', $seedProduction)
            ->with('success', 'Data uji laboratorium & label sertifikasi BPSB berhasil disimpan.');
    }

    public function updateOutput(Request $request, SeedProduction $seedProduction): RedirectResponse
    {
        if ($seedProduction->status === 'batal') {
            return redirect()
                ->route('seed-productions.show', $seedProduction)
                ->withErrors(['output_item_id' => 'Hasil produksi tidak bisa disimpan karena produksi sudah dibatalkan.']);
        }

        $validated = $request->validate([
            'output_item_id' => [
                'required',
                'uuid',
                Rule::exists('items', 'id')
                    ->where('is_active', true)
                    ->where('category', 'benih')
                    ->when($seedProduction->rice_variety_id, fn ($rule) => $rule->where('rice_variety_id', $seedProduction->rice_variety_id)),
            ],
            'output_warehouse_id' => ['required', 'uuid', 'exists:warehouses,id'],
            'lot_number' => ['nullable', 'string', 'max:100'],
            'output_date' => ['required', 'date'],
            'output_quantity' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'status' => ['required', 'string', Rule::in(['proses', 'siap_salur'])],
        ]);

        $lotNumber = $request->has('lot_number')
            ? ($request->filled('lot_number')
                ? Str::upper(preg_replace('/\s+/', ' ', trim((string) $request->input('lot_number'))))
                : null)
            : $seedProduction->lot_number;

        DB::transaction(function () use ($seedProduction, $validated, $lotNumber, $request) {
            $oldItemId = $seedProduction->output_item_id;
            $oldWarehouseId = $seedProduction->output_warehouse_id;
            $oldLotNumber = $seedProduction->lot_number;
            $oldQuantity = (float) ($seedProduction->output_quantity ?? 0);
            $newQuantity = (float) $validated['output_quantity'];

            $newStock = Stock::lockForUpdate()->firstOrCreate(
                [
                    'item_id' => $validated['output_item_id'],
                    'warehouse_id' => $validated['output_warehouse_id'],
                    'lot_number' => $lotNumber,
                ],
                ['quantity' => 0]
            );

            if ($oldQuantity > 0 && $oldItemId && $oldWarehouseId) {
                $sameStock = $oldItemId === $validated['output_item_id']
                    && $oldWarehouseId === $validated['output_warehouse_id']
                    && $oldLotNumber === $lotNumber;

                if ($sameStock) {
                    $this->applyOutputStockDifference($newStock, $newQuantity - $oldQuantity, $validated['output_date'], $seedProduction, $request);
                } else {
                    $oldStock = Stock::lockForUpdate()
                        ->where('item_id', $oldItemId)
                        ->where('warehouse_id', $oldWarehouseId)
                        ->where('lot_number', $oldLotNumber)
                        ->first();

                    if ($oldStock && $oldQuantity > 0) {
                        $this->applyOutputStockDifference($oldStock, -$oldQuantity, $validated['output_date'], $seedProduction, $request);
                    }

                    $this->applyOutputStockDifference($newStock, $newQuantity, $validated['output_date'], $seedProduction, $request);
                }
            } else {
                $this->applyOutputStockDifference($newStock, $newQuantity, $validated['output_date'], $seedProduction, $request);
            }

            $outputItem = Item::find($validated['output_item_id']);

            $seedProduction->forceFill([
                'output_item_id' => $validated['output_item_id'],
                'output_warehouse_id' => $validated['output_warehouse_id'],
                'output_stock_id' => $newStock->id,
                'lot_number' => $lotNumber,
                'output_date' => $validated['output_date'],
                'output_quantity' => $newQuantity,
                'unit' => $outputItem?->unit ?? $seedProduction->unit,
                'status' => $validated['status'],
            ])->save();

            if ($validated['status'] === 'siap_salur') {
                $readyStep = $seedProduction->steps()->where('stage', 'siap_salur')->first();
                if ($readyStep) {
                    $readyStep->update([
                        'actual_date' => $readyStep->actual_date ?? $validated['output_date'],
                        'quantity' => $readyStep->quantity ?? $newQuantity,
                        'status' => 'selesai',
                        'updated_by' => $request->user()?->id,
                    ]);
                }
            }
        });

        return redirect()
            ->route('seed-productions.show', $seedProduction)
            ->with('success', 'Hasil akhir produksi benih berhasil disimpan dan stok gudang telah diperbarui.');
    }

    private function applyOutputStockDifference(
        Stock $stock,
        float $difference,
        string $movementDate,
        SeedProduction $seedProduction,
        Request $request
    ): void {
        if (abs($difference) < 0.00001) {
            return;
        }

        $balanceAfter = (float) $stock->quantity + $difference;

        if ($balanceAfter < 0) {
            throw ValidationException::withMessages([
                'output_quantity' => 'Stok hasil produksi sudah terpakai dan tidak mencukupi untuk dikoreksi.',
            ]);
        }

        $stock->update(['quantity' => $balanceAfter]);

        StockMovement::create([
            'stock_id' => $stock->id,
            'item_id' => $stock->item_id,
            'warehouse_id' => $stock->warehouse_id,
            'movement_date' => $movementDate,
            'type' => 'seed_production_output',
            'quantity_in' => $difference > 0 ? $difference : 0,
            'quantity_out' => $difference < 0 ? abs($difference) : 0,
            'balance_after' => $balanceAfter,
            'reference_type' => 'seed_production',
            'reference_id' => $seedProduction->id,
            'reference_number' => $seedProduction->number,
            'notes' => 'Hasil produksi benih jadi '.$seedProduction->number,
            'created_by' => $request->user()?->id,
        ]);
    }

    public function storeInput(Request $request, SeedProduction $seedProduction): RedirectResponse
    {
        if (in_array($seedProduction->status, ['batal', 'siap_salur'], true)) {
            throw ValidationException::withMessages([
                'stock_id' => 'Bahan produksi tidak bisa ditambahkan pada produksi yang sudah batal atau siap salur.',
            ]);
        }

        $validated = $request->validate([
            'movement_date' => ['required', 'date'],
            'stock_id' => ['required', 'uuid', 'exists:stocks,id'],
            'role' => ['required', 'string', Rule::in(array_keys(self::INPUT_ROLES))],
            'quantity' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($seedProduction, $validated, $request) {
            $stock = Stock::with('item')
                ->whereKey($validated['stock_id'])
                ->lockForUpdate()
                ->firstOrFail();
            $quantity = (float) $validated['quantity'];

            if ((float) $stock->quantity < $quantity) {
                throw ValidationException::withMessages([
                    'stock_id' => "Stok {$stock->item?->name} tidak mencukupi.",
                ]);
            }

            if (
                $validated['role'] === 'bahan_utama'
                && filled($seedProduction->rice_variety_id)
                && $stock->item?->rice_variety_id !== $seedProduction->rice_variety_id
            ) {
                throw ValidationException::withMessages([
                    'stock_id' => 'Bahan utama harus sesuai dengan varietas produksi yang dipilih.',
                ]);
            }

            $input = $seedProduction->inputs()->create([
                'stock_id' => $stock->id,
                'item_id' => $stock->item_id,
                'warehouse_id' => $stock->warehouse_id,
                'role' => $validated['role'],
                'quantity' => $quantity,
                'unit' => $stock->item?->unit ?? $seedProduction->unit,
                'notes' => $validated['notes'] ?? null,
            ]);

            $balanceAfter = (float) $stock->quantity - $quantity;
            $stock->update(['quantity' => $balanceAfter]);

            StockMovement::create([
                'stock_id' => $stock->id,
                'item_id' => $stock->item_id,
                'warehouse_id' => $stock->warehouse_id,
                'movement_date' => $validated['movement_date'],
                'type' => 'seed_production_input',
                'quantity_in' => 0,
                'quantity_out' => $quantity,
                'balance_after' => $balanceAfter,
                'reference_type' => 'seed_production',
                'reference_id' => $input->id,
                'reference_number' => $seedProduction->number,
                'notes' => 'Tambahan bahan produksi '.$seedProduction->number,
                'created_by' => $request->user()?->id,
            ]);
        });

        return redirect()
            ->route('seed-productions.show', $seedProduction)
            ->with('success', 'Bahan produksi berhasil ditambahkan.');
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
