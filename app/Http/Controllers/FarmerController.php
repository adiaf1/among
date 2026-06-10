<?php

namespace App\Http\Controllers;

use App\Models\Farmer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FarmerController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $farmers = Farmer::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('identity_number', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('master.farmers.index', compact('farmers', 'search'));
    }

    public function create(): View
    {
        return view('master.farmers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:farmers,code'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'identity_number' => ['nullable', 'string', 'max:100', 'unique:farmers,identity_number'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        Farmer::create($validated);

        return redirect()
            ->route('master.farmers.index')
            ->with('success', 'Data petani berhasil ditambahkan.');
    }

    public function show(Farmer $farmer): View
    {
        return view('master.farmers.show', compact('farmer'));
    }

    public function edit(Farmer $farmer): View
    {
        return view('master.farmers.edit', compact('farmer'));
    }

    public function update(Request $request, Farmer $farmer): RedirectResponse
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('farmers', 'code')->ignore($farmer),
            ],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'identity_number' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('farmers', 'identity_number')->ignore($farmer),
            ],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $farmer->update($validated);

        return redirect()
            ->route('master.farmers.index')
            ->with('success', 'Data petani berhasil diperbarui.');
    }

    public function destroy(Farmer $farmer): RedirectResponse
    {
        $farmer->delete();

        return redirect()
            ->route('master.farmers.index')
            ->with('success', 'Data petani berhasil dihapus.');
    }
}
