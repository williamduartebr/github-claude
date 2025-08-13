{{-- 
Partial: tire-pressure/amp/car/pressure-conversion.blade.php
Conversão de unidades para carros - AMP OTIMIZADO
Versão AMP focada em pressões típicas de carros e conversões práticas
--}}

@php
    $conversionData = $article->getData()['unit_conversion'] ?? [];
    $vehicleInfo = $article->getData()['vehicle_info'] ?? [];
    $tireSpecs = $article->getData()['tire_specifications_by_version'] ?? [];
    
    // Pressão de referência do veículo (carros geralmente usam pressões menores que motos)
    $mainSpec = $tireSpecs[0] ?? [];
    $referencePressure = $mainSpec['front_normal'] ?? $vehicleInfo['pressure_front'] ?? '32 PSI';
    $referencePsi = (int) str_replace([' PSI', ' psi'], '', $referencePressure);
@endphp

<section class="car-conversion-section">
    <h2 class="section-title">🔄 Conversão de Unidades</h2>
    
    <!-- Conversão da Pressão do Seu Carro -->
    <div class="reference-conversion">
        <div class="ref-header">
            <span class="ref-icon">🚗</span>
            <span class="ref-title">Seu Veículo</span>
        </div>
        <div class="ref-pressure">{{ $referencePressure }}</div>
        <div class="ref-conversions">
            <div class="conversion-item">
                <div class="conversion-label">kgf/cm²</div>
                <div class="conversion-value">{{ number_format($referencePsi * 0.070307, 2) }}</div>
            </div>
            <div class="conversion-item">
                <div class="conversion-label">Bar</div>
                <div class="conversion-value">{{ number_format($referencePsi * 0.0689476, 2) }}</div>
            </div>
            <div class="conversion-item">
                <div class="conversion-label">kPa</div>
                <div class="conversion-value">{{ number_format($referencePsi * 6.89476, 0) }}</div>
            </div>
        </div>
    </div>
    
    <!-- Tabela de Conversão para Carros -->
    <div class="conversion-table-container">
        <h3 class="table-title">📊 Conversões Comuns para Carros</h3>
        <div class="table-wrapper">
            <table class="conversion-table">
                <thead>
                    <tr>
                        <th>PSI</th>
                        <th>Bar</th>
                        <th>kgf/cm²</th>
                        <th>Uso Típico</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $carPressures = [
                            ['psi' => 28, 'use' => 'Carros pequenos'],
                            ['psi' => 30, 'use' => 'Compactos'],
                            ['psi' => 32, 'use' => 'Sedãs médios'],
                            ['psi' => 34, 'use' => 'SUVs leves'],
                            ['psi' => 36, 'use' => 'Carga completa'],
                            ['psi' => 38, 'use' => 'SUVs grandes'],
                            ['psi' => 40, 'use' => 'Veículos pesados'],
                            ['psi' => 42, 'use' => 'Carga máxima']
                        ];
                    @endphp
                    
                    @foreach($carPressures as $pressure)
                    @php
                        $psi = $pressure['psi'];
                        $bar = number_format($psi * 0.0689476, 2);
                        $kgf = number_format($psi * 0.070307, 2);
                        $isReference = $psi == $referencePsi;
                    @endphp
                    <tr class="{{ $isReference ? 'highlight-row' : '' }}">
                        <td class="{{ $isReference ? 'highlight-psi' : '' }}">{{ $psi }}</td>
                        <td>{{ $bar }}</td>
                        <td>{{ $kgf }}</td>
                        <td class="use-cell">{{ $pressure['use'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Fórmulas Essenciais -->
    <div class="essential-formulas">
        <h3 class="formulas-title">⚡ Fórmulas Essenciais</h3>
        <div class="formulas-grid">
            <div class="formula-card main">
                <div class="formula-header">
                    <span class="formula-icon">🇧🇷</span>
                    <span class="formula-name">PSI → kgf/cm²</span>
                </div>
                <div class="formula-calculation">PSI × 0.070</div>
                <div class="formula-example">{{ $referencePsi }} PSI = {{ number_format($referencePsi * 0.070307, 2) }} kgf/cm²</div>
                <div class="formula-note">Conversão mais usada no Brasil</div>
            </div>
            
            <div class="formula-card secondary">
                <div class="formula-header">
                    <span class="formula-icon">🇪🇺</span>
                    <span class="formula-name">PSI → Bar</span>
                </div>
                <div class="formula-calculation">PSI × 0.069</div>
                <div class="formula-example">{{ $referencePsi }} PSI = {{ number_format($referencePsi * 0.0689476, 2) }} Bar</div>
                <div class="formula-note">Padrão europeu</div>
            </div>
        </div>
    </div>
    
    <!-- Conversões Rápidas -->
    <div class="quick-conversions">
        <h4 class="quick-title">⚡ Conversões Rápidas</h4>
        <div class="quick-grid">
            @php
                $quickValues = [28, 30, 32, 34, 36, 38, 40, 42];
            @endphp
            @foreach($quickValues as $psi)
            <div class="quick-item {{ $psi == $referencePsi ? 'highlight' : '' }}">
                <div class="quick-psi">{{ $psi }} PSI</div>
                <div class="quick-conversions-values">
                    <div class="quick-value">{{ number_format($psi * 0.0689476, 2) }} Bar</div>
                    <div class="quick-value">{{ number_format($psi * 0.070307, 2) }} kgf/cm²</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    
    <!-- Diferenças por Região -->
    <div class="regional-standards">
        <h4 class="regional-title">🌍 Padrões por Região</h4>
        <div class="standards-grid">
            <div class="standard-card brazil">
                <div class="standard-flag">🇧🇷</div>
                <div class="standard-info">
                    <div class="standard-name">Brasil</div>
                    <div class="standard-unit">PSI</div>
                    <div class="standard-range">28-40 PSI</div>
                    <div class="standard-note">Padrão oficial</div>
                </div>
            </div>
            
            <div class="standard-card europe">
                <div class="standard-flag">🇪🇺</div>
                <div class="standard-info">
                    <div class="standard-name">Europa</div>
                    <div class="standard-unit">Bar</div>
                    <div class="standard-range">1.9-2.8 Bar</div>
                    <div class="standard-note">Sistema métrico</div>
                </div>
            </div>
            
            <div class="standard-card old-brazil">
                <div class="standard-flag">📜</div>
                <div class="standard-info">
                    <div class="standard-name">Brasil Antigo</div>
                    <div class="standard-unit">kgf/cm²</div>
                    <div class="standard-range">2.0-2.8 kgf/cm²</div>
                    <div class="standard-note">Ainda usado</div>
                </div>
            </div>
            
            <div class="standard-card technical">
                <div class="standard-flag">🔬</div>
                <div class="standard-info">
                    <div class="standard-name">Técnico</div>
                    <div class="standard-unit">kPa</div>
                    <div class="standard-range">190-280 kPa</div>
                    <div class="standard-note">Internacional</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Dicas Específicas para Carros -->
    <div class="car-specific-tips">
        <h4 class="tips-title">🚗 Dicas Específicas para Carros</h4>
        <div class="tips-list">
            <div class="tip-item important">
                <span class="tip-icon">⚠️</span>
                <span class="tip-text">Carros usam pressões menores que motos</span>
            </div>
            <div class="tip-item info">
                <span class="tip-icon">📏</span>
                <span class="tip-text">Estepe precisa de pressão maior (+10 PSI)</span>
            </div>
            <div class="tip-item success">
                <span class="tip-icon">✅</span>
                <span class="tip-text">Diferença máxima: 2 PSI entre pneus</span>
            </div>
            <div class="tip-item warning">
                <span class="tip-icon">🔄</span>
                <span class="tip-text">Ajustar conforme carga e passageiros</span>
            </div>
        </div>
    </div>
    
    <!-- Tabela de Referência por Categoria -->
    <div class="category-reference">
        <h4 class="category-title">🚙 Pressões por Categoria de Veículo</h4>
        <div class="category-table">
            <div class="category-row">
                <div class="category-type">
                    <span class="type-icon">🚗</span>
                    <span class="type-name">Hatch/Sedan</span>
                </div>
                <div class="category-pressure">28-32 PSI</div>
                <div class="category-note">Uso urbano</div>
            </div>
            
            <div class="category-row">
                <div class="category-type">
                    <span class="type-icon">🚙</span>
                    <span class="type-name">SUV Compacto</span>
                </div>
                <div class="category-pressure">30-34 PSI</div>
                <div class="category-note">Altura maior</div>
            </div>
            
            <div class="category-row">
                <div class="category-type">
                    <span class="type-icon">🚐</span>
                    <span class="type-name">SUV Grande</span>
                </div>
                <div class="category-pressure">34-38 PSI</div>
                <div class="category-note">Mais peso</div>
            </div>
            
            <div class="category-row">
                <div class="category-type">
                    <span class="type-icon">🚛</span>
                    <span class="type-name">Pickup/Van</span>
                </div>
                <div class="category-pressure">36-42 PSI</div>
                <div class="category-note">Carga pesada</div>
            </div>
        </div>
    </div>
</section>

<style>
/* Seção principal */
.car-conversion-section {
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

/* Conversão de referência */
.reference-conversion {
    margin-bottom: 24px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 2px solid #2563eb;
    text-align: center;
}

.ref-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-bottom: 8px;
}

.ref-icon {
    font-size: 18px;
    color: #2563eb;
}

.ref-title {
    font-size: 14px;
    font-weight: 600;
    color: #1e40af;
}

.ref-pressure {
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 12px;
}

.ref-conversions {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 8px;
}

.conversion-item {
    background: #f8fafc;
    border-radius: 6px;
    padding: 8px;
}

.conversion-label {
    font-size: 10px;
    color: #6b7280;
    margin-bottom: 2px;
}

.conversion-value {
    font-size: 14px;
    font-weight: 700;
    color: #2563eb;
}

/* Tabela de conversão */
.conversion-table-container {
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

.conversion-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    font-size: 12px;
    min-width: 400px;
}

.conversion-table th {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: white;
    padding: 10px 6px;
    text-align: center;
    font-weight: 600;
    font-size: 11px;
}

.conversion-table td {
    padding: 8px 6px;
    text-align: center;
    border-bottom: 1px solid #f1f5f9;
    font-weight: 500;
}

.conversion-table tr:nth-child(even) {
    background-color: #f8fafc;
}

.highlight-row {
    background: linear-gradient(135deg, #fef3c7, #fde68a) !important;
}

.highlight-psi {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: white;
    border-radius: 4px;
    font-weight: 700;
}

.use-cell {
    font-size: 10px;
    color: #6b7280;
}

/* Fórmulas essenciais */
.essential-formulas {
    margin-bottom: 24px;
}

.formulas-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.formulas-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}

.formula-card {
    background: white;
    border-radius: 10px;
    padding: 14px;
    border: 2px solid;
    text-align: center;
}

.formula-card.main {
    border-color: #16a34a;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
}

.formula-card.secondary {
    border-color: #7c3aed;
    background: linear-gradient(135deg, #f5f3ff, #ede9fe);
}

.formula-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin-bottom: 8px;
}

.formula-icon {
    font-size: 14px;
}

.formula-name {
    font-size: 12px;
    font-weight: 700;
    color: #374151;
}

.formula-calculation {
    font-size: 16px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 6px;
}

.formula-example {
    font-size: 11px;
    color: #6b7280;
    margin-bottom: 4px;
}

.formula-note {
    font-size: 9px;
    color: #16a34a;
    font-weight: 600;
}

/* Conversões rápidas */
.quick-conversions {
    margin-bottom: 24px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #e5e7eb;
}

.quick-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.quick-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.quick-item {
    background: #f8fafc;
    border-radius: 6px;
    padding: 8px;
    text-align: center;
    border: 1px solid #e2e8f0;
}

.quick-item.highlight {
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    border-color: #f59e0b;
}

.quick-psi {
    font-size: 12px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 4px;
}

.quick-conversions-values {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.quick-value {
    font-size: 9px;
    color: #6b7280;
    font-weight: 500;
}

/* Padrões regionais */
.regional-standards {
    margin-bottom: 24px;
}

.regional-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.standards-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.standard-card {
    background: white;
    border-radius: 8px;
    padding: 10px;
    border: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    gap: 8px;
}

.standard-flag {
    font-size: 14px;
    flex-shrink: 0;
}

.standard-info {
    flex: 1;
}

.standard-name {
    font-size: 10px;
    font-weight: 600;
    color: #374151;
}

.standard-unit {
    font-size: 11px;
    font-weight: 700;
    color: #1f2937;
}

.standard-range {
    font-size: 9px;
    color: #2563eb;
    font-weight: 600;
}

.standard-note {
    font-size: 8px;
    color: #6b7280;
}

/* Dicas específicas */
.car-specific-tips {
    margin-bottom: 24px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #e5e7eb;
}

.tips-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.tips-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.tip-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px;
    border-radius: 6px;
}

.tip-item.important {
    background: #fef2f2;
    color: #991b1b;
}

.tip-item.info {
    background: #eff6ff;
    color: #1e40af;
}

.tip-item.success {
    background: #f0fdf4;
    color: #166534;
}

.tip-item.warning {
    background: #fffbeb;
    color: #92400e;
}

.tip-icon {
    font-size: 12px;
    flex-shrink: 0;
}

.tip-text {
    font-size: 11px;
    font-weight: 500;
}

/* Categorias de veículos */
.category-reference {
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 2px solid #16a34a;
}

.category-title {
    font-size: 14px;
    font-weight: 600;
    color: #166534;
    margin-bottom: 12px;
    text-align: center;
}

.category-table {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.category-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px;
    background: #f8fafc;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
}

.category-type {
    display: flex;
    align-items: center;
    gap: 6px;
}

.type-icon {
    font-size: 14px;
}

.type-name {
    font-size: 11px;
    font-weight: 600;
    color: #374151;
}

.category-pressure {
    font-size: 12px;
    font-weight: 700;
    color: #16a34a;
}

.category-note {
    font-size: 9px;
    color: #6b7280;
}

/* Responsividade mobile */
@media (max-width: 768px) {
    .car-conversion-section {
        padding: 16px;
        margin: 16px 0;
    }
    
    .section-title {
        font-size: 18px;
    }
    
    .ref-conversions {
        grid-template-columns: 1fr;
        gap: 6px;
    }
    
    .conversion-table th,
    .conversion-table td {
        padding: 6px 4px;
        font-size: 10px;
    }
    
    .formulas-grid {
        gap: 8px;
    }
    
    .formula-card {
        padding: 12px;
    }
    
    .quick-grid {
        grid-template-columns: 1fr;
        gap: 6px;
    }
    
    .standards-grid {
        grid-template-columns: 1fr;
        gap: 6px;
    }
    
    .category-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }
    
    .tips-list {
        gap: 6px;
    }
    
    .tip-item {
        padding: 6px;
    }
}
</style>