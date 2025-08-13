{{-- 
Partial: tire-pressure/amp/motorcycle/sport-motorcycle-warning.blade.php
Alertas específicos para motocicletas esportivas - AMP
Versão AMP com cuidados especiais para motos de alta performance
--}}

@php
    $vehicleInfo = $article->getData()['vehicle_info'] ?? [];
    $motorcycleCategory = $vehicleInfo['category'] ?? 'standard';
    $isSportBike = $motorcycleCategory === 'sport' || (method_exists($article, 'isSportMotorcycle') && $article->isSportMotorcycle());
@endphp

@if($isSportBike)
<section class="sport-warning-section">
    <h2 class="section-title">🏁 Alertas para Motocicletas Esportivas</h2>
    
    <!-- Alerta Principal -->
    <div class="main-warning">
        <div class="warning-header">
            <span class="warning-icon">⚡</span>
            <span class="warning-title">ATENÇÃO: MOTO ESPORTIVA</span>
        </div>
        <div class="warning-content">
            Motos esportivas exigem cuidados especiais com pneus devido às altas velocidades, 
            frenagens intensas e curvas agressivas. Pressões inadequadas podem ser fatais.
        </div>
    </div>
    
    <!-- Pressões Específicas -->
    <div class="sport-pressures">
        <h3 class="pressures-title">🎯 Pressões Específicas para Performance</h3>
        <div class="pressures-grid">
            <div class="pressure-scenario street">
                <div class="scenario-header">
                    <span class="scenario-icon">🏙️</span>
                    <span class="scenario-name">USO URBANO</span>
                </div>
                <div class="scenario-pressures">
                    <div class="pressure-value">Dianteiro: 32-34 PSI</div>
                    <div class="pressure-value">Traseiro: 36-38 PSI</div>
                </div>
                <div class="scenario-note">Conforto e aderência em baixa velocidade</div>
            </div>
            
            <div class="pressure-scenario highway">
                <div class="scenario-header">
                    <span class="scenario-icon">🛣️</span>
                    <span class="scenario-name">ESTRADA</span>
                </div>
                <div class="scenario-pressures">
                    <div class="pressure-value">Dianteiro: 34-36 PSI</div>
                    <div class="pressure-value">Traseiro: 38-40 PSI</div>
                </div>
                <div class="scenario-note">Estabilidade em alta velocidade</div>
            </div>
            
            <div class="pressure-scenario track">
                <div class="scenario-header">
                    <span class="scenario-icon">🏁</span>
                    <span class="scenario-name">TRACK DAY</span>
                </div>
                <div class="scenario-pressures">
                    <div class="pressure-value">Dianteiro: 30-32 PSI</div>
                    <div class="pressure-value">Traseiro: 28-30 PSI</div>
                </div>
                <div class="scenario-note">Máxima aderência na pista</div>
            </div>
        </div>
    </div>
    
    <!-- Cuidados Críticos -->
    <div class="critical-care">
        <h3 class="care-title">🚨 Cuidados Críticos</h3>
        <div class="care-grid">
            <div class="care-item temperature">
                <div class="care-icon">🌡️</div>
                <div class="care-content">
                    <div class="care-name">Temperatura dos Pneus</div>
                    <div class="care-desc">Esportivas geram muito calor. Verificar pressão sempre a frio.</div>
                    <div class="care-warning">+5°C = +1 PSI de pressão</div>
                </div>
            </div>
            
            <div class="care-item wear">
                <div class="care-icon">⏱️</div>
                <div class="care-content">
                    <div class="care-name">Desgaste Acelerado</div>
                    <div class="care-desc">Pneus esportivos duram 50% menos que convencionais.</div>
                    <div class="care-warning">Trocar a cada 10.000-15.000km</div>
                </div>
            </div>
            
            <div class="care-item compound">
                <div class="care-icon">🏎️</div>
                <div class="care-content">
                    <div class="care-name">Compound Específico</div>
                    <div class="care-desc">Use sempre pneus esportivos com índices V, W ou Y.</div>
                    <div class="care-warning">Nunca misture tipos diferentes</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Verificações Pré-Saída -->
    <div class="pre-ride-checks">
        <h3 class="checks-title">✅ Verificações Pré-Saída</h3>
        <div class="checks-importance">
            <div class="importance-level critical">
                <div class="level-header">
                    <span class="level-icon">🔴</span>
                    <span class="level-name">CRÍTICO - Toda saída</span>
                </div>
                <div class="level-items">
                    <div class="check-item">• Pressão dos pneus (manômetro)</div>
                    <div class="check-item">• Temperatura ambiente</div>
                    <div class="check-item">• Desgaste dos sulcos</div>
                    <div class="check-item">• Objetos presos</div>
                </div>
            </div>
            
            <div class="importance-level high">
                <div class="level-header">
                    <span class="level-icon">🟡</span>
                    <span class="level-name">ALTO - Semanal</span>
                </div>
                <div class="level-items">
                    <div class="check-item">• Alinhamento das rodas</div>
                    <div class="check-item">• Balanceamento</div>
                    <div class="check-item">• Válvulas e tampas</div>
                    <div class="check-item">• Flancos dos pneus</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Situações de Risco -->
    <div class="risk-situations">
        <h3 class="risk-title">⚠️ Situações de Alto Risco</h3>
        <div class="risk-cards">
            <div class="risk-card wet">
                <div class="risk-header">
                    <span class="risk-icon">🌧️</span>
                    <span class="risk-name">PISTA MOLHADA</span>
                </div>
                <div class="risk-content">
                    <div class="risk-warning">EXTREMO CUIDADO</div>
                    <div class="risk-tips">
                        <div class="risk-tip">• Reduzir pressão em 2-3 PSI</div>
                        <div class="risk-tip">• Evitar inclinações excessivas</div>
                        <div class="risk-tip">• Acelerar e frear suavemente</div>
                        <div class="risk-tip">• Usar pneus com sulcos adequados</div>
                    </div>
                </div>
            </div>
            
            <div class="risk-card cold">
                <div class="risk-header">
                    <span class="risk-icon">🧊</span>
                    <span class="risk-name">TEMPERATURA BAIXA</span>
                </div>
                <div class="risk-content">
                    <div class="risk-warning">AQUECIMENTO NECESSÁRIO</div>
                    <div class="risk-tips">
                        <div class="risk-tip">• Aquecer pneus gradualmente</div>
                        <div class="risk-tip">• Evitar curvas fortes no início</div>
                        <div class="risk-tip">• Pressão pode estar baixa</div>
                        <div class="risk-tip">• Aderência reduzida</div>
                    </div>
                </div>
            </div>
            
            <div class="risk-card track">
                <div class="risk-header">
                    <span class="risk-icon">🏁</span>
                    <span class="risk-name">TRACK DAY</span>
                </div>
                <div class="risk-content">
                    <div class="risk-warning">CONFIGURAÇÃO ESPECIAL</div>
                    <div class="risk-tips">
                        <div class="risk-tip">• Pressão mais baixa a frio</div>
                        <div class="risk-tip">• Aquecimento obrigatório</div>
                        <div class="risk-tip">• Monitorar temperatura</div>
                        <div class="risk-tip">• Pneus específicos para pista</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Performance vs Segurança -->
    <div class="performance-safety">
        <h3 class="balance-title">⚖️ Equilíbrio Performance vs Segurança</h3>
        <div class="balance-comparison">
            <div class="balance-side performance">
                <div class="side-header">
                    <span class="side-icon">🚀</span>
                    <span class="side-name">MÁXIMA PERFORMANCE</span>
                </div>
                <div class="side-specs">
                    <div class="spec-item">• Pressão otimizada por situação</div>
                    <div class="spec-item">• Pneus racing compound</div>
                    <div class="spec-item">• Aquecimento antes do uso</div>
                    <div class="spec-item">• Monitoramento constante</div>
                </div>
                <div class="side-note danger">⚠️ Apenas para pilotos experientes</div>
            </div>
            
            <div class="balance-side safety">
                <div class="side-header">
                    <span class="side-icon">🛡️</span>
                    <span class="side-name">SEGURANÇA MÁXIMA</span>
                </div>
                <div class="side-specs">
                    <div class="spec-item">• Pressão padrão do manual</div>
                    <div class="spec-item">• Pneus touring esportivos</div>
                    <div class="spec-item">• Margens de segurança</div>
                    <div class="spec-item">• Verificação frequente</div>
                </div>
                <div class="side-note safe">✅ Recomendado para uso diário</div>
            </div>
        </div>
    </div>
    
    <!-- Indicadores de Desgaste -->
    <div class="wear-indicators">
        <h4 class="wear-title">📏 Indicadores de Desgaste em Esportivas</h4>
        <div class="wear-patterns">
            <div class="pattern-item center">
                <div class="pattern-icon">⬆️</div>
                <div class="pattern-info">
                    <div class="pattern-name">Desgaste Central</div>
                    <div class="pattern-cause">Pressão excessiva</div>
                    <div class="pattern-action">Reduzir 2-3 PSI</div>
                </div>
            </div>
            
            <div class="pattern-item edges">
                <div class="pattern-icon">↔️</div>
                <div class="pattern-info">
                    <div class="pattern-name">Desgaste Lateral</div>
                    <div class="pattern-cause">Pressão baixa / Curvas agressivas</div>
                    <div class="pattern-action">Aumentar pressão</div>
                </div>
            </div>
            
            <div class="pattern-item irregular">
                <div class="pattern-icon">〰️</div>
                <div class="pattern-info">
                    <div class="pattern-name">Desgaste Irregular</div>
                    <div class="pattern-cause">Desalinhamento / Pilotagem</div>
                    <div class="pattern-action">Verificar suspensão</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recomendações de Pneus -->
    <div class="tire-recommendations">
        <h4 class="recommend-title">🏆 Pneus Recomendados para Esportivas</h4>
        <div class="tire-categories">
            <div class="tire-category street">
                <div class="category-header">
                    <span class="category-icon">🏙️</span>
                    <span class="category-name">USO MISTO</span>
                </div>
                <div class="category-features">
                    <div class="feature">• Compound dual (centro/lateral)</div>
                    <div class="feature">• Boa durabilidade</div>
                    <div class="feature">• Performance moderada</div>
                </div>
            </div>
            
            <div class="tire-category sport">
                <div class="category-header">
                    <span class="category-icon">🏁</span>
                    <span class="category-name">ESPORTIVO</span>
                </div>
                <div class="category-features">
                    <div class="feature">• Compound macio</div>
                    <div class="feature">• Máxima aderência</div>
                    <div class="feature">• Menor durabilidade</div>
                </div>
            </div>
            
            <div class="tire-category track">
                <div class="category-header">
                    <span class="category-icon">🏎️</span>
                    <span class="category-name">PISTA</span>
                </div>
                <div class="category-features">
                    <div class="feature">• Compound ultra macio</div>
                    <div class="feature">• Aquecimento obrigatório</div>
                    <div class="feature">• Uso exclusivo em pista</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Aviso Final Crítico -->
    <div class="final-critical-warning">
        <div class="critical-header">
            <span class="critical-icon">🆘</span>
            <span class="critical-title">AVISO CRÍTICO</span>
        </div>
        <div class="critical-content">
            <strong>EM MOTOCICLETAS ESPORTIVAS, ERROS COM PNEUS PODEM SER FATAIS.</strong><br>
            Se você não tem experiência ou conhecimento técnico adequado, 
            <strong>sempre consulte um especialista</strong> antes de fazer ajustes.
        </div>
        <div class="critical-stats">
            <div class="stat-item">
                <span class="stat-number">85%</span>
                <span class="stat-text">dos acidentes graves envolvem problemas com pneus</span>
            </div>
        </div>
    </div>
