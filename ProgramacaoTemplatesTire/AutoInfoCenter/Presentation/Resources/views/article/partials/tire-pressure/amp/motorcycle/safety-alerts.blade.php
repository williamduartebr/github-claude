{{-- 
Partial: tire-pressure/amp/motorcycle/safety-alerts.blade.php
Alertas críticos de segurança para motocicletas - AMP REFATORADO
Versão AMP otimizada com foco em alertas essenciais e ações práticas
--}}

@php
    $criticalAlerts = $article->getData()['critical_alerts'] ?? [];
    $vehicleInfo = $article->getData()['vehicle_info'] ?? [];
    $motorcycleCategory = $vehicleInfo['category'] ?? 'standard';
@endphp

<section class="safety-alerts-section">
    <h2 class="section-title">🚨 Alertas Críticos de Segurança</h2>
    
    <!-- Alerta Principal -->
    <div class="main-danger-alert">
        <div class="danger-icon">💀</div>
        <div class="danger-content">
            <div class="danger-title">PRESSÃO INCORRETA PODE SER FATAL</div>
            <div class="danger-text">
                Em motocicletas, erro de pressão causa <strong>85% dos acidentes graves</strong> 
                relacionados a pneus. Não é só conforto - é sua vida!
            </div>
        </div>
    </div>
    
    <!-- Alertas por Situação -->
    <div class="situation-alerts">
        <h3 class="alerts-title">⚠️ Situações de Alto Risco</h3>
        <div class="alerts-grid">
            <div class="alert-card low-pressure">
                <div class="alert-header">
                    <span class="alert-icon">🔴</span>
                    <span class="alert-name">PRESSÃO BAIXA</span>
                </div>
                <div class="alert-content">
                    <div class="alert-risk">RISCO EXTREMO</div>
                    <div class="alert-consequences">
                        <div class="consequence">• Instabilidade em curvas</div>
                        <div class="consequence">• Perda de controle súbita</div>
                        <div class="consequence">• Aquaplanagem fácil</div>
                        <div class="consequence">• Estouro em alta velocidade</div>
                    </div>
                </div>
            </div>
            
            <div class="alert-card high-pressure">
                <div class="alert-header">
                    <span class="alert-icon">🟡</span>
                    <span class="alert-name">PRESSÃO ALTA</span>
                </div>
                <div class="alert-content">
                    <div class="alert-risk">RISCO ALTO</div>
                    <div class="alert-consequences">
                        <div class="consequence">• Perda de aderência</div>
                        <div class="consequence">• Frenagem ineficiente</div>
                        <div class="consequence">• Moto "saltitante"</div>
                        <div class="consequence">• Desgaste central excessivo</div>
                    </div>
                </div>
            </div>
            
            <div class="alert-card unbalanced">
                <div class="alert-header">
                    <span class="alert-icon">⚖️</span>
                    <span class="alert-name">PRESSÕES DIFERENTES</span>
                </div>
                <div class="alert-content">
                    <div class="alert-risk">RISCO CRÍTICO</div>
                    <div class="alert-consequences">
                        <div class="consequence">• Moto puxa para um lado</div>
                        <div class="consequence">• Frenagem desigual</div>
                        <div class="consequence">• Perda de estabilidade</div>
                        <div class="consequence">• Acidentes em linha reta</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sinais de Perigo -->
    <div class="danger-signs">
        <h3 class="signs-title">🚩 Sinais de Perigo Imediato</h3>
        <div class="signs-checklist">
            <div class="sign-category critical">
                <div class="category-header">
                    <span class="category-icon">🛑</span>
                    <span class="category-name">PARE IMEDIATAMENTE</span>
                </div>
                <div class="signs-list">
                    <div class="sign-item">• Pneu visualmente murcho</div>
                    <div class="sign-item">• Vibração no guidão</div>
                    <div class="sign-item">• Moto puxa para um lado</div>
                    <div class="sign-item">• Ruído anormal do pneu</div>
                </div>
            </div>
            
            <div class="sign-category urgent">
                <div class="category-header">
                    <span class="category-icon">⚠️</span>
                    <span class="category-name">VERIFICAR HOJE</span>
                </div>
                <div class="signs-list">
                    <div class="sign-item">• Frenagem mais longa</div>
                    <div class="sign-item">• Curvas instáveis</div>
                    <div class="sign-item">• Consumo aumentou</div>
                    <div class="sign-item">• Desgaste irregular</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Estatísticas de Segurança -->
    <div class="safety-statistics">
        <h3 class="stats-title">📊 Estatísticas que Salvam Vidas</h3>
        <div class="stats-grid">
            <div class="stat-item critical">
                <div class="stat-number">85%</div>
                <div class="stat-text">dos acidentes graves envolvem pressão incorreta</div>
            </div>
            
            <div class="stat-item warning">
                <div class="stat-number">3x</div>
                <div class="stat-text">maior chance de derrapagem</div>
            </div>
            
            <div class="stat-item info">
                <div class="stat-number">50%</div>
                <div class="stat-text">mais desgaste com pressão baixa</div>
            </div>
        </div>
    </div>
    
    <!-- Ações Imediatas -->
    <div class="immediate-actions">
        <h3 class="actions-title">🆘 Ações Imediatas</h3>
        <div class="actions-steps">
            <div class="action-step">
                <div class="step-number">1</div>
                <div class="step-content">
                    <div class="step-title">PARE EM SEGURANÇA</div>
                    <div class="step-desc">Local plano, longe do trânsito</div>
                </div>
            </div>
            
            <div class="action-step">
                <div class="step-number">2</div>
                <div class="step-content">
                    <div class="step-title">VERIFIQUE VISUALMENTE</div>
                    <div class="step-desc">Pneus, objetos presos, deformações</div>
                </div>
            </div>
            
            <div class="action-step">
                <div class="step-number">3</div>
                <div class="step-content">
                    <div class="step-title">MEÇA A PRESSÃO</div>
                    <div class="step-desc">Use manômetro se disponível</div>
                </div>
            </div>
            
            <div class="action-step">
                <div class="step-number">4</div>
                <div class="step-content">
                    <div class="step-title">PROCURE AJUDA</div>
                    <div class="step-desc">Posto, borracharia ou guincho</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Prevenção Diária -->
    <div class="daily-prevention">
        <h4 class="prevention-title">✅ Prevenção Diária (2 minutos)</h4>
        <div class="prevention-routine">
            <div class="routine-time morning">
                <div class="time-label">🌅 ANTES DE SAIR</div>
                <div class="time-tasks">
                    <div class="task">• Olhar os pneus rapidamente</div>
                    <div class="task">• Verificar se há objetos presos</div>
                    <div class="task">• Observar se algum está murcho</div>
                </div>
            </div>
            
            <div class="routine-time weekly">
                <div class="time-label">📅 TODA SEMANA</div>
                <div class="time-tasks">
                    <div class="task">• Medir pressão com manômetro</div>
                    <div class="task">• Verificar desgaste dos sulcos</div>
                    <div class="task">• Limpar pedras dos pneus</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cuidados Específicos -->
    @if($motorcycleCategory === 'sport')
    <div class="category-warnings sport">
        <h4 class="warning-title">🏁 Atenção: Moto Esportiva</h4>
        <div class="warning-content">
            <div class="warning-text">
                Motos esportivas são <strong>ainda mais sensíveis</strong> à pressão incorreta. 
                Uma curva em alta velocidade com pressão errada pode ser fatal.
            </div>
            <div class="warning-actions">
                <div class="action">• Verificar antes de CADA saída</div>
                <div class="action">• Aquecimento gradual obrigatório</div>
                <div class="action">• Pressão específica para track days</div>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Emergência -->
    <div class="emergency-contact">
        <div class="emergency-header">
            <span class="emergency-icon">📞</span>
            <span class="emergency-title">EM CASO DE EMERGÊNCIA</span>
        </div>
        <div class="emergency-content">
            <div class="emergency-text">
                Se você está na estrada e suspeita de problema nos pneus:
            </div>
            <div class="emergency-actions">
                <div class="emergency-action critical">🛑 Reduza velocidade IMEDIATAMENTE</div>
                <div class="emergency-action warning">⚠️ Procure local seguro para parar</div>
                <div class="emergency-action info">📞 Ligue para o guincho se necessário</div>
            </div>
            <div class="emergency-reminder">
                <strong>Sua vida vale mais que o atraso!</strong>
            </div>
        </div>
    </div>
