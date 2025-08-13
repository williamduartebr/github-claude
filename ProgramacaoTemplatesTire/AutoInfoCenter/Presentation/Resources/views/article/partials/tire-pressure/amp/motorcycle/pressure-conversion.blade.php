{{-- 
Partial: tire-pressure/amp/motorcycle/pressure-conversion.blade.php
Conversão de unidades para motocicletas - AMP REFATORADO
Versão AMP otimizada com foco em conversões práticas e fórmulas simples
--}}

@php
    $conversionData = $article->getData()['unit_conversion'] ?? [];
    $vehicleInfo = $article->getData()['vehicle_info'] ?? [];
    $tireSpecs = $article->getData()['tire_specifications_by_version'] ?? [];
    
    // Pressão de referência do veículo
    $mainSpec = $tireSpecs[0] ?? [];
    $referencePressure = $mainSpec['front_normal'] ?? $vehicleInfo['pressure_front'] ?? '30 PSI';
    $referencePsi = (int) str_replace([' PSI', ' psi'], '', $referencePressure);
@endphp

<section class="conversion-section">
    <h2 class="section-title">🔄 Conversão de Unidades</h2>
    
    <!-- Conversão da Pressão de Referência -->
    <div class="reference-conversion">
        <div class="ref-header">
            <span class="ref-icon">🏍️</span>
            <span class="ref-title">Sua Motocicleta</span>
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
    
    <!-- Tabela de Conversão Comum -->
    <div class="conversion-table-container">
        <h3 class="table-title">📊 Tabela de Conversão Comum</h3>
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
                        $commonPressures = [
                            ['psi' => 26, 'use' => 'Motos 125cc'],
                            ['psi' => 28, 'use' => 'Urbanas'],
                            ['psi' => 30, 'use' => 'Padrão'],
                            ['psi' => 32, 'use' => 'Médias'],
                            ['psi' => 34, 'use' => 'Esportivas'],
                            ['psi' => 36, 'use' => 'Com garupa'],
                            ['psi' => 38, 'use' => 'Carga máxima'],
                            ['psi' => 40, 'use' => 'Track day']
                        ];
                    @endphp
                    
                    @foreach($commonPressures as $pressure)
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
    
    <!-- Fórmulas Rápidas -->
    <div class="quick-formulas">
        <h3 class="formulas-title">⚡ Fórmulas Rápidas</h3>
        <div class="formulas-grid">
            <div class="formula-card psi-to-bar">
                <div class="formula-header">
                    <span class="formula-icon">➡️</span>
                    <span class="formula-name">PSI → Bar</span>
                </div>
                <div class="formula-calculation">PSI × 0.069</div>
                <div class="formula-example">30 PSI = 2.07 Bar</div>
            </div>
            
            <div class="formula-card psi-to-kgf">
                <div class="formula-header">
                    <span class="formula-icon">⬇️</span>
                    <span class="formula-name">PSI → kgf/cm²</span>
                </div>
                <div class="formula-calculation">PSI × 0.070</div>
                <div class="formula-example">30 PSI = 2.10 kgf/cm²</div>
            </div>
            
            <div class="formula-card bar-to-psi">
                <div class="formula-header">
                    <span class="formula-icon">⬅️</span>
                    <span class="formula-name">Bar → PSI</span>
                </div>
                <div class="formula-calculation">Bar × 14.5</div>
                <div class="formula-example">2.0 Bar = 29 PSI</div>
            </div>
        </div>
    </div>
    
    <!-- Conversões Rápidas -->
    <div class="quick-conversions">
        <h4 class="quick-title">⚡ Conversões Mais Usadas</h4>
        <div class="quick-grid">
            @php
                $quickValues = [26, 28, 30, 32, 34, 36, 38, 40];
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
    <div class="regional-differences">
        <h4 class="regional-title">🌍 Unidades por Região</h4>
        <div class="regions-grid">
            <div class="region-card brazil">
                <div class="region-flag">🇧🇷</div>
                <div class="region-info">
                    <div class="region-name">Brasil</div>
                    <div class="region-unit">PSI</div>
                    <div class="region-note">Padrão oficial</div>
                </div>
            </div>
            
            <div class="region-card europe">
                <div class="region-flag">🇪🇺</div>
                <div class="region-info">
                    <div class="region-name">Europa</div>
                    <div class="region-unit">Bar</div>
                    <div class="region-note">Sistema métrico</div>
                </div>
            </div>
            
            <div class="region-card technical">
                <div class="region-flag">🔬</div>
                <div class="region-info">
                    <div class="region-name">Técnico</div>
                    <div class="region-unit">kPa</div>
                    <div class="region-note">Sistema internacional</div>
                </div>
            </div>
            
            <div class="region-card old">
                <div class="region-flag">📜</div>
                <div class="region-info">
                    <div class="region-name">Antigo</div>
                    <div class="region-unit">kgf/cm²</div>
                    <div class="region-note">Ainda usado</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Dicas Importantes -->
    <div class="conversion-tips">
        <h4 class="tips-title">💡 Dicas Importantes</h4>
        <div class="tips-list">
            <div class="tip-item critical">
                <span class="tip-icon">⚠️</span>
                <span class="tip-text">Use sempre a unidade do seu manômetro</span>
            </div>
            <div class="tip-item warning">
                <span class="tip-icon">🔄</span>
                <span class="tip-text">Converta apenas quando necessário</span>
            </div>
            <div class="tip-item info">
                <span class="tip-icon">📱</span>
                <span class="tip-text">Apps de conversão são mais precisos</span>
            </div>
            <div class="tip-item success">
                <span class="tip-icon">✅</span>
                <span class="tip-text">No Brasil, PSI é o padrão</span>
            </div>
        </div>
    </div>
