<?php

namespace Src\AutoInfoCenter\Domain\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleModelArticle extends Model
{
    protected $connection = 'mysql';
    protected $table = 'vehicle_models'; // Nome da tabela
    
    protected $fillable = [
        'article_id',
        'make',
        'make_slug',
        'model',
        'model_slug',
        'year_start',
        'year_end',
        'year_range',
        'engine',
        'version',
        'fuel',
        'category',
        'vehicle_type',
        'article_title',
        'article_slug'
    ];
    
    protected $casts = [
        'year_range' => 'boolean'
    ];
    
    /**
     * Obter a marca relacionada a este artigo
     *
     * @return BelongsTo
     */
    public function make(): BelongsTo
    {
        return $this->belongsTo(Make::class, 'make_slug', 'slug');
    }
    
    /**
     * Obter o modelo relacionado a este artigo
     *
     * @return BelongsTo
     */
    public function vehicleModel(): BelongsTo
    {
        return $this->belongsTo(VehicleModel::class, 'model_slug', 'slug')
            ->where('make_slug', $this->make_slug);
    }
    
    /**
     * Atualizar contadores nas tabelas relacionadas
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();
        
        // Ao criar um novo registro
        static::created(function ($vehicleModelArticle) {
            if (!empty($vehicleModelArticle->make_slug)) {
                $make = Make::where('slug', $vehicleModelArticle->make_slug)->first();
                if ($make) {
                    $make->incrementArticleCount();
                }
                
                if (!empty($vehicleModelArticle->model_slug)) {
                    $model = VehicleModel::where('slug', $vehicleModelArticle->model_slug)
                        ->where('make_slug', $vehicleModelArticle->make_slug)
                        ->first();
                    
                    if ($model) {
                        $model->incrementArticleCount();
                    }
                }
            }
        });
        
        // Ao excluir um registro
        static::deleted(function ($vehicleModelArticle) {
            if (!empty($vehicleModelArticle->make_slug)) {
                $make = Make::where('slug', $vehicleModelArticle->make_slug)->first();
                if ($make) {
                    $make->decrementArticleCount();
                }
                
                if (!empty($vehicleModelArticle->model_slug)) {
                    $model = VehicleModel::where('slug', $vehicleModelArticle->model_slug)
                        ->where('make_slug', $vehicleModelArticle->make_slug)
                        ->first();
                    
                    if ($model) {
                        $model->decrementArticleCount();
                    }
                }
            }
        });
    }
}
