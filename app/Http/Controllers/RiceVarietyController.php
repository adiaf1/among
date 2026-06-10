<?php

namespace App\Http\Controllers;

use App\Models\RiceVariety;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RiceVarietyController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $riceVarieties = RiceVariety::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('master.rice-varieties.index', compact('riceVarieties', 'search'));
    }

    public function create(): View
    {
        return view('master.rice-varieties.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:rice_varieties,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        RiceVariety::create($validated);

        return redirect()
            ->route('master.rice-varieties.index')
            ->with('success', 'Varietas padi berhasil ditambahkan.');
    }

    public function show(RiceVariety $riceVariety): View
    {
        return view('master.rice-varieties.show', compact('riceVariety'));
    }

    public function edit(RiceVariety $riceVariety): View
    {
        return view('master.rice-varieties.edit', compact('riceVariety'));
    }

    public function update(Request $request, RiceVariety $riceVariety): RedirectResponse
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('rice_varieties', 'code')->ignore($riceVariety),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $riceVariety->update($validated);

        return redirect()
            ->route('master.rice-varieties.index')
            ->with('success', 'Varietas padi berhasil diperbarui.');
    }

    public function destroy(RiceVariety $riceVariety): RedirectResponse
    {
        $riceVariety->delete();

        return redirect()
            ->route('master.rice-varieties.index')
            ->with('success', 'Varietas padi berhasil dihapus.');
    }
}
