<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\SeedGrowing;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SeedGrowingHarvestController extends Controller
{
    public function update(Request $request, SeedGrowing $seedGrowing): RedirectResponse
    {
        if ($seedGrowing->status === 'batal') {
            return redirect()
                ->route('seed-growings.show', $seedGrowing)
                ->withErrors(['status' => 'Panen tidak bisa disimpan karena penangkaran sudah batal.']);
        }

        $validated = $request->validate([
            'harvest_item_id' => [
                'required',
                'uuid',
                Rule::exists('items', 'id')
                    ->where('is_active', true)
                    ->where('rice_variety_id', $seedGrowing->rice_variety_id)
                    ->whereIn('category', ['gabah', 'benih']),
            ],
            'harvest_warehouse_id' => ['required', 'uuid', 'exists:warehouses,id'],
            'lot_number' => ['nullable', 'string', 'max:100'],
            'harvest_date' => ['required', 'date'],
            'harvested_quantity' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'unit' => ['required', 'string', 'max:50'],
            'material_state' => ['required', 'string', Rule::in(array_keys(self::materialStates()))],
            'status' => ['required', 'string', Rule::in(array_keys(self::statuses()))],
            'notes' => ['nullable', 'string'],
        ]);

        $lotNumber = $request->has('lot_number')
            ? ($request->filled('lot_number')
                ? Str::upper(preg_replace('/\s+/', ' ', trim((string) $request->input('lot_number'))))
                : null)
            : $seedGrowing->lot_number;

        $harvestAttributes = collect($validated)->except('lot_number')->all();

        DB::transaction(function () use ($harvestAttributes, $lotNumber, $request, $seedGrowing) {
            $harvest = $seedGrowing->harvest()->lockForUpdate()->first();
            $oldItemId = $harvest?->harvest_item_id;
            $oldWarehouseId = $harvest?->harvest_warehouse_id;
            $oldLotNumber = $seedGrowing->lot_number;
            $oldQuantity = (float) ($harvest?->harvested_quantity ?? 0);
            $newQuantity = (float) $harvestAttributes['harvested_quantity'];
            $newStock = Stock::lockForUpdate()->firstOrCreate(
                [
                    'item_id' => $harvestAttributes['harvest_item_id'],
                    'warehouse_id' => $harvestAttributes['harvest_warehouse_id'],
                    'lot_number' => $lotNumber,
                ],
                ['quantity' => 0]
            );

            if ($harvest && $oldItemId && $oldWarehouseId) {
                $sameStock = $oldItemId === $harvestAttributes['harvest_item_id']
                    && $oldWarehouseId === $harvestAttributes['harvest_warehouse_id']
                    && $oldLotNumber === $lotNumber;

                if ($sameStock) {
                    $this->applyStockDifference($newStock, $newQuantity - $oldQuantity, $harvestAttributes, $seedGrowing, $request);
                } else {
                    $oldStock = Stock::lockForUpdate()
                        ->where('item_id', $oldItemId)
                        ->where('warehouse_id', $oldWarehouseId)
                        ->where('lot_number', $oldLotNumber)
                        ->first();

                    if ($oldStock && $oldQuantity > 0) {
                        $this->applyStockDifference($oldStock, -$oldQuantity, $harvestAttributes, $seedGrowing, $request);
                    }

                    $this->applyStockDifference($newStock, $newQuantity, $harvestAttributes, $seedGrowing, $request);
                }
            } else {
                $this->applyStockDifference($newStock, $newQuantity, $harvestAttributes, $seedGrowing, $request);
            }

            $seedGrowing->harvest()->updateOrCreate(
                ['seed_growing_id' => $seedGrowing->id],
                [
                    ...$harvestAttributes,
                    'stock_id' => $newStock->id,
                    'updated_by' => $request->user()?->id,
                    'created_by' => $harvest?->created_by ?? $request->user()?->id,
                ]
            );

            $seedGrowing->forceFill([
                'harvest_date' => $harvestAttributes['harvest_date'],
                'lot_number' => $lotNumber,
            ])->save();
            SeedGrowingController::syncStatusFromProcess($seedGrowing->fresh(['inspections', 'harvest']));
        });


        return redirect()
            ->route('seed-growings.show', $seedGrowing)
            ->with('success', 'Data panen penangkaran berhasil disimpan.');
    }

    public static function statuses(): array
    {
        return [
            'panen' => 'Panen',
            'selesai' => 'Selesai',
        ];
    }

    public static function materialStates(): array
    {
        return [
            'basah' => 'Basah',
            'kering' => 'Kering',
        ];
    }

    private function applyStockDifference(
        Stock $stock,
        float $difference,
        array $validated,
        SeedGrowing $seedGrowing,
        Request $request
    ): void {
        if ($difference === 0.0) {
            return;
        }

        $oldQuantity = (float) $stock->quantity;
        $newQuantity = $oldQuantity + $difference;

        if ($newQuantity < 0) {
            throw ValidationException::withMessages([
                'harvested_quantity' => 'Stok hasil panen sudah terpakai, tidak bisa mengurangi data panen melebihi saldo stok.',
            ]);
        }

        $stock->update(['quantity' => $newQuantity]);

        StockMovement::create([
            'stock_id' => $stock->id,
            'item_id' => $stock->item_id,
            'warehouse_id' => $stock->warehouse_id,
            'movement_date' => $validated['harvest_date'],
            'type' => 'seed_growing_harvest',
            'quantity_in' => $difference > 0 ? $difference : 0,
            'quantity_out' => $difference < 0 ? abs($difference) : 0,
            'balance_after' => $newQuantity,
            'reference_type' => 'seed_growing_harvest',
            'reference_id' => $seedGrowing->id,
            'reference_number' => $seedGrowing->number,
            'notes' => 'Hasil panen penangkaran '.$seedGrowing->field_number,
            'created_by' => $request->user()?->id,
        ]);
    }
}
