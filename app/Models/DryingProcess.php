<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DryingProcess extends Model
{
    use HasFactory;

    protected $fillable = [
        'process_number',
        'process_date',
        'wet_rice_receipt_id',
        'variety_id',
        'warehouse_id',
        'initial_weight',
        'initial_moisture',
        'final_weight',
        'final_moisture',
        'weight_loss',
        'notes',
    ];

    protected $casts = [
        'process_date' => 'date',
    ];

    public function wetRiceReceipt()
    {
        return $this->belongsTo(WetRiceReceipt::class);
    }

    public function variety()
    {
        return $this->belongsTo(Variety::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function dryRiceStock()
    {
        return $this->hasOne(DryRiceStock::class);
    }
}
