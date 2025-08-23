<?php

namespace Src\TirePressureCorrection\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use MongoDB\BSON\Regex;

/**
 * Command para testar diferentes formas de query no MongoDB
 */
class TestMongoDbQueryCommand extends Command
{
    protected $signature = 'articles:test-mongodb-query';
    
    protected $description = 'Testar diferentes queries MongoDB para extracted_entities';
    
    public function handle(): int
    {
        $this->info('=== TESTE DE QUERIES MONGODB ===');
        $this->newLine();
        
        // 1. Pegar um artigo de exemplo
        $this->info('1. ARTIGO DE EXEMPLO:');
        $sampleArticle = Article::where('template', 'when_to_change_tires')->first();
        
        if (!$sampleArticle) {
            $this->error('Nenhum artigo encontrado!');
            return Command::FAILURE;
        }
        
        $this->line("ID: {$sampleArticle->_id}");
        $this->line("Slug: {$sampleArticle->slug}");
        
        // Mostrar estrutura completa de extracted_entities
        $this->info("\n2. ESTRUTURA DE EXTRACTED_ENTITIES:");
        $extracted = $sampleArticle->extracted_entities;
        $this->line("Tipo: " . gettype($extracted));
        
        if (is_array($extracted)) {
            $this->line("Conteúdo:");
            foreach ($extracted as $key => $value) {
                $this->line("  - {$key}: {$value} (" . gettype($value) . ")");
            }
        } else {
            $this->line("Conteúdo: " . json_encode($extracted));
        }
        
        // 3. Testar acesso direto
        $this->info("\n3. TESTE DE ACESSO DIRETO:");
        $this->line("extracted_entities: " . ($sampleArticle->extracted_entities ? "EXISTS" : "NULL"));
        $this->line("extracted_entities['marca']: " . ($sampleArticle->extracted_entities['marca'] ?? 'NULL'));
        $this->line("data_get marca: " . data_get($sampleArticle, 'extracted_entities.marca', 'NULL'));
        
        // 4. Testar diferentes queries
        $this->info("\n4. TESTANDO DIFERENTES QUERIES:");
        
        // Query 1: Básica
        $count1 = Article::where('template', 'when_to_change_tires')->count();
        $this->line("a) Template when_to_change_tires: {$count1}");
        
        // Query 2: Com exists
        $count2 = Article::where('template', 'when_to_change_tires')
            ->where('extracted_entities', 'exists', true)
            ->count();
        $this->line("b) Com extracted_entities exists: {$count2}");
        
        // Query 3: whereNotNull
        $count3 = Article::where('template', 'when_to_change_tires')
            ->whereNotNull('extracted_entities')
            ->count();
        $this->line("c) Com extracted_entities whereNotNull: {$count3}");
        
        // Query 4: Raw MongoDB
        $count4 = Article::where('template', 'when_to_change_tires')
            ->whereRaw(['extracted_entities.marca' => ['$exists' => true]])
            ->count();
        $this->line("d) Raw query marca exists: {$count4}");
        
        // Query 5: Sem ponto
        $count5 = Article::where('template', 'when_to_change_tires')
            ->where('extracted_entities', '!=', null)
            ->count();
        $this->line("e) extracted_entities != null: {$count5}");
        
        // Query 6: Com tipo específico
        $count6 = Article::where('template', 'when_to_change_tires')
            ->whereRaw(['extracted_entities.marca' => ['$type' => 'string']])
            ->count();
        $this->line("f) marca é string: {$count6}");
        
        // 5. Verificar se é problema de case sensitivity
        $this->info("\n5. TESTE DE CASE SENSITIVITY:");
        
        // Buscar com regex case insensitive
        $count7 = Article::where('template', 'when_to_change_tires')
            ->whereRaw(['extracted_entities.marca' => new Regex('fiat', 'i')])
            ->count();
        $this->line("Com marca contendo 'fiat' (case insensitive): {$count7}");
        
        // 6. Listar alguns artigos manualmente
        $this->info("\n6. VERIFICAÇÃO MANUAL (3 primeiros):");
        
        $articles = Article::where('template', 'when_to_change_tires')->limit(3)->get();
        foreach ($articles as $index => $article) {
            $this->line("\nArtigo " . ($index + 1) . ":");
            
            // Tentar diferentes formas de acesso
            $marca1 = $article->extracted_entities['marca'] ?? 'NULL';
            $marca2 = data_get($article->extracted_entities, 'marca', 'NULL');
            $marca3 = isset($article->extracted_entities) && isset($article->extracted_entities['marca']) 
                ? $article->extracted_entities['marca'] 
                : 'NULL';
            
            $this->line("  Marca (array access): {$marca1}");
            $this->line("  Marca (data_get): {$marca2}");
            $this->line("  Marca (isset check): {$marca3}");
            
            // Verificar se extracted_entities é um objeto ao invés de array
            if (is_object($article->extracted_entities)) {
                $this->warn("  ⚠️ extracted_entities é um OBJETO!");
                $marca4 = $article->extracted_entities->marca ?? 'NULL';
                $this->line("  Marca (object access): {$marca4}");
            }
        }
        
        // 7. Solução final
        $this->info("\n7. TESTANDO SOLUÇÃO:");
        
        // Tentar query mais simples
        $finalCount = 0;
        $validArticles = [];
        
        Article::where('template', 'when_to_change_tires')
            ->chunk(100, function ($articles) use (&$finalCount, &$validArticles) {
                foreach ($articles as $article) {
                    $marca = data_get($article, 'extracted_entities.marca');
                    $modelo = data_get($article, 'extracted_entities.modelo');
                    
                    if ($marca && $modelo) {
                        $finalCount++;
                        if (count($validArticles) < 5) {
                            $validArticles[] = [
                                'id' => $article->_id,
                                'marca' => $marca,
                                'modelo' => $modelo
                            ];
                        }
                    }
                }
            });
        
        $this->line("Artigos com marca E modelo (verificação manual): {$finalCount}");
        
        if (!empty($validArticles)) {
            $this->info("\nPrimeiros artigos válidos:");
            foreach ($validArticles as $valid) {
                $this->line("  - {$valid['marca']} {$valid['modelo']} (ID: {$valid['id']})");
            }
        }
        
        return Command::SUCCESS;
    }
}