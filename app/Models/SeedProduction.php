<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeedProduction extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'number',
        'production_date',
        'lot_number',
        'rice_variety_id',
        'seed_class_id',
        'output_warehouse_id',
        'target_quantity',
        'unit',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'production_date' => 'date',
        'target_quantity' => 'decimal:2',
    ];

    public function riceVariety(): BelongsTo
    {
        return $this->belongsTo(RiceVariety::class);
    }

    public function seedClass(): BelongsTo
    {
        return $this->belongsTo(SeedClass::class);
    }

    public function outputWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'output_warehouse_id');
    }

    public function inputs(): HasMany
    {
        return $this->hasMany(SeedProductionInput::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(SeedProductionStep::class)->orderBy('sort_order');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
