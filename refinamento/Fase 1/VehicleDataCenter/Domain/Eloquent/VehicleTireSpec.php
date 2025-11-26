<?php

namespace Src\VehicleDataCenter\Domain\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleTireSpec extends Model
{
    use SoftDeletes;

    protected $table = 'vehicle_tire_specs';

    protected $fillable = [
        'version_id',
        'front_tire_size',
        'rear_tire_size',
        'front_rim_size',
        'rear_rim_size',
        'front_pressure_psi',
        'rear_pressure_psi',
        'spare_tire_type',
        'additional_data',
    ];

    protected $casts = [
        'version_id' => 'integer',
        'front_pressure_psi' => 'decimal:1',
        'rear_pressure_psi' => 'decimal:1',
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
