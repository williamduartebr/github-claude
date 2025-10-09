<?php

namespace Src\ContentGeneration\ReviewSchedule\Console;

use Illuminate\Console\Command;
use Src\AutoInfoCenter\Domain\Eloquent\Article;

class FixHybridDetailedScheduleCommand extends Command
{
    protected $signature = 'review-schedule:fix-hybrid-detailed {--limit=100 : Limite de artigos} {--force : Executar sem confirmação}';
    protected $description = 'Corrige cronograma detalhado dos artigos de veículos híbridos';

    public function handle(): int
    {
        $limit = $this->option('limit');
        $force = $this->option('force');

        // Buscar artigos de híbridos com problema
        $articles = Article::where('template', 'review_schedule_hybrid')
            ->limit($limit)
            ->get();

        if ($articles->isEmpty()) {
            $this->info('✅ Nenhum artigo híbrido encontrado para correção.');
            return self::SUCCESS;
        }

        $this->info("🔍 Encontrados {$articles->count()} artigos híbridos para análise.");

        // Analisar problemas
        $problemArticles = [];
        foreach ($articles as $article) {
            if ($this->hasIncorrectSchedule($article)) {
                $problemArticles[] = $article;
            }
        }

        if (empty($problemArticles)) {
            $this->info('✅ Todos os cronogramas híbridos estão corretos.');
            return self::SUCCESS;
        }

        $this->warn("⚠️  " . count($problemArticles) . " artigos híbridos precisam de correção!");

        if (!$force && !$this->confirm('Deseja corrigir os cronogramas detalhados dos híbridos?')) {
            $this->info('Operação cancelada.');
            return self::SUCCESS;
        }

        return $this->fixArticles($problemArticles);
    }

    private function hasIncorrectSchedule(Article $article): bool
    {
        $content = $article->content;
        
        if (empty($content['cronograma_detalhado'])) {
            return true;
        }

        // Verificar se tem cronogramas idênticos (problema comum)
        $schedules = $content['cronograma_detalhado'];
        if (count($schedules) >= 2) {
            $firstServices = implode('|', $schedules[0]['servicos_principais'] ?? []);
            $duplicateCount = 0;
            
            foreach ($schedules as $schedule) {
                $currentServices = implode('|', $schedule['servicos_principais'] ?? []);
                if ($firstServices === $currentServices) {
                    $duplicateCount++;
                }
            }
            
            // Se mais de 3 revisões são idênticas, há problema
            if ($duplicateCount > 3) {
                return true;
            }
        }

        // Verificar se tem serviços inadequados para híbrido
        foreach ($content['cronograma_detalhado'] as $revision) {
            $services = implode(' ', $revision['servicos_principais'] ?? []);
            
            // Indicadores de conteúdo inadequado para híbrido
            if (str_contains($services, 'ar-condicionado') ||
                str_contains($services, 'Diagnóstico básico dos sistemas elétricos') ||
                (!str_contains($services, 'híbrido') && !str_contains($services, 'bateria') && !str_contains($services, 'regenerativo'))) {
                return true;
            }
        }

        return false;
    }

