{{-- 
Partial: tire-pressure/amp/car/full-load-table.blade.php
Tabela completa de cargas para carros - AMP
Versão AMP com pressões por ocupação e bagagem
--}}

@php
    $fullLoadTable = $article->getData()['full_load_table'] ?? [];
    $vehicleInfo = $article->getData()['vehicle_info'] ?? [];
    $tireSpecs = $article->getData()['tire_specifications_by_version'] ?? [];
    $seasonalAdjustments = $article->getData()['seasonal_adjustments'] ?? [];
    
    // Dados principais da primeira versão ou fallback
    $mainSpec = $tireSpecs[0] ?? [];
    $frontEmpty = $mainSpec['front_normal'] ?? $vehicleInfo['pressure_front'] ?? '32 PSI';
    $rearEmpty = $mainSpec['rear_normal'] ?? $vehicleInfo['pressure_rear'] ?? '32 PSI';
    $frontLoaded = $mainSpec['front_loaded'] ?? $vehicleInfo['pressure_front_loaded'] ?? '36 PSI';
    $rearLoaded = $mainSpec['rear_loaded'] ?? $vehicleInfo['pressure_rear_loaded'] ?? '42 PSI';
@endphp

<section class="full-load-table-section">
    <h2 class="section-title">📊 Tabela Completa de Cargas</h2>
    
    <!-- Resumo do Veículo -->
    <div class="vehicle-summary">
        <div class="summary-header">
            <span class="summary-icon">🚗</span>
            <span class="summary-title">{{ $vehicleInfo['full_name'] ?? 'Automóvel' }}</span>
        </div>
        <div class="summary-subtitle">Pressões por situação de carga</div>
    </div>
    
    <!-- Tabela Principal de Cargas -->
    <div class="main-load-table">
        <h3 class="table-title">⚖️ Pressões por Ocupação</h3>
        <div class="table-wrapper">
            <table class="load-table">
                <thead>
                    <tr>
                        <th>Situação</th>
                        <th>Ocupantes</th>
                        <th>Dianteiro</th>
                        <th>Traseiro</th>
                        <th>Observação</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Veículo Vazio -->
                    <tr class="load-row empty">
                        <td class="situation-cell">
                            <div class="situation-info">
                                <span class="situation-icon">🚗</span>
                                <div class="situation-details">
                                    <div class="situation-name">Vazio</div>
                                    <div class="situation-desc">Apenas motorista</div>
                                </div>
                            </div>
                        </td>
                        <td class="occupants-cell">1 pessoa</td>
                        <td class="pressure-cell">
                            <span class="pressure-value front">{{ $frontEmpty }}</span>
                        </td>
                        <td class="pressure-cell">
                            <span class="pressure-value rear">{{ $rearEmpty }}</span>
                        </td>
                        <td class="observation-cell">Pressão mínima</td>
                    </tr>
                    
                    <!-- Ocupação Parcial -->
                    <tr class="load-row partial">
                        <td class="situation-cell">
                            <div class="situation-info">
                                <span class="situation-icon">👥</span>
                                <div class="situation-details">
                                    <div class="situation-name">Parcial</div>
                                    <div class="situation-desc">2-3 pessoas</div>
                                </div>
                            </div>
                        </td>
                        <td class="occupants-cell">2-3 pessoas</td>
                        <td class="pressure-cell">
                            <span class="pressure-value front partial">{{ str_replace(' PSI', '', $frontEmpty) + 2 }} PSI</span>
                        </td>
                        <td class="pressure-cell">
                            <span class="pressure-value rear partial">{{ str_replace(' PSI', '', $rearEmpty) + 3 }} PSI</span>
                        </td>
                        <td class="observation-cell">Uso normal</td>
                    </tr>
                    
                    <!-- Ocupação Completa -->
                    <tr class="load-row full">
                        <td class="situation-cell">
                            <div class="situation-info">
                                <span class="situation-icon">👨‍👩‍👧‍👦</span>
                                <div class="situation-details">
                                    <div class="situation-name">Completa</div>
                                    <div class="situation-desc">4-5 pessoas</div>
                                </div>
                            </div>
                        </td>
                        <td class="occupants-cell">4-5 pessoas</td>
                        <td class="pressure-cell">
                            <span class="pressure-value front full">{{ $frontLoaded }}</span>
                        </td>
                        <td class="pressure-cell">
                            <span class="pressure-value rear full">{{ $rearLoaded }}</span>
                        </td>
                        <td class="observation-cell">Carga máxima</td>
                    </tr>
                    
                    <!-- Com Bagagem -->
                    <tr class="load-row luggage">
                        <td class="situation-cell">
                            <div class="situation-info">
                                <span class="situation-icon">🧳</span>
                                <div class="situation-details">
                                    <div class="situation-name">+ Bagagem</div>
                                    <div class="situation-desc">Porta-malas cheio</div>
                                </div>
                            </div>
                        </td>
                        <td class="occupants-cell">Variável</td>
                        <td class="pressure-cell">
                            <span class="pressure-value front luggage">{{ $frontLoaded }}</span>
                        </td>
                        <td class="pressure-cell">
                            <span class="pressure-value rear luggage">{{ str_replace(' PSI', '', $rearLoaded) + 2 }} PSI</span>
                        </td>
                        <td class="observation-cell">Viagens longas</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Cards de Situações Específicas -->
    <div class="load-scenarios">
        <h3 class="scenarios-title">🎯 Cenários Específicos</h3>
        <div class="scenarios-grid">
            <div class="scenario-card city">
                <div class="card-header">
                    <span class="card-icon">🏙️</span>
                    <span class="card-title">Uso Urbano</span>
                </div>
                <div class="card-content">
                    <div class="pressure-recommendation">
                        <div class="pressure-item">
                            <span class="pressure-label">Dianteiro:</span>
                            <span class="pressure-number">{{ $frontEmpty }}</span>
                        </div>
                        <div class="pressure-item">
                            <span class="pressure-label">Traseiro:</span>
                            <span class="pressure-number">{{ $rearEmpty }}</span>
                        </div>
                    </div>
                    <div class="card-notes">
                        <div class="note-item">• Conforto prioritário</div>
                        <div class="note-item">• Baixas velocidades</div>
                        <div class="note-item">• Para-arranca frequente</div>
                    </div>
                </div>
            </div>
            
            <div class="scenario-card highway">
                <div class="card-header">
                    <span class="card-icon">🛣️</span>
                    <span class="card-title">Estrada</span>
                </div>
                <div class="card-content">
                    <div class="pressure-recommendation">
                        <div class="pressure-item">
                            <span class="pressure-label">Dianteiro:</span>
                            <span class="pressure-number">{{ str_replace(' PSI', '', $frontEmpty) + 2 }} PSI</span>
                        </div>
                        <div class="pressure-item">
                            <span class="pressure-label">Traseiro:</span>
                            <span class="pressure-number">{{ str_replace(' PSI', '', $rearEmpty) + 3 }} PSI</span>
                        </div>
                    </div>
                    <div class="card-notes">
                        <div class="note-item">• Estabilidade em velocidade</div>
                        <div class="note-item">• Menor resistência</div>
                        <div class="note-item">• Economia de combustível</div>
                    </div>
                </div>
            </div>
            
            <div class="scenario-card travel">
                <div class="card-header">
                    <span class="card-icon">✈️</span>
                    <span class="card-title">Viagem</span>
                </div>
                <div class="card-content">
                    <div class="pressure-recommendation">
                        <div class="pressure-item">
                            <span class="pressure-label">Dianteiro:</span>
                            <span class="pressure-number">{{ $frontLoaded }}</span>
                        </div>
                        <div class="pressure-item">
                            <span class="pressure-label">Traseiro:</span>
                            <span class="pressure-number">{{ str_replace(' PSI', '', $rearLoaded) + 2 }} PSI</span>
                        </div>
                    </div>
                    <div class="card-notes">
                        <div class="note-item">• Carga completa + bagagem</div>
                        <div class="note-item">• Longas distâncias</div>
                        <div class="note-item">• Verificar antes da viagem</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Ajustes Sazonais -->
    <div class="seasonal-adjustments">
        <h3 class="seasonal-title">🌡️ Ajustes Sazonais</h3>
        <div class="seasonal-grid">
            <div class="season-card summer">
                <div class="season-header">
                    <span class="season-icon">☀️</span>
                    <span class="season-name">VERÃO</span>
                </div>
                <div class="season-temp">Acima de 25°C</div>
                <div class="season-adjustment">-1 a -2 PSI</div>
                <div class="season-reason">Expansão do ar com calor</div>
            </div>
            
            <div class="season-card winter">
                <div class="season-header">
                    <span class="season-icon">❄️</span>
                    <span class="season-name">INVERNO</span>
                </div>
                <div class="season-temp">Abaixo de 10°C</div>
                <div class="season-adjustment">+1 a +2 PSI</div>
                <div class="season-reason">Contração do ar com frio</div>
            </div>
            
            <div class="season-card mild">
                <div class="season-header">
                    <span class="season-icon">🍂</span>
                    <span class="season-name">AMENO</span>
                </div>
                <div class="season-temp">10°C a 25°C</div>
                <div class="season-adjustment">Padrão</div>
                <div class="season-reason">Temperatura ideal</div>
            </div>
        </div>
    </div>
    
    <!-- Guia de Peso da Bagagem -->
    <div class="luggage-guide">
        <h4 class="luggage-title">🧳 Guia de Peso da Bagagem</h4>
        <div class="weight-table">
            <div class="weight-header">
                <div class="header-item">Peso da Bagagem</div>
                <div class="header-item">Ajuste Traseiro</div>
                <div class="header-item">Situação</div>
            </div>
            <div class="weight-rows">
                <div class="weight-row light">
                    <div class="weight-range">Até 50kg</div>
                    <div class="weight-adjustment">+2 PSI</div>
                    <div class="weight-situation">Bagagem leve</div>
                </div>
                <div class="weight-row medium">
                    <div class="weight-range">50-100kg</div>
                    <div class="weight-adjustment">+4 PSI</div>
                    <div class="weight-situation">Bagagem média</div>
                </div>
                <div class="weight-row heavy">
                    <div class="weight-range">100-150kg</div>
                    <div class="weight-adjustment">+6 PSI</div>
                    <div class="weight-situation">Bagagem pesada</div>
                </div>
                <div class="weight-row max">
                    <div class="weight-range">Acima 150kg</div>
                    <div class="weight-adjustment">Consulte manual</div>
                    <div class="weight-situation">Peso máximo</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Variações por Tipo de Veículo -->
    <div class="vehicle-type-variations">
        <h4 class="variations-title">🚗 Variações por Tipo</h4>
        <div class="vehicle-types">
            <div class="type-card hatch">
                <div class="type-header">
                    <span class="type-icon">🚗</span>
                    <span class="type-name">Hatch/Sedan</span>
                </div>
                <div class="type-characteristics">
                    <div class="char-item">• Peso moderado</div>
                    <div class="char-item">• Bagageiro limitado</div>
                    <div class="char-item">• Pressões padrão</div>
                </div>
            </div>
            
            <div class="type-card suv">
                <div class="type-header">
                    <span class="type-icon">🚙</span>
                    <span class="type-name">SUV/Crossover</span>
                </div>
                <div class="type-characteristics">
                    <div class="char-item">• Peso elevado</div>
                    <div class="char-item">• Grande capacidade</div>
                    <div class="char-item">• Pressões mais altas</div>
                </div>
            </div>
            
            <div class="type-card pickup">
                <div class="type-header">
                    <span class="type-icon">🚚</span>
                    <span class="type-name">Pick-up</span>
                </div>
                <div class="type-characteristics">
                    <div class="char-item">• Carga variável</div>
                    <div class="char-item">• Caçamba livre</div>
                    <div class="char-item">• Ajustar por carga</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Dicas Importantes -->
    <div class="important-tips">
        <h4 class="tips-title">💡 Dicas Importantes</h4>
        <div class="tips-grid">
            <div class="tip-card critical">
                <div class="tip-icon">⚠️</div>
                <div class="tip-content">
                    <div class="tip-title">Nunca Exceda</div>
                    <div class="tip-text">Não ultrapasse a pressão máxima indicada no pneu</div>
                </div>
            </div>
            
            <div class="tip-card warning">
                <div class="tip-icon">🏋️</div>
                <div class="tip-content">
                    <div class="tip-title">Peso Máximo</div>
                    <div class="tip-text">Respeite sempre o peso máximo do veículo</div>
                </div>
            </div>
            
            <div class="tip-card info">
                <div class="tip-icon">🔄</div>
                <div class="tip-content">
                    <div class="tip-title">Reajuste</div>
                    <div class="tip-text">Volte à pressão normal após descarregar</div>
                </div>
            </div>
            
            <div class="tip-card success">
                <div class="tip-icon">📖</div>
                <div class="tip-content">
                    <div class="tip-title">Manual</div>
                    <div class="tip-text">Consulte sempre o manual do proprietário</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Nota de Segurança -->
    <div class="safety-note">
        <div class="note-header">
            <span class="note-icon">🛡️</span>
            <span class="note-title">Nota de Segurança</span>
        </div>
        <div class="note-content">
            <strong>Sempre verifique a pressão antes de viagens longas</strong> ou quando carregar peso extra. 
            A sobrecarga com pressão inadequada pode causar <strong>estouros e acidentes graves</strong>. 
            Em caso de dúvida, opte sempre por pressões ligeiramente mais altas.
        </div>
    </div>
