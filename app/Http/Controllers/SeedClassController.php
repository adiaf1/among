<?php

namespace App\Http\Controllers;

use App\Models\SeedClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SeedClassController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $seedClasses = SeedClass::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->orderBy('code')
            ->paginate(10)
            ->withQueryString();

        return view('master.seed-classes.index', compact('seedClasses', 'search'));
    }

    public function create(): View
    {
        return view('master.seed-classes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:seed_classes,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        SeedClass::create($validated);

        return redirect()
            ->route('master.seed-classes.index')
            ->with('success', 'Kelas benih berhasil ditambahkan.');
    }

    public function show(SeedClass $seedClass): View
    {
        return view('master.seed-classes.show', compact('seedClass'));
    }

    public function edit(SeedClass $seedClass): View
    {
        return view('master.seed-classes.edit', compact('seedClass'));
    }

    public function update(Request $request, SeedClass $seedClass): RedirectResponse
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('seed_classes', 'code')->ignore($seedClass),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $seedClass->update($validated);

        return redirect()
            ->route('master.seed-classes.index')
            ->with('success', 'Kelas benih berhasil diperbarui.');
    }

    public function destroy(SeedClass $seedClass): RedirectResponse
    {
        $seedClass->delete();

        return redirect()
            ->route('master.seed-classes.index')
            ->with('success', 'Kelas benih berhasil dihapus.');
    }
}
