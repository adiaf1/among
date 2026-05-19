<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|editor']);
    }

    public function index()
    {
        $movements = StockMovement::with(['stockable', 'user'])->latest()->paginate(20);
        return view('stock-movements.index', compact('movements'));
    }

    public function show(StockMovement $movement)
    {
        return view('stock-movements.show', compact('movement'));
    }
}
