<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variety extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function wetRiceReceipts()
    {
        return $this->hasMany(WetRiceReceipt::class);
    }

    public function dryingProcesses()
    {
        return $this->hasMany(DryingProcess::class);
    }

    public function dryRiceStocks()
    {
        return $this->hasMany(DryRiceStock::class);
    }

    public function packagingProcesses()
    {
        return $this->hasMany(PackagingProcess::class);
    }

    public function packedStocks()
    {
        return $this->hasMany(PackedStock::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
