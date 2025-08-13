{{-- 
Partial: tire-pressure/amp/car/spare-tire.blade.php
Informações específicas do pneu estepe para carros - AMP
Versão AMP com pressões, limitações e cuidados especiais
--}}

@php
    $spareTireInfo = $article->getData()['spare_tire_info'] ?? [];
    $vehicleInfo = $article->getData()['vehicle_info'] ?? [];
    $tireSpecs = $article->getData()['tire_specifications_by_version'] ?? [];
    
    // Dados do estepe ou fallbacks padrão
    $sparePressure = $spareTireInfo['pressure'] ?? '60 PSI';
    $spareType = $spareTireInfo['type'] ?? 'Temporário';
    $maxSpeed = $spareTireInfo['max_speed'] ?? '80 km/h';
    $maxDistance = $spareTireInfo['max_distance'] ?? '80 km';
@endphp

<section class="spare-tire-section">
    <h2 class="section-title">🛞 Pneu Estepe</h2>
    
    <!-- Informações Principais do Estepe -->
    <div class="spare-main-info">
        <div class="spare-card">
            <div class="spare-header">
                <div class="spare-icon-large">🛞</div>
                <div class="spare-details">
                    <div class="spare-title">{{ $vehicleInfo['model_name'] ?? 'Seu Veículo' }}</div>
                    <div class="spare-subtitle">Informações do Pneu Estepe</div>
                </div>
                <div class="spare-type-badge">
                    @if(str_contains(strtolower($spareType), 'temporário') || str_contains(strtolower($spareType), 'temp'))
                    <span class="type-badge temporary">⏱️ Temporário</span>
                    @elseif(str_contains(strtolower($spareType), 'completo') || str_contains(strtolower($spareType), 'full'))
                    <span class="type-badge full">✅ Completo</span>
                    @else
                    <span class="type-badge standard">🔧 Padrão</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Dados Técnicos Principais -->
    <div class="spare-specs-summary">
        <div class="specs-grid">
            <div class="spec-card pressure">
                <div class="spec-icon">📊</div>
                <div class="spec-content">
                    <div class="spec-label">Pressão Recomendada</div>
                    <div class="spec-value">{{ $sparePressure }}</div>
                    <div class="spec-note">Sempre calibrar antes do uso</div>
                </div>
            </div>
            
            <div class="spec-card speed">
                <div class="spec-icon">🚗</div>
                <div class="spec-content">
                    <div class="spec-label">Velocidade Máxima</div>
                    <div class="spec-value">{{ $maxSpeed }}</div>
                    <div class="spec-note">Não exceder este limite</div>
                </div>
            </div>
            
            <div class="spec-card distance">
                <div class="spec-icon">📏</div>
                <div class="spec-content">
                    <div class="spec-label">Distância Máxima</div>
                    <div class="spec-value">{{ $maxDistance }}</div>
                    <div class="spec-note">Para uso temporário</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tipos de Estepe -->
    <div class="spare-types">
        <h3 class="types-title">🔍 Tipos de Pneu Estepe</h3>
        <div class="types-grid">
            <div class="type-card temporary {{ str_contains(strtolower($spareType), 'temporário') ? 'active' : '' }}">
                <div class="type-header">
                    <span class="type-icon">⏱️</span>
                    <span class="type-name">ESTEPE TEMPORÁRIO</span>
                </div>
                <div class="type-features">
                    <div class="feature-item">• Menor que pneus originais</div>
                    <div class="feature-item">• Pressão mais alta (60 PSI)</div>
                    <div class="feature-item">• Máximo 80 km/h</div>
                    <div class="feature-item">• Até 80 km de distância</div>
                    <div class="feature-item">• Mais leve e compacto</div>
                </div>
                <div class="type-warning">⚠️ Apenas para emergências</div>
            </div>
            
            <div class="type-card full {{ str_contains(strtolower($spareType), 'completo') ? 'active' : '' }}">
                <div class="type-header">
                    <span class="type-icon">✅</span>
                    <span class="type-name">ESTEPE COMPLETO</span>
                </div>
                <div class="type-features">
                    <div class="feature-item">• Igual aos pneus originais</div>
                    <div class="feature-item">• Mesma pressão dos outros</div>
                    <div class="feature-item">• Sem limite de velocidade</div>
                    <div class="feature-item">• Uso prolongado permitido</div>
                    <div class="feature-item">• Pode fazer rodízio</div>
                </div>
                <div class="type-warning">✅ Substituto permanente</div>
            </div>
        </div>
    </div>
    
    <!-- Procedimento de Troca -->
    <div class="change-procedure">
        <h3 class="procedure-title">🔧 Procedimento de Troca</h3>
        <div class="procedure-steps">
            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-content">
                    <div class="step-title">Segurança Primeiro</div>
                    <div class="step-description">
                        Pare em local seguro, sinalize com triângulo, 
                        acione pisca-alerta e certifique-se que o carro está estável.
                    </div>
                </div>
            </div>
            
            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-content">
                    <div class="step-title">Prepare as Ferramentas</div>
                    <div class="step-description">
                        Retire macaco, chave de roda e o estepe. 
                        Verifique a pressão do estepe antes de instalar.
                    </div>
                </div>
            </div>
            
            <div class="step-item">
                <div class="step-number">3</div>
                <div class="step-content">
                    <div class="step-title">Troque o Pneu</div>
                    <div class="step-description">
                        Afrouxe as porcas, levante o carro, remova o pneu furado 
                        e instale o estepe. Aperte as porcas em cruz.
                    </div>
                </div>
            </div>
            
            <div class="step-item">
                <div class="step-number">4</div>
                <div class="step-content">
                    <div class="step-title">Verificação Final</div>
                    <div class="step-description">
                        Abaixe o carro, aperte definitivamente as porcas 
                        e verifique novamente a pressão do estepe.
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cuidados Especiais -->
    <div class="special-care">
        <h3 class="care-title">⚠️ Cuidados Especiais</h3>
        <div class="care-categories">
            <div class="care-category pressure">
                <div class="care-header">
                    <span class="care-icon">📊</span>
                    <span class="care-name">PRESSÃO</span>
                </div>
                <div class="care-items">
                    <div class="care-item">• Verificar mensalmente</div>
                    <div class="care-item">• Calibrar antes de viajar</div>
                    <div class="care-item">• Pressão mais alta que normal</div>
                    <div class="care-item">• Pode perder ar mais rápido</div>
                </div>
            </div>
            
            <div class="care-category usage">
                <div class="care-header">
                    <span class="care-icon">🚗</span>
                    <span class="care-name">USO</span>
                </div>
                <div class="care-items">
                    <div class="care-item">• Procure borracharia rapidamente</div>
                    <div class="care-item">• Evite curvas em alta velocidade</div>
                    <div class="care-item">• Freie suavemente</div>
                    <div class="care-item">• Não carregue peso excessivo</div>
                </div>
            </div>
            
            <div class="care-category maintenance">
                <div class="care-header">
                    <span class="care-icon">🔧</span>
                    <span class="care-name">MANUTENÇÃO</span>
                </div>
                <div class="care-items">
                    <div class="care-item">• Inspecionar regularmente</div>
                    <div class="care-item">• Trocar a cada 6-8 anos</div>
                    <div class="care-item">• Verificar data de fabricação</div>
                    <div class="care-item">• Guardar limpo e seco</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Verificação e Manutenção -->
    <div class="maintenance-schedule">
        <h4 class="maintenance-title">📅 Cronograma de Verificação</h4>
        <div class="schedule-table">
            <div class="schedule-row">
                <div class="schedule-frequency">MENSAL</div>
                <div class="schedule-task">Verificar pressão</div>
                <div class="schedule-importance">Crítico</div>
            </div>
            <div class="schedule-row">
                <div class="schedule-frequency">ANTES DE VIAGENS</div>
                <div class="schedule-task">Inspeção completa</div>
                <div class="schedule-importance">Essencial</div>
            </div>
            <div class="schedule-row">
                <div class="schedule-frequency">SEMESTRAL</div>
                <div class="schedule-task">Verificar condição geral</div>
                <div class="schedule-importance">Importante</div>
            </div>
            <div class="schedule-row">
                <div class="schedule-frequency">ANUAL</div>
                <div class="schedule-task">Avaliar troca</div>
                <div class="schedule-importance">Preventivo</div>
            </div>
        </div>
    </div>
    
    <!-- Localização do Estepe -->
    <div class="spare-location">
        <h4 class="location-title">📍 Onde Encontrar o Estepe</h4>
        <div class="location-options">
            <div class="location-option common">
                <div class="location-icon">🚗</div>
                <div class="location-info">
                    <div class="location-name">Porta-malas</div>
                    <div class="location-desc">Sob o assoalho do porta-malas</div>
                </div>
            </div>
            <div class="location-option suv">
                <div class="location-icon">🚙</div>
                <div class="location-info">
                    <div class="location-name">SUVs</div>
                    <div class="location-desc">Fixado na parte traseira externa</div>
                </div>
            </div>
            <div class="location-option pickup">
                <div class="location-icon">🛻</div>
                <div class="location-info">
                    <div class="location-name">Pick-ups</div>
                    <div class="location-desc">Sob a caçamba ou chassi</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quando NÃO Usar o Estepe -->
    <div class="dont-use-when">
        <h4 class="dont-title">🚫 Quando NÃO Usar o Estepe</h4>
        <div class="dont-list">
            <div class="dont-item critical">
                <span class="dont-icon">❌</span>
                <span class="dont-text">Estepe com pressão muito baixa</span>
            </div>
            <div class="dont-item critical">
                <span class="dont-icon">❌</span>
                <span class="dont-text">Estepe com rachaduras ou deformações</span>
            </div>
            <div class="dont-item critical">
                <span class="dont-icon">❌</span>
                <span class="dont-text">Mais de 8 anos de fabricação</span>
            </div>
            <div class="dont-item warning">
                <span class="dont-icon">⚠️</span>
                <span class="dont-text">Chuva forte ou pista muito molhada</span>
            </div>
            <div class="dont-item warning">
                <span class="dont-icon">⚠️</span>
                <span class="dont-text">Viagens longas sem borracharia</span>
            </div>
        </div>
    </div>
    
    <!-- Dica Final -->
    <div class="final-tip">
        <div class="tip-header">
            <span class="tip-icon">💡</span>
            <span class="tip-title">Dica Importante</span>
        </div>
        <div class="tip-content">
            <strong>O estepe é uma solução temporária!</strong> Mesmo que seja do tipo "completo", 
            procure uma borracharia o mais rápido possível para reparar ou substituir o pneu danificado. 
            Dirigir com o estepe por muito tempo pode afetar o alinhamento e desgastar outros componentes.
        </div>
    </div>
