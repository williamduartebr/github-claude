<?php

namespace Src\VehicleDataCenter\Domain\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleBatterySpec extends Model
{
    use SoftDeletes;

    protected $table = 'vehicle_battery_specs';

    protected $fillable = [
        'version_id',
        'battery_type',
        'voltage',
        'capacity_ah',
        'cca',
        'group_size',
        'battery_capacity_kwh',
        'electric_range_km',
        'charging_time',
        'additional_data',
    ];

    protected $casts = [
        'version_id' => 'integer',
        'voltage' => 'integer',
        'capacity_ah' => 'integer',
        'cca' => 'integer',
        'battery_capacity_kwh' => 'decimal:2',
        'electric_range_km' => 'integer',
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
