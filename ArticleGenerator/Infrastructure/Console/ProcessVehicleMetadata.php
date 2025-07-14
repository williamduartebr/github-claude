<?php

namespace Src\ArticleGenerator\Infrastructure\Console;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Src\AutoInfoCenter\Domain\Eloquent\Article;

class ProcessVehicleMetadata extends Command
{
    /**
     * Nome e assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'articles:process-vehicle-metadata
                           {--all : Processar todos os artigos}
                           {--unprocessed : Processar apenas artigos sem vehicle_info}
                           {--sync-mysql : Sincronizar com tabela MySQL}
                           {--create-mysql-tables : Criar tabelas MySQL necessárias}';

    /**
     * Descrição do comando.
     *
     * @var string
     */
    protected $description = 'Processa metadados de veículos dos artigos para SEO e filtros';

    /**
     * Execute o comando.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->option('create-mysql-tables')) {
            return $this->createMySQLTables();
        }
        
        $this->info('Iniciando processamento de metadados de veículos...');

        // Construir a query base
        $query = Article::query();
        
        if ($this->option('unprocessed')) {
            $query->whereNull('vehicle_info')->orWhere('vehicle_info', '=', []);
            $this->info('Processando apenas artigos sem metadados de veículo.');
        }
        
        // Contar artigos para a barra de progresso
        $articlesCount = $query->count();
        
        if ($articlesCount === 0) {
            $this->warn('Nenhum artigo encontrado para processar.');
            return Command::SUCCESS;
        }
        
        $this->info("Encontrados {$articlesCount} artigos para processar.");
        
        $bar = $this->output->createProgressBar($articlesCount);
        $bar->start();
        
        $processed = 0;
        $syncedToMySQL = 0;
        
        // Processar artigos em lotes para evitar problemas de memória
        $perPage = 100;
        $page = 1;
        
        do {
            $articles = $query->forPage($page, $perPage)->get();
            
            if ($articles->isEmpty()) {
                break;
            }
            
            $vehicleData = [];
            
            foreach ($articles as $article) {
                $seoFilterData = $this->extractSeoFilterData($article);
                
                // Atualizar o artigo no MongoDB
                Article::find($article->_id)
                    ->update([
                        'vehicle_info' => $seoFilterData['vehicle_info'],
                        'filter_data' => $seoFilterData['filter_data'],
                    ]);
                
                // Preparar dados para MySQL, se necessário
                if ($this->option('sync-mysql') && !empty($seoFilterData['vehicle_info']['make'])) {
                    $vehicleData[] = $this->prepareVehicleDataForMySQL($article, $seoFilterData);
                    $syncedToMySQL++;
                }
                
                $processed++;
                $bar->advance();
            }
            
            // Sincronizar com MySQL se solicitado e tivermos dados para inserir
            if ($this->option('sync-mysql') && !empty($vehicleData)) {
                $this->syncToMySQL($vehicleData);
            }
            
            // Limpar a memória
            $articles = null;
            $vehicleData = null;
            gc_collect_cycles();
            
            // Avançar para a próxima página
            $page++;
            
        } while (true);
        
        $bar->finish();
        $this->newLine(2);
        
        $this->info("Concluído! {$processed} artigos foram processados com metadados de veículos.");
        
        if ($this->option('sync-mysql')) {
            $this->info("{$syncedToMySQL} registros foram sincronizados com o MySQL.");
        }
        
        return Command::SUCCESS;
    }
    
    /**
     * Extrai dados de SEO e filtros do artigo
     *
     * @param mixed $article
     * @return array
     */
    protected function extractSeoFilterData($article)
    {
        $result = [
            'vehicle_info' => [],
            'filter_data' => []
        ];
        
        // Veículos: marca, modelo, ano, versão, motorização
        if (!empty($article->extracted_entities)) {
            $vehicleInfo = [];
            $filterData = [];
            
            // Mapeamento de campos para vehicle_info
            $vehicleFields = [
                'marca' => 'make',
                'modelo' => 'model',
                'ano' => 'year',
                'versao' => 'version',
                'motorizacao' => 'engine',
                'combustivel' => 'fuel',
                'categoria' => 'category',
                'tipo_veiculo' => 'vehicle_type'
            ];
            
            foreach ($vehicleFields as $sourceField => $targetField) {
                if (!empty($article->extracted_entities[$sourceField])) {
                    $vehicleInfo[$targetField] = $article->extracted_entities[$sourceField];
                    
                    // Adicionar aos dados de filtro também
                    $filterData[$sourceField] = $article->extracted_entities[$sourceField];
                }
            }
            
            // Tratar ano como array (pode ser um intervalo)
            if (!empty($vehicleInfo['year']) && strpos($vehicleInfo['year'], '-') !== false) {
                $yearRange = explode('-', $vehicleInfo['year']);
                if (count($yearRange) == 2) {
                    $vehicleInfo['year_start'] = trim($yearRange[0]);
                    $vehicleInfo['year_end'] = trim($yearRange[1]);
                    $vehicleInfo['year_range'] = true;
                }
            }
            
            // Adicionar slug combinados para SEO e filtros
            if (!empty($vehicleInfo['make'])) {
                $makeSlug = Str::slug($vehicleInfo['make']);
                $vehicleInfo['make_slug'] = $makeSlug;
                $filterData['marca_slug'] = $makeSlug;
                
                if (!empty($vehicleInfo['model'])) {
                    $modelSlug = Str::slug($vehicleInfo['model']);
                    $vehicleInfo['model_slug'] = $modelSlug;
                    $filterData['modelo_slug'] = $modelSlug;
                    
                    // Slug combinado marca-modelo
                    $vehicleInfo['make_model_slug'] = $makeSlug . '-' . $modelSlug;
                    $filterData['marca_modelo_slug'] = $makeSlug . '-' . $modelSlug;
                }
            }
            
            $result['vehicle_info'] = $vehicleInfo;
            $result['filter_data'] = $filterData;
        }
        
        return $result;
    }
    
