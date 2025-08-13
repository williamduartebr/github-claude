{{-- 
Partial: tire-pressure/amp/motorcycle/pressure-table.blade.php
Tabela principal de pressões para motocicletas - AMP REFATORADO
Versão AMP otimizada com foco em piloto solo vs garupa
--}}

@php
    $pressureTable = $article->getData()['pressure_table'] ?? [];
    $vehicleInfo = $article->getData()['vehicle_info'] ?? [];
    $tireSpecs = $article->getData()['tire_specifications_by_version'] ?? [];
    
    // Dados principais da primeira versão ou fallback
    $mainSpec = $tireSpecs[0] ?? [];
    $frontNormal = $mainSpec['front_normal'] ?? $vehicleInfo['pressure_front'] ?? '30 PSI';
    $rearNormal = $mainSpec['rear_normal'] ?? $vehicleInfo['pressure_rear'] ?? '32 PSI';
    $frontLoaded = $mainSpec['front_loaded'] ?? $vehicleInfo['pressure_front_loaded'] ?? '32 PSI';
    $rearLoaded = $mainSpec['rear_loaded'] ?? $vehicleInfo['pressure_rear_loaded'] ?? '36 PSI';
@endphp

<section class="pressure-table-section">
    <h2 class="section-title">🎯 Tabela de Pressões por Situação</h2>
    
    <!-- Resumo Visual Principal -->
    <div class="pressure-summary">
        <div class="summary-header">
            <span class="summary-icon">🏍️</span>
            <span class="summary-title">{{ $vehicleInfo['full_name'] ?? 'Pressões Recomendadas' }}</span>
        </div>
        
        <div class="comparison-cards">
            <div class="pressure-card solo">
                <div class="card-header">
                    <span class="card-icon">👤</span>
                    <span class="card-title">PILOTO SOLO</span>
                </div>
                <div class="pressure-values">
                    <div class="pressure-item front">
                        <div class="pressure-label">Dianteiro</div>
                        <div class="pressure-number">{{ $frontNormal }}</div>
                    </div>
                    <div class="pressure-item rear">
                        <div class="pressure-label">Traseiro</div>
                        <div class="pressure-number">{{ $rearNormal }}</div>
                    </div>
                </div>
                <div class="card-note">Uso normal diário</div>
            </div>
            
            <div class="pressure-card passenger">
                <div class="card-header">
                    <span class="card-icon">👥</span>
                    <span class="card-title">COM GARUPA</span>
                </div>
                <div class="pressure-values">
                    <div class="pressure-item front">
                        <div class="pressure-label">Dianteiro</div>
                        <div class="pressure-number">{{ $frontLoaded }}</div>
                    </div>
                    <div class="pressure-item rear">
                        <div class="pressure-label">Traseiro</div>
                        <div class="pressure-number">{{ $rearLoaded }}</div>
                    </div>
                </div>
                <div class="card-note">Aumentar traseiro</div>
            </div>
        </div>
    </div>
    
    <!-- Tabela Detalhada -->
    @if(!empty($tireSpecs) && count($tireSpecs) > 1)
    <div class="detailed-table">
        <h3 class="table-title">📊 Por Versão da Motocicleta</h3>
        <div class="table-wrapper">
            <table class="pressure-table">
                <thead>
                    <tr>
                        <th>Versão</th>
                        <th>Solo - D/T</th>
                        <th>Garupa - D/T</th>
                        <th>Observação</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tireSpecs as $index => $spec)
                    <tr class="{{ $index === 0 ? 'main-version' : '' }}">
                        <td class="version-cell">
                            <div class="version-info">
                                @if($index === 0)
                                <div class="version-badge main">Principal</div>
                                @endif
                                <div class="version-name">{{ $spec['version'] ?? 'Versão ' . ($index + 1) }}</div>
                            </div>
                        </td>
                        <td class="pressure-cell">
                            <div class="pressure-pair">
                                <span class="front">{{ $spec['front_normal'] ?? 'N/A' }}</span>
                                <span class="separator">/</span>
                                <span class="rear">{{ $spec['rear_normal'] ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td class="pressure-cell">
                            <div class="pressure-pair">
                                <span class="front">{{ $spec['front_loaded'] ?? 'N/A' }}</span>
                                <span class="separator">/</span>
                                <span class="rear">{{ $spec['rear_loaded'] ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td class="observation-cell">
                            @if($index === 0)
                            <span class="obs-text">Recomendado</span>
                            @else
                            <span class="obs-text">Consulte manual</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="table-legend">
            <span class="legend-item">D = Dianteiro</span>
            <span class="legend-item">T = Traseiro</span>
        </div>
    </div>
    @endif
    
    <!-- Guia de Ajustes -->
    <div class="adjustment-guide">
        <h3 class="guide-title">⚖️ Guia de Ajustes</h3>
        <div class="adjustment-scenarios">
            <div class="scenario normal">
                <div class="scenario-header">
                    <span class="scenario-icon">😊</span>
                    <span class="scenario-name">USO NORMAL</span>
                </div>
                <div class="scenario-content">
                    <div class="scenario-pressure">{{ $frontNormal }} / {{ $rearNormal }}</div>
                    <div class="scenario-desc">Piloto solo, uso urbano/estrada</div>
                </div>
            </div>
            
            <div class="scenario loaded">
                <div class="scenario-header">
                    <span class="scenario-icon">🎒</span>
                    <span class="scenario-name">COM CARGA</span>
                </div>
                <div class="scenario-content">
                    <div class="scenario-pressure">{{ $frontLoaded }} / {{ $rearLoaded }}</div>
                    <div class="scenario-desc">Garupa ou bagagem pesada</div>
                </div>
            </div>
            
            <div class="scenario hot">
                <div class="scenario-header">
                    <span class="scenario-icon">🌡️</span>
                    <span class="scenario-name">TEMPO QUENTE</span>
                </div>
                <div class="scenario-content">
                    <div class="scenario-pressure">-1 PSI</div>
                    <div class="scenario-desc">Reduzir em dias muito quentes</div>
                </div>
            </div>
            
            <div class="scenario cold">
                <div class="scenario-header">
                    <span class="scenario-icon">❄️</span>
                    <span class="scenario-name">TEMPO FRIO</span>
                </div>
                <div class="scenario-content">
                    <div class="scenario-pressure">+1 PSI</div>
                    <div class="scenario-desc">Aumentar em dias muito frios</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Diferença Crítica -->
    <div class="critical-difference">
        <h4 class="diff-title">⚠️ Diferença Crítica Dianteiro/Traseiro</h4>
        <div class="diff-explanation">
            <div class="diff-visual">
                <div class="tire-diagram front">
                    <div class="tire-label">DIANTEIRO</div>
                    <div class="tire-pressure lower">{{ $frontNormal }}</div>
                    <div class="tire-function">Direção + Frenagem</div>
                </div>
                <div class="diff-arrow">→</div>
                <div class="tire-diagram rear">
                    <div class="tire-label">TRASEIRO</div>
                    <div class="tire-pressure higher">{{ $rearNormal }}</div>
                    <div class="tire-function">Tração + Estabilidade</div>
                </div>
            </div>
            <div class="diff-note">
                <strong>NUNCA iguale as pressões!</strong> O traseiro precisa ser sempre maior para compensar 
                o peso do motor e garantir estabilidade.
            </div>
        </div>
    </div>
    
    <!-- Condições Especiais -->
    @if(!empty($pressureTable['special_conditions']))
    <div class="special-conditions">
        <h4 class="conditions-title">🔧 Condições Especiais</h4>
        <div class="conditions-list">
            @foreach($pressureTable['special_conditions'] as $condition)
            <div class="condition-item">
                <div class="condition-name">{{ $condition['condition'] ?? 'Condição Especial' }}</div>
                <div class="condition-adjustment">{{ $condition['front_pressure'] ?? 'Ver manual' }} / {{ $condition['rear_pressure'] ?? 'Ver manual' }}</div>
                <div class="condition-note">{{ $condition['observation'] ?? 'Consulte o manual' }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    
    <!-- Dica Final -->
    <div class="pressure-tip">
        <div class="tip-icon">💡</div>
        <div class="tip-content">
            <strong>Lembre-se:</strong> A pressão correta não é apenas sobre números, 
            mas sobre <strong>segurança</strong>, <strong>economia</strong> e <strong>performance</strong> 
            da sua motocicleta.
        </div>
    </div>
</section>

<style>
/* Seção principal */
.pressure-table-section {
    margin: 24px 0;
    padding: 20px;
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
    border-radius: 16px;
    border: 2px solid #dc2626;
}

.section-title {
    font-size: 20px;
    font-weight: 700;
    color: #991b1b;
    margin-bottom: 20px;
    text-align: center;
    border-bottom: 3px solid #dc2626;
    padding-bottom: 8px;
}

/* Resumo visual */
.pressure-summary {
    margin-bottom: 24px;
}

.summary-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-bottom: 16px;
}

.summary-icon {
    font-size: 20px;
    color: #dc2626;
}

.summary-title {
    font-size: 16px;
    font-weight: 600;
    color: #991b1b;
}

.comparison-cards {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.pressure-card {
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 2px solid;
    text-align: center;
}

.pressure-card.solo {
    border-color: #16a34a;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
}

.pressure-card.passenger {
    border-color: #f59e0b;
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
}

.card-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin-bottom: 12px;
}

.card-icon {
    font-size: 16px;
}

.card-title {
    font-size: 11px;
    font-weight: 700;
    color: #374151;
}

.pressure-values {
    display: flex;
    justify-content: space-around;
    margin-bottom: 8px;
}

.pressure-item {
    text-align: center;
}

.pressure-label {
    font-size: 9px;
    color: #6b7280;
    margin-bottom: 2px;
}

.pressure-number {
    font-size: 16px;
    font-weight: 700;
    color: #1f2937;
}

.card-note {
    font-size: 10px;
    color: #6b7280;
    font-style: italic;
}

/* Tabela detalhada */
.detailed-table {
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

.pressure-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    font-size: 12px;
    min-width: 500px;
}

.pressure-table th {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    color: white;
    padding: 10px 8px;
    text-align: center;
    font-weight: 600;
    font-size: 11px;
}

.pressure-table td {
    padding: 8px;
    border-bottom: 1px solid #f1f5f9;
    text-align: center;
}

.pressure-table tr:nth-child(even) {
    background-color: #f8fafc;
}

.main-version {
    background: linear-gradient(135deg, #fef3c7, #fde68a) !important;
}

.version-cell {
    text-align: left !important;
}

.version-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.version-badge {
    font-size: 8px;
    padding: 2px 6px;
    border-radius: 8px;
    font-weight: 700;
    color: white;
    background: #dc2626;
    align-self: flex-start;
}

.version-name {
    font-size: 11px;
    font-weight: 600;
    color: #374151;
}

.pressure-cell {
    text-align: center;
}

.pressure-pair {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}

.pressure-pair .front {
    color: #3b82f6;
    font-weight: 600;
}

.pressure-pair .rear {
    color: #16a34a;
    font-weight: 600;
}

.separator {
    color: #6b7280;
    font-weight: 700;
}

.observation-cell {
    text-align: center;
}

.obs-text {
    font-size: 10px;
    color: #6b7280;
    font-style: italic;
}

.table-legend {
    display: flex;
    justify-content: center;
    gap: 16px;
    margin-top: 8px;
}

.legend-item {
    font-size: 10px;
    color: #6b7280;
    font-weight: 500;
}

/* Guia de ajustes */
.adjustment-guide {
    margin-bottom: 24px;
}

.guide-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.adjustment-scenarios {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.scenario {
    background: white;
    border-radius: 8px;
    padding: 10px;
    border: 2px solid;
    text-align: center;
}

.scenario.normal {
    border-color: #16a34a;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
}

.scenario.loaded {
    border-color: #f59e0b;
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
}

.scenario.hot {
    border-color: #dc2626;
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
}

.scenario.cold {
    border-color: #3b82f6;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
}

.scenario-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    margin-bottom: 6px;
}

.scenario-icon {
    font-size: 12px;
}

.scenario-name {
    font-size: 9px;
    font-weight: 700;
    color: #374151;
}

.scenario-content {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.scenario-pressure {
    font-size: 14px;
    font-weight: 700;
    color: #1f2937;
}

.scenario-desc {
    font-size: 8px;
    color: #6b7280;
}

/* Diferença crítica */
.critical-difference {
    margin-bottom: 24px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 2px solid #dc2626;
}

.diff-title {
    font-size: 14px;
    font-weight: 600;
    color: #991b1b;
    margin-bottom: 12px;
    text-align: center;
}

.diff-visual {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin-bottom: 12px;
}

.tire-diagram {
    text-align: center;
    padding: 12px;
    border-radius: 8px;
    flex: 1;
}

.tire-diagram.front {
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    border: 1px solid #3b82f6;
}

.tire-diagram.rear {
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    border: 1px solid #16a34a;
}

.tire-label {
    font-size: 9px;
    font-weight: 700;
    color: #374151;
    margin-bottom: 4px;
}

.tire-pressure {
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 4px;
}

.tire-pressure.lower {
    color: #3b82f6;
}

.tire-pressure.higher {
    color: #16a34a;
}

.tire-function {
    font-size: 8px;
    color: #6b7280;
}

.diff-arrow {
    font-size: 16px;
    color: #6b7280;
}

.diff-note {
    font-size: 11px;
    color: #991b1b;
    line-height: 1.4;
    text-align: center;
    padding: 8px;
    background: #fef2f2;
    border-radius: 6px;
}

/* Condições especiais */
.special-conditions {
    margin-bottom: 24px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #e5e7eb;
}

.conditions-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.conditions-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.condition-item {
    padding: 8px;
    background: #f8fafc;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
}

.condition-name {
    font-size: 11px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 2px;
}

.condition-adjustment {
    font-size: 12px;
    font-weight: 700;
    color: #dc2626;
    margin-bottom: 2px;
}

.condition-note {
    font-size: 9px;
    color: #6b7280;
    font-style: italic;
}

/* Dica final */
.pressure-tip {
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 2px solid #3b82f6;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    display: flex;
    gap: 12px;
    align-items: flex-start;
}

.tip-icon {
    font-size: 20px;
    color: #3b82f6;
    flex-shrink: 0;
}

.tip-content {
    font-size: 12px;
    color: #1e40af;
    line-height: 1.4;
}

/* Responsividade mobile */
@media (max-width: 768px) {
    .pressure-table-section {
        padding: 16px;
        margin: 16px 0;
    }
    
    .section-title {
        font-size: 18px;
    }
    
    .comparison-cards {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    
    .pressure-card {
        padding: 12px;
    }
    
    .pressure-number {
        font-size: 14px;
    }
    
    .adjustment-scenarios {
        grid-template-columns: 1fr;
        gap: 6px;
    }
    
    .scenario {
        padding: 8px;
    }
    
    .diff-visual {
        flex-direction: column;
        gap: 8px;
    }
    
    .diff-arrow {
        transform: rotate(90deg);
    }
    
    .tire-diagram {
        padding: 10px;
    }
    
    .tire-pressure {
        font-size: 14px;
    }
    
    .pressure-tip {
        gap: 8px;
        padding: 12px;
    }
    
    .tip-icon {
        font-size: 16px;
    }
    
    .tip-content {
        font-size: 11px;
    }
}
</style>