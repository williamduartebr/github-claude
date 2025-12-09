<?php

namespace Src\VehicleDataCenter\Presentation\ViewModels;

/**
 * ViewModel para ficha técnica de uma versão específica - CORRIGIDO
 * 
 * ✅ BUSCA DADOS REAIS DO MYSQL
 * ✅ USA RELAÇÕES DO ELOQUENT
 * ✅ FALLBACKS PARA DADOS AUSENTES
 * 
 * Rota: /veiculos/{make}/{model}/{year}/{version}
 * View: vehicles.version
 * Exemplo: /veiculos/toyota/corolla/2023/gli-20
 */
class VehicleVersionViewModel
{
    private $version;
    private $specs;
    private $engineSpecs;
    private $fluidSpecs;
    private $tireSpecs;
    private $batterySpecs;
    private $dimensionsSpecs;

    public function __construct($version)
    {
        $this->version = $version;
        
        // Eager load all relationships
        $this->version->load([
            'model.make',
            'specs',
            'engineSpecs',
            'fluidSpecs',
            'tireSpecs',
            'batterySpecs',
            'dimensionsSpecs'
        ]);

        // Store specs for easy access
        $this->specs = $this->version->specs;
        $this->engineSpecs = $this->version->engineSpecs;
        $this->fluidSpecs = $this->version->fluidSpecs;
        $this->tireSpecs = $this->version->tireSpecs;
        $this->batterySpecs = $this->version->batterySpecs;
        $this->dimensionsSpecs = $this->version->dimensionsSpecs;
    }

    /**
     * Retorna dados completos da versão
     */
    public function getVersion(): array
    {
        return [
            'id' => $this->version->id,
            'name' => $this->version->name,
            'slug' => $this->version->slug,
            'year' => $this->version->year,
            'full_name' => $this->getFullName(),
            'description' => $this->getDescription(),
            'image' => $this->getImage(),
        ];
    }

    /**
     * Retorna dados da marca
     */
    public function getMake(): array
    {
        return [
            'id' => $this->version->model->make->id,
            'name' => $this->version->model->make->name,
            'slug' => $this->version->model->make->slug,
        ];
    }

    /**
     * Retorna dados do modelo
     */
    public function getModel(): array
    {
        return [
            'id' => $this->version->model->id,
            'name' => $this->version->model->name,
            'slug' => $this->version->model->slug,
        ];
    }

    /**
     * Retorna badges de qualidade
     */
    public function getBadges(): array
    {
        return [
            ['text' => 'Dados Verificados', 'color' => 'green', 'icon' => 'check'],
            ['text' => 'Especificações Oficiais', 'color' => 'blue', 'icon' => 'document'],
            ['text' => 'Atualizado 2025', 'color' => 'purple', 'icon' => 'refresh'],
        ];
    }

    /**
     * Retorna quick facts (4 infos rápidas)
     * ✅ AGORA COM DADOS REAIS DO MYSQL
     */
    public function getQuickFacts(): array
    {
        return [
            [
                'label' => 'Motor',
                'value' => $this->formatEngine()
            ],
            [
                'label' => 'Potência',
                'value' => $this->formatPower()
            ],
            [
                'label' => 'Transmissão',
                'value' => $this->formatTransmission()
            ],
            [
                'label' => 'Porta-malas',
                'value' => $this->formatTrunk()
            ],
        ];
    }

