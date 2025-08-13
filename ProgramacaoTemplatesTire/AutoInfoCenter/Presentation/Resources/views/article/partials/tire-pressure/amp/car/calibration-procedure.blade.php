{{-- 
Partial: tire-pressure/amp/car/calibration-procedure.blade.php
Procedimento de calibragem detalhado otimizado para AMP - carros
Versão AMP com passo a passo visual incluindo estepe e dicas para automóveis
--}}

@php
    $vehicleInfo = $article->getData()['vehicle_info'] ?? [];
    $pressureTable = $article->getData()['pressure_table'] ?? [];
    $maintenanceTips = $article->getData()['maintenance_tips'] ?? [];
    $spareTireInfo = $article->getData()['spare_tire_info'] ?? [];
    $tireSpecs = $article->getData()['tire_specifications_by_version'] ?? [];
    
    // Pressões de referência para carros
    $mainSpec = $tireSpecs[0] ?? [];
    $frontNormal = $mainSpec['front_normal'] ?? $vehicleInfo['pressure_front'] ?? '32 PSI';
    $rearNormal = $mainSpec['rear_normal'] ?? $vehicleInfo['pressure_rear'] ?? '32 PSI';
    $frontLoaded = $mainSpec['front_loaded'] ?? $vehicleInfo['pressure_front_loaded'] ?? '35 PSI';
    $rearLoaded = $mainSpec['rear_loaded'] ?? $vehicleInfo['pressure_rear_loaded'] ?? '38 PSI';
    $sparePressure = $spareTireInfo['recommended_pressure'] ?? '60 PSI';
@endphp

