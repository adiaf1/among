<?php

namespace App\Http\Controllers;

use App\Models\PackagingProcess;
use App\Models\DryRiceStock;
use Illuminate\Http\Request;

class PackagingProcessController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|editor']);
    }

    public function index()
    {
        $processes = PackagingProcess::with(['dryRiceStock.dryingProcess.wetRiceReceipt.variety'])->latest()->paginate(15);
        return view('packaging-processes.index', compact('processes'));
    }

    public function create()
    {
        $stocks = DryRiceStock::all();
        return view('packaging-processes.create', compact('stocks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dry_rice_stock_id' => 'required|exists:dry_rice_stocks,id',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'packaging_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        PackagingProcess::create($validated);

        return redirect()->route('packaging.processes.index')
            ->with('success', 'Proses packing berhasil ditambahkan.');
    }

    public function show(PackagingProcess $process)
    {
        return view('packaging-processes.show', compact('process'));
    }

    public function edit(PackagingProcess $process)
    {
        $stocks = DryRiceStock::all();
        return view('packaging-processes.edit', compact('process', 'stocks'));
    }

    public function update(Request $request, PackagingProcess $process)
    {
        $validated = $request->validate([
            'dry_rice_stock_id' => 'required|exists:dry_rice_stocks,id',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'packaging_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $process->update($validated);

        return redirect()->route('packaging.processes.index')
            ->with('success', 'Proses packing berhasil diupdate.');
    }

    public function destroy(PackagingProcess $process)
    {
        $process->delete();

        return redirect()->route('packaging.processes.index')
            ->with('success', 'Proses packing berhasil dihapus.');
    }
}