    /**
     * Retorna ficha técnica principal
     * ✅ AGORA COM DADOS REAIS DO MYSQL
     */
    public function getMainSpecs(): array
    {
        $specs = [];

        // Motor
        if ($this->engineSpecs) {
            $displacement = $this->engineSpecs->displacement_cc ?? null;
            $cylinders = $this->engineSpecs->cylinders ?? null;
            $fuelType = $this->version->fuel_type ?? 'N/A';
            
            $motorText = '';
            if ($displacement) {
                $motorText = number_format($displacement / 1000, 1) . 'L';
            }
            if ($cylinders) {
                $motorText .= ($motorText ? ' ' : '') . $cylinders . ' cilindros';
            }
            if ($fuelType !== 'N/A') {
                $motorText .= ($motorText ? ' • ' : '') . ucfirst($fuelType);
            }
            
            if ($motorText) {
                $specs[] = ['label' => 'Motor', 'value' => $motorText];
            }
        }

        // Potência
        if ($this->specs) {
            $powerHP = $this->specs->power_hp ?? null;
            $powerKW = $this->specs->power_kw ?? null;
            
            if ($powerHP || $powerKW) {
                $powerText = '';
                if ($powerHP) $powerText .= "{$powerHP} cv";
                if ($powerKW) $powerText .= ($powerText ? ' • ' : '') . "{$powerKW} kW";
                $specs[] = ['label' => 'Potência', 'value' => $powerText];
            }
        }

        // Torque
        if ($this->specs && $this->specs->torque_nm) {
            $torque = $this->specs->torque_nm;
            $torqueKgfm = round($torque / 9.80665, 1);
            $specs[] = ['label' => 'Torque', 'value' => "{$torque} Nm • {$torqueKgfm} kgf·m"];
        }

        // Transmissão
        $transmission = $this->formatTransmission();
        if ($transmission !== 'N/A') {
            $specs[] = ['label' => 'Transmissão', 'value' => $transmission];
        }

        // Combustível
        $fuelType = $this->version->fuel_type ?? null;
        if ($fuelType) {
            $fuelMap = [
                'gasoline' => 'Gasolina',
                'ethanol' => 'Etanol',
                'diesel' => 'Diesel',
                'flex' => 'Flex (Gasolina/Etanol)',
                'electric' => 'Elétrico',
                'hybrid' => 'Híbrido',
            ];
            $specs[] = ['label' => 'Combustível', 'value' => $fuelMap[$fuelType] ?? ucfirst($fuelType)];
        }

        // Peso
        if ($this->specs && $this->specs->weight_kg) {
            $specs[] = ['label' => 'Peso', 'value' => $this->specs->weight_kg . ' kg'];
        }

        // Porta-malas
        if ($this->specs && $this->specs->trunk_capacity_liters) {
            $specs[] = ['label' => 'Porta-malas', 'value' => $this->specs->trunk_capacity_liters . ' L'];
        }

        // Consumo médio
        if ($this->specs && $this->specs->fuel_consumption_mixed) {
            $specs[] = ['label' => 'Consumo médio', 'value' => $this->specs->fuel_consumption_mixed . ' km/l'];
        }

        // Tanque
        if ($this->specs && $this->specs->fuel_tank_capacity) {
            $specs[] = ['label' => 'Tanque', 'value' => $this->specs->fuel_tank_capacity . ' L'];
        }

        // Aceleração 0-100
        if ($this->specs && $this->specs->acceleration_0_100) {
            $specs[] = ['label' => 'Aceleração 0-100 km/h', 'value' => $this->specs->acceleration_0_100 . ' s'];
        }

        // Velocidade máxima
        if ($this->specs && $this->specs->top_speed_kmh) {
            $specs[] = ['label' => 'Velocidade máxima', 'value' => $this->specs->top_speed_kmh . ' km/h'];
        }

        return $specs;
    }

