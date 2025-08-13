{{-- 
Partial: tire-pressure/amp/shared/climate-adjustments.blade.php
Ajustes de pressão por clima e temperatura - AMP
Versão AMP compartilhada entre carros e motocicletas
--}}

@php
    $vehicleInfo = $article->getData()['vehicle_info'] ?? [];
    $seasonalAdjustments = $article->getData()['seasonal_adjustments'] ?? [];
    $climateInfo = $article->getData()['climate_adjustments'] ?? [];
    $unitConversion = $article->getData()['unit_conversion'] ?? [];
    
    // Determinar tipo de veículo
    $vehicleType = isset($vehicleInfo['category']) && in_array($vehicleInfo['category'], ['sport', 'touring', 'naked', 'cruiser', 'adventure']) ? 'motorcycle' : 'car';
    $isMotorcycle = $vehicleType === 'motorcycle';
@endphp

<section class="climate-adjustments-section">
    <h2 class="section-title">🌡️ Ajustes por Clima e Temperatura</h2>
    
    <!-- Princípio Básico -->
    <div class="basic-principle">
        <div class="principle-header">
            <span class="principle-icon">🔬</span>
            <span class="principle-title">Princípio Físico</span>
        </div>
        <div class="principle-content">
            A pressão dos pneus varia com a temperatura seguindo a <strong>Lei dos Gases Ideais</strong>. 
            Para cada <strong>10°C de mudança</strong> na temperatura ambiente, a pressão altera aproximadamente <strong>±1 PSI</strong>.
        </div>
    </div>
    
    <!-- Calculadora de Temperatura -->
    <div class="temperature-calculator">
        <h3 class="calc-title">🧮 Calculadora de Ajuste por Temperatura</h3>
        <div class="calc-example">
            <div class="calc-scenario">
                <div class="scenario-header">
                    <span class="scenario-icon">📊</span>
                    <span class="scenario-name">Exemplo Prático</span>
                </div>
                <div class="calc-data">
                    <div class="calc-row">
                        <span class="calc-label">Pressão calibrada a:</span>
                        <span class="calc-value">20°C = 32 PSI</span>
                    </div>
                    <div class="calc-row">
                        <span class="calc-label">Temperatura atual:</span>
                        <span class="calc-value">35°C (+15°C)</span>
                    </div>
                    <div class="calc-row result">
                        <span class="calc-label">Pressão atual:</span>
                        <span class="calc-value">~33.5 PSI (+1.5)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Ajustes Sazonais -->
    <div class="seasonal-adjustments">
        <h3 class="seasonal-title">🗓️ Ajustes Sazonais</h3>
        <div class="seasons-grid">
            <div class="season-card summer">
                <div class="season-header">
                    <span class="season-icon">☀️</span>
                    <span class="season-name">VERÃO</span>
                    <span class="season-temp">25°C - 40°C</span>
                </div>
                <div class="season-adjustments">
                    <div class="adjustment-item">
                        <span class="adj-icon">⬇️</span>
                        <span class="adj-text">Reduzir 1-2 PSI da pressão padrão</span>
                    </div>
                    <div class="adjustment-item">
                        <span class="adj-icon">🌡️</span>
                        <span class="adj-text">Calibrar pela manhã (pneus frios)</span>
                    </div>
                    <div class="adjustment-item">
                        <span class="adj-icon">☀️</span>
                        <span class="adj-text">Evitar asfalto muito quente</span>
                    </div>
                    @if($isMotorcycle)
                    <div class="adjustment-item">
                        <span class="adj-icon">🏍️</span>
                        <span class="adj-text">Verificar após cada viagem longa</span>
                    </div>
                    @else
                    <div class="adjustment-item">
                        <span class="adj-icon">🚗</span>
                        <span class="adj-text">Incluir estepe na verificação</span>
                    </div>
                    @endif
                </div>
            </div>
            
            <div class="season-card winter">
                <div class="season-header">
                    <span class="season-icon">❄️</span>
                    <span class="season-name">INVERNO</span>
                    <span class="season-temp">5°C - 20°C</span>
                </div>
                <div class="season-adjustments">
                    <div class="adjustment-item">
                        <span class="adj-icon">⬆️</span>
                        <span class="adj-text">Adicionar 1-2 PSI da pressão padrão</span>
                    </div>
                    <div class="adjustment-item">
                        <span class="adj-icon">🌡️</span>
                        <span class="adj-text">Verificar mais frequentemente</span>
                    </div>
                    <div class="adjustment-item">
                        <span class="adj-icon">❄️</span>
                        <span class="adj-text">Aderência reduzida no frio</span>
                    </div>
                    @if($isMotorcycle)
                    <div class="adjustment-item">
                        <span class="adj-icon">🏍️</span>
                        <span class="adj-text">Aquecimento dos pneus essencial</span>
                    </div>
                    @else
                    <div class="adjustment-item">
                        <span class="adj-icon">🚗</span>
                        <span class="adj-text">Considerar pneus de inverno</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabela de Compensação -->
    <div class="compensation-table">
        <h3 class="table-title">📋 Tabela de Compensação por Temperatura</h3>
        <div class="table-wrapper">
            <table class="climate-table">
                <thead>
                    <tr>
                        <th>Temperatura</th>
                        <th>Diferença</th>
                        <th>Ajuste PSI</th>
                        <th>Ação Recomendada</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="temp-row extreme-cold">
                        <td>Abaixo de 5°C</td>
                        <td class="diff-value">-15°C</td>
                        <td class="psi-adjustment">+2 PSI</td>
                        <td class="action">Verificar diariamente</td>
                    </tr>
                    <tr class="temp-row cold">
                        <td>5°C - 15°C</td>
                        <td class="diff-value">-5°C</td>
                        <td class="psi-adjustment">+1 PSI</td>
                        <td class="action">Monitorar semanalmente</td>
                    </tr>
                    <tr class="temp-row normal">
                        <td>15°C - 25°C</td>
                        <td class="diff-value">Padrão</td>
                        <td class="psi-adjustment">Sem ajuste</td>
                        <td class="action">Verificação normal</td>
                    </tr>
                    <tr class="temp-row warm">
                        <td>25°C - 35°C</td>
                        <td class="diff-value">+10°C</td>
                        <td class="psi-adjustment">-1 PSI</td>
                        <td class="action">Calibrar pela manhã</td>
                    </tr>
                    <tr class="temp-row hot">
                        <td>Acima de 35°C</td>
                        <td class="diff-value">+15°C</td>
                        <td class="psi-adjustment">-2 PSI</td>
                        <td class="action">Evitar meio-dia</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Situações Especiais -->
    <div class="special-situations">
        <h3 class="situations-title">⚡ Situações Especiais</h3>
        <div class="situations-grid">
            <div class="situation-card altitude">
                <div class="situation-header">
                    <span class="situation-icon">🏔️</span>
                    <span class="situation-name">ALTITUDE ELEVADA</span>
                </div>
                <div class="situation-content">
                    <div class="situation-desc">
                        Acima de 1000m de altitude, a pressão atmosférica diminui
                    </div>
                    <div class="situation-tips">
                        <div class="tip-item">• +0.5 PSI a cada 500m de altitude</div>
                        <div class="tip-item">• Verificar ao chegar no destino</div>
                        <div class="tip-item">• Reajustar ao voltar ao nível do mar</div>
                    </div>
                </div>
            </div>
            
            <div class="situation-card humidity">
                <div class="situation-header">
                    <span class="situation-icon">💧</span>
                    <span class="situation-name">ALTA UMIDADE</span>
                </div>
                <div class="situation-content">
                    <div class="situation-desc">
                        Umidade acima de 80% afeta a aderência dos pneus
                    </div>
                    <div class="situation-tips">
                        <div class="tip-item">• Manter pressão padrão</div>
                        <div class="tip-item">• Reduzir velocidade em curvas</div>
                        <div class="tip-item">• Verificar sulcos regularmente</div>
                    </div>
                </div>
            </div>
            
            <div class="situation-card thermal-shock">
                <div class="situation-header">
                    <span class="situation-icon">🔥❄️</span>
                    <span class="situation-name">CHOQUE TÉRMICO</span>
                </div>
                <div class="situation-content">
                    <div class="situation-desc">
                        Mudanças bruscas de temperatura (±20°C)
                    </div>
                    <div class="situation-tips">
                        <div class="tip-item">• Verificar após mudança climática</div>
                        <div class="tip-item">• Aguardar estabilização</div>
                        <div class="tip-item">• Recalibrar se necessário</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Dicas por Região Brasileira -->
    <div class="regional-tips">
        <h4 class="regional-title">🇧🇷 Dicas por Região do Brasil</h4>
        <div class="regions-grid">
            <div class="region-card north">
                <div class="region-header">
                    <span class="region-icon">🌴</span>
                    <span class="region-name">NORTE/NORDESTE</span>
                </div>
                <div class="region-temp">25°C - 40°C</div>
                <div class="region-tips">
                    <div class="region-tip">• Calibrar sempre pela manhã</div>
                    <div class="region-tip">• -1 a -2 PSI no verão</div>
                    <div class="region-tip">• Verificar 2x por semana</div>
                </div>
            </div>
            
            <div class="region-card center">
                <div class="region-header">
                    <span class="region-icon">🌾</span>
                    <span class="region-name">CENTRO-OESTE</span>
                </div>
                <div class="region-temp">15°C - 35°C</div>
                <div class="region-tips">
                    <div class="region-tip">• Ajustar sazonalmente</div>
                    <div class="region-tip">• Cuidado na seca</div>
                    <div class="region-tip">• Umidade baixa afeta aderência</div>
                </div>
            </div>
            
            <div class="region-card southeast">
                <div class="region-header">
                    <span class="region-icon">🏙️</span>
                    <span class="region-name">SUDESTE</span>
                </div>
                <div class="region-temp">10°C - 35°C</div>
                <div class="region-tips">
                    <div class="region-tip">• Variação sazonal moderada</div>
                    <div class="region-tip">• Ajustar no inverno/verão</div>
                    <div class="region-tip">• Cuidado com chuvas</div>
                </div>
            </div>
            
            <div class="region-card south">
                <div class="region-header">
                    <span class="region-icon">❄️</span>
                    <span class="region-name">SUL</span>
                </div>
                <div class="region-temp">0°C - 30°C</div>
                <div class="region-tips">
                    <div class="region-tip">• +2 PSI no inverno</div>
                    <div class="region-tip">• Verificar geada</div>
                    <div class="region-tip">• Pneus de inverno recomendados</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Alertas Importantes -->
    <div class="climate-alerts">
        <h4 class="alerts-title">⚠️ Alertas Importantes</h4>
        <div class="alerts-list">
            <div class="alert-item critical">
                <span class="alert-icon">🚨</span>
                <span class="alert-text">Nunca calibre pneus quentes - aguarde pelo menos 3 horas</span>
            </div>
            <div class="alert-item warning">
                <span class="alert-icon">⚠️</span>
                <span class="alert-text">Variações de ±3 PSI podem afetar drasticamente a dirigibilidade</span>
            </div>
            <div class="alert-item info">
                <span class="alert-icon">ℹ️</span>
                <span class="alert-text">Em viagens longas, verifique a pressão a cada parada</span>
            </div>
            <div class="alert-item success">
                <span class="alert-icon">✅</span>
                <span class="alert-text">Mantenha um termômetro no carro para referência</span>
            </div>
        </div>
    </div>
