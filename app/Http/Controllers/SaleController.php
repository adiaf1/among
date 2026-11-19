<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SaleController extends Controller
{
    public const PAYMENT_STATUSES = [
        'lunas' => 'Lunas',
        'belum_lunas' => 'Belum Lunas',
    ];

    public const CUSTOMER_TYPES = [
        'perorangan' => 'Perorangan',
        'kios' => 'Kios',
        'distributor' => 'Distributor',
        'instansi' => 'Instansi',
    ];

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

    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $paymentStatus = $request->string('payment_status')->toString();
        $startDate = $request->date('start_date')?->toDateString() ?? now()->toDateString();
        $endDate = $request->date('end_date')?->toDateString() ?? $startDate;
        $perPage = in_array((int) $request->input('per_page', 10), [10, 25, 50, 100], true)
            ? (int) $request->input('per_page', 10)
            : 10;

        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $sales = Sale::query()
            ->with(['customer'])
            ->withCount('items')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->when($paymentStatus && array_key_exists($paymentStatus, self::PAYMENT_STATUSES), function ($query) use ($paymentStatus) {
                $query->where('payment_status', $paymentStatus);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('number', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('sale_date')
            ->latest('created_at')
            ->paginate($perPage)
            ->withQueryString();

        return view('sales.index', [
            'sales' => $sales,
            'search' => $search,
            'paymentStatus' => $paymentStatus,
            'paymentStatuses' => self::PAYMENT_STATUSES,
            'customerTypes' => self::CUSTOMER_TYPES,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'perPage' => $perPage,
        ]);
    }

    public function create(): View
    {
        $availableStocks = Stock::query()
            ->with(['item', 'warehouse'])
            ->where('quantity', '>', 0)
            ->whereHas('item', fn ($query) => $query->where('is_active', true))
            ->orderByDesc('quantity')
            ->get();

        return view('sales.create', [
            'nextNumber' => $this->generateNumber(),
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
            'customerTypes' => self::CUSTOMER_TYPES,
            'paymentStatuses' => self::PAYMENT_STATUSES,
            'transportTypes' => $this->distinctSaleValues('transport_type'),
            'vehiclePlateNumbers' => $this->distinctSaleValues('vehicle_plate_number'),
            'availableStocks' => $availableStocks,
            'itemCategories' => self::ITEM_CATEGORIES,
            'itemMaterialStates' => self::ITEM_MATERIAL_STATES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'items' => collect($request->input('items', []))
                ->map(function (array $item) {
                    if (isset($item['unit_price']) && is_string($item['unit_price'])) {
                        $item['unit_price'] = str_replace('.', '', $item['unit_price']);
                    }

                    return $item;
                })
                ->filter(fn (array $item) => filled($item['stock_id'] ?? null)
                    || filled($item['quantity'] ?? null)
                    || filled($item['unit_price'] ?? null))
                ->values()
                ->all(),
            'transport_type' => $request->filled('transport_type')
                ? preg_replace('/\s+/', ' ', trim((string) $request->input('transport_type')))
                : null,
            'vehicle_plate_number' => $request->filled('vehicle_plate_number')
                ? Str::upper(preg_replace('/\s+/', ' ', trim((string) $request->input('vehicle_plate_number'))))
                : null,
        ]);

        $validated = $request->validate([
            'number' => ['nullable', 'string', 'max:100', 'unique:sales,number'],
            'sale_date' => ['required', 'date'],
            'customer_id' => ['required', 'uuid', 'exists:customers,id'],
            'payment_status' => ['required', 'string', Rule::in(array_keys(self::PAYMENT_STATUSES))],
            'transport_type' => ['nullable', 'string', 'max:100'],
            'vehicle_plate_number' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.stock_id' => ['required', 'uuid', 'exists:stocks,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
        ]);

        $sale = DB::transaction(function () use ($validated, $request) {
            $number = $validated['number'] ?: $this->generateNumber();
            $totalAmount = collect($validated['items'])->sum(
                fn (array $item) => (float) $item['quantity'] * (float) $item['unit_price']
            );

            $sale = Sale::create([
                'number' => $number,
                'sale_date' => $validated['sale_date'],
                'customer_id' => $validated['customer_id'],
                'payment_status' => $validated['payment_status'],
                'transport_type' => $validated['transport_type'] ?? null,
                'vehicle_plate_number' => $validated['vehicle_plate_number'] ?? null,
                'total_amount' => $totalAmount,
                'notes' => $validated['notes'] ?? null,
                'created_by' => $request->user()?->id,
            ]);

            foreach ($validated['items'] as $index => $detail) {
                $stock = Stock::with(['item', 'warehouse'])
                    ->lockForUpdate()
                    ->findOrFail($detail['stock_id']);

                $quantity = (float) $detail['quantity'];
                $unitPrice = (float) $detail['unit_price'];
                $subtotal = $quantity * $unitPrice;

                if ((float) $stock->quantity < $quantity) {
                    $itemName = $stock->item?->name ?? 'Barang';
                    $unit = strtoupper($stock->item?->unit ?? '');
                    $availableFormatted = number_format((float) $stock->quantity, 2, ',', '.');

                    throw ValidationException::withMessages([
                        "items.{$index}.quantity" => "Stok {$itemName} tidak mencukupi (tersedia: {$availableFormatted} {$unit}).",
                    ]);
                }

                $saleItem = $sale->items()->create([
                    'stock_id' => $stock->id,
                    'item_id' => $stock->item_id,
                    'warehouse_id' => $stock->warehouse_id,
                    'lot_number' => $stock->lot_number,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                $balanceAfter = (float) $stock->quantity - $quantity;
                $stock->update(['quantity' => $balanceAfter]);

                StockMovement::create([
                    'stock_id' => $stock->id,
                    'item_id' => $stock->item_id,
                    'warehouse_id' => $stock->warehouse_id,
                    'movement_date' => $validated['sale_date'],
                    'type' => 'sale',
                    'quantity_in' => 0,
                    'quantity_out' => $quantity,
                    'balance_after' => $balanceAfter,
                    'reference_type' => 'sale',
                    'reference_id' => $saleItem->id,
                    'reference_number' => $number,
                    'notes' => $validated['notes'] ?? "Penjualan ke {$sale->customer?->name}",
                    'created_by' => $request->user()?->id,
                ]);
            }

            return $sale;
        });

        return redirect()
            ->route('sales.show', $sale)
            ->with('success', 'Penjualan barang berhasil disimpan dan stok gudang telah dikurangi.');
    }

    public function show(Sale $sale): View
    {
        $sale->load(['customer', 'items.item', 'items.warehouse', 'creator']);

        return view('sales.show', [
            'sale' => $sale,
            'paymentStatuses' => self::PAYMENT_STATUSES,
            'customerTypes' => self::CUSTOMER_TYPES,
            'itemCategories' => self::ITEM_CATEGORIES,
            'itemMaterialStates' => self::ITEM_MATERIAL_STATES,
        ]);
    }

    public function updatePaymentStatus(Request $request, Sale $sale): RedirectResponse
    {
        $validated = $request->validate([
            'payment_status' => ['required', 'string', Rule::in(array_keys(self::PAYMENT_STATUSES))],
        ]);

        $sale->update([
            'payment_status' => $validated['payment_status'],
        ]);

        $statusLabel = self::PAYMENT_STATUSES[$validated['payment_status']] ?? $validated['payment_status'];

        return redirect()
            ->route('sales.show', $sale)
            ->with('success', "Status pembayaran berhasil diperbarui menjadi {$statusLabel}.");
    }

    private function generateNumber(): string
    {
        do {
            $number = 'SO-'.now()->format('Ymd').'-'.Str::upper(Str::random(4));
        } while (Sale::where('number', $number)->exists());

        return $number;
    }

    private function distinctSaleValues(string $column)
    {
        return Sale::query()
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->values();
    }
}
