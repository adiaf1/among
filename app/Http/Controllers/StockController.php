<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $itemId = $request->string('item_id')->toString();
        $warehouseId = $request->string('warehouse_id')->toString();

        $stocks = Stock::query()
            ->with(['item', 'warehouse'])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('item', function ($query) use ($search) {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                })->orWhereHas('warehouse', function ($query) use ($search) {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when($itemId, fn ($query) => $query->where('item_id', $itemId))
            ->when($warehouseId, fn ($query) => $query->where('warehouse_id', $warehouseId))
            ->join('items', 'stocks.item_id', '=', 'items.id')
            ->join('warehouses', 'stocks.warehouse_id', '=', 'warehouses.id')
            ->orderBy('items.name')
            ->orderBy('warehouses.name')
            ->select('stocks.*')
            ->paginate(10)
            ->withQueryString();

        return view('stocks.index', [
            'stocks' => $stocks,
            'search' => $search,
            'itemId' => $itemId,
            'warehouseId' => $warehouseId,
            'items' => Item::where('is_active', true)->orderBy('name')->get(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('stocks.create', [
            'items' => Item::where('is_active', true)->orderBy('name')->get(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'item_id' => ['required', 'uuid', 'exists:items,id'],
            'warehouse_id' => ['required', 'uuid', 'exists:warehouses,id'],
            'quantity' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'movement_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $stock = DB::transaction(function () use ($validated, $request) {
            $stock = Stock::lockForUpdate()->firstOrCreate(
                [
                    'item_id' => $validated['item_id'],
                    'warehouse_id' => $validated['warehouse_id'],
                ],
                ['quantity' => 0]
            );

            $oldQuantity = (float) $stock->quantity;
            $newQuantity = (float) $validated['quantity'];
            $difference = $newQuantity - $oldQuantity;

            $stock->update(['quantity' => $newQuantity]);

            StockMovement::create([
                'stock_id' => $stock->id,
                'item_id' => $stock->item_id,
                'warehouse_id' => $stock->warehouse_id,
                'movement_date' => $validated['movement_date'],
                'type' => 'adjustment',
                'quantity_in' => $difference > 0 ? $difference : 0,
                'quantity_out' => $difference < 0 ? abs($difference) : 0,
                'balance_after' => $newQuantity,
                'reference_type' => 'stock_adjustment',
                'reference_number' => 'ADJ-'.now()->format('YmdHis'),
                'notes' => $validated['notes'] ?? null,
                'created_by' => $request->user()?->id,
            ]);

            return $stock;
        });

        return redirect()
            ->route('stocks.show', $stock)
            ->with('success', 'Stok berhasil disesuaikan.');
    }

    public function show(Stock $stock): View
    {
        $stock->load(['item', 'warehouse']);

        $movements = $stock->movements()
            ->with('creator')
            ->latest('movement_date')
            ->latest()
            ->paginate(10);

        return view('stocks.show', compact('stock', 'movements'));
    }
}
