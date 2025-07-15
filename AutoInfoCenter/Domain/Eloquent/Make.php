<?php

namespace Src\AutoInfoCenter\Domain\Eloquent;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Make extends Model
{
    protected $connection = 'mysql';
    
    protected $fillable = [
        'name',
        'slug',
        'logo_url',
        'description',
        'is_active',
        'article_count'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'article_count' => 'integer'
    ];
    
    /**
     * Obter todos os modelos relacionados a esta marca
     *
     * @return HasMany
     */
    public function models(): HasMany
    {
        return $this->hasMany(VehicleModel::class, 'make_slug', 'slug');
    }
    
    /**
     * Obter todos os artigos relacionados a esta marca
     *
     * @return HasMany
     */
    public function vehicleModels(): HasMany
    {
        return $this->hasMany(VehicleModelArticle::class, 'make_slug', 'slug');
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