    /**
     * Prepara dados de veículo para sincronização com MySQL
     *
     * @param Article $article
     * @param array $seoFilterData
     * @return array
     */
    protected function prepareVehicleDataForMySQL($article, $seoFilterData)
    {
        $vehicleInfo = $seoFilterData['vehicle_info'];
        
        // Dados base para inserção em vehicle_models
        $data = [
            'article_id' => (string) $article->_id,
            'make' => $vehicleInfo['make'] ?? null,
            'make_slug' => $vehicleInfo['make_slug'] ?? null,
            'model' => $vehicleInfo['model'] ?? null,
            'model_slug' => $vehicleInfo['model_slug'] ?? null,
            'year_start' => $vehicleInfo['year_start'] ?? null,
            'year_end' => $vehicleInfo['year_end'] ?? null,
            'year_range' => $vehicleInfo['year_range'] ?? false,
            'engine' => $vehicleInfo['engine'] ?? null,
            'version' => $vehicleInfo['version'] ?? null,
            'fuel' => $vehicleInfo['fuel'] ?? null,
            'category' => $vehicleInfo['category'] ?? null,
            'vehicle_type' => $vehicleInfo['vehicle_type'] ?? null,
            'article_title' => $article->title,
            'article_slug' => $article->slug,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        return $data;
    }
    
    /**
     * Sincroniza dados com tabelas MySQL
     *
     * @param array $vehicleData
     * @return void
     */
    protected function syncToMySQL($vehicleData)
    {
        // Tentar inserir registros ignorando duplicatas
        try {
            // Inserir em vehicle_models
            DB::connection('mysql')->table('vehicle_models')
                ->insertOrIgnore($vehicleData);
            
            // Processar marcas únicas para a tabela makes
            $uniqueMakes = [];
            foreach ($vehicleData as $data) {
                if (!empty($data['make']) && !empty($data['make_slug'])) {
                    $makeKey = $data['make_slug'];
                    $uniqueMakes[$makeKey] = [
                        'name' => $data['make'],
                        'slug' => $data['make_slug'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            
            if (!empty($uniqueMakes)) {
                DB::connection('mysql')->table('makes')
                    ->insertOrIgnore(array_values($uniqueMakes));
            }
            
            // Processar modelos únicos para a tabela models
            $uniqueModels = [];
            foreach ($vehicleData as $data) {
                if (!empty($data['make_slug']) && !empty($data['model']) && !empty($data['model_slug'])) {
                    $modelKey = $data['make_slug'] . '-' . $data['model_slug'];
                    $uniqueModels[$modelKey] = [
                        'make_slug' => $data['make_slug'],
                        'name' => $data['model'],
                        'slug' => $data['model_slug'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            
            if (!empty($uniqueModels)) {
                DB::connection('mysql')->table('models')
                    ->insertOrIgnore(array_values($uniqueModels));
            }
            
        } catch (\Exception $e) {
            $this->error("Erro ao sincronizar com MySQL: {$e->getMessage()}");
        }
    }
    
    /**
     * Cria as tabelas MySQL necessárias
     *
     * @return int
     */
    protected function createMySQLTables()
    {
        $this->info('Criando tabelas MySQL para veículos...');
        
        try {
            // Tabela de marcas
            if (!DB::connection('mysql')->getSchemaBuilder()->hasTable('makes')) {
                DB::connection('mysql')->getSchemaBuilder()->create('makes', function ($table) {
                    $table->id();
                    $table->string('name');
                    $table->string('slug')->unique();
                    $table->string('logo_url')->nullable();
                    $table->text('description')->nullable();
                    $table->boolean('is_active')->default(true);
                    $table->integer('article_count')->default(0);
                    $table->timestamps();
                });
                
                $this->info('Tabela "makes" criada com sucesso.');
            } else {
                $this->info('Tabela "makes" já existe.');
            }
            
            // Tabela de modelos
            if (!DB::connection('mysql')->getSchemaBuilder()->hasTable('models')) {
                DB::connection('mysql')->getSchemaBuilder()->create('models', function ($table) {
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
                
                $this->info('Tabela "models" criada com sucesso.');
            } else {
                $this->info('Tabela "models" já existe.');
            }
            
            // Tabela de veículos (mapeia artigos para veículos)
            if (!DB::connection('mysql')->getSchemaBuilder()->hasTable('vehicle_models')) {
                DB::connection('mysql')->getSchemaBuilder()->create('vehicle_models', function ($table) {
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
                
                $this->info('Tabela "vehicle_models" criada com sucesso.');
            } else {
                $this->info('Tabela "vehicle_models" já existe.');
            }
            
            $this->info('Todas as tabelas MySQL necessárias foram criadas com sucesso.');
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("Erro ao criar tabelas MySQL: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
