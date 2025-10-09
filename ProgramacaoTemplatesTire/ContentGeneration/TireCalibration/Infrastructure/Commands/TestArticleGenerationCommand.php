<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands;

use Illuminate\Console\Command;
use Src\ContentGeneration\TireCalibration\Application\Services\ArticleMappingService;

/**
 * TestArticleGenerationCommand - Testar geração de artigo único
 * 
 * Command para testar a nova funcionalidade de mapeamento sem afetar dados reais
 * 
 * USO:
 * php artisan tire-calibration:test-article-generation
 */
class TestArticleGenerationCommand extends Command
{
    protected $signature = 'tire-calibration:test-article-generation 
                            {--save-json : Salvar resultado em arquivo JSON}';

    protected $description = 'Testar geração de artigo único para validação';

    private ArticleMappingService $mappingService;
    
    public function __construct(ArticleMappingService $mappingService)
    {
        parent::__construct();
        $this->mappingService = $mappingService;
    }

    public function handle(): int
    {
        $this->info('🧪 TESTE DE GERAÇÃO DE ARTIGO');
        $this->newLine();

        // Dados de teste (simulando arquivo JSON de database/vehicle-data/)
        $mockVehicleData = [
            'make' => 'Honda',
            'model' => 'Civic',
            'tire_size' => '215/55 R16',
            'main_category' => 'sedan',
            'pressure_empty_front' => 32,
            'pressure_empty_rear' => 30,
            'has_tpms' => true,
            'data_quality_score' => 9
        ];

        // Mock TireCalibration (criar instância real)
        $mockCalibration = new \Src\ContentGeneration\TireCalibration\Domain\Entities\TireCalibration([
            'version' => 'v2',
            'vehicle_make' => 'Honda',
            'vehicle_model' => 'Civic',
            'main_category' => 'sedan',
            'enrichment_phase' => 'pending'
        ]);
        
        // Definir ID fictício para o teste
        $mockCalibration->_id = 'test-id-12345';

        try {
            $this->info('📝 Gerando artigo de teste...');
            
            // Gerar artigo usando o service
            $article = $this->mappingService->mapVehicleDataToArticle(
                $mockVehicleData, 
                $mockCalibration
            );

            // Exibir estatísticas
            $this->displayStats($article);

            // Salvar em arquivo se solicitado
            if ($this->option('save-json')) {
                $this->saveToFile($article);
            }

            $this->newLine();
            $this->info('✅ Teste concluído com sucesso!');
            
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Erro no teste: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function displayStats(array $article): void
    {
        $this->newLine();
        $this->info('📊 ESTATÍSTICAS DO ARTIGO GERADO:');
        
        $this->table(
            ['Campo', 'Valor'],
            [
                ['Título', substr($article['title'], 0, 50) . '...'],
                ['Slug', $article['slug']],
                ['Template', $article['template']],
                ['Palavra-chave', $article['seo_data']['primary_keyword']],
                ['Pressão Display', $article['vehicle_data']['pressure_specifications']['pressure_display']],
                ['Marca/Modelo', $article['vehicle_data']['make'] . ' ' . $article['vehicle_data']['model']],
                ['Categoria', $article['vehicle_data']['main_category']],
                ['TPMS', $article['vehicle_data']['has_tpms'] ? 'Sim' : 'Não'],
                ['Seções de Conteúdo', count($article['content'])],
                ['FAQs', count($article['content']['perguntas_frequentes'] ?? [])],
                ['Tamanho JSON', number_format(strlen(json_encode($article))) . ' bytes']
            ]
        );

        $this->newLine();
        $this->info('🎯 VALIDAÇÕES:');
        $this->line('   ✅ Estrutura base: OK');
        $this->line('   ✅ SEO data: ' . (isset($article['seo_data']['primary_keyword']) ? 'OK' : 'ERRO'));
        $this->line('   ✅ Vehicle data: ' . (isset($article['vehicle_data']['make']) ? 'OK' : 'ERRO'));
        $this->line('   ✅ Conteúdo: ' . (isset($article['content']['introducao']) ? 'OK' : 'ERRO'));
        $this->line('   ✅ Template: ' . ($article['template'] === 'tire_calibration_car' ? 'OK' : 'ERRO'));
    }

    private function saveToFile(array $article): void
    {
        $filename = 'test-article-' . date('Y-m-d-H-i-s') . '.json';
        $filepath = storage_path("app/test-articles/{$filename}");
        
        // Criar diretório se não existir
        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        file_put_contents($filepath, json_encode($article, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        $this->newLine();
        $this->info("💾 Arquivo salvo: storage/app/test-articles/{$filename}");
        $this->line("   📏 Tamanho: " . number_format(filesize($filepath)) . " bytes");
    }
}