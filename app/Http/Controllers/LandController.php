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
    private const CERTIFICATION_STATUSES = [
        'belum_ditinjau' => 'Belum Ditinjau',
        'layak' => 'Layak',
        'perlu_perbaikan' => 'Perlu Perbaikan',
        'tidak_layak' => 'Tidak Layak',
    ];

    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $lands = Land::with('farmer')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhere('certification_status', 'like', "%{$search}%")
                        ->orWhereHas('farmer', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('code')
            ->paginate(10)
            ->withQueryString();

        return view('master.lands.index', [
            'lands' => $lands,
            'search' => $search,
            'certificationStatuses' => self::CERTIFICATION_STATUSES,
        ]);
    }

    public function create(): View
    {
        $farmers = Farmer::where('is_active', true)->orderBy('name')->get();

        return view('master.lands.create', [
            'farmers' => $farmers,
            'certificationStatuses' => self::CERTIFICATION_STATUSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'farmer_id' => ['required', 'exists:farmers,id'],
            'code' => ['required', 'string', 'max:50', 'unique:lands,code'],
            'name' => ['required', 'string', 'max:255'],
            'area_size' => ['nullable', 'numeric', 'min:0'],
            'location' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'soil_type' => ['nullable', 'string', 'max:100'],
            'irrigation_type' => ['nullable', 'string', 'max:100'],
            'ownership_status' => ['nullable', 'string', 'max:100'],
            'certification_status' => ['required', 'string', Rule::in(array_keys(self::CERTIFICATION_STATUSES))],
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

        return view('master.lands.show', [
            'land' => $land,
            'certificationStatuses' => self::CERTIFICATION_STATUSES,
        ]);
    }

    public function edit(Land $land): View
    {
        $farmers = Farmer::where('is_active', true)
            ->orWhere('id', $land->farmer_id)
            ->orderBy('name')
            ->get();

        return view('master.lands.edit', [
            'land' => $land,
            'farmers' => $farmers,
            'certificationStatuses' => self::CERTIFICATION_STATUSES,
        ]);
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
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'soil_type' => ['nullable', 'string', 'max:100'],
            'irrigation_type' => ['nullable', 'string', 'max:100'],
            'ownership_status' => ['nullable', 'string', 'max:100'],
            'certification_status' => ['required', 'string', Rule::in(array_keys(self::CERTIFICATION_STATUSES))],
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
