<?php

namespace Src\VehicleDataCenter\Domain\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleSpec extends Model
{
    use SoftDeletes;

    protected $table = 'vehicle_specs';

    protected $fillable = [
        'version_id',
        'power_hp',
        'power_kw',
        'torque_nm',
        'top_speed_kmh',
        'acceleration_0_100',
        'fuel_consumption_city',
        'fuel_consumption_highway',
        'fuel_consumption_mixed',
        'fuel_tank_capacity',
        'weight_kg',
        'payload_kg',
        'trunk_capacity_liters',
        'seating_capacity',
        'body_type',
        'doors',
        'drive_type',
        'additional_specs',
    ];

    protected $casts = [
        'version_id' => 'integer',
        'power_hp' => 'decimal:2',
        'power_kw' => 'decimal:2',
        'torque_nm' => 'integer',
        'top_speed_kmh' => 'integer',
        'acceleration_0_100' => 'decimal:2',
        'fuel_consumption_city' => 'decimal:2',
        'fuel_consumption_highway' => 'decimal:2',
        'fuel_consumption_mixed' => 'decimal:2',
        'fuel_tank_capacity' => 'integer',
        'weight_kg' => 'integer',
        'payload_kg' => 'integer',
        'trunk_capacity_liters' => 'integer',
        'seating_capacity' => 'integer',
        'doors' => 'integer',
        'additional_specs' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(VehicleVersion::class, 'version_id');
    }
}
