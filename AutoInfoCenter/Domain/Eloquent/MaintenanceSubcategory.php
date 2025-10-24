<?php

namespace Src\AutoInfoCenter\Domain\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Src\AutoInfoCenter\Domain\Eloquent\Article;

class MaintenanceSubcategory extends Model
{
    protected $connection = 'mysql';
    protected $table = 'maintenance_subcategories';

    protected $fillable = [
        'maintenance_category_id',
        'name',
        'slug',
        'description',
        'icon_svg',
        'icon_bg_color',
        'icon_text_color',
        'article_id',
        'display_order',
        'views',
        'likes',
        'is_active',
        'is_published',
        'published_at',
        'seo_info',
        'metadata'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'seo_info' => 'array',
        'metadata' => 'array',
        'views' => 'integer',
        'likes' => 'integer'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(MaintenanceCategory::class, 'maintenance_category_id');
    }

    public function article(): ?Article
    {
        if (!$this->article_id) {
            return null;
        }

        return Article::find($this->article_id);
    }

    public function syncWithArticle(Article $article): void
    {
        $this->update([
            'article_id' => $article->_id,
            'is_published' => $article->status === 'published',
            'published_at' => $article->status === 'published' ? now() : null
        ]);
    }

    public function incrementViews(): void
    {
        $this->increment('views');
    }

    public function incrementLikes(): void
    {
        $this->increment('likes');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeWithArticle($query)
    {
        return $query->whereNotNull('article_id');
    }
}