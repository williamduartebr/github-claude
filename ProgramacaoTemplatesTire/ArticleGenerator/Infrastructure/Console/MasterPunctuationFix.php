<?php

namespace Src\ArticleGenerator\Infrastructure\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class MasterPunctuationFix extends Command
{
    /**
     * Nome e assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'articles:master-punctuation-fix 
                           {--phase=analyze : Fase a executar (analyze|process|correct|full|maintenance|stats|health)}
                           {--limit=50 : Limite de artigos por fase}
                           {--template= : Filtrar por template específico}
                           {--priority=all : Prioridade para correções (high/medium/low/all)}
                           {--dry-run : Executar em modo simulação}
                           {--delay=3 : Delay entre processamentos em segundos}
                           {--force : Forçar reanálise de artigos já analisados}
                           {--clean : Limpar dados antigos antes de executar}
                           {--auto : Executar sem confirmações (modo automático)}
                           {--progress : Mostrar barras de progresso detalhadas}
                           {--batch-size=10 : Tamanho do lote para análises}';

    /**
     * Descrição do comando.
     *
     * @var string
     */
    protected $description = 'Sistema master para análise e correção de pontuação em lote (versão completa v2.1)';

    /**
     * Execute o comando.
     */
    public function handle()
    {
        $phase = $this->option('phase');
        $dryRun = $this->option('dry-run');

        $this->showHeader();

        // Verificar saúde do sistema se não for stats ou health
        if (!in_array($phase, ['stats', 'health'])) {
            if (!$this->checkSystemHealth()) {
                return Command::FAILURE;
            }
        }

        // Limpeza opcional antes de executar
        if ($this->option('clean') && !$dryRun) {
            $this->runCleanupPhase();
            $this->line('');
        }

        // Mostrar estatísticas iniciais
        if (!in_array($phase, ['stats'])) {
            $this->showCurrentStats();
        }

        switch ($phase) {
            case 'analyze':
                return $this->runAnalyzePhase();
            
            case 'process':
                return $this->runProcessPhase();
            
            case 'correct':
                return $this->runCorrectPhase();
            
            case 'full':
                return $this->runFullPipeline();
            
            case 'maintenance':
                return $this->runMaintenancePhase();
            
            case 'stats':
                return $this->runDetailedStats();
            
            case 'health':
                return $this->runHealthCheck();
            
            default:
                $this->error("Fase inválida: {$phase}");
                $this->showAvailablePhases();
                return Command::FAILURE;
        }
    }

    /**
     * Mostra cabeçalho do sistema
     */
    protected function showHeader()
    {
        $this->info('🚀 Master Punctuation Fix v2.1 - Sistema Inteligente de Correção');
        $this->info('================================================================');
        $this->line('');

        if ($this->option('dry-run')) {
            $this->warn('⚠️ MODO DRY-RUN ATIVO - Nenhuma alteração será feita');
            $this->line('');
        }

        if ($this->option('auto')) {
            $this->info('🤖 MODO AUTOMÁTICO - Executando sem confirmações');
            $this->line('');
        }
    }

    /**
     * Verifica saúde do sistema
     */
    protected function checkSystemHealth()
    {
        $this->info('🔍 Verificando saúde do sistema...');

        // Verificar processos rodando
        $runningProcesses = (int) shell_exec("ps aux | grep 'artisan articles:' | grep -v grep | wc -l");
        if ($runningProcesses > 3) {
            $this->error("❌ Muitos processos rodando ({$runningProcesses}). Aguarde ou finalize processos existentes.");
            return false;
        }

        // Verificar carga de CPU
        $cpuLoad = sys_getloadavg();
        if ($cpuLoad && $cpuLoad[0] > 2.5) {
            $this->warn("⚠️ Carga de CPU alta ({$cpuLoad[0]}). Considere executar em horário de menor demanda.");
            
            if (!$this->option('auto') && !$this->confirm('Continuar mesmo assim?')) {
                return false;
            }
        }

        // Verificar espaço em disco
        $diskFree = disk_free_space(storage_path());
        $diskTotal = disk_total_space(storage_path());
        if ($diskFree && $diskTotal) {
            $diskUsagePercent = (($diskTotal - $diskFree) / $diskTotal) * 100;
            
            if ($diskUsagePercent > 90) {
                $this->error("❌ Pouco espaço em disco ({$diskUsagePercent}% usado). Libere espaço antes de continuar.");
                return false;
            }
        }

        $this->info('✅ Sistema saudável - pronto para executar');
        return true;
    }

