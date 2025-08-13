{{-- 
Partial: tire-pressure/amp/motorcycle/vehicle-data.blade.php
Dados principais do veículo para motocicletas - AMP
Versão AMP com informações técnicas e localização da pressão
--}}

@php
    $vehicleInfo = $article->getData()['vehicle_info'] ?? [];
    $labelLocation = $article->getData()['label_location'] ?? [];
    $informationLocation = $article->getData()['information_location'] ?? [];
    $motorcycleCategory = $vehicleInfo['category'] ?? 'standard';
@endphp

@if(!empty($vehicleInfo))
<section class="vehicle-data-section">
    <h2 class="section-title">🏍️ Dados do Veículo</h2>
    
    <!-- Informações Principais -->
    <div class="main-vehicle-info">
        <div class="vehicle-card">
            <div class="vehicle-header">
                <div class="vehicle-icon-large">🏍️</div>
                <div class="vehicle-details">
                    <div class="vehicle-name">{{ $vehicleInfo['full_name'] ?? 'Motocicleta' }}</div>
                    @if(!empty($vehicleInfo['year_range']))
                    <div class="vehicle-years">{{ $vehicleInfo['year_range'] }}</div>
                    @endif
                    @if(!empty($vehicleInfo['engine_size']))
                    <div class="vehicle-engine">{{ $vehicleInfo['engine_size'] }}</div>
                    @endif
                </div>
                <div class="category-badge-large">
                    @if($motorcycleCategory === 'sport')
                    <span class="badge sport">🏁 Esportiva</span>
                    @elseif($motorcycleCategory === 'touring')
                    <span class="badge touring">🛣️ Touring</span>
                    @elseif($motorcycleCategory === 'naked')
                    <span class="badge naked">⚡ Naked</span>
                    @elseif($motorcycleCategory === 'cruiser')
                    <span class="badge cruiser">🌊 Cruiser</span>
                    @elseif($motorcycleCategory === 'adventure')
                    <span class="badge adventure">🏔️ Adventure</span>
                    @else
                    <span class="badge standard">🔧 Standard</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pressões Recomendadas */
    <div class="pressure-summary-card">
        <div class="summary-header">
            <span class="summary-icon">📊</span>
            <span class="summary-title">Pressões Recomendadas</span>
        </div>
        <div class="pressure-data">
            <div class="pressure-row normal">
                <div class="situation-label">
                    <span class="situation-icon">👤</span>
                    <span class="situation-text">Piloto Solo</span>
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
            
            @if(!empty($vehicleInfo['pressure_front_loaded']) || !empty($vehicleInfo['pressure_rear_loaded']))
            <div class="pressure-row loaded">
                <div class="situation-label">
                    <span class="situation-icon">👥</span>
                    <span class="situation-text">Com Garupa</span>
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
                    {{ $labelLocation['main_location'] ?? $informationLocation['primary'] ?? 'Chassi lado direito ou manual do proprietário' }}
                </div>
                <div class="location-note">Informação mais confiável</div>
            </div>
            @endif
            
            @if(!empty($labelLocation['alternative_locations']) || !empty($informationLocation['alternatives']))
            @php
                $alternatives = $labelLocation['alternative_locations'] ?? $informationLocation['alternatives'] ?? [];
                if (is_string($alternatives)) $alternatives = [$alternatives];
            @endphp
            @foreach(array_slice($alternatives, 0, 2) as $index => $location)
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
                <div class="spec-label">Cilindrada</div>
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
                <div class="spec-label">Transmissão</div>
                <div class="spec-value">{{ $vehicleInfo['transmission'] }}</div>
            </div>
            @endif
            
            @if(!empty($vehicleInfo['weight']))
            <div class="spec-item">
                <div class="spec-label">Peso</div>
                <div class="spec-value">{{ $vehicleInfo['weight'] }}</div>
            </div>
            @endif
        </div>
    </div>
    
    <!-- Características por Categoria -->
    <div class="category-characteristics">
        <h4 class="char-title">🎯 Características da Categoria</h4>
        @if($motorcycleCategory === 'sport')
        <div class="char-card sport">
            <div class="char-header">
                <span class="char-icon">🏁</span>
                <span class="char-name">Motocicleta Esportiva</span>
            </div>
            <div class="char-features">
                <div class="feature-item">• Alta performance e velocidade</div>
                <div class="feature-item">• Pressões mais altas recomendadas</div>
                <div class="feature-item">• Verificação frequente necessária</div>
                <div class="feature-item">• Pneus específicos esportivos</div>
                <div class="feature-item">• Desgaste mais rápido</div>
            </div>
        </div>
        @elseif($motorcycleCategory === 'touring')
        <div class="char-card touring">
            <div class="char-header">
                <span class="char-icon">🛣️</span>
                <span class="char-name">Motocicleta Touring</span>
            </div>
            <div class="char-features">
                <div class="feature-item">• Foco em conforto e durabilidade</div>
                <div class="feature-item">• Ajustar pressão para bagagem</div>
                <div class="feature-item">• Verificação antes de viagens</div>
                <div class="feature-item">• Pneus de longa duração</div>
                <div class="feature-item">• Estabilidade prioritária</div>
            </div>
        </div>
        @elseif($motorcycleCategory === 'naked')
        <div class="char-card naked">
            <div class="char-header">
                <span class="char-icon">⚡</span>
                <span class="char-name">Motocicleta Naked</span>
            </div>
            <div class="char-features">
                <div class="feature-item">• Versatilidade urbana/estrada</div>
                <div class="feature-item">• Pressões equilibradas</div>
                <div class="feature-item">• Manutenção moderada</div>
                <div class="feature-item">• Pneus versáteis</div>
                <div class="feature-item">• Boa relação custo/benefício</div>
            </div>
        </div>
        @elseif($motorcycleCategory === 'cruiser')
        <div class="char-card cruiser">
            <div class="char-header">
                <span class="char-icon">🌊</span>
                <span class="char-name">Motocicleta Cruiser</span>
            </div>
            <div class="char-features">
                <div class="feature-item">• Estilo relaxado de pilotagem</div>
                <div class="feature-item">• Pressões para conforto</div>
                <div class="feature-item">• Pneus largos traseiros</div>
                <div class="feature-item">• Manutenção padrão</div>
                <div class="feature-item">• Estabilidade em linha reta</div>
            </div>
        </div>
        @elseif($motorcycleCategory === 'adventure')
        <div class="char-card adventure">
            <div class="char-header">
                <span class="char-icon">🏔️</span>
                <span class="char-name">Motocicleta Adventure</span>
            </div>
            <div class="char-features">
                <div class="feature-item">• Uso misto on/off-road</div>
                <div class="feature-item">• Ajustar pressão por terreno</div>
                <div class="feature-item">• Verificação frequente</div>
                <div class="feature-item">• Pneus específicos para terreno</div>
                <div class="feature-item">• Proteção extra necessária</div>
            </div>
        </div>
        @else
        <div class="char-card standard">
            <div class="char-header">
                <span class="char-icon">🔧</span>
                <span class="char-name">Motocicleta Padrão</span>
            </div>
            <div class="char-features">
                <div class="feature-item">• Uso urbano e rodoviário</div>
                <div class="feature-item">• Pressões conforme manual</div>
                <div class="feature-item">• Manutenção regular</div>
                <div class="feature-item">• Pneus convencionais</div>
                <div class="feature-item">• Economia de combustível</div>
            </div>
        </div>
        @endif
    </div>
    
    <!-- Informações Importantes -->
    <div class="important-info">
        <h4 class="info-title">⚠️ Informações Importantes</h4>
        <div class="info-cards">
            <div class="info-card manual">
                <div class="info-icon">📖</div>
                <div class="info-content">
                    <div class="info-name">Manual do Proprietário</div>
                    <div class="info-desc">Sempre consulte o manual para informações específicas da sua versão</div>
                </div>
            </div>
            
            <div class="info-card temperature">
                <div class="info-icon">🌡️</div>
                <div class="info-content">
                    <div class="info-name">Temperatura Ambiente</div>
                    <div class="info-desc">Pressão varia ±1 PSI a cada 10°C de mudança</div>
                </div>
            </div>
            
            <div class="info-card load">
                <div class="info-icon">⚖️</div>
                <div class="info-content">
                    <div class="info-name">Carga Adicional</div>
                    <div class="info-desc">Ajuste a pressão traseira quando carregar passageiro ou bagagem</div>
                </div>
            </div>
            
            <div class="info-card maintenance">
                <div class="info-icon">🔧</div>
                <div class="info-content">
                    <div class="info-name">Verificação Regular</div>
                    <div class="info-desc">Verifique semanalmente ou antes de viagens longas</div>
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
            As informações apresentadas são baseadas em dados técnicos padrão. 
            <strong>Sempre consulte o manual específico do seu veículo</strong> para obter os valores 
            exatos recomendados pelo fabricante. Em caso de dúvida, procure uma concessionária autorizada.
        </div>
    </div>
