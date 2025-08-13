{{-- 
Partial: tire-pressure/amp/motorcycle/maintenance-tips.blade.php
Dicas de manutenção específicas para motocicletas - AMP REFATORADO
Versão AMP otimizada focada em inspeção visual, desgaste e cuidados preventivos
--}}

@php
    $maintenanceTips = $article->getData()['maintenance_tips'] ?? [];
    $vehicleInfo = $article->getData()['vehicle_info'] ?? [];
    $motorcycleCategory = $vehicleInfo['category'] ?? 'standard';
@endphp

<section class="maintenance-section">
    <h2 class="section-title">🔧 Manutenção Preventiva dos Pneus</h2>
    
    <!-- Inspeção Visual Diária -->
    <div class="daily-inspection">
        <h3 class="inspection-title">👁️ Inspeção Visual Diária (30 segundos)</h3>
        <div class="inspection-grid">
            <div class="check-category visual">
                <div class="check-header">
                    <span class="check-icon">💨</span>
                    <span class="check-name">Aspecto Geral</span>
                </div>
                <div class="check-items">
                    <div class="check-item">• Pneu "murcho" ou muito rígido?</div>
                    <div class="check-item">• Formato irregular visível?</div>
                    <div class="check-item">• Deformações na lateral?</div>
                </div>
            </div>
            
            <div class="check-category objects">
                <div class="check-header">
                    <span class="check-icon">🔍</span>
                    <span class="check-name">Objetos Presos</span>
                </div>
                <div class="check-items">
                    <div class="check-item">• Pregos ou parafusos</div>
                    <div class="check-item">• Pedras nos sulcos</div>
                    <div class="check-item">• Vidros ou metal</div>
                </div>
            </div>
            
            <div class="check-category damage">
                <div class="check-header">
                    <span class="check-icon">⚠️</span>
                    <span class="check-name">Danos Visíveis</span>
                </div>
                <div class="check-items">
                    <div class="check-item">• Rachaduras nos flancos</div>
                    <div class="check-item">• Bolhas ou ondulações</div>
                    <div class="check-item">• Válvulas danificadas</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cronograma de Verificações -->
    <div class="verification-schedule">
        <h3 class="schedule-title">📅 Cronograma de Verificações</h3>
        <div class="schedule-timeline">
            <div class="timeline-item daily">
                <div class="timeline-marker"></div>
                <div class="timeline-content">
                    <div class="timeline-period">DIÁRIO</div>
                    <div class="timeline-task">Inspeção visual rápida</div>
                </div>
            </div>
            
            <div class="timeline-item weekly">
                <div class="timeline-marker"></div>
                <div class="timeline-content">
                    <div class="timeline-period">SEMANAL</div>
                    <div class="timeline-task">Medição com manômetro</div>
                </div>
            </div>
            
            <div class="timeline-item monthly">
                <div class="timeline-marker"></div>
                <div class="timeline-content">
                    <div class="timeline-period">MENSAL</div>
                    <div class="timeline-task">Inspeção completa</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Padrões de Desgaste -->
    <div class="wear-patterns">
        <h3 class="patterns-title">📊 Padrões de Desgaste</h3>
        <div class="patterns-grid">
            <div class="pattern-card center">
                <div class="pattern-icon">🎯</div>
                <div class="pattern-info">
                    <div class="pattern-name">Desgaste Central</div>
                    <div class="pattern-cause">Pressão excessiva</div>
                    <div class="pattern-solution">Reduzir 2-3 PSI</div>
                </div>
            </div>
            
            <div class="pattern-card edges">
                <div class="pattern-icon">↔️</div>
                <div class="pattern-info">
                    <div class="pattern-name">Desgaste Lateral</div>
                    <div class="pattern-cause">Pressão baixa / Curvas</div>
                    <div class="pattern-solution">Aumentar pressão</div>
                </div>
            </div>
            
            <div class="pattern-card irregular">
                <div class="pattern-icon">〰️</div>
                <div class="pattern-info">
                    <div class="pattern-name">Desgaste Irregular</div>
                    <div class="pattern-cause">Desalinhamento</div>
                    <div class="pattern-solution">Verificar suspensão</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cuidados por Categoria -->
    @if($motorcycleCategory === 'sport')
    <div class="category-care sport">
        <h4 class="care-title">🏁 Cuidados para Motos Esportivas</h4>
        <div class="care-grid">
            <div class="care-item">• Verificar pressão antes de cada saída</div>
            <div class="care-item">• Aquecimento gradual dos pneus</div>
            <div class="care-item">• Trocar a cada 10.000-15.000km</div>
            <div class="care-item">• Usar pneus com índice V/W/Y</div>
        </div>
    </div>
    @elseif($motorcycleCategory === 'touring')
    <div class="category-care touring">
        <h4 class="care-title">🛣️ Cuidados para Motos Touring</h4>
        <div class="care-grid">
            <div class="care-item">• Verificar semanalmente</div>
            <div class="care-item">• Ajustar para peso da bagagem</div>
            <div class="care-item">• Trocar a cada 20.000-25.000km</div>
            <div class="care-item">• Inspeção antes de viagens</div>
        </div>
    </div>
    @else
    <div class="category-care standard">
        <h4 class="care-title">🔧 Cuidados Gerais</h4>
        <div class="care-grid">
            <div class="care-item">• Verificar 2x por semana</div>
            <div class="care-item">• Seguir manual do proprietário</div>
            <div class="care-item">• Trocar a cada 15.000-20.000km</div>
            <div class="care-item">• Manutenção preventiva regular</div>
        </div>
    </div>
    @endif
    
    <!-- Dicas de Economia -->
    <div class="economy-tips">
        <h4 class="economy-title">💰 Dicas para Economizar</h4>
        <div class="tips-comparison">
            <div class="tip-card save">
                <div class="tip-header">
                    <span class="tip-icon">💵</span>
                    <span class="tip-name">Como Economizar</span>
                </div>
                <div class="tip-benefits">
                    <div class="benefit-item">
                        <span class="benefit-stat">+30%</span>
                        <span class="benefit-desc">vida útil com pressão correta</span>
                    </div>
                    <div class="benefit-item">
                        <span class="benefit-stat">-15%</span>
                        <span class="benefit-desc">consumo com calibragem ideal</span>
                    </div>
                </div>
            </div>
            
            <div class="tip-card actions">
                <div class="tip-header">
                    <span class="tip-icon">✅</span>
                    <span class="tip-name">Ações Práticas</span>
                </div>
                <div class="action-list">
                    <div class="action-item">• Evitar frenagens bruscas</div>
                    <div class="action-item">• Acelerar suavemente</div>
                    <div class="action-item">• Evitar meio-fio e buracos</div>
                    <div class="action-item">• Estacionar na sombra</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Ferramentas Básicas -->
    <div class="basic-tools">
        <h4 class="tools-title">🧰 Ferramentas Essenciais</h4>
        <div class="tools-list">
            <div class="tool-item essential">
                <span class="tool-icon">📏</span>
                <div class="tool-info">
                    <div class="tool-name">Manômetro Digital</div>
                    <div class="tool-price">R$ 30-80</div>
                </div>
            </div>
            
            <div class="tool-item important">
                <span class="tool-icon">🔧</span>
                <div class="tool-info">
                    <div class="tool-name">Medidor de Sulco</div>
                    <div class="tool-price">R$ 15-40</div>
                </div>
            </div>
            
            <div class="tool-item useful">
                <span class="tool-icon">🔦</span>
                <div class="tool-info">
                    <div class="tool-name">Lanterna LED</div>
                    <div class="tool-price">R$ 20-60</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quando Trocar -->
    <div class="replacement-indicators">
        <h4 class="replace-title">🔄 Sinais para Trocar</h4>
        <div class="indicators-list">
            <div class="indicator critical">
                <span class="indicator-icon">❌</span>
                <span class="indicator-text">Sulco menor que 1.6mm</span>
                <span class="indicator-action">IMEDIATO</span>
            </div>
            
            <div class="indicator warning">
                <span class="indicator-icon">⚠️</span>
                <span class="indicator-text">Mais de 5 anos de uso</span>
                <span class="indicator-action">PLANEJE</span>
            </div>
            
            <div class="indicator info">
                <span class="indicator-icon">📅</span>
                <span class="indicator-text">Quilometragem atingida</span>
                <span class="indicator-action">NORMAL</span>
            </div>
        </div>
    </div>
    
    <!-- Dica Final -->
    <div class="final-maintenance-tip">
        <div class="tip-header">
            <span class="tip-icon">🎯</span>
            <span class="tip-title">Dica de Ouro</span>
        </div>
        <div class="tip-message">
            <strong>5 minutos por semana</strong> de inspeção podem evitar 
            <strong>horas de problemas</strong> e <strong>centenas de reais</strong> em reparos.
        </div>
    </div>