</section>

<style>
/* Seção principal */
.climate-adjustments-section {
    margin: 24px 0;
    padding: 20px;
    background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
    border-radius: 16px;
    border: 2px solid #0ea5e9;
}

.section-title {
    font-size: 20px;
    font-weight: 700;
    color: #0c4a6e;
    margin-bottom: 20px;
    text-align: center;
    border-bottom: 3px solid #0ea5e9;
    padding-bottom: 8px;
}

/* Princípio básico */
.basic-principle {
    margin-bottom: 24px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 2px solid #3b82f6;
}

.principle-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
    justify-content: center;
}

.principle-icon {
    font-size: 18px;
    color: #3b82f6;
}

.principle-title {
    font-size: 16px;
    font-weight: 600;
    color: #1e40af;
}

.principle-content {
    font-size: 13px;
    color: #1e3a8a;
    line-height: 1.5;
    text-align: center;
}

/* Calculadora de temperatura */
.temperature-calculator {
    margin-bottom: 24px;
}

.calc-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.calc-example {
    background: white;
    border-radius: 10px;
    padding: 16px;
    border: 1px solid #e5e7eb;
}

.calc-scenario {
    text-align: center;
}

.scenario-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-bottom: 12px;
}

.scenario-icon {
    font-size: 16px;
    color: #f59e0b;
}

