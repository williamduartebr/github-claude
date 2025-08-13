{{-- 
Partial: tire-pressure/amp/motorcycle/calibration-procedure.blade.php
Procedimento de calibragem detalhado otimizado para AMP - motocicletas
Versão AMP com passo a passo visual e dicas categorizadas
--}}

@php
    $vehicleInfo = $article->getData()['vehicle_info'] ?? [];
    $pressureTable = $article->getData()['pressure_table'] ?? [];
    $maintenanceTips = $article->getData()['maintenance_tips'] ?? [];
    $mainTireSpec = $article->getData()['tire_specifications_by_version'][0] ?? null;
    
    // Pressões de referência
    $frontNormal = $mainTireSpec ? str_replace([' PSI', ' psi'], '', $mainTireSpec['front_normal']) : '30';
    $rearNormal = $mainTireSpec ? str_replace([' PSI', ' psi'], '', $mainTireSpec['rear_normal']) : '32';
    $frontLoaded = $mainTireSpec ? str_replace([' PSI', ' psi'], '', $mainTireSpec['front_loaded']) : '32';
    $rearLoaded = $mainTireSpec ? str_replace([' PSI', ' psi'], '', $mainTireSpec['rear_loaded']) : '36';
@endphp

<section class="calibration-procedure-section">
    <h2 class="section-title">🔧 Procedimento de Calibragem</h2>
    
    <!-- Seção de Preparação -->
    <div class="preparation-section">
        <h3 class="prep-title">📋 Preparação Essencial</h3>
        <div class="prep-grid">
            <div class="prep-item temperature">
                <div class="prep-icon">🌡️</div>
                <div class="prep-content">
                    <div class="prep-name">Pneus Frios</div>
                    <div class="prep-desc">Aguarde 3h após rodar ou calibre pela manhã</div>
                </div>
            </div>
            
            <div class="prep-item tool">
                <div class="prep-icon">⚙️</div>
                <div class="prep-content">
                    <div class="prep-name">Manômetro</div>
                    <div class="prep-desc">Use sempre o mesmo equipamento calibrado</div>
                </div>
            </div>
            
            <div class="prep-item timing">
                <div class="prep-icon">⏰</div>
                <div class="prep-content">
                    <div class="prep-name">Timing</div>
                    <div class="prep-desc">Reserve 10-15 min para o processo completo</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Passo a Passo Numerado -->
    <div class="steps-section">
        <h3 class="steps-title">📝 Passo a Passo Detalhado</h3>
        
        <div class="step-item">
            <div class="step-number">1</div>
            <div class="step-content">
                <div class="step-title">Posicione a Motocicleta</div>
                <div class="step-description">
                    Coloque em superfície plana e nivelada. Use cavalete central se disponível, 
                    ou mantenha a moto em pé com apoio firme.
                </div>
            </div>
        </div>
        
        <div class="step-item">
            <div class="step-number">2</div>
            <div class="step-content">
                <div class="step-title">Remova as Tampas das Válvulas</div>
                <div class="step-description">
                    Retire cuidadosamente as tampinhas plásticas das válvulas. 
                    Guarde em local seguro para não perder.
                </div>
            </div>
        </div>
        
        <div class="step-item">
            <div class="step-number">3</div>
            <div class="step-content">
                <div class="step-title">Meça a Pressão Atual</div>
                <div class="step-description">
                    Conecte o manômetro firmemente na válvula. Faça a leitura rapidamente 
                    para evitar perda de ar.
                </div>
            </div>
        </div>
        
        <div class="step-item">
            <div class="step-number">4</div>
            <div class="step-content">
                <div class="step-title">Ajuste a Pressão</div>
                <div class="step-description">
                    Se baixa: conecte o compressor e adicione ar aos poucos. 
                    Se alta: pressione o pino central da válvula para liberar ar.
                </div>
            </div>
        </div>
        
        <div class="step-item">
            <div class="step-number">5</div>
            <div class="step-content">
                <div class="step-title">Confira e Finalize</div>
                <div class="step-description">
                    Meça novamente para confirmar. Recoloque as tampas das válvulas 
                    e anote a data da calibragem.
                </div>
            </div>
        </div>
    </div>
    
    <!-- Valores de Referência -->
    <div class="reference-values">
        <h3 class="ref-title">📊 Valores de Referência</h3>
        <div class="ref-grid">
            <div class="ref-card solo">
                <div class="ref-header">
                    <span class="ref-icon">👤</span>
                    <span class="ref-label">PILOTO SOLO</span>
                </div>
                <div class="ref-pressures">
                    <div class="pressure-item front">
                        <div class="pressure-label">Dianteiro</div>
                        <div class="pressure-value">{{ $frontNormal }} PSI</div>
                    </div>
                    <div class="pressure-item rear">
                        <div class="pressure-label">Traseiro</div>
                        <div class="pressure-value">{{ $rearNormal }} PSI</div>
                    </div>
                </div>
            </div>
            
            <div class="ref-card loaded">
                <div class="ref-header">
                    <span class="ref-icon">👥</span>
                    <span class="ref-label">COM GARUPA</span>
                </div>
                <div class="ref-pressures">
                    <div class="pressure-item front">
                        <div class="pressure-label">Dianteiro</div>
                        <div class="pressure-value">{{ $frontLoaded }} PSI</div>
                    </div>
                    <div class="pressure-item rear">
                        <div class="pressure-label">Traseiro</div>
                        <div class="pressure-value">{{ $rearLoaded }} PSI</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Dicas Categorizadas -->
    <div class="tips-section">
        <h3 class="tips-title">💡 Dicas Importantes</h3>
        
        <div class="tips-categories">
            <div class="tip-category critical">
                <div class="tip-cat-header">
                    <span class="tip-cat-icon">⚠️</span>
                    <span class="tip-cat-name">CRÍTICAS</span>
                </div>
                <div class="tip-cat-items">
                    <div class="tip-cat-item">• Nunca calibre com pneus quentes</div>
                    <div class="tip-cat-item">• Não exceda a pressão máxima do pneu</div>
                    <div class="tip-cat-item">• Verifique antes de viagens longas</div>
                </div>
            </div>
            
            <div class="tip-category warning">
                <div class="tip-cat-header">
                    <span class="tip-cat-icon">⚡</span>
                    <span class="tip-cat-name">ATENÇÃO</span>
                </div>
                <div class="tip-cat-items">
                    <div class="tip-cat-item">• Calibre sempre os dois pneus</div>
                    <div class="tip-cat-item">• Use apenas ar comprimido limpo</div>
                    <div class="tip-cat-item">• Evite posto com compressor muito potente</div>
                </div>
            </div>
            
            <div class="tip-category info">
                <div class="tip-cat-header">
                    <span class="tip-cat-icon">ℹ️</span>
                    <span class="tip-cat-name">INFORMAÇÕES</span>
                </div>
                <div class="tip-cat-items">
                    <div class="tip-cat-item">• Pressão varia ±1 PSI por 10°C</div>
                    <div class="tip-cat-item">• Pneus perdem 1-2 PSI por mês</div>
                    <div class="tip-cat-item">• Manômetros podem ter variação</div>
                </div>
            </div>
            
            <div class="tip-category success">
                <div class="tip-cat-header">
                    <span class="tip-cat-icon">✅</span>
                    <span class="tip-cat-name">BOAS PRÁTICAS</span>
                </div>
                <div class="tip-cat-items">
                    <div class="tip-cat-item">• Tenha seu próprio manômetro</div>
                    <div class="tip-cat-item">• Anote datas das calibragens</div>
                    <div class="tip-cat-item">• Calibre sempre no mesmo posto</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Frequência de Verificação -->
    <div class="frequency-section">
        <h3 class="freq-title">📅 Frequência de Verificação</h3>
        <div class="freq-grid">
            <div class="freq-item daily">
                <div class="freq-header">
                    <span class="freq-icon">📱</span>
                    <span class="freq-period">DIÁRIO</span>
                </div>
                <div class="freq-desc">Inspeção visual dos pneus</div>
            </div>
            
            <div class="freq-item weekly">
                <div class="freq-header">
                    <span class="freq-icon">📊</span>
                    <span class="freq-period">SEMANAL</span>
                </div>
                <div class="freq-desc">Verificação com manômetro</div>
            </div>
            
            <div class="freq-item monthly">
                <div class="freq-header">
                    <span class="freq-icon">🔍</span>
                    <span class="freq-period">MENSAL</span>
                </div>
                <div class="freq-desc">Inspeção completa dos pneus</div>
            </div>
        </div>
    </div>
    
    <!-- Erros Comuns -->
    <div class="common-errors">
        <h4 class="errors-title">🚫 Erros Comuns a Evitar</h4>
        <div class="errors-list">
            <div class="error-item">
                <span class="error-icon">❌</span>
                <span class="error-text">Calibrar após andar (pneus quentes)</span>
            </div>
            <div class="error-item">
                <span class="error-icon">❌</span>
                <span class="error-text">Não conferir a pressão após calibrar</span>
            </div>
            <div class="error-item">
                <span class="error-icon">❌</span>
                <span class="error-text">Usar manômetro descalibrado</span>
            </div>
            <div class="error-item">
                <span class="error-icon">❌</span>
                <span class="error-text">Esquecer de ajustar para carga extra</span>
            </div>
        </div>
    </div>