</section>

<style>
/* Seção principal */
.maintenance-section {
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

/* Inspeção visual diária */
.daily-inspection {
    margin-bottom: 24px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #e5e7eb;
}

.inspection-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.inspection-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}

.check-category {
    background: #f8fafc;
    border-radius: 8px;
    padding: 12px;
    border: 1px solid #e2e8f0;
}

.check-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}

.check-icon {
    font-size: 16px;
    color: #16a34a;
}

.check-name {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
}

.check-items {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.check-item {
    font-size: 11px;
    color: #4b5563;
    line-height: 1.3;
}

/* Cronograma */
.verification-schedule {
    margin-bottom: 24px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #e5e7eb;
}

.schedule-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.schedule-timeline {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.timeline-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px;
    border-radius: 6px;
}

.timeline-item.daily {
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
}

.timeline-item.weekly {
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
}

.timeline-item.monthly {
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
}

.timeline-marker {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    flex-shrink: 0;
}

.timeline-item.daily .timeline-marker {
    background: #16a34a;
}

.timeline-item.weekly .timeline-marker {
    background: #3b82f6;
}

.timeline-item.monthly .timeline-marker {
    background: #f59e0b;
}

.timeline-content {
    flex: 1;
}

.timeline-period {
    font-size: 11px;
    font-weight: 700;
    color: #374151;
}

.timeline-task {
    font-size: 12px;
    color: #6b7280;
}

/* Padrões de desgaste */
.wear-patterns {
    margin-bottom: 24px;
}

.patterns-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.patterns-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}

