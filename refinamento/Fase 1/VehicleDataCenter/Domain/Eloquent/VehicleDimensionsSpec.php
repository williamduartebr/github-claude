<?php

namespace Src\VehicleDataCenter\Domain\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleDimensionsSpec extends Model
{
    use SoftDeletes;

    protected $table = 'vehicle_dimensions_specs';

    protected $fillable = [
        'version_id',
        'length_mm',
        'width_mm',
        'height_mm',
        'wheelbase_mm',
        'front_track_mm',
        'rear_track_mm',
        'ground_clearance_mm',
        'additional_data',
    ];

    protected $casts = [
        'version_id' => 'integer',
        'length_mm' => 'integer',
        'width_mm' => 'integer',
        'height_mm' => 'integer',
        'wheelbase_mm' => 'integer',
        'front_track_mm' => 'integer',
        'rear_track_mm' => 'integer',
        'ground_clearance_mm' => 'integer',
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
