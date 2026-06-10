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
        'kemasan' => 'Kemasan',
        'bahan_produksi' => 'Bahan Produksi',
        'lainnya' => 'Lainnya',
    ];

    private const UNITS = [
        'kg' => 'Kg',
        'pcs' => 'Pcs',
        'karung' => 'Karung',
        'liter' => 'Liter',
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
        ]);
    }

    public function create(): View
    {
        return $this->formView('master.items.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $validated['is_active'] = $request->boolean('is_active');

        Item::create($validated);

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
        ]);
    }

    public function edit(Item $item): View
    {
        return $this->formView('master.items.edit', $item);
    }

    public function update(Request $request, Item $item): RedirectResponse
    {
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
            'units' => self::UNITS,
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
            'unit' => ['required', 'string', 'max:50'],
            'rice_variety_id' => ['nullable', 'uuid', 'exists:rice_varieties,id'],
            'seed_class_id' => ['nullable', 'uuid', 'exists:seed_classes,id'],
            'minimum_stock' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
