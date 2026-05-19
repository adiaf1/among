<?php

namespace App\Http\Controllers;

use App\Models\DryRiceStock;
use App\Models\DryingProcess;
use Illuminate\Http\Request;

class DryRiceStockController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|editor']);
    }

    public function index()
    {
        $stocks = DryRiceStock::with(['dryingProcess.wetRiceReceipt.variety'])->latest()->paginate(15);
        return view('dry-rice-stocks.index', compact('stocks'));
    }

    public function create()
    {
        $processes = DryingProcess::whereDoesntHave('dryRiceStock')->get();
        return view('dry-rice-stocks.create', compact('processes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'drying_process_id' => 'required|exists:drying_processes,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'notes' => 'nullable|string',
        ]);

        DryRiceStock::create($validated);

        return redirect()->route('dry-rice.stocks.index')
            ->with('success', 'Stok beras kering berhasil ditambahkan.');
    }

    public function show(DryRiceStock $stock)
    {
        return view('dry-rice-stocks.show', compact('stock'));
    }

    public function edit(DryRiceStock $stock)
    {
        $processes = DryingProcess::all();
        return view('dry-rice-stocks.edit', compact('stock', 'processes'));
    }

    public function update(Request $request, DryRiceStock $stock)
    {
        $validated = $request->validate([
            'drying_process_id' => 'required|exists:drying_processes,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'notes' => 'nullable|string',
        ]);

        $stock->update($validated);

        return redirect()->route('dry-rice.stocks.index')
            ->with('success', 'Stok beras kering berhasil diupdate.');
    }

    public function destroy(DryRiceStock $stock)
    {
        $stock->delete();

        return redirect()->route('dry-rice.stocks.index')
            ->with('success', 'Stok beras kering berhasil dihapus.');
    }
}