</section>
@endif

<style>
/* Seção principal */
.sport-warning-section {
    margin: 24px 0;
    padding: 20px;
    background: linear-gradient(135deg, #7f1d1d, #991b1b);
    border-radius: 16px;
    border: 3px solid #dc2626;
    color: white;
}

.section-title {
    font-size: 20px;
    font-weight: 700;
    color: #fef2f2;
    margin-bottom: 20px;
    text-align: center;
    border-bottom: 3px solid #fef2f2;
    padding-bottom: 8px;
}

/* Alerta principal */
.main-warning {
    margin-bottom: 24px;
    background: rgba(254, 242, 242, 0.1);
    border-radius: 12px;
    padding: 16px;
    border: 2px solid #fef2f2;
}

.warning-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
    justify-content: center;
}

.warning-icon {
    font-size: 24px;
    color: #fbbf24;
}

.warning-title {
    font-size: 16px;
    font-weight: 700;
    color: #fef2f2;
}

.warning-content {
    font-size: 13px;
    line-height: 1.5;
    color: #fee2e2;
    text-align: center;
}

/* Pressões específicas */
.sport-pressures {
    margin-bottom: 24px;
}

.pressures-title {
    font-size: 16px;
    font-weight: 600;
    color: #fef2f2;
    margin-bottom: 12px;
    text-align: center;
}