</section>

<style>
/* Seção principal */
.conversion-section {
    margin: 24px 0;
    padding: 20px;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    border-radius: 16px;
    border: 2px solid #16a34a;
}

.section-title {
    font-size: 20px;
    font-weight: 700;
    color: #166534;
    margin-bottom: 20px;
    text-align: center;
    border-bottom: 3px solid #16a34a;
    padding-bottom: 8px;
}

/* Conversão de referência */
.reference-conversion {
    margin-bottom: 24px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 2px solid #16a34a;
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
    color: #16a34a;
}

.ref-title {
    font-size: 14px;
    font-weight: 600;
    color: #166534;
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
    color: #16a34a;
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
    background: linear-gradient(135deg, #16a34a, #15803d);
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
    background: linear-gradient(135deg, #16a34a, #15803d);
    color: white;
    border-radius: 4px;
    font-weight: 700;
}

.use-cell {
    font-size: 10px;
    color: #6b7280;
}

/* Fórmulas rápidas */
.quick-formulas {
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
    gap: 10px;
}

.formula-card {
    background: white;
    border-radius: 8px;
    padding: 12px;
    border: 2px solid;
    text-align: center;
}

.formula-card.psi-to-bar {
    border-color: #3b82f6;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
}

.formula-card.psi-to-kgf {
    border-color: #f59e0b;
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
}

.formula-card.bar-to-psi {
    border-color: #7c3aed;
    background: linear-gradient(135deg, #f5f3ff, #ede9fe);
}

.formula-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin-bottom: 6px;
}

.formula-icon {
    font-size: 12px;
}

.formula-name {
    font-size: 11px;
    font-weight: 700;
    color: #374151;
}

.formula-calculation {
    font-size: 14px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 4px;
}

.formula-example {
    font-size: 9px;
    color: #6b7280;
    font-style: italic;
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

/* Diferenças regionais */
.regional-differences {
    margin-bottom: 24px;
}

.regional-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.regions-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.region-card {
    background: white;
    border-radius: 8px;
    padding: 10px;
    border: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    gap: 8px;
}

.region-flag {
    font-size: 16px;
    flex-shrink: 0;
}

.region-info {
    flex: 1;
}

.region-name {
    font-size: 11px;
    font-weight: 600;
    color: #374151;
}

.region-unit {
    font-size: 12px;
    font-weight: 700;
    color: #1f2937;
}

.region-note {
    font-size: 9px;
    color: #6b7280;
}

/* Dicas importantes */
.conversion-tips {
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

.tip-item.critical {
    background: #fef2f2;
    color: #991b1b;
}

.tip-item.warning {
    background: #fffbeb;
    color: #92400e;
}

.tip-item.info {
    background: #eff6ff;
    color: #1e40af;
}

.tip-item.success {
    background: #f0fdf4;
    color: #166534;
}

.tip-icon {
    font-size: 12px;
    flex-shrink: 0;
}

.tip-text {
    font-size: 11px;
    font-weight: 500;
}

/* JavaScript para calculadora */
.calc-input-field {
    transition: border-color 0.2s;
}

.calc-input-field:focus {
    outline: none;
    border-color: #16a34a;
}

/* Responsividade mobile */
@media (max-width: 768px) {
    .conversion-section {
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
        padding: 10px;
    }
    
    .quick-grid {
        grid-template-columns: 1fr;
        gap: 6px;
    }
    
    .quick-item {
        padding: 6px;
    }
    
    .regions-grid {
        grid-template-columns: 1fr;
        gap: 6px;
    }
    
    .tips-list {
        gap: 6px;
    }
    
    .tip-item {
        padding: 6px;
    }
}
</style>
