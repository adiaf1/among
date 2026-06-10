<?php

namespace App\Http\Controllers;

use App\Models\Farmer;
use App\Models\Land;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LandController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $lands = Land::with('farmer')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhereHas('farmer', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('code')
            ->paginate(10)
            ->withQueryString();

        return view('master.lands.index', compact('lands', 'search'));
    }

    public function create(): View
    {
        $farmers = Farmer::where('is_active', true)->orderBy('name')->get();

        return view('master.lands.create', compact('farmers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'farmer_id' => ['required', 'exists:farmers,id'],
            'code' => ['required', 'string', 'max:50', 'unique:lands,code'],
            'name' => ['required', 'string', 'max:255'],
            'area_size' => ['nullable', 'numeric', 'min:0'],
            'location' => ['nullable', 'string'],
            'soil_type' => ['nullable', 'string', 'max:100'],
            'irrigation_type' => ['nullable', 'string', 'max:100'],
            'ownership_status' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        Land::create($validated);

        return redirect()
            ->route('master.lands.index')
            ->with('success', 'Data lahan berhasil ditambahkan.');
    }

    public function show(Land $land): View
    {
        $land->load('farmer');

        return view('master.lands.show', compact('land'));
    }

    public function edit(Land $land): View
    {
        $farmers = Farmer::where('is_active', true)
            ->orWhere('id', $land->farmer_id)
            ->orderBy('name')
            ->get();

        return view('master.lands.edit', compact('land', 'farmers'));
    }

    public function update(Request $request, Land $land): RedirectResponse
    {
        $validated = $request->validate([
            'farmer_id' => ['required', 'exists:farmers,id'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('lands', 'code')->ignore($land),
            ],
            'name' => ['required', 'string', 'max:255'],
            'area_size' => ['nullable', 'numeric', 'min:0'],
            'location' => ['nullable', 'string'],
            'soil_type' => ['nullable', 'string', 'max:100'],
            'irrigation_type' => ['nullable', 'string', 'max:100'],
            'ownership_status' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $land->update($validated);

        return redirect()
            ->route('master.lands.index')
            ->with('success', 'Data lahan berhasil diperbarui.');
    }

    public function destroy(Land $land): RedirectResponse
    {
        $land->delete();

        return redirect()
            ->route('master.lands.index')
            ->with('success', 'Data lahan berhasil dihapus.');
    }
}
