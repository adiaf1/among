<?php

namespace App\Http\Controllers;

use App\Models\WetRiceReceipt;
use App\Models\Variety;
use Illuminate\Http\Request;

class WetRiceReceiptController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|editor']);
    }

    public function index()
    {
        $receipts = WetRiceReceipt::with('variety')->latest()->paginate(15);
        return view('wet-rice-receipts.index', compact('receipts'));
    }

    public function create()
    {
        $varieties = Variety::all();
        return view('wet-rice-receipts.create', compact('varieties'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'variety_id' => 'required|exists:varieties,id',
            'farmer_name' => 'required|string|max:255',
            'weight' => 'required|numeric|min:0',
            'moisture_content' => 'required|numeric|min:0|max:100',
            'harvest_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        WetRiceReceipt::create($validated);

        return redirect()->route('wet-rice.receipts.index')
            ->with('success', 'Barang masuk basah berhasil ditambahkan.');
    }

    public function show(WetRiceReceipt $receipt)
    {
        return view('wet-rice-receipts.show', compact('receipt'));
    }

    public function edit(WetRiceReceipt $receipt)
    {
        $varieties = Variety::all();
        return view('wet-rice-receipts.edit', compact('receipt', 'varieties'));
    }

    public function update(Request $request, WetRiceReceipt $receipt)
    {
        $validated = $request->validate([
            'variety_id' => 'required|exists:varieties,id',
            'farmer_name' => 'required|string|max:255',
            'weight' => 'required|numeric|min:0',
            'moisture_content' => 'required|numeric|min:0|max:100',
            'harvest_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $receipt->update($validated);

        return redirect()->route('wet-rice.receipts.index')
            ->with('success', 'Barang masuk basah berhasil diupdate.');
    }

    public function destroy(WetRiceReceipt $receipt)
    {
        $receipt->delete();

        return redirect()->route('wet-rice.receipts.index')
            ->with('success', 'Barang masuk basah berhasil dihapus.');
    }
}
