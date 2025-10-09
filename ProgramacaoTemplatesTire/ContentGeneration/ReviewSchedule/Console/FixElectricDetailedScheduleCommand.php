<?php

namespace Src\ContentGeneration\ReviewSchedule\Console;


use Illuminate\Console\Command;
use Src\AutoInfoCenter\Domain\Eloquent\Article;

class FixElectricDetailedScheduleCommand extends Command
{
    protected $signature = 'review-schedule:fix-electric-detailed {--limit=100 : Limite de artigos} {--force : Executar sem confirmação}';
    protected $description = 'Corrige cronograma detalhado dos artigos de veículos elétricos';

    public function handle(): int
    {
        $limit = $this->option('limit');
        $force = $this->option('force');

        // Buscar artigos de elétricos com problema
        $articles = Article::where('template', 'review_schedule_electric')
            ->where('status', 'published')
            ->limit($limit)
            ->get();

        if ($articles->isEmpty()) {
            $this->info('✅ Nenhum artigo elétrico encontrado para correção.');
            return self::SUCCESS;
        }

        $this->info("🔍 Encontrados {$articles->count()} artigos elétricos para análise.");

        // Analisar problemas
        $problemArticles = [];
        foreach ($articles as $article) {
            if ($this->hasIncorrectSchedule($article)) {
                $problemArticles[] = $article;
            }
        }

        if (empty($problemArticles)) {
            $this->info('✅ Todos os cronogramas elétricos estão corretos.');
            return self::SUCCESS;
        }

        $this->warn("⚠️  " . count($problemArticles) . " artigos elétricos precisam de correção!");

        if (!$force && !$this->confirm('Deseja corrigir os cronogramas detalhados dos elétricos?')) {
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

        // Verificar se tem serviços IMPOSSÍVEIS para elétricos
        foreach ($content['cronograma_detalhado'] as $revision) {
            $services = implode(' ', $revision['servicos_principais'] ?? []);
            
            // Serviços que NÃO existem em carros elétricos
            if (str_contains($services, 'óleo') ||
                str_contains($services, 'combustível') ||
                str_contains($services, 'injeção') ||
                str_contains($services, 'embreagem') ||
                str_contains($services, 'motor') ||
                str_contains($services, 'filtro do motor')) {
                return true;
            }
        }

        // Verificar cronogramas idênticos
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
            
            if ($duplicateCount > 3) {
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
                $this->fixElectricSchedule($article);
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
        $this->info("📊 Artigos elétricos corrigidos: {$fixed}");
        
        if ($errors > 0) {
            $this->warn("⚠️  Erros: {$errors}");
        }

        return self::SUCCESS;
    }

    private function fixElectricSchedule(Article $article): void
    {
        $content = $article->content;
        $vehicleData = $article->extracted_entities ?? [];

        // Gerar cronograma correto para elétrico
        $content['cronograma_detalhado'] = $this->generateCorrectElectricSchedule($vehicleData);

        $article->content = $content;
        $article->save();
    }

    private function generateCorrectElectricSchedule(array $vehicleData): array
    {
        $make = strtolower($vehicleData['marca'] ?? '');
        $model = strtolower($vehicleData['modelo'] ?? '');

        $revisions = [
            [
                'numero_revisao' => 1,
                'intervalo' => '10.000 km ou 12 meses',
                'km' => '10.000',
                'servicos_principais' => [
                    'Diagnóstico inicial da bateria de alta tensão',
                    'Verificação dos sistemas de freios regenerativos',
                    'Inspeção dos conectores de carregamento',
                    'Teste dos sistemas eletrônicos básicos'
                ],
                'verificacoes_complementares' => [
                    'Verificação da pressão dos pneus',
                    'Teste do sistema de climatização',
                    'Inspeção do sistema de iluminação LED',
                    'Verificação da central eletrônica'
                ],
                'estimativa_custo' => $this->getCostForRevision(1, $make),
                'observacoes' => 'Primeira verificação dos sistemas elétricos'
            ],
            [
                'numero_revisao' => 2,
                'intervalo' => '20.000 km ou 24 meses',
                'km' => '20.000',
                'servicos_principais' => [
                    'Análise da degradação da bateria',
                    'Calibração dos freios regenerativos',
                    'Limpeza dos conectores de alta tensão',
                    'Verificação do sistema de refrigeração da bateria'
                ],
                'verificacoes_complementares' => [
                    'Teste de autonomia e eficiência',
                    'Verificação do inversor de potência',
                    'Inspeção dos cabos de alta tensão',
                    'Atualização de software disponível'
                ],
                'estimativa_custo' => $this->getCostForRevision(2, $make),
                'observacoes' => 'Manutenção dos sistemas de propulsão elétrica'
            ],
            [
                'numero_revisao' => 3,
                'intervalo' => '30.000 km ou 36 meses',
                'km' => '30.000',
                'servicos_principais' => [
                    'Diagnóstico avançado da bateria de tração',
                    'Verificação do sistema térmico da bateria',
                    'Troca do fluido de freio',
                    'Inspeção completa dos sistemas de alta tensão'
                ],
                'verificacoes_complementares' => [
                    'Teste de isolamento elétrico',
                    'Verificação dos sensores de temperatura',
                    'Inspeção do sistema de ar-condicionado',
                    'Análise dos dados de performance'
                ],
                'estimativa_custo' => $this->getCostForRevision(3, $make),
                'observacoes' => 'Manutenção preventiva intermediária elétrica'
            ],
            [
                'numero_revisao' => 4,
                'intervalo' => '40.000 km ou 48 meses',
                'km' => '40.000',
                'servicos_principais' => [
                    'Revisão completa do sistema de propulsão',
                    'Manutenção do sistema de refrigeração',
                    'Atualização de software dos controladores',
                    'Verificação da eletrônica de potência'
                ],
                'verificacoes_complementares' => [
                    'Teste de capacidade total da bateria',
                    'Verificação dos conversores DC-DC',
                    'Inspeção dos motores elétricos',
                    'Análise de eficiência energética'
                ],
                'estimativa_custo' => $this->getCostForRevision(4, $make),
                'observacoes' => 'Revisão avançada com foco na eletrônica'
            ],
            [
                'numero_revisao' => 5,
                'intervalo' => '50.000 km ou 60 meses',
                'km' => '50.000',
                'servicos_principais' => [
                    'Análise completa da saúde da bateria',
                    'Manutenção dos sistemas auxiliares elétricos',
                    'Verificação dos sistemas de segurança',
                    'Teste de performance dos motores elétricos'
                ],
                'verificacoes_complementares' => [
                    'Inspeção estrutural dos componentes',
                    'Verificação dos sistemas de proteção',
                    'Teste de todos os sensores elétricos',
                    'Calibração dos sistemas de assistência'
                ],
                'estimativa_custo' => $this->getCostForRevision(5, $make),
                'observacoes' => 'Verificação completa dos sistemas críticos'
            ],
            [
                'numero_revisao' => 6,
                'intervalo' => '60.000 km ou 72 meses',
                'km' => '60.000',
                'servicos_principais' => [
                    'Revisão extensiva da bateria de alta tensão',
                    'Manutenção completa dos sistemas eletrônicos',
                    'Otimização de software e calibrações',
                    'Análise de vida útil restante dos componentes'
                ],
                'verificacoes_complementares' => [
                    'Teste de capacidade e autonomia máximas',
                    'Verificação de todos os sistemas de segurança',
                    'Inspeção completa da estrutura elétrica',
                    'Avaliação geral do veículo elétrico'
                ],
                'estimativa_custo' => $this->getCostForRevision(6, $make),
                'observacoes' => 'Revisão extensiva para máxima longevidade elétrica'
            ]
        ];

        return $revisions;
    }

    private function getCostForRevision(int $revision, string $make): string
    {
        $baseCosts = [
            1 => ['min' => 350, 'max' => 500],
            2 => ['min' => 450, 'max' => 650],
            3 => ['min' => 600, 'max' => 850],
            4 => ['min' => 750, 'max' => 1100],
            5 => ['min' => 650, 'max' => 950],
            6 => ['min' => 900, 'max' => 1400]
        ];

        // Ajustar por marca (premium vs popular)
        $premiumBrands = ['tesla', 'mercedes', 'mercedes-benz', 'bmw', 'audi', 'volvo', 'jaguar', 'porsche'];
        $popularBrands = ['chevrolet', 'renault', 'fiat', 'hyundai', 'kia'];
        
        $multiplier = 1.0;
        if (in_array($make, $premiumBrands)) {
            $multiplier = 1.6; // Elétricos premium são mais caros
        } elseif (in_array($make, $popularBrands)) {
            $multiplier = 0.8;
        }

        $min = (int)($baseCosts[$revision]['min'] * $multiplier);
        $max = (int)($baseCosts[$revision]['max'] * $multiplier);

        return "R$ {$min} - R$ {$max}";
    }
}