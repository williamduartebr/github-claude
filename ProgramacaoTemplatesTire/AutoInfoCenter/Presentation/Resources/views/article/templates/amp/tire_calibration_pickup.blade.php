{{--
Template AMP: tire_calibration_pickup.blade.php
ADAPTADO PARA PICKUPS - MANTÉM TODO CONTEÚDO dos mocks, apenas simplifica estrutura
--}}

@extends('auto-info-center::layouts.amp')

@section('amp-head')
<script async custom-element="amp-accordion" src="https://cdn.ampproject.org/v0/amp-accordion-0.1.js"></script>
<style amp-custom>
/* CSS OTIMIZADO mas MANTENDO funcionalidade */
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;line-height:1.6;color:#333;margin:0;background:#fff}
.container{max-width:800px;margin:0 auto;padding:16px}

/* TYPOGRAPHY */
h1{font-size:28px;font-weight:700;margin-bottom:16px;color:#151C25;line-height:1.3}
h2{font-size:22px;font-weight:600;margin:32px 0 16px;padding-bottom:8px;border-bottom:2px solid #2563eb;color:#151C25}
h3{font-size:18px;font-weight:600;margin:20px 0 12px;color:#151C25}
p{margin-bottom:16px;line-height:1.7}

/* COMPONENTES ESSENCIAIS */
.highlight-box{background:linear-gradient(135deg,#eff6ff,#dbeafe);border:2px solid #2563eb;border-radius:12px;padding:24px;margin:24px 0;text-align:center}
.highlight-title{font-size:20px;font-weight:700;color:#1e40af;margin-bottom:16px}

/* PRESSÃO ESPECÍFICA PARA PICKUPS */
.pickup-pressure-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin:16px 0}
.pressure-card{background:rgba(255,255,255,0.8);border:1px solid #93c5fd;border-radius:8px;padding:14px;text-align:center}
.pressure-card.loaded{border-color:#f97316;background:linear-gradient(135deg,#fff7ed,#fed7aa)}
.pressure-card.spare{border-color:#16a34a;background:linear-gradient(135deg,#f0fdf4,#dcfce7)}
.pressure-label{font-size:13px;font-weight:600;margin-bottom:6px;color:#6b7280}
.pressure-value{font-size:20px;font-weight:700;margin-bottom:4px;color:#1d4ed8}
.pressure-card.loaded .pressure-value{color:#ea580c}
.pressure-card.spare .pressure-value{color:#15803d}
.pressure-unit{font-size:11px;color:#6b7280}
.pressure-note{font-size:12px;color:#6b7280;margin-top:4px}

/* SEÇÕES DE CONTEÚDO */
.content-section{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:24px;margin:24px 0;box-shadow:0 2px 4px rgba(0,0,0,0.05)}
.section-header{display:flex;align-items:center;margin-bottom:20px}
.section-icon{font-size:24px;margin-right:12px}
.section-title{font-size:18px;font-weight:600;color:#1f2937}

/* ESPECIFICAÇÕES POR VERSÃO - PICKUP ADAPTADO */
.version-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:20px;margin:20px 0}
.version-card{border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;background:#fff}
.version-header{background:linear-gradient(135deg,#2563eb,#1d4ed8);color:white;padding:16px;font-weight:600;text-align:center}
.version-body{padding:20px}
.spec-row{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f1f5f9}
.spec-row:last-child{border-bottom:none}
.spec-label{font-size:14px;color:#6b7280;font-weight:500}
.spec-value{font-size:14px;font-weight:600;color:#1f2937;background:#eff6ff;padding:4px 8px;border-radius:4px}
.spec-value.loaded{background:#fef3c7;color:#92400e}

/* TABELA CARGA COMPLETA - PICKUP ESPECÍFICA */
.table-container{overflow-x:auto;margin:20px 0;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1)}
.load-table{width:100%;min-width:600px;border-collapse:collapse;border-radius:8px;overflow:hidden}
.load-table th{background:linear-gradient(135deg,#f97316,#ea580c);color:white;padding:12px 8px;text-align:center;font-size:13px;font-weight:600;white-space:nowrap}
.load-table td{padding:10px 8px;border-bottom:1px solid #f1f5f9;text-align:center;font-size:13px;white-space:nowrap}
.load-table tr:nth-child(even){background:#f8fafc}
.pressure-badge{background:#dbeafe;color:#1e40af;padding:4px 8px;border-radius:12px;font-weight:700;font-size:11px;white-space:nowrap}
.pressure-badge.loaded{background:#fed7aa;color:#9a3412}

/* LOCALIZAÇÃO ETIQUETA */
.location-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin:16px 0}
.location-card{background:rgba(255,255,255,0.8);border:1px solid #7dd3fc;border-radius:8px;padding:16px;text-align:center}
.location-priority{font-size:11px;background:#0284c7;color:white;padding:2px 6px;border-radius:10px;margin-bottom:8px;display:inline-block;font-weight:600}
.location-desc{font-size:13px;color:#0c4a6e;font-weight:500}

/* CONDIÇÕES ESPECIAIS - PICKUP ADAPTADO */
.conditions-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;margin:20px 0}
.condition-card{border:1px solid #e5e7eb;border-radius:8px;background:#fff;overflow:hidden}
.condition-header{background:linear-gradient(135deg,#6366f1,#4f46e5);color:white;padding:14px 16px;font-weight:600;text-align:center}
.condition-header.offroad{background:linear-gradient(135deg,#059669,#047857)}
.condition-header.cargo{background:linear-gradient(135deg,#dc2626,#b91c1c)}
.condition-body{padding:16px}
.condition-adjustment{background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;padding:12px;margin:12px 0;text-align:center}
.adjustment-label{font-size:12px;color:#6b7280;margin-bottom:4px}
.adjustment-value{font-size:16px;font-weight:700;color:#4f46e5}

/* CONVERSÃO UNIDADES */
.conversion-container{background:linear-gradient(135deg,#ecfdf5,#d1fae5);border:1px solid #10b981;border-radius:12px;padding:24px;margin:24px 0}
.conversion-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:16px;margin:16px 0}
.conversion-item{background:rgba(255,255,255,0.8);border:1px solid #6ee7b7;border-radius:8px;padding:12px;text-align:center}
.conversion-unit{font-size:12px;color:#065f46;font-weight:600;margin-bottom:4px}
.conversion-value{font-size:16px;font-weight:700;color:#047857}

/* BENEFÍCIOS CALIBRAGEM */
.benefits-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px;margin:20px 0}
.benefit-card{border-radius:8px;padding:20px;text-align:center;border-left:4px solid}
.benefit-card.safety{border-color:#ef4444;background:linear-gradient(135deg,#fef2f2,#fee2e2)}
.benefit-card.economy{border-color:#10b981;background:linear-gradient(135deg,#ecfdf5,#d1fae5)}
.benefit-card.performance{border-color:#f59e0b;background:linear-gradient(135deg,#fffbeb,#fef3c7)}
.benefit-card.durability{border-color:#8b5cf6;background:linear-gradient(135deg,#f5f3ff,#ede9fe)}
.benefit-title{font-weight:600;margin-bottom:12px;color:#1f2937}
.benefit-list{text-align:left;font-size:14px;line-height:1.6}

/* ALERTAS PICKUP */
.alert-box{border-radius:8px;padding:16px;margin:16px 0;border-left:4px solid}
.alert-critical{background:#fef2f2;border-color:#ef4444}
.alert-warning{background:#fffbeb;border-color:#f59e0b}
.alert-info{background:#eff6ff;border-color:#3b82f6}
.alert-success{background:#f0fdf4;border-color:#10b981}
.alert-pickup{background:#fef3c7;border-color:#f59e0b}

/* PROCEDIMENTO CALIBRAGEM */
.procedure-steps{margin:20px 0}
.procedure-step{display:flex;margin-bottom:20px;align-items:flex-start}
.step-number{width:36px;height:36px;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:white;border-radius:50%;display:flex;align-items:center;justify-content:center;margin-right:16px;flex-shrink:0;font-weight:700;font-size:16px}
.step-content{flex:1}
.step-title{margin:0 0 8px;font-size:16px;font-weight:600;color:#1f2937}
.step-desc{margin:0;color:#4b5563;line-height:1.6}

/* FAQ */
amp-accordion{border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;margin:20px 0}
amp-accordion section{border-bottom:1px solid #e5e7eb}
amp-accordion section:last-child{border-bottom:none}
amp-accordion h4{font-size:15px;padding:16px;margin:0;background:#f9fafb;font-weight:600;color:#374151}
amp-accordion .faq-content{padding:16px;background:#fff;color:#4b5563;line-height:1.6}

/* ADS */
.ad-container{text-align:center;margin:32px 0;padding:16px;background:#f8fafc;border-radius:8px;border:1px solid #e5e7eb}
.ad-label{font-size:11px;color:#999;margin-bottom:12px;text-transform:uppercase}

/* META */
.meta-info{color:#6b7280;font-size:14px;margin-bottom:20px;padding:8px 0;border-bottom:1px solid #e5e7eb}

/* SISTEMA TPMS */
.tpms-section{background:linear-gradient(135deg,#dbeafe,#bfdbfe);border:2px solid #3b82f6;border-radius:12px;padding:20px;margin:24px 0}
.tpms-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin:16px 0}
.tpms-card{background:rgba(255,255,255,0.8);border-radius:8px;padding:14px}
.tpms-title{font-size:14px;font-weight:600;color:#1e40af;margin-bottom:8px}
.tpms-list{font-size:13px;line-height:1.6;color:#1e40af}

/* RESPONSIVO */
@media (max-width:768px){
.container{padding:12px}
h1{font-size:24px}
h2{font-size:20px}
.pickup-pressure-grid{grid-template-columns:1fr}
.version-grid,.conditions-grid,.benefits-grid{grid-template-columns:1fr}
.location-grid{grid-template-columns:1fr}
.conversion-grid{grid-template-columns:1fr 1fr}
.tpms-grid{grid-template-columns:1fr}
}
</style>
@endsection

@section('content')
<div class="container">
    <article>
        @php
            // APENAS variáveis da ViewModel - NÃO INVENTAR
            $vehicleInfo = $article->getData()['vehicle_info'] ?? [];
            $pressureSpecs = $article->getData()['pressure_specifications'] ?? [];
            $contentData = $article->getData()['content'] ?? [];
            $tireSpecs = $article->getData()['tire_specifications_by_version'] ?? $contentData['especificacoes_por_versao'] ?? [];
            $fullLoadTable = $article->getData()['full_load_table'] ?? $contentData['tabela_carga_completa'] ?? [];
            $labelLocation = $article->getData()['label_location'] ?? $contentData['localizacao_etiqueta'] ?? [];
            $specialConditions = $article->getData()['special_conditions'] ?? $contentData['condicoes_especiais'] ?? [];
            $unitConversion = $article->getData()['unit_conversion'] ?? $contentData['conversao_unidades'] ?? [];
            $careRecommendations = $article->getData()['care_recommendations'] ?? $contentData['cuidados_recomendacoes'] ?? [];
            $pressureImpact = $article->getData()['pressure_impact'] ?? $contentData['impacto_pressao'] ?? [];
            $faq = $article->getData()['faq'] ?? $contentData['perguntas_frequentes'] ?? [];
            
            // Usar dados da ViewModel, não inventar
            $vehicleName = $vehicleInfo['full_name'] ?? $article->getData()['title'] ?? 'pickup';
            $hasTpms = $vehicleInfo['has_tpms'] ?? false;
            $isElectric = $vehicleInfo['is_electric'] ?? false;
            $isPremium = $vehicleInfo['is_premium'] ?? false;
            $isPickup = true; // Template específico para pickups
        @endphp

        <!-- CABEÇALHO -->
        <header>
            <h1>{{ $article->getData()['seo_data']['h1'] ?? $article->getData()['title'] }}</h1>
            <div class="meta-info">
                Atualizado em: {{ $article->getData()['formated_updated_at'] ?? '15 de janeiro de 2025' }}
            </div>
        </header>
        
        <!-- INTRODUÇÃO COMPLETA dos mocks -->
        @if(!empty($contentData['introducao']) || !empty($article->getData()['introduction']))
        <section style="margin-bottom: 32px;">
            <p style="font-size: 17px; line-height: 1.7; color: #374151;">
                {!! nl2br(e($contentData['introducao'] ?? $article->getData()['introduction'])) !!}
            </p>
        </section>
        @endif        

        <!-- ANÚNCIO 1 -->
        <div class="ad-container">
            <div class="ad-label">Publicidade</div>
            <amp-ad width="100vw" height="320"
                type="adsense"
                data-ad-client="{{ config('services.google_adsense.id') }}"
                data-ad-slot="1234567890"
                data-auto-format="rspv"
                data-full-width>
                <div overflow></div>
            </amp-ad>
        </div>
        
        <!-- PRESSÕES PRINCIPAIS PARA PICKUP -->
        <div class="highlight-box">
            <div class="highlight-title">🚛 Pressões Ideais para {{ $vehicleName }}</div>
            <div class="pickup-pressure-grid">
                <div class="pressure-card">
                    <div class="pressure-label">Dianteiros (Normal)</div>
                    <div class="pressure-value">{{ $pressureSpecs['pressure_empty_front'] ?? '35' }}</div>
                    <div class="pressure-unit">PSI</div>
                    <div class="pressure-note">Sem carga</div>
                </div>
                <div class="pressure-card">
                    <div class="pressure-label">Traseiros (Normal)</div>
                    <div class="pressure-value">{{ $pressureSpecs['pressure_empty_rear'] ?? '40' }}</div>
                    <div class="pressure-unit">PSI</div>
                    <div class="pressure-note">Sem carga</div>
                </div>
                <div class="pressure-card loaded">
                    <div class="pressure-label">Com Carga</div>
                    <div class="pressure-value">{{ $pressureSpecs['loaded_pressure_display'] ?? '38/45' }}</div>
                    <div class="pressure-unit">PSI</div>
                    <div class="pressure-note">Caçamba carregada</div>
                </div>
            </div>
            @if(!empty($pressureSpecs['pressure_spare']))
            <div style="margin-top: 16px;">
                <div class="pressure-card spare" style="display: inline-block; min-width: 150px;">
                    <div class="pressure-label">Pneu Estepe</div>
                    <div class="pressure-value">{{ $pressureSpecs['pressure_spare'] }}</div>
                    <div class="pressure-unit">PSI</div>
                </div>
            </div>
            @endif
            <p style="color: #1e40af; font-weight: 500; font-size: 14px; margin-top: 16px;">
                Verificação {{ $isPremium ? 'semanal' : 'quinzenal' }} recomendada • Sempre com pneus frios
            </p>
        </div>

        <!-- ALERTA ESPECÍFICO PARA PICKUPS -->
        <div class="alert-box alert-pickup">
            <h4 style="margin: 0 0 8px; font-weight: 600;">🚛 Importante para Pickups</h4>
            <p style="margin: 0;">Pickups têm pressões traseiras mais altas devido à capacidade de carga. Sempre ajuste conforme o peso transportado na caçamba para manter estabilidade e segurança.</p>
        </div>

        <!-- ESPECIFICAÇÕES POR VERSÃO (se existir nos mocks) -->
        @if(!empty($tireSpecs))
        <div class="content-section">
            <div class="section-header">
                <span class="section-icon">🚚</span>
                <span class="section-title">Especificações dos Pneus por Versão</span>
            </div>
            <div class="version-grid">
                @foreach($tireSpecs as $spec)
                <div class="version-card">
                    <div class="version-header">{{ $spec['version'] ?? $spec['versao'] ?? 'Versão Principal' }}</div>
                    <div class="version-body">
                        @if(!empty($spec['tire_size']) || !empty($spec['medida_pneus']))
                        <div class="spec-row">
                            <span class="spec-label">Medida dos Pneus:</span>
                            <span class="spec-value">{{ $spec['tire_size'] ?? $spec['medida_pneus'] }}</span>
                        </div>
                        @endif
                        <div class="spec-row">
                            <span class="spec-label">Dianteiro (Normal):</span>
                            <span class="spec-value">{{ $spec['front_normal'] ?? $spec['pressao_dianteiro_normal'] ?? $pressureSpecs['pressure_empty_front'] ?? '35' }}</span>
                        </div>
                        <div class="spec-row">
                            <span class="spec-label">Traseiro (Normal):</span>
                            <span class="spec-value">{{ $spec['rear_normal'] ?? $spec['pressao_traseiro_normal'] ?? $pressureSpecs['pressure_empty_rear'] ?? '40' }}</span>
                        </div>
                        <div class="spec-row">
                            <span class="spec-label">Dianteiro (c/ Carga):</span>
                            <span class="spec-value loaded">{{ $spec['front_loaded'] ?? $spec['pressao_dianteiro_carregado'] ?? $pressureSpecs['pressure_max_front'] ?? '38' }}</span>
                        </div>
                        <div class="spec-row">
                            <span class="spec-label">Traseiro (c/ Carga):</span>
                            <span class="spec-value loaded">{{ $spec['rear_loaded'] ?? $spec['pressao_traseiro_carregado'] ?? $pressureSpecs['pressure_max_rear'] ?? '45' }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- TABELA CARGA COMPLETA (se existir nos mocks) -->
        @if(!empty($fullLoadTable['conditions']) || !empty($fullLoadTable['condicoes']))
        <div class="content-section">
            <div class="section-header">
                <span class="section-icon">📊</span>
                <span class="section-title">{{ $fullLoadTable['title'] ?? $fullLoadTable['titulo'] ?? 'Pressões para Carga na Caçamba' }}</span>
            </div>
            <p style="color: #4b5563; margin-bottom: 16px;">
                {{ $fullLoadTable['description'] ?? $fullLoadTable['descricao'] ?? 'Pressões para uso com diferentes cargas na caçamba.' }}
            </p>
            <div class="table-container">
                <table class="load-table">
                    <thead>
                        <tr>
                            <th>Versão</th>
                            <th>Ocupantes</th>
                            <th>Carga na Caçamba</th>
                            <th>Dianteiros</th>
                            <th>Traseiros</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fullLoadTable['conditions'] ?? $fullLoadTable['condicoes'] as $condition)
                        <tr>
                            <td><strong>{{ $condition['version'] ?? $condition['versao'] ?? 'Pickup' }}</strong></td>
                            <td>{{ $condition['occupants'] ?? $condition['ocupantes'] ?? '2-5' }}</td>
                            <td>{{ $condition['luggage'] ?? $condition['bagagem'] ?? 'Normal' }}</td>
                            <td><span class="pressure-badge loaded">{{ $condition['front_pressure'] ?? $condition['pressao_dianteira'] ?? '38' }}</span></td>
                            <td><span class="pressure-badge loaded">{{ $condition['rear_pressure'] ?? $condition['pressao_traseira'] ?? '45' }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="alert-box alert-info" style="margin-top: 12px;">
                <p style="margin: 0; font-size: 14px;"><strong>💡 Dica:</strong> Use pressões "Normal" para uso urbano sem carga. Use pressões "c/ Carga" quando transportar peso na caçamba ou rebocar.</p>
            </div>
        </div>
        @endif

        <!-- LOCALIZAÇÃO DA ETIQUETA (se existir nos mocks) -->
        @if(!empty($labelLocation))
        <div class="content-section">
            <div class="section-header">
                <span class="section-icon">📍</span>
                <span class="section-title">Onde Encontrar a Etiqueta de Pressão</span>
            </div>
            <p style="color: #4b5563; margin-bottom: 16px;">
                {{ $labelLocation['description'] ?? $labelLocation['descricao'] ?? 'Localizações mais comuns para encontrar as informações oficiais.' }}
            </p>
            <div class="location-grid">
                <div class="location-card">
                    <div class="location-priority">Principal</div>
                    <div class="location-desc">{{ $labelLocation['main_location'] ?? $labelLocation['local_principal'] ?? 'Porta do motorista' }}</div>
                </div>
                @foreach($labelLocation['alternative_locations'] ?? $labelLocation['locais_alternativos'] ?? ['Manual do proprietário', 'Porta-luvas'] as $index => $location)
                <div class="location-card">
                    <div class="location-priority">{{ $index === 0 ? 'Alternativo' : 'Outro' }}</div>
                    <div class="location-desc">{{ $location }}</div>
                </div>
                @endforeach
            </div>
            @if(!empty($labelLocation['note']) || !empty($labelLocation['observacao']))
            <div class="alert-box alert-info" style="margin-top: 16px;">
                <strong>💡 Dica:</strong> {{ $labelLocation['note'] ?? $labelLocation['observacao'] }}
            </div>
            @endif
        </div>
        @endif

        <!-- CONDIÇÕES ESPECIAIS PARA PICKUPS -->
        @if(!empty($specialConditions))
        <div class="content-section">
            <div class="section-header">
                <span class="section-icon">⚖️</span>
                <span class="section-title">Condições Especiais para Pickups</span>
            </div>
            <div class="conditions-grid">
                @foreach($specialConditions as $condition)
                @php
                    $conditionName = $condition['condition'] ?? $condition['condicao'] ?? '';
                    $headerClass = '';
                    if(str_contains(strtolower($conditionName), 'off-road') || str_contains(strtolower($conditionName), 'off')) {
                        $headerClass = 'offroad';
                    } elseif(str_contains(strtolower($conditionName), 'carga') || str_contains(strtolower($conditionName), 'reboque')) {
                        $headerClass = 'cargo';
                    }
                @endphp
                <div class="condition-card">
                    <div class="condition-header {{ $headerClass }}">{{ $conditionName }}</div>
                    <div class="condition-body">
                        <div class="condition-adjustment">
                            <div class="adjustment-label">Ajuste Recomendado</div>
                            <div class="adjustment-value">{{ $condition['recommended_adjustment'] ?? $condition['ajuste_recomendado'] ?? '' }}</div>
                        </div>
                        @if(!empty($condition['application']) || !empty($condition['aplicacao']))
                        <p style="margin: 12px 0; font-size: 14px;"><strong>Aplicação:</strong> {{ $condition['application'] ?? $condition['aplicacao'] }}</p>
                        @endif
                        @if(!empty($condition['justification']) || !empty($condition['justificativa']))
                        <p style="margin: 0; font-size: 14px; color: #6b7280;"><strong>Justificativa:</strong> {{ $condition['justification'] ?? $condition['justificativa'] }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- CONVERSÃO DE UNIDADES -->
        @if(!empty($unitConversion) || true)
        <div class="conversion-container">
            <h3 style="text-align: center; color: #065f46; margin-bottom: 16px;">🔄 Conversão de Unidades</h3>
            <div class="conversion-grid">
                @if(!empty($unitConversion['conversion_table']) || !empty($unitConversion['tabela_conversao']))
                    @php $conversionTable = $unitConversion['conversion_table'] ?? $unitConversion['tabela_conversao'] ?? []; @endphp
                    @foreach(array_slice($conversionTable, 0, 3) as $conversion)
                    <div class="conversion-item">
                        <div class="conversion-unit">{{ $conversion['psi'] }} PSI</div>
                        <div class="conversion-value">{{ $conversion['kgf_cm2'] }} kgf/cm²</div>
                    </div>
                    <div class="conversion-item">
                        <div class="conversion-unit">{{ $conversion['psi'] }} PSI</div>
                        <div class="conversion-value">{{ $conversion['bar'] }} Bar</div>
                    </div>
                    @endforeach
                @else
                    <div class="conversion-item">
                        <div class="conversion-unit">{{ $pressureSpecs['pressure_empty_front'] ?? '35' }} PSI</div>
                        <div class="conversion-value">{{ number_format(($pressureSpecs['pressure_empty_front'] ?? 35) / 14.22, 1) }} kgf/cm²</div>
                    </div>
                    <div class="conversion-item">
                        <div class="conversion-unit">{{ $pressureSpecs['pressure_empty_rear'] ?? '40' }} PSI</div>
                        <div class="conversion-value">{{ number_format(($pressureSpecs['pressure_empty_rear'] ?? 40) / 14.5, 1) }} Bar</div>
                    </div>
                @endif
            </div>
            <p style="text-align: center; font-size: 12px; color: #059669; margin-top: 12px;">
                <strong>Fórmulas:</strong> PSI ÷ 14,22 = kgf/cm² • PSI ÷ 14,5 = Bar
            </p>
        </div>
        @endif

        <!-- SISTEMA TPMS (se aplicável) -->
        @if($hasTpms)
        <div class="tpms-section">
            <div class="section-header">
                <span class="section-icon">📡</span>
                <span class="section-title">Sistema TPMS Disponível</span>
            </div>
            <p style="color: #1e40af; margin-bottom: 16px;">Esta pickup possui sistema TPMS que monitora automaticamente a pressão dos pneus e alerta no painel quando há variações críticas.</p>
            <div class="tpms-grid">
                <div class="tpms-card">
                    <div class="tpms-title">Vantagens do TPMS:</div>
                    <ul class="tpms-list" style="margin: 0; padding-left: 16px;">
                        <li>Alerta em tempo real</li>
                        <li>Maior segurança com carga</li>
                        <li>Prevenção de acidentes</li>
                        <li>Economia de combustível</li>
                    </ul>
                </div>
                <div class="tpms-card">
                    <div class="tpms-title">Importante Lembrar:</div>
                    <ul class="tpms-list" style="margin: 0; padding-left: 16px;">
                        <li>Não substitui verificação manual</li>
                        <li>Alerta apenas quedas críticas</li>
                        <li>Verificar {{ $isPremium ? 'semanalmente' : 'quinzenalmente' }} mesmo assim</li>
                        <li>Recalibrar após reset</li>
                    </ul>
                </div>
            </div>
        </div>
        @endif

        <!-- ANÚNCIO 2 -->
        <div class="ad-container">
            <div class="ad-label">Publicidade</div>
            <amp-ad width="100vw" height="320"
                type="adsense"
                data-ad-client="{{ config('services.google_adsense.id') }}"
                data-ad-slot="2345678901"
                data-auto-format="rspv"
                data-full-width>
                <div overflow></div>
            </amp-ad>
        </div>

        <!-- DICAS DE MANUTENÇÃO ESPECÍFICAS PARA PICKUPS -->
        @if(!empty($careRecommendations) || !empty($contentData['cuidados_recomendacoes']))
        <div class="content-section">
            <div class="section-header">
                <span class="section-icon">🔧</span>
                <span class="section-title">Cuidados Específicos para Pickups</span>
            </div>
            @foreach($careRecommendations ?? $contentData['cuidados_recomendacoes'] ?? [] as $dica)
            <div class="alert-box alert-info" style="margin-bottom: 16px;">
                <h4 style="margin: 0 0 8px; font-weight: 600;">{{ $dica['categoria'] ?? $dica['category'] ?? $dica['title'] }}</h4>
                <p style="margin: 0; font-size: 14px;">{{ $dica['descricao'] ?? $dica['description'] }}</p>
                @if(!empty($dica['procedures']) && is_array($dica['procedures']))
                <ul style="margin: 8px 0 0 16px; font-size: 14px;">
                    @foreach($dica['procedures'] as $procedure)
                    <li>{{ $procedure }}</li>
                    @endforeach
                </ul>
                @endif
            </div>
            @endforeach
            
            <!-- Alerta específico para pickups -->
            <div class="alert-box alert-pickup">
                <h4 style="margin: 0 0 8px; font-weight: 600;">Atenção Especial para Pickups</h4>
                <p style="margin: 0; font-size: 14px;">Pickups sofrem variações maiores de carga que carros comuns. Variações de peso de 300-1000kg na caçamba exigem ajustes frequentes na pressão dos pneus para manter segurança e economia.</p>
            </div>
        </div>
        @endif

        <!-- IMPACTO NO DESEMPENHO -->
        @if(!empty($pressureImpact) || !empty($contentData['impacto_pressao']))
        <div class="content-section">
            <div class="section-header">
                <span class="section-icon">📊</span>
                <span class="section-title">Impacto da Pressão no Desempenho</span>
            </div>
            <div class="benefits-grid">
                @php $impacts = $pressureImpact ?? $contentData['impacto_pressao'] ?? []; @endphp
                @foreach($impacts as $key => $impact)
                @php
                    $cardClass = match($key) {
                        'subcalibrado' => 'safety',
                        'ideal' => 'economy', 
                        'sobrecalibrado' => 'performance',
                        default => 'economy'
                    };
                @endphp
                <div class="benefit-card {{ $cardClass }}">
                    <div class="benefit-title">{{ $impact['titulo'] ?? $impact['title'] ?? ucfirst($key) }}</div>
                    <div class="benefit-list">
                        @if(!empty($impact['problemas']) && is_array($impact['problemas']))
                        <ul style="margin: 0; padding-left: 16px;">
                            @foreach($impact['problemas'] as $problema)
                            <li>{{ $problema }}</li>
                            @endforeach
                        </ul>
                        @elseif(!empty($impact['beneficios']) && is_array($impact['beneficios']))
                        <ul style="margin: 0; padding-left: 16px;">
                            @foreach($impact['beneficios'] as $beneficio)
                            <li>{{ $beneficio }}</li>
                            @endforeach
                        </ul>
                        @elseif(!empty($impact['items']) && is_array($impact['items']))
                        <ul style="margin: 0; padding-left: 16px;">
                            @foreach($impact['items'] as $item)
                            <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- FAQ COMPLETO DOS MOCKS -->
        @if(!empty($faq))
        <div class="content-section">
            <div class="section-header">
                <span class="section-icon">❓</span>
                <span class="section-title">Perguntas Frequentes sobre {{ $vehicleName }}</span>
            </div>
            <amp-accordion expand-single-section>
                @foreach($faq as $pergunta)
                <section>
                    <h4>{{ $pergunta['pergunta'] ?? $pergunta['question'] }}</h4>
                    <div class="faq-content">
                        <p>{{ $pergunta['resposta'] ?? $pergunta['answer'] }}</p>
                    </div>
                </section>
                @endforeach
            </amp-accordion>
        </div>
        @endif

        <!-- ANÚNCIO 3 -->
        <div class="ad-container">
            <div class="ad-label">Publicidade</div>
            <amp-ad width="100vw" height="320"
                type="adsense"
                data-ad-client="{{ config('services.google_adsense.id') }}"
                data-ad-slot="3456789012"
                data-auto-format="rspv"
                data-full-width>
                <div overflow></div>
            </amp-ad>
        </div>

        <!-- CONSIDERAÇÕES FINAIS COMPLETAS DOS MOCKS -->
        @if(!empty($contentData['consideracoes_finais']) || !empty($article->getData()['final_considerations']))
        <div class="content-section">
            <div class="section-header">
                <span class="section-icon">📝</span>
                <span class="section-title">Considerações Finais</span>
            </div>
            <div style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-left: 4px solid #2563eb; padding: 24px; border-radius: 8px; line-height: 1.8; font-size: 16px;">
                {!! nl2br(e($contentData['consideracoes_finais'] ?? $article->getData()['final_considerations'])) !!}
            </div>
        </div>
        @endif
        
        <!-- RESUMO EXECUTIVO FINAL PARA PICKUP -->
        <div class="highlight-box">
            <div class="highlight-title">🚛 Resumo Executivo - Pickup</div>
            <div style="text-align: left; max-width: 600px; margin: 0 auto;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <h4 style="margin: 0 0 8px; font-weight: 600; color: #1e40af;">Uso Normal (Sem Carga):</h4>
                        <p style="margin: 0; font-size: 14px;">
                            Dianteiros: <strong>{{ $pressureSpecs['pressure_empty_front'] ?? '35' }} PSI</strong><br>
                            Traseiros: <strong>{{ $pressureSpecs['pressure_empty_rear'] ?? '40' }} PSI</strong>
                        </p>
                    </div>
                    <div>
                        <h4 style="margin: 0 0 8px; font-weight: 600; color: #1e40af;">Com Carga na Caçamba:</h4>
                        <p style="margin: 0; font-size: 14px;">
                            Dianteiros: <strong>{{ $pressureSpecs['pressure_max_front'] ?? '38' }} PSI</strong><br>
                            Traseiros: <strong>{{ $pressureSpecs['pressure_max_rear'] ?? '45' }} PSI</strong>
                        </p>
                    </div>
                </div>
                
                <div style="border-top: 1px solid #93c5fd; padding-top: 16px;">
                    <h4 style="margin: 0 0 12px; font-weight: 600; color: #1e40af;">Lembre-se Sempre (Pickups):</h4>
                    <ul style="margin: 0; padding-left: 20px; font-size: 14px; color: #1e40af;">
                        <li>Verificar {{ $isPremium ? 'semanalmente' : 'quinzenalmente' }} devido ao uso intensivo</li>
                        <li>Sempre com pneus frios (3 horas parados mínimo)</li>
                        <li>Ajustar conforme peso na caçamba (fundamental!)</li>
                        @if(!empty($pressureSpecs['pressure_spare']))
                        <li>Incluir o estepe na verificação ({{ $pressureSpecs['pressure_spare'] }} PSI)</li>
                        @endif
                        <li>Pressões diferentes para off-road quando aplicável</li>
                        @if($hasTpms)
                        <li>Aguardar recalibração do TPMS após ajustes</li>
                        @endif
                        <li>Considerar peso do reboque quando aplicável</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- NOTA TÉCNICA FINAL -->
        <div class="alert-box alert-info" style="margin-top: 32px;">
            <p style="margin: 0; font-size: 13px; text-align: center;">
                <strong>📋 Nota Técnica:</strong> As informações deste guia são baseadas nas especificações oficiais da {{ $vehicleName }}. 
                Em caso de dúvidas específicas sobre carga, reboque ou uso off-road, consulte sempre o manual do proprietário ou um profissional qualificado. 
                A calibragem correta é fundamental para segurança, economia e desempenho da pickup.
            </p>
        </div>

        <!-- Nota informativa -->
        @include('auto-info-center::article.partials.info_note_manual')
        
        <!-- Footer do artigo -->
        @include('auto-info-center::article.partials.article_footer')
    </article>
</div>
@endsection