.pressures-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}

.pressure-scenario {
    background: rgba(255, 255, 255, 0.9);
    border-radius: 10px;
    padding: 12px;
    color: #374151;
}

.scenario-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}

.scenario-icon {
    font-size: 16px;
}

.scenario-name {
    font-size: 12px;
    font-weight: 700;
    color: #1f2937;
}

.scenario-pressures {
    display: flex;
    flex-direction: column;
    gap: 4px;
    margin-bottom: 6px;
}

.pressure-value {
    font-size: 11px;
    font-weight: 600;
    color: #dc2626;
}

.scenario-note {
    font-size: 10px;
    color: #6b7280;
    font-style: italic;
}

/* Cuidados críticos */
.critical-care {
    margin-bottom: 24px;
}

.care-title {
    font-size: 16px;
    font-weight: 600;
    color: #fef2f2;
    margin-bottom: 12px;
    text-align: center;
}

.care-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}

.care-item {
    background: rgba(255, 255, 255, 0.9);
    border-radius: 8px;
    padding: 12px;
    color: #374151;
    display: flex;
    gap: 10px;
}

.care-icon {
    font-size: 18px;
    flex-shrink: 0;
    color: #dc2626;
}

.care-content {
    flex: 1;
}

.care-name {
    font-size: 12px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 4px;
}