</section>

<style>
/* Seção principal */
.safety-alerts-section {
    margin: 24px 0;
    padding: 20px;
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
    border-radius: 16px;
    border: 3px solid #dc2626;
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

/* Alerta principal */
.main-danger-alert {
    margin-bottom: 24px;
    background: linear-gradient(135deg, #7f1d1d, #991b1b);
    border-radius: 12px;
    padding: 16px;
    color: white;
    display: flex;
    align-items: center;
    gap: 12px;
    border: 2px solid #fbbf24;
}

.danger-icon {
    font-size: 28px;
    flex-shrink: 0;
}

.danger-content {
    flex: 1;
}

.danger-title {
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 6px;
    color: #fbbf24;
}

.danger-text {
    font-size: 12px;
    line-height: 1.4;
    color: #fee2e2;
}

/* Alertas por situação */
.situation-alerts {
    margin-bottom: 24px;
}

.alerts-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.alerts-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}

.alert-card {
    background: white;
    border-radius: 10px;
    padding: 12px;
    border: 2px solid;
}

.alert-card.low-pressure {
    border-color: #dc2626;
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
}

.alert-card.high-pressure {
    border-color: #f59e0b;
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
}

.alert-card.unbalanced {
    border-color: #7c3aed;
    background: linear-gradient(135deg, #f5f3ff, #ede9fe);
}

.alert-header {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
}

.alert-icon {
    font-size: 14px;
}

.alert-name {
    font-size: 11px;
    font-weight: 700;
    color: #374151;
}

.alert-content {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.alert-risk {
    font-size: 10px;
    font-weight: 700;
    color: #dc2626;
    text-align: center;
    padding: 4px;
    background: rgba(220, 38, 38, 0.1);
    border-radius: 4px;
}

.alert-consequences {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.consequence {
    font-size: 10px;
    color: #4b5563;
    line-height: 1.3;
}

/* Sinais de perigo */
.danger-signs {
    margin-bottom: 24px;
}

.signs-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.signs-checklist {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}

.sign-category {
    background: white;
    border-radius: 8px;
    padding: 12px;
    border: 2px solid;
}

.sign-category.critical {
    border-color: #dc2626;
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
}

.sign-category.urgent {
    border-color: #f59e0b;
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
}

.category-header {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
}

.category-icon {
    font-size: 14px;
}

.category-name {
    font-size: 11px;
    font-weight: 700;
    color: #374151;
}

.signs-list {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.sign-item {
    font-size: 10px;
    color: #4b5563;
    line-height: 1.3;
}

/* Estatísticas */
.safety-statistics {
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
    gap: 10px;
}

.stat-item {
    text-align: center;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

.stat-item.critical {
    background: #fef2f2;
}

.stat-item.warning {
    background: #fffbeb;
}

.stat-item.info {
    background: #eff6ff;
}

.stat-number {
    font-size: 24px;
    font-weight: 700;
    color: #dc2626;
    margin-bottom: 4px;
}

.stat-text {
    font-size: 10px;
    color: #4b5563;
    line-height: 1.3;
}

/* Ações imediatas */
.immediate-actions {
    margin-bottom: 24px;
}

.actions-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.actions-steps {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.action-step {
    background: white;
    border-radius: 8px;
    padding: 10px;
    border: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    gap: 10px;
}

.step-number {
    width: 24px;
    height: 24px;
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    flex-shrink: 0;
}

.step-content {
    flex: 1;
}

.step-title {
    font-size: 11px;
    font-weight: 700;
    color: #374151;
    margin-bottom: 2px;
}

.step-desc {
    font-size: 10px;
    color: #6b7280;
}

/* Prevenção diária */
.daily-prevention {
    margin-bottom: 24px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 2px solid #16a34a;
}

.prevention-title {
    font-size: 14px;
    font-weight: 600;
    color: #166534;
    margin-bottom: 12px;
    text-align: center;
}

.prevention-routine {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}

.routine-time {
    background: #f8fafc;
    border-radius: 8px;
    padding: 10px;
}

.routine-time.morning {
    border: 1px solid #16a34a;
}

.routine-time.weekly {
    border: 1px solid #3b82f6;
}

.time-label {
    font-size: 11px;
    font-weight: 700;
    color: #374151;
    margin-bottom: 6px;
}

.time-tasks {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.task {
    font-size: 10px;
    color: #4b5563;
}

/* Cuidados específicos */
.category-warnings {
    margin-bottom: 24px;
    background: white;
    border-radius: 10px;
    padding: 14px;
    border: 2px solid #dc2626;
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
}

.warning-title {
    font-size: 14px;
    font-weight: 600;
    color: #991b1b;
    margin-bottom: 8px;
    text-align: center;
}

.warning-content {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.warning-text {
    font-size: 11px;
    color: #7f1d1d;
    line-height: 1.4;
    text-align: center;
}

.warning-actions {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.action {
    font-size: 10px;
    color: #991b1b;
    font-weight: 500;
}

/* Emergência */
.emergency-contact {
    background: linear-gradient(135deg, #0c4a6e, #075985);
    border-radius: 12px;
    padding: 16px;
    color: white;
}

.emergency-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-bottom: 10px;
}

.emergency-icon {
    font-size: 18px;
    color: #fbbf24;
}

.emergency-title {
    font-size: 14px;
    font-weight: 700;
    color: #fef2f2;
}

.emergency-content {
    text-align: center;
}

.emergency-text {
    font-size: 11px;
    color: #e0f2fe;
    margin-bottom: 8px;
}

.emergency-actions {
    display: flex;
    flex-direction: column;
    gap: 4px;
    margin-bottom: 8px;
}

.emergency-action {
    font-size: 10px;
    padding: 4px 8px;
    border-radius: 4px;
}

.emergency-action.critical {
    background: rgba(220, 38, 38, 0.3);
    color: #fef2f2;
}

.emergency-action.warning {
    background: rgba(245, 158, 11, 0.3);
    color: #fefce8;
}

.emergency-action.info {
    background: rgba(59, 130, 246, 0.3);
    color: #eff6ff;
}

.emergency-reminder {
    font-size: 12px;
    font-weight: 700;
    color: #fbbf24;
    margin-top: 8px;
}

/* Responsividade mobile */
@media (max-width: 768px) {
    .safety-alerts-section {
        padding: 16px;
        margin: 16px 0;
    }
    
    .section-title {
        font-size: 18px;
    }
    
    .main-danger-alert {
        gap: 8px;
        padding: 12px;
    }
    
    .danger-icon {
        font-size: 24px;
    }
    
    .danger-title {
        font-size: 14px;
    }
    
    .danger-text {
        font-size: 11px;
    }
    
    .alerts-grid,
    .signs-checklist,
    .stats-grid,
    .prevention-routine {
        gap: 8px;
    }
    
    .alert-card,
    .sign-category {
        padding: 10px;
    }
    
    .stat-number {
        font-size: 20px;
    }
    
    .action-step {
        gap: 8px;
        padding: 8px;
    }
    
    .step-number {
        width: 20px;
        height: 20px;
        font-size: 10px;
    }
    
    .emergency-contact {
        padding: 12px;
    }
}
</style>