<?php

namespace Src\ContentGeneration\TirePressureGuide\Infrastructure\Console\Commands\Schedules;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ContentGeneration\TirePressureGuide\Domain\Entities\TirePressureArticle;
use Src\ContentGeneration\TirePressureGuide\Infrastructure\Services\VehicleDataCorrectionService;
use Src\ContentGeneration\TirePressureGuide\Application\Services\TirePressureGuideApplicationService;


/**
 * Schedule SIMPLES para correção do vehicle_data
 * 
 * EXECUÇÃO: A cada 3 minutos
 * ESTRATÉGIA: 1 veículo = 2 artigos corrigidos simultaneamente
 * ECONOMIA: 50% menos chamadas Claude
 */
class VehicleDataCorrectionSchedule extends Command
{
    protected $signature = 'tire-pressure:correct-vehicle-data-schedule 
                           {--limit=1 : Número de veículos por execução}
                           {--dry-run : Preview sem executar}';

    protected $description = 'Schedule para correção automática do vehicle_data';

    protected VehicleDataCorrectionService $correctionService;

    public function __construct(VehicleDataCorrectionService $correctionService)
    {
        parent::__construct();
        $this->correctionService = $correctionService;
    }

    /**
     * Execução principal do schedule
     * ✅ SIMPLIFICADO: Processar 1 artigo por vez
     */
    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $isDryRun = $this->option('dry-run');

        $this->info("🚀 Iniciando correção do vehicle_data...");
        
        if ($isDryRun) {
            $this->warn("⚠️  MODO DRY-RUN ATIVO - Nenhuma alteração será salva");
        }

