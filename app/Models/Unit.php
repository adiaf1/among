<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function wetRiceReceipts()
    {
        return $this->hasMany(WetRiceReceipt::class);
    }

    public function packagingProcesses()
    {
        return $this->hasMany(PackagingProcess::class, 'packaging_unit_id');
    }

    public function packedStocks()
    {
        return $this->hasMany(PackedStock::class);
    }
}