</section>
@endif

<style>
/* Seção principal */
.vehicle-data-section {
    margin: 24px 0;
    padding: 20px;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    border-radius: 16px;
    border: 2px solid #3b82f6;
}

.section-title {
    font-size: 20px;
    font-weight: 700;
    color: #1e40af;
    margin-bottom: 20px;
    text-align: center;
    border-bottom: 3px solid #3b82f6;
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
    color: #3b82f6;
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

.badge.sport {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
}

.badge.touring {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
}

.badge.naked {
    background: linear-gradient(135deg, #16a34a, #15803d);
}

.badge.cruiser {
    background: linear-gradient(135deg, #7c3aed, #6d28d9);
}

.badge.adventure {
    background: linear-gradient(135deg, #f59e0b, #d97706);
}

.badge.standard {
    background: linear-gradient(135deg, #64748b, #475569);
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

.situation-label {
    display: flex;
    align-items: center;
    gap: 6px;
    min-width: 80px;
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
.pressure-rear {
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

.char-card.sport {
    border-color: #dc2626;
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
}

.char-card.touring {
    border-color: #2563eb;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
}

.char-card.naked {
    border-color: #16a34a;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
}

.char-card.cruiser {
    border-color: #7c3aed;
    background: linear-gradient(135deg, #f5f3ff, #ede9fe);
}

.char-card.adventure {
    border-color: #f59e0b;
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
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
    grid-template-columns: 1fr;
    gap: 8px;
}

.info-card {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background: white;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
}

.info-icon {
    font-size: 16px;
    color: #3b82f6;
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
    font-size: 10px;
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
    .vehicle-data-section {
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
    
    .location-options,
    .info-cards {
        gap: 6px;
    }
    
    .location-card,
    .info-card {
        padding: 8px;
    }
}
</style>