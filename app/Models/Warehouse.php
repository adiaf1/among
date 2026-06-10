<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