.scenario-name {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
}

.calc-data {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.calc-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 12px;
    background: #f8fafc;
    border-radius: 6px;
}

.calc-row.result {
    background: linear-gradient(135deg, #ecfdf5, #d1fae5);
    border: 1px solid #16a34a;
}

.calc-label {
    font-size: 11px;
    color: #6b7280;
}

.calc-value {
    font-size: 12px;
    font-weight: 600;
    color: #374151;
}

.calc-row.result .calc-value {
    color: #166534;
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

.seasons-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}

.season-card {
    background: white;
    border-radius: 12px;
    padding: 16px;
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

.season-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
    flex-wrap: wrap;
}

.season-icon {
    font-size: 18px;
}

.season-name {
    font-size: 14px;
    font-weight: 700;
    color: #374151;
}

.season-temp {
    font-size: 10px;
    color: #6b7280;
    background: rgba(255, 255, 255, 0.5);
    padding: 2px 6px;
    border-radius: 8px;
}

.season-adjustments {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.adjustment-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    color: #4b5563;
}

.adj-icon {
    font-size: 12px;
    flex-shrink: 0;
}

.adj-text {
    line-height: 1.3;
}

/* Tabela de compensação */
.compensation-table {
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

.climate-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    font-size: 12px;
}

.climate-table th {
    background: linear-gradient(135deg, #0ea5e9, #0284c7);
    color: white;
    padding: 10px 6px;
    text-align: center;
    font-weight: 600;
    font-size: 11px;
}

.climate-table td {
    padding: 8px 6px;
    text-align: center;
    border-bottom: 1px solid #f1f5f9;
}

.temp-row.extreme-cold {
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
}

.temp-row.cold {
    background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
}

.temp-row.normal {
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
}

.temp-row.warm {
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
}

.temp-row.hot {
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
}

.diff-value {
    font-weight: 600;
    color: #374151;
}

.psi-adjustment {
    font-weight: 700;
    color: #dc2626;
}

.action {
    font-size: 10px;
    color: #6b7280;
}

/* Situações especiais */
.special-situations {
    margin-bottom: 24px;
}

.situations-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.situations-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}

.situation-card {
    background: white;
    border-radius: 8px;
    padding: 12px;
    border: 1px solid #e5e7eb;
}

.situation-header {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
}

.situation-icon {
    font-size: 14px;
}

.situation-name {
    font-size: 11px;
    font-weight: 700;
    color: #374151;
}

.situation-content {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.situation-desc {
    font-size: 10px;
    color: #6b7280;
    line-height: 1.3;
}

.situation-tips {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.tip-item {
    font-size: 9px;
    color: #4b5563;
}

/* Dicas regionais */
.regional-tips {
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
    text-align: center;
}

.region-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    margin-bottom: 4px;
}

.region-icon {
    font-size: 12px;
}

.region-name {
    font-size: 9px;
    font-weight: 700;
    color: #374151;
}

.region-temp {
    font-size: 8px;
    color: #6b7280;
    margin-bottom: 6px;
}

.region-tips {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.region-tip {
    font-size: 8px;
    color: #4b5563;
}

/* Alertas climáticos */
.climate-alerts {
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #e5e7eb;
}

.alerts-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.alerts-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.alert-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px;
    border-radius: 6px;
    font-size: 11px;
}

.alert-item.critical {
    background: #fef2f2;
    color: #991b1b;
}

.alert-item.warning {
    background: #fffbeb;
    color: #92400e;
}

.alert-item.info {
    background: #eff6ff;
    color: #1e40af;
}

.alert-item.success {
    background: #f0fdf4;
    color: #166534;
}

.alert-icon {
    font-size: 12px;
    flex-shrink: 0;
}

.alert-text {
    line-height: 1.3;
}

/* Responsividade mobile */
@media (max-width: 768px) {
    .climate-adjustments-section {
        padding: 16px;
        margin: 16px 0;
    }
    
    .section-title {
        font-size: 18px;
    }
    
    .seasons-grid,
    .situations-grid {
        gap: 8px;
    }
    
    .season-card,
    .situation-card {
        padding: 12px;
    }
    
    .climate-table th,
    .climate-table td {
        padding: 6px 4px;
        font-size: 10px;
    }
    
    .regions-grid {
        grid-template-columns: 1fr;
        gap: 6px;
    }
    
    .region-card {
        padding: 8px;
    }
    
    .alerts-list {
        gap: 6px;
    }
    
    .alert-item {
        gap: 6px;
        padding: 6px;
        font-size: 10px;
    }
}
</style>