<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $suppliers = Supplier::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('contact_person', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('master.suppliers.index', compact('suppliers', 'search'));
    }

    public function create(): View
    {
        return view('master.suppliers.create', [
            'nextCode' => $this->generateCode(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255', 'unique:suppliers,email'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['code'] = $this->generateCode();
        $validated['is_active'] = $request->boolean('is_active');

        $supplier = Supplier::create($validated);

        if ($request->input('return_to') === 'purchases.create') {
            return redirect()
                ->route('purchases.create', [
                    'source_type' => 'supplier',
                    'supplier_id' => $supplier->id,
                ])
                ->with('success', 'Data supplier berhasil ditambahkan.');
        }

        return redirect()
            ->route('master.suppliers.index')
            ->with('success', 'Data supplier berhasil ditambahkan.');
    }

    public function show(Supplier $supplier): View
    {
        return view('master.suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier): View
    {
        return view('master.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('suppliers', 'code')->ignore($supplier),
            ],
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('suppliers', 'email')->ignore($supplier),
            ],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $supplier->update($validated);

        return redirect()
            ->route('master.suppliers.index')
            ->with('success', 'Data supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $supplier->delete();

        return redirect()
            ->route('master.suppliers.index')
            ->with('success', 'Data supplier berhasil dihapus.');
    }

    private function generateCode(): string
    {
        $lastNumber = Supplier::query()
            ->where('code', 'like', 'SUP%')
            ->get()
            ->map(function (Supplier $supplier) {
                return (int) preg_replace('/\D/', '', $supplier->code);
            })
            ->max() ?? 0;

        do {
            $lastNumber++;
            $code = 'SUP'.str_pad((string) $lastNumber, 3, '0', STR_PAD_LEFT);
        } while (Supplier::where('code', $code)->exists());

        return $code;
    }
}
