<?php

namespace Src\ContentGeneration\TireCalibration\Domain\Entities;

use MongoDB\Laravel\Eloquent\Model;

class TireCalibration extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'tire_calibrations';
    protected $guarded = ['_id'];

    protected $casts = [
        'blog_modified_time' => 'datetime',
        'blog_published_time' => 'datetime',
    ];
}