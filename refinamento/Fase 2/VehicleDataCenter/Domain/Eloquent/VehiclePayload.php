<?php

namespace Src\VehicleDataCenter\Domain\Eloquent;

use MongoDB\Laravel\Eloquent\Model;

class VehiclePayload extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'vehicle_payloads';

    protected $fillable = [
        'version_id',
        'source',
        'raw_data',
        'processed_data',
        'status',
        'processed_at',
    ];

    protected $casts = [
        'version_id' => 'integer',
        'raw_data' => 'array',
        'processed_data' => 'array',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }
}

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

class VehicleDynamicSpec extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'vehicle_dynamic_specs';

    protected $fillable = [
        'version_id',
        'spec_type',
        'data',
        'source',
        'confidence_score',
        'verified',
    ];

    protected $casts = [
        'version_id' => 'integer',
        'data' => 'array',
        'confidence_score' => 'float',
        'verified' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('spec_type', $type);
    }
}