<section class="car-calibration-section">
    <h2 class="section-title">🔧 Procedimento de Calibragem</h2>
    
    <!-- Preparação Específica para Carros -->
    <div class="car-preparation">
        <h3 class="prep-title">📋 Preparação para Automóveis</h3>
        <div class="prep-grid">
            <div class="prep-item temperature">
                <div class="prep-icon">🌡️</div>
                <div class="prep-content">
                    <div class="prep-name">Pneus Frios</div>
                    <div class="prep-desc">Aguarde 3h após dirigir ou calibre pela manhã cedo</div>
                    <div class="prep-note">Carros retêm calor mais tempo que motos</div>
                </div>
            </div>
            
            <div class="prep-item tools">
                <div class="prep-icon">🛠️</div>
                <div class="prep-content">
                    <div class="prep-name">Ferramentas Necessárias</div>
                    <div class="prep-desc">Manômetro digital, compressor ou bomba manual</div>
                    <div class="prep-note">Verificar calibração do manômetro</div>
                </div>
            </div>
            
            <div class="prep-item location">
                <div class="prep-icon">📍</div>
                <div class="prep-content">
                    <div class="prep-name">Local Adequado</div>
                    <div class="prep-desc">Superfície plana, boa iluminação, seguro</div>
                    <div class="prep-note">Evitar ladeiras e locais movimentados</div>
                </div>
            </div>
            
            <div class="prep-item spare">
                <div class="prep-icon">🛞</div>
                <div class="prep-content">
                    <div class="prep-name">Incluir o Estepe</div>
                    <div class="prep-desc">Sempre verificar o pneu sobressalente</div>
                    <div class="prep-note">Pressão mais alta que pneus normais</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Passo a Passo Detalhado para Carros -->
    <div class="car-steps-section">
        <h3 class="steps-title">📝 Passo a Passo Completo</h3>
        
        <div class="step-item">
            <div class="step-number">1</div>
            <div class="step-content">
                <div class="step-title">Posicione o Veículo</div>
                <div class="step-description">
                    Estacione em local plano e seguro. Acione o freio de mão e desligue o motor.
                    Para carros baixos, posicione próximo ao meio-fio para facilitar o acesso.
                </div>
                <div class="step-tips">
                    <div class="tip-item">💡 Deixe as rodas alinhadas (direção centralizada)</div>
                    <div class="tip-item">🚗 Para SUVs/Pick-ups: cuidado com a altura</div>
                </div>
            </div>
        </div>
        
        <div class="step-item">
            <div class="step-number">2</div>
            <div class="step-content">
                <div class="step-title">Localize a Etiqueta de Pressão</div>
                <div class="step-description">
                    Consulte a etiqueta na porta do motorista, manual do proprietário ou compartimento 
                    do combustível para confirmar as pressões recomendadas.
                </div>
                <div class="step-tips">
                    <div class="tip-item">🏷️ Porta do motorista: local mais comum</div>
                    <div class="tip-item">📖 Manual: sempre mais completo</div>
                    <div class="tip-item">⛽ Tampa combustível: alguns modelos</div>
                </div>
            </div>
        </div>
        
        <div class="step-item">
            <div class="step-number">3</div>
            <div class="step-content">
                <div class="step-title">Remova Calotas e Tampas</div>
                <div class="step-description">
                    Retire calotas (se houver) e tampinhas das válvulas dos 4 pneus + estepe.
                    Organize as tampinhas para não perder durante o processo.
                </div>
                <div class="step-tips">
                    <div class="tip-item">🔧 Calotas: use ferramenta apropriada</div>
                    <div class="tip-item">📦 Organize tampinhas em recipiente</div>
                    <div class="tip-item">🛞 Não esqueça do estepe</div>
                </div>
            </div>
        </div>
        
        <div class="step-item">
            <div class="step-number">4</div>
            <div class="step-content">
                <div class="step-title">Meça Pressão Atual</div>
                <div class="step-description">
                    Conecte o manômetro na válvula de cada pneu. Faça pressão firme e rápida
                    para obter leitura precisa. Anote os valores encontrados.
                </div>
                <div class="step-tips">
                    <div class="tip-item">📊 Anote: DD, TD, DE, TE + Estepe</div>
                    <div class="tip-item">⚡ Conexão rápida evita perda de ar</div>
                    <div class="tip-item">🔄 Meça 2x se houver dúvida</div>
                </div>
            </div>
        </div>
        
        <div class="step-item">
            <div class="step-number">5</div>
            <div class="step-content">
                <div class="step-title">Ajuste as Pressões</div>
                <div class="step-description">
                    Calibre cada pneu conforme especificação. Adicione ar se baixo,
                    libere ar pressionando o pino da válvula se alto. Verifique novamente.
                </div>
                <div class="step-tips">
                    <div class="tip-item">➕ Compressor: adicionar aos poucos</div>
                    <div class="tip-item">➖ Liberar: pressionar pino central</div>
                    <div class="tip-item">✅ Confirmar: medir após ajuste</div>
                </div>
            </div>
        </div>
        
        <div class="step-item">
            <div class="step-number">6</div>
            <div class="step-content">
                <div class="step-title">Finalize e Registre</div>
                <div class="step-description">
                    Recoloque todas as tampas e calotas. Anote a data da calibragem
                    e próxima verificação. Teste o TPMS se o carro tiver.
                </div>
                <div class="step-tips">
                    <div class="tip-item">📅 Anotar data para controle</div>
                    <div class="tip-item">📱 TPMS: aguardar sincronização</div>
                    <div class="tip-item">🚗 Fazer volta de teste</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Valores de Referência para Carros -->
    <div class="car-reference-values">
        <h3 class="ref-title">📊 Valores de Referência por Situação</h3>
        <div class="ref-scenarios">
            <div class="scenario-card normal">
                <div class="scenario-header">
                    <span class="scenario-icon">👨‍👩‍👧‍👦</span>
                    <span class="scenario-label">USO NORMAL</span>
                </div>
                <div class="scenario-desc">1-2 passageiros, bagagem leve</div>
                <div class="scenario-pressures">
                    <div class="pressure-pair">
                        <div class="pressure-front">Dianteiro: {{ $frontNormal }}</div>
                        <div class="pressure-rear">Traseiro: {{ $rearNormal }}</div>
                    </div>
                </div>
            </div>
            
            <div class="scenario-card loaded">
                <div class="scenario-header">
                    <span class="scenario-icon">🧳</span>
                    <span class="scenario-label">CARGA COMPLETA</span>
                </div>
                <div class="scenario-desc">4-5 passageiros, bagageiro cheio</div>
                <div class="scenario-pressures">
                    <div class="pressure-pair">
                        <div class="pressure-front">Dianteiro: {{ $frontLoaded }}</div>
                        <div class="pressure-rear">Traseiro: {{ $rearLoaded }}</div>
                    </div>
                </div>
            </div>
            
            <div class="scenario-card spare">
                <div class="scenario-header">
                    <span class="scenario-icon">🛞</span>
                    <span class="scenario-label">ESTEPE</span>
                </div>
                <div class="scenario-desc">Pneu sobressalente</div>
                <div class="scenario-pressures">
                    <div class="pressure-single">
                        <div class="pressure-spare">Pressão: {{ $sparePressure }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Dicas Específicas para Carros -->
    <div class="car-specific-tips">
        <h3 class="tips-title">🚗 Dicas Específicas para Automóveis</h3>
        <div class="tips-categories">
            <div class="tip-category safety">
                <div class="tip-cat-header">
                    <span class="tip-cat-icon">🛡️</span>
                    <span class="tip-cat-name">SEGURANÇA</span>
                </div>
                <div class="tip-cat-items">
                    <div class="tip-cat-item">• Verificar antes de viagens longas</div>
                    <div class="tip-cat-item">• Pressão afeta frenagem e estabilidade</div>
                    <div class="tip-cat-item">• Estepe sempre com pressão mais alta</div>
                    <div class="tip-cat-item">• TPMS não substitui verificação manual</div>
                </div>
            </div>
            
            <div class="tip-category economy">
                <div class="tip-cat-header">
                    <span class="tip-cat-icon">💰</span>
                    <span class="tip-cat-name">ECONOMIA</span>
                </div>
                <div class="tip-cat-items">
                    <div class="tip-cat-item">• Pressão correta economiza até 10% combustível</div>
                    <div class="tip-cat-item">• Aumenta vida útil dos pneus em 25%</div>
                    <div class="tip-cat-item">• Reduz desgaste irregular</div>
                    <div class="tip-cat-item">• Melhora performance geral do veículo</div>
                </div>
            </div>
            
            <div class="tip-category maintenance">
                <div class="tip-cat-header">
                    <span class="tip-cat-icon">🔧</span>
                    <span class="tip-cat-name">MANUTENÇÃO</span>
                </div>
                <div class="tip-cat-items">
                    <div class="tip-cat-item">• Verificar mensalmente (mínimo)</div>
                    <div class="tip-cat-item">• Incluir estepe na rotina</div>
                    <div class="tip-cat-item">• Calibrar após mudança de temperatura</div>
                    <div class="tip-cat-item">• Fazer rodízio conforme manual</div>
                </div>
            </div>
            
            <div class="tip-category technology">
                <div class="tip-cat-header">
                    <span class="tip-cat-icon">📱</span>
                    <span class="tip-cat-name">TECNOLOGIA</span>
                </div>
                <div class="tip-cat-items">
                    <div class="tip-cat-item">• TPMS: resetar após calibragem</div>
                    <div class="tip-cat-item">• Apps de lembrete disponíveis</div>
                    <div class="tip-cat-item">• Sensores têm vida útil limitada</div>
                    <div class="tip-cat-item">• Manômetros digitais mais precisos</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Frequência de Verificação para Carros -->
    <div class="car-frequency">
        <h4 class="freq-title">📅 Frequência de Verificação</h4>
        <div class="freq-timeline">
            <div class="freq-item monthly">
                <div class="freq-period">MENSAL</div>
                <div class="freq-tasks">
                    <div class="freq-task">✓ Verificação completa (4 pneus + estepe)</div>
                    <div class="freq-task">✓ Inspeção visual de desgaste</div>
                    <div class="freq-task">✓ Limpeza das válvulas</div>
                </div>
            </div>
            
            <div class="freq-item seasonal">
                <div class="freq-period">SAZONAL</div>
                <div class="freq-tasks">
                    <div class="freq-task">✓ Ajuste para mudança de temperatura</div>
                    <div class="freq-task">✓ Verificação após chuvas intensas</div>
                    <div class="freq-task">✓ Preparação para viagens</div>
                </div>
            </div>
            
            <div class="freq-item emergency">
                <div class="freq-period">EMERGENCIAL</div>
                <div class="freq-tasks">
                    <div class="freq-task">⚠️ Luz do TPMS acesa</div>
                    <div class="freq-task">⚠️ Desgaste visual irregular</div>
                    <div class="freq-task">⚠️ Vibração ou ruído anormal</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cuidados com TPMS -->
    <div class="tpms-care">
        <h4 class="tpms-title">📡 Cuidados com o TPMS</h4>
        <div class="tpms-info">
            <div class="tpms-card reset">
                <div class="tpms-icon">🔄</div>
                <div class="tpms-content">
                    <div class="tpms-name">Reset após Calibragem</div>
                    <div class="tpms-desc">Sempre resetar o sistema após ajustar pressões</div>
                    <div class="tpms-steps">Menu → Configurações → TPMS → Reset</div>
                </div>
            </div>
            
            <div class="tpms-card warning">
                <div class="tpms-icon">⚠️</div>
                <div class="tpms-content">
                    <div class="tpms-name">Luz Amarela Piscando</div>
                    <div class="tpms-desc">Problema no sistema, não apenas pressão baixa</div>
                    <div class="tpms-steps">Procurar concessionária para diagnóstico</div>
                </div>
            </div>
            
            <div class="tpms-card info">
                <div class="tpms-icon">💡</div>
                <div class="tpms-content">
                    <div class="tpms-name">Sensores</div>
                    <div class="tpms-desc">Vida útil de 5-10 anos, substituição necessária</div>
                    <div class="tpms-steps">Trocar junto com pneus novos</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Erros Comuns em Carros -->
    <div class="car-common-errors">
        <h4 class="errors-title">🚫 Erros Comuns em Automóveis</h4>
        <div class="errors-grid">
            <div class="error-item">
                <span class="error-icon">❌</span>
                <span class="error-text">Esquecer de verificar o estepe</span>
                <span class="error-consequence">Ficar na mão em emergência</span>
            </div>
            <div class="error-item">
                <span class="error-icon">❌</span>
                <span class="error-text">Não ajustar para carga extra</span>
                <span class="error-consequence">Desgaste prematuro traseiro</span>
            </div>
            <div class="error-item">
                <span class="error-icon">❌</span>
                <span class="error-text">Ignorar o sistema TPMS</span>
                <span class="error-consequence">Perder avisos importantes</span>
            </div>
            <div class="error-item">
                <span class="error-icon">❌</span>
                <span class="error-text">Calibrar com pneus quentes</span>
                <span class="error-consequence">Pressão incorreta</span>
            </div>
        </div>
    </div>
</section>

<style>
/* Seção principal */
.car-calibration-section {
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

/* Preparação específica para carros */
.car-preparation {
    margin-bottom: 24px;
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
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.prep-item {
    background: white;
    border-radius: 10px;
    padding: 12px;
    border: 2px solid;
    display: flex;
    gap: 10px;
}

.prep-item.temperature {
    border-color: #dc2626;
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
}

.prep-item.tools {
    border-color: #16a34a;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
}

.prep-item.location {
    border-color: #f59e0b;
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
}

.prep-item.spare {
    border-color: #7c3aed;
    background: linear-gradient(135deg, #f5f3ff, #ede9fe);
}

.prep-icon {
    font-size: 18px;
    flex-shrink: 0;
}

.prep-content {
    flex: 1;
}

.prep-name {
    font-size: 12px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 4px;
}

.prep-desc {
    font-size: 10px;
    color: #6b7280;
    margin-bottom: 4px;
    line-height: 1.3;
}

.prep-note {
    font-size: 9px;
    color: #8b5cf6;
    font-style: italic;
}

/* Passos específicos para carros */
.car-steps-section {
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
    gap: 14px;
    margin-bottom: 16px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #e5e7eb;
}

.step-number {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
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
    margin-bottom: 6px;
}

.step-description {
    font-size: 12px;
    color: #6b7280;
    line-height: 1.4;
    margin-bottom: 8px;
}

.step-tips {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.tip-item {
    font-size: 10px;
    color: #3b82f6;
    background: #eff6ff;
    padding: 4px 8px;
    border-radius: 4px;
}

/* Valores de referência para carros */
.car-reference-values {
    margin-bottom: 24px;
}

.ref-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.ref-scenarios {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}

.scenario-card {
    background: white;
    border-radius: 10px;
    padding: 14px;
    border: 2px solid;
    text-align: center;
}

.scenario-card.normal {
    border-color: #16a34a;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
}

.scenario-card.loaded {
    border-color: #f59e0b;
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
}

.scenario-card.spare {
    border-color: #7c3aed;
    background: linear-gradient(135deg, #f5f3ff, #ede9fe);
}

.scenario-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-bottom: 8px;
}

.scenario-icon {
    font-size: 16px;
}

.scenario-label {
    font-size: 12px;
    font-weight: 700;
    color: #374151;
}

.scenario-desc {
    font-size: 10px;
    color: #6b7280;
    margin-bottom: 10px;
}

.scenario-pressures {
    display: flex;
    justify-content: center;
}

.pressure-pair {
    display: flex;
    gap: 12px;
}

.pressure-front,
.pressure-rear {
    font-size: 11px;
    font-weight: 600;
    color: #1f2937;
    padding: 4px 8px;
    border-radius: 4px;
}

.pressure-front {
    background: #dbeafe;
    color: #1e40af;
}

.pressure-rear {
    background: #dcfce7;
    color: #166534;
}

.pressure-single {
    display: flex;
    justify-content: center;
}

.pressure-spare {
    font-size: 12px;
    font-weight: 700;
    color: #7c3aed;
    padding: 6px 12px;
    background: rgba(124, 58, 237, 0.1);
    border-radius: 6px;
}

/* Dicas específicas para carros */
.car-specific-tips {
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
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.tip-category {
    background: white;
    border-radius: 8px;
    padding: 12px;
    border: 2px solid;
}

.tip-category.safety {
    border-color: #dc2626;
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
}

.tip-category.economy {
    border-color: #16a34a;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
}

.tip-category.maintenance {
    border-color: #f59e0b;
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
}

.tip-category.technology {
    border-color: #2563eb;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
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
    gap: 3px;
}

.tip-cat-item {
    font-size: 9px;
    color: #4b5563;
    line-height: 1.3;
}

/* Frequência para carros */
.car-frequency {
    margin-bottom: 24px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #e5e7eb;
}

.freq-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.freq-timeline {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.freq-item {
    display: flex;
    gap: 12px;
    padding: 10px;
    border-radius: 8px;
    border-left: 4px solid;
}

.freq-item.monthly {
    border-color: #16a34a;
    background: #f0fdf4;
}

.freq-item.seasonal {
    border-color: #f59e0b;
    background: #fffbeb;
}

.freq-item.emergency {
    border-color: #dc2626;
    background: #fef2f2;
}

.freq-period {
    font-size: 11px;
    font-weight: 700;
    color: #374151;
    min-width: 70px;
    flex-shrink: 0;
}

.freq-tasks {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.freq-task {
    font-size: 10px;
    color: #4b5563;
}

/* Cuidados TPMS */
.tpms-care {
    margin-bottom: 24px;
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #e5e7eb;
}

.tpms-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    text-align: center;
}

.tpms-info {
    display: grid;
    grid-template-columns: 1fr;
    gap: 8px;
}

.tpms-card {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
}

.tpms-card.reset {
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
}

.tpms-card.warning {
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
}

.tpms-card.info {
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
}

.tpms-icon {
    font-size: 16px;
    flex-shrink: 0;
}

.tpms-content {
    flex: 1;
}

.tpms-name {
    font-size: 11px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 3px;
}

.tpms-desc {
    font-size: 10px;
    color: #6b7280;
    margin-bottom: 3px;
    line-height: 1.3;
}

.tpms-steps {
    font-size: 9px;
    color: #3b82f6;
    font-weight: 500;
}

/* Erros comuns */
.car-common-errors {
    background: white;
    border-radius: 12px;
    padding: 16px;
    border: 2px solid #dc2626;
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
}

.errors-title {
    font-size: 14px;
    font-weight: 600;
    color: #991b1b;
    margin-bottom: 12px;
    text-align: center;
}

.errors-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 8px;
}

.error-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px;
    background: rgba(255, 255, 255, 0.5);
    border-radius: 6px;
}

.error-icon {
    font-size: 12px;
    color: #dc2626;
    flex-shrink: 0;
}

.error-text {
    font-size: 10px;
    color: #374151;
    font-weight: 500;
    flex: 1;
}

.error-consequence {
    font-size: 9px;
    color: #991b1b;
    font-style: italic;
}

/* Responsividade mobile */
@media (max-width: 768px) {
    .car-calibration-section {
        padding: 16px;
        margin: 16px 0;
    }
    
    .section-title {
        font-size: 18px;
    }
    
    .prep-grid {
        grid-template-columns: 1fr;
        gap: 8px;
    }
    
    .prep-item {
        gap: 8px;
        padding: 10px;
    }
    
    .step-item {
        gap: 10px;
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
    
    .tip-item {
        font-size: 9px;
        padding: 3px 6px;
    }
    
    .ref-scenarios {
        gap: 8px;
    }
    
    .scenario-card {
        padding: 12px;
    }
    
    .pressure-pair {
        flex-direction: column;
        gap: 6px;
        align-items: center;
    }
    
    .tips-categories {
        grid-template-columns: 1fr;
        gap: 8px;
    }
    
    .tip-category {
        padding: 10px;
    }
    
    .freq-item {
        flex-direction: column;
        gap: 6px;
    }
    
    .freq-period {
        min-width: auto;
    }
    
    .tpms-info {
        gap: 6px;
    }
    
    .tpms-card {
        gap: 8px;
        padding: 8px;
    }
    
    .errors-grid {
        gap: 6px;
    }
    
    .error-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
        padding: 6px;
    }
}
</style>