    /**
     * Retorna cards laterais (óleo, pneus, tanque)
     * ✅ AGORA COM DADOS REAIS DO MYSQL
     */
    public function getSideCards(): array
    {
        $cards = [];

        // Card 1: Óleo recomendado
        if ($this->fluidSpecs) {
            $oilType = $this->fluidSpecs->engine_oil_type ?? null;
            $oilCapacity = $this->fluidSpecs->engine_oil_capacity ?? null;
            
            if ($oilType || $oilCapacity) {
                $cards[] = [
                    'title' => 'Óleo recomendado',
                    'value' => $oilType ?? 'Consulte manual',
                    'extra' => $oilCapacity ? "Volume: {$oilCapacity} L" : null,
                ];
            }
        }

        // Card 2: Pneus originais
        if ($this->tireSpecs) {
            $frontTire = $this->tireSpecs->front_tire_size ?? null;
            $rearTire = $this->tireSpecs->rear_tire_size ?? null;
            
            if ($frontTire) {
                $cards[] = [
                    'title' => 'Pneus originais',
                    'value' => $frontTire,
                    'extra' => ($rearTire && $rearTire !== $frontTire) ? "Traseiro: {$rearTire}" : null,
                ];
            }
        }

        // Card 3: Tanque
        if ($this->specs && $this->specs->fuel_tank_capacity) {
            $cards[] = [
                'title' => 'Tanque',
                'value' => $this->specs->fuel_tank_capacity . ' L',
                'extra' => null,
            ];
        }

        // Se não tiver dados, retornar array vazio (melhor que dados mockados)
        return $cards;
    }

    /**
     * Retorna fluidos e capacidades
     * ✅ AGORA COM DADOS REAIS DO MYSQL
     */
    public function getFluids(): array
    {
        $fluids = [];

        if (!$this->fluidSpecs) {
            return []; // Sem dados, retorna vazio
        }

        // Óleo do motor
        if ($this->fluidSpecs->engine_oil_type || $this->fluidSpecs->engine_oil_capacity) {
            $oilValue = $this->fluidSpecs->engine_oil_type ?? 'Consulte manual';
            if ($this->fluidSpecs->engine_oil_capacity) {
                $oilValue .= " – {$this->fluidSpecs->engine_oil_capacity} L";
            }
            $fluids[] = ['emoji' => '💧', 'label' => 'Óleo do motor', 'value' => $oilValue];
        }

        // Fluido de freio
        if ($this->fluidSpecs->brake_fluid_type || $this->fluidSpecs->brake_fluid_capacity) {
            $brakeValue = $this->fluidSpecs->brake_fluid_type ?? 'DOT 3/4';
            if ($this->fluidSpecs->brake_fluid_capacity) {
                $brakeValue .= " – {$this->fluidSpecs->brake_fluid_capacity} L";
            }
            $fluids[] = ['emoji' => '🛑', 'label' => 'Fluido de freio', 'value' => $brakeValue];
        }

        // Arrefecimento
        if ($this->fluidSpecs->coolant_type || $this->fluidSpecs->coolant_capacity) {
            $coolantValue = $this->fluidSpecs->coolant_type ?? 'Etilenoglicol';
            if ($this->fluidSpecs->coolant_capacity) {
                $coolantValue .= " – {$this->fluidSpecs->coolant_capacity} L";
            }
            $fluids[] = ['emoji' => '❄️', 'label' => 'Arrefecimento', 'value' => $coolantValue];
        }

        // Óleo de câmbio
        if ($this->fluidSpecs->transmission_fluid_type || $this->fluidSpecs->transmission_fluid_capacity) {
            $transValue = $this->fluidSpecs->transmission_fluid_type ?? 'Consulte manual';
            if ($this->fluidSpecs->transmission_fluid_capacity) {
                $transValue .= " – {$this->fluidSpecs->transmission_fluid_capacity} L";
            }
            
            $transmission = $this->version->transmission ?? 'manual';
            $label = str_contains(strtolower($transmission), 'auto') ? 'Câmbio automático' : 'Câmbio manual';
            $emoji = str_contains(strtolower($transmission), 'auto') ? '🔄' : '⚙️';
            
            $fluids[] = ['emoji' => $emoji, 'label' => $label, 'value' => $transValue];
        }

        // Direção hidráulica
        if ($this->fluidSpecs->power_steering_fluid_type || $this->fluidSpecs->power_steering_fluid_capacity) {
            $psValue = $this->fluidSpecs->power_steering_fluid_type ?? 'ATF';
            if ($this->fluidSpecs->power_steering_fluid_capacity) {
                $psValue .= " – {$this->fluidSpecs->power_steering_fluid_capacity} L";
            }
            $fluids[] = ['emoji' => '🔧', 'label' => 'Direção hidráulica', 'value' => $psValue];
        }

        // Bateria
        if ($this->batterySpecs) {
            $batteryValue = '';
            if ($this->batterySpecs->capacity_ah) {
                $batteryValue = "{$this->batterySpecs->capacity_ah} Ah";
            }
            if ($this->batterySpecs->group_size) {
                $batteryValue .= ($batteryValue ? ' • ' : '') . $this->batterySpecs->group_size;
            }
            if ($batteryValue) {
                $fluids[] = ['emoji' => '🔋', 'label' => 'Bateria', 'value' => $batteryValue];
            }
        }

        return $fluids;
    }

