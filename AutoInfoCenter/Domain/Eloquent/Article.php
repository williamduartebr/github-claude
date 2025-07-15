<?php

namespace Src\AutoInfoCenter\Domain\Eloquent;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $table = 'articles';
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
        'content' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'tags' => 'array',
        'related_topics' => 'array',
        'author' => 'array',
        'vehicle_info' => 'array',
        'filter_data' => 'array',
    ];
    
    /**
     * Obter artigos pelo status
     * 
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
    
    /**
     * Obter artigos pela categoria
     * 
     * @param string $categorySlug
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCategory($query, $categorySlug)
    {
        return $query->where('category_slug', $categorySlug);
    }
    
    /**
     * Busca artigos por texto
     * 
     * @param string $text
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $text)
    {
        return $query->whereRaw([
            '$text' => [
                '$search' => $text
            ]
        ]);
    }
    
    /**
     * Busca artigos relacionados a uma marca e modelo específicos
     * 
     * @param string $marca
     * @param string|null $modelo
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByVehicle($query, $marca, $modelo = null)
    {
        $query->where('extracted_entities.marca', $marca);
        
        if ($modelo) {
            $query->where('extracted_entities.modelo', $modelo);
        }
        
        return $query;
    }
    
    /**
     * Busca artigos por tag
     * 
     * @param string|array $tags
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByTags($query, $tags)
    {
        if (is_array($tags)) {
            return $query->whereIn('tags', $tags);
        }
        
        return $query->where('tags', $tags);
    }
    
    /**
     * Encontra artigos relacionados baseados em tags e extracted_entities
     * 
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findRelatedArticles($limit = 4)
    {
        $query = self::where('_id', '!=', $this->_id)
            ->where('status', 'published');
            
        // Procurar por artigos com as mesmas tags
        if (!empty($this->tags)) {
            $query->where(function($q) {
                $q->whereIn('tags', $this->tags);
            });
        }
        
        // Se tiver informações de veículo, considerar também
        if (!empty($this->extracted_entities['marca'])) {
            $query->orWhere('extracted_entities.marca', $this->extracted_entities['marca']);
            
            if (!empty($this->extracted_entities['modelo'])) {
                $query->orWhere('extracted_entities.modelo', $this->extracted_entities['modelo']);
            }
        }
        
        // Ordenar por relevância (artigos mais recentes)
        // Selecionar apenas campos necessários para reduzir uso de memória
        return $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get(['_id', 'title', 'slug', 'created_at', 'content.introducao']);
    }
    
    /**
     * Verifica e atualiza os tópicos relacionados
     * 
     * @return void
     */
    public function updateRelatedTopics()
    {
        // Obter tópicos relacionados da estrutura de conteúdo ou SEO
        $relatedTopics = $this->extractRelatedTopicsFromContent();
        
        if (!empty($relatedTopics)) {
            $this->related_topics = $relatedTopics;
            $this->save();
        }
    }
    
    /**
     * Extrai tópicos relacionados da estrutura de conteúdo
     * 
     * @return array
     */
    protected function extractRelatedTopicsFromContent()
    {
        $topics = [];
        
        // Tentar extrair de metadata.related_content
        if (!empty($this->metadata['related_content'])) {
            foreach ($this->metadata['related_content'] as $related) {
                if (!empty($related['title']) && !empty($related['slug'])) {
                    $topics[] = [
                        'title' => $related['title'],
                        'slug' => $related['slug'],
                        'icon' => $related['icon'] ?? null
                    ];
                }
            }
        }
        
        // Tentar extrair de seo_data.related_topics
        if (empty($topics) && !empty($this->seo_data['related_topics'])) {
            foreach ($this->seo_data['related_topics'] as $topic) {
                $topics[] = [
                    'title' => $topic,
                    'slug' => \Illuminate\Support\Str::slug($topic),
                    'icon' => null
                ];
            }
        }
        
        return $topics;
    }
}
