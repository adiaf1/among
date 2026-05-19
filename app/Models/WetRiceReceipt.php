<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WetRiceReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_number',
        'receipt_date',
        'farmer_id',
        'variety_id',
        'warehouse_id',
        'unit_id',
        'gross_weight',
        'tare_weight',
        'net_weight',
        'moisture_content',
        'notes',
    ];

    protected $casts = [
        'receipt_date' => 'date',
    ];

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
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

    public function dryingProcesses()
    {
        return $this->hasMany(DryingProcess::class);
    }
}
