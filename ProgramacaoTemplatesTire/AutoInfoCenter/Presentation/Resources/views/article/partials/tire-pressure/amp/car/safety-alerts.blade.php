{{-- 
Partial: tire-pressure/amp/car/safety-alerts.blade.php
Alertas de segurança específicos para carros - AMP
Versão AMP com foco em segurança rodoviária e familiar
--}}

@php
    $criticalAlerts = $article->getData()['critical_alerts'] ?? [];
    $vehicleInfo = $article->getData()['vehicle_info'] ?? [];
    $maintenanceTips = $article->getData()['maintenance_tips'] ?? [];
    $hasTpms = method_exists($article, 'hasTpmsSystem') && $article->hasTpmsSystem();
@endphp

<section class="car-safety-alerts-section">
    <h2 class="section-title">🚗 Alertas de Segurança para Carros</h2>
    
    <!-- Grid de Alertas Principais -->
    <div class="alerts-grid">
        <div class="alert-card critical">
            <div class="alert-icon">🔴</div>
            <div class="alert-content">
                <div class="alert-title">Pressão Baixa</div>
                <div class="alert-description">Aumenta em 35% o risco de acidentes rodoviários</div>
                <div class="alert-action">Verificar mensalmente</div>
            </div>
        </div>
        
        <div class="alert-card warning">
            <div class="alert-icon">🟡</div>
            <div class="alert-content">
                <div class="alert-title">Sobrecarga</div>
                <div class="alert-description">5 passageiros + bagagem = ajustar pressão</div>
                <div class="alert-action">Aumentar +3 PSI traseiro</div>
            </div>
        </div>
        
        <div class="alert-card info">
            <div class="alert-icon">🔵</div>
            <div class="alert-content">
                <div class="alert-title">Estepe</div>
                <div class="alert-description">Verificar trimestralmente a pressão</div>
                <div class="alert-action">Manter sempre calibrado</div>
            </div>
        </div>
    </div>
    
    <!-- Estatísticas de Segurança Rodoviária -->
    <div class="road-safety-stats">
        <h3 class="stats-title">📊 Estatísticas de Segurança Rodoviária</h3>
        <div class="stats-grid">
            <div class="stat-item accidents">
                <div class="stat-number">35%</div>
                <div class="stat-label">Dos acidentes graves envolvem problemas com pneus</div>
                <div class="stat-icon">💥</div>
            </div>
            <div class="stat-item fuel">
                <div class="stat-number">15%</div>
                <div class="stat-label">Aumento no consumo com pressão baixa</div>
                <div class="stat-icon">⛽</div>
            </div>
            <div class="stat-item wear">
                <div class="stat-number">40%</div>
                <div class="stat-label">Redução da vida útil dos pneus</div>
                <div class="stat-icon">🔄</div>
            </div>
        </div>
    </div>
    
    <!-- TPMS - Sistema de Monitoramento -->
    @if($hasTpms)
    <div class="tpms-section">
        <h3 class="tpms-title">📱 Sistema TPMS</h3>
        <div class="tpms-info">
            <div class="tpms-card benefits">
                <div class="tpms-header">
                    <span class="tpms-icon">✅</span>
                    <span class="tpms-name">BENEFÍCIOS</span>
                </div>
                <div class="tpms-features">
                    <div class="feature-item">• Alerta automático de pressão baixa</div>
                    <div class="feature-item">• Monitoramento constante</div>
                    <div class="feature-item">• Redução de acidentes</div>
                    <div class="feature-item">• Economia de combustível</div>
                </div>
            </div>
            
            <div class="tpms-card limitations">
                <div class="tpms-header">
                    <span class="tpms-icon">⚠️</span>
                    <span class="tpms-name">LIMITAÇÕES</span>
                </div>
                <div class="tpms-features">
                    <div class="feature-item">• Não substitui verificação manual</div>
                    <div class="feature-item">• Alerta só com 25% de perda</div>
                    <div class="feature-item">• Sensores podem falhar</div>
                    <div class="feature-item">• Necessita reset após calibragem</div>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Checklist de Segurança Familiar -->
    <div class="family-safety-checklist">
        <h3 class="checklist-title">👨‍👩‍👧‍👦 Checklist de Segurança Familiar</h3>
        <div class="checklist-categories">
            <div class="check-category before-trip">
                <div class="category-header">
                    <span class="category-icon">🧳</span>
                    <span class="category-name">Antes da Viagem</span>
                </div>
                <div class="check-items">
                    <div class="check-item">
                        <span class="check-box">☐</span>
                        <span class="check-text">Verificar pressão de todos os pneus + estepe</span>
                    </div>
                    <div class="check-item">
                        <span class="check-box">☐</span>
                        <span class="check-text">Ajustar pressão para carga de bagagem</span>
                    </div>
                    <div class="check-item">
                        <span class="check-box">☐</span>
                        <span class="check-text">Inspecionar desgaste e sulcos</span>
                    </div>
                    <div class="check-item">
                        <span class="check-box">☐</span>
                        <span class="check-text">Verificar se há objetos presos</span>
                    </div>
                    <div class="check-item">
                        <span class="check-box">☐</span>
                        <span class="check-text">Testar TPMS (se disponível)</span>
                    </div>
                </div>
            </div>
            
            <div class="check-category monthly">
                <div class="category-header">
                    <span class="category-icon">📅</span>
                    <span class="category-name">Verificação Mensal</span>
                </div>
                <div class="check-items">
                    <div class="check-item">
                        <span class="check-box">☐</span>
                        <span class="check-text">Pressão com pneus frios</span>
                    </div>
                    <div class="check-item">
                        <span class="check-box">☐</span>
                        <span class="check-text">Inspeção visual completa</span>
                    </div>
                    <div class="check-item">
                        <span class="check-box">☐</span>
                        <span class="check-text">Verificar válvulas</span>
                    </div>
                    <div class="check-item">
                        <span class="check-box">☐</span>
                        <span class="check-text">Rodízio (quando necessário)</span>
                    </div>
                    <div class="check-item">
                        <span class="check-box">☐</span>
                        <span class="check-text">Alinhamento e balanceamento</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Situações de Emergência -->
    <div class="emergency-situations">
        <h3 class="emergency-title">🆘 Situações de Emergência</h3>
        <div class="emergency-grid">
            <div class="emergency-card flat-tire">
                <div class="emergency-header">
                    <span class="emergency-icon">🛞</span>
                    <span class="emergency-name">PNEU FURADO</span>
                </div>
                <div class="emergency-steps">
                    <div class="step-item">1. Pare em local seguro</div>
                    <div class="step-item">2. Sinalize o veículo</div>
                    <div class="step-item">3. Use o estepe ou chame socorro</div>
                    <div class="step-item">4. Dirija devagar até o conserto</div>
                </div>
            </div>
            
            <div class="emergency-card low-pressure">
                <div class="emergency-header">
                    <span class="emergency-icon">📉</span>
                    <span class="emergency-name">PRESSÃO MUITO BAIXA</span>
                </div>
                <div class="emergency-steps">
                    <div class="step-item">1. Reduza a velocidade</div>
                    <div class="step-item">2. Evite frenagens bruscas</div>
                    <div class="step-item">3. Procure posto mais próximo</div>
                    <div class="step-item">4. Calibre imediatamente</div>
                </div>
            </div>
            
            <div class="emergency-card blowout">
                <div class="emergency-header">
                    <span class="emergency-icon">💥</span>
                    <span class="emergency-name">ESTOURO</span>
                </div>
                <div class="emergency-steps">
                    <div class="step-item">1. Mantenha a calma</div>
                    <div class="step-item">2. Segure firme o volante</div>
                    <div class="step-item">3. Não freie bruscamente</div>
                    <div class="step-item">4. Encoste gradualmente</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Riscos da Aquaplanagem -->
    <div class="aquaplaning-risks">
        <h4 class="aqua-title">🌧️ Aquaplanagem - Riscos e Prevenção</h4>
        <div class="aqua-content">
            <div class="aqua-causes">
                <div class="aqua-header">
                    <span class="aqua-icon">⚠️</span>
                    <span class="aqua-name">CAUSAS PRINCIPAIS</span>
                </div>
                <div class="aqua-list">
                    <div class="aqua-item">• Pressão baixa dos pneus</div>
                    <div class="aqua-item">• Sulcos gastos (menos de 3mm)</div>
                    <div class="aqua-item">• Velocidade excessiva na chuva</div>
                    <div class="aqua-item">• Pneus inadequados para chuva</div>
                </div>
            </div>
            
            <div class="aqua-prevention">
                <div class="aqua-header">
                    <span class="aqua-icon">🛡️</span>
                    <span class="aqua-name">PREVENÇÃO</span>
                </div>
                <div class="aqua-list">
                    <div class="aqua-item">• Manter pressão correta</div>
                    <div class="aqua-item">• Trocar pneus com sulco baixo</div>
                    <div class="aqua-item">• Reduzir velocidade na chuva</div>
                    <div class="aqua-item">• Evitar poças e empoçamentos</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sinais de Alerta Visual -->
    <div class="visual-warning-signs">
        <h4 class="signs-title">👁️ Sinais Visuais de Alerta</h4>
        <div class="signs-grid">
            <div class="sign-item steering">
                <div class="sign-icon">🎯</div>
                <div class="sign-name">Direção</div>
                <div class="sign-symptoms">
                    <div class="symptom">• Puxando para um lado</div>
                    <div class="symptom">• Vibração no volante</div>
                    <div class="symptom">• Direção pesada</div>
                </div>
            </div>
            
            <div class="sign-item noise">
                <div class="sign-icon">🔊</div>
                <div class="sign-name">Ruídos</div>
                <div class="sign-symptoms">
                    <div class="symptom">• Ronco anormal</div>
                    <div class="symptom">• Chiado em curvas</div>
                    <div class="symptom">• Barulho metálico</div>
                </div>
            </div>
            
            <div class="sign-item visual">
                <div class="sign-icon">👀</div>
                <div class="sign-name">Aparência</div>
                <div class="sign-symptoms">
                    <div class="symptom">• Pneu murcho visualmente</div>
                    <div class="symptom">• Desgaste irregular</div>
                    <div class="symptom">• Rachaduras na lateral</div>
                </div>
            </div>
            
            <div class="sign-item performance">
                <div class="sign-icon">⚡</div>
                <div class="sign-name">Performance</div>
                <div class="sign-symptoms">
                    <div class="symptom">• Consumo aumentado</div>
                    <div class="symptom">• Frenagem deficiente</div>
                    <div class="symptom">• Perda de aderência</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Dicas de Economia e Segurança -->
    <div class="economy-safety-tips">
        <h4 class="tips-title">💰 Economia + Segurança</h4>
        <div class="tips-dual-grid">
            <div class="tip-card economy">
                <div class="tip-header">
                    <span class="tip-icon">💵</span>
                    <span class="tip-name">ECONOMIA</span>
                </div>
                <div class="tip-benefits">
                    <div class="benefit-item">✓ Até 15% menos combustível</div>
                    <div class="benefit-item">✓ Pneus duram 40% mais</div>
                    <div class="benefit-item">✓ Menos desgaste da suspensão</div>
                    <div class="benefit-item">✓ Evita reparos caros</div>
                </div>
            </div>
            
            <div class="tip-card safety">
                <div class="tip-header">
                    <span class="tip-icon">🛡️</span>
                    <span class="tip-name">SEGURANÇA</span>
                </div>
                <div class="tip-benefits">
                    <div class="benefit-item">✓ Reduz risco de acidentes</div>
                    <div class="benefit-item">✓ Melhor frenagem</div>
                    <div class="benefit-item">✓ Estabilidade em curvas</div>
                    <div class="benefit-item">✓ Aderência na chuva</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Alerta Final Crítico -->
    <div class="final-critical-alert">
        <div class="critical-header">
            <span class="critical-icon">🚨</span>
            <span class="critical-title">LEMBRE-SE SEMPRE</span>
        </div>
        <div class="critical-content">
            <strong>A segurança da sua família depende dos pneus.</strong><br>
            5 minutos de verificação mensal podem evitar acidentes graves e economizar centenas de reais.
        </div>
        <div class="critical-action">
            <div class="action-button">📅 Agende um lembrete mensal</div>
        </div>
    </div>
