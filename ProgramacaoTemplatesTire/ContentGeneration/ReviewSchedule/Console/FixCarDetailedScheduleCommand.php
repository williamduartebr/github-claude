<?php

namespace Src\ContentGeneration\ReviewSchedule\Console;


use Illuminate\Console\Command;
use Src\AutoInfoCenter\Domain\Eloquent\Article;

class FixCarDetailedScheduleCommand extends Command
{
    protected $signature = 'review-schedule:fix-car-detailed {--limit=100 : Limite de artigos} {--force : Executar sem confirmação}';
    protected $description = 'Corrige cronograma detalhado dos artigos de carros convencionais';

    public function handle(): int
    {
        $limit = $this->option('limit');
        $force = $this->option('force');

        // Buscar artigos de carros com problema
        $articles = Article::where('template', 'review_schedule_car')
            ->limit($limit)
            ->get();

        if ($articles->isEmpty()) {
            $this->info('✅ Nenhum artigo de carro encontrado para correção.');
            return self::SUCCESS;
        }

        $this->info("🔍 Encontrados {$articles->count()} artigos de carros para análise.");

        // Analisar problemas
        $problemArticles = [];
        foreach ($articles as $article) {
            if ($this->hasIncorrectSchedule($article)) {
                $problemArticles[] = $article;
            }
        }

        if (empty($problemArticles)) {
            $this->info('✅ Todos os cronogramas de carros estão corretos.');
            return self::SUCCESS;
        }

        $this->warn("⚠️  " . count($problemArticles) . " artigos de carros precisam de correção!");

        if (!$force && !$this->confirm('Deseja corrigir os cronogramas detalhados dos carros?')) {
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

        // Verificar se tem serviços muito genéricos
        foreach ($content['cronograma_detalhado'] as $revision) {
            $services = implode(' ', $revision['servicos_principais'] ?? []);
            
            // Indicadores de conteúdo genérico demais
            if (str_contains($services, 'Verificação minuciosa') ||
                str_contains($services, 'Diagnóstico básico') ||
                str_contains($services, 'Inspeção detalhada dos pneumáticos')) {
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
                $this->fixCarSchedule($article);
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
        $this->info("📊 Artigos de carros corrigidos: {$fixed}");
        
        if ($errors > 0) {
            $this->warn("⚠️  Erros: {$errors}");
        }

        return self::SUCCESS;
    }

    private function fixCarSchedule(Article $article): void
    {
        $content = $article->content;
        $vehicleData = $article->extracted_entities ?? [];

        // Gerar cronograma correto para carro
        $content['cronograma_detalhado'] = $this->generateCorrectCarSchedule($vehicleData);

        $article->content = $content;
        $article->save();
    }

    private function generateCorrectCarSchedule(array $vehicleData): array
    {
        $make = strtolower($vehicleData['marca'] ?? '');
        $model = strtolower($vehicleData['modelo'] ?? '');
        $year = $vehicleData['ano'] ?? date('Y');

        $revisions = [
            [
                'numero_revisao' => 1,
                'intervalo' => '10.000 km ou 12 meses',
                'km' => '10.000',
                'servicos_principais' => [
                    'Troca de óleo e filtro do motor',
                    'Verificação dos sistemas de freios',
                    'Inspeção dos filtros de ar e combustível',
                    'Diagnóstico dos sistemas básicos'
                ],
                'verificacoes_complementares' => [
                    'Verificação da pressão dos pneus',
                    'Teste da bateria e sistema de carga',
                    'Inspeção do sistema de iluminação',
                    'Verificação dos níveis de fluidos'
                ],
                'estimativa_custo' => $this->getCostForRevision(1, $make),
                'observacoes' => 'Primeira revisão com verificações básicas'
            ],
            [
                'numero_revisao' => 2,
                'intervalo' => '20.000 km ou 24 meses',
                'km' => '20.000',
                'servicos_principais' => [
                    'Troca de óleo e filtro do motor',
                    'Substituição dos filtros de ar e combustível',
                    'Verificação do sistema de arrefecimento',
                    'Inspeção das pastilhas de freio'
                ],
                'verificacoes_complementares' => [
                    'Verificação do sistema de escape',
                    'Teste do sistema de injeção',
                    'Inspeção das correias auxiliares',
                    'Verificação da suspensão'
                ],
                'estimativa_custo' => $this->getCostForRevision(2, $make),
                'observacoes' => 'Manutenção dos sistemas de filtração e freios'
            ],
            [
                'numero_revisao' => 3,
                'intervalo' => '30.000 km ou 36 meses',
                'km' => '30.000',
                'servicos_principais' => [
                    'Troca de óleo e filtro do motor',
                    'Limpeza do sistema de injeção',
                    'Verificação da embreagem (câmbio manual)',
                    'Troca do fluido de freio'
                ],
                'verificacoes_complementares' => [
                    'Análise do sistema elétrico completo',
                    'Verificação da direção hidráulica',
                    'Inspeção do sistema de climatização',
                    'Teste de alinhamento e balanceamento'
                ],
                'estimativa_custo' => $this->getCostForRevision(3, $make),
                'observacoes' => 'Manutenção preventiva intermediária'
            ],
            [
                'numero_revisao' => 4,
                'intervalo' => '40.000 km ou 48 meses',
                'km' => '40.000',
                'servicos_principais' => [
                    'Troca de óleo e filtro do motor',
                    'Substituição das velas de ignição',
                    'Verificação das correias do motor',
                    'Inspeção do sistema de transmissão'
                ],
                'verificacoes_complementares' => [
                    'Teste do sistema de ignição completo',
                    'Verificação dos amortecedores',
                    'Inspeção dos terminais de direção',
                    'Análise do sistema de escape'
                ],
                'estimativa_custo' => $this->getCostForRevision(4, $make),
                'observacoes' => 'Revisão com foco em ignição e transmissão'
            ],
            [
                'numero_revisao' => 5,
                'intervalo' => '50.000 km ou 60 meses',
                'km' => '50.000',
                'servicos_principais' => [
                    'Troca de óleo e filtro do motor',
                    'Manutenção do sistema de arrefecimento',
                    'Verificação da direção e suspensão',
                    'Inspeção das pastilhas e discos de freio'
                ],
                'verificacoes_complementares' => [
                    'Teste do sistema de ar-condicionado',
                    'Verificação da bomba de combustível',
                    'Inspeção dos sensores do motor',
                    'Análise de desgaste dos pneus'
                ],
                'estimativa_custo' => $this->getCostForRevision(5, $make),
                'observacoes' => 'Manutenção dos sistemas de conforto e direção'
            ],
            [
                'numero_revisao' => 6,
                'intervalo' => '60.000 km ou 72 meses',
                'km' => '60.000',
                'servicos_principais' => [
                    'Troca de óleo e filtro do motor',
                    'Substituição da correia dentada',
                    'Revisão completa dos freios',
                    'Manutenção geral dos fluidos'
                ],
                'verificacoes_complementares' => [
                    'Inspeção estrutural completa',
                    'Verificação de todos os sistemas eletrônicos',
                    'Teste de performance do motor',
                    'Avaliação geral do veículo'
                ],
                'estimativa_custo' => $this->getCostForRevision(6, $make),
                'observacoes' => 'Revisão extensiva para máxima durabilidade'
            ]
        ];

        return $revisions;
    }

    private function getCostForRevision(int $revision, string $make): string
    {
        $baseCosts = [
            1 => ['min' => 280, 'max' => 380],
            2 => ['min' => 350, 'max' => 480],
            3 => ['min' => 450, 'max' => 650],
            4 => ['min' => 550, 'max' => 750],
            5 => ['min' => 600, 'max' => 850],
            6 => ['min' => 750, 'max' => 1200]
        ];

        // Ajustar por marca (premium vs popular)
        $premiumBrands = ['bmw', 'mercedes', 'audi', 'lexus', 'volvo', 'jaguar', 'land rover'];
        $popularBrands = ['chevrolet', 'ford', 'fiat', 'renault', 'volkswagen', 'hyundai'];
        
        $multiplier = 1.0;
        if (in_array($make, $premiumBrands)) {
            $multiplier = 1.5;
        } elseif (in_array($make, $popularBrands)) {
            $multiplier = 0.8;
        }

        $min = (int)($baseCosts[$revision]['min'] * $multiplier);
        $max = (int)($baseCosts[$revision]['max'] * $multiplier);

        return "R$ {$min} - R$ {$max}";
    }
}