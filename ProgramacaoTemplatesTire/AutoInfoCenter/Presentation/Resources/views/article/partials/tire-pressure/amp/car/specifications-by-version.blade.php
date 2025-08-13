{{-- 
Partial: tire-pressure/amp/car/specifications-by-version.blade.php
Especificações detalhadas por versão do carro - AMP
Versão AMP com tabela completa de versões, pneus e pressões
--}}

@php
    $tireSpecsByVersion = $article->getData()['tire_specifications_by_version'] ?? [];
    $vehicleInfo = $article->getData()['vehicle_info'] ?? [];
    $carCategory = $vehicleInfo['category'] ?? 'sedan';
    $defaultTireSpecs = $article->getData()['tire_specifications'] ?? [];
@endphp

@if(!empty($tireSpecsByVersion) || !empty($defaultTireSpecs))
<section class="car-specs-by-version-section">
    <h2 class="section-title">🚗 Especificações por Versão</h2>
    
    <!-- Informações do Veículo -->
    <div class="vehicle-summary-card">
        <div class="summary-header">
            <div class="vehicle-icon">🚗</div>
            <div class="vehicle-info">
                <div class="vehicle-name">{{ $vehicleInfo['full_name'] ?? 'Veículo' }}</div>
                <div class="vehicle-category">
                    @if($carCategory === 'hatch')
                    <span class="category-badge hatch">🚙 Hatchback</span>
                    @elseif($carCategory === 'sedan')
                    <span class="category-badge sedan">🚘 Sedan</span>
                    @elseif($carCategory === 'suv')
                    <span class="category-badge suv">🚐 SUV</span>
                    @elseif($carCategory === 'pickup')
                    <span class="category-badge pickup">🛻 Pick-up</span>
                    @elseif($carCategory === 'wagon')
                    <span class="category-badge wagon">🚛 Perua</span>
                    @else
                    <span class="category-badge standard">🚗 Padrão</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabela Principal de Especificações -->
    @if(!empty($tireSpecsByVersion))
    <div class="specs-table-container">
        <h3 class="table-title">📊 Tabela Completa por Versão</h3>
        <div class="table-wrapper">
            <table class="specs-table">
                <thead>
                    <tr>
                        <th>Versão</th>
                        <th>Pneu</th>
                        <th>Estepe</th>
                        <th>Vazio</th>
                        <th>Carregado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tireSpecsByVersion as $index => $spec)
                    <tr class="{{ $index === 0 ? 'main-version' : 'alt-version' }}">
                        <!-- Versão -->
                        <td class="version-cell">
                            <div class="version-info">
                                @if($index === 0)
                                <div class="version-indicator main"></div>
                                @else
                                <div class="version-indicator alt"></div>
                                @endif
                                <div class="version-content">
                                    <div class="version-name">{{ $spec['version'] ?? 'Versão ' . ($index + 1) }}</div>
                                    @if($index === 0)
                                    <div class="version-label">Principal</div>
                                    @endif
                                    @if(!empty($spec['trim_level']))
                                    <div class="trim-level">{{ $spec['trim_level'] }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        
                        <!-- Pneu Principal -->
                        <td class="tire-cell">
                            <div class="tire-info">
                                <div class="tire-size">{{ $spec['tire_size'] ?? $spec['front_tire_size'] ?? 'N/A' }}</div>
                                @if(!empty($spec['rim_size']))
                                <div class="rim-size">Aro {{ $spec['rim_size'] }}</div>
                                @endif
                                @if(!empty($spec['load_speed_index']))
                                <div class="load-speed">{{ $spec['load_speed_index'] }}</div>
                                @endif
                            </div>
                        </td>
                        
                        <!-- Estepe -->
                        <td class="spare-cell">
                            <div class="spare-info">
                                @if(!empty($spec['spare_tire_size']))
                                <div class="spare-size">{{ $spec['spare_tire_size'] }}</div>
                                <div class="spare-pressure">{{ $spec['spare_pressure'] ?? '60 PSI' }}</div>
                                @else
                                <div class="spare-size">Mesmo tamanho</div>
                                <div class="spare-pressure">{{ $spec['spare_pressure'] ?? '60 PSI' }}</div>
                                @endif
                            </div>
                        </td>
                        
                        <!-- Pressão Vazio -->
                        <td class="pressure-cell">
                            <div class="pressure-values empty">
                                <div class="pressure-front">D: {{ $spec['front_normal'] ?? $spec['pressure_front'] ?? 'N/A' }}</div>
                                <div class="pressure-rear">T: {{ $spec['rear_normal'] ?? $spec['pressure_rear'] ?? 'N/A' }}</div>
                            </div>
                        </td>
                        
                        <!-- Pressão Carregado -->
                        <td class="pressure-cell">
                            <div class="pressure-values loaded">
                                <div class="pressure-front">D: {{ $spec['front_loaded'] ?? $spec['pressure_front_loaded'] ?? $spec['front_normal'] ?? 'N/A' }}</div>
                                <div class="pressure-rear">T: {{ $spec['rear_loaded'] ?? $spec['pressure_rear_loaded'] ?? $spec['rear_normal'] ?? 'N/A' }}</div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
    
    <!-- Cards de Versões Detalhadas -->
    <div class="version-details">
        <h3 class="details-title">🔍 Detalhes por Versão</h3>
        <div class="version-cards">
            @foreach(array_slice($tireSpecsByVersion, 0, 3) as $index => $spec)
            <div class="version-card {{ $index === 0 ? 'featured' : 'standard' }}">
                <div class="card-header">
                    @if($index === 0)
                    <span class="card-badge main">⭐ Principal</span>
                    @else
                    <span class="card-badge alt">📋 Alternativa</span>
                    @endif
                    <div class="card-version">{{ $spec['version'] ?? 'Versão ' . ($index + 1) }}</div>
                </div>
                
                <div class="card-content">
                    <!-- Especificações do Pneu -->
                    <div class="spec-group tire-specs">
                        <div class="spec-header">
                            <span class="spec-icon">🛞</span>
                            <span class="spec-title">Pneu</span>
                        </div>
                        <div class="spec-details">
                            <div class="spec-row">
                                <span class="spec-label">Medida:</span>
                                <span class="spec-value">{{ $spec['tire_size'] ?? $spec['front_tire_size'] ?? 'N/A' }}</span>
                            </div>
                            @if(!empty($spec['rim_size']))
                            <div class="spec-row">
                                <span class="spec-label">Aro:</span>
                                <span class="spec-value">{{ $spec['rim_size'] }}</span>
                            </div>
                            @endif
                            @if(!empty($spec['load_speed_index']))
                            <div class="spec-row">
                                <span class="spec-label">Índice:</span>
                                <span class="spec-value">{{ $spec['load_speed_index'] }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Pressões Recomendadas -->
                    <div class="spec-group pressure-specs">
                        <div class="spec-header">
                            <span class="spec-icon">📊</span>
                            <span class="spec-title">Pressões</span>
                        </div>
                        <div class="pressure-comparison">
                            <div class="pressure-scenario empty">
                                <div class="scenario-label">Vazio</div>
                                <div class="scenario-values">
                                    <span class="front-pressure">{{ $spec['front_normal'] ?? $spec['pressure_front'] ?? 'N/A' }}</span>
                                    <span class="rear-pressure">{{ $spec['rear_normal'] ?? $spec['pressure_rear'] ?? 'N/A' }}</span>
                                </div>
                            </div>
                            <div class="pressure-scenario full">
                                <div class="scenario-label">Carregado</div>
                                <div class="scenario-values">
                                    <span class="front-pressure">{{ $spec['front_loaded'] ?? $spec['pressure_front_loaded'] ?? 'N/A' }}</span>
                                    <span class="rear-pressure">{{ $spec['rear_loaded'] ?? $spec['pressure_rear_loaded'] ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Estepe -->
                    @if(!empty($spec['spare_tire_size']) || !empty($spec['spare_pressure']))
                    <div class="spec-group spare-specs">
                        <div class="spec-header">
                            <span class="spec-icon">🔄</span>
                            <span class="spec-title">Estepe</span>
                        </div>
                        <div class="spec-details">
                            <div class="spec-row">
                                <span class="spec-label">Medida:</span>
                                <span class="spec-value">{{ $spec['spare_tire_size'] ?? 'Igual aos pneus' }}</span>
                            </div>
                            <div class="spec-row">
                                <span class="spec-label">Pressão:</span>
                                <span class="spec-value">{{ $spec['spare_pressure'] ?? '60 PSI' }}</span>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    
    <!-- Diferenças entre Versões -->
    @if(count($tireSpecsByVersion) > 1)
    <div class="version-differences">
        <h4 class="diff-title">🔄 Principais Diferenças</h4>
        <div class="diff-comparison">
            <div class="diff-category tire-sizes">
                <div class="diff-header">
                    <span class="diff-icon">📏</span>
                    <span class="diff-name">Tamanhos de Pneu</span>
                </div>
                <div class="diff-items">
                    @foreach($tireSpecsByVersion as $spec)
                    @if(!empty($spec['tire_size']) || !empty($spec['front_tire_size']))
                    <div class="diff-item">
                        <span class="diff-version">{{ $spec['version'] ?? 'N/A' }}:</span>
                        <span class="diff-value">{{ $spec['tire_size'] ?? $spec['front_tire_size'] ?? 'N/A' }}</span>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
            
            <div class="diff-category rim-sizes">
                <div class="diff-header">
                    <span class="diff-icon">⭕</span>
                    <span class="diff-name">Aros</span>
                </div>
                <div class="diff-items">
                    @foreach($tireSpecsByVersion as $spec)
                    @if(!empty($spec['rim_size']))
                    <div class="diff-item">
                        <span class="diff-version">{{ $spec['version'] ?? 'N/A' }}:</span>
                        <span class="diff-value">{{ $spec['rim_size'] }}</span>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
            
            <div class="diff-category pressure-ranges">
                <div class="diff-header">
                    <span class="diff-icon">📈</span>
                    <span class="diff-name">Pressões</span>
                </div>
                <div class="diff-items">
                    @foreach($tireSpecsByVersion as $spec)
                    @if(!empty($spec['front_normal']) || !empty($spec['pressure_front']))
                    <div class="diff-item">
                        <span class="diff-version">{{ $spec['version'] ?? 'N/A' }}:</span>
                        <span class="diff-value">{{ $spec['front_normal'] ?? $spec['pressure_front'] ?? 'N/A' }}/{{ $spec['rear_normal'] ?? $spec['pressure_rear'] ?? 'N/A' }}</span>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Guia de Interpretação -->
    <div class="interpretation-guide">
        <h4 class="guide-title">📖 Como Interpretar</h4>
        <div class="guide-sections">
            <div class="guide-section tire-reading">
                <div class="guide-header">
                    <span class="guide-icon">🔍</span>
                    <span class="guide-name">Lendo o Pneu</span>
                </div>
                <div class="guide-content">
                    <div class="example-tire">195/65 R15 91H</div>
                    <div class="tire-breakdown">
                        <div class="breakdown-item">
                            <span class="breakdown-part">195</span>
                            <span class="breakdown-desc">Largura (mm)</span>
                        </div>
                        <div class="breakdown-item">
                            <span class="breakdown-part">65</span>
                            <span class="breakdown-desc">Perfil (%)</span>
                        </div>
                        <div class="breakdown-item">
                            <span class="breakdown-part">R15</span>
                            <span class="breakdown-desc">Aro 15"</span>
                        </div>
                        <div class="breakdown-item">
                            <span class="breakdown-part">91H</span>
                            <span class="breakdown-desc">Carga/Velocidade</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="guide-section pressure-reading">
                <div class="guide-header">
                    <span class="guide-icon">📊</span>
                    <span class="guide-name">Pressões D/T</span>
                </div>
                <div class="guide-content">
                    <div class="pressure-explanation">
                        <div class="pressure-demo">32/30 PSI</div>
                        <div class="pressure-breakdown">
                            <div class="breakdown-item">
                                <span class="breakdown-part">32</span>
                                <span class="breakdown-desc">Dianteiro</span>
                            </div>
                            <div class="breakdown-item">
                                <span class="breakdown-part">30</span>
                                <span class="breakdown-desc">Traseiro</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Dicas Importantes -->
    <div class="important-tips">
        <h4 class="tips-title">💡 Dicas Importantes</h4>
        <div class="tips-grid">
            <div class="tip-item version-tip">
                <div class="tip-icon">🎯</div>
                <div class="tip-content">
                    <div class="tip-title">Versão Correta</div>
                    <div class="tip-text">Sempre confirme a versão exata do seu veículo no manual ou documento</div>
                </div>
            </div>
            
            <div class="tip-item upgrade-tip">
                <div class="tip-icon">⬆️</div>
                <div class="tip-content">
                    <div class="tip-title">Upgrade de Pneus</div>
                    <div class="tip-text">Pneus maiores podem exigir ajustes na pressão recomendada</div>
                </div>
            </div>
            
            <div class="tip-item spare-tip">
                <div class="tip-icon">🔄</div>
                <div class="tip-content">
                    <div class="tip-title">Estepe</div>
                    <div class="tip-text">Sempre mantenha o estepe com maior pressão (geralmente 60 PSI)</div>
                </div>
            </div>
            
            <div class="tip-item load-tip">
                <div class="tip-icon">⚖️</div>
                <div class="tip-content">
                    <div class="tip-title">Carga</div>
                    <div class="tip-text">Ajuste a pressão conforme o número de passageiros e bagagem</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Observação Final -->
    @if(!empty($defaultTireSpecs['observation']) || !empty($tireSpecsByVersion[0]['observation']))
    <div class="final-observation">
        <div class="obs-header">
            <span class="obs-icon">📋</span>
            <span class="obs-title">Observação Importante</span>
        </div>
        <div class="obs-content">
            {{ $defaultTireSpecs['observation'] ?? $tireSpecsByVersion[0]['observation'] ?? 'Sempre consulte o manual do proprietário para informações específicas da sua versão.' }}
        </div>
    </div>
    @endif
</section>
@endif

<style>
/* Seção principal */
.car-specs-by-version-section {
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
.vehicle-summary-card {
    margin-bottom: 24px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #e5e7eb;
}

.summary-header {
    display: flex;
    align-items: center;
    gap: 12px;
}

.vehicle-icon {
    font-size: 24px;
}

.vehicle-info {
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
    border-radius: 10px;
    color: white;
}

.category-badge.hatch {
    background: linear-gradient(135deg, #16a34a, #15803d);
}

.category-badge.sedan {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
}

.category-badge.suv {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
}

.category-badge.pickup {
    background: linear-gradient(135deg, #f59e0b, #d97706);
}

.category-badge.wagon {
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
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
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
    background: linear-gradient(135deg, #dbeafe, #bfdbfe) !important;
}

.version-cell {
    text-align: left !important;
}

.version-info {
    display: flex;
    align-items: center;
    gap: 6px;
}

.version-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}

.version-indicator.main {
    background: #2563eb;
}

.version-indicator.alt {
    background: #6b7280;
}

.version-content {
    flex: 1;
}

.version-name {
    font-size: 11px;
    font-weight: 600;
    color: #374151;
}

.version-label {
    font-size: 8px;
    color: #2563eb;
    font-weight: 500;
}

.trim-level {
    font-size: 8px;
    color: #6b7280;
}

.tire-cell,
.spare-cell {
    text-align: center;
}

.tire-info,
.spare-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.tire-size,
.spare-size {
    font-size: 11px;
    font-weight: 600;
    color: #1f2937;
}

.rim-size,
.spare-pressure {
    font-size: 9px;
    color: #6b7280;
}

.load-speed {
    font-size: 8px;
    color: #3b82f6;
    font-weight: 500;
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

.pressure-values.empty .pressure-front {
    background: #dcfce7;
    color: #166534;
}

.pressure-values.empty .pressure-rear {
    background: #dbeafe;
    color: #1e40af;
}

.pressure-values.loaded .pressure-front,
.pressure-values.loaded .pressure-rear {
    background: #fef3c7;
    color: #92400e;
}

/* Cards de versões */
.version-details {
    margin-bottom: 24px;
}

.details-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.version-cards {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}

.version-card {
    background: white;
    border-radius: 10px;
    padding: 14px;
    border: 2px solid;
}

.version-card.featured {
    border-color: #2563eb;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
}

.version-card.standard {
    border-color: #6b7280;
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
}

.card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}

.card-badge {
    font-size: 9px;
    font-weight: 600;
    padding: 3px 6px;
    border-radius: 8px;
    color: white;
}

.card-badge.main {
    background: #2563eb;
}

.card-badge.alt {
    background: #6b7280;
}

.card-version {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
}

.card-content {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.spec-group {
    background: rgba(255, 255, 255, 0.5);
    border-radius: 6px;
    padding: 8px;
}

.spec-header {
    display: flex;
    align-items: center;
    gap: 4px;
    margin-bottom: 6px;
}

.spec-icon {
    font-size: 12px;
}

.spec-title {
    font-size: 11px;
    font-weight: 600;
    color: #374151;
}

.spec-details {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.spec-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.spec-label {
    font-size: 9px;
    color: #6b7280;
}

.spec-value {
    font-size: 10px;
    color: #1f2937;
    font-weight: 600;
}

.pressure-comparison {
    display: flex;
    justify-content: space-between;
    gap: 8px;
}

.pressure-scenario {
    flex: 1;
    text-align: center;
}

.scenario-label {
    font-size: 9px;
    color: #6b7280;
    margin-bottom: 4px;
}

.scenario-values {
    display: flex;
    justify-content: center;
    gap: 4px;
}

.front-pressure,
.rear-pressure {
    font-size: 10px;
    font-weight: 600;
    padding: 2px 4px;
    border-radius: 3px;
    background: #e5e7eb;
    color: #374151;
}

/* Diferenças entre versões */
.version-differences {
    margin-bottom: 24px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #e5e7eb;
}

.diff-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.diff-comparison {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}

.diff-category {
    background: #f8fafc;
    border-radius: 6px;
    padding: 10px;
}

.diff-header {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 6px;
}

.diff-icon {
    font-size: 12px;
    color: #2563eb;
}

.diff-name {
    font-size: 11px;
    font-weight: 600;
    color: #374151;
}

.diff-items {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.diff-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 10px;
}

.diff-version {
    color: #6b7280;
}

.diff-value {
    color: #1f2937;
    font-weight: 600;
}

/* Guia de interpretação */
.interpretation-guide {
    margin-bottom: 24px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #e5e7eb;
}

.guide-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.guide-sections {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}

.guide-section {
    background: #f8fafc;
    border-radius: 8px;
    padding: 12px;
}

.guide-header {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
}

.guide-icon {
    font-size: 14px;
    color: #2563eb;
}

.guide-name {
    font-size: 12px;
    font-weight: 600;
    color: #374151;
}

.guide-content {
    text-align: center;
}

.example-tire {
    font-size: 16px;
    font-weight: 700;
    color: #2563eb;
    margin-bottom: 8px;
}

.pressure-demo {
    font-size: 16px;
    font-weight: 700;
    color: #16a34a;
    margin-bottom: 8px;
}

.tire-breakdown,
.pressure-breakdown {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4px;
}

.breakdown-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 4px;
    background: white;
    border-radius: 4px;
}

.breakdown-part {
    font-size: 11px;
    font-weight: 700;
    color: #1f2937;
}

.breakdown-desc {
    font-size: 8px;
    color: #6b7280;
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

.tip-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px;
    background: white;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
}

.tip-icon {
    font-size: 14px;
    color: #2563eb;
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

/* Observação final */
.final-observation {
    background: white;
    border-radius: 10px;
    padding: 14px;
    border: 2px solid #f59e0b;
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
    font-size: 11px;
    color: #451a03;
    line-height: 1.4;
}

/* Responsividade mobile */
@media (max-width: 768px) {
    .car-specs-by-version-section {
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
    
    .version-cards {
        gap: 8px;
    }
    
    .version-card {
        padding: 10px;
    }
    
    .card-content {
        gap: 8px;
    }
    
    .spec-group {
        padding: 6px;
    }
    
    .pressure-comparison {
        gap: 6px;
    }
    
    .diff-comparison {
        gap: 8px;
    }
    
    .guide-sections {
        gap: 8px;
    }
    
    .tire-breakdown,
    .pressure-breakdown {
        grid-template-columns: 1fr;
        gap: 2px;
    }
    
    .tips-grid {
        grid-template-columns: 1fr;
        gap: 6px;
    }
    
    .tip-item {
        gap: 6px;
        padding: 8px;
    }
    
    .example-tire,
    .pressure-demo {
        font-size: 14px;
    }
}

/* Melhorias para tabela em mobile */
@media (max-width: 480px) {
    .specs-table {
        font-size: 10px;
    }
    
    .specs-table th,
    .specs-table td {
        padding: 4px 2px;
    }
    
    .version-name {
        font-size: 10px;
    }
    
    .tire-size,
    .spare-size {
        font-size: 10px;
    }
    
    .pressure-front,
    .pressure-rear {
        font-size: 9px;
        padding: 1px 3px;
    }
    
    .card-version {
        font-size: 12px;
    }
    
    .spec-title {
        font-size: 10px;
    }
    
    .spec-label,
    .spec-value {
        font-size: 8px;
    }
}
</style>