<?php

namespace App\ContentGeneration\WhenToChangeTires\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallWhenToChangeTiresCommand extends Command
{
    protected $signature = 'when-to-change-tires:install {--force : Sobrescrever arquivos existentes}';
    protected $description = 'Instalar módulo Quando Trocar Pneus';

    public function handle(): int
    {
        $this->info('🚀 Instalando módulo Quando Trocar Pneus...');

        try {
            // 1. Publicar configurações
            $this->info('📋 Publicando configurações...');
            Artisan::call('vendor:publish', [
                '--tag' => 'when-to-change-tires-config',
                '--force' => $this->option('force')
            ]);

            // 2. Publicar migrations
            $this->info('🗄️ Publicando migrations...');
            Artisan::call('vendor:publish', [
                '--tag' => 'when-to-change-tires-migrations',
                '--force' => $this->option('force')
            ]);

            // 3. Executar migrations
            if ($this->confirm('Deseja executar as migrations agora?', true)) {
                $this->info('⚡ Executando migrations...');
                Artisan::call('migrate');
                $this->line(Artisan::output());
            }

            // 4. Criar diretórios necessários
            $this->info('📁 Criando diretórios...');
            $this->createDirectories();

            // 5. Verificar CSV
            $this->info('📄 Verificando arquivo CSV...');
            $this->checkCsvFile();

            $this->info('✅ Instalação concluída com sucesso!');
            $this->line('');
            $this->info('📚 PRÓXIMOS PASSOS:');
            $this->line('1. Configure as variáveis no .env se necessário');
            $this->line('2. Coloque o arquivo todos_veiculos.csv na pasta storage/app/');
            $this->line('3. Execute: php artisan when-to-change-tires:import-vehicles --show-stats');
            $this->line('4. Execute: php artisan when-to-change-tires:generate-initial-articles --dry-run');

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Erro durante instalação: ' . $e->getMessage());
            return 1;
        }
    }

    protected function createDirectories(): void
    {
        $directories = [
            storage_path('app/articles'),
            storage_path('app/articles/when-to-change-tires'),
            storage_path('app/exports'),
            storage_path('app/exports/tire-articles'),
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
                $this->line("   ✅ Criado: {$dir}");
            } else {
                $this->line("   ⏭️ Já existe: {$dir}");
            }
        }
    }

    protected function checkCsvFile(): void
    {
        $csvPath = storage_path('app/todos_veiculos.csv');
        
        if (file_exists($csvPath)) {
            $lines = count(file($csvPath));
            $this->line("   ✅ CSV encontrado: {$lines} linhas");
        } else {
            $this->warn("   ⚠️ CSV não encontrado em: {$csvPath}");
            $this->line("   📥 Faça o upload do arquivo todos_veiculos.csv");
        }
    }
}
