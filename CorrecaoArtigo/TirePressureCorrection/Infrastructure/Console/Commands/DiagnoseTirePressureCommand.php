<?php

namespace Src\TirePressureCorrection\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\TirePressureCorrection\Domain\Entities\TirePressureCorrection;

/**
 * Command para diagnosticar problemas na correção de pressões
 */
class DiagnoseTirePressureCommand extends Command
{
    protected $signature = 'articles:diagnose-tire-pressure';
    
    protected $description = 'Diagnosticar problemas com correção de pressões';
    
    public function handle(): int
    {
        $this->info('=== DIAGNÓSTICO DE CORREÇÃO DE PRESSÕES ===');
        $this->newLine();
        
        // 1. Verificar total de artigos
        $this->info('1. VERIFICANDO ARTIGOS:');
        
        $totalArticles = Article::count();
        $this->line("   Total de artigos no banco: {$totalArticles}");
        
        $tirePressureArticles = Article::where('template', 'when_to_change_tires')->count();
        $this->line("   Artigos com template 'when_to_change_tires': {$tirePressureArticles}");
        
        // 2. Verificar artigos com dados necessários
        $this->newLine();
        $this->info('2. ARTIGOS COM DADOS COMPLETOS:');
        
        $articlesWithMarca = Article::where('template', 'when_to_change_tires')
            ->whereNotNull('extracted_entities.marca')
            ->count();
        $this->line("   Com marca: {$articlesWithMarca}");
        
        $articlesWithModelo = Article::where('template', 'when_to_change_tires')
            ->whereNotNull('extracted_entities.modelo')
            ->count();
        $this->line("   Com modelo: {$articlesWithModelo}");
        
        $articlesComplete = Article::where('template', 'when_to_change_tires')
            ->whereNotNull('extracted_entities.marca')
            ->whereNotNull('extracted_entities.modelo')
            ->count();
        $this->line("   Com marca E modelo: {$articlesComplete}");
        
        // 3. Mostrar amostra de artigos
        $this->newLine();
        $this->info('3. AMOSTRA DE ARTIGOS:');
        
        $sampleArticles = Article::where('template', 'when_to_change_tires')
            ->limit(5)
            ->get();
        
        if ($sampleArticles->isEmpty()) {
            $this->warn('   Nenhum artigo encontrado!');
        } else {
            foreach ($sampleArticles as $index => $article) {
                $this->line("\n   Artigo " . ($index + 1) . ":");
                $this->line("   - ID: {$article->_id}");
                $this->line("   - Slug: {$article->slug}");
                $this->line("   - Template: {$article->template}");
                
                $entities = data_get($article, 'extracted_entities', []);
                $this->line("   - Marca: " . (data_get($entities, 'marca') ?: 'NULL'));
                $this->line("   - Modelo: " . (data_get($entities, 'modelo') ?: 'NULL'));
                $this->line("   - Ano: " . (data_get($entities, 'ano') ?: 'NULL'));
                
                // Verificar estrutura de extracted_entities
                if (empty($entities)) {
                    $this->warn("   ⚠️  extracted_entities está vazio!");
                } else {
                    $this->line("   - Estrutura extracted_entities: " . implode(', ', array_keys($entities)));
                }
            }
        }
        
        // 4. Verificar correções existentes
        $this->newLine();
        $this->info('4. CORREÇÕES EXISTENTES:');
        
        $totalCorrections = TirePressureCorrection::count();
        $this->line("   Total de correções: {$totalCorrections}");
        
        if ($totalCorrections > 0) {
            $statuses = TirePressureCorrection::raw(function($collection) {
                return $collection->aggregate([
                    ['$group' => [
                        '_id' => '$status',
                        'count' => ['$sum' => 1]
                    ]]
                ]);
            });
            
            foreach ($statuses as $status) {
                $this->line("   - {$status->_id}: {$status->count}");
            }
        }
        
        // 5. Verificar artigos não processados
        $this->newLine();
        $this->info('5. ARTIGOS DISPONÍVEIS PARA PROCESSAMENTO:');
        
        // Artigos já processados
        $processedArticles = TirePressureCorrection::where('created_at', '>=', now()->subDays(7))
            ->where('status', '!=', TirePressureCorrection::STATUS_FAILED)
            ->pluck('article_id');
        
        $this->line("   Artigos já processados (últimos 7 dias): {$processedArticles->count()}");
        
        // Artigos pendentes
        $pendingQuery = Article::where('template', 'when_to_change_tires')
            ->whereNotNull('extracted_entities.marca')
            ->whereNotNull('extracted_entities.modelo');
            
        if ($processedArticles->isNotEmpty()) {
            $pendingQuery->whereNotIn('_id', $processedArticles);
        }
        
        $pendingCount = $pendingQuery->count();
        $this->line("   Artigos disponíveis para processar: {$pendingCount}");
        
        // 6. Testar query exata do comando
        $this->newLine();
        $this->info('6. TESTANDO QUERY DO COMANDO COLLECT:');
        
        // Testar com whereNotNull (método antigo)
        $testOldMethod = Article::where('template', 'when_to_change_tires')
            ->whereNotNull('extracted_entities.marca')
            ->whereNotNull('extracted_entities.modelo')
            ->count();
        $this->line("   Com whereNotNull: {$testOldMethod} artigos");
        
        // Testar com exists (método MongoDB)
        $testNewMethod = Article::where('template', 'when_to_change_tires')
            ->where('extracted_entities.marca', 'exists', true)
            ->where('extracted_entities.modelo', 'exists', true)
            ->count();
        $this->line("   Com exists: {$testNewMethod} artigos");
        
        // Mostrar diferença
        if ($testOldMethod != $testNewMethod) {
            $this->warn("   ⚠️  Diferença detectada! Use o método 'exists' para MongoDB.");
        }
        
        // 7. Sugestões
        $this->newLine();
        $this->info('7. SUGESTÕES:');
        
        if ($tirePressureArticles == 0) {
            $this->error('   ❌ Nenhum artigo com template "when_to_change_tires" encontrado!');
            $this->line('   Verifique se o template está correto ou se os artigos foram importados.');
        } elseif ($articlesComplete == 0) {
            $this->error('   ❌ Nenhum artigo tem marca E modelo em extracted_entities!');
            $this->line('   Os artigos precisam ter extracted_entities.marca e extracted_entities.modelo preenchidos.');
        } elseif ($pendingCount == 0 && $processedArticles->isNotEmpty()) {
            $this->warn('   ⚠️  Todos os artigos já foram processados recentemente.');
            $this->line('   Use --force para reprocessar ou aguarde 7 dias.');
        } else {
            $this->info('   ✅ Existem artigos disponíveis para processar!');
            $this->line('   Tente executar: php artisan articles:collect-tire-pressures --force');
        }
        
        return Command::SUCCESS;
    }
}