    /**
     * Mostra fases disponíveis
     */
    protected function showAvailablePhases()
    {
        $this->line('');
        $this->info('📋 FASES DISPONÍVEIS:');
        $this->table(['Fase', 'Descrição', 'Uso Recomendado'], [
            ['analyze', 'Analisa artigos em busca de problemas', 'Execução diária/noturna'],
            ['process', 'Processa análises com Claude API', 'Após análises pendentes'],
            ['correct', 'Aplica correções nos artigos', 'Após confirmação de problemas'],
            ['full', 'Executa todas as fases em sequência', 'Pipeline completo'],
            ['maintenance', 'Limpeza e manutenção do sistema', 'Semanal ou quando necessário'],
            ['stats', 'Estatísticas detalhadas do sistema', 'Monitoramento diário'],
            ['health', 'Verificação e correção de problemas', 'Quando houver inconsistências']
        ]);
    }

    /**
     * Mostra estatísticas atuais
     */
    protected function showCurrentStats()
    {
        $this->info('📊 Status Atual do Sistema:');
        $this->line('');
        
        try {
            Artisan::call('articles:analyze-punctuation', ['--stats' => true]);
            echo Artisan::output();
        } catch (\Exception $e) {
            $this->error('Erro ao obter estatísticas: ' . $e->getMessage());
        }
        
        $this->line('');
    }

    /**
     * Fase de limpeza
     */
    protected function runCleanupPhase()
    {
        $this->info('🧹 FASE DE LIMPEZA: Preparando sistema');
        $this->line('===================================');

        // Limpar análises falhadas antigas
        $this->line('🗑️ Limpando análises falhadas antigas...');
        $cleanOptions = ['--clean-failed' => true];
        
        if ($this->option('dry-run')) {
            $cleanOptions['--dry-run'] = true;
        }

        try {
            Artisan::call('articles:analyze-punctuation', $cleanOptions);
            echo Artisan::output();
        } catch (\Exception $e) {
            $this->error('Erro na limpeza: ' . $e->getMessage());
        }

        $this->info('✅ Limpeza concluída!');
    }

