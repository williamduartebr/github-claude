<?php

namespace Src\VehicleDataCenter\Domain\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class VehicleVersion extends Model
{
    use SoftDeletes;

    protected $table = 'vehicle_versions';

    protected $fillable = [
        'id',
        'model_id',
        'name',
        'slug',
        'year',
        'engine_code',
        'fuel_type',
        'transmission',
        'price_msrp',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'model_id' => 'integer',
        'year' => 'integer',
        'price_msrp' => 'decimal:2',
        'is_active' => 'boolean',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function model(): BelongsTo
    {
        return $this->belongsTo(VehicleModel::class, 'model_id');
    }

    public function specs(): HasOne
    {
        return $this->hasOne(VehicleSpec::class, 'version_id');
    }

    public function engineSpecs(): HasOne
    {
        return $this->hasOne(VehicleEngineSpec::class, 'version_id');
    }

    public function tireSpecs(): HasOne
    {
        return $this->hasOne(VehicleTireSpec::class, 'version_id');
    }

    public function fluidSpecs(): HasOne
    {
        return $this->hasOne(VehicleFluidSpec::class, 'version_id');
    }

    public function batterySpecs(): HasOne
    {
        return $this->hasOne(VehicleBatterySpec::class, 'version_id');
    }

    public function dimensionsSpecs(): HasOne
    {
        return $this->hasOne(VehicleDimensionsSpec::class, 'version_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    public function scopeByFuelType($query, string $fuelType)
    {
        return $query->where('fuel_type', $fuelType);
    }

    public function scopeByTransmission($query, string $transmission)
    {
        return $query->where('transmission', $transmission);
    }
}
