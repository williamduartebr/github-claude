<?php

namespace Src\ContentGeneration\TirePressureGuide\Application\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;
use Src\ContentGeneration\TirePressureGuide\Domain\Entities\TirePressureArticle;

class TirePressureGuideApplicationService
{
    /**
     * Obter estatísticas dos artigos de calibragem
     */
    public function getArticleStats(): array
    {
        try {
            return [
                'total' => TirePressureArticle::count(),
                'generated' => TirePressureArticle::where('generation_status', 'generated')->count(),
                'claude_enhanced' => TirePressureArticle::where('generation_status', 'claude_enhanced')->count(),
                'published' => TirePressureArticle::where('generation_status', 'published')->count(),
                'sections_ready' => TirePressureArticle::withSectionsReadyForRefinement()->count(),
                'sections_complete' => TirePressureArticle::withSectionsComplete()->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao obter estatísticas dos artigos de calibragem: ' . $e->getMessage());
            return [
                'total' => 0,
                'generated' => 0,
                'claude_enhanced' => 0,
                'published' => 0,
                'sections_ready' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obter estatísticas do TempArticle
     */
    public function getTempArticleStats(): array
    {
        $total = TempArticle::count();
        $tirePressureGuide = TempArticle::where('source', 'tire_pressure_guide')->count();

        return [
            'total' => $total,
            'tire_pressure_guide' => $tirePressureGuide
        ];
    }

    /**
     * Publicar artigos para TempArticle collection
     */
    public function publishToTempArticles(
        string $status = 'claude_enhanced',
        int $limit = 100,
        bool $dryRun = false,
        ?callable $progressCallback = null
    ): object {
        $results = (object)[
            'published' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        try {
            // Buscar artigos que já foram refinados pelo Claude (seções completas)
            $articles = TirePressureArticle::where('generation_status', $status)
                ->withSectionsComplete() // Usar scope correto - artigos com refinamento completo
                ->orderBy('sections_last_refined_at', 'desc')
                ->limit($limit)
                ->get();

            $totalArticles = $articles->count();
            $processed = 0;

            foreach ($articles as $article) {
                try {
                    // Verificar se já existe no TempArticle
                    if ($this->tempArticleExists($article->slug)) {
                        $results->skipped++;
                        $results->errors[] = "Artigo já existe no TempArticle: {$article->slug}";
                        continue;
                    }

                    // Verificar se possui seções refinadas completas
                    if (!$this->hasCompletedSectionsRefinement($article)) {
                        $results->failed++;
                        $results->errors[] = "Artigo sem refinamento completo das seções: {$article->slug}";
                        continue;
                    }

                    if (!$dryRun) {
                        $tempArticleData = $this->convertToTempArticleFormat($article);

                        $tempArticle = new TempArticle();
                        $tempArticle->fill($tempArticleData);

                        if ($tempArticle->save()) {
                            $results->published++;
                        } else {
                            $results->failed++;
                            $results->errors[] = "Falha ao salvar no TempArticle: {$article->slug}";
                        }
                    } else {
                        $results->published++; // Simular para dry run
                    }
                } catch (\Exception $e) {
                    $results->failed++;
                    $results->errors[] = "Erro ao publicar {$article->slug}: " . $e->getMessage();
                }

                $processed++;
                if ($progressCallback) {
                    $progressCallback($processed, $totalArticles);
                }
            }
        } catch (\Exception $e) {
            $results->errors[] = "Erro ao buscar artigos: " . $e->getMessage();
        }

        return $results;
    }

    /**
     * Verificar se artigo já existe no TempArticle
     */
    private function tempArticleExists(string $slug): bool
    {
        try {
            return TempArticle::where('slug', $slug)->exists();
        } catch (\Exception $e) {
            Log::error('Erro ao verificar existência no TempArticle: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar se possui refinamento completo das seções
     */
    private function hasCompletedSectionsRefinement($article): bool
    {
        // Verificar se tem todas as 6 seções refinadas
        $sectionsRefined = $article->sections_refined ?? [];
        
        if (count($sectionsRefined) < 6) {
            return false;
        }

        // Verificar se todas as seções obrigatórias estão preenchidas
        $requiredSections = [
            'sections_intro',
            'sections_pressure_table', 
            'sections_how_to_calibrate',
            'sections_middle_content',
            'sections_faq',
            'sections_conclusion'
        ];

        foreach ($requiredSections as $section) {
            if (empty($article->$section)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Converter artigo para formato TempArticle
     */
    private function convertToTempArticleFormat($article): array
    {
        // Construir conteúdo das seções refinadas pelo Claude
        $refinedContent = $this->buildContentFromRefinedSections($article);

        // Extrair entidades do veículo
        $extractedEntities = [
            'marca' => $article->make ?? '',
            'modelo' => $article->model ?? '',
            'ano' => (string)($article->year ?? ''),
            'medida_pneu' => $article->tire_size ?? '',
            'versao' => 'Todas',
            'tipo_veiculo' => $this->mapVehicleType($article->vehicle_data['vehicle_type'] ?? 'car'),
            'categoria' => $article->category ?? 'hatch',
            'combustivel' => 'flex'
        ];

        // Dados SEO formatados para calibragem
        $seoFormatted = [
            'page_title' => $article->title,
            'meta_description' => $article->meta_description ?? $this->generateCalibrationMetaDescription($article),
            'url_slug' => $article->wordpress_url ?? Str::slug($article->title),
            'h1' => $article->title,
            'h2_tags' => $this->extractCalibrationH2Tags($refinedContent),
            'primary_keyword' => $this->generateCalibrationPrimaryKeyword($article),
            'secondary_keywords' => $article->seo_keywords ?? $this->generateCalibrationSecondaryKeywords($article),
            'meta_robots' => 'index,follow',
            'canonical_url' => $article->canonical_url ?? config('app.url') . '/info/' . Str::slug($article->title),
            'schema_type' => 'Article',
            'focus_keywords' => $this->generateCalibrationFocusKeywords($article)
        ];

        return [
            'original_post_id' => $article->blog_id ?? null,
            'title' => $article->title,
            'slug' => $article->wordpress_url ?? Str::slug($article->title),
            'new_slug' => $article->slug,
            'content' => $refinedContent, // Conteúdo das seções refinadas Claude
            'extracted_entities' => $extractedEntities,
            'seo_data' => $seoFormatted,
            'source' => 'tire_pressure_guide',
            'category_id' => 1, // Calibragem de Pneus
            'category_name' => 'Calibragem de Pneus',
            'category_slug' => 'calibragem-pneus',
            'published_at' => $article->blog_published_time ?? null,
            'modified_at' => $article->blog_modified_time ?? null,
            'blog_status' => $article->blog_status ?? null,
            'domain' => $article->template_used ?? 'tire_pressure_guide_car',
            'status' => 'draft',
            'template' => $article->template_used ?? 'tire_pressure_guide_car',
            'quality_score' => (float)($article->content_score ?? 8.0),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }

    /**
     * Construir conteúdo das seções refinadas pelo Claude
     */
    private function buildContentFromRefinedSections($article): array
    {
        $content = [];

        // Mapear seções refinadas para estrutura final
        $sectionMapping = [
            'introducao' => 'sections_intro',
            'tabela_pressao' => 'sections_pressure_table',
            'como_calibrar' => 'sections_how_to_calibrate', 
            'conteudo_meio' => 'sections_middle_content',
            'perguntas_frequentes' => 'sections_faq',
            'conclusao' => 'sections_conclusion'
        ];

        foreach ($sectionMapping as $contentKey => $sectionKey) {
            if (!empty($article->$sectionKey)) {
                $sectionData = is_string($article->$sectionKey) 
                    ? json_decode($article->$sectionKey, true) 
                    : $article->$sectionKey;
                
                if ($sectionData) {
                    $content[$contentKey] = $sectionData;
                }
            }
        }

        // Adicionar metadados das seções refinadas
        if (!empty($article->sections_scores)) {
            $content['metadata'] = [
                'refined_sections' => true,
                'claude_enhanced' => true,
                'sections_scores' => $article->sections_scores,
                'refined_at' => $article->sections_last_refined_at,
                'template_version' => '2.0'
            ];
        }

        return $content;
    }

    /**
     * Mapear tipo de veículo
     */
    private function mapVehicleType(string $type): string
    {
        return match (strtolower($type)) {
            'motorcycle', 'moto', 'motocicleta' => 'motocicleta',
            'electric', 'elétrico' => 'elétrico', 
            'hybrid', 'híbrido' => 'híbrido',
            default => 'carro'
        };
    }

    /**
     * Gerar meta description para calibragem
     */
    private function generateCalibrationMetaDescription($article): string
    {
        $make = $article->make ?? '';
        $model = $article->model ?? '';
        $year = $article->year ?? '';
        
        $pressureDisplay = $article->vehicle_data['pressure_display'] ?? 'pressão ideal';

        return "Calibragem dos pneus do {$make} {$model} {$year}. Pressão correta: {$pressureDisplay}. Dicas, tabela completa e guia para máxima segurança e economia.";
    }

    /**
     * Extrair H2 tags do conteúdo de calibragem
     */
    private function extractCalibrationH2Tags(array $content): array
    {
        return [
            'A Importância da Calibragem Correta',
            'Qual a Pressão Ideal',
            'Como Calibrar os Pneus',
            'Tabela de Pressões',
            'Checklist de Manutenção',
            'Perguntas Frequentes',
            'Conclusão'
        ];
    }

    /**
     * Gerar palavra-chave primária para calibragem
     */
    private function generateCalibrationPrimaryKeyword($article): string
    {
        $make = strtolower($article->make ?? '');
        $model = strtolower($article->model ?? '');

        return "calibragem pneu {$make} {$model}";
    }

    /**
     * Gerar palavras-chave secundárias para calibragem
     */
    private function generateCalibrationSecondaryKeywords($article): array
    {
        $make = strtolower($article->make ?? '');
        $model = strtolower($article->model ?? '');
        $year = $article->year ?? '';

        return [
            "pressão pneu {$make} {$model}",
            "calibragem {$make} {$model} {$year}",
            "pressão ideal {$make}",
            "calibrar pneus {$make} {$model}",
            "tabela pressão pneu",
            "manutenção pneu {$make}"
        ];
    }

    /**
     * Gerar focus keywords para calibragem
     */
    private function generateCalibrationFocusKeywords($article): array
    {
        return [
            'calibragem pneus',
            'pressão pneu',
            'calibrar pneu',
            'pressão ideal',
            'manutenção pneu'
        ];
    }
}