    /**
     * Verificação de saúde
     */
    protected function runHealthCheck()
    {
        $this->info('🏥 VERIFICAÇÃO DE SAÚDE DO SISTEMA');
        $this->line('==================================');

        $healthOptions = ['--detailed' => true];

        if ($this->option('dry-run')) {
            $healthOptions['--dry-run'] = true;
        } else {
            // Se não for dry-run, aplicar correções
            $healthOptions['--fix'] = true;
        }

        try {
            $exitCode = Artisan::call('articles:health-check', $healthOptions);
            echo Artisan::output();

            if ($exitCode === 0) {
                $this->info('✅ Sistema verificado e corrigido!');
            } else {
                $this->warn('⚠️ Alguns problemas foram encontrados - verifique os logs');
            }

            return $exitCode;
        } catch (\Exception $e) {
            $this->error('Erro na verificação de saúde: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Estatísticas detalhadas
     */
    protected function runDetailedStats()
    {
        $this->info('📈 ESTATÍSTICAS DETALHADAS DO SISTEMA');
        $this->line('====================================');

        try {
            Artisan::call('articles:punctuation-stats', [
                '--detailed' => true,
                '--problems' => !$this->option('auto') // Só mostrar problemas se não for automático
            ]);
            echo Artisan::output();

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Erro ao gerar estatísticas: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Fase de manutenção
     */
    protected function runMaintenancePhase()
    {
        $this->info('🔧 FASE DE MANUTENÇÃO: Otimização do sistema');
        $this->line('============================================');

        $results = [];

        // 1. Verificação de saúde
        $this->line('🏥 Executando verificação de saúde...');
        try {
            $healthOptions = ['--fix' => true];
            if ($this->option('dry-run')) {
                $healthOptions['--dry-run'] = true;
            }

            $results['health'] = Artisan::call('articles:health-check', $healthOptions);
            if ($results['health'] === 0) {
                $this->info('✅ Verificação de saúde concluída');
            }
        } catch (\Exception $e) {
            $this->error('Erro na verificação de saúde: ' . $e->getMessage());
            $results['health'] = 1;
        }

        // 2. Limpeza de dados antigos
        $this->line('');
        $this->line('🗑️ Limpando dados antigos...');
        try {
            $cleanOptions = ['--clean-failed' => true];
            if ($this->option('dry-run')) {
                $cleanOptions['--dry-run'] = true;
            }

            $results['cleanup'] = Artisan::call('articles:analyze-punctuation', $cleanOptions);
            if ($results['cleanup'] === 0) {
                $this->info('✅ Limpeza de dados concluída');
            }
        } catch (\Exception $e) {
            $this->error('Erro na limpeza: ' . $e->getMessage());
            $results['cleanup'] = 1;
        }

        // 3. Relatório final
        $this->line('');
        $this->info('📊 RESULTADO DA MANUTENÇÃO:');
        $totalTasks = count($results);
        $successTasks = count(array_filter($results, function($code) { return $code === 0; }));
        
        $this->table(['Tarefa', 'Status'], [
            ['Verificação de Saúde', $results['health'] === 0 ? '✅ Sucesso' : '❌ Falha'],
            ['Limpeza de Dados', $results['cleanup'] === 0 ? '✅ Sucesso' : '❌ Falha']
        ]);

        $this->line("📈 Sucesso: {$successTasks}/{$totalTasks} tarefas concluídas");

        return $successTasks === $totalTasks ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Fase 1: Análise de artigos
     */
    protected function runAnalyzePhase()
    {
        $this->info('📝 FASE 1: Análise de Problemas de Pontuação');
        $this->line('===========================================');

        $options = $this->buildAnalysisOptions();

        // Mostrar configuração
        $this->showPhaseConfiguration('Análise', $options);

        try {
            $exitCode = Artisan::call('articles:analyze-punctuation', $options);
            echo Artisan::output();

            if ($exitCode === 0) {
                $this->info('✅ Fase de análise concluída com sucesso!');
                $this->showNextStepGuidance('process');
            } else {
                $this->error('❌ Fase de análise falhou');
            }

            return $exitCode;
        } catch (\Exception $e) {
            $this->error('Erro na análise: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Constrói opções para análise
     */
    protected function buildAnalysisOptions()
    {
        $options = [
            '--limit' => $this->option('limit'),
            '--status' => 'published'
        ];

        // Estratégia de análise
        if ($this->option('force')) {
            $options['--force-reanalyze'] = true;
            $this->warn('⚠️ Modo forçado: reanalisando todos os artigos');
        } else {
            $options['--skip-analyzed'] = true;
        }

        // Template específico
        if ($this->option('template')) {
            $options['--template'] = $this->option('template');
        }

        // Configurações de processamento
        if ($this->option('batch-size')) {
            $options['--batch-size'] = $this->option('batch-size');
        }

        if ($this->option('progress')) {
            $options['--progress'] = true;
        }

        if ($this->option('dry-run')) {
            $options['--dry-run'] = true;
        }

        return $options;
    }

    /**
     * Fase 2: Processamento de análises com Claude
     */
    protected function runProcessPhase()
    {
        $this->info('⚙️ FASE 2: Processamento com Claude API');
        $this->line('=====================================');

        $options = $this->buildProcessingOptions();

        // Mostrar configuração
        $this->showPhaseConfiguration('Processamento', $options);

        try {
            $exitCode = Artisan::call('articles:process-analysis', $options);
            echo Artisan::output();

            if ($exitCode === 0) {
                $this->info('✅ Fase de processamento concluída com sucesso!');
                $this->showNextStepGuidance('correct');
            } else {
                $this->error('❌ Fase de processamento falhou');
            }

            return $exitCode;
        } catch (\Exception $e) {
            $this->error('Erro no processamento: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Constrói opções para processamento
     */
    protected function buildProcessingOptions()
    {
        $options = [
            '--type' => 'punctuation_analysis',
            '--limit' => $this->option('limit'),
            '--delay' => $this->option('delay')
        ];

        if ($this->option('priority') !== 'all') {
            $options['--priority'] = $this->option('priority');
        }

        if ($this->option('dry-run')) {
            $options['--dry-run'] = true;
        }

        return $options;
    }

    /**
     * Fase 3: Aplicação de correções
     */
    protected function runCorrectPhase()
    {
        $this->info('🔧 FASE 3: Aplicação de Correções');
        $this->line('=================================');

        $results = [];

        // Etapa 1: Criar correções baseadas em análises confirmadas
        $this->line('📝 Criando correções baseadas em análises confirmadas...');
        $results['creation'] = $this->runCorrectionCreation();

        if (!$this->option('dry-run') && $results['creation'] === 0) {
            $this->line('');
            $this->line('⚙️ Processando correções pendentes...');
            
            // Etapa 2: Processar as correções criadas
            $results['processing'] = $this->runCorrectionProcessing();
        } else {
            $results['processing'] = 0; // Sucesso em dry-run ou se criação falhou
        }

        // Resultado final
        $success = ($results['creation'] === 0) && ($results['processing'] === 0);
        
        if ($success) {
            $this->info('✅ Fase de correção concluída com sucesso!');
            $this->showCompletionGuidance();
        } else {
            $this->error('❌ Fase de correção falhou em uma ou mais etapas');
        }

        return $success ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Executa criação de correções
     */
    protected function runCorrectionCreation()
    {
        $createOptions = ['--from-analysis' => true];
        
        if ($this->option('dry-run')) {
            $createOptions['--dry-run'] = true;
        }

        try {
            $exitCode = Artisan::call('articles:process-corrections', $createOptions);
            echo Artisan::output();
            return $exitCode;
        } catch (\Exception $e) {
            $this->error('Erro na criação de correções: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Executa processamento de correções
     */
    protected function runCorrectionProcessing()
    {
        $processOptions = [
            '--type' => 'introduction_fix',
            '--limit' => $this->option('limit'),
            '--delay' => $this->option('delay')
        ];

        if ($this->option('priority') !== 'all') {
            $processOptions['--priority'] = $this->option('priority');
        }

        try {
            $exitCode = Artisan::call('articles:process-corrections', $processOptions);
            echo Artisan::output();
            return $exitCode;
        } catch (\Exception $e) {
            $this->error('Erro no processamento de correções: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Pipeline completo
     */
    protected function runFullPipeline()
    {
        $this->info('🔄 PIPELINE COMPLETO: Execução de Todas as Fases');
        $this->line('===============================================');

        if ($this->option('dry-run')) {
            $this->warn('⚠️ Modo DRY-RUN ativo - simulando execução completa');
            $this->line('');
        }

        $phases = [
            'analyze' => 'Análise de Artigos',
            'process' => 'Processamento Claude',
            'correct' => 'Aplicação de Correções'
        ];

        $results = [];
        $startTime = microtime(true);

        foreach ($phases as $phase => $description) {
            $this->line('');
            $this->info("🔄 Executando: {$description} (" . (array_search($phase, array_keys($phases)) + 1) . "/" . count($phases) . ")");
            $this->line(str_repeat('-', 60));
            
            $phaseStartTime = microtime(true);
            
            switch ($phase) {
                case 'analyze':
                    $results[$phase] = $this->runAnalyzePhase();
                    break;
                case 'process':
                    $results[$phase] = $this->runProcessPhase();
                    break;
                case 'correct':
                    $results[$phase] = $this->runCorrectPhase();
                    break;
            }

            $phaseTime = round(microtime(true) - $phaseStartTime, 2);
            
            if ($results[$phase] === Command::SUCCESS) {
                $this->info("✅ {$description} concluído em {$phaseTime}s");
            } else {
                $this->error("❌ {$description} falhou após {$phaseTime}s");
                $this->error("Pipeline interrompido devido à falha na fase: {$phase}");
                return $results[$phase];
            }

            // Delay entre fases (apenas em modo produção)
            if (!$this->option('dry-run') && $phase !== 'correct') {
                $this->line('');
                $this->info('⏳ Aguardando 5 segundos antes da próxima fase...');
                sleep(5);
            }
        }

        $totalTime = round(microtime(true) - $startTime, 2);

        // Relatório final do pipeline
        $this->showPipelineResults($results, $totalTime);

        return Command::SUCCESS;
    }

    /**
     * Mostra configuração da fase
     */
    protected function showPhaseConfiguration($phaseName, $options)
    {
        $this->line("🔧 Configuração da {$phaseName}:");
        
        $configTable = [];
        foreach ($options as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? 'SIM' : 'NÃO';
            }
            $configTable[] = [str_replace('--', '', $key), $value];
        }
        
        $this->table(['Parâmetro', 'Valor'], $configTable);
        $this->line('');
    }

    /**
     * Mostra orientação para próximo passo
     */
    protected function showNextStepGuidance($nextPhase)
    {
        $this->line('');
        $this->info('💡 Próximo passo recomendado:');
        
        switch ($nextPhase) {
            case 'process':
                $this->line('php artisan articles:master-punctuation-fix --phase=process');
                break;
            case 'correct':
                $this->line('php artisan articles:master-punctuation-fix --phase=correct');
                break;
        }
    }

    /**
     * Mostra orientação de conclusão
     */
    protected function showCompletionGuidance()
    {
        $this->line('');
        $this->info('🎯 PROCESSO CONCLUÍDO! Próximas ações recomendadas:');
        $this->line('1. 📊 Verificar resultados: php artisan articles:master-punctuation-fix --phase=stats');
        $this->line('2. 🏥 Verificar saúde: php artisan articles:master-punctuation-fix --phase=health');
        $this->line('3. 🔄 Agendar execução regular via cron ou schedule');
    }

    /**
     * Mostra resultados do pipeline
     */
    protected function showPipelineResults($results, $totalTime)
    {
        $this->line('');
        $this->info('🎉 PIPELINE CONCLUÍDO COM SUCESSO!');
        $this->line('==================================');
        
        $successCount = count(array_filter($results, function($code) { 
            return $code === Command::SUCCESS; 
        }));
        $totalPhases = count($results);
        
        $this->table(['Fase', 'Status', 'Resultado'], [
            ['📝 Análise', $results['analyze'] === Command::SUCCESS ? '✅' : '❌', 
             $results['analyze'] === Command::SUCCESS ? 'Sucesso' : 'Falha'],
            ['⚙️ Processamento', $results['process'] === Command::SUCCESS ? '✅' : '❌',
             $results['process'] === Command::SUCCESS ? 'Sucesso' : 'Falha'],
            ['🔧 Correção', $results['correct'] === Command::SUCCESS ? '✅' : '❌',
             $results['correct'] === Command::SUCCESS ? 'Sucesso' : 'Falha']
        ]);
        
        $this->line('');
        $this->info("📊 Resumo: {$successCount}/{$totalPhases} fases concluídas com sucesso");
        $this->info("⏱️ Tempo total: {$totalTime} segundos");
        
        // Estatísticas finais
        $this->line('');
        $this->info('📈 ESTATÍSTICAS FINAIS:');
        $this->showCurrentStats();
        
        $this->showPostPipelineGuidance();
    }

    /**
     * Mostra orientações pós-pipeline
     */
    protected function showPostPipelineGuidance()
    {
        $this->line('');
        $this->info('📋 RECOMENDAÇÕES PÓS-EXECUÇÃO:');
        $this->line('');
        $this->line('1. 📊 Monitoramento contínuo:');
        $this->line('   php artisan articles:master-punctuation-fix --phase=stats');
        $this->line('');
        $this->line('2. 🏥 Verificação de saúde regular:');
        $this->line('   php artisan articles:master-punctuation-fix --phase=health');
        $this->line('');
        $this->line('3. 🧹 Manutenção semanal:');
        $this->line('   php artisan articles:master-punctuation-fix --phase=maintenance');
        $this->line('');
        $this->line('4. 🔄 Automação via schedule:');
        $this->line('   Configurar execução automática no PunctuationSchedule');
        $this->line('');
        $this->line('5. 📈 Análise de templates específicos:');
        $this->line('   php artisan articles:master-punctuation-fix --phase=analyze --template=oil_recommendation');
    }

    /**
     * Mostra menu de ajuda
     */
    protected function showHelp()
    {
        $this->line('');
        $this->info('📚 Guia Completo do Master Punctuation Fix v2.1');
        $this->line('===============================================');
        $this->line('');
        
        $this->info('🎯 FASES PRINCIPAIS:');
        $this->table(['Fase', 'Descrição', 'Quando Usar'], [
            ['analyze', 'Detecta problemas de pontuação', 'Diariamente ou sob demanda'],
            ['process', 'Confirma problemas via Claude API', 'Após análises pendentes'],
            ['correct', 'Aplica correções nos artigos', 'Após confirmação de problemas'],
            ['full', 'Pipeline completo (todas as fases)', 'Processamento em lote completo'],
            ['maintenance', 'Limpeza e otimização', 'Semanalmente ou quando necessário'],
            ['stats', 'Relatórios detalhados', 'Monitoramento diário'],
            ['health', 'Diagnóstico e correção de problemas', 'Quando houver inconsistências']
        ]);
        
        $this->line('');
        $this->info('⚙️ OPÇÕES PRINCIPAIS:');
        $this->table(['Opção', 'Descrição', 'Exemplo'], [
            ['--dry-run', 'Simula execução sem alterações', '--dry-run'],
            ['--force', 'Força reanálise de artigos', '--force'],
            ['--clean', 'Limpa dados antes de executar', '--clean'],
            ['--auto', 'Executa sem confirmações', '--auto'],
            ['--progress', 'Mostra barras de progresso', '--progress'],
            ['--limit=N', 'Limita artigos processados', '--limit=100'],
            ['--template=X', 'Filtra por template', '--template=oil_recommendation'],
            ['--priority=X', 'Filtra por prioridade', '--priority=high'],
            ['--delay=N', 'Delay entre processamentos', '--delay=5'],
            ['--batch-size=N', 'Tamanho do lote', '--batch-size=20']
        ]);
        
        $this->line('');
        $this->info('📖 EXEMPLOS DE USO:');
        $this->line('');
        $this->line('# Pipeline completo em modo simulação');
        $this->line('php artisan articles:master-punctuation-fix --phase=full --dry-run --progress');
        $this->line('');
        $this->line('# Análise forçada com limpeza prévia');
        $this->line('php artisan articles:master-punctuation-fix --phase=analyze --force --clean --limit=50');
        $this->line('');
        $this->line('# Processamento de alta prioridade');
        $this->line('php artisan articles:master-punctuation-fix --phase=process --priority=high --auto');
        $this->line('');
        $this->line('# Template específico com progresso');
        $this->line('php artisan articles:master-punctuation-fix --phase=full --template=tire_recommendation --progress');
        $this->line('');
        $this->line('# Manutenção completa');
        $this->line('php artisan articles:master-punctuation-fix --phase=maintenance --auto');
        $this->line('');
        $this->line('# Verificação de saúde com correções');
        $this->line('php artisan articles:master-punctuation-fix --phase=health');
        $this->line('');
        
        $this->info('🔄 FLUXO RECOMENDADO PARA PRODUÇÃO:');
        $this->line('1. Verificar saúde: --phase=health');
        $this->line('2. Teste com simulação: --phase=full --dry-run --limit=10');
        $this->line('3. Execução real por fases ou completa: --phase=full --progress');
        $this->line('4. Monitoramento contínuo: --phase=stats');
        $this->line('5. Manutenção periódica: --phase=maintenance');
        $this->line('');
        
        $this->info('🚨 RESOLUÇÃO DE PROBLEMAS:');
        $this->line('• Muitas falhas: Execute --phase=health --clean primeiro');
        $this->line('• Loop infinito: Use --force ou --template específico');
        $this->line('• Performance ruim: Reduza --limit e aumente --delay');
        $this->line('• Inconsistências: Execute --phase=maintenance');
        $this->line('• API errors: Verifique configuração Claude e rate limits');
        $this->line('');
        
        $this->info('💡 DICAS DE OTIMIZAÇÃO:');
        $this->line('• Use --batch-size menor para sistemas com pouca memória');
        $this->line('• Execute análises à noite para melhor performance');
        $this->line('• Monitore logs em storage/logs/punctuation-*.log');
        $this->line('• Configure schedule automático para execução regular');
        $this->line('• Use templates específicos para focar em problemas conhecidos');
    }
}