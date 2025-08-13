{{-- 
Partial: tire-pressure/amp/shared/tpms-info.blade.php
Sistema TPMS (Tire Pressure Monitoring System) - AMP
Versão AMP compartilhada entre carros e motocicletas
--}}

@php
    $vehicleInfo = $article->getData()['vehicle_info'] ?? [];
    $tpmsInfo = $article->getData()['tpms_information'] ?? [];
    $hasTPMS = method_exists($article, 'hasTpmsSystem') ? $article->hasTpmsSystem() : !empty($tpmsInfo);
    $vehicleYear = $vehicleInfo['year_start'] ?? $vehicleInfo['year'] ?? 2020;
    $isModernVehicle = $vehicleYear >= 2012;
@endphp

<section class="tpms-info-section">
    <h2 class="section-title">📡 Sistema TPMS - Monitoramento de Pressão</h2>
    
    <!-- Status TPMS do Veículo -->
    <div class="tpms-status-card">
        <div class="status-header">
            <span class="status-icon">🚗</span>
            <span class="status-title">Status do Seu Veículo</span>
        </div>
        <div class="status-content">
            @if($hasTPMS)
            <div class="status-indicator equipped">
                <div class="indicator-light equipped"></div>
                <div class="indicator-text">
                    <div class="indicator-title">EQUIPADO COM TPMS</div>
                    <div class="indicator-desc">{{ $vehicleInfo['full_name'] ?? 'Seu veículo' }} possui sistema de monitoramento</div>
                </div>
            </div>
            @elseif($isModernVehicle)
            <div class="status-indicator probably">
                <div class="indicator-light probably"></div>
                <div class="indicator-text">
                    <div class="indicator-title">PROVAVELMENTE EQUIPADO</div>
                    <div class="indicator-desc">Veículos pós-2012 geralmente possuem TPMS</div>
                </div>
            </div>
            @else
            <div class="status-indicator not-equipped">
                <div class="indicator-light not-equipped"></div>
                <div class="indicator-text">
                    <div class="indicator-title">SEM TPMS</div>
                    <div class="indicator-desc">Verificação manual necessária</div>
                </div>
            </div>
            @endif
        </div>
    </div>
    
    <!-- O que é TPMS -->
    <div class="tpms-explanation">
        <h3 class="explanation-title">🤔 O que é o Sistema TPMS?</h3>
        <div class="explanation-content">
            <div class="explanation-text">
                O <strong>TPMS (Tire Pressure Monitoring System)</strong> é um sistema eletrônico que monitora 
                constantemente a pressão dos pneus e alerta o motorista quando há alguma anormalidade.
            </div>
            <div class="explanation-benefits">
                <div class="benefit-item safety">
                    <span class="benefit-icon">🛡️</span>
                    <span class="benefit-text">Aumenta a segurança</span>
                </div>
                <div class="benefit-item economy">
                    <span class="benefit-icon">⛽</span>
                    <span class="benefit-text">Economiza combustível</span>
                </div>
                <div class="benefit-item durability">
                    <span class="benefit-icon">🔧</span>
                    <span class="benefit-text">Prolonga vida dos pneus</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tipos de TPMS -->
    <div class="tpms-types">
        <h3 class="types-title">📊 Tipos de Sistema TPMS</h3>
        <div class="types-grid">
            <div class="type-card direct">
                <div class="type-header">
                    <span class="type-icon">📡</span>
                    <span class="type-name">TPMS DIRETO</span>
                </div>
                <div class="type-content">
                    <div class="type-description">
                        Sensores dentro de cada roda medem a pressão exata em tempo real
                    </div>
                    <div class="type-features">
                        <div class="feature-item">✅ Precisão alta</div>
                        <div class="feature-item">✅ Mostra pressão exata</div>
                        <div class="feature-item">✅ Detecta vazamentos rápidos</div>
                        <div class="feature-item">❌ Mais caro para manter</div>
                        <div class="feature-item">❌ Bateria dos sensores</div>
                    </div>
                </div>
            </div>
            
            <div class="type-card indirect">
                <div class="type-header">
                    <span class="type-icon">⚡</span>
                    <span class="type-name">TPMS INDIRETO</span>
                </div>
                <div class="type-content">
                    <div class="type-description">
                        Usa sensores ABS para detectar diferenças na rotação das rodas
                    </div>
                    <div class="type-features">
                        <div class="feature-item">✅ Mais barato</div>
                        <div class="feature-item">✅ Sem baterias para trocar</div>
                        <div class="feature-item">✅ Manutenção simples</div>
                        <div class="feature-item">❌ Menos preciso</div>
                        <div class="feature-item">❌ Não mostra pressão exata</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Luz de Alerta TPMS -->
    <div class="tpms-warning-light">
        <h3 class="warning-title">⚠️ Luz de Alerta TPMS</h3>
        <div class="warning-scenarios">
            <div class="scenario-card low-pressure">
                <div class="scenario-icon">🔴</div>
                <div class="scenario-content">
                    <div class="scenario-name">LUZ ACESA CONSTANTE</div>
                    <div class="scenario-meaning">Pressão baixa em um ou mais pneus</div>
                    <div class="scenario-action">
                        <strong>Ação:</strong> Verifique e calibre todos os pneus imediatamente
                    </div>
                </div>
            </div>
            
            <div class="scenario-card system-fault">
                <div class="scenario-icon">🟡</div>
                <div class="scenario-content">
                    <div class="scenario-name">LUZ PISCANDO</div>
                    <div class="scenario-meaning">Falha no sistema TPMS</div>
                    <div class="scenario-action">
                        <strong>Ação:</strong> Leve à oficina especializada para diagnóstico
                    </div>
                </div>
            </div>
            
            <div class="scenario-card normal">
                <div class="scenario-icon">✅</div>
                <div class="scenario-content">
                    <div class="scenario-name">LUZ APAGADA</div>
                    <div class="scenario-meaning">Sistema funcionando normalmente</div>
                    <div class="scenario-action">
                        <strong>Ação:</strong> Continue verificando mensalmente a pressão
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Como Resetar TPMS -->
    <div class="tpms-reset">
        <h3 class="reset-title">🔄 Como Resetar o Sistema TPMS</h3>
        <div class="reset-steps">
            <div class="step-card">
                <div class="step-number">1</div>
                <div class="step-content">
                    <div class="step-title">Calibre todos os pneus</div>
                    <div class="step-desc">Inclua o estepe na pressão correta</div>
                </div>
            </div>
            
            <div class="step-card">
                <div class="step-number">2</div>
                <div class="step-content">
                    <div class="step-title">Ligue o veículo</div>
                    <div class="step-desc">Motor ligado ou chave na posição ON</div>
                </div>
            </div>
            
            <div class="step-card">
                <div class="step-number">3</div>
                <div class="step-content">
                    <div class="step-title">Pressione o botão TPMS</div>
                    <div class="step-desc">Segure até a luz piscar 3 vezes</div>
                </div>
            </div>
            
            <div class="step-card">
                <div class="step-number">4</div>
                <div class="step-content">
                    <div class="step-title">Dirija por 10-15 minutos</div>
                    <div class="step-desc">Acima de 25 km/h para calibrar o sistema</div>
                </div>
            </div>
        </div>
        
        <div class="reset-alternatives">
            <div class="alternative-header">
                <span class="alt-icon">💡</span>
                <span class="alt-title">Métodos Alternativos</span>
            </div>
            <div class="alternative-methods">
                <div class="method-item">• Menu do painel de instrumentos</div>
                <div class="method-item">• Combinação de botões específica da marca</div>
                <div class="method-item">• Scanner automotivo em oficina</div>
                <div class="method-item">• Consulte manual do proprietário</div>
            </div>
        </div>
    </div>
    
    <!-- Manutenção TPMS -->
    <div class="tpms-maintenance">
        <h4 class="maintenance-title">🔧 Manutenção do Sistema TPMS</h4>
        <div class="maintenance-grid">
            <div class="maintenance-card sensors">
                <div class="maint-header">
                    <span class="maint-icon">🔋</span>
                    <span class="maint-name">SENSORES</span>
                </div>
                <div class="maint-content">
                    <div class="maint-info">Bateria dura 7-10 anos</div>
                    <div class="maint-cost">Custo: R$ 150-300 cada</div>
                    <div class="maint-tip">Troque todos juntos</div>
                </div>
            </div>
            
            <div class="maintenance-card valves">
                <div class="maint-header">
                    <span class="maint-icon">⚙️</span>
                    <span class="maint-name">VÁLVULAS</span>
                </div>
                <div class="maint-content">
                    <div class="maint-info">Troque junto com sensores</div>
                    <div class="maint-cost">Custo: R$ 20-40 cada</div>
                    <div class="maint-tip">Evita vazamentos</div>
                </div>
            </div>
            
            <div class="maintenance-card programming">
                <div class="maint-header">
                    <span class="maint-icon">💻</span>
                    <span class="maint-name">PROGRAMAÇÃO</span>
                </div>
                <div class="maint-content">
                    <div class="maint-info">Necessária após troca</div>
                    <div class="maint-cost">Custo: R$ 80-150</div>
                    <div class="maint-tip">Apenas oficina especializada</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Marcas e Compatibilidade -->
    <div class="brand-compatibility">
        <h4 class="brand-title">🏭 Compatibilidade por Marca</h4>
        <div class="brand-grid">
            <div class="brand-item european">
                <div class="brand-label">EUROPEIAS</div>
                <div class="brand-info">Volkswagen, Fiat, Peugeot, Renault</div>
                <div class="brand-note">TPMS indireto mais comum</div>
            </div>
            
            <div class="brand-item japanese">
                <div class="brand-label">JAPONESAS</div>
                <div class="brand-info">Toyota, Honda, Nissan, Mitsubishi</div>
                <div class="brand-note">TPMS direto em modelos premium</div>
            </div>
            
            <div class="brand-item american">
                <div class="brand-label">AMERICANAS</div>
                <div class="brand-info">Ford, Chevrolet, Jeep</div>
                <div class="brand-note">TPMS obrigatório desde 2008</div>
            </div>
            
            <div class="brand-item korean">
                <div class="brand-label">COREANAS</div>
                <div class="brand-info">Hyundai, Kia</div>
                <div class="brand-note">TPMS direto padrão</div>
            </div>
        </div>
    </div>
    
    <!-- Dicas Importantes -->
    <div class="tpms-tips">
        <h4 class="tips-title">💡 Dicas Importantes</h4>
        <div class="tips-list">
            <div class="tip-item critical">
                <span class="tip-icon">⚠️</span>
                <span class="tip-text">TPMS não substitui verificação manual mensal</span>
            </div>
            <div class="tip-item warning">
                <span class="tip-icon">🔧</span>
                <span class="tip-text">Sempre informe sobre TPMS ao trocar pneus</span>
            </div>
            <div class="tip-item info">
                <span class="tip-icon">❄️</span>
                <span class="tip-text">Sistema pode alertar no inverno (pressão baixa natural)</span>
            </div>
            <div class="tip-item success">
                <span class="tip-icon">✅</span>
                <span class="tip-text">Reset necessário após rodízio ou troca de pneus</span>
            </div>
        </div>
    </div>
    
    <!-- Problemas Comuns -->
    <div class="common-problems">
        <h4 class="problems-title">🚨 Problemas Comuns</h4>
        <div class="problems-grid">
            <div class="problem-item">
                <div class="problem-icon">🔴</div>
                <div class="problem-content">
                    <div class="problem-name">Luz não apaga após calibrar</div>
                    <div class="problem-solution">Reset necessário ou sensor com defeito</div>
                </div>
            </div>
            
            <div class="problem-item">
                <div class="problem-icon">🟡</div>
                <div class="problem-content">
                    <div class="problem-name">Luz acende no frio</div>
                    <div class="problem-solution">Normal - pressão diminui com temperatura</div>
                </div>
            </div>
            
            <div class="problem-item">
                <div class="problem-icon">🔋</div>
                <div class="problem-content">
                    <div class="problem-name">Sensor não funciona</div>
                    <div class="problem-solution">Bateria do sensor esgotada</div>
                </div>
            </div>
            
            <div class="problem-item">
                <div class="problem-icon">⚙️</div>
                <div class="problem-content">
                    <div class="problem-name">Pressão mostrada incorreta</div>
                    <div class="problem-solution">Calibração do sistema necessária</div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Seção principal */
