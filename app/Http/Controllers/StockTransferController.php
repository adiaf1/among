<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StockTransferController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $perPage = in_array((int) $request->input('per_page', 10), [10, 25, 50, 100], true)
            ? (int) $request->input('per_page', 10)
            : 10;

        $stockTransfers = StockTransfer::query()
            ->with(['sourceWarehouse', 'destinationWarehouse'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('number', 'like', "%{$search}%")
                        ->orWhereHas('sourceWarehouse', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        })
                        ->orWhereHas('destinationWarehouse', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('created_at')
            ->paginate($perPage)
            ->withQueryString();

        return view('stock-transfers.index', compact('stockTransfers', 'search', 'perPage'));
    }

    public function create(): View
    {
        return view('stock-transfers.create', [
            'nextNumber' => $this->generateNumber(),
            'items' => Item::where('is_active', true)->orderBy('name')->get(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'availableStocks' => Stock::query()
                ->with('item')
                ->where('quantity', '>', 0)
                ->orderBy('lot_number')
                ->get()
                ->map(fn (Stock $stock) => [
                    'id' => $stock->id,
                    'warehouse_id' => $stock->warehouse_id,
                    'item_id' => $stock->item_id,
                    'lot_number' => $stock->lot_number,
                    'quantity' => (float) $stock->quantity,
                    'unit' => strtoupper($stock->item?->unit ?? ''),
                ])
                ->values(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'items' => collect($request->input('items', []))
                ->filter(fn (array $item) => filled($item['item_id'] ?? null)
                    || filled($item['quantity'] ?? null))
                ->values()
                ->all(),
        ]);

        $validated = $request->validate([
            'number' => ['nullable', 'string', 'max:100', 'unique:stock_transfers,number'],
            'transfer_date' => ['required', 'date'],
            'source_warehouse_id' => ['required', 'uuid', 'exists:warehouses,id'],
            'destination_warehouse_id' => ['required', 'uuid', 'exists:warehouses,id', 'different:source_warehouse_id'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'uuid', 'exists:items,id'],
            'items.*.stock_id' => ['nullable', 'uuid', 'exists:stocks,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
        ]);

        $stockTransfer = DB::transaction(function () use ($validated, $request) {
            $number = $validated['number'] ?: $this->generateNumber();

            $stockTransfer = StockTransfer::create([
                'number' => $number,
                'transfer_date' => $validated['transfer_date'],
                'source_warehouse_id' => $validated['source_warehouse_id'],
                'destination_warehouse_id' => $validated['destination_warehouse_id'],
                'notes' => $validated['notes'] ?? null,
                'created_by' => $request->user()?->id,
            ]);

            foreach ($validated['items'] as $detail) {
                $quantity = (float) $detail['quantity'];
                $item = Item::findOrFail($detail['item_id']);

                $sourceStockQuery = Stock::where('item_id', $detail['item_id'])
                    ->where('warehouse_id', $validated['source_warehouse_id']);

                if (filled($detail['stock_id'] ?? null)) {
                    $sourceStockQuery->where('id', $detail['stock_id']);
                }

                $sourceStock = $sourceStockQuery
                    ->lockForUpdate()
                    ->first();

                if (! $sourceStock || (float) $sourceStock->quantity < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$item->name} di gudang asal tidak cukup.",
                    ]);
                }

                $destinationStock = Stock::lockForUpdate()->firstOrCreate(
                    [
                        'item_id' => $detail['item_id'],
                        'warehouse_id' => $validated['destination_warehouse_id'],
                        'lot_number' => $sourceStock->lot_number,
                    ],
                    ['quantity' => 0]
                );

                $transferItem = $stockTransfer->items()->create([
                    'item_id' => $detail['item_id'],
                    'lot_number' => $sourceStock->lot_number,
                    'quantity' => $quantity,
                ]);

                $sourceBalanceAfter = (float) $sourceStock->quantity - $quantity;
                $sourceStock->update(['quantity' => $sourceBalanceAfter]);

                StockMovement::create([
                    'stock_id' => $sourceStock->id,
                    'item_id' => $sourceStock->item_id,
                    'warehouse_id' => $sourceStock->warehouse_id,
                    'movement_date' => $validated['transfer_date'],
                    'type' => 'transfer_out',
                    'quantity_in' => 0,
                    'quantity_out' => $quantity,
                    'balance_after' => $sourceBalanceAfter,
                    'reference_type' => 'stock_transfer',
                    'reference_id' => $transferItem->id,
                    'reference_number' => $number,
                    'notes' => $validated['notes'] ?? null,
                    'created_by' => $request->user()?->id,
                ]);

                $destinationBalanceAfter = (float) $destinationStock->quantity + $quantity;
                $destinationStock->update(['quantity' => $destinationBalanceAfter]);

                StockMovement::create([
                    'stock_id' => $destinationStock->id,
                    'item_id' => $destinationStock->item_id,
                    'warehouse_id' => $destinationStock->warehouse_id,
                    'movement_date' => $validated['transfer_date'],
                    'type' => 'transfer_in',
                    'quantity_in' => $quantity,
                    'quantity_out' => 0,
                    'balance_after' => $destinationBalanceAfter,
                    'reference_type' => 'stock_transfer',
                    'reference_id' => $transferItem->id,
                    'reference_number' => $number,
                    'notes' => $validated['notes'] ?? null,
                    'created_by' => $request->user()?->id,
                ]);
            }

            return $stockTransfer;
        });

        return redirect()
            ->route('stock-transfers.show', $stockTransfer)
            ->with('success', 'Mutasi stok berhasil disimpan.');
    }

    public function show(StockTransfer $stockTransfer): View
    {
        $stockTransfer->load(['sourceWarehouse', 'destinationWarehouse', 'items.item', 'creator']);

        return view('stock-transfers.show', compact('stockTransfer'));
    }

    private function generateNumber(): string
    {
        do {
            $number = 'MT-'.now()->format('Ymd').'-'.Str::upper(Str::random(4));
        } while (StockTransfer::where('number', $number)->exists());

        return $number;
    }
}
