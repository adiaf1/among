<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Purchase;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $purchases = Purchase::query()
            ->with('supplier')
            ->when($search, function ($query) use ($search) {
                $query->where('number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
            })
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('purchases.index', compact('purchases', 'search'));
    }

    public function create(): View
    {
        return view('purchases.create', [
            'suppliers' => Supplier::where('is_active', true)->orderBy('name')->get(),
            'items' => Item::where('is_active', true)->orderBy('name')->get(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'items' => collect($request->input('items', []))
                ->filter(fn (array $item) => filled($item['item_id'] ?? null)
                    || filled($item['warehouse_id'] ?? null)
                    || filled($item['quantity'] ?? null)
                    || filled($item['unit_price'] ?? null))
                ->values()
                ->all(),
        ]);

        $validated = $request->validate([
            'number' => ['nullable', 'string', 'max:100', 'unique:purchases,number'],
            'purchase_date' => ['required', 'date'],
            'supplier_id' => ['required', 'uuid', 'exists:suppliers,id'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'uuid', 'exists:items,id'],
            'items.*.warehouse_id' => ['required', 'uuid', 'exists:warehouses,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
        ]);

        $purchase = DB::transaction(function () use ($validated, $request) {
            $number = $validated['number'] ?: $this->generateNumber();
            $totalAmount = collect($validated['items'])->sum(
                fn (array $item) => (float) $item['quantity'] * (float) $item['unit_price']
            );

            $purchase = Purchase::create([
                'number' => $number,
                'purchase_date' => $validated['purchase_date'],
                'supplier_id' => $validated['supplier_id'],
                'total_amount' => $totalAmount,
                'notes' => $validated['notes'] ?? null,
                'created_by' => $request->user()?->id,
            ]);

            foreach ($validated['items'] as $detail) {
                $quantity = (float) $detail['quantity'];
                $unitPrice = (float) $detail['unit_price'];
                $subtotal = $quantity * $unitPrice;

                $purchaseItem = $purchase->items()->create([
                    'item_id' => $detail['item_id'],
                    'warehouse_id' => $detail['warehouse_id'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                $stock = Stock::lockForUpdate()->firstOrCreate(
                    [
                        'item_id' => $detail['item_id'],
                        'warehouse_id' => $detail['warehouse_id'],
                    ],
                    ['quantity' => 0]
                );

                $balanceAfter = (float) $stock->quantity + $quantity;
                $stock->update(['quantity' => $balanceAfter]);

                StockMovement::create([
                    'stock_id' => $stock->id,
                    'item_id' => $stock->item_id,
                    'warehouse_id' => $stock->warehouse_id,
                    'movement_date' => $validated['purchase_date'],
                    'type' => 'purchase',
                    'quantity_in' => $quantity,
                    'quantity_out' => 0,
                    'balance_after' => $balanceAfter,
                    'reference_type' => 'purchase',
                    'reference_id' => $purchaseItem->id,
                    'reference_number' => $number,
                    'notes' => $validated['notes'] ?? null,
                    'created_by' => $request->user()?->id,
                ]);
            }

            return $purchase;
        });

        return redirect()
            ->route('purchases.show', $purchase)
            ->with('success', 'Pembelian barang berhasil disimpan dan stok sudah bertambah.');
    }

    public function show(Purchase $purchase): View
    {
        $purchase->load(['supplier', 'items.item', 'items.warehouse', 'creator']);

        return view('purchases.show', compact('purchase'));
    }

    private function generateNumber(): string
    {
        do {
            $number = 'PO-'.now()->format('Ymd').'-'.Str::upper(Str::random(4));
        } while (Purchase::where('number', $number)->exists());

        return $number;
    }
}