    /**
     * Retorna resumo de manutenção
     * ✅ AGORA COM DADOS SEMI-REAIS (baseados em padrões da indústria)
     */
    public function getMaintenanceSummary(): array
    {
        // Manutenção básica segue padrões da indústria
        // Pode ser expandido para buscar dados específicos do fabricante no futuro
        
        return [
            ['km' => '10.000', 'items' => 'Óleo, filtro de óleo, inspeções gerais.'],
            ['km' => '20.000', 'items' => 'Óleo, filtros (ar/óleo/cabine), correias, fluidos.'],
            ['km' => '40.000', 'items' => 'Óleo, filtros, velas, alinhamento, balanceamento.'],
            ['km' => '60.000', 'items' => 'Revisão completa, troca de fluidos, inspeção de freios.'],
            ['km' => '80.000', 'items' => 'Óleo, filtros, bateria, correias, suspensão.'],
            ['km' => '100.000', 'items' => 'Revisão geral, troca de correia dentada (se aplicável).'],
        ];
    }

    /**
     * Retorna guias técnicos relacionados
     */
    public function getGuides(): array
    {
        $make = $this->version->model->make->slug;
        $model = $this->version->model->slug;
        $year = $this->version->year;

        return [
            ['emoji' => '🛢️', 'name' => 'Óleo Recomendado', 'url' => "/guias/oleo/{$make}/{$model}/{$year}"],
            ['emoji' => '🔧', 'name' => 'Calibragem', 'url' => "/guias/calibragem/{$make}/{$model}/{$year}"],
            ['emoji' => '🚗', 'name' => 'Pneus', 'url' => "/guias/pneus/{$make}/{$model}/{$year}"],
            ['emoji' => '📋', 'name' => 'Revisões', 'url' => "/guias/revisao/{$make}/{$model}/{$year}"],
            ['emoji' => '⚠️', 'name' => 'Problemas', 'url' => "/guias/problemas/{$make}/{$model}/{$year}"],
            ['emoji' => '⛽', 'name' => 'Consumo', 'url' => "/guias/consumo/{$make}/{$model}/{$year}"],
            ['emoji' => '🔋', 'name' => 'Bateria', 'url' => "/guias/bateria/{$make}/{$model}/{$year}"],
            ['emoji' => '🔄', 'name' => 'Câmbio', 'url' => "/guias/cambio/{$make}/{$model}/{$year}"],
        ];
    }

    /**
     * Retorna dados para SEO
     */
    public function getSeoData(): array
    {
        $fullName = $this->getFullName();
        
        return [
            'title' => "{$fullName} – Ficha Técnica Completa | Mercado Veículos",
            'description' => "Ficha técnica completa do {$fullName}: motor, potência, medidas, capacidades, fluidos, revisões e links para todos os guias técnicos (óleo, pneus, calibragem, manutenção, consumo, bateria e muito mais).",
            'canonical' => route('vehicles.version', [
                'make' => $this->version->model->make->slug,
                'model' => $this->version->model->slug,
                'year' => $this->version->year,
                'version' => $this->version->slug,
            ]),
            'og_image' => $this->getImage(),
        ];
    }

