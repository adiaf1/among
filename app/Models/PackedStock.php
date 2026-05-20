<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackedStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_number',
        'lot_number',
        'stock_date',
        'packaging_process_id',
        'variety_id',
        'warehouse_id',
        'unit_id',
        'quantity',
        'weight_per_package',
        'total_weight',
        'remaining_quantity',
        'notes',
    ];

    protected $casts = [
        'stock_date' => 'date',
    ];

    public function packagingProcess()
    {
        return $this->belongsTo(PackagingProcess::class);
    }

    public function variety()
    {
        return $this->belongsTo(Variety::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
