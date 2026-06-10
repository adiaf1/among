<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'person_in_charge',
        'phone',
        'address',
        'capacity_kg',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'capacity_kg' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function sourceStockTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'source_warehouse_id');
    }

    public function destinationStockTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'destination_warehouse_id');
    }
}
