<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'movement_number',
        'movement_date',
        'warehouse_id',
        'variety_id',
        'stock_type',
        'stock_id',
        'movement_type',
        'quantity_before',
        'quantity_change',
        'quantity_after',
        'unit',
        'notes',
    ];

    protected $casts = [
        'movement_date' => 'date',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function variety()
    {
        return $this->belongsTo(Variety::class);
    }

    public function stock()
    {
        if ($this->stock_type === 'dry_rice') {
            return $this->belongsTo(DryRiceStock::class, 'stock_id');
        } elseif ($this->stock_type === 'packed') {
            return $this->belongsTo(PackedStock::class, 'stock_id');
        }
        return null;
    }
}