        try {
            // 1. Buscar próximo artigo que precisa correção
            $articleToProcess = $this->findNextArticleToCorrect();
            
            if (!$articleToProcess) {
                $this->info("✅ Todos os artigos já foram corrigidos!");
                return 0;
            }

            // 2. Corrigir vehicle_data deste artigo
            $correctedData = $this->correctionService->correctVehicleData($articleToProcess->vehicle_data);

            // 3. Aplicar correção no artigo
            $this->applyCorrectionsToArticle($articleToProcess, $correctedData, $isDryRun);

            // 4. Relatório
            $this->displayResults($articleToProcess, $correctedData);

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ ERRO: " . $e->getMessage());
            Log::error('VehicleDataCorrectionSchedule failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Encontrar próximo artigo que precisa correção
     * ✅ DEBUG: Investigar problema na busca
     */
    protected function findNextArticleToCorrect(): ?TirePressureArticle
    {
        $this->info("🔍 Buscando artigos que precisam correção...");
        
        // ✅ DEBUG: Testar busca passo a passo
        $totalArticles = TirePressureArticle::count();
        $this->info("   📊 Total de artigos: {$totalArticles}");
        
        $withVehicleData = TirePressureArticle::whereNotNull('vehicle_data')->count();
        $this->info("   📊 Com vehicle_data: {$withVehicleData}");
        
        // Testar scope separadamente
        $needsCorrectionCount = TirePressureArticle::query()->needsVehicleDataCorrection()->count();
        $this->info("   📊 Precisam correção (scope): {$needsCorrectionCount}");
        
        // Testar query manual
        $manualCount = TirePressureArticle::where(function($query) {
            $query->whereNull('vehicle_data_version')
                  ->orWhere('vehicle_data_version', '!=', 'v2.1');
        })->count();
        $this->info("   📊 Precisam correção (manual): {$manualCount}");
        
        // Testar query específica
        $nullVersionCount = TirePressureArticle::whereNull('vehicle_data_version')->count();
        $this->info("   📊 Com vehicle_data_version null: {$nullVersionCount}");
        
        $v21Count = TirePressureArticle::where('vehicle_data_version', 'v2.1')->count();
        $this->info("   📊 Com vehicle_data_version v2.1: {$v21Count}");
        
        // Buscar primeiro artigo com várias estratégias
        $this->info("\n🔍 Testando diferentes buscas:");
        
        // Estratégia 1: Scope
        $article1 = TirePressureArticle::query()
            ->needsVehicleDataCorrection()
            ->first();
        $this->info("   1. Scope: " . ($article1 ? "Encontrado ID {$article1->_id}" : "Não encontrado"));
        
        // Estratégia 2: Manual
        $article2 = TirePressureArticle::where(function($query) {
            $query->whereNull('vehicle_data_version')
                  ->orWhere('vehicle_data_version', '!=', 'v2.1');
        })->first();
        $this->info("   2. Manual: " . ($article2 ? "Encontrado ID {$article2->_id}" : "Não encontrado"));
        
        // Estratégia 3: Só null
        $article3 = TirePressureArticle::whereNull('vehicle_data_version')->first();
        $this->info("   3. Null: " . ($article3 ? "Encontrado ID {$article3->_id}" : "Não encontrado"));
        
        // Usar a primeira estratégia que funcionar
        $article = $article1 ?? $article2 ?? $article3;
        
        if ($article) {
            $vehicleData = $article->vehicle_data ?? [];
            $this->info("\n✅ Artigo selecionado:");
            $this->info("   🚗 Veículo: {$vehicleData['make']} {$vehicleData['model']} {$vehicleData['year']}");
            $this->info("   📋 ID: {$article->_id}");
            $this->info("   📅 Template: {$article->template_type}");
            $this->info("   📄 Slug: {$article->slug}");
            $this->info("   🔧 Versão atual: " . ($article->vehicle_data_version ?? 'null'));
        }

        return $article;
    }

    /**
     * Buscar AMBOS os artigos do mesmo veículo
     * ✅ CORRIGIDO: Validação mais rigorosa e debug melhorado
     */
    protected function findVehicleArticles(TirePressureArticle $baseArticle): \Illuminate\Database\Eloquent\Collection
    {
        $vehicleData = $baseArticle->vehicle_data;
        
        $this->info("🔍 Buscando artigos para: {$vehicleData['make']} {$vehicleData['model']} {$vehicleData['year']}");
        
        // ✅ BUSCA CORRIGIDA: Incluir o próprio artigo na busca
        $articles = TirePressureArticle::where('vehicle_data.make', $vehicleData['make'])
            ->where('vehicle_data.model', $vehicleData['model'])
            ->where('vehicle_data.year', $vehicleData['year'])
            ->get();

        $this->info("   📊 Artigos encontrados: {$articles->count()}");
        
        foreach ($articles as $article) {
            $this->info("   • {$article->template_type}: {$article->slug} (ID: {$article->_id})");
        }

        // ✅ VALIDAÇÃO: Se não encontrou pelo menos o próprio artigo, algo está errado
        if ($articles->isEmpty()) {
            $this->error("❌ ERRO CRÍTICO: Nem mesmo o artigo base foi encontrado!");
            $this->error("   Artigo base ID: {$baseArticle->_id}");
            $this->error("   Veículo: {$vehicleData['make']} {$vehicleData['model']} {$vehicleData['year']}");
            $this->error("   Template: {$baseArticle->template_type}");
        } elseif ($articles->count() == 1) {
            $this->warn("⚠️  Apenas 1 artigo encontrado (esperado: 2)");
            $this->warn("   Pode ser um artigo órfão ou problema na geração");
        }
        
        return $articles;
    }

    /**
     * Aplicar correção em um único artigo
     * ✅ SIMPLIFICADO: Processar 1 artigo por vez
     */
    protected function applyCorrectionsToArticle(TirePressureArticle $article, array $correctedData, bool $isDryRun): void
    {
        $vehicleData = $article->vehicle_data ?? [];
        $vehicleName = "{$vehicleData['make']} {$vehicleData['model']} {$vehicleData['year']}";
        
        $this->info("🔧 Corrigindo artigo: {$article->slug}");
        $this->info("   🚗 Veículo: {$vehicleName}");
        $this->info("   📄 Template: {$article->template_type}");
        
        if (!$isDryRun) {
            // Usar o método do model para aplicar correções
            $article->markVehicleDataAsCorrected($correctedData);
            $this->info("   ✅ Correção aplicada e salva");
        } else {
            $this->info("   🔍 [DRY-RUN] Seria atualizado");
        }
    }

    /**
     * Exibir resultados da correção
     * ✅ SIMPLIFICADO: Para 1 artigo
     */
    protected function displayResults(TirePressureArticle $article, array $correctedData): void
    {
        $vehicleData = $article->vehicle_data ?? [];
        $vehicleName = $correctedData['vehicle_full_name'] ?? "{$vehicleData['make']} {$vehicleData['model']} {$vehicleData['year']}";
        
        $this->info("\n📊 CORREÇÃO APLICADA:");
        $this->line("🚗 Veículo: {$vehicleName}");
        $this->line("📄 Template: {$article->template_type}");
        $this->line("📄 Artigo: {$article->slug}");
        
        $this->info("\n🔧 DADOS CORRIGIDOS:");
        $this->line("• Segmento: " . ($correctedData['vehicle_segment'] ?? 'N/A'));
        $this->line("• Pressão vazio: " . ($correctedData['empty_pressure_display'] ?? 'N/A'));
        $this->line("• Pressão carregado: " . ($correctedData['loaded_pressure_display'] ?? 'N/A'));
        $this->line("• Pressão display: " . ($correctedData['pressure_display'] ?? 'N/A'));
        $this->line("• Premium: " . (($correctedData['is_premium'] ?? false) ? 'Sim' : 'Não'));
        $this->line("• TPMS: " . (($correctedData['has_tpms'] ?? false) ? 'Sim' : 'Não'));
        
        $this->info("\n✅ Correção concluída!");
        $this->info("💡 Execute novamente para processar o próximo artigo");
    }
}