.tpms-info-section {
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

/* Status TPMS */
.tpms-status-card {
    margin-bottom: 24px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #e5e7eb;
}

.status-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
    justify-content: center;
}

.status-icon {
    font-size: 18px;
}

.status-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
}

.status-content {
    display: flex;
    justify-content: center;
}

.status-indicator {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border-radius: 8px;
    max-width: 300px;
}

.status-indicator.equipped {
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
}

.status-indicator.probably {
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
}

.status-indicator.not-equipped {
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
}

.indicator-light {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    flex-shrink: 0;
}

.indicator-light.equipped {
    background: #16a34a;
    box-shadow: 0 0 8px rgba(22, 163, 74, 0.4);
}

.indicator-light.probably {
    background: #f59e0b;
    box-shadow: 0 0 8px rgba(245, 158, 11, 0.4);
}

.indicator-light.not-equipped {
    background: #dc2626;
    box-shadow: 0 0 8px rgba(220, 38, 38, 0.4);
}

.indicator-text {
    flex: 1;
}

.indicator-title {
    font-size: 12px;
    font-weight: 700;
    color: #374151;
    margin-bottom: 2px;
}

.indicator-desc {
    font-size: 10px;
    color: #6b7280;
}

/* Explicação TPMS */
.tpms-explanation {
    margin-bottom: 24px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #e5e7eb;
}