</section>

<style>
/* Seção principal */
.full-load-table-section {
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

/* Resumo do veículo */
.vehicle-summary {
    margin-bottom: 24px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    text-align: center;
    border: 1px solid #e5e7eb;
}

.summary-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-bottom: 8px;
}

.summary-icon {
    font-size: 20px;
    color: #2563eb;
}

.summary-title {
    font-size: 16px;
    font-weight: 600;
    color: #1e40af;
}

.summary-subtitle {
    font-size: 12px;
    color: #6b7280;
}

/* Tabela principal */
.main-load-table {
    margin-bottom: 24px;
}

.table-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.table-wrapper {
    overflow-x: auto;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.load-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    font-size: 12px;
}

.load-table th {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: white;
    padding: 10px 6px;
    text-align: center;
    font-weight: 600;
    font-size: 11px;
}

.load-table td {
    padding: 8px 6px;
    border-bottom: 1px solid #f1f5f9;
}

.load-table tr:nth-child(even) {
    background-color: #f8fafc;
}

.load-row.empty {
    background: linear-gradient(135deg, #f0fdf4, #dcfce7) !important;
}

.load-row.partial {
    background: linear-gradient(135deg, #fffbeb, #fef3c7) !important;
}

.load-row.full {
    background: linear-gradient(135deg, #fef2f2, #fee2e2) !important;
}

.load-row.luggage {
    background: linear-gradient(135deg, #f5f3ff, #ede9fe) !important;
}

.situation-cell {
    text-align: left;
}

.situation-info {
    display: flex;
    align-items: center;
    gap: 6px;
}

.situation-icon {
    font-size: 14px;
    flex-shrink: 0;
}

.situation-details {
    flex: 1;
}

.situation-name {
    font-size: 11px;
    font-weight: 600;
    color: #374151;
}

.situation-desc {
    font-size: 9px;
    color: #6b7280;
}

.occupants-cell {
    font-size: 10px;
    color: #4b5563;
    text-align: center;
}

.pressure-cell {
    text-align: center;
}

.pressure-value {
    font-size: 12px;
    font-weight: 700;
    padding: 3px 6px;
    border-radius: 4px;
    color: white;
}

.pressure-value.front {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
}

.pressure-value.rear {
    background: linear-gradient(135deg, #16a34a, #15803d);
}

.pressure-value.partial {
    background: linear-gradient(135deg, #f59e0b, #d97706);
}

.pressure-value.full {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
}

.pressure-value.luggage {
    background: linear-gradient(135deg, #7c3aed, #6d28d9);
}

.observation-cell {
    font-size: 9px;
    color: #6b7280;
    text-align: center;
}

/* Cards de cenários */
.load-scenarios {
    margin-bottom: 24px;
}

.scenarios-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.scenarios-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}

.scenario-card {
    background: white;
    border-radius: 10px;
    padding: 14px;
    border: 2px solid;
}

.scenario-card.city {
    border-color: #16a34a;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
}

.scenario-card.highway {
    border-color: #2563eb;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
}

.scenario-card.travel {
    border-color: #f59e0b;
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
}

.card-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
}

.card-icon {
    font-size: 16px;
}

.card-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
}

.card-content {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.pressure-recommendation {
    display: flex;
    justify-content: space-around;
    padding: 10px;
    background: rgba(255, 255, 255, 0.5);
    border-radius: 6px;
}

.pressure-item {
    text-align: center;
}

.pressure-label {
    font-size: 10px;
    color: #6b7280;
    margin-bottom: 2px;
    display: block;
}

.pressure-number {
    font-size: 14px;
    font-weight: 700;
    color: #1f2937;
}

.card-notes {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.note-item {
    font-size: 10px;
    color: #4b5563;
}

/* Ajustes sazonais */
.seasonal-adjustments {
    margin-bottom: 24px;
}

.seasonal-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.seasonal-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}

.season-card {
    background: white;
    border-radius: 8px;
    padding: 12px;
    text-align: center;
    border: 2px solid;
}

.season-card.summer {
    border-color: #f59e0b;
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
}

.season-card.winter {
    border-color: #3b82f6;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
}

.season-card.mild {
    border-color: #16a34a;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
}

.season-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin-bottom: 6px;
}

.season-icon {
    font-size: 16px;
}

.season-name {
    font-size: 12px;
    font-weight: 700;
    color: #374151;
}

.season-temp {
    font-size: 10px;
    color: #6b7280;
    margin-bottom: 4px;
}

.season-adjustment {
    font-size: 14px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 4px;
}

.season-reason {
    font-size: 9px;
    color: #6b7280;
    font-style: italic;
}

/* Guia de bagagem */
.luggage-guide {
    margin-bottom: 24px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #e5e7eb;
}

.luggage-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.weight-table {
    display: flex;
    flex-direction: column;
}

.weight-header {
    display: flex;
    background: #f3f4f6;
    border-radius: 6px 6px 0 0;
    font-weight: 600;
}

.header-item {
    flex: 1;
    padding: 8px;
    text-align: center;
    font-size: 11px;
    color: #374151;
}

.weight-rows {
    display: flex;
    flex-direction: column;
}

.weight-row {
    display: flex;
    border-bottom: 1px solid #e5e7eb;
}

.weight-row:last-child {
    border-bottom: none;
    border-radius: 0 0 6px 6px;
}

.weight-range,
.weight-adjustment,
.weight-situation {
    flex: 1;
    padding: 8px;
    text-align: center;
    font-size: 10px;
}

.weight-range {
    font-weight: 600;
    color: #374151;
}

.weight-adjustment {
    color: #dc2626;
    font-weight: 600;
}

.weight-situation {
    color: #6b7280;
}

/* Variações por tipo */
.vehicle-type-variations {
    margin-bottom: 24px;
}

.variations-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.vehicle-types {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}

.type-card {
    background: white;
    border-radius: 8px;
    padding: 12px;
    border: 1px solid #e5e7eb;
}

.type-header {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
}

.type-icon {
    font-size: 16px;
}

.type-name {
    font-size: 12px;
    font-weight: 600;
    color: #374151;
}

.type-characteristics {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.char-item {
    font-size: 10px;
    color: #4b5563;
}

/* Dicas importantes */
.important-tips {
    margin-bottom: 24px;
}

.tips-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.tips-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.tip-card {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px;
    background: white;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
}

.tip-card.critical {
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
    border-color: #dc2626;
}

.tip-card.warning {
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
    border-color: #f59e0b;
}

.tip-card.info {
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    border-color: #3b82f6;
}

.tip-card.success {
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    border-color: #16a34a;
}

.tip-icon {
    font-size: 14px;
    flex-shrink: 0;
}

.tip-content {
    flex: 1;
}

.tip-title {
    font-size: 10px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 2px;
}

.tip-text {
    font-size: 9px;
    color: #6b7280;
    line-height: 1.3;
}

/* Nota de segurança */
.safety-note {
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 2px solid #dc2626;
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
}

.note-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}

.note-icon {
    font-size: 18px;
    color: #dc2626;
}

.note-title {
    font-size: 14px;
    font-weight: 600;
    color: #991b1b;
}

.note-content {
    font-size: 12px;
    color: #7f1d1d;
    line-height: 1.4;
}

/* Responsividade mobile */
@media (max-width: 768px) {
    .full-load-table-section {
        padding: 16px;
        margin: 16px 0;
    }
    
    .section-title {
        font-size: 18px;
    }
    
    .load-table th,
    .load-table td {
        padding: 6px 4px;
        font-size: 10px;
    }
    
    .situation-info {
        gap: 4px;
    }
    
    .situation-icon {
        font-size: 12px;
    }
    
    .pressure-value {
        font-size: 10px;
        padding: 2px 4px;
    }
    
    .scenarios-grid,
    .seasonal-grid,
    .vehicle-types {
        gap: 8px;
    }
    
    .scenario-card,
    .season-card,
    .type-card {
        padding: 10px;
    }
    
    .pressure-recommendation {
        gap: 8px;
        padding: 8px;
    }
    
    .pressure-number {
        font-size: 12px;
    }
    
    .tips-grid {
        grid-template-columns: 1fr;
        gap: 6px;
    }
    
    .tip-card {
        gap: 6px;
        padding: 8px;
    }
    
    .weight-header,
    .weight-row {
        flex-direction: column;
        text-align: left;
    }
    
    .header-item,
    .weight-range,
    .weight-adjustment,
    .weight-situation {
        padding: 4px 8px;
    }
    
    .weight-header {
        background: #e5e7eb;
    }
    
    .weight-row {
        background: #f8fafc;
        margin-bottom: 4px;
        border-radius: 4px;
        border-bottom: none;
    }
}

/* Melhorias para telas muito pequenas */
@media (max-width: 480px) {
    .load-table {
        font-size: 9px;
    }
    
    .load-table th,
    .load-table td {
        padding: 4px 2px;
    }
    
    .situation-name {
        font-size: 9px;
    }
    
    .situation-desc {
        font-size: 8px;
    }
    
    .pressure-value {
        font-size: 9px;
        padding: 2px 3px;
    }
    
    .observation-cell,
    .occupants-cell {
        font-size: 8px;
    }
    
    .card-header {
        gap: 6px;
    }
    
    .card-icon {
        font-size: 14px;
    }
    
    .card-title {
        font-size: 12px;
    }
    
    .pressure-number {
        font-size: 11px;
    }
    
    .note-item,
    .char-item {
        font-size: 9px;
    }
}
</style>