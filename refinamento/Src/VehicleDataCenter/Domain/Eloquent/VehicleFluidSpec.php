<?php

namespace Src\VehicleDataCenter\Domain\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleFluidSpec extends Model
{
    use SoftDeletes;

    protected $table = 'vehicle_fluid_specs';

    protected $fillable = [
        'version_id',
        'engine_oil_type',
        'engine_oil_capacity',
        'engine_oil_standard',
        'coolant_type',
        'coolant_capacity',
        'transmission_fluid_type',
        'transmission_fluid_capacity',
        'brake_fluid_type',
        'power_steering_fluid_type',
        'additional_data',
    ];

    protected $casts = [
        'version_id' => 'integer',
        'engine_oil_capacity' => 'decimal:2',
        'coolant_capacity' => 'decimal:2',
        'transmission_fluid_capacity' => 'decimal:2',
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