</section>

<style>
/* Seção principal */
.spare-tire-section {
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

/* Informações principais */
.spare-main-info {
    margin-bottom: 24px;
}

.spare-card {
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.spare-header {
    display: flex;
    align-items: center;
    gap: 12px;
}

.spare-icon-large {
    font-size: 32px;
    flex-shrink: 0;
}

.spare-details {
    flex: 1;
}

.spare-title {
    font-size: 18px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 4px;
}

.spare-subtitle {
    font-size: 12px;
    color: #6b7280;
}

.spare-type-badge {
    flex-shrink: 0;
}

.type-badge {
    font-size: 11px;
    font-weight: 600;
    padding: 6px 10px;
    border-radius: 12px;
    color: white;
}

.type-badge.temporary {
    background: linear-gradient(135deg, #f59e0b, #d97706);
}

.type-badge.full {
    background: linear-gradient(135deg, #16a34a, #15803d);
}

.type-badge.standard {
    background: linear-gradient(135deg, #64748b, #475569);
}

/* Resumo de especificações */
.spare-specs-summary {
    margin-bottom: 24px;
}

.specs-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}

.spec-card {
    background: white;
    border-radius: 10px;
    padding: 14px;
    border: 2px solid;
    display: flex;
    align-items: center;
    gap: 12px;
}

.spec-card.pressure {
    border-color: #dc2626;
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
}

.spec-card.speed {
    border-color: #f59e0b;
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
}

.spec-card.distance {
    border-color: #3b82f6;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
}

.spec-icon {
    font-size: 20px;
    flex-shrink: 0;
}

.spec-content {
    flex: 1;
}

.spec-label {
    font-size: 11px;
    color: #6b7280;
    margin-bottom: 2px;
}

.spec-value {
    font-size: 16px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 2px;
}

.spec-note {
    font-size: 9px;
    color: #6b7280;
    font-style: italic;
}

/* Tipos de estepe */
.spare-types {
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
    border: 2px solid #e5e7eb;
}

.type-card.active {
    border-color: #2563eb;
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

.type-features {
    display: flex;
    flex-direction: column;
    gap: 4px;
    margin-bottom: 8px;
}

.feature-item {
    font-size: 10px;
    color: #4b5563;
}

.type-warning {
    font-size: 10px;
    font-weight: 600;
    text-align: center;
    padding: 4px 8px;
    border-radius: 4px;
}

.type-card.temporary .type-warning {
    background: #fef3c7;
    color: #92400e;
}

.type-card.full .type-warning {
    background: #dcfce7;
    color: #166534;
}

/* Procedimento de troca */
.change-procedure {
    margin-bottom: 24px;
}

.procedure-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.procedure-steps {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.step-item {
    display: flex;
    gap: 12px;
    background: white;
    border-radius: 10px;
    padding: 14px;
    border: 1px solid #e5e7eb;
}

.step-number {
    width: 28px;
    height: 28px;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
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
    margin-bottom: 4px;
}

.step-description {
    font-size: 11px;
    color: #6b7280;
    line-height: 1.4;
}

/* Cuidados especiais */
.special-care {
    margin-bottom: 24px;
}

.care-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.care-categories {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}

.care-category {
    background: white;
    border-radius: 8px;
    padding: 12px;
    border: 2px solid;
}

.care-category.pressure {
    border-color: #dc2626;
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
}

.care-category.usage {
    border-color: #f59e0b;
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
}

.care-category.maintenance {
    border-color: #16a34a;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
}

.care-header {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
}

.care-icon {
    font-size: 14px;
}

.care-name {
    font-size: 11px;
    font-weight: 700;
    color: #374151;
}

.care-items {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.care-item {
    font-size: 10px;
    color: #4b5563;
}

/* Cronograma de manutenção */
.maintenance-schedule {
    margin-bottom: 24px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #e5e7eb;
}

.maintenance-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.schedule-table {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.schedule-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px;
    background: #f8fafc;
    border-radius: 6px;
}

.schedule-frequency {
    font-size: 9px;
    font-weight: 700;
    color: #374151;
    min-width: 80px;
}

.schedule-task {
    font-size: 10px;
    color: #4b5563;
    flex: 1;
    text-align: center;
}

.schedule-importance {
    font-size: 9px;
    color: #dc2626;
    font-weight: 600;
    min-width: 60px;
    text-align: right;
}

/* Localização do estepe */
.spare-location {
    margin-bottom: 24px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #e5e7eb;
}

.location-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.location-options {
    display: grid;
    grid-template-columns: 1fr;
    gap: 8px;
}

.location-option {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background: #f8fafc;
    border-radius: 6px;
}

.location-icon {
    font-size: 16px;
    flex-shrink: 0;
}

.location-info {
    flex: 1;
}

.location-name {
    font-size: 11px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 2px;
}

.location-desc {
    font-size: 10px;
    color: #6b7280;
}

/* Quando não usar */
.dont-use-when {
    margin-bottom: 24px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 2px solid #dc2626;
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
}

.dont-title {
    font-size: 14px;
    font-weight: 600;
    color: #991b1b;
    margin-bottom: 12px;
    text-align: center;
}

.dont-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.dont-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px;
    border-radius: 4px;
}

.dont-item.critical {
    background: rgba(255, 255, 255, 0.5);
}

.dont-item.warning {
    background: rgba(255, 255, 255, 0.3);
}

.dont-icon {
    font-size: 12px;
    flex-shrink: 0;
}

.dont-text {
    font-size: 10px;
    color: #7f1d1d;
    font-weight: 500;
}

/* Dica final */
.final-tip {
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 2px solid #16a34a;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
}

.tip-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}

.tip-icon {
    font-size: 16px;
    color: #16a34a;
}

.tip-title {
    font-size: 14px;
    font-weight: 600;
    color: #166534;
}

.tip-content {
    font-size: 12px;
    color: #14532d;
    line-height: 1.4;
}

/* Responsividade mobile */
@media (max-width: 768px) {
    .spare-tire-section {
        padding: 16px;
        margin: 16px 0;
    }
    
    .section-title {
        font-size: 18px;
    }
    
    .spare-header {
        gap: 8px;
    }
    
    .spare-icon-large {
        font-size: 28px;
    }
    
    .spare-title {
        font-size: 16px;
    }
    
    .specs-grid,
    .types-grid,
    .care-categories,
    .location-options {
        gap: 8px;
    }
    
    .spec-card,
    .type-card,
    .care-category {
        padding: 10px;
    }
    
    .step-item {
        gap: 8px;
        padding: 10px;
    }
    
    .step-number {
        width: 24px;
        height: 24px;
        font-size: 10px;
    }
    
    .schedule-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }
    
    .schedule-frequency,
    .schedule-task,
    .schedule-importance {
        min-width: auto;
        text-align: left;
    }
}
</style>