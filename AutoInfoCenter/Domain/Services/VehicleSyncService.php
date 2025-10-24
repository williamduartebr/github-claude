<?php

namespace Src\AutoInfoCenter\Domain\Services;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Src\AutoInfoCenter\Domain\Eloquent\Make;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\AutoInfoCenter\Domain\Eloquent\VehicleModel;
use Src\AutoInfoCenter\Domain\Eloquent\VehicleModelArticle;


class VehicleSyncService
{
    /**
     * Sincroniza um artigo com as tabelas MySQL
     * 
     * @param Article $article O artigo a ser sincronizado
     * @return bool Sucesso da operação
     */
    public function syncArticleToMySQL(Article $article)
    {
        try {
            // Verificar se o artigo tem informações de veículo
            if (empty($article->vehicle_info) || empty($article->vehicle_info['make'])) {
                return false;
            }
            
            // Preparar os dados para a tabela vehicle_models
            $vehicleData = [
                'article_id' => (string) $article->_id,
                'make' => $article->vehicle_info['make'] ?? null,
                'make_slug' => $article->vehicle_info['make_slug'] ?? null,
                'model' => $article->vehicle_info['model'] ?? null,
                'model_slug' => $article->vehicle_info['model_slug'] ?? null,
                'year_start' => $article->vehicle_info['year_start'] ?? null,
                'year_end' => $article->vehicle_info['year_end'] ?? null,
                'year_range' => $article->vehicle_info['year_range'] ?? false,
                'engine' => $article->vehicle_info['engine'] ?? null,
                'version' => $article->vehicle_info['version'] ?? null,
                'fuel' => $article->vehicle_info['fuel'] ?? null,
                'category' => $article->vehicle_info['category'] ?? null,
                'vehicle_type' => $article->vehicle_info['vehicle_type'] ?? null,
                'article_title' => $article->title,
                'article_slug' => $article->slug,
            ];
            
            // Inserir ou atualizar na tabela vehicle_models
            $vehicleModelArticle = VehicleModelArticle::updateOrCreate(
                ['article_id' => (string) $article->_id],
                $vehicleData
            );
            
            // Processar a marca (se existir)
            if (!empty($article->vehicle_info['make']) && !empty($article->vehicle_info['make_slug'])) {
                Make::updateOrCreate(
                    ['slug' => $article->vehicle_info['make_slug']],
                    [
                        'name' => $article->vehicle_info['make'],
                        'slug' => $article->vehicle_info['make_slug'],
                        'is_active' => true,
                    ]
                );
            }
            
            // Processar o modelo (se existir)
            if (!empty($article->vehicle_info['model']) && 
                !empty($article->vehicle_info['model_slug']) && 
                !empty($article->vehicle_info['make_slug'])) {
                
                VehicleModel::updateOrCreate(
                    [
                        'make_slug' => $article->vehicle_info['make_slug'], 
                        'slug' => $article->vehicle_info['model_slug']
                    ],
                    [
                        'name' => $article->vehicle_info['model'],
                        'is_active' => true,
                    ]
                );
            }
            
            return true;
            
        } catch (\Exception $e) {
            \Log::error("Erro ao sincronizar artigo {$article->_id} com MySQL: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Sincroniza todos os artigos com as tabelas MySQL
     * 
     * @param bool $onlyMissing Sincronizar apenas artigos que ainda não estão no MySQL
     * @return array Estatísticas de sincronização
     */
    public function syncAllArticlesToMySQL($onlyMissing = true)
    {
        $stats = [
            'total' => 0,
            'processed' => 0,
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
        ];
        
        // Buscar artigos publicados com informações de veículo
        $query = Article::where('status', 'published')
            ->whereNotNull('vehicle_info')
            ->where('vehicle_info', '!=', []);
        
        if ($onlyMissing) {
            // Buscar IDs de artigos já sincronizados
            $syncedIds = VehicleModelArticle::pluck('article_id')->toArray();
            if (!empty($syncedIds)) {
                $query->whereNotIn('_id', $syncedIds);
            }
        }
        
        // Contar o total de artigos
        $stats['total'] = $query->count();
        
        // Processar artigos em lotes para evitar problemas de memória
        $perPage = 50;
        $page = 1;
        
        do {
            $articles = $query->forPage($page, $perPage)->get();
            
            if ($articles->isEmpty()) {
                break;
            }
            
            foreach ($articles as $article) {
                $stats['processed']++;
                
                if ($this->syncArticleToMySQL($article)) {
                    $stats['success']++;
                } else {
                    $stats['failed']++;
                }
            }
            
            // Limpar a memória
            $articles = null;
            gc_collect_cycles();
            
            // Avançar para a próxima página
            $page++;
            
        } while (true);
        
        // Atualizar contadores nas tabelas de marca e modelo
        $this->updateAllCounters();
        
        return $stats;
    }
    
    /**
     * Atualiza todos os contadores de artigos nas tabelas de marca e modelo
     * 
     * @return void
     */
    public function updateAllCounters()
    {
        try {
            // Atualizar contadores de marca
            DB::connection('mysql')->statement("
                UPDATE makes m
                SET article_count = (
                    SELECT COUNT(*) 
                    FROM vehicle_models vm 
                    WHERE vm.make_slug = m.slug
                )
            ");
            
            // Atualizar contadores de modelo
            DB::connection('mysql')->statement("
                UPDATE models m
                SET article_count = (
                    SELECT COUNT(*) 
                    FROM vehicle_models vm 
                    WHERE vm.make_slug = m.make_slug AND vm.model_slug = m.slug
                )
            ");
        } catch (\Exception $e) {
            \Log::error("Erro ao atualizar contadores: " . $e->getMessage());
        }
    }
    
    /**
     * Cria as tabelas MySQL necessárias
     * 
     * @return bool Sucesso da operação
     */
    public function createMySQLTables()
    {
        try {
            // Verificar e criar a tabela de marcas
            if (!Schema::connection('mysql')->hasTable('makes')) {
                Schema::connection('mysql')->create('makes', function ($table) {
                    $table->id();
                    $table->string('name');
                    $table->string('slug')->unique();
                    $table->string('logo_url')->nullable();
                    $table->text('description')->nullable();
                    $table->boolean('is_active')->default(true);
                    $table->integer('article_count')->default(0);
                    $table->timestamps();
                });
            }
            
            // Verificar e criar a tabela de modelos
            if (!Schema::connection('mysql')->hasTable('models')) {
                Schema::connection('mysql')->create('models', function ($table) {
                    $table->id();
                    $table->string('make_slug');
                    $table->string('name');
                    $table->string('slug');
                    $table->string('image_url')->nullable();
                    $table->text('description')->nullable();
                    $table->boolean('is_active')->default(true);
                    $table->integer('article_count')->default(0);
                    $table->timestamps();
                    
                    $table->unique(['make_slug', 'slug']);
                    $table->index('make_slug');
                });
            }
            
            // Verificar e criar a tabela de veículos (mapeia artigos para veículos)
            if (!Schema::connection('mysql')->hasTable('vehicle_models')) {
                Schema::connection('mysql')->create('vehicle_models', function ($table) {
                    $table->id();
                    $table->string('article_id');
                    $table->string('make')->nullable();
                    $table->string('make_slug')->nullable();
                    $table->string('model')->nullable();
                    $table->string('model_slug')->nullable();
                    $table->string('year_start')->nullable();
                    $table->string('year_end')->nullable();
                    $table->boolean('year_range')->default(false);
                    $table->string('engine')->nullable();
                    $table->string('version')->nullable();
                    $table->string('fuel')->nullable();
                    $table->string('category')->nullable();
                    $table->string('vehicle_type')->nullable();
                    $table->string('article_title');
                    $table->string('article_slug');
                    $table->timestamps();
                    
                    $table->unique('article_id');
                    $table->index('make_slug');
                    $table->index('model_slug');
                    $table->index(['make_slug', 'model_slug']);
                });
            }
            
            return true;
        } catch (\Exception $e) {
            \Log::error("Erro ao criar tabelas MySQL: " . $e->getMessage());
            return false;
        }
    }
}
