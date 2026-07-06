<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\RiceVariety;
use App\Models\SeedClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ItemController extends Controller
{
    private const CATEGORIES = [
        'benih' => 'Benih',
        'gabah' => 'Gabah',
        'karung' => 'Karung',
        'plastik' => 'Plastik',
        'benang_karung' => 'Benang Karung',
        'kemasan' => 'Kemasan',
        'bahan_produksi' => 'Bahan Produksi',
        'lainnya' => 'Lainnya',
    ];

    private const MATERIAL_STATES = [
        'none' => 'Tidak Ada',
        'basah' => 'Basah',
        'kering' => 'Kering',
        'benih_jadi' => 'Benih Jadi',
        'bahan_pendukung' => 'Bahan Pendukung',
    ];

    private const UNITS = [
        'kg',
        'pcs',
        'karung',
        'roll',
        'liter',
        'meter',
        'pack',
    ];

    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $items = Item::query()
            ->with(['riceVariety', 'seedClass'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhere('material_state', 'like', "%{$search}%")
                        ->orWhere('unit', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('master.items.index', [
            'items' => $items,
            'search' => $search,
            'categories' => self::CATEGORIES,
            'materialStates' => self::MATERIAL_STATES,
        ]);
    }

    public function create(): View
    {
        return $this->formView('master.items.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->normalizeUnit($request);

        $validated = $request->validate($this->rules());
        $validated['is_active'] = $request->boolean('is_active');

        $item = Item::create($validated);

        if ($request->input('return_to') === 'purchases.create') {
            return redirect()
                ->route('purchases.create', ['item_id' => $item->id])
                ->with('success', 'Data barang berhasil ditambahkan. Barang baru sudah dipilih di detail pembelian.');
        }

        if ($request->input('return_to') === 'seed-growings.show' && $request->filled('seed_growing_id')) {
            return redirect()
                ->route('seed-growings.show', $request->input('seed_growing_id'))
                ->with('success', 'Data barang hasil panen berhasil ditambahkan. Silakan pilih barang pada form panen.');
        }

        return redirect()
            ->route('master.items.index')
            ->with('success', 'Data barang berhasil ditambahkan.');
    }

    public function show(Item $item): View
    {
        $item->load(['riceVariety', 'seedClass']);

        return view('master.items.show', [
            'item' => $item,
            'categories' => self::CATEGORIES,
            'materialStates' => self::MATERIAL_STATES,
        ]);
    }

    public function edit(Item $item): View
    {
        return $this->formView('master.items.edit', $item);
    }

    public function update(Request $request, Item $item): RedirectResponse
    {
        $this->normalizeUnit($request);

        $validated = $request->validate($this->rules($item));
        $validated['is_active'] = $request->boolean('is_active');

        $item->update($validated);

        return redirect()
            ->route('master.items.index')
            ->with('success', 'Data barang berhasil diperbarui.');
    }

    public function destroy(Item $item): RedirectResponse
    {
        $item->delete();

        return redirect()
            ->route('master.items.index')
            ->with('success', 'Data barang berhasil dihapus.');
    }

    private function formView(string $view, ?Item $item = null): View
    {
        return view($view, [
            'item' => $item,
            'categories' => self::CATEGORIES,
            'materialStates' => self::MATERIAL_STATES,
            'unitOptions' => $this->unitOptions(),
            'riceVarieties' => RiceVariety::where('is_active', true)->orderBy('name')->get(),
            'seedClasses' => SeedClass::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    private function rules(?Item $item = null): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('items', 'code')->ignore($item),
            ],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', Rule::in(array_keys(self::CATEGORIES))],
            'material_state' => ['required', 'string', Rule::in(array_keys(self::MATERIAL_STATES))],
            'unit' => ['required', 'string', 'max:50'],
            'rice_variety_id' => ['nullable', 'uuid', 'exists:rice_varieties,id'],
            'seed_class_id' => ['nullable', 'uuid', 'exists:seed_classes,id'],
            'minimum_stock' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    private function normalizeUnit(Request $request): void
    {
        if (! $request->filled('unit')) {
            return;
        }

        $request->merge([
            'unit' => mb_strtolower(preg_replace('/\s+/', ' ', trim((string) $request->input('unit')))),
        ]);
    }

    private function unitOptions()
    {
        return collect(self::UNITS)
            ->merge(Item::query()
                ->whereNotNull('unit')
                ->where('unit', '!=', '')
                ->distinct()
                ->orderBy('unit')
                ->pluck('unit'))
            ->map(fn ($unit) => trim((string) $unit))
            ->filter()
            ->unique(fn ($unit) => mb_strtolower($unit))
            ->values();
    }
}
