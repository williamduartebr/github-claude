<?php

namespace Src\VehicleDataCenter\Domain\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleEngineSpec extends Model
{
    use SoftDeletes;

    protected $table = 'vehicle_engine_specs';

    protected $fillable = [
        'version_id',
        'engine_type',
        'engine_code',
        'displacement_cc',
        'cylinders',
        'cylinder_arrangement',
        'valves_per_cylinder',
        'aspiration',
        'compression_ratio',
        'max_rpm',
        'additional_data',
    ];

    protected $casts = [
        'version_id' => 'integer',
        'displacement_cc' => 'integer',
        'cylinders' => 'integer',
        'valves_per_cylinder' => 'integer',
        'compression_ratio' => 'decimal:2',
        'max_rpm' => 'integer',
        'additional_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(VehicleVersion::class, 'version_id');
    }
}