    private function fixArticles(array $articles): int
    {
        $fixed = 0;
        $errors = 0;

        $progressBar = $this->output->createProgressBar(count($articles));
        $progressBar->start();

        foreach ($articles as $article) {
            try {
                $this->fixHybridSchedule($article);
                $fixed++;
            } catch (\Exception $e) {
                $errors++;
                $this->error("\nErro ao corrigir {$article->slug}: " . $e->getMessage());
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("✅ Correção concluída!");
        $this->info("📊 Artigos híbridos corrigidos: {$fixed}");
        
        if ($errors > 0) {
            $this->warn("⚠️  Erros: {$errors}");
        }

        return self::SUCCESS;
    }

    private function fixHybridSchedule(Article $article): void
    {
        $content = $article->content;
        $vehicleData = $article->extracted_entities ?? [];

        // Gerar cronograma correto para híbrido
        $content['cronograma_detalhado'] = $this->generateCorrectHybridSchedule($vehicleData);

        $article->content = $content;
        $article->save();
    }

    private function generateCorrectHybridSchedule(array $vehicleData): array
    {
        $make = strtolower($vehicleData['marca'] ?? '');
        $model = strtolower($vehicleData['modelo'] ?? '');

        $revisions = [
            [
                'numero_revisao' => 1,
                'intervalo' => '10.000 km ou 12 meses',
                'km' => '10.000',
                'servicos_principais' => [
                    'Troca de óleo motor híbrido (0W20 sintético)',
                    'Verificação inicial da bateria híbrida',
                    'Diagnóstico do sistema híbrido completo',
                    'Calibração dos freios regenerativos'
                ],
                'verificacoes_complementares' => [
                    'Verificação da pressão dos pneus',
                    'Teste do sistema de alta voltagem',
                    'Inspeção dos conectores híbridos',
                    'Verificação dos modos de condução'
                ],
                'estimativa_custo' => $this->getCostForRevision(1, $make),
                'observacoes' => 'Primeira revisão com foco na adaptação do sistema híbrido'
            ],
            [
                'numero_revisao' => 2,
                'intervalo' => '20.000 km ou 24 meses',
                'km' => '20.000',
                'servicos_principais' => [
                    'Troca de óleo e filtro do motor híbrido',
                    'Diagnóstico da bateria de alta tensão',
                    'Verificação do sistema de arrefecimento duplo',
                    'Inspeção dos freios regenerativos'
                ],
                'verificacoes_complementares' => [
                    'Análise da eficiência energética',
                    'Teste do conversor DC-DC',
                    'Verificação do sistema de escape',
                    'Inspeção da transmissão e-CVT'
                ],
                'estimativa_custo' => $this->getCostForRevision(2, $make),
                'observacoes' => 'Manutenção dos sistemas de propulsão dual'
            ],
            [
                'numero_revisao' => 3,
                'intervalo' => '30.000 km ou 36 meses',
                'km' => '30.000',
                'servicos_principais' => [
                    'Troca de óleo específico para híbridos',
                    'Limpeza do sistema de injeção otimizado',
                    'Calibração da unidade de controle híbrida',
                    'Verificação do sistema térmico avançado'
                ],
                'verificacoes_complementares' => [
                    'Análise da degradação da bateria',
                    'Teste de autonomia em modo elétrico',
                    'Verificação das soldas de alta voltagem',
                    'Inspeção do sistema de climatização'
                ],
                'estimativa_custo' => $this->getCostForRevision(3, $make),
                'observacoes' => 'Manutenção preventiva intermediária híbrida'
            ],
            [
                'numero_revisao' => 4,
                'intervalo' => '40.000 km ou 48 meses',
                'km' => '40.000',
                'servicos_principais' => [
                    'Troca de óleo e análise da viscosidade',
                    'Substituição do filtro de ar de alta eficiência',
                    'Verificação completa da transmissão híbrida',
                    'Atualização de software do sistema'
                ],
                'verificacoes_complementares' => [
                    'Diagnóstico de códigos de erro híbridos',
                    'Teste de performance do motor elétrico',
                    'Verificação de vazamentos no sistema',
                    'Inspeção de cabos de alta voltagem'
                ],
                'estimativa_custo' => $this->getCostForRevision(4, $make),
                'observacoes' => 'Revisão ampla com atualização tecnológica'
            ],
            [
                'numero_revisao' => 5,
                'intervalo' => '50.000 km ou 60 meses',
                'km' => '50.000',
                'servicos_principais' => [
                    'Troca de óleo com aditivos especiais',
                    'Manutenção do sistema de arrefecimento híbrido',
                    'Verificação da eletrônica de potência',
                    'Teste de eficiência energética completo'
                ],
                'verificacoes_complementares' => [
                    'Análise térmica da bateria híbrida',
                    'Verificação do isolamento elétrico',
                    'Teste de todos os sensores híbridos',
                    'Calibração dos sistemas de assistência'
                ],
                'estimativa_custo' => $this->getCostForRevision(5, $make),
                'observacoes' => 'Verificação avançada dos sistemas críticos'
            ],
            [
                'numero_revisao' => 6,
                'intervalo' => '60.000 km ou 72 meses',
                'km' => '60.000',
                'servicos_principais' => [
                    'Troca de óleo e todos os filtros híbridos',
                    'Revisão completa da transmissão e-CVT',
                    'Manutenção avançada da bateria híbrida',
                    'Otimização completa do sistema híbrido'
                ],
                'verificacoes_complementares' => [
                    'Teste de capacidade total da bateria',
                    'Verificação estrutural do veículo',
                    'Análise de desgaste dos componentes',
                    'Avaliação da vida útil restante'
                ],
                'estimativa_custo' => $this->getCostForRevision(6, $make),
                'observacoes' => 'Revisão extensiva para máxima longevidade híbrida'
            ]
        ];

        return $revisions;
    }

    private function getCostForRevision(int $revision, string $make): string
    {
        $baseCosts = [
            1 => ['min' => 500, 'max' => 650],
            2 => ['min' => 650, 'max' => 850],
            3 => ['min' => 800, 'max' => 1100],
            4 => ['min' => 950, 'max' => 1300],
            5 => ['min' => 750, 'max' => 1050],
            6 => ['min' => 1200, 'max' => 1800]
        ];

        // Ajustar por marca (premium vs popular)
        $premiumBrands = ['lexus', 'bmw', 'mercedes', 'audi', 'infiniti', 'acura'];
        $multiplier = in_array($make, $premiumBrands) ? 1.4 : 1.0;

        $min = (int)($baseCosts[$revision]['min'] * $multiplier);
        $max = (int)($baseCosts[$revision]['max'] * $multiplier);

        return "R$ {$min} - R$ {$max}";
    }
}