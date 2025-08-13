{{-- 
Partial: tire-pressure/amp/car/vehicle-data.blade.php
Dados principais do veículo para carros - AMP
Versão AMP com informações técnicas e localização da pressão
--}}

@php
    $vehicleInfo = $article->getData()['vehicle_info'] ?? [];
    $labelLocation = $article->getData()['label_location'] ?? [];
    $informationLocation = $article->getData()['information_location'] ?? [];
    $carCategory = $vehicleInfo['category'] ?? 'sedan';
    $hasTpms = $article->getData()['has_tpms'] ?? false;
@endphp

@if(!empty($vehicleInfo))
<section class="car-vehicle-data-section">
    <h2 class="section-title">🚗 Dados do Veículo</h2>
    
    <!-- Informações Principais -->
    <div class="main-vehicle-info">
        <div class="vehicle-card">
            <div class="vehicle-header">
                <div class="vehicle-icon-large">🚗</div>
                <div class="vehicle-details">
                    <div class="vehicle-name">{{ $vehicleInfo['full_name'] ?? 'Veículo' }}</div>
                    @if(!empty($vehicleInfo['year_range']))
                    <div class="vehicle-years">{{ $vehicleInfo['year_range'] }}</div>
                    @endif
                    @if(!empty($vehicleInfo['engine_size']))
                    <div class="vehicle-engine">{{ $vehicleInfo['engine_size'] }}</div>
                    @endif
                </div>
                <div class="category-badge-large">
                    @if($carCategory === 'hatchback')
                    <span class="badge hatchback">🚙 Hatchback</span>
                    @elseif($carCategory === 'sedan')
                    <span class="badge sedan">🚘 Sedan</span>
                    @elseif($carCategory === 'suv')
                    <span class="badge suv">🚙 SUV</span>
                    @elseif($carCategory === 'pickup')
                    <span class="badge pickup">🚛 Pick-up</span>
                    @elseif($carCategory === 'wagon')
                    <span class="badge wagon">🚗 Wagon</span>
                    @elseif($carCategory === 'crossover')
                    <span class="badge crossover">🚙 Crossover</span>
                    @elseif($carCategory === 'sport')
                    <span class="badge sport">🏎️ Esportivo</span>
                    @else
                    <span class="badge standard">🚗 Padrão</span>
                    @endif
                </div>
            </div>
            
            <!-- TPMS Indicator -->
            @if($hasTpms)
            <div class="tpms-indicator">
                <div class="tpms-icon">📡</div>
                <div class="tpms-text">
                    <span class="tpms-label">TPMS Equipado</span>
                    <span class="tpms-desc">Sistema de monitoramento automático</span>
                </div>
            </div>
            @endif
        </div>
    </div>
    
    <!-- Pressões Recomendadas -->
    <div class="pressure-summary-card">
        <div class="summary-header">
            <span class="summary-icon">📊</span>
            <span class="summary-title">Pressões Recomendadas</span>
        </div>
        <div class="pressure-data">
            <!-- Uso Normal -->
            <div class="pressure-row normal">
                <div class="situation-label">
                    <span class="situation-icon">👥</span>
                    <span class="situation-text">Uso Normal</span>
                </div>
                <div class="pressure-values">
                    <div class="pressure-front">
                        <span class="pressure-label">Dianteiro</span>
                        <span class="pressure-value">{{ $vehicleInfo['pressure_front'] ?? 'Consulte manual' }}</span>
                    </div>
                    <div class="pressure-rear">
                        <span class="pressure-label">Traseiro</span>
                        <span class="pressure-value">{{ $vehicleInfo['pressure_rear'] ?? 'Consulte manual' }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Carga Completa -->
            @if(!empty($vehicleInfo['pressure_front_loaded']) || !empty($vehicleInfo['pressure_rear_loaded']))
            <div class="pressure-row loaded">
                <div class="situation-label">
                    <span class="situation-icon">🧳</span>
                    <span class="situation-text">Carga Completa</span>
                </div>
                <div class="pressure-values">
                    <div class="pressure-front">
                        <span class="pressure-label">Dianteiro</span>
                        <span class="pressure-value">{{ $vehicleInfo['pressure_front_loaded'] ?? $vehicleInfo['pressure_front'] ?? 'N/A' }}</span>
                    </div>
                    <div class="pressure-rear">
                        <span class="pressure-label">Traseiro</span>
                        <span class="pressure-value">{{ $vehicleInfo['pressure_rear_loaded'] ?? $vehicleInfo['pressure_rear'] ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Estepe -->
            @if(!empty($vehicleInfo['spare_tire_pressure']))
            <div class="pressure-row spare">
                <div class="situation-label">
                    <span class="situation-icon">⭕</span>
                    <span class="situation-text">Estepe</span>
                </div>
                <div class="pressure-values">
                    <div class="pressure-spare">
                        <span class="pressure-label">Temporário</span>
                        <span class="pressure-value">{{ $vehicleInfo['spare_tire_pressure'] }}</span>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    
    <!-- Onde Encontrar a Informação -->
    @if(!empty($labelLocation) || !empty($informationLocation))
    <div class="location-info">
        <h3 class="location-title">📍 Onde Encontrar a Pressão Correta</h3>
        <div class="location-options">
            @if(!empty($labelLocation['main_location']) || !empty($informationLocation['primary']))
            <div class="location-card primary">
                <div class="location-header">
                    <span class="location-icon">🏷️</span>
                    <span class="location-name">ETIQUETA PRINCIPAL</span>
                </div>
                <div class="location-description">
                    {{ $labelLocation['main_location'] ?? $informationLocation['primary'] ?? 'Batente da porta do motorista ou manual do proprietário' }}
                </div>
                <div class="location-note">Informação mais confiável</div>
            </div>
            @endif
            
            @if(!empty($labelLocation['alternative_locations']) || !empty($informationLocation['alternatives']))
            @php
                $alternatives = $labelLocation['alternative_locations'] ?? $informationLocation['alternatives'] ?? [];
                if (is_string($alternatives)) $alternatives = [$alternatives];
                $carAlternatives = [
                    'Tampa do tanque de combustível',
                    'Porta-luvas',
                    'Console central',
                    'Manual do proprietário'
                ];
                $alternatives = array_merge($alternatives, $carAlternatives);
                $alternatives = array_unique(array_slice($alternatives, 0, 3));
            @endphp
            @foreach($alternatives as $index => $location)
            <div class="location-card alternative">
                <div class="location-header">
                    <span class="location-icon">📋</span>
                    <span class="location-name">ALTERNATIVA {{ $index + 1 }}</span>
                </div>
                <div class="location-description">{{ $location }}</div>
                <div class="location-note">Verificar se disponível</div>
            </div>
            @endforeach
            @endif
        </div>
        
        @if(!empty($labelLocation['note']) || !empty($informationLocation['note']))
        <div class="location-tip">
            <div class="tip-header">
                <span class="tip-icon">💡</span>
                <span class="tip-text">Dica:</span>
            </div>
            <div class="tip-content">
                {{ $labelLocation['note'] ?? $informationLocation['note'] }}
            </div>
        </div>
        @endif
    </div>
    @endif
    
    <!-- Especificações Técnicas -->
    <div class="technical-specs">
        <h3 class="specs-title">🔧 Especificações Técnicas</h3>
        <div class="specs-grid">
            @if(!empty($vehicleInfo['model_name']))
            <div class="spec-item">
                <div class="spec-label">Modelo</div>
                <div class="spec-value">{{ $vehicleInfo['model_name'] }}</div>
            </div>
            @endif
            
            @if(!empty($vehicleInfo['engine_size']))
            <div class="spec-item">
                <div class="spec-label">Motor</div>
                <div class="spec-value">{{ $vehicleInfo['engine_size'] }}</div>
            </div>
            @endif
            
            @if(!empty($vehicleInfo['year_range']))
            <div class="spec-item">
                <div class="spec-label">Anos</div>
                <div class="spec-value">{{ $vehicleInfo['year_range'] }}</div>
            </div>
            @endif
            
            @if(!empty($vehicleInfo['fuel_type']))
            <div class="spec-item">
                <div class="spec-label">Combustível</div>
                <div class="spec-value">{{ $vehicleInfo['fuel_type'] }}</div>
            </div>
            @endif
            
            @if(!empty($vehicleInfo['transmission']))
            <div class="spec-item">
                <div class="spec-label">Câmbio</div>
                <div class="spec-value">{{ $vehicleInfo['transmission'] }}</div>
            </div>
            @endif
            
            @if(!empty($vehicleInfo['doors']))
            <div class="spec-item">
                <div class="spec-label">Portas</div>
                <div class="spec-value">{{ $vehicleInfo['doors'] }}</div>
            </div>
            @endif
            
            @if(!empty($vehicleInfo['seats']))
            <div class="spec-item">
                <div class="spec-label">Lugares</div>
                <div class="spec-value">{{ $vehicleInfo['seats'] }}</div>
            </div>
            @endif
            
            @if(!empty($vehicleInfo['trunk_capacity']))
            <div class="spec-item">
                <div class="spec-label">Porta-malas</div>
                <div class="spec-value">{{ $vehicleInfo['trunk_capacity'] }}</div>
            </div>
            @endif
        </div>
    </div>
    
    <!-- Características por Categoria -->
    <div class="category-characteristics">
        <h4 class="char-title">🎯 Características da Categoria</h4>
        @if($carCategory === 'hatchback')
        <div class="char-card hatchback">
            <div class="char-header">
                <span class="char-icon">🚙</span>
                <span class="char-name">Hatchback</span>
            </div>
            <div class="char-features">
                <div class="feature-item">• Ideal para uso urbano</div>
                <div class="feature-item">• Economia de combustível</div>
                <div class="feature-item">• Facilidade de estacionamento</div>
                <div class="feature-item">• Pressões padrão adequadas</div>
                <div class="feature-item">• Verificação mensal suficiente</div>
            </div>
        </div>
        @elseif($carCategory === 'sedan')
        <div class="char-card sedan">
            <div class="char-header">
                <span class="char-icon">🚘</span>
                <span class="char-name">Sedan</span>
            </div>
            <div class="char-features">
                <div class="feature-item">• Conforto para viagens</div>
                <div class="feature-item">• Estabilidade em velocidade</div>
                <div class="feature-item">• Porta-malas amplo</div>
                <div class="feature-item">• Ajustar pressão para carga</div>
                <div class="feature-item">• Verificar antes de viagens</div>
            </div>
        </div>
        @elseif($carCategory === 'suv')
        <div class="char-card suv">
            <div class="char-header">
                <span class="char-icon">🚙</span>
                <span class="char-name">SUV</span>
            </div>
            <div class="char-features">
                <div class="feature-item">• Maior capacidade de carga</div>
                <div class="feature-item">• Uso misto urbano/estrada</div>
                <div class="feature-item">• Pressões mais altas necessárias</div>
                <div class="feature-item">• Verificação frequente importante</div>
                <div class="feature-item">• Consumo afetado pela pressão</div>
            </div>
        </div>
        @elseif($carCategory === 'pickup')
        <div class="char-card pickup">
            <div class="char-header">
                <span class="char-icon">🚛</span>
                <span class="char-name">Pick-up</span>
            </div>
            <div class="char-features">
                <div class="feature-item">• Capacidade de carga variável</div>
                <div class="feature-item">• Ajustar pressão conforme peso</div>
                <div class="feature-item">• Diferença significativa vazio/carregado</div>
                <div class="feature-item">• Pneus traseiros críticos</div>
                <div class="feature-item">• Verificação antes do trabalho</div>
            </div>
        </div>
        @elseif($carCategory === 'sport')
        <div class="char-card sport">
            <div class="char-header">
                <span class="char-icon">🏎️</span>
                <span class="char-name">Esportivo</span>
            </div>
            <div class="char-features">
                <div class="feature-item">• Performance prioritária</div>
                <div class="feature-item">• Pressões específicas por uso</div>
                <div class="feature-item">• Pneus de alta performance</div>
                <div class="feature-item">• Verificação antes de cada saída</div>
                <div class="feature-item">• Sensibilidade às variações</div>
            </div>
        </div>
        @else
        <div class="char-card standard">
            <div class="char-header">
                <span class="char-icon">🚗</span>
                <span class="char-name">Padrão</span>
            </div>
            <div class="char-features">
                <div class="feature-item">• Uso familiar balanceado</div>
                <div class="feature-item">• Pressões conforme manual</div>
                <div class="feature-item">• Manutenção regular</div>
                <div class="feature-item">• Economia como prioridade</div>
                <div class="feature-item">• Verificação mensal</div>
            </div>
        </div>
        @endif
    </div>
    
    <!-- Sistemas Especiais -->
    @if($hasTpms || !empty($vehicleInfo['special_systems']))
    <div class="special-systems">
        <h4 class="systems-title">⚙️ Sistemas Especiais</h4>
        <div class="systems-grid">
            @if($hasTpms)
            <div class="system-card tpms">
                <div class="system-icon">📡</div>
                <div class="system-content">
                    <div class="system-name">TPMS</div>
                    <div class="system-desc">Sistema de monitoramento de pressão dos pneus</div>
                    <div class="system-note">Alerta automático no painel</div>
                </div>
            </div>
            @endif
            
            @if(!empty($vehicleInfo['has_abs']))
            <div class="system-card abs">
                <div class="system-icon">🛡️</div>
                <div class="system-content">
                    <div class="system-name">ABS</div>
                    <div class="system-desc">Sistema antibloqueio de freios</div>
                    <div class="system-note">Sensível à pressão dos pneus</div>
                </div>
            </div>
            @endif
            
            @if(!empty($vehicleInfo['has_esp']))
            <div class="system-card esp">
                <div class="system-icon">🎯</div>
                <div class="system-content">
                    <div class="system-name">ESP/ESC</div>
                    <div class="system-desc">Controle eletrônico de estabilidade</div>
                    <div class="system-note">Requer pressão correta</div>
                </div>
            </div>
            @endif
            
            @if(!empty($vehicleInfo['has_awd']))
            <div class="system-card awd">
                <div class="system-icon">🔄</div>
                <div class="system-content">
                    <div class="system-name">AWD/4WD</div>
                    <div class="system-desc">Tração integral</div>
                    <div class="system-note">Pressão uniforme essencial</div>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif
    
    <!-- Informações Importantes -->
    <div class="important-info">
        <h4 class="info-title">⚠️ Informações Importantes</h4>
        <div class="info-cards">
            <div class="info-card manual">
                <div class="info-icon">📖</div>
                <div class="info-content">
                    <div class="info-name">Manual do Proprietário</div>
                    <div class="info-desc">Sempre consulte para informações específicas da sua versão</div>
                </div>
            </div>
            
            <div class="info-card load">
                <div class="info-icon">⚖️</div>
                <div class="info-content">
                    <div class="info-name">Variação de Carga</div>
                    <div class="info-desc">Ajuste conforme número de passageiros e bagagem</div>
                </div>
            </div>
            
            <div class="info-card spare">
                <div class="info-icon">⭕</div>
                <div class="info-content">
                    <div class="info-name">Estepe</div>
                    <div class="info-desc">Pressão mais alta, velocidade limitada a 80 km/h</div>
                </div>
            </div>
            
            <div class="info-card maintenance">
                <div class="info-icon">🔧</div>
                <div class="info-content">
                    <div class="info-name">Verificação Mensal</div>
                    <div class="info-desc">Inclua o estepe na verificação regular</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Nota de Responsabilidade -->
    <div class="responsibility-note">
        <div class="note-header">
            <span class="note-icon">📋</span>
            <span class="note-title">Nota Importante</span>
        </div>
        <div class="note-content">
            As informações apresentadas são baseadas em dados técnicos padrão para esta categoria de veículo. 
            <strong>Sempre consulte o manual específico do seu carro</strong> e a etiqueta oficial para obter os valores 
            exatos recomendados pelo fabricante. Para veículos com TPMS, resete o sistema após calibrar.
        </div>
    </div>
</section>
@endif

<style>
/* Seção principal */
.car-vehicle-data-section {
    margin: 24px 0;
    padding: 20px;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    border-radius: 16px;
    border: 2px solid #2563eb;
}

.section-title {
    font-size: 20px;
    font-weight: 700;
    color: #1e40af;
    margin-bottom: 20px;
    text-align: center;
    border-bottom: 3px solid #2563eb;
    padding-bottom: 8px;
}

/* Informações principais do veículo */
.main-vehicle-info {
    margin-bottom: 24px;
}

.vehicle-card {
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.vehicle-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

.vehicle-icon-large {
    font-size: 32px;
    flex-shrink: 0;
}

.vehicle-details {
    flex: 1;
}

.vehicle-name {
    font-size: 18px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 4px;
}

.vehicle-years {
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 2px;
}

.vehicle-engine {
    font-size: 12px;
    color: #2563eb;
    font-weight: 600;
}

.category-badge-large {
    flex-shrink: 0;
}

.badge {
    font-size: 11px;
    font-weight: 600;
    padding: 6px 10px;
    border-radius: 12px;
    color: white;
}

.badge.hatchback {
    background: linear-gradient(135deg, #16a34a, #15803d);
}

.badge.sedan {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
}

.badge.suv {
    background: linear-gradient(135deg, #7c3aed, #6d28d9);
}

.badge.pickup {
    background: linear-gradient(135deg, #f59e0b, #d97706);
}

.badge.wagon {
    background: linear-gradient(135deg, #0ea5e9, #0284c7);
}

.badge.crossover {
    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
}

.badge.sport {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
}

.badge.standard {
    background: linear-gradient(135deg, #64748b, #475569);
}

/* Indicador TPMS */
.tpms-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: linear-gradient(135deg, #ecfdf5, #d1fae5);
    border-radius: 6px;
    border: 1px solid #16a34a;
}

.tpms-icon {
    font-size: 16px;
    color: #16a34a;
}

.tpms-text {
    display: flex;
    flex-direction: column;
}

.tpms-label {
    font-size: 11px;
    font-weight: 600;
    color: #166534;
}

.tpms-desc {
    font-size: 9px;
    color: #15803d;
}

/* Resumo de pressões */
.pressure-summary-card {
    margin-bottom: 24px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 2px solid #16a34a;
}

.summary-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
    justify-content: center;
}

.summary-icon {
    font-size: 18px;
    color: #16a34a;
}

.summary-title {
    font-size: 16px;
    font-weight: 600;
    color: #166534;
}

.pressure-data {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.pressure-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border-radius: 8px;
}

.pressure-row.normal {
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
}

.pressure-row.loaded {
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
}

.pressure-row.spare {
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
}

.situation-label {
    display: flex;
    align-items: center;
    gap: 6px;
    min-width: 90px;
}

.situation-icon {
    font-size: 16px;
}

.situation-text {
    font-size: 12px;
    font-weight: 600;
    color: #374151;
}

.pressure-values {
    display: flex;
    gap: 16px;
    flex: 1;
}

.pressure-front,
.pressure-rear,
.pressure-spare {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
}

.pressure-label {
    font-size: 10px;
    color: #6b7280;
    margin-bottom: 2px;
}

.pressure-value {
    font-size: 14px;
    font-weight: 700;
    color: #1f2937;
}

/* Localização da informação */
.location-info {
    margin-bottom: 24px;
}

.location-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.location-options {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
    margin-bottom: 12px;
}

.location-card {
    background: white;
    border-radius: 8px;
    padding: 12px;
    border: 2px solid;
}

.location-card.primary {
    border-color: #dc2626;
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
}

.location-card.alternative {
    border-color: #3b82f6;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
}

.location-header {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 6px;
}

.location-icon {
    font-size: 14px;
}

.location-name {
    font-size: 11px;
    font-weight: 700;
    color: #374151;
}

.location-description {
    font-size: 11px;
    color: #4b5563;
    margin-bottom: 4px;
    line-height: 1.3;
}

.location-note {
    font-size: 9px;
    color: #6b7280;
    font-style: italic;
}

.location-tip {
    background: white;
    border-radius: 8px;
    padding: 12px;
    border: 1px solid #f59e0b;
}

.tip-header {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 6px;
}

.tip-icon {
    font-size: 14px;
    color: #f59e0b;
}

.tip-text {
    font-size: 12px;
    font-weight: 600;
    color: #92400e;
}

.tip-content {
    font-size: 11px;
    color: #451a03;
    line-height: 1.3;
}

/* Especificações técnicas */
.technical-specs {
    margin-bottom: 24px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #e5e7eb;
}

.specs-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.specs-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.spec-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px;
    background: #f8fafc;
    border-radius: 6px;
}

.spec-label {
    font-size: 11px;
    color: #6b7280;
    font-weight: 500;
}

.spec-value {
    font-size: 11px;
    color: #1f2937;
    font-weight: 600;
}

/* Características por categoria */
.category-characteristics {
    margin-bottom: 24px;
}

.char-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.char-card {
    background: white;
    border-radius: 10px;
    padding: 14px;
    border: 2px solid;
}

.char-card.hatchback {
    border-color: #16a34a;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
}

.char-card.sedan {
    border-color: #2563eb;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
}

.char-card.suv {
    border-color: #7c3aed;
    background: linear-gradient(135deg, #f5f3ff, #ede9fe);
}

.char-card.pickup {
    border-color: #f59e0b;
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
}

.char-card.wagon {
    border-color: #0ea5e9;
    background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
}

.char-card.crossover {
    border-color: #8b5cf6;
    background: linear-gradient(135deg, #faf5ff, #f3e8ff);
}

.char-card.sport {
    border-color: #dc2626;
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
}

.char-card.standard {
    border-color: #64748b;
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
}

.char-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
}

.char-icon {
    font-size: 16px;
}

.char-name {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
}

.char-features {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.feature-item {
    font-size: 11px;
    color: #4b5563;
    line-height: 1.3;
}

/* Sistemas especiais */
.special-systems {
    margin-bottom: 24px;
}

.systems-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.systems-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.system-card {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px;
    background: white;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
}

.system-card.tpms {
    background: linear-gradient(135deg, #ecfdf5, #d1fae5);
    border-color: #16a34a;
}

.system-card.abs {
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    border-color: #3b82f6;
}

.system-card.esp {
    background: linear-gradient(135deg, #f5f3ff, #ede9fe);
    border-color: #7c3aed;
}

.system-card.awd {
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
    border-color: #f59e0b;
}

.system-icon {
    font-size: 16px;
    flex-shrink: 0;
}

.system-content {
    flex: 1;
}

.system-name {
    font-size: 11px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 2px;
}

.system-desc {
    font-size: 9px;
    color: #6b7280;
    margin-bottom: 2px;
    line-height: 1.2;
}

.system-note {
    font-size: 8px;
    color: #374151;
    font-weight: 500;
}

/* Informações importantes */
.important-info {
    margin-bottom: 24px;
}

.info-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.info-cards {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.info-card {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px;
    background: white;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
}

.info-icon {
    font-size: 16px;
    color: #2563eb;
    flex-shrink: 0;
}

.info-content {
    flex: 1;
}

.info-name {
    font-size: 11px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 2px;
}

.info-desc {
    font-size: 9px;
    color: #6b7280;
    line-height: 1.3;
}

/* Nota de responsabilidade */
.responsibility-note {
    background: white;
    border-radius: 10px;
    padding: 14px;
    border: 2px solid #f59e0b;
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
}

.note-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}

.note-icon {
    font-size: 16px;
    color: #f59e0b;
}

.note-title {
    font-size: 14px;
    font-weight: 600;
    color: #92400e;
}

.note-content {
    font-size: 11px;
    color: #451a03;
    line-height: 1.4;
}

/* Responsividade mobile */
@media (max-width: 768px) {
    .car-vehicle-data-section {
        padding: 16px;
        margin: 16px 0;
    }
    
    .section-title {
        font-size: 18px;
    }
    
    .vehicle-header {
        gap: 8px;
    }
    
    .vehicle-icon-large {
        font-size: 28px;
    }
    
    .vehicle-name {
        font-size: 16px;
    }
    
    .pressure-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .pressure-values {
        width: 100%;
        justify-content: space-around;
    }
    
    .specs-grid {
        grid-template-columns: 1fr;
        gap: 6px;
    }
    
    .systems-grid {
        grid-template-columns: 1fr;
        gap: 6px;
    }
    
    .info-cards {
        grid-template-columns: 1fr;
        gap: 6px;
    }
    
    .location-options {
        gap: 6px;
    }
    
    .location-card,
    .system-card,
    .info-card {
        padding: 8px;
    }
    
    .tpms-indicator {
        padding: 6px 8px;
    }
}
</style>