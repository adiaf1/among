<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeedProductionStep extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'seed_production_id',
        'sort_order',
        'stage',
        'label',
        'planned_date',
        'actual_date',
        'quantity',
        'cost_per_kg',
        'cost',
        'status',
        'notes',
        'updated_by',
    ];

    protected $casts = [
        'planned_date' => 'date',
        'actual_date' => 'date',
        'quantity' => 'decimal:2',
        'cost_per_kg' => 'decimal:2',
        'cost' => 'decimal:2',
    ];

    public function seedProduction(): BelongsTo
    {
        return $this->belongsTo(SeedProduction::class);
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
