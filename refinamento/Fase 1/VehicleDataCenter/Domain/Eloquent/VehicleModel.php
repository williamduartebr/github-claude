<?php

namespace Src\VehicleDataCenter\Domain\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleModel extends Model
{
    use SoftDeletes;

    protected $table = 'vehicle_models';

    protected $fillable = [
        'id',
        'make_id',
        'name',
        'slug',
        'year_start',
        'year_end',
        'category',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'make_id' => 'integer',
        'year_start' => 'integer',
        'year_end' => 'integer',
        'is_active' => 'boolean',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function make(): BelongsTo
    {
        return $this->belongsTo(VehicleMake::class, 'make_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(VehicleVersion::class, 'model_id');
    }

    public function activeVersions(): HasMany
    {
        return $this->hasMany(VehicleVersion::class, 'model_id')
            ->where('is_active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByYearRange($query, int $yearStart, int $yearEnd)
    {
        return $query->where('year_start', '<=', $yearEnd)
            ->where(function ($q) use ($yearStart) {
                $q->whereNull('year_end')
                    ->orWhere('year_end', '>=', $yearStart);
            });
    }
}
