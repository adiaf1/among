<?php

namespace App\Http\Controllers;

use App\Models\PackedStock;
use App\Models\PackagingProcess;
use Illuminate\Http\Request;

class PackedStockController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|editor']);
    }

    public function index()
    {
        $stocks = PackedStock::with(['packagingProcess.dryRiceStock.dryingProcess.wetRiceReceipt.variety'])->latest()->paginate(15);
        return view('packed-stocks.index', compact('stocks'));
    }

    public function create()
    {
        $processes = PackagingProcess::all();
        return view('packed-stocks.create', compact('processes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'packaging_process_id' => 'required|exists:packaging_processes,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'notes' => 'nullable|string',
        ]);

        PackedStock::create($validated);

        return redirect()->route('packed.stocks.index')
            ->with('success', 'Stok terpack berhasil ditambahkan.');
    }

    public function show(PackedStock $stock)
    {
        return view('packed-stocks.show', compact('stock'));
    }

    public function edit(PackedStock $stock)
    {
        $processes = PackagingProcess::all();
        return view('packed-stocks.edit', compact('stock', 'processes'));
    }

    public function update(Request $request, PackedStock $stock)
    {
        $validated = $request->validate([
            'packaging_process_id' => 'required|exists:packaging_processes,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'notes' => 'nullable|string',
        ]);

        $stock->update($validated);

        return redirect()->route('packed.stocks.index')
            ->with('success', 'Stok terpack berhasil diupdate.');
    }

    public function destroy(PackedStock $stock)
    {
        $stock->delete();

        return redirect()->route('packed.stocks.index')
            ->with('success', 'Stok terpack berhasil dihapus.');
    }
}
