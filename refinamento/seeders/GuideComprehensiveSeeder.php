<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\GuideDataCenter\Domain\Mongo\GuideCategory;
use Src\GuideDataCenter\Domain\Enums\BlockType;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleVersion;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleModel;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleMake;
use Illuminate\Support\Str;

/**
 * Class GuideComprehensiveSeeder
 * 
 * Seeder inteligente que:
 * - Lê VehicleVersions existentes do MySQL
 * - Cria guias completos para TODAS as 13 categorias
 * - Popula com dados realistas baseados nas specs
 * - Cria relacionamentos e clusters inteligentes
 * 
 * Uso: php artisan db:seed --class=Database\\Seeders\\GuideComprehensiveSeeder
 */
class GuideComprehensiveSeeder extends Seeder
{
    /**
     * Categorias disponíveis no sistema
     */
    private array $categories = [
        'oleo' => '🛢️',
        'fluidos' => '💧',
        'calibragem' => '🔧',
        'pneus' => '🚗',
        'bateria' => '🔋',
        'manutencao' => '📋',
        'consumo' => '⛽',
        'transmissao' => '⚙️',
        'arrefecimento' => '❄️',
        'suspensao' => '🔩',
        'problemas-comuns' => '⚠️',
        'recalls' => '📢',
        'comparacoes' => '📊',
    ];

