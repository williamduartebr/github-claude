<?php

namespace Src\ArticleGenerator\Infrastructure\Eloquent;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class TempArticle extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $table = 'temp_articles';
    protected $guarded = ['_id'];
    
    /**
     * Atributos que devem ser convertidos em tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'metadata' => 'array',
        'extracted_entities' => 'array',
        'seo_data' => 'array',
        'published_at' => 'datetime',
        'modified_at' => 'datetime',
    ];
    
}