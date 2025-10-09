<?php

namespace Src\ContentGeneration\ReviewSchedule\Console;

use Illuminate\Console\Command;
use Src\AutoInfoCenter\Domain\Eloquent\Article;

class FixMotorcycleDetailedScheduleCommand extends Command
{
    protected $signature = 'review-schedule:fix-motorcycle-detailed {--limit=100 : Limite de artigos} {--force : Executar sem confirmação}';
    protected $description = 'Corrige cronograma detalhado dos artigos de motocicletas';

    public function handle(): int
    {
        $limit = $this->option('limit');
        $force = $this->option('force');

        // Buscar artigos de motocicletas com problema
        $articles = Article::where('template', 'review_schedule_motorcycle')
            ->limit($limit)
            ->get();

        if ($articles->isEmpty()) {
            $this->info('✅ Nenhum artigo de motocicleta encontrado para correção.');
            return self::SUCCESS;
        }

        $this->info("🔍 Encontrados {$articles->count()} artigos de motocicletas para análise.");

        // Analisar problemas
        $problemArticles = [];
        foreach ($articles as $article) {
            if ($this->hasIncorrectSchedule($article)) {
                $problemArticles[] = $article;
            }
        }

        if (empty($problemArticles)) {
            $this->info('✅ Todos os cronogramas de motocicletas estão corretos.');
            return self::SUCCESS;
        }

        $this->warn("⚠️  " . count($problemArticles) . " artigos precisam de correção!");

        if (!$force && !$this->confirm('Deseja corrigir os cronogramas detalhados?')) {
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

        // Verificar se tem serviços inadequados para moto
        foreach ($content['cronograma_detalhado'] as $revision) {
            $services = implode(' ', $revision['servicos_principais'] ?? []);
            
            // Indicadores de conteúdo incorreto
            if (str_contains($services, 'ar-condicionado') ||
                str_contains($services, 'arrefecimento') ||
                str_contains($services, 'radiador') ||
                str_contains($services, 'anticongelante')) {
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
                $this->fixMotorcycleSchedule($article);
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
        $this->info("📊 Artigos corrigidos: {$fixed}");
        
        if ($errors > 0) {
            $this->warn("⚠️  Erros: {$errors}");
        }

        return self::SUCCESS;
    }

    private function fixMotorcycleSchedule(Article $article): void
    {
        $content = $article->content;
        $vehicleData = $article->extracted_entities ?? [];

        // Gerar cronograma correto para motocicleta
        $content['cronograma_detalhado'] = $this->generateCorrectMotorcycleSchedule($vehicleData);

        $article->content = $content;
        $article->save();
    }

    private function generateCorrectMotorcycleSchedule(array $vehicleData): array
    {
        $make = strtolower($vehicleData['marca'] ?? '');
        $model = strtolower($vehicleData['modelo'] ?? '');

        $revisions = [
            [
                'numero_revisao' => 1,
                'intervalo' => '1.000 km ou 6 meses',
                'km' => '1.000',
                'servicos_principais' => [
                    'Primeira revisão obrigatória (amaciamento)',
                    'Troca de óleo e filtro do motor',
                    'Verificação de folgas das válvulas',
                    'Ajuste da corrente de transmissão'
                ],
                'verificacoes_complementares' => [
                    'Verificação da pressão dos pneus',
                    'Teste do sistema de freios',
                    'Inspeção do sistema de iluminação',
                    'Verificação dos níveis de fluidos'
                ],
                'estimativa_custo' => $this->getCostForRevision(1, $make),
                'observacoes' => 'Revisão obrigatória do período de amaciamento'
            ],
            [
                'numero_revisao' => 2,
                'intervalo' => '5.000 km ou 12 meses',
                'km' => '5.000',
                'servicos_principais' => [
                    'Troca de óleo e filtro do motor',
                    'Verificação e ajuste da corrente',
                    'Inspeção das pastilhas de freio',
                    'Verificação do sistema elétrico'
                ],
                'verificacoes_complementares' => [
                    'Verificação da pressão dos pneus',
                    'Teste da bateria e sistema de carga',
                    'Inspeção do sistema de escape',
                    'Verificação de vazamentos'
                ],
                'estimativa_custo' => $this->getCostForRevision(2, $make),
                'observacoes' => 'Manutenção regular dos sistemas principais'
            ],
            [
                'numero_revisao' => 3,
                'intervalo' => '10.000 km ou 18 meses',
                'km' => '10.000',
                'servicos_principais' => [
                    'Troca de óleo e filtro do motor',
                    'Verificação e ajuste das válvulas',
                    'Inspeção da embreagem',
                    'Limpeza do filtro de ar'
                ],
                'verificacoes_complementares' => [
                    'Verificação do desgaste dos pneus',
                    'Teste do sistema de freios',
                    'Inspeção da suspensão',
                    'Verificação do sistema de combustível'
                ],
                'estimativa_custo' => $this->getCostForRevision(3, $make),
                'observacoes' => 'Revisão intermediária com foco no motor'
            ],
            [
                'numero_revisao' => 4,
                'intervalo' => '15.000 km ou 24 meses',
                'km' => '15.000',
                'servicos_principais' => [
                    'Troca de óleo e filtro do motor',
                    'Substituição das velas de ignição',
                    'Troca do fluido de freio',
                    'Verificação dos rolamentos das rodas'
                ],
                'verificacoes_complementares' => [
                    'Inspeção do sistema de transmissão',
                    'Verificação da pressão dos pneus',
                    'Teste do sistema elétrico completo',
                    'Verificação de folgas e apertos'
                ],
                'estimativa_custo' => $this->getCostForRevision(4, $make),
                'observacoes' => 'Manutenção ampla com troca de componentes críticos'
            ],
            [
                'numero_revisao' => 5,
                'intervalo' => '20.000 km ou 30 meses',
                'km' => '20.000',
                'servicos_principais' => [
                    'Troca de óleo e filtro do motor',
                    'Verificação completa da transmissão',
                    'Inspeção das pastilhas e discos de freio',
                    'Sincronização dos carburadores/injeção'
                ],
                'verificacoes_complementares' => [
                    'Verificação da suspensão dianteira e traseira',
                    'Teste de performance do motor',
                    'Inspeção do sistema de escape',
                    'Verificação de todos os fluidos'
                ],
                'estimativa_custo' => $this->getCostForRevision(5, $make),
                'observacoes' => 'Revisão ampla com foco na performance'
            ],
            [
                'numero_revisao' => 6,
                'intervalo' => '25.000 km ou 36 meses',
                'km' => '25.000',
                'servicos_principais' => [
                    'Troca de óleo e filtro do motor',
                    'Revisão completa do sistema de transmissão',
                    'Manutenção da suspensão',
                    'Verificação completa dos sistemas eletrônicos'
                ],
                'verificacoes_complementares' => [
                    'Inspeção estrutural completa',
                    'Verificação de desgastes gerais',
                    'Teste de todos os sistemas de segurança',
                    'Avaliação do estado geral da motocicleta'
                ],
                'estimativa_custo' => $this->getCostForRevision(6, $make),
                'observacoes' => 'Revisão extensiva com foco na longevidade'
            ]
        ];

        return $revisions;
    }

    private function getCostForRevision(int $revision, string $make): string
    {
        $baseCosts = [
            1 => ['min' => 250, 'max' => 400],
            2 => ['min' => 300, 'max' => 450],
            3 => ['min' => 400, 'max' => 600],
            4 => ['min' => 500, 'max' => 750],
            5 => ['min' => 600, 'max' => 900],
            6 => ['min' => 700, 'max' => 1000]
        ];

        // Ajustar por marca (premium vs popular)
        $premiumBrands = ['bmw', 'ducati', 'triumph', 'harley', 'harley-davidson'];
        $multiplier = in_array($make, $premiumBrands) ? 1.3 : 1.0;

        $min = (int)($baseCosts[$revision]['min'] * $multiplier);
        $max = (int)($baseCosts[$revision]['max'] * $multiplier);

        return "R$ {$min} - R$ {$max}";
    }
}