.pattern-card {
    background: white;
    border-radius: 8px;
    padding: 12px;
    border: 2px solid;
    display: flex;
    align-items: center;
    gap: 12px;
}

.pattern-card.center {
    border-color: #dc2626;
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
}

.pattern-card.edges {
    border-color: #f59e0b;
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
}

.pattern-card.irregular {
    border-color: #7c3aed;
    background: linear-gradient(135deg, #f5f3ff, #ede9fe);
}

.pattern-icon {
    font-size: 20px;
    flex-shrink: 0;
}

.pattern-info {
    flex: 1;
}

.pattern-name {
    font-size: 12px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 2px;
}

.pattern-cause {
    font-size: 10px;
    color: #6b7280;
    margin-bottom: 2px;
}

.pattern-solution {
    font-size: 10px;
    font-weight: 600;
    color: #dc2626;
}

/* Cuidados por categoria */
.category-care {
    margin-bottom: 24px;
    background: white;
    border-radius: 10px;
    padding: 14px;
    border: 2px solid;
}

.category-care.sport {
    border-color: #dc2626;
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
}

.category-care.touring {
    border-color: #2563eb;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
}

.category-care.standard {
    border-color: #64748b;
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
}

.care-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 10px;
    text-align: center;
}

.care-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 4px;
}

.care-item {
    font-size: 11px;
    color: #4b5563;
    line-height: 1.3;
}

/* Dicas de economia */
.economy-tips {
    margin-bottom: 24px;
}

.economy-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.tips-comparison {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}

.tip-card {
    background: white;
    border-radius: 8px;
    padding: 12px;
    border: 1px solid #e5e7eb;
}

.tip-header {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
}

.tip-icon {
    font-size: 14px;
    color: #16a34a;
}

.tip-name {
    font-size: 12px;
    font-weight: 600;
    color: #374151;
}

.tip-benefits {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.benefit-item {
    display: flex;
    align-items: center;
    gap: 6px;
}

.benefit-stat {
    font-size: 14px;
    font-weight: 700;
    color: #16a34a;
}

.benefit-desc {
    font-size: 10px;
    color: #6b7280;
}

.action-list {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.action-item {
    font-size: 10px;
    color: #4b5563;
}

/* Ferramentas */
.basic-tools {
    margin-bottom: 24px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #e5e7eb;
}

.tools-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.tools-list {
    display: grid;
    grid-template-columns: 1fr;
    gap: 8px;
}

.tool-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px;
    background: #f8fafc;
    border-radius: 6px;
}

.tool-icon {
    font-size: 16px;
    color: #3b82f6;
    flex-shrink: 0;
}

.tool-info {
    flex: 1;
}

.tool-name {
    font-size: 11px;
    font-weight: 600;
    color: #374151;
}

.tool-price {
    font-size: 10px;
    color: #16a34a;
    font-weight: 500;
}

/* Indicadores de troca */
.replacement-indicators {
    margin-bottom: 24px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #e5e7eb;
}

.replace-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.indicators-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.indicator {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px;
    border-radius: 6px;
}

.indicator.critical {
    background: #fef2f2;
}

.indicator.warning {
    background: #fffbeb;
}

.indicator.info {
    background: #f0fdf4;
}

.indicator-icon {
    font-size: 14px;
    flex-shrink: 0;
}

.indicator-text {
    font-size: 11px;
    color: #374151;
    flex: 1;
}

.indicator-action {
    font-size: 9px;
    font-weight: 700;
    color: #6b7280;
}

/* Dica final */
.final-maintenance-tip {
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
    justify-content: center;
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

.tip-message {
    font-size: 12px;
    color: #14532d;
    line-height: 1.4;
    text-align: center;
}

/* Responsividade mobile */
@media (max-width: 768px) {
    .maintenance-section {
        padding: 16px;
        margin: 16px 0;
    }
    
    .section-title {
        font-size: 18px;
    }
    
    .daily-inspection,
    .verification-schedule,
    .basic-tools,
    .replacement-indicators,
    .final-maintenance-tip {
        padding: 12px;
    }
    
    .inspection-grid,
    .patterns-grid,
    .tips-comparison,
    .tools-list {
        gap: 8px;
    }
    
    .check-category,
    .pattern-card,
    .tip-card,
    .tool-item {
        padding: 10px;
    }
    
    .timeline-item {
        padding: 8px;
    }
}
</style>