<?php

namespace App\Http\Controllers;

use App\Models\Variety;
use Illuminate\Http\Request;

class VarietyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $varieties = Variety::latest()->paginate(10);
        return view('varieties.index', compact('varieties'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('varieties.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:varieties',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        Variety::create($validated);

        return redirect()->route('master.varieties.index')
            ->with('success', 'Varietas berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Variety $variety)
    {
        return view('varieties.show', compact('variety'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Variety $variety)
    {
        return view('varieties.edit', compact('variety'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Variety $variety)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:varieties,code,' . $variety->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $variety->update($validated);

        return redirect()->route('master.varieties.index')
            ->with('success', 'Varietas berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Variety $variety)
    {
        $variety->delete();

        return redirect()->route('master.varieties.index')
            ->with('success', 'Varietas berhasil dihapus.');
    }
}