.care-desc {
    font-size: 10px;
    color: #4b5563;
    margin-bottom: 4px;
    line-height: 1.3;
}

.care-warning {
    font-size: 10px;
    color: #dc2626;
    font-weight: 600;
}

/* Verificações pré-saída */
.pre-ride-checks {
    margin-bottom: 24px;
}

.checks-title {
    font-size: 16px;
    font-weight: 600;
    color: #fef2f2;
    margin-bottom: 12px;
    text-align: center;
}

.checks-importance {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.importance-level {
    background: rgba(255, 255, 255, 0.9);
    border-radius: 8px;
    padding: 12px;
    color: #374151;
}

.level-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}

.level-icon {
    font-size: 14px;
}

.level-name {
    font-size: 11px;
    font-weight: 700;
    color: #1f2937;
}

.level-items {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.check-item {
    font-size: 10px;
    color: #4b5563;
}

/* Situações de risco */
.risk-situations {
    margin-bottom: 24px;
}

.risk-title {
    font-size: 16px;
    font-weight: 600;
    color: #fef2f2;
    margin-bottom: 12px;
    text-align: center;
}

.risk-cards {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}

.risk-card {
    background: rgba(255, 255, 255, 0.9);
    border-radius: 8px;
    padding: 12px;
    color: #374151;
}

.risk-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}

.risk-icon {
    font-size: 16px;
}

.risk-name {
    font-size: 11px;
    font-weight: 700;
    color: #1f2937;
}