</section>

<style>
/* Seção principal */
.car-safety-alerts-section {
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

/* Grid de alertas principais */
.alerts-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
    margin-bottom: 24px;
}

.alert-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px;
    border-radius: 12px;
    border: 2px solid;
}

.alert-card.critical {
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
    border-color: #dc2626;
}

.alert-card.warning {
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
    border-color: #f59e0b;
}

.alert-card.info {
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    border-color: #3b82f6;
}

.alert-icon {
    font-size: 24px;
    flex-shrink: 0;
}

.alert-content {
    flex: 1;
}

.alert-title {
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 4px;
    color: #1f2937;
}

.alert-description {
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 4px;
    line-height: 1.3;
}

.alert-action {
    font-size: 11px;
    font-weight: 600;
    color: #374151;
}

/* Estatísticas rodoviárias */
.road-safety-stats {
    margin-bottom: 24px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #e5e7eb;
}

.stats-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.stats-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}

.stat-item {
    text-align: center;
    padding: 14px;
    border-radius: 10px;
    border: 2px solid;
    position: relative;
}

.stat-item.accidents {
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
    border-color: #dc2626;
}

.stat-item.fuel {
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
    border-color: #f59e0b;
}

.stat-item.wear {
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    border-color: #16a34a;
}

.stat-number {
    font-size: 28px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 8px;
    display: block;
}