.explanation-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.explanation-content {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.explanation-text {
    font-size: 12px;
    color: #4b5563;
    line-height: 1.5;
    text-align: center;
}

.explanation-benefits {
    display: flex;
    justify-content: space-around;
    gap: 8px;
}

.benefit-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    flex: 1;
}

.benefit-icon {
    font-size: 16px;
}

.benefit-text {
    font-size: 10px;
    color: #6b7280;
    text-align: center;
    font-weight: 500;
}

/* Tipos de TPMS */
.tpms-types {
    margin-bottom: 24px;
}

.types-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.types-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}

.type-card {
    background: white;
    border-radius: 10px;
    padding: 14px;
    border: 2px solid;
}

.type-card.direct {
    border-color: #16a34a;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
}

.type-card.indirect {
    border-color: #3b82f6;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
}

.type-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
}

.type-icon {
    font-size: 16px;
}

.type-name {
    font-size: 12px;
    font-weight: 700;
    color: #374151;
}

.type-content {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.type-description {
    font-size: 11px;
    color: #4b5563;
    line-height: 1.3;
}

.type-features {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.feature-item {
    font-size: 10px;
    color: #374151;
}

/* Luz de alerta */
.tpms-warning-light {
    margin-bottom: 24px;
}

.warning-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.warning-scenarios {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}

.scenario-card {
    background: white;
    border-radius: 8px;
    padding: 12px;
    border: 1px solid #e5e7eb;
    display: flex;
    gap: 10px;
}

.scenario-icon {
    font-size: 18px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.scenario-content {
    flex: 1;
}

.scenario-name {
    font-size: 11px;
    font-weight: 700;
    color: #374151;
    margin-bottom: 3px;
}

.scenario-meaning {
    font-size: 10px;
    color: #6b7280;
    margin-bottom: 4px;
}

.scenario-action {
    font-size: 10px;
    color: #374151;
}

/* Reset TPMS */
.tpms-reset {
    margin-bottom: 24px;
}

.reset-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.reset-steps {
    display: grid;
    grid-template-columns: 1fr;
    gap: 8px;
    margin-bottom: 16px;
}

.step-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px;
    background: white;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

.step-number {
    width: 24px;
    height: 24px;
    background: linear-gradient(135deg, #0ea5e9, #0284c7);
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
    font-size: 12px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 2px;
}

.step-desc {
    font-size: 10px;
    color: #6b7280;
}

.reset-alternatives {
    background: white;
    border-radius: 8px;
    padding: 12px;
    border: 1px solid #f59e0b;
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
}

.alternative-header {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
}

.alt-icon {
    font-size: 14px;
    color: #f59e0b;
}

.alt-title {
    font-size: 12px;
    font-weight: 600;
    color: #92400e;
}

.alternative-methods {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.method-item {
    font-size: 10px;
    color: #451a03;
}

/* Manutenção TPMS */
.tpms-maintenance {
    margin-bottom: 24px;
}

.maintenance-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.maintenance-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}

.maintenance-card {
    background: white;
    border-radius: 8px;
    padding: 12px;
    border: 1px solid #e5e7eb;
}

.maint-header {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
}

.maint-icon {
    font-size: 14px;
    color: #3b82f6;
}

.maint-name {
    font-size: 11px;
    font-weight: 700;
    color: #374151;
}

.maint-content {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.maint-info {
    font-size: 10px;
    color: #4b5563;
}

.maint-cost {
    font-size: 10px;
    color: #dc2626;
    font-weight: 600;
}

.maint-tip {
    font-size: 9px;
    color: #6b7280;
    font-style: italic;
}

/* Compatibilidade por marca */
.brand-compatibility {
    margin-bottom: 24px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #e5e7eb;
}

.brand-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.brand-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.brand-item {
    padding: 10px;
    border-radius: 6px;
    text-align: center;
}

.brand-item.european {
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
}

.brand-item.japanese {
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
}

.brand-item.american {
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
}

.brand-item.korean {
    background: linear-gradient(135deg, #f5f3ff, #ede9fe);
}

.brand-label {
    font-size: 10px;
    font-weight: 700;
    color: #374151;
    margin-bottom: 4px;
}

.brand-info {
    font-size: 9px;
    color: #6b7280;
    margin-bottom: 4px;
    line-height: 1.2;
}

.brand-note {
    font-size: 8px;
    color: #9ca3af;
    font-style: italic;
}

/* Dicas importantes */
.tpms-tips {
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

.tip-item.critical {
    background: #fef2f2;
    border-left: 3px solid #dc2626;
}

.tip-item.warning {
    background: #fffbeb;
    border-left: 3px solid #f59e0b;
}

.tip-item.info {
    background: #eff6ff;
    border-left: 3px solid #3b82f6;
}

.tip-item.success {
    background: #f0fdf4;
    border-left: 3px solid #16a34a;
}

.tip-icon {
    font-size: 12px;
    flex-shrink: 0;
}

.tip-text {
    font-size: 11px;
    color: #374151;
    font-weight: 500;
    line-height: 1.3;
}

/* Problemas comuns */
.common-problems {
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #e5e7eb;
}

.problems-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.problems-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 8px;
}

.problem-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background: #f8fafc;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
}

.problem-icon {
    font-size: 16px;
    flex-shrink: 0;
}

.problem-content {
    flex: 1;
}

.problem-name {
    font-size: 11px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 2px;
}

.problem-solution {
    font-size: 10px;
    color: #6b7280;
    line-height: 1.3;
}

/* Responsividade mobile */
@media (max-width: 768px) {
    .tpms-info-section {
        padding: 16px;
        margin: 16px 0;
    }
    
    .section-title {
        font-size: 18px;
    }
    
    .status-indicator {
        gap: 8px;
        padding: 10px;
    }
    
    .explanation-benefits {
        flex-direction: column;
        gap: 6px;
    }
    
    .benefit-item {
        flex-direction: row;
        justify-content: flex-start;
        gap: 6px;
    }
    
    .types-grid {
        gap: 8px;
    }
    
    .type-card {
        padding: 12px;
    }
    
    .warning-scenarios {
        gap: 8px;
    }
    
    .scenario-card {
        gap: 8px;
        padding: 10px;
    }
    
    .reset-steps {
        gap: 6px;
    }
    
    .step-card {
        gap: 8px;
        padding: 8px;
    }
    
    .step-number {
        width: 20px;
        height: 20px;
        font-size: 10px;
    }
    
    .maintenance-grid {
        gap: 8px;
    }
    
    .maintenance-card {
        padding: 10px;
    }
    
    .brand-grid {
        grid-template-columns: 1fr;
        gap: 6px;
    }
    
    .brand-item {
        padding: 8px;
    }
    
    .tips-list {
        gap: 6px;
    }
    
    .tip-item {
        gap: 6px;
        padding: 6px;
    }
    
    .problems-grid {
        gap: 6px;
    }
    
    .problem-item {
        gap: 8px;
        padding: 8px;
    }
}
</style>