</section>

<style>
/* Seção principal */
.calibration-procedure-section {
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

/* Preparação */
.preparation-section {
    margin-bottom: 24px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #e5e7eb;
}

.prep-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.prep-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}

.prep-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

.prep-item.temperature {
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
}

.prep-item.tool {
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
}

.prep-item.timing {
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
}

.prep-icon {
    font-size: 20px;
    flex-shrink: 0;
}

.prep-content {
    flex: 1;
}

.prep-name {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 2px;
}

.prep-desc {
    font-size: 11px;
    color: #6b7280;
    line-height: 1.3;
}

/* Passos */
.steps-section {
    margin-bottom: 24px;
}

.steps-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 16px;
    text-align: center;
}

.step-item {
    display: flex;
    gap: 16px;
    margin-bottom: 16px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #e5e7eb;
}

.step-number {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 700;
    flex-shrink: 0;
}

.step-content {
    flex: 1;
}

.step-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 4px;
}

.step-description {
    font-size: 12px;
    color: #6b7280;
    line-height: 1.4;
}

/* Valores de referência */
.reference-values {
    margin-bottom: 24px;
}

.ref-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.ref-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}

.ref-card {
    background: white;
    border-radius: 10px;
    padding: 16px;
    border: 2px solid;
}

.ref-card.solo {
    border-color: #16a34a;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
}

