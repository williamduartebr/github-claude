<?php

namespace Src\VehicleDataCenter\Domain\Eloquent;

use MongoDB\Laravel\Eloquent\Model;

class VehicleVariant extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'vehicle_variants';

    protected $fillable = [
        'version_id',
        'variant_name',
        'features',
        'options',
        'colors',
        'price_variations',
        'availability',
    ];

    protected $casts = [
        'version_id' => 'integer',
        'features' => 'array',
        'options' => 'array',
        'colors' => 'array',
        'price_variations' => 'array',
        'availability' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
