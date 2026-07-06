<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeedGrowingHarvest extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'seed_growing_id',
        'harvest_item_id',
        'harvest_warehouse_id',
        'stock_id',
        'harvest_date',
        'harvested_quantity',
        'unit',
        'material_state',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'harvest_date' => 'date',
        'harvested_quantity' => 'decimal:2',
    ];

    public function seedGrowing(): BelongsTo
    {
        return $this->belongsTo(SeedGrowing::class);
    }

    public function harvestItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'harvest_item_id');
    }

    public function harvestWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'harvest_warehouse_id');
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
