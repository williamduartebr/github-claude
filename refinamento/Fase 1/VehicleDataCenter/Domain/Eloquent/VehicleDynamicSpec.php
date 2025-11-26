<?php

namespace Src\VehicleDataCenter\Domain\Eloquent;

use MongoDB\Laravel\Eloquent\Model;

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
