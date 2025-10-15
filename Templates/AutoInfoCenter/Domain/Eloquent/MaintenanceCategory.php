<?php

namespace Src\AutoInfoCenter\Domain\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaintenanceCategory extends Model
{
    protected $connection = 'mysql';
    protected $table = 'maintenance_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon_svg',
        'icon_bg_color',
        'icon_text_color',
        'display_order',
        'is_active',
        'seo_info',
        'info_sections',
        'to_follow'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'to_follow' => 'boolean',
        'seo_info' => 'array',
        'info_sections' => 'array'
    ];

    public function subcategories(): HasMany
    {
        return $this->hasMany(MaintenanceSubcategory::class, 'maintenance_category_id');
    }

    public function activeSubcategories(): HasMany
    {
        return $this->subcategories()->where('is_active', true);
    }

    public function publishedSubcategories(): HasMany
    {
        return $this->subcategories()->published();
    }
}