.risk-content {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.risk-warning {
    font-size: 10px;
    font-weight: 700;
    color: #dc2626;
    text-align: center;
    padding: 4px;
    background: #fef2f2;
    border-radius: 4px;
}

.risk-tips {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.risk-tip {
    font-size: 9px;
    color: #4b5563;
}

/* Performance vs Segurança */
.performance-safety {
    margin-bottom: 24px;
}

.balance-title {
    font-size: 16px;
    font-weight: 600;
    color: #fef2f2;
    margin-bottom: 12px;
    text-align: center;
}

.balance-comparison {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}

.balance-side {
    background: rgba(255, 255, 255, 0.9);
    border-radius: 8px;
    padding: 12px;
    color: #374151;
}

.side-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}

.side-icon {
    font-size: 16px;
}

.side-name {
    font-size: 11px;
    font-weight: 700;
    color: #1f2937;
}

.side-specs {
    display: flex;
    flex-direction: column;
    gap: 3px;
    margin-bottom: 8px;
}

.spec-item {
    font-size: 10px;
    color: #4b5563;
}

.side-note {
    font-size: 9px;
    font-weight: 600;
    text-align: center;
    padding: 4px;
    border-radius: 4px;
}

.side-note.danger {
    background: #fef2f2;
    color: #dc2626;
}

.side-note.safe {
    background: #f0fdf4;
    color: #16a34a;
}

/* Indicadores de desgaste */
.wear-indicators {
    margin-bottom: 24px;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 12px;
    padding: 16px;
    color: #374151;
}

.wear-title {
    font-size: 14px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 12px;
    text-align: center;
}

.wear-patterns {
    display: grid;
    grid-template-columns: 1fr;
    gap: 8px;
}

.pattern-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px;
    background: #f8fafc;
    border-radius: 6px;
}

.pattern-icon {
    font-size: 16px;
    color: #dc2626;
    flex-shrink: 0;
}

.pattern-info {
    flex: 1;
}

.pattern-name {
    font-size: 11px;
    font-weight: 600;
    color: #1f2937;
}

.pattern-cause {
    font-size: 9px;
    color: #6b7280;
}

.pattern-action {
    font-size: 9px;
    color: #dc2626;
    font-weight: 600;
}

/* Recomendações de pneus */
.tire-recommendations {
    margin-bottom: 24px;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 12px;
    padding: 16px;
    color: #374151;
}

.recommend-title {
    font-size: 14px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 12px;
    text-align: center;
}

.tire-categories {
    display: grid;
    grid-template-columns: 1fr;
    gap: 8px;
}

.tire-category {
    padding: 10px;
    background: #f8fafc;
    border-radius: 6px;
}

.category-header {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 6px;
}

.category-icon {
    font-size: 14px;
}

.category-name {
    font-size: 11px;
    font-weight: 700;
    color: #1f2937;
}

.category-features {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.feature {
    font-size: 9px;
    color: #4b5563;
}

/* Aviso final crítico */
.final-critical-warning {
    background: rgba(0, 0, 0, 0.3);
    border-radius: 12px;
    padding: 16px;
    border: 2px solid #fbbf24;
}

.critical-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
    justify-content: center;
}

.critical-icon {
    font-size: 20px;
    color: #fbbf24;
}

.critical-title {
    font-size: 14px;
    font-weight: 700;
    color: #fef2f2;
}

.critical-content {
    font-size: 12px;
    line-height: 1.4;
    color: #fee2e2;
    text-align: center;
    margin-bottom: 12px;
}

.critical-stats {
    display: flex;
    justify-content: center;
}

.stat-item {
    text-align: center;
    padding: 8px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 6px;
}

.stat-number {
    display: block;
    font-size: 18px;
    font-weight: 700;
    color: #fbbf24;
}

.stat-text {
    font-size: 10px;
    color: #fee2e2;
}

/* Responsividade mobile */
@media (max-width: 768px) {
    .sport-warning-section {
        padding: 16px;
        margin: 16px 0;
    }
    
    .section-title {
        font-size: 18px;
    }
    
    .warning-icon {
        font-size: 20px;
    }
    
    .warning-title {
        font-size: 14px;
    }
    
    .warning-content {
        font-size: 12px;
    }
    
    .pressures-grid,
    .care-grid,
    .risk-cards,
    .balance-comparison,
    .wear-patterns,
    .tire-categories {
        gap: 8px;
    }
    
    .pressure-scenario,
    .care-item,
    .risk-card,
    .balance-side {
        padding: 10px;
    }
    
    .critical-content {
        font-size: 11px;
    }
    
    .stat-number {
        font-size: 16px;
    }
}
</style>