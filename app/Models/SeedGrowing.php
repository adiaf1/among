<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SeedGrowing extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'number',
        'farmer_id',
        'land_id',
        'field_number',
        'lot_number',
        'season_year',
        'rice_variety_id',
        'seed_class_id',
        'source_seed_item_id',
        'source_seed_warehouse_id',
        'source_seed_quantity',
        'field_area',
        'sowing_date',
        'planting_date',
        'preliminary_date',
        'field_inspection_1_date',
        'field_inspection_2_date',
        'field_inspection_3_date',
        'harvest_date',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'season_year' => 'integer',
        'source_seed_quantity' => 'decimal:2',
        'field_area' => 'decimal:2',
        'sowing_date' => 'date',
        'planting_date' => 'date',
        'preliminary_date' => 'date',
        'field_inspection_1_date' => 'date',
        'field_inspection_2_date' => 'date',
        'field_inspection_3_date' => 'date',
        'harvest_date' => 'date',
    ];

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    public function land(): BelongsTo
    {
        return $this->belongsTo(Land::class);
    }

    public function riceVariety(): BelongsTo
    {
        return $this->belongsTo(RiceVariety::class);
    }

    public function seedClass(): BelongsTo
    {
        return $this->belongsTo(SeedClass::class);
    }

    public function sourceSeedItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'source_seed_item_id');
    }

    public function sourceSeedWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'source_seed_warehouse_id');
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(SeedGrowingInspection::class);
    }

    public function harvest(): HasOne
    {
        return $this->hasOne(SeedGrowingHarvest::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
