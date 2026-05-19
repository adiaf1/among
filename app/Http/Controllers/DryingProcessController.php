<?php

namespace App\Http\Controllers;

use App\Models\DryingProcess;
use App\Models\WetRiceReceipt;
use App\Models\Variety;
use Illuminate\Http\Request;

class DryingProcessController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|editor']);
    }

    public function index()
    {
        $processes = DryingProcess::with(['wetRiceReceipt.variety'])->latest()->paginate(15);
        return view('drying-processes.index', compact('processes'));
    }

    public function create()
    {
        $receipts = WetRiceReceipt::whereDoesntHave('dryingProcess')->get();
        $varieties = Variety::all();
        return view('drying-processes.create', compact('receipts', 'varieties'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'wet_rice_receipt_id' => 'required|exists:wet_rice_receipts,id',
            'dry_weight' => 'required|numeric|min:0',
            'final_moisture_content' => 'required|numeric|min:0|max:100',
            'drying_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        DryingProcess::create($validated);

        return redirect()->route('drying.processes.index')
            ->with('success', 'Proses pengeringan berhasil ditambahkan.');
    }

    public function show(DryingProcess $process)
    {
        return view('drying-processes.show', compact('process'));
    }

    public function edit(DryingProcess $process)
    {
        $receipts = WetRiceReceipt::all();
        $varieties = Variety::all();
        return view('drying-processes.edit', compact('process', 'receipts', 'varieties'));
    }

    public function update(Request $request, DryingProcess $process)
    {
        $validated = $request->validate([
            'wet_rice_receipt_id' => 'required|exists:wet_rice_receipts,id',
            'dry_weight' => 'required|numeric|min:0',
            'final_moisture_content' => 'required|numeric|min:0|max:100',
            'drying_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $process->update($validated);

        return redirect()->route('drying.processes.index')
            ->with('success', 'Proses pengeringan berhasil diupdate.');
    }

    public function destroy(DryingProcess $process)
    {
        $process->delete();

        return redirect()->route('drying.processes.index')
            ->with('success', 'Proses pengeringan berhasil dihapus.');
    }
}
