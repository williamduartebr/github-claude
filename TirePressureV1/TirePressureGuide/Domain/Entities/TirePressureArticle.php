<?php

namespace Src\ContentGeneration\TirePressureGuide\Domain\Entities;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class TirePressureArticle
 * 
 * Model principal para artigos de calibragem de pneus
 * Sistema em duas etapas: Geração inicial + Refinamento Claude
 * 
 * NOVA FUNCIONALIDADE: Seções separadas para refinamento granular Claude
 * 
 * @package Src\ContentGeneration\TirePressureGuide\Domain\Entities
 */
class TirePressureArticle extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'tire_pressure_articles';
    protected $guarded = ['_id'];

    protected $casts = [
        'vehicle_data' => 'array',
        'article_content' => 'array',
        'seo_keywords' => 'array',
        'claude_enhancements' => 'array',
        'quality_issues' => 'array',
        
        // NOVO: Seções separadas para refinamento Claude
        'sections_intro' => 'array',
        'sections_pressure_table' => 'array',
        'sections_how_to_calibrate' => 'array',
        'sections_middle_content' => 'array',
        'sections_faq' => 'array',
        'sections_conclusion' => 'array',
        'sections_refined' => 'array',
        'sections_scores' => 'array',
        'sections_status' => 'array',
        
        'claude_last_enhanced_at' => 'datetime',
        'sections_last_refined_at' => 'datetime',
        'processed_at' => 'datetime',
        'quality_checked' => 'boolean',
        'pressure_light_front' => 'decimal:1',
        'pressure_light_rear' => 'decimal:1',
        'pressure_spare' => 'decimal:1',
        'content_score' => 'decimal:2',
        'blog_published_time' => 'datetime',
        'blog_modified_time' => 'datetime',
        'blog_synced' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $fillable = [
        // Dados básicos do veículo
        'make',
        'model',
        'year',
        'tire_size',
        'vehicle_data',

        // Conteúdo do artigo (estruturado)
        'title',
        'slug',
        'wordpress_slug',
        'article_content', // Mantém versão consolidada
        'template_used',

        // NOVO: Seções separadas para refinamento Claude (Etapa 2)
        'sections_intro',           // Introdução
        'sections_pressure_table',  // Tabela de pressões
        'sections_how_to_calibrate', // Como calibrar
        'sections_middle_content',  // Conteúdo intermediário (dicas, avisos)
        'sections_faq',            // FAQ (se houver)
        'sections_conclusion',     // Conclusão
        
        // Controle de refinamento por seção
        'sections_refined',        // Array: quais seções já foram refinadas
        'sections_scores',         // Array: score individual de cada seção
        'sections_status',         // Array: status de cada seção (pending, refining, refined)
        'sections_last_refined_at', // Timestamp da última refinamento de seção

        // SEO e URLs
        'meta_description',
        'seo_keywords',
        'wordpress_url',
        'amp_url',
        'canonical_url',

        // Status de geração (Etapas 1 e 2)
        'generation_status', // pending, generated, claude_enhanced, published

        // Claude API Enhancement (Etapa 2)
        'claude_enhancements',
        'claude_last_enhanced_at',
        'claude_enhancement_count',

        // Dados técnicos de pressão dos pneus
        'pressure_empty_front',
        'pressure_empty_rear',
        'pressure_light_front',
        'pressure_light_rear',
        'pressure_max_front',
        'pressure_max_rear',
        'pressure_spare',

        // Classificação e categoria
        'category',

        // Controle de qualidade
        'quality_checked',
        'quality_issues',
        'content_score',

        // Controle de lotes e processamento
        'batch_id',
        'processed_at',

        // Integração com blog WordPress
        'blog_id',
        'blog_status',
        'blog_published_time',
        'blog_modified_time',
        'blog_synced',

        // Timestamps padrão
        'created_at',
        'updated_at'
    ];

    /**
     * Definir índices para MongoDB
     * Executar via migration separada
     */
    public static function createIndexes(): void
    {
        $collection = (new static)->getCollection();
        
        // Índices básicos
        $collection->createIndex(['make' => 1]);
        $collection->createIndex(['model' => 1]); 
        $collection->createIndex(['year' => 1]);
        $collection->createIndex(['generation_status' => 1]);
        $collection->createIndex(['batch_id' => 1]);
        
        // Índice único para slug WordPress
        $collection->createIndex(['wordpress_slug' => 1], ['unique' => true, 'name' => 'unique_wordpress_slug_tire_pressure']);
        
        // Índices compostos para performance
        $collection->createIndex(['make' => 1, 'model' => 1, 'year' => 1], ['name' => 'vehicle_composite_index']);
        $collection->createIndex(['generation_status' => 1, 'created_at' => 1], ['name' => 'status_created_index']);
        $collection->createIndex(['generation_status' => 1, 'claude_enhancement_count' => 1], ['name' => 'claude_ready_index']);
        
        // Índices para dados aninhados (MongoDB feature)
        $collection->createIndex(['vehicle_data.vehicle_type' => 1]);
        $collection->createIndex(['vehicle_data.main_category' => 1]);
        $collection->createIndex(['vehicle_data.is_motorcycle' => 1]);
    }

    // =======================================================================
    // MÉTODOS PARA GESTÃO DAS SEÇÕES SEPARADAS (NOVA FUNCIONALIDADE)
    // =======================================================================

    /**
     * Quebrar article_content em seções separadas
     * Chama após gerar o artigo inicial (Etapa 1)
     */
    public function breakIntoSections(): void
    {
        $content = $this->article_content['sections'] ?? [];
        $warnings = $this->article_content['warnings'] ?? [];
        $tips = $this->article_content['tips'] ?? [];
        
        // Mapear seções do article_content para campos separados
        $this->sections_intro = $content['introduction'] ?? null;
        $this->sections_pressure_table = $content['pressure_table'] ?? null;
        $this->sections_how_to_calibrate = $content['how_to_calibrate'] ?? null;
        
        // Middle content inclui: middle_content + maintenance_checklist + tips + warnings
        $this->sections_middle_content = [
            'middle_content' => $content['middle_content'] ?? null,
            'maintenance_checklist' => $content['maintenance_checklist'] ?? null,
            'tips' => $tips,
            'warnings' => $warnings
        ];
        
        $this->sections_faq = $content['faq'] ?? null;
        $this->sections_conclusion = $content['conclusion'] ?? null;
        
        // Inicializar controles de refinamento
        $this->sections_refined = [];
        $this->sections_scores = [
            'intro' => $this->calculateSectionScore('intro'),
            'pressure_table' => $this->calculateSectionScore('pressure_table'),
            'how_to_calibrate' => $this->calculateSectionScore('how_to_calibrate'),
            'middle_content' => $this->calculateSectionScore('middle_content'),
            'faq' => $this->calculateSectionScore('faq'),
            'conclusion' => $this->calculateSectionScore('conclusion')
        ];
        $this->sections_status = [
            'intro' => 'pending',
            'pressure_table' => 'pending',
            'how_to_calibrate' => 'pending',
            'middle_content' => 'pending',
            'faq' => 'pending',
            'conclusion' => 'pending'
        ];
        
        $this->save();
    }

    /**
     * Consolidar seções refinadas de volta no article_content
     * Chama após refinamento Claude completo
     */
    public function consolidateSections(): void
    {
        // Extrair tips e warnings do sections_middle_content
        $middleContent = $this->sections_middle_content ?? [];
        
        $this->article_content = [
            'sections' => [
                'introduction' => $this->sections_intro,
                'middle_content' => $middleContent['middle_content'] ?? null,
                'pressure_table' => $this->sections_pressure_table,
                'how_to_calibrate' => $this->sections_how_to_calibrate,
                'maintenance_checklist' => $middleContent['maintenance_checklist'] ?? null,
                'faq' => $this->sections_faq,
                'conclusion' => $this->sections_conclusion
            ],
            'warnings' => $middleContent['warnings'] ?? [],
            'tips' => $middleContent['tips'] ?? [],
            'metadata' => [
                'sections_refined' => $this->sections_refined,
                'sections_scores' => $this->sections_scores,
                'last_consolidated_at' => now()->toISOString(),
                'vehicle_type' => $this->is_motorcycle ? 'motorcycle' : 'car',
                'generated_at' => $this->created_at?->toISOString(),
                'template_version' => '1.0'
            ]
        ];
        
        $this->save();
    }

    /**
     * Refinar uma seção específica via Claude
     */
    public function refineSection(string $sectionName, $refinedContent, array $metadata = []): bool
    {
        if (!$this->isSectionRefinable($sectionName)) {
            return false;
        }

        // Atualizar a seção específica
        $sectionField = "sections_{$sectionName}";
        
        switch ($sectionName) {
            case 'middle_content':
                // Para middle_content, atualizar subsecção específica
                $subsection = $metadata['subsection'] ?? 'middle_content';
                $currentMiddleContent = $this->sections_middle_content ?? [];
                
                if ($subsection === 'tips') {
                    // Para tips, espera-se um array
                    $currentMiddleContent['tips'] = is_array($refinedContent) ? $refinedContent : [$refinedContent];
                } elseif ($subsection === 'warnings') {
                    // Para warnings, espera-se um array  
                    $currentMiddleContent['warnings'] = is_array($refinedContent) ? $refinedContent : [$refinedContent];
                } else {
                    // Para middle_content e maintenance_checklist, conteúdo direto
                    $currentMiddleContent[$subsection] = $refinedContent;
                }
                
                $this->$sectionField = $currentMiddleContent;
                break;
                
            case 'intro':
            case 'conclusion':
                // Seções de texto simples - conteúdo direto
                $this->$sectionField = $refinedContent;
                break;
                
            case 'pressure_table':
                // Tabela de pressões - estrutura específica
                $this->$sectionField = $refinedContent;
                break;
                
            case 'how_to_calibrate':
                // Lista de passos - estrutura específica
                $this->$sectionField = $refinedContent;
                break;
                
            case 'faq':
                // FAQ - array de perguntas e respostas
                $this->$sectionField = $refinedContent;
                break;
                
            default:
                // Fallback para seções não mapeadas
                $this->$sectionField = $refinedContent;
                break;
        }

        // Atualizar controles
        $this->markSectionAsRefined($sectionName, $metadata);
        
        return true;
    }

    /**
     * Marcar seção como refinada
     */
    public function markSectionAsRefined(string $sectionName, array $metadata = []): void
    {
        $sectionsRefined = $this->sections_refined ?? [];
        $sectionsRefined[$sectionName] = [
            'refined_at' => now()->toISOString(),
            'metadata' => $metadata
        ];
        $this->sections_refined = $sectionsRefined;

        // Atualizar status
        $sectionsStatus = $this->sections_status ?? [];
        $sectionsStatus[$sectionName] = 'refined';
        $this->sections_status = $sectionsStatus;

        // Atualizar score da seção
        $sectionsScores = $this->sections_scores ?? [];
        $sectionsScores[$sectionName] = $this->calculateSectionScore($sectionName);
        $this->sections_scores = $sectionsScores;

        $this->sections_last_refined_at = now();
        $this->save();
    }

    /**
     * Verificar se seção pode ser refinada
     */
    public function isSectionRefinable(string $sectionName): bool
    {
        $validSections = ['intro', 'pressure_table', 'how_to_calibrate', 'middle_content', 'faq', 'conclusion'];
        
        if (!in_array($sectionName, $validSections)) {
            return false;
        }

        $sectionField = "sections_{$sectionName}";
        
        // Verificar se a seção existe
        if (empty($this->$sectionField)) {
            return false;
        }

        // Verificar se não foi refinada muitas vezes
        $refined = $this->sections_refined[$sectionName] ?? null;
        if ($refined && isset($refined['refine_count']) && $refined['refine_count'] >= 3) {
            return false;
        }

        return true;
    }

    /**
     * Calcular score de uma seção específica
     */
    public function calculateSectionScore(string $sectionName): float
    {
        $sectionField = "sections_{$sectionName}";
        $content = $this->$sectionField;

        if (empty($content)) {
            return 0.0;
        }

        $score = 5.0; // Base score

        switch ($sectionName) {
            case 'intro':
                $score += $this->scoreSectionIntro($content);
                break;
            case 'pressure_table':
                $score += $this->scoreSectionPressureTable($content);
                break;
            case 'how_to_calibrate':
                $score += $this->scoreSectionHowToCalibrate($content);
                break;
            case 'middle_content':
                $score += $this->scoreSectionMiddleContent($content);
                break;
            case 'faq':
                $score += $this->scoreSectionFaq($content);
                break;
            case 'conclusion':
                $score += $this->scoreSectionConclusion($content);
                break;
            default:
                // Para seções não mapeadas, score básico
                $score += 1.0;
                break;
        }

        return min(10.0, round($score, 1));
    }

    /**
     * Obter seção específica para refinamento
     */
    public function getSectionForRefinement(string $sectionName): ?array
    {
        if (!$this->isSectionRefinable($sectionName)) {
            return null;
        }

        $sectionField = "sections_{$sectionName}";
        $content = $this->$sectionField;

        return [
            'section_name' => $sectionName,
            'content' => $content,
            'current_score' => $this->sections_scores[$sectionName] ?? 0,
            'status' => $this->sections_status[$sectionName] ?? 'pending',
            'vehicle_data' => $this->vehicle_data,
            'context' => [
                'make' => $this->make,
                'model' => $this->model,
                'year' => $this->year,
                'tire_size' => $this->tire_size,
                'pressures' => [
                    'front' => $this->pressure_empty_front,
                    'rear' => $this->pressure_empty_rear
                ]
            ]
        ];
    }

    /**
     * Obter progresso de refinamento
     */
    public function getSectionsProgress(): array
    {
        $totalSections = 6;
        $refinedSections = count($this->sections_refined ?? []);
        
        return [
            'total_sections' => $totalSections,
            'refined_sections' => $refinedSections,
            'progress_percentage' => round(($refinedSections / $totalSections) * 100, 1),
            'sections_status' => $this->sections_status ?? [],
            'sections_scores' => $this->sections_scores ?? [],
            'is_complete' => $refinedSections >= $totalSections
        ];
    }

    // =======================================================================
    // MÉTODOS AUXILIARES PARA SCORING POR SEÇÃO
    // =======================================================================

    private function scoreSectionIntro($content): float
    {
        $score = 0;
        
        // Se for array com estrutura do sistema (title, content, type)
        if (is_array($content) && isset($content['content'])) {
            if (strlen($content['content']) > 100) {
                $score += 2.0;
            }
            if (strlen($content['content']) > 200) {
                $score += 0.5; // Bonus para introduções mais detalhadas
            }
        }
        // Se for string direta
        elseif (is_string($content) && strlen($content) > 100) {
            $score += 2.0;
        }
        
        return $score;
    }

    private function scoreSectionPressureTable($content): float
    {
        $score = 0;
        
        // Estrutura esperada: ['title' => '...', 'content' => ['headers' => [...], 'rows' => [...]], 'type' => 'table']
        if (is_array($content) && isset($content['content'])) {
            $tableContent = $content['content'];
            
            if (isset($tableContent['rows']) && count($tableContent['rows']) >= 3) {
                $score += 3.0;
            }
            
            if (isset($tableContent['headers']) && count($tableContent['headers']) >= 3) {
                $score += 1.0;
            }
        }
        
        return $score;
    }

    private function scoreSectionHowToCalibrate($content): float
    {
        $score = 0;
        
        // Estrutura esperada: ['title' => '...', 'content' => [...], 'type' => 'list']
        if (is_array($content) && isset($content['content'])) {
            $steps = $content['content'];
            
            if (is_array($steps) && count($steps) >= 5) {
                $score += 2.5;
            }
            
            if (is_array($steps) && count($steps) >= 3) {
                $score += 1.0; // Mínimo aceitável
            }
        }
        
        return $score;
    }

    private function scoreSectionMiddleContent(array $content): float
    {
        $score = 0;
        
        // Middle content principal
        if (isset($content['middle_content']) && !empty($content['middle_content'])) {
            $score += 1.5;
        }
        
        // Maintenance checklist
        if (isset($content['maintenance_checklist']) && !empty($content['maintenance_checklist'])) {
            $score += 1.0;
        }
        
        // Tips array
        if (isset($content['tips']) && is_array($content['tips']) && count($content['tips']) > 0) {
            $score += 0.5;
        }
        
        // Warnings array
        if (isset($content['warnings']) && is_array($content['warnings']) && count($content['warnings']) > 0) {
            $score += 0.5;
        }
        
        return $score;
    }

    private function scoreSectionFaq($content): float
    {
        $score = 0;
        
        // Estrutura esperada: ['title' => '...', 'content' => [['question' => '...', 'answer' => '...'], ...], 'type' => 'faq']
        if (is_array($content) && isset($content['content'])) {
            $questions = $content['content'];
            
            if (is_array($questions) && count($questions) >= 3) {
                $score += 2.0;
            }
            
            // Bonus para FAQs mais completas
            if (is_array($questions) && count($questions) >= 5) {
                $score += 0.5;
            }
        }
        
        return $score;
    }

    private function scoreSectionConclusion($content): float
    {
        $score = 0;
        
        // Se for array com estrutura do sistema (title, content, type)
        if (is_array($content) && isset($content['content'])) {
            if (strlen($content['content']) > 80) {
                $score += 1.5;
            }
            
            // Verificar se menciona call-to-action ou segurança
            $text = strtolower($content['content']);
            if (strpos($text, 'segurança') !== false || strpos($text, 'calibr') !== false) {
                $score += 0.5;
            }
        }
        // Se for string direta
        elseif (is_string($content) && strlen($content) > 80) {
            $score += 1.5;
        }
        
        return $score;
    }

    // =======================================================================
    // SCOPES PARA FILTRAGEM (ETAPAS 1 E 2 + SEÇÕES)
    // =======================================================================

    /**
     * Scope: Artigos pendentes para geração inicial (Etapa 1)
     */
    public function scopePendingGeneration($query)
    {
        return $query->where('generation_status', 'pending');
    }

    /**
     * Scope: Artigos já gerados na etapa inicial
     */
    public function scopeGenerated($query)
    {
        return $query->where('generation_status', 'generated');
    }

    /**
     * Scope: Artigos prontos para refinamento Claude (Etapa 2)
     */
    public function scopeReadyForClaude($query)
    {
        return $query->where('generation_status', 'generated')
                    ->where(function($q) {
                        $q->whereNull('claude_enhancement_count')
                          ->orWhere('claude_enhancement_count', '<', 3);
                    });
    }

    /**
     * Scope: Artigos já refinados pelo Claude
     */
    public function scopeClaudeEnhanced($query)
    {
        return $query->where('generation_status', 'claude_enhanced');
    }

    /**
     * Scope: Artigos publicados
     */
    public function scopePublished($query)
    {
        return $query->where('generation_status', 'published');
    }

    /**
     * Scope: Filtrar por marca
     */
    public function scopeByMake($query, string $make)
    {
        return $query->where('make', $make);
    }

    /**
     * Scope: Filtrar por tipo de veículo
     */
    public function scopeByVehicleType($query, string $type)
    {
        return $query->where('vehicle_data.vehicle_type', $type);
    }

    /**
     * Scope: Filtrar por faixa de anos
     */
    public function scopeByYearRange($query, int $yearFrom = null, int $yearTo = null)
    {
        if ($yearFrom) {
            $query->where('year', '>=', $yearFrom);
        }
        
        if ($yearTo) {
            $query->where('year', '<=', $yearTo);
        }
        
        return $query;
    }

    /**
     * Scope: Filtrar por lote
     */
    public function scopeByBatch($query, string $batchId)
    {
        return $query->where('batch_id', $batchId);
    }

    /**
     * Scope: Artigos com seções prontas para refinamento
     */
    public function scopeWithSectionsReadyForRefinement($query)
    {
        return $query->where('generation_status', 'generated')
                    ->whereNotNull('sections_intro')
                    ->where(function($q) {
                        $q->whereNull('sections_refined')
                          ->orWhereRaw('JSON_LENGTH(sections_refined) < 6');
                    });
    }

    /**
     * Scope: Artigos com refinamento de seções completo
     */
    public function scopeWithSectionsComplete($query)
    {
        return $query->whereNotNull('sections_refined')
                    ->whereRaw('JSON_LENGTH(sections_refined) >= 6');
    }

    // =======================================================================
    // ACCESSORS E MUTATORS
    // =======================================================================

    /**
     * Acessor: Verificar se é motocicleta
     */
    public function getIsMotorcycleAttribute(): bool
    {
        return $this->vehicle_data['is_motorcycle'] ?? false;
    }

    /**
     * Acessor: Obter nome completo do veículo
     */
    public function getVehicleFullNameAttribute(): string
    {
        return "{$this->make} {$this->model} {$this->year}";
    }

    /**
     * Acessor: Obter pressão para exibição
     */
    public function getPressureDisplayAttribute(): string
    {
        if ($this->is_motorcycle) {
            return "Dianteiro: {$this->pressure_empty_front} PSI / Traseiro: {$this->pressure_empty_rear} PSI";
        }
        
        return "Dianteiros: {$this->pressure_empty_front} PSI / Traseiros: {$this->pressure_empty_rear} PSI";
    }

    /**
     * Acessor: Status de refinamento Claude
     */
    public function getClaudeStatusAttribute(): string
    {
        if ($this->generation_status === 'claude_enhanced') {
            return 'Refinado pelo Claude';
        }
        
        if ($this->generation_status === 'generated' && $this->claude_enhancement_count > 0) {
            return "Parcialmente refinado ({$this->claude_enhancement_count}x)";
        }
        
        if ($this->generation_status === 'generated') {
            return 'Pronto para refinamento';
        }
        
        return 'Aguardando geração';
    }

    /**
     * Acessor: Progresso das seções em texto
     */
    public function getSectionsProgressTextAttribute(): string
    {
        $progress = $this->getSectionsProgress();
        return "{$progress['refined_sections']}/{$progress['total_sections']} seções ({$progress['progress_percentage']}%)";
    }

    /**
     * Mutator: Gerar slug WordPress automaticamente
     */
    public function setWordpressSlugAttribute($value)
    {
        if (!$value && $this->make && $this->model && $this->year) {
            $this->attributes['wordpress_slug'] = $this->generateWordPressSlug();
        } else {
            $this->attributes['wordpress_slug'] = $value;
        }
    }

    // =======================================================================
    // MÉTODOS DE NEGÓCIO
    // =======================================================================

    /**
     * Gerar slug compatível com WordPress
     * Formato: calibragem-pneu-[marca]-[modelo]-[ano]
     */
    public function generateWordPressSlug(): string
    {
        $make = $this->slugify($this->make);
        $model = $this->slugify($this->model);
        $year = $this->year;
        
        return "calibragem-pneu-{$make}-{$model}-{$year}";
    }

    /**
     * Converter string para slug
     */
    private function slugify(string $text): string
    {
        // Remover acentos
        $text = $this->removeAccents($text);
        
        // Converter para minúsculas
        $text = strtolower($text);
        
        // Remover caracteres especiais e substituir por hífen
        $text = preg_replace('/[^a-z0-9\-_]/', '-', $text);
        
        // Remover hífens múltiplos
        $text = preg_replace('/-+/', '-', $text);
        
        // Remover hífens do início e fim
        return trim($text, '-');
    }

    /**
     * Remover acentos
     */
    private function removeAccents(string $text): string
    {
        $unwanted = [
            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'ÿ' => 'y', 'ñ' => 'n', 'ç' => 'c',
            'Á' => 'A', 'À' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
            'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ó' => 'O', 'Ò' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
            'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ý' => 'Y', 'Ñ' => 'N', 'Ç' => 'C',
        ];

        return strtr($text, $unwanted);
    }

    /**
     * Marcar como gerado (Etapa 1 concluída)
     */
    public function markAsGenerated(): void
    {
        $this->generation_status = 'generated';
        $this->processed_at = now();
        $this->save();
        
        // Automaticamente quebrar em seções após geração
        $this->breakIntoSections();
    }

    /**
     * Marcar como refinado pelo Claude (Etapa 2 concluída)
     */
    public function markAsClaudeEnhanced(): void
    {
        $this->generation_status = 'claude_enhanced';
        $this->claude_last_enhanced_at = now();
        $this->claude_enhancement_count = ($this->claude_enhancement_count ?? 0) + 1;
        
        // Consolidar seções refinadas
        $this->consolidateSections();
        
        $this->save();
    }

    /**
     * Adicionar refinamento Claude ao histórico
     */
    public function addClaudeEnhancement(string $section, string $originalContent, string $enhancedContent, array $metadata = []): void
    {
        $enhancements = $this->claude_enhancements ?? [];
        
        $enhancements[] = [
            'timestamp' => now()->toISOString(),
            'section' => $section,
            'original_content' => $originalContent,
            'enhanced_content' => $enhancedContent,
            'metadata' => $metadata
        ];
        
        $this->claude_enhancements = $enhancements;
        $this->save();
    }

    /**
     * Verificar se pode ser refinado pelo Claude
     */
    public function canBeEnhancedByClaude(): bool
    {
        return $this->generation_status === 'generated' && 
               ($this->claude_enhancement_count ?? 0) < 3;
    }

    /**
     * Obter URL completa do WordPress
     */
    public function getWordPressUrl(): string
    {
        return $this->wordpress_url ?? "https://mercadoveiculos.com/info/{$this->wordpress_slug}/";
    }

    /**
     * Obter URL canônica
     */
    public function getCanonicalUrl(): string
    {
        return $this->canonical_url ?? $this->getWordPressUrl();
    }

    // =======================================================================
    // ESTATÍSTICAS E RELATÓRIOS
    // =======================================================================

    /**
     * Estatísticas gerais do sistema
     */
    public static function getGenerationStatistics(): array
    {
        $total = static::count();
        $pending = static::where('generation_status', 'pending')->count();
        $generated = static::where('generation_status', 'generated')->count();
        $enhanced = static::where('generation_status', 'claude_enhanced')->count();
        $published = static::where('generation_status', 'published')->count();
        
        return [
            'total' => $total,
            'pending' => $pending,
            'generated' => $generated,
            'claude_enhanced' => $enhanced,
            'published' => $published,
            'ready_for_claude' => static::readyForClaude()->count(),
            'sections_ready' => static::withSectionsReadyForRefinement()->count(),
            'sections_complete' => static::withSectionsComplete()->count()
        ];
    }

    /**
     * Relatório por marca
     */
    public static function getStatisticsByMake(): Collection
    {
        return static::raw(function($collection) {
            return $collection->aggregate([
                [
                    '$group' => [
                        '_id' => '$make',
                        'total' => ['$sum' => 1],
                        'generated' => [
                            '$sum' => [
                                '$cond' => [
                                    ['$eq' => ['$generation_status', 'generated']], 
                                    1, 
                                    0
                                ]
                            ]
                        ],
                        'claude_enhanced' => [
                            '$sum' => [
                                '$cond' => [
                                    ['$eq' => ['$generation_status', 'claude_enhanced']], 
                                    1, 
                                    0
                                ]
                            ]
                        ],
                        'sections_complete' => [
                            '$sum' => [
                                '$cond' => [
                                    ['$gte' => [['$size' => ['$ifNull' => ['$sections_refined', []]]], 6]], 
                                    1, 
                                    0
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    '$sort' => ['total' => -1]
                ]
            ]);
        });
    }

    /**
     * Estatísticas de progresso das seções
     */
    public static function getSectionsStatistics(): array
    {
        $stats = [
            'total_articles_with_sections' => static::whereNotNull('sections_intro')->count(),
            'articles_sections_complete' => static::withSectionsComplete()->count(),
            'articles_sections_pending' => static::withSectionsReadyForRefinement()->count(),
        ];

        // Estatísticas por seção individual
        $sectionNames = ['intro', 'pressure_table', 'how_to_calibrate', 'middle_content', 'faq', 'conclusion'];
        
        foreach ($sectionNames as $section) {
            $stats["section_{$section}_refined"] = static::whereRaw("JSON_EXTRACT(sections_refined, '$.{$section}') IS NOT NULL")->count();
        }

        // Distribuição de scores por seção
        $stats['average_section_scores'] = [];
        foreach ($sectionNames as $section) {
            $avgScore = static::whereNotNull('sections_scores')
                           ->get()
                           ->avg(function($article) use ($section) {
                               return $article->sections_scores[$section] ?? 0;
                           });
            $stats['average_section_scores'][$section] = round($avgScore, 2);
        }

        return $stats;
    }

    /**
     * Relatório de progresso de refinamento
     */
    public static function getRefinementProgressReport(): array
    {
        $articles = static::where('generation_status', 'generated')
                         ->whereNotNull('sections_intro')
                         ->get();

        $progressDistribution = [
            '0%' => 0,    // Nenhuma seção refinada
            '1-25%' => 0, // 1 seção refinada
            '26-50%' => 0, // 2-3 seções refinadas
            '51-75%' => 0, // 4-5 seções refinadas
            '100%' => 0   // Todas as 6 seções refinadas
        ];

        foreach ($articles as $article) {
            $progress = $article->getSectionsProgress();
            $percentage = $progress['progress_percentage'];

            if ($percentage == 0) {
                $progressDistribution['0%']++;
            } elseif ($percentage <= 25) {
                $progressDistribution['1-25%']++;
            } elseif ($percentage <= 50) {
                $progressDistribution['26-50%']++;
            } elseif ($percentage <= 75) {
                $progressDistribution['51-75%']++;
            } else {
                $progressDistribution['100%']++;
            }
        }

        return [
            'total_articles' => $articles->count(),
            'progress_distribution' => $progressDistribution,
            'average_progress' => round($articles->avg(function($article) {
                return $article->getSectionsProgress()['progress_percentage'];
            }), 2)
        ];
    }

    /**
     * Obter próximo lote de seções para refinamento
     */
    public static function getNextSectionsForRefinement(int $limit = 50): Collection
    {
        return static::withSectionsReadyForRefinement()
                    ->orderBy('created_at', 'asc')
                    ->limit($limit)
                    ->get()
                    ->map(function($article) {
                        $sectionsToRefine = [];
                        $sectionNames = ['intro', 'pressure_table', 'how_to_calibrate', 'middle_content', 'faq', 'conclusion'];
                        
                        foreach ($sectionNames as $section) {
                            if ($article->isSectionRefinable($section)) {
                                $sectionsToRefine[] = $article->getSectionForRefinement($section);
                            }
                        }
                        
                        return [
                            'article_id' => $article->id,
                            'vehicle' => $article->vehicle_full_name,
                            'sections_to_refine' => $sectionsToRefine,
                            'priority_score' => $article->content_score ?? 5.0
                        ];
                    })
                    ->filter(function($item) {
                        return !empty($item['sections_to_refine']);
                    });
    }

    /**
     * Marcar seção como em processamento (evitar duplicação)
     */
    public function markSectionAsProcessing(string $sectionName): bool
    {
        if (!$this->isSectionRefinable($sectionName)) {
            return false;
        }

        $sectionsStatus = $this->sections_status ?? [];
        $sectionsStatus[$sectionName] = 'processing';
        $this->sections_status = $sectionsStatus;
        $this->save();

        return true;
    }

    /**
     * Reverter seção para status pendente (em caso de erro)
     */
    public function revertSectionToPending(string $sectionName): bool
    {
        $sectionsStatus = $this->sections_status ?? [];
        
        if (isset($sectionsStatus[$sectionName])) {
            $sectionsStatus[$sectionName] = 'pending';
            $this->sections_status = $sectionsStatus;
            $this->save();
            return true;
        }

        return false;
    }

    /**
     * Obter tempo estimado para conclusão do refinamento
     */
    public function getEstimatedRefinementTime(): array
    {
        $progress = $this->getSectionsProgress();
        $remainingSections = $progress['total_sections'] - $progress['refined_sections'];
        
        // Estimativa: 30 segundos por seção (baseado na API Claude)
        $estimatedSeconds = $remainingSections * 30;
        
        return [
            'remaining_sections' => $remainingSections,
            'estimated_seconds' => $estimatedSeconds,
            'estimated_minutes' => round($estimatedSeconds / 60, 1),
            'estimated_formatted' => $this->formatEstimatedTime($estimatedSeconds)
        ];
    }

    /**
     * Formatar tempo estimado em texto legível
     */
    private function formatEstimatedTime(int $seconds): string
    {
        if ($seconds < 60) {
            return "{$seconds} segundos";
        }
        
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;
        
        if ($minutes < 60) {
            return $remainingSeconds > 0 
                ? "{$minutes}min {$remainingSeconds}s" 
                : "{$minutes} minutos";
        }
        
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        return $remainingMinutes > 0 
            ? "{$hours}h {$remainingMinutes}min" 
            : "{$hours} horas";
    }

    /**
     * Validar integridade das seções
     */
    public function validateSectionsIntegrity(): array
    {
        $issues = [];
        $sectionNames = ['intro', 'pressure_table', 'how_to_calibrate', 'middle_content', 'faq', 'conclusion'];
        
        foreach ($sectionNames as $section) {
            $sectionField = "sections_{$section}";
            $content = $this->$sectionField;
            
            if (empty($content)) {
                $issues[] = "Seção '{$section}' está vazia";
                continue;
            }
            
            // Validações específicas por seção
            switch ($section) {
                case 'intro':
                    if (!isset($content['content']) || strlen($content['content']) < 50) {
                        $issues[] = "Introdução muito curta (< 50 caracteres)";
                    }
                    break;
                    
                case 'pressure_table':
                    if (!isset($content['content']['rows']) || count($content['content']['rows']) < 2) {
                        $issues[] = "Tabela de pressão incompleta (< 2 linhas)";
                    }
                    break;
                    
                case 'how_to_calibrate':
                    if (!isset($content['steps']) || count($content['steps']) < 3) {
                        $issues[] = "Passos de calibragem insuficientes (< 3 passos)";
                    }
                    break;
                    
                case 'middle_content':
                    $hasContent = !empty($content['middle_content']) || 
                                 !empty($content['maintenance_checklist']) ||
                                 (!empty($content['tips']) && is_array($content['tips'])) ||
                                 (!empty($content['warnings']) && is_array($content['warnings']));
                    if (!$hasContent) {
                        $issues[] = "Conteúdo intermediário vazio (sem middle_content, maintenance_checklist, tips ou warnings)";
                    }
                    break;
                    
                case 'conclusion':
                    if (!isset($content['content']) || strlen($content['content']) < 30) {
                        $issues[] = "Conclusão muito curta (< 30 caracteres)";
                    }
                    break;
            }
        }
        
        return [
            'is_valid' => empty($issues),
            'issues' => $issues,
            'sections_count' => count($sectionNames),
            'valid_sections' => count($sectionNames) - count($issues)
        ];
    }

    /**
     * toString para debug
     */
    public function __toString(): string
    {
        $progress = $this->getSectionsProgress();
        return "TirePressureArticle[{$this->vehicle_full_name}] - Status: {$this->generation_status} - Seções: {$progress['progress_percentage']}%";
    }

    /**
     * Método para debug - dump de seções
     */
    public function dumpSections(): array
    {
        return [
            'article_id' => $this->id,
            'vehicle' => $this->vehicle_full_name,
            'generation_status' => $this->generation_status,
            'sections_status' => $this->sections_status ?? [],
            'sections_scores' => $this->sections_scores ?? [],
            'sections_refined' => $this->sections_refined ?? [],
            'progress' => $this->getSectionsProgress(),
            'validation' => $this->validateSectionsIntegrity(),
            'estimated_time' => $this->getEstimatedRefinementTime()
        ];
    }
}