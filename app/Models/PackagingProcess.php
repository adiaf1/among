<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackagingProcess extends Model
{
    use HasFactory;

    protected $fillable = [
        'process_number',
        'process_date',
        'dry_rice_stock_id',
        'variety_id',
        'warehouse_id',
        'packaging_unit_id',
        'input_weight',
        'quantity_packed',
        'weight_per_package',
        'remaining_loose_weight',
        'notes',
    ];

    protected $casts = [
        'process_date' => 'date',
    ];

    public function dryRiceStock()
    {
        return $this->belongsTo(DryRiceStock::class);
    }

    public function variety()
    {
        return $this->belongsTo(Variety::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function packagingUnit()
    {
        return $this->belongsTo(Unit::class, 'packaging_unit_id');
    }

    public function packedStock()
    {
        return $this->hasOne(PackedStock::class);
    }
}
