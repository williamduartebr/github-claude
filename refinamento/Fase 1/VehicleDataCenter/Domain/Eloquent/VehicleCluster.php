<?php

namespace Src\VehicleDataCenter\Domain\Eloquent;

use MongoDB\Laravel\Eloquent\Model;

class VehicleCluster extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'vehicle_clusters';

    protected $fillable = [
        'cluster_type',
        'cluster_key',
        'vehicles',
        'metadata',
        'count',
    ];

    protected $casts = [
        'vehicles' => 'array',
        'metadata' => 'array',
        'count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopeByType($query, string $type)
    {
        return $query->where('cluster_type', $type);
    }
}
