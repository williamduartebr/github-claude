<?php

namespace Src\VehicleDataCenter\Domain\Eloquent;

use MongoDB\Laravel\Eloquent\Model;

class VehicleDocument extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'vehicle_documents';

    protected $fillable = [
        'version_id',
        'make_slug',
        'model_slug',
        'version_slug',
        'year',
        'full_name',
        'payload',
        'enriched_data',
        'metadata',
        'indexed_at',
    ];

    protected $casts = [
        'version_id' => 'integer',
        'year' => 'integer',
        'payload' => 'array',
        'enriched_data' => 'array',
        'metadata' => 'array',
        'indexed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Índices para busca otimizada
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->indexed_at = now();
        });
    }

    public function scopeByMake($query, string $makeSlug)
    {
        return $query->where('make_slug', $makeSlug);
    }

    public function scopeByModel($query, string $modelSlug)
    {
        return $query->where('model_slug', $modelSlug);
    }

    public function scopeByYear($query, int $year)
    {
        return $query->where('year', $year);
    }
}
