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
        'output_date',
        'lot_number',
        'rice_variety_id',
        'seed_class_id',
        'output_item_id',
        'output_warehouse_id',
        'output_stock_id',
        'target_quantity',
        'output_quantity',
        'lab_test_date',
        'moisture_content',
        'purity_percentage',
        'germination_rate',
        'lab_result',
        'certificate_number',
        'label_serial_range',
        'label_count',
        'label_expiry_date',
        'certification_notes',
        'unit',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'production_date' => 'date',
        'output_date' => 'date',
        'lab_test_date' => 'date',
        'label_expiry_date' => 'date',
        'target_quantity' => 'decimal:2',
        'output_quantity' => 'decimal:2',
        'moisture_content' => 'decimal:2',
        'purity_percentage' => 'decimal:2',
        'germination_rate' => 'decimal:2',
        'label_count' => 'integer',
    ];

    public function riceVariety(): BelongsTo
    {
        return $this->belongsTo(RiceVariety::class);
    }

    public function seedClass(): BelongsTo
    {
        return $this->belongsTo(SeedClass::class);
    }

    public function outputItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'output_item_id');
    }

    public function outputWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'output_warehouse_id');
    }

    public function outputStock(): BelongsTo
    {
        return $this->belongsTo(Stock::class, 'output_stock_id');
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