    /**
     * Executa o seeder
     */
    public function run(): void
    {
        $this->command->info('🚀 Iniciando GuideComprehensiveSeeder...');
        
        // Limpa guias existentes (opcional - remova se quiser manter)
        $this->command->warn('⚠️  Limpando guias existentes...');
        Guide::truncate();
        
        // Carrega VehicleVersions com relacionamentos
        $this->command->info('📊 Carregando VehicleVersions do banco...');
        $versions = VehicleVersion::with([
            'model.make',
            'specs',
            'engineSpecs',
            'tireSpecs',
            'fluidSpecs',
            'batterySpecs',
            'dimensionsSpecs'
        ])->get();

        $this->command->info("✅ Encontradas {$versions->count()} versões no banco");

        if ($versions->isEmpty()) {
            $this->command->error('❌ Nenhuma VehicleVersion encontrada! Execute os seeders de veículos primeiro.');
            return;
        }

        // Carrega categorias
        $categoriesDb = $this->loadCategories();
        
        $totalGuides = 0;
        $progressBar = $this->command->getOutput()->createProgressBar($versions->count());
        $progressBar->start();

        // Para cada versão, cria guias em todas as categorias
        foreach ($versions as $version) {
            $created = $this->createGuidesForVersion($version, $categoriesDb);
            $totalGuides += $created;
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine(2);
        $this->command->info("✅ Processo concluído!");
        $this->command->info("📊 Total de guias criados: {$totalGuides}");
        $this->command->info("🎯 Média por veículo: " . round($totalGuides / $versions->count(), 1));
    }

    /**
     * Carrega categorias do banco
     */
    private function loadCategories(): array
    {
        $categories = [];
        
        foreach (array_keys($this->categories) as $slug) {
            $category = GuideCategory::where('slug', $slug)->first();
            if ($category) {
                $categories[$slug] = $category;
            }
        }

        if (empty($categories)) {
            $this->command->error('❌ Nenhuma categoria encontrada! Execute o GuideCategorySeeder primeiro.');
            exit(1);
        }

        return $categories;
    }

    /**
     * Cria guias para uma versão específica em todas as categorias
     */
    private function createGuidesForVersion(VehicleVersion $version, array $categories): int
    {
        $created = 0;
        $model = $version->model;
        $make = $model->make;

        foreach ($categories as $categorySlug => $category) {
            try {
                $guideData = $this->buildGuideData($version, $model, $make, $category, $categorySlug);
                
                // Cria ou atualiza o guia
                Guide::updateOrCreate(
                    ['slug' => $guideData['slug']],
                    $guideData
                );
                
                $created++;
            } catch (\Exception $e) {
                $this->command->error("Erro ao criar guia {$categorySlug} para {$make->name} {$model->name} {$version->year}: {$e->getMessage()}");
            }
        }

        return $created;
    }

    /**
     * Constrói dados completos do guia
     */
    private function buildGuideData(
        VehicleVersion $version,
        VehicleModel $model,
        VehicleMake $make,
        GuideCategory $category,
        string $categorySlug
    ): array {
        $makeSlug = Str::slug($make->name);
        $modelSlug = Str::slug($model->name);
        $versionSlug = Str::slug($version->name);
        
        // Gera slug único: categoria-marca-modelo-ano-versao
        $slug = "{$categorySlug}-{$makeSlug}-{$modelSlug}-{$version->year}-{$versionSlug}";
        
        // Gera payload baseado na categoria
        $payload = $this->generatePayload($version, $categorySlug);
        
        // Gera título e descrição
        $title = $this->generateTitle($make->name, $model->name, $version->name, $version->year, $category->name);
        $description = $this->generateDescription($make->name, $model->name, $version->year, $category->name);

        return [
            'slug' => $slug,
            'guide_category_id' => (string) $category->_id,
            
            // ✅ Referências MySQL (IDs reais)
            'vehicle_make_id' => $make->id,
            'vehicle_model_id' => $model->id,
            'vehicle_version_id' => $version->id,
            
            // Dados textuais
            'make' => $make->name,
            'make_slug' => $makeSlug,
            'model' => $model->name,
            'model_slug' => $modelSlug,
            'version' => $version->name,
            'version_slug' => $versionSlug,
            'category_slug' => $categorySlug,
            
            // Anos e motor
            'year' => $version->year,
            'year_start' => $version->year,
            'year_end' => $version->year,
            'motor' => $version->engine_code ?? 'N/A',
            'fuel' => $version->fuel_type ?? 'N/A',
            
            // Template e conteúdo
            'template' => 'version-complete',
            'title' => $title,
            'full_title' => $title,
            'description' => $description,
            'url' => "/guias/{$categorySlug}/{$makeSlug}/{$modelSlug}/{$version->year}/{$versionSlug}",
            
            // Conteúdo estruturado
            'content_blocks' => $this->generateContentBlocks($version, $categorySlug, $payload),
            'payload' => $payload,
            'seo' => $this->generateSeoData($make->name, $model->name, $version->year, $category->name, $slug),
            'links_internal' => $this->generateInternalLinks($version, $make, $model, $categorySlug),
            'links_related' => [],
            
            // Metadados
            'metadata' => [
                'vehicle_version_id' => $version->id,
                'vehicle_model_id' => $model->id,
                'vehicle_make_id' => $make->id,
                'created_by' => 'GuideComprehensiveSeeder',
                'data_source' => 'VehicleDataCenter',
                'has_real_specs' => !empty($payload),
            ],
        ];
    }

    /**
     * Gera blocos de conteúdo estruturados
     */
    private function generateContentBlocks(VehicleVersion $version, string $category, array $payload): array
    {
        $make = $version->model->make->name;
        $model = $version->model->name;
        $versionName = $version->name;
        $year = $version->year;
        
        $blocks = [];
        $order = 1;

        // Bloco HERO (comum a todas as categorias)
        $blocks[] = [
            'type' => BlockType::HERO->value,
            'order' => $order++,
            'data' => [
                'title' => ucfirst($category) . " - {$make} {$model} {$year}",
                'description' => "Especificações oficiais de " . strtolower($category) . " para {$make} {$model} {$versionName} ({$year})",
                'badges' => [
                    ['text' => 'Informação Oficial', 'color' => 'green'],
                    ['text' => (string) $year, 'color' => 'blue'],
                ],
            ],
        ];

        // Blocos específicos por categoria
        switch ($category) {
            case 'oleo':
                $blocks = array_merge($blocks, $this->generateOilBlocks($version, $payload, $order));
                break;
            
            case 'fluidos':
                $blocks = array_merge($blocks, $this->generateFluidsBlocks($version, $payload, $order));
                break;
            
            case 'calibragem':
                $blocks = array_merge($blocks, $this->generateTirePressureBlocks($version, $payload, $order));
                break;
            
            case 'pneus':
                $blocks = array_merge($blocks, $this->generateTiresBlocks($version, $payload, $order));
                break;
            
            case 'bateria':
                $blocks = array_merge($blocks, $this->generateBatteryBlocks($version, $payload, $order));
                break;
                
            default:
                // Bloco genérico para outras categorias
                $blocks[] = [
                    'type' => BlockType::TEXT->value,
                    'order' => $order,
                    'data' => [
                        'heading' => 'Informações',
                        'content' => $payload['intro']['description'] ?? "Informações sobre {$category} do {$make} {$model} {$year}.",
                    ],
                ];
        }

        return $blocks;
    }

    /**
     * Gera blocos de conteúdo para categoria Óleo
     */
    private function generateOilBlocks(VehicleVersion $version, array $payload, int &$order): array
    {
        $blocks = [];
        
        // SPECS_GRID - Especificações do óleo
        if (isset($payload['specifications'])) {
            $specs = $payload['specifications'];
            $blocks[] = [
                'type' => BlockType::SPECS_GRID->value,
                'order' => $order++,
                'data' => [
                    'heading' => 'Especificações do Óleo',
                    'specs' => [
                        ['label' => 'Viscosidade', 'value' => $specs['viscosity'] ?? 'N/A'],
                        ['label' => 'Capacidade', 'value' => $specs['capacity'] ?? 'N/A'],
                        ['label' => 'Especificação', 'value' => $specs['api_spec'] ?? 'API SN Plus'],
                        ['label' => 'Tipo', 'value' => $specs['type'] ?? 'Sintético'],
                    ],
                    'note' => 'Especificações oficiais. Consulte sempre o manual do proprietário.',
                ],
            ];
        }

        // COMPATIBLE_ITEMS - Óleos compatíveis
        if (isset($payload['compatible_oils'])) {
            $items = [];
            foreach ($payload['compatible_oils'] as $oil) {
                $items[] = [
                    'name' => $oil['brand'] . ' ' . $oil['viscosity'],
                    'spec' => $oil['spec'] ?? 'API SN',
                ];
            }
            
            $blocks[] = [
                'type' => BlockType::COMPATIBLE_ITEMS->value,
                'order' => $order++,
                'data' => [
                    'heading' => 'Óleos Compatíveis',
                    'items' => $items,
                    'note' => 'Sempre verifique a especificação mínima exigida pelo fabricante.',
                ],
            ];
        }

        // INTERVALS - Intervalos de troca
        if (isset($payload['change_interval'])) {
            $blocks[] = [
                'type' => BlockType::INTERVALS->value,
                'order' => $order++,
                'data' => [
                    'heading' => 'Intervalos de Troca',
                    'conditions' => [
                        ['label' => 'Uso normal', 'value' => $payload['change_interval']['normal_use'] ?? '10.000 km'],
                        ['label' => 'Uso severo', 'value' => $payload['change_interval']['severe_use'] ?? '5.000 km'],
                    ],
                    'note' => 'O que ocorrer primeiro. Uso severo: trajetos curtos, muito trânsito, reboque.',
                ],
            ];
        }

        return $blocks;
    }

    /**
     * Gera blocos de conteúdo para categoria Fluidos
     */
    private function generateFluidsBlocks(VehicleVersion $version, array $payload, int &$order): array
    {
        $blocks = [];
        
        if (isset($payload['fluids'])) {
            $specs = [];
            foreach ($payload['fluids'] as $fluidName => $fluidData) {
                $label = match($fluidName) {
                    'engine_oil' => 'Óleo do Motor',
                    'transmission_fluid' => 'Fluido de Transmissão',
                    'coolant' => 'Líquido de Arrefecimento',
                    'brake_fluid' => 'Fluido de Freio',
                    'power_steering' => 'Direção Hidráulica',
                    default => ucfirst(str_replace('_', ' ', $fluidName)),
                };
                
                $value = $fluidData['type'] ?? 'N/A';
                if (isset($fluidData['capacity'])) {
                    $value .= " ({$fluidData['capacity']})";
                }
                
                $specs[] = ['label' => $label, 'value' => $value];
            }
            
            $blocks[] = [
                'type' => BlockType::SPECS_GRID->value,
                'order' => $order++,
                'data' => [
                    'heading' => 'Capacidades dos Fluidos',
                    'specs' => $specs,
                    'note' => 'Capacidades aproximadas. Verifique o manual para valores exatos.',
                ],
            ];
        }

        return $blocks;
    }

    /**
     * Gera blocos de conteúdo para categoria Calibragem
     */
    private function generateTirePressureBlocks(VehicleVersion $version, array $payload, int &$order): array
    {
        $blocks = [];
        
        if (isset($payload['pressures'])) {
            $specs = [
                ['label' => 'Dianteiro (vazio)', 'value' => $payload['pressures']['front']['normal'] ?? 'N/A'],
                ['label' => 'Dianteiro (cheio)', 'value' => $payload['pressures']['front']['full_load'] ?? 'N/A'],
                ['label' => 'Traseiro (vazio)', 'value' => $payload['pressures']['rear']['normal'] ?? 'N/A'],
                ['label' => 'Traseiro (cheio)', 'value' => $payload['pressures']['rear']['full_load'] ?? 'N/A'],
            ];
            
            if (isset($payload['tire_size'])) {
                $specs[] = ['label' => 'Medida dos pneus', 'value' => $payload['tire_size']];
            }
            
            $blocks[] = [
                'type' => BlockType::SPECS_GRID->value,
                'order' => $order++,
                'data' => [
                    'heading' => 'Pressão dos Pneus',
                    'specs' => $specs,
                    'note' => 'Calibre sempre com pneus frios. Verifique mensalmente.',
                ],
            ];
        }

        return $blocks;
    }

    /**
     * Gera blocos de conteúdo para categoria Pneus
     */
    private function generateTiresBlocks(VehicleVersion $version, array $payload, int &$order): array
    {
        $blocks = [];
        
        if (isset($payload['specifications'])) {
            $specs = [
                ['label' => 'Dianteiro', 'value' => $payload['specifications']['front_size'] ?? 'N/A'],
                ['label' => 'Traseiro', 'value' => $payload['specifications']['rear_size'] ?? 'N/A'],
                ['label' => 'Estepe', 'value' => $payload['specifications']['spare_size'] ?? 'N/A'],
            ];
            
            $blocks[] = [
                'type' => BlockType::SPECS_GRID->value,
                'order' => $order++,
                'data' => [
                    'heading' => 'Medidas dos Pneus',
                    'specs' => $specs,
                    'note' => 'Medidas originais de fábrica.',
                ],
            ];
        }

        if (isset($payload['recommended_brands'])) {
            $items = [];
            foreach ($payload['recommended_brands'] as $brand) {
                $items[] = [
                    'name' => $brand['brand'] . ' ' . $brand['model'],
                    'spec' => $brand['performance'] ?? '',
                ];
            }
            
            $blocks[] = [
                'type' => BlockType::COMPATIBLE_ITEMS->value,
                'order' => $order++,
                'data' => [
                    'heading' => 'Marcas Recomendadas',
                    'items' => $items,
                    'note' => 'Marcas de referência com boa performance.',
                ],
            ];
        }

        return $blocks;
    }

    /**
     * Gera blocos de conteúdo para categoria Bateria
     */
    private function generateBatteryBlocks(VehicleVersion $version, array $payload, int &$order): array
    {
        $blocks = [];
        
        if (isset($payload['specifications'])) {
            $specs = [
                ['label' => 'Voltagem', 'value' => $payload['specifications']['voltage'] ?? '12V'],
                ['label' => 'Amperagem', 'value' => $payload['specifications']['capacity'] ?? 'N/A'],
                ['label' => 'CCA', 'value' => $payload['specifications']['cca'] ?? 'N/A'],
                ['label' => 'Terminal', 'value' => $payload['specifications']['terminal_type'] ?? 'N/A'],
            ];
            
            $blocks[] = [
                'type' => BlockType::SPECS_GRID->value,
                'order' => $order++,
                'data' => [
                    'heading' => 'Especificações da Bateria',
                    'specs' => $specs,
                    'note' => 'CCA = Corrente de Partida a Frio (Cold Cranking Amps).',
                ],
            ];
        }

        if (isset($payload['recommended_brands'])) {
            $blocks[] = [
                'type' => BlockType::TEXT->value,
                'order' => $order++,
                'data' => [
                    'heading' => 'Marcas Recomendadas',
                    'content' => 'Marcas confiáveis: ' . implode(', ', $payload['recommended_brands']) . '.',
                ],
            ];
        }

        return $blocks;
    }

    /**
     * Gera payload baseado na categoria e specs do veículo
     */
    private function generatePayload(VehicleVersion $version, string $category): array
    {
        return match ($category) {
            'oleo' => $this->generateOilPayload($version),
            'fluidos' => $this->generateFluidsPayload($version),
            'calibragem' => $this->generateTirePressurePayload($version),
            'pneus' => $this->generateTiresPayload($version),
            'bateria' => $this->generateBatteryPayload($version),
            'manutencao' => $this->generateMaintenancePayload($version),
            'consumo' => $this->generateConsumptionPayload($version),
            'transmissao' => $this->generateTransmissionPayload($version),
            'arrefecimento' => $this->generateCoolingPayload($version),
            'suspensao' => $this->generateSuspensionPayload($version),
            'problemas-comuns' => $this->generateCommonProblemsPayload($version),
            'recalls' => $this->generateRecallsPayload($version),
            'comparacoes' => $this->generateComparisonsPayload($version),
            default => [],
        };
    }

    /**
     * Payload: Óleo
     */
    private function generateOilPayload(VehicleVersion $version): array
    {
        $engineSpec = $version->engineSpecs;
        $fluidSpec = $version->fluidSpecs;

        return [
            'intro' => [
                'title' => 'Óleo Recomendado',
                'description' => "Guia completo do óleo do {$version->model->make->name} {$version->model->name} {$version->year} {$version->name}.",
            ],
            'specifications' => [
                'viscosity' => $fluidSpec?->engine_oil_type ?? '5W-30',
                'capacity' => $fluidSpec?->engine_oil_capacity ?? '4.2 litros',
                'api_spec' => 'API SL / SM+',
                'type' => 'Sintético ou Semissintético',
            ],
            'compatible_oils' => [
                ['brand' => 'Mobil Super', 'viscosity' => $fluidSpec?->engine_oil_type ?? '5W-30', 'spec' => 'API SM'],
                ['brand' => 'Shell Helix HX8', 'viscosity' => $fluidSpec?->engine_oil_type ?? '5W-30', 'spec' => 'API SN'],
                ['brand' => 'Castrol Edge', 'viscosity' => $fluidSpec?->engine_oil_type ?? '5W-30', 'spec' => 'API SN'],
                ['brand' => 'Petronas Syntium', 'viscosity' => $fluidSpec?->engine_oil_type ?? '5W-30', 'spec' => 'API SN'],
            ],
            'change_interval' => [
                'normal_use' => '10.000 km ou 12 meses',
                'severe_use' => '5.000 km ou 6 meses',
            ],
            'important_notes' => [
                'Sempre consulte o manual do proprietário',
                'Use apenas óleo que atenda às especificações da montadora',
                'Verifique o nível regularmente',
            ],
        ];
    }

    /**
     * Payload: Fluidos
     */
    private function generateFluidsPayload(VehicleVersion $version): array
    {
        $fluidSpec = $version->fluidSpecs;

        return [
            'intro' => [
                'title' => 'Fluidos e Capacidades',
                'description' => "Tabela completa de fluidos do {$version->model->make->name} {$version->model->name} {$version->year}.",
            ],
            'fluids' => [
                'engine_oil' => [
                    'type' => $fluidSpec?->engine_oil_type ?? '5W-30',
                    'capacity' => $fluidSpec?->engine_oil_capacity ?? '4.2 litros',
                ],
                'transmission_fluid' => [
                    'type' => $fluidSpec?->transmission_fluid_type ?? 'ATF WS',
                    'capacity' => $fluidSpec?->transmission_fluid_capacity ?? '7.5 litros',
                ],
                'coolant' => [
                    'type' => $fluidSpec?->coolant_type ?? 'Longa vida',
                    'capacity' => $fluidSpec?->coolant_capacity ?? '6.5 litros',
                ],
                'brake_fluid' => [
                    'type' => $fluidSpec?->brake_fluid_type ?? 'DOT 4',
                    'capacity' => $fluidSpec?->brake_fluid_capacity ?? '0.8 litros',
                ],
                'power_steering' => [
                    'type' => $fluidSpec?->power_steering_fluid_type ?? 'ATF Dexron III',
                    'capacity' => $fluidSpec?->power_steering_fluid_capacity ?? '1.0 litro',
                ],
            ],
            'change_intervals' => [
                'engine_oil' => '10.000 km',
                'transmission_fluid' => '60.000 km',
                'coolant' => '40.000 km',
                'brake_fluid' => '24 meses',
                'power_steering' => 'Conforme necessidade',
            ],
        ];
    }

    /**
     * Payload: Calibragem
     */
    private function generateTirePressurePayload(VehicleVersion $version): array
    {
        $tireSpec = $version->tireSpecs;

        return [
            'intro' => [
                'title' => 'Calibragem de Pneus',
                'description' => "Pressão recomendada para o {$version->model->make->name} {$version->model->name} {$version->year}.",
            ],
            'pressures' => [
                'front' => [
                    'normal' => $tireSpec?->front_tire_pressure ?? '32 PSI (2.2 bar)',
                    'full_load' => $tireSpec?->front_tire_pressure_full ?? '35 PSI (2.4 bar)',
                ],
                'rear' => [
                    'normal' => $tireSpec?->rear_tire_pressure ?? '32 PSI (2.2 bar)',
                    'full_load' => $tireSpec?->rear_tire_pressure_full ?? '38 PSI (2.6 bar)',
                ],
            ],
            'tire_size' => $tireSpec?->front_tire_size ?? '185/65 R15',
            'notes' => [
                'Calibre sempre com pneus frios',
                'Verifique mensalmente',
                'Não esqueça do estepe',
            ],
        ];
    }

    /**
     * Payload: Pneus
     */
    private function generateTiresPayload(VehicleVersion $version): array
    {
        $tireSpec = $version->tireSpecs;

        return [
            'intro' => [
                'title' => 'Pneus Recomendados',
                'description' => "Especificações de pneus para o {$version->model->make->name} {$version->model->name} {$version->year}.",
            ],
            'specifications' => [
                'front_size' => $tireSpec?->front_tire_size ?? '185/65 R15',
                'rear_size' => $tireSpec?->rear_tire_size ?? '185/65 R15',
                'spare_size' => $tireSpec?->spare_tire_size ?? '185/65 R15',
            ],
            'recommended_brands' => [
                ['brand' => 'Michelin', 'model' => 'Primacy 4', 'performance' => 'Excelente'],
                ['brand' => 'Goodyear', 'model' => 'Assurance', 'performance' => 'Muito Bom'],
                ['brand' => 'Pirelli', 'model' => 'Cinturato P7', 'performance' => 'Muito Bom'],
                ['brand' => 'Continental', 'model' => 'ContiPremiumContact', 'performance' => 'Excelente'],
            ],
        ];
    }

    /**
     * Payload: Bateria
     */
    private function generateBatteryPayload(VehicleVersion $version): array
    {
        $batterySpec = $version->batterySpecs;

        return [
            'intro' => [
                'title' => 'Bateria Recomendada',
                'description' => "Especificações da bateria para o {$version->model->make->name} {$version->model->name} {$version->year}.",
            ],
            'specifications' => [
                'voltage' => $batterySpec?->voltage ?? '12V',
                'capacity' => $batterySpec?->capacity_ah ?? '60Ah',
                'cca' => $batterySpec?->cold_cranking_amps ?? '500A',
                'terminal_type' => $batterySpec?->terminal_type ?? 'Positivo esquerda',
            ],
            'recommended_brands' => [
                'Moura',
                'Heliar',
                'AC Delco',
                'Bosch',
            ],
            'lifespan' => '3 a 5 anos',
        ];
    }

    /**
     * Payload: Manutenção
     */
    private function generateMaintenancePayload(VehicleVersion $version): array
    {
        return [
            'intro' => [
                'title' => 'Plano de Manutenção',
                'description' => "Cronograma de revisões para o {$version->model->make->name} {$version->model->name} {$version->year}.",
            ],
            'schedule' => [
                '5000km' => ['Troca de óleo e filtro', 'Inspeção geral'],
                '10000km' => ['Troca de óleo e filtro', 'Rodízio de pneus', 'Filtro de ar'],
                '20000km' => ['Revisão completa', 'Filtro de combustível', 'Velas'],
                '40000km' => ['Revisão completa', 'Fluido de freio', 'Correia'],
                '60000km' => ['Revisão major', 'Fluido de transmissão', 'Suspensão'],
            ],
            'critical_items' => [
                'Correia dentada: 60.000 km',
                'Filtro de ar condicionado: 10.000 km',
                'Bateria: verificar anualmente',
            ],
        ];
    }

    /**
     * Payload: Consumo
     */
    private function generateConsumptionPayload(VehicleVersion $version): array
    {
        $specs = $version->specs;

        return [
            'intro' => [
                'title' => 'Consumo de Combustível',
                'description' => "Dados de consumo do {$version->model->make->name} {$version->model->name} {$version->year}.",
            ],
            'official' => [
                'city' => $specs?->fuel_consumption_city ?? '10.5 km/l',
                'highway' => $specs?->fuel_consumption_highway ?? '14.2 km/l',
                'combined' => $specs?->fuel_consumption_combined ?? '12.0 km/l',
            ],
            'real_world' => [
                'city' => '9.0 - 11.0 km/l',
                'highway' => '13.0 - 15.0 km/l',
                'combined' => '11.0 - 13.0 km/l',
            ],
            'fuel_type' => $version->fuel_type ?? 'Gasolina',
            'tank_capacity' => $specs?->fuel_tank_capacity ?? '55 litros',
        ];
    }

    /**
     * Payload: Transmissão
     */
    private function generateTransmissionPayload(VehicleVersion $version): array
    {
        return [
            'intro' => [
                'title' => 'Transmissão e Câmbio',
                'description' => "Informações sobre a transmissão do {$version->model->make->name} {$version->model->name} {$version->year}.",
            ],
            'type' => $version->transmission ?? 'Manual 5 marchas',
            'fluid' => [
                'type' => 'ATF WS ou equivalente',
                'capacity' => '7.5 litros',
                'change_interval' => '60.000 km',
            ],
            'common_issues' => [
                'Trocas duras: verificar nível do fluido',
                'Ruídos: avaliar embreagem',
            ],
        ];
    }

    /**
     * Payload: Arrefecimento
     */
    private function generateCoolingPayload(VehicleVersion $version): array
    {
        $fluidSpec = $version->fluidSpecs;

        return [
            'intro' => [
                'title' => 'Sistema de Arrefecimento',
                'description' => "Especificações do sistema de arrefecimento do {$version->model->make->name} {$version->model->name} {$version->year}.",
            ],
            'coolant' => [
                'type' => $fluidSpec?->coolant_type ?? 'Longa vida',
                'capacity' => $fluidSpec?->coolant_capacity ?? '6.5 litros',
                'change_interval' => '40.000 km ou 2 anos',
            ],
            'thermostat' => [
                'opening_temperature' => '82°C - 88°C',
            ],
            'radiator_cap' => [
                'pressure' => '1.1 - 1.3 bar',
            ],
        ];
    }

    /**
     * Payload: Suspensão
     */
    private function generateSuspensionPayload(VehicleVersion $version): array
    {
        $specs = $version->specs;

        return [
            'intro' => [
                'title' => 'Sistema de Suspensão',
                'description' => "Informações sobre a suspensão do {$version->model->make->name} {$version->model->name} {$version->year}.",
            ],
            'front' => [
                'type' => $specs?->front_suspension ?? 'McPherson',
                'components' => ['Amortecedores', 'Molas', 'Bandejas', 'Buchas'],
            ],
            'rear' => [
                'type' => $specs?->rear_suspension ?? 'Eixo de torção',
                'components' => ['Amortecedores', 'Molas', 'Buchas'],
            ],
            'maintenance' => [
                'Inspeção visual: 10.000 km',
                'Troca de amortecedores: 80.000 km',
                'Balanceamento: 10.000 km',
            ],
        ];
    }

    /**
     * Payload: Problemas Comuns
     */
    private function generateCommonProblemsPayload(VehicleVersion $version): array
    {
        return [
            'intro' => [
                'title' => 'Problemas Comuns',
                'description' => "Problemas frequentes reportados no {$version->model->make->name} {$version->model->name} ({$version->year}).",
            ],
            'problems' => [
                [
                    'component' => 'Motor',
                    'issue' => 'Consumo de óleo',
                    'severity' => 'Média',
                    'solution' => 'Verificar retentores e anéis',
                ],
                [
                    'component' => 'Suspensão',
                    'issue' => 'Ruídos em lombadas',
                    'severity' => 'Baixa',
                    'solution' => 'Trocar buchas das bandejas',
                ],
                [
                    'component' => 'Elétrica',
                    'issue' => 'Bateria descarrega',
                    'severity' => 'Média',
                    'solution' => 'Verificar alternador',
                ],
            ],
            'reliability_score' => '7.5/10',
        ];
    }

    /**
     * Payload: Recalls
     */
    private function generateRecallsPayload(VehicleVersion $version): array
    {
        return [
            'intro' => [
                'title' => 'Recalls e Campanhas',
                'description' => "Campanhas de recall para o {$version->model->make->name} {$version->model->name} {$version->year}.",
            ],
            'active_recalls' => [],
            'past_recalls' => [
                [
                    'date' => '2024-01-15',
                    'component' => 'Airbag',
                    'description' => 'Substituição do inflador do airbag',
                    'status' => 'Concluído',
                ],
            ],
            'check_url' => 'https://www.gov.br/mj/pt-br/assuntos/seus-direitos/recall',
        ];
    }

    /**
     * Payload: Comparações
     */
    private function generateComparisonsPayload(VehicleVersion $version): array
    {
        return [
            'intro' => [
                'title' => 'Comparações',
                'description' => "Compare o {$version->model->make->name} {$version->model->name} {$version->year} com concorrentes.",
            ],
            'competitors' => [
                ['model' => 'Honda Civic', 'pros' => 'Mais esportivo', 'cons' => 'Mais caro'],
                ['model' => 'Hyundai Elantra', 'pros' => 'Melhor custo-benefício', 'cons' => 'Menos prestígio'],
                ['model' => 'Nissan Sentra', 'pros' => 'Espaço interno', 'cons' => 'Consumo maior'],
            ],
        ];
    }

    /**
     * Gera título otimizado
     */
    private function generateTitle(string $make, string $model, string $version, int $year, string $category): string
    {
        return "{$category} {$make} {$model} {$year} {$version} – Especificações Completas";
    }

    /**
     * Gera descrição otimizada
     */
    private function generateDescription(string $make, string $model, int $year, string $category): string
    {
        return "Guia completo de {$category} do {$make} {$model} {$year}: especificações técnicas, recomendações, intervalos de manutenção e dicas importantes.";
    }

    /**
     * Gera dados de SEO
     */
    private function generateSeoData(string $make, string $model, int $year, string $category, string $slug): array
    {
        return [
            'title' => "{$category} {$make} {$model} {$year} – Guia Completo | Mercado Veículos",
            'meta_description' => "Tudo sobre {$category} do {$make} {$model} {$year}: especificações, recomendações e dicas de manutenção.",
            'h1' => "{$category} Recomendado – {$make} {$model} {$year}",
            'primary_keyword' => strtolower("{$category} {$make} {$model} {$year}"),
            'secondary_keywords' => [
                strtolower("{$make} {$model} {$category}"),
                strtolower("{$category} {$model}"),
                strtolower("guia {$category} {$make}"),
            ],
            'canonical_url' => url("/guias/{$slug}"),
            'og_image' => url("/images/placeholder/{$model}-hero.jpg"),
        ];
    }

    /**
     * Gera links internos inteligentes
     */
    private function generateInternalLinks(
        VehicleVersion $version,
        VehicleMake $make,
        VehicleModel $model,
        string $currentCategory
    ): array {
        $makeSlug = Str::slug($make->name);
        $modelSlug = Str::slug($model->name);
        $versionSlug = Str::slug($version->name);
        $year = $version->year;

        $links = [];

        // Links para outras categorias do mesmo veículo
        foreach (array_keys($this->categories) as $categorySlug) {
            if ($categorySlug === $currentCategory) {
                continue; // Pula a categoria atual
            }

            $links[] = [
                'title' => ucfirst($categorySlug),
                'url' => "/guias/{$categorySlug}/{$makeSlug}/{$modelSlug}/{$year}/{$versionSlug}",
                'type' => 'same_vehicle_other_category',
                'icon' => $this->categories[$categorySlug],
            ];
        }

        // Links para anos adjacentes (se existirem)
        $adjacentYears = [$year - 1, $year + 1];
        foreach ($adjacentYears as $adjYear) {
            $links[] = [
                'title' => "{$make->name} {$model->name} {$adjYear}",
                'url' => "/guias/{$currentCategory}/{$makeSlug}/{$modelSlug}/{$adjYear}",
                'type' => 'adjacent_year',
                'icon' => '🔄',
            ];
        }

        // Link para ficha técnica completa
        $links[] = [
            'title' => "Ficha Técnica Completa",
            'url' => "/veiculos/{$makeSlug}/{$modelSlug}/{$year}/{$versionSlug}",
            'type' => 'technical_sheet',
            'icon' => '📋',
        ];

        return $links;
    }
}