.ref-card.loaded {
    border-color: #dc2626;
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
}

.ref-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
    justify-content: center;
}

.ref-icon {
    font-size: 16px;
}

.ref-label {
    font-size: 12px;
    font-weight: 700;
    color: #374151;
}

.ref-pressures {
    display: flex;
    justify-content: space-around;
    gap: 16px;
}

.pressure-item {
    text-align: center;
    flex: 1;
}

.pressure-label {
    font-size: 11px;
    color: #6b7280;
    margin-bottom: 4px;
}

.pressure-value {
    font-size: 18px;
    font-weight: 700;
    color: #1f2937;
}

/* Dicas categorizadas */
.tips-section {
    margin-bottom: 24px;
}

.tips-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.tips-categories {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}

.tip-category {
    background: white;
    border-radius: 10px;
    padding: 12px;
    border: 2px solid;
}

.tip-category.critical {
    border-color: #dc2626;
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
}

.tip-category.warning {
    border-color: #f59e0b;
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
}

.tip-category.info {
    border-color: #3b82f6;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
}

.tip-category.success {
    border-color: #16a34a;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
}

.tip-cat-header {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
}

.tip-cat-icon {
    font-size: 12px;
}

.tip-cat-name {
    font-size: 10px;
    font-weight: 700;
    color: #374151;
}

.tip-cat-items {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.tip-cat-item {
    font-size: 10px;
    color: #4b5563;
    line-height: 1.3;
}

/* Frequência */
.frequency-section {
    margin-bottom: 24px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #e5e7eb;
}

.freq-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.freq-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}

.freq-item {
    text-align: center;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

.freq-item.daily {
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
}

.freq-item.weekly {
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
}

.freq-item.monthly {
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
}

.freq-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin-bottom: 4px;
}

.freq-icon {
    font-size: 14px;
}

.freq-period {
    font-size: 11px;
    font-weight: 700;
    color: #374151;
}

.freq-desc {
    font-size: 10px;
    color: #6b7280;
}

/* Erros comuns */
.common-errors {
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #e5e7eb;
}

.errors-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.errors-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.error-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px;
    background: #fef2f2;
    border-radius: 6px;
}

.error-icon {
    font-size: 12px;
    color: #dc2626;
    flex-shrink: 0;
}

.error-text {
    font-size: 11px;
    color: #4b5563;
    font-weight: 500;
}

/* Responsividade mobile */
@media (max-width: 768px) {
    .calibration-procedure-section {
        padding: 16px;
        margin: 16px 0;
    }
    
    .section-title {
        font-size: 18px;
    }
    
    .step-item {
        gap: 12px;
        padding: 12px;
    }
    
    .step-number {
        width: 28px;
        height: 28px;
        font-size: 12px;
    }
    
    .step-title {
        font-size: 13px;
    }
    
    .step-description {
        font-size: 11px;
    }
    
    .ref-pressures {
        gap: 12px;
    }
    
    .pressure-value {
        font-size: 16px;
    }
    
    .tips-categories {
        gap: 8px;
    }
    
    .tip-category {
        padding: 10px;
    }
    
    .freq-grid {
        gap: 8px;
    }
    
    .freq-item {
        padding: 10px;
    }
}
</style>