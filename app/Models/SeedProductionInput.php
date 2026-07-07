<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeedProductionInput extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'seed_production_id',
        'stock_id',
        'item_id',
        'warehouse_id',
        'role',
        'quantity',
        'unit',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function seedProduction(): BelongsTo
    {
        return $this->belongsTo(SeedProduction::class);
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
