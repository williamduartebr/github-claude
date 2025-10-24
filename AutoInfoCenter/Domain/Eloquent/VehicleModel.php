<?php

namespace Src\AutoInfoCenter\Domain\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleModel extends Model
{
    protected $connection = 'mysql';
    protected $table = 'models'; // Nome da tabela

    protected $fillable = [
        'make_slug',
        'name',
        'slug',
        'image_url',
        'description',
        'is_active',
        'article_count'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'article_count' => 'integer'
    ];

    /**
     * Obter a marca relacionada a este modelo
     *
     * @return BelongsTo
     */
    public function make(): BelongsTo
    {
        return $this->belongsTo(Make::class, 'make_slug', 'slug');
    }

    /**
     * Obter todos os artigos relacionados a este modelo
     *
     * @return HasMany
     */
    public function vehicleModelArticles(): HasMany
    {
        return $this->hasMany(VehicleModelArticle::class, 'model_slug', 'slug')
            ->where('make_slug', $this->make_slug);
    }

    /**
     * Incrementar a contagem de artigos
     *
     * @return void
     */
    public function incrementArticleCount()
    {
        $this->increment('article_count');
    }

    /**
     * Decrementar a contagem de artigos
     *
     * @return void
     */
    public function decrementArticleCount()
    {
        if ($this->article_count > 0) {
            $this->decrement('article_count');
        }
    }
}
