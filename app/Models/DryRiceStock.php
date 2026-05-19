<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DryRiceStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_number',
        'stock_date',
        'drying_process_id',
        'variety_id',
        'warehouse_id',
        'weight',
        'moisture_content',
        'remaining_weight',
        'notes',
    ];

    protected $casts = [
        'stock_date' => 'date',
    ];

    public function dryingProcess()
    {
        return $this->belongsTo(DryingProcess::class);
    }

    public function variety()
    {
        return $this->belongsTo(Variety::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function packagingProcesses()
    {
        return $this->hasMany(PackagingProcess::class);
    }
}
