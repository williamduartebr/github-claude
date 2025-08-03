<?php

namespace Src\TirePressureCorrection\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\TirePressureCorrection\Domain\Entities\TirePressureCorrection;
use Src\VehicleData\Domain\Entities\VehicleData;

/**
 * Command para diagnosticar status das correções de pressão
 */
class DiagnosticTirePressureStatusCommand extends Command
{
    protected $signature = 'articles:diagnostic-tire-pressure-status
                           {--show-samples=5 : Mostrar amostras de artigos}';
    
    protected $description = 'Diagnosticar status atual das correções de pressão';
    
    public function handle(): int
    {
        $this->info('🔍 DIAGNÓSTICO DE STATUS DAS CORREÇÕES');
        $this->newLine();
        
        // 1. Status da tabela TirePressureCorrection
        $this->showCorrectionTableStatus();
        
        // 2. Status dos artigos
        $this->showArticleStatus();
        
        // 3. Status do VehicleData
        $this->showVehicleDataStatus();
        
        // 4. Amostras
        $showSamples = (int) $this->option('show-samples');
        if ($showSamples > 0) {
            $this->showSamples($showSamples);
        }
        
        return Command::SUCCESS;
    }
    
    /**
     * Mostrar status da tabela de correções
     */
    protected function showCorrectionTableStatus(): void
    {
        $this->info('📋 STATUS DA TABELA TIRE_PRESSURE_CORRECTIONS:');
        
        $stats = TirePressureCorrection::getStats();
        
        if ($stats['total'] === 0) {
            $this->warn('   ⚠️  Tabela vazia - nenhuma correção foi executada ainda');
            $this->line('   📝 Primeira execução processará todos os artigos elegíveis');
        } else {
            $this->line("   📊 Total de registros: {$stats['total']}");
            $this->line("   ✅ Concluídas: {$stats['completed']}");
            $this->line("   ⏳ Pendentes: {$stats['pending']}");
            $this->line("   ❌ Falhas: {$stats['failed']}");
            $this->line("   ➡️  Sem alterações: {$stats['no_changes']}");
        }
        
        $this->newLine();
    }
    
    /**
     * Mostrar status dos artigos
     */
    protected function showArticleStatus(): void
    {
        $this->info('📄 STATUS DOS ARTIGOS:');
        
        // Total de artigos with template
        $totalArticles = Article::where('template', 'when_to_change_tires')->count();
        $this->line("   📊 Total com template 'when_to_change_tires': {$totalArticles}");
        
        // Artigos com dados válidos
        $validArticles = $this->countValidArticles();
        $this->line("   ✅ Com marca/modelo/ano válidos: {$validArticles}");
        
        // Artigos já processados (últimos 7 dias)
        $recentlyProcessed = TirePressureCorrection::where('created_at', '>=', now()->subDays(7))
            ->where('status', '!=', TirePressureCorrection::STATUS_FAILED)
            ->pluck('article_id');
        
        $processedCount = $recentlyProcessed->count();
        $this->line("   🔄 Já processados (últimos 7 dias): {$processedCount}");
        
        // Artigos elegíveis para processamento
        $eligible = $validArticles - $processedCount;
        $this->line("   🎯 Elegíveis para processamento: {$eligible}");
        
        $this->newLine();
    }
    
    /**
     * Mostrar status do VehicleData
     */
    protected function showVehicleDataStatus(): void
    {
        $this->info('🚗 STATUS DO VEHICLE DATA:');
        
        $totalVehicles = VehicleData::count();
        $this->line("   📊 Total de veículos: {$totalVehicles}");
        
        if ($totalVehicles === 0) {
            $this->warn('   ⚠️  Tabela VehicleData vazia!');
            $this->line('   📝 Execute: php artisan vehicle-data:extract');
            $this->newLine();
            return;
        }
        
        // Veículos com pressões válidas
        $withPressures = VehicleData::whereNotNull('pressure_specifications')->count();
        $this->line("   🔧 Com especificações de pressão: {$withPressures}");
        
        // Qualidade média
        $avgQuality = VehicleData::avg('data_quality_score');
        $avgQuality = $avgQuality ? round($avgQuality, 2) : 0;
        $this->line("   📈 Qualidade média: {$avgQuality}/10");
        
        // Por categoria
        $motorcycles = VehicleData::where('is_motorcycle', true)->count();
        $cars = VehicleData::where('is_motorcycle', false)->count();
        $this->line("   🏍️  Motocicletas: {$motorcycles}");
        $this->line("   🚗 Carros: {$cars}");
        
        $this->newLine();
    }
    
    /**
     * Contar artigos válidos
     */
    protected function countValidArticles(): int
    {
        $count = 0;
        
        Article::where('template', 'when_to_change_tires')
            ->whereNotNull('extracted_entities')
            ->chunk(100, function ($articles) use (&$count) {
                foreach ($articles as $article) {
                    $marca = data_get($article, 'extracted_entities.marca');
                    $modelo = data_get($article, 'extracted_entities.modelo');
                    $ano = data_get($article, 'extracted_entities.ano');
                    
                    if (!empty($marca) && !empty($modelo) && !empty($ano)) {
                        $count++;
                    }
                }
            });
        
        return $count;
    }
    
    /**
     * Mostrar amostras de artigos
     */
    protected function showSamples(int $limit): void
    {
        $this->info("📋 AMOSTRAS DE ARTIGOS (primeiros {$limit}):");
        $this->newLine();
        
        $articles = Article::where('template', 'when_to_change_tires')
            ->whereNotNull('extracted_entities')
            ->limit($limit)
            ->get();
        
        $headers = ['#', 'Veículo', 'Slug', 'Processado?', 'VehicleData?'];
        $rows = [];
        
        foreach ($articles as $index => $article) {
            $extractedEntities = data_get($article, 'extracted_entities', []);
            $marca = data_get($extractedEntities, 'marca', '?');
            $modelo = data_get($extractedEntities, 'modelo', '?');
            $ano = data_get($extractedEntities, 'ano', '?');
            
            $vehicleName = "{$marca} {$modelo} {$ano}";
            
            // Verificar se foi processado
            $processed = TirePressureCorrection::byArticle($article->_id)->exists();
            $processedStatus = $processed ? '✅ Sim' : '❌ Não';
            
            // Verificar se existe no VehicleData
            $vehicleExists = false;
            if (!empty($marca) && !empty($modelo) && !empty($ano)) {
                $vehicleExists = VehicleData::findVehicle($marca, $modelo, (int)$ano) !== null;
            }
            $vehicleStatus = $vehicleExists ? '✅ Sim' : '❌ Não';
            
            $rows[] = [
                $index + 1,
                \Str::limit($vehicleName, 30),
                \Str::limit($article->slug, 40),
                $processedStatus,
                $vehicleStatus
            ];
        }
        
        $this->table($headers, $rows);
        $this->newLine();
    }
}