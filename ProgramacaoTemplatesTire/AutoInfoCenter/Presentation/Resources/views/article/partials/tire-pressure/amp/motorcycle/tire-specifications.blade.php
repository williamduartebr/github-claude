{{-- 
Partial: tire-pressure/amp/motorcycle/tire-specifications.blade.php
Especificações detalhadas dos pneus por versão da motocicleta - AMP
Versão AMP com medidas, índices e categorização por tipo
--}}

@php
    $tireSpecs = $article->getData()['tire_specifications_by_version'] ?? [];
    $vehicleInfo = $article->getData()['vehicle_info'] ?? [];
    $motorcycleCategory = $vehicleInfo['category'] ?? 'standard';
    $defaultTireSpecs = $article->getData()['tire_specifications'] ?? [];
@endphp

@if(!empty($tireSpecs) || !empty($defaultTireSpecs))
<section class="tire-specs-section">
    <h2 class="section-title">🏍️ Especificações dos Pneus</h2>
    
    <!-- Informações do Veículo -->
    <div class="vehicle-info-card">
        <div class="vehicle-header">
            <span class="vehicle-icon">🏍️</span>
            <div class="vehicle-details">
                <div class="vehicle-name">{{ $vehicleInfo['full_name'] ?? 'Motocicleta' }}</div>
                <div class="vehicle-category">
                    @if($motorcycleCategory === 'sport')
                    <span class="category-badge sport">🏁 Esportiva</span>
                    @elseif($motorcycleCategory === 'touring')
                    <span class="category-badge touring">🛣️ Touring</span>
                    @elseif($motorcycleCategory === 'naked')
                    <span class="category-badge naked">⚡ Naked</span>
                    @elseif($motorcycleCategory === 'cruiser')
                    <span class="category-badge cruiser">🌊 Cruiser</span>
                    @else
                    <span class="category-badge standard">🔧 Padrão</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabela de Especificações por Versão -->
    @if(!empty($tireSpecs))
    <div class="specs-table-container">
        <h3 class="table-title">📊 Especificações por Versão</h3>
        <div class="table-wrapper">
            <table class="specs-table">
                <thead>
                    <tr>
                        <th>Versão</th>
                        <th>Dianteiro</th>
                        <th>Traseiro</th>
                        <th>Pressão Solo</th>
                        <th>Com Garupa</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tireSpecs as $index => $spec)
                    <tr class="{{ $index === 0 ? 'main-version' : '' }}">
                        <td class="version-cell">
                            <div class="version-info">
                                @if($index === 0)
                                <div class="version-indicator main"></div>
                                @else
                                <div class="version-indicator alt"></div>
                                @endif
                                <div class="version-content">
                                    <div class="version-name">{{ $spec['version'] ?? 'Padrão' }}</div>
                                    @if($index === 0)
                                    <div class="version-label">Principal</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="tire-cell">
                            <div class="tire-size">{{ $spec['front_tire_size'] ?? 'N/A' }}</div>
                            @if(!empty($spec['front_tire_type']))
                            <div class="tire-type">{{ $spec['front_tire_type'] }}</div>
                            @endif
                        </td>
                        <td class="tire-cell">
                            <div class="tire-size">{{ $spec['rear_tire_size'] ?? 'N/A' }}</div>
                            @if(!empty($spec['rear_tire_type']))
                            <div class="tire-type">{{ $spec['rear_tire_type'] }}</div>
                            @endif
                        </td>
                        <td class="pressure-cell">
                            <div class="pressure-values">
                                <div class="pressure-front">D: {{ $spec['front_normal'] ?? 'N/A' }}</div>
                                <div class="pressure-rear">T: {{ $spec['rear_normal'] ?? 'N/A' }}</div>
                            </div>
                        </td>
                        <td class="pressure-cell">
                            <div class="pressure-values">
                                <div class="pressure-front loaded">D: {{ $spec['front_loaded'] ?? 'N/A' }}</div>
                                <div class="pressure-rear loaded">T: {{ $spec['rear_loaded'] ?? 'N/A' }}</div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
    
    <!-- Especificações Detalhadas dos Pneus -->
    @if(!empty($defaultTireSpecs))
    <div class="detailed-specs">
        <h3 class="specs-title">🔍 Especificações Técnicas Detalhadas</h3>
        <div class="specs-grid">
            @if(!empty($defaultTireSpecs['front_tire']))
            <div class="tire-detail-card front">
                <div class="tire-detail-header">
                    <span class="tire-icon">🔝</span>
                    <span class="tire-position">Pneu Dianteiro</span>
                </div>
                <div class="tire-detail-specs">
                    @if(!empty($defaultTireSpecs['front_tire']['size']))
                    <div class="spec-row">
                        <span class="spec-label">Medida:</span>
                        <span class="spec-value">{{ $defaultTireSpecs['front_tire']['size'] }}</span>
                    </div>
                    @endif
                    @if(!empty($defaultTireSpecs['front_tire']['load_index']))
                    <div class="spec-row">
                        <span class="spec-label">Índice de Carga:</span>
                        <span class="spec-value">{{ $defaultTireSpecs['front_tire']['load_index'] }}</span>
                    </div>
                    @endif
                    @if(!empty($defaultTireSpecs['front_tire']['speed_rating']))
                    <div class="spec-row">
                        <span class="spec-label">Índice de Velocidade:</span>
                        <span class="spec-value">{{ $defaultTireSpecs['front_tire']['speed_rating'] }}</span>
                    </div>
                    @endif
                    @if(!empty($defaultTireSpecs['front_tire']['type']))
                    <div class="spec-row">
                        <span class="spec-label">Tipo:</span>
                        <span class="spec-value">{{ $defaultTireSpecs['front_tire']['type'] }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif
            
            @if(!empty($defaultTireSpecs['rear_tire']))
            <div class="tire-detail-card rear">
                <div class="tire-detail-header">
                    <span class="tire-icon">🔙</span>
                    <span class="tire-position">Pneu Traseiro</span>
                </div>
                <div class="tire-detail-specs">
                    @if(!empty($defaultTireSpecs['rear_tire']['size']))
                    <div class="spec-row">
                        <span class="spec-label">Medida:</span>
                        <span class="spec-value">{{ $defaultTireSpecs['rear_tire']['size'] }}</span>
                    </div>
                    @endif
                    @if(!empty($defaultTireSpecs['rear_tire']['load_index']))
                    <div class="spec-row">
                        <span class="spec-label">Índice de Carga:</span>
                        <span class="spec-value">{{ $defaultTireSpecs['rear_tire']['load_index'] }}</span>
                    </div>
                    @endif
                    @if(!empty($defaultTireSpecs['rear_tire']['speed_rating']))
                    <div class="spec-row">
                        <span class="spec-label">Índice de Velocidade:</span>
                        <span class="spec-value">{{ $defaultTireSpecs['rear_tire']['speed_rating'] }}</span>
                    </div>
                    @endif
                    @if(!empty($defaultTireSpecs['rear_tire']['type']))
                    <div class="spec-row">
                        <span class="spec-label">Tipo:</span>
                        <span class="spec-value">{{ $defaultTireSpecs['rear_tire']['type'] }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif
    
    <!-- Guia de Índices -->
    <div class="indices-guide">
        <h4 class="guide-title">📚 Guia de Índices</h4>
        <div class="guide-grid">
            <div class="guide-card load">
                <div class="guide-header">
                    <span class="guide-icon">⚖️</span>
                    <span class="guide-name">Índice de Carga</span>
                </div>
                <div class="guide-content">
                    <div class="guide-desc">Capacidade máxima de peso suportado pelo pneu</div>
                    <div class="guide-examples">
                        <span class="example">60 = 250kg</span>
                        <span class="example">70 = 335kg</span>
                        <span class="example">75 = 387kg</span>
                    </div>
                </div>
            </div>
            
            <div class="guide-card speed">
                <div class="guide-header">
                    <span class="guide-icon">🏁</span>
                    <span class="guide-name">Índice de Velocidade</span>
                </div>
                <div class="guide-content">
                    <div class="guide-desc">Velocidade máxima segura do pneu</div>
                    <div class="guide-examples">
                        <span class="example">H = 210km/h</span>
                        <span class="example">V = 240km/h</span>
                        <span class="example">W = 270km/h</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Dicas por Categoria -->
    <div class="category-tips">
        <h4 class="tips-title">💡 Dicas por Categoria</h4>
        @if($motorcycleCategory === 'sport')
        <div class="tip-card sport">
            <div class="tip-icon">🏁</div>
            <div class="tip-content">
                <div class="tip-title">Motos Esportivas</div>
                <div class="tip-text">Require pneus com índices altos de velocidade (V, W, Y). Pressões mais altas para performance.</div>
            </div>
        </div>
        @elseif($motorcycleCategory === 'touring')
        <div class="tip-card touring">
            <div class="tip-icon">🛣️</div>
            <div class="tip-content">
                <div class="tip-title">Motos Touring</div>
                <div class="tip-text">Foco no conforto e durabilidade. Índices de carga altos para bagagens.</div>
            </div>
        </div>
        @elseif($motorcycleCategory === 'naked')
        <div class="tip-card naked">
            <div class="tip-icon">⚡</div>
            <div class="tip-content">
                <div class="tip-title">Motos Naked</div>
                <div class="tip-text">Equilíbrio entre performance e uso urbano. Índices médios adequados.</div>
            </div>
        </div>
        @else
        <div class="tip-card standard">
            <div class="tip-icon">🔧</div>
            <div class="tip-content">
                <div class="tip-title">Uso Padrão</div>
                <div class="tip-text">Priorize economia e durabilidade. Siga sempre as especificações do manual.</div>
            </div>
        </div>
        @endif
    </div>
    
    <!-- Observações Importantes -->
    @if(!empty($defaultTireSpecs['observation']) || !empty($tireSpecs[0]['observation']))
    <div class="observations">
        <div class="obs-header">
            <span class="obs-icon">📌</span>
            <span class="obs-title">Observações Importantes</span>
        </div>
        <div class="obs-content">
            {{ $defaultTireSpecs['observation'] ?? $tireSpecs[0]['observation'] ?? '' }}
        </div>
    </div>
    @endif
</section>
@endif

<style>
/* Seção principal */
.tire-specs-section {
    margin: 24px 0;
    padding: 20px;
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    border-radius: 16px;
    border: 2px solid #64748b;
}

.section-title {
    font-size: 20px;
    font-weight: 700;
    color: #334155;
    margin-bottom: 20px;
    text-align: center;
    border-bottom: 3px solid #64748b;
    padding-bottom: 8px;
}

/* Informações do veículo */
.vehicle-info-card {
    margin-bottom: 24px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #e2e8f0;
}

.vehicle-header {
    display: flex;
    align-items: center;
    gap: 12px;
}

.vehicle-icon {
    font-size: 24px;
}

.vehicle-details {
    flex: 1;
}

.vehicle-name {
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 4px;
}

.vehicle-category {
    display: flex;
    align-items: center;
}

.category-badge {
    font-size: 11px;
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 12px;
    color: white;
}

.category-badge.sport {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
}

.category-badge.touring {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
}

.category-badge.naked {
    background: linear-gradient(135deg, #16a34a, #15803d);
}

.category-badge.cruiser {
    background: linear-gradient(135deg, #7c3aed, #6d28d9);
}

.category-badge.standard {
    background: linear-gradient(135deg, #64748b, #475569);
}

/* Tabela de especificações */
.specs-table-container {
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

.specs-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    font-size: 12px;
}

.specs-table th {
    background: linear-gradient(135deg, #64748b, #475569);
    color: white;
    padding: 10px 6px;
    text-align: center;
    font-weight: 600;
    font-size: 11px;
}

.specs-table td {
    padding: 8px 6px;
    text-align: center;
    border-bottom: 1px solid #f1f5f9;
}

.specs-table tr:nth-child(even) {
    background-color: #f8fafc;
}

.specs-table tr.main-version {
    background: linear-gradient(135deg, #fef3c7, #fde68a) !important;
}

.version-cell {
    text-align: left !important;
}

.version-info {
    display: flex;
    align-items: center;
    gap: 8px;
}

.version-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}

.version-indicator.main {
    background: #dc2626;
}

.version-indicator.alt {
    background: #6b7280;
}

.version-content {
    flex: 1;
}

.version-name {
    font-size: 12px;
    font-weight: 600;
    color: #374151;
}

.version-label {
    font-size: 9px;
    color: #dc2626;
    font-weight: 500;
}

.tire-cell {
    text-align: center;
}

.tire-size {
    font-size: 11px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 2px;
}

.tire-type {
    font-size: 9px;
    color: #6b7280;
}

.pressure-cell {
    text-align: center;
}

.pressure-values {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.pressure-front,
.pressure-rear {
    font-size: 10px;
    font-weight: 600;
    padding: 2px 4px;
    border-radius: 3px;
}

.pressure-front {
    background: #dbeafe;
    color: #1e40af;
}

.pressure-rear {
    background: #dcfce7;
    color: #166534;
}

.pressure-front.loaded,
.pressure-rear.loaded {
    background: #fef3c7;
    color: #92400e;
}

/* Especificações detalhadas */
.detailed-specs {
    margin-bottom: 24px;
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
    grid-template-columns: 1fr;
    gap: 12px;
}

.tire-detail-card {
    background: white;
    border-radius: 10px;
    padding: 16px;
    border: 2px solid;
}

.tire-detail-card.front {
    border-color: #3b82f6;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
}

.tire-detail-card.rear {
    border-color: #16a34a;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
}

.tire-detail-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
}

.tire-icon {
    font-size: 16px;
}

.tire-position {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
}

.tire-detail-specs {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.spec-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px;
    background: rgba(255, 255, 255, 0.5);
    border-radius: 6px;
}

.spec-label {
    font-size: 11px;
    color: #6b7280;
    font-weight: 500;
}

.spec-value {
    font-size: 12px;
    color: #1f2937;
    font-weight: 600;
}

/* Guia de índices */
.indices-guide {
    margin-bottom: 24px;
}

.guide-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.guide-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}

.guide-card {
    background: white;
    border-radius: 8px;
    padding: 12px;
    border: 1px solid #e5e7eb;
}

.guide-header {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
}

.guide-icon {
    font-size: 14px;
}

.guide-name {
    font-size: 12px;
    font-weight: 600;
    color: #374151;
}

.guide-content {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.guide-desc {
    font-size: 10px;
    color: #6b7280;
    line-height: 1.3;
}

.guide-examples {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.example {
    font-size: 9px;
    padding: 2px 6px;
    background: #f3f4f6;
    border-radius: 4px;
    color: #374151;
    font-weight: 500;
}

/* Dicas por categoria */
.category-tips {
    margin-bottom: 24px;
}

.tips-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.tip-card {
    background: white;
    border-radius: 10px;
    padding: 12px;
    border: 2px solid;
    display: flex;
    align-items: center;
    gap: 12px;
}

.tip-card.sport {
    border-color: #dc2626;
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
}

.tip-card.touring {
    border-color: #2563eb;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
}

.tip-card.naked {
    border-color: #16a34a;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
}

.tip-card.standard {
    border-color: #64748b;
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
}

.tip-icon {
    font-size: 20px;
    flex-shrink: 0;
}

.tip-content {
    flex: 1;
}

.tip-title {
    font-size: 12px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 4px;
}

.tip-text {
    font-size: 11px;
    color: #6b7280;
    line-height: 1.3;
}

/* Observações */
.observations {
    background: white;
    border-radius: 10px;
    padding: 16px;
    border: 1px solid #f59e0b;
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
}

.obs-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}

.obs-icon {
    font-size: 16px;
    color: #f59e0b;
}

.obs-title {
    font-size: 14px;
    font-weight: 600;
    color: #92400e;
}

.obs-content {
    font-size: 12px;
    color: #451a03;
    line-height: 1.4;
}

/* Responsividade mobile */
@media (max-width: 768px) {
    .tire-specs-section {
        padding: 16px;
        margin: 16px 0;
    }
    
    .section-title {
        font-size: 18px;
    }
    
    .specs-table th,
    .specs-table td {
        padding: 6px 4px;
        font-size: 10px;
    }
    
    .vehicle-header {
        gap: 8px;
    }
    
    .vehicle-icon {
        font-size: 20px;
    }
    
    .vehicle-name {
        font-size: 14px;
    }
    
    .tire-detail-card {
        padding: 12px;
    }
    
    .guide-grid {
        gap: 8px;
    }
    
    .tip-card {
        gap: 8px;
        padding: 10px;
    }
}
</style>