.stat-label {
    font-size: 11px;
    color: #4b5563;
    line-height: 1.3;
    margin-bottom: 8px;
}

.stat-icon {
    font-size: 20px;
    position: absolute;
    top: 8px;
    right: 8px;
    opacity: 0.7;
}

/* TPMS Section */
.tpms-section {
    margin-bottom: 24px;
}

.tpms-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.tpms-info {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}

.tpms-card {
    background: white;
    border-radius: 10px;
    padding: 14px;
    border: 2px solid;
}

.tpms-card.benefits {
    border-color: #16a34a;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
}

.tpms-card.limitations {
    border-color: #f59e0b;
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
}

.tpms-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
}

.tpms-icon {
    font-size: 16px;
}

.tpms-name {
    font-size: 12px;
    font-weight: 700;
    color: #374151;
}

.tpms-features {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.feature-item {
    font-size: 11px;
    color: #4b5563;
    line-height: 1.3;
}

/* Checklist familiar */
.family-safety-checklist {
    margin-bottom: 24px;
}

.checklist-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.checklist-categories {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}

.check-category {
    background: white;
    border-radius: 10px;
    padding: 14px;
    border: 2px solid;
}

.check-category.before-trip {
    border-color: #7c3aed;
    background: linear-gradient(135deg, #f5f3ff, #ede9fe);
}

.check-category.monthly {
    border-color: #2563eb;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
}

.category-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
}

.category-icon {
    font-size: 16px;
}

.category-name {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
}

.check-items {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.check-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.check-box {
    font-size: 14px;
    color: #16a34a;
    flex-shrink: 0;
}

.check-text {
    font-size: 11px;
    color: #4b5563;
    line-height: 1.3;
}

/* Situações de emergência */
.emergency-situations {
    margin-bottom: 24px;
}

.emergency-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.emergency-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}

.emergency-card {
    background: white;
    border-radius: 8px;
    padding: 12px;
    border: 2px solid #dc2626;
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
}

.emergency-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}