    /**
     * Retorna breadcrumbs
     */
    public function getBreadcrumbs(): array
    {
        $make = $this->version->model->make;
        $model = $this->version->model;
        
        return [
            ['name' => 'Início', 'url' => route('home')],
            ['name' => 'Veículos', 'url' => route('vehicles.index')],
            ['name' => $make->name, 'url' => route('vehicles.make', ['make' => $make->slug])],
            ['name' => $model->name, 'url' => route('vehicles.model', ['make' => $make->slug, 'model' => $model->slug])],
            ['name' => "{$this->version->name} {$this->version->year}", 'url' => null],
        ];
    }

    // ================================================================
    // MÉTODOS PRIVADOS DE FORMATAÇÃO
    // ================================================================

    /**
     * Formata informações do motor
     */
    private function formatEngine(): string
    {
        if (!$this->engineSpecs) {
            return $this->version->engine_code ?? 'N/A';
        }

        $parts = [];
        
        // Displacement
        if ($this->engineSpecs->displacement_cc) {
            $liters = number_format($this->engineSpecs->displacement_cc / 1000, 1);
            $parts[] = "{$liters}L";
        }
        
        // Cylinders
        if ($this->engineSpecs->cylinders) {
            $parts[] = "{$this->engineSpecs->cylinders} cilindros";
        }
        
        // Engine type
        if ($this->engineSpecs->engine_type) {
            $types = [
                'inline' => 'Em linha',
                'v' => 'V',
                'boxer' => 'Boxer',
                'rotary' => 'Rotativo',
            ];
            $type = $types[strtolower($this->engineSpecs->engine_type)] ?? $this->engineSpecs->engine_type;
            if (!in_array($type, $parts)) {
                $parts[] = $type;
            }
        }

        return implode(' • ', $parts) ?: ($this->version->engine_code ?? 'N/A');
    }

    /**
     * Formata potência
     */
    private function formatPower(): string
    {
        if (!$this->specs) {
            return 'N/A';
        }

        $parts = [];
        
        if ($this->specs->power_hp) {
            $parts[] = "{$this->specs->power_hp} cv";
        }
        
        if ($this->specs->power_kw) {
            $parts[] = "{$this->specs->power_kw} kW";
        }

        return implode(' • ', $parts) ?: 'N/A';
    }

    /**
     * Formata transmissão
     */
    private function formatTransmission(): string
    {
        $transmission = $this->version->transmission ?? null;
        
        if (!$transmission) {
            return 'N/A';
        }

        $map = [
            'manual' => 'Manual',
            'automatic' => 'Automática',
            'cvt' => 'CVT',
            'dct' => 'DCT',
            'amt' => 'Automatizada',
        ];

        return $map[strtolower($transmission)] ?? ucfirst($transmission);
    }

    /**
     * Formata capacidade do porta-malas
     */
    private function formatTrunk(): string
    {
        if ($this->specs && $this->specs->trunk_capacity_liters) {
            return $this->specs->trunk_capacity_liters . ' L';
        }

        return 'N/A';
    }

    /**
     * Nome completo da versão
     */
    private function getFullName(): string
    {
        return "{$this->version->model->make->name} {$this->version->model->name} {$this->version->name} {$this->version->year}";
    }

    /**
     * Descrição da versão
     */
    private function getDescription(): string
    {
        $fullName = $this->getFullName();
        return "Ficha técnica completa do {$fullName}, incluindo motor, potência, dimensões, capacidades, fluidos e manutenção. Acesse também os guias completos de óleo, pneus, calibragem, consumo e muito mais.";
    }

    /**
     * URL da imagem
     */
    private function getImage(): string
    {
        // TODO: Implementar lógica de imagem real
        $make = $this->version->model->make->slug;
        $model = $this->version->model->slug;
        $year = $this->version->year;
        $version = $this->version->slug;
        
        return "/images/vehicles/{$make}/{$model}/{$year}/{$version}/hero.jpg";
    }
}