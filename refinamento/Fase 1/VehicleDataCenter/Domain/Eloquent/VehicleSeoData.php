<?php

namespace Src\VehicleDataCenter\Domain\Eloquent;

use MongoDB\Laravel\Eloquent\Model;

class VehicleSeoData extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'vehicle_seo_data';

    protected $fillable = [
        'version_id',
        'make_slug',
        'model_slug',
        'version_slug',
        'year',
        'title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
        'og_data',
        'schema_markup',
        'json_ld',
        'internal_links',
    ];

    protected $casts = [
        'version_id' => 'integer',
        'year' => 'integer',
        'meta_keywords' => 'array',
        'og_data' => 'array',
        'schema_markup' => 'array',
        'json_ld' => 'array',
        'internal_links' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
