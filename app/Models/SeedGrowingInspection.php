<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeedGrowingInspection extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'seed_growing_id',
        'stage',
        'planned_date',
        'actual_date',
        'cost',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'planned_date' => 'date',
        'actual_date' => 'date',
        'cost' => 'decimal:2',
    ];

    public function seedGrowing(): BelongsTo
    {
        return $this->belongsTo(SeedGrowing::class);
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