.emergency-icon {
    font-size: 16px;
    color: #dc2626;
}

.emergency-name {
    font-size: 11px;
    font-weight: 700;
    color: #991b1b;
}

.emergency-steps {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.step-item {
    font-size: 10px;
    color: #7f1d1d;
    font-weight: 500;
}

/* Aquaplanagem */
.aquaplaning-risks {
    margin-bottom: 24px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 2px solid #3b82f6;
}

.aqua-title {
    font-size: 14px;
    font-weight: 600;
    color: #1e40af;
    margin-bottom: 12px;
    text-align: center;
}

.aqua-content {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}

.aqua-causes,
.aqua-prevention {
    padding: 12px;
    border-radius: 8px;
}

.aqua-causes {
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
}

.aqua-prevention {
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
}

.aqua-header {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
}

.aqua-icon {
    font-size: 14px;
}

.aqua-name {
    font-size: 11px;
    font-weight: 700;
    color: #374151;
}

.aqua-list {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.aqua-item {
    font-size: 10px;
    color: #4b5563;
}

/* Sinais visuais */
.visual-warning-signs {
    margin-bottom: 24px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #e5e7eb;
}

.signs-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.signs-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.sign-item {
    text-align: center;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    background: #f8fafc;
}

.sign-icon {
    font-size: 18px;
    margin-bottom: 6px;
    display: block;
}

.sign-name {
    font-size: 11px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 6px;
}

.sign-symptoms {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.symptom {
    font-size: 9px;
    color: #6b7280;
}

/* Economia e segurança */
.economy-safety-tips {
    margin-bottom: 24px;
}

.tips-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.tips-dual-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}

.tip-card {
    background: white;
    border-radius: 10px;
    padding: 14px;
    border: 2px solid;
}

.tip-card.economy {
    border-color: #16a34a;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
}

.tip-card.safety {
    border-color: #2563eb;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
}

.tip-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
}

.tip-icon {
    font-size: 16px;
}

.tip-name {
    font-size: 12px;
    font-weight: 700;
    color: #374151;
}

.tip-benefits {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.benefit-item {
    font-size: 11px;
    color: #4b5563;
    line-height: 1.3;
}

/* Alerta final crítico */
.final-critical-alert {
    background: linear-gradient(135deg, #1e40af, #2563eb);
    border-radius: 12px;
    padding: 16px;
    color: white;
    text-align: center;
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
    font-size: 16px;
    font-weight: 700;
    color: #dbeafe;
}

.critical-content {
    font-size: 13px;
    line-height: 1.4;
    color: #dbeafe;
    margin-bottom: 12px;
}

.critical-action {
    display: flex;
    justify-content: center;
}

.action-button {
    background: rgba(255, 255, 255, 0.2);
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    color: #dbeafe;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

/* Responsividade mobile */
@media (max-width: 768px) {
    .car-safety-alerts-section {
        padding: 16px;
        margin: 16px 0;
    }
    
    .section-title {
        font-size: 18px;
    }
    
    .alert-card {
        gap: 10px;
        padding: 12px;
    }
    
    .alert-icon {
        font-size: 20px;
    }
    
    .alert-title {
        font-size: 14px;
    }
    
    .stat-number {
        font-size: 24px;
    }
    
    .stat-icon {
        font-size: 16px;
        top: 6px;
        right: 6px;
    }
    
    .stats-grid,
    .alerts-grid,
    .tpms-info,
    .checklist-categories,
    .emergency-grid,
    .tips-dual-grid {
        gap: 8px;
    }
    
    .stat-item,
    .tpms-card,
    .check-category,
    .emergency-card,
    .tip-card {
        padding: 10px;
    }
    
    .signs-grid {
        grid-template-columns: 1fr;
        gap: 8px;
    }
    
    .aqua-content {
        gap: 8px;
    }
    
    .critical-content {
        font-size: 12px;
    }
    
    .action-button {
        font-size: 11px;
        padding: 6px 12px;
    }
}
</style>