{{--
Template AMP: ideal_tire_pressure_car.blade.php
MANTÉM TODO CONTEÚDO dos mocks, apenas simplifica estrutura
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

.pressure-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin:16px 0}
.pressure-card{background:rgba(255,255,255,0.8);border:1px solid #93c5fd;border-radius:8px;padding:16px;text-align:center}
.pressure-label{font-size:14px;font-weight:600;margin-bottom:8px;color:#6b7280}
.pressure-value{font-size:24px;font-weight:700;margin-bottom:4px;color:#1d4ed8}
.pressure-unit{font-size:12px;color:#6b7280}

/* SEÇÕES DE CONTEÚDO */
.content-section{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:24px;margin:24px 0;box-shadow:0 2px 4px rgba(0,0,0,0.05)}
.section-header{display:flex;align-items:center;margin-bottom:20px}
.section-icon{font-size:24px;margin-right:12px}
.section-title{font-size:18px;font-weight:600;color:#1f2937}

/* ESPECIFICAÇÕES POR VERSÃO */
.version-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:20px;margin:20px 0}
.version-card{border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;background:#fff}
.version-header{background:linear-gradient(135deg,#2563eb,#1d4ed8);color:white;padding:16px;font-weight:600;text-align:center}
.version-body{padding:20px}
.spec-row{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f1f5f9}
.spec-row:last-child{border-bottom:none}
.spec-label{font-size:14px;color:#6b7280;font-weight:500}
.spec-value{font-size:14px;font-weight:600;color:#1f2937;background:#eff6ff;padding:4px 8px;border-radius:4px}

/* TABELA CARGA COMPLETA */
.load-table{width:100%;border-collapse:collapse;margin:20px 0;border-radius:8px;overflow:hidden;box-shadow:0 2px 4px rgba(0,0,0,0.1)}
.load-table th{background:linear-gradient(135deg,#2563eb,#1d4ed8);color:white;padding:12px 8px;text-align:center;font-size:13px;font-weight:600}
.load-table td{padding:10px 8px;border-bottom:1px solid #f1f5f9;text-align:center;font-size:13px}
.load-table tr:nth-child(even){background:#f8fafc}
.pressure-badge{background:#dbeafe;color:#1e40af;padding:4px 8px;border-radius:12px;font-weight:700;font-size:11px}

/* LOCALIZAÇÃO ETIQUETA */
.location-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin:16px 0}
.location-card{background:rgba(255,255,255,0.8);border:1px solid #7dd3fc;border-radius:8px;padding:16px;text-align:center}
.location-priority{font-size:11px;background:#0284c7;color:white;padding:2px 6px;border-radius:10px;margin-bottom:8px;display:inline-block;font-weight:600}
.location-desc{font-size:13px;color:#0c4a6e;font-weight:500}

/* CONDIÇÕES ESPECIAIS */
.conditions-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;margin:20px 0}
.condition-card{border:1px solid #e5e7eb;border-radius:8px;background:#fff;overflow:hidden}
.condition-header{background:linear-gradient(135deg,#6366f1,#4f46e5);color:white;padding:14px 16px;font-weight:600;text-align:center}
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
.benefit-title{font-weight:600;margin-bottom:12px;color:#1f2937}
.benefit-list{text-align:left;font-size:14px;line-height:1.6}

/* ALERTAS */
.alert-box{border-radius:8px;padding:16px;margin:16px 0;border-left:4px solid}
.alert-critical{background:#fef2f2;border-color:#ef4444}
.alert-warning{background:#fffbeb;border-color:#f59e0b}
.alert-info{background:#eff6ff;border-color:#3b82f6}
.alert-success{background:#f0fdf4;border-color:#10b981}

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

/* RESPONSIVO */
@media (max-width:768px){
.container{padding:12px}
h1{font-size:24px}
h2{font-size:20px}
.version-grid,.conditions-grid,.benefits-grid{grid-template-columns:1fr}
.pressure-grid{grid-template-columns:1fr}
.location-grid{grid-template-columns:1fr}
.conversion-grid{grid-template-columns:1fr 1fr}
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
            $tireSpecs = $article->getData()['tire_specifications_by_version'] ?? [];
            $fullLoadTable = $article->getData()['full_load_table'] ?? [];
            $labelLocation = $article->getData()['label_location'] ?? [];
            $specialConditions = $article->getData()['special_conditions'] ?? [];
            $unitConversion = $article->getData()['unit_conversion'] ?? [];
            $careRecommendations = $article->getData()['care_recommendations'] ?? [];
            $pressureImpact = $article->getData()['pressure_impact'] ?? [];
            $faq = $article->getData()['faq'] ?? $contentData['perguntas_frequentes'] ?? [];
            
            // Usar dados da ViewModel, não inventar
            $vehicleName = $vehicleInfo['full_name'] ?? $article->getData()['title'] ?? 'veículo';
            $hasTpms = $vehicleInfo['has_tpms'] ?? false;
            $isElectric = $vehicleInfo['is_electric'] ?? false;
            $isPremium = $vehicleInfo['is_premium'] ?? false;
        @endphp

        <!-- CABEÇALHO -->
        <header>
            <h1>{{ $article->getData()['seo_data']['h1'] ?? $article->getData()['title'] }}</h1>
            <div class="meta-info">
                Atualizado em: {{ $article->getData()['formated_updated_at'] ?? '13 de agosto de 2025' }}
            </div>
        </header>
        
        <!-- INTRODUÇÃO COMPLETA dos mocks -->
        @if(!empty($article->getData()['introduction']))
        <section style="margin-bottom: 32px;">
            <p style="font-size: 17px; line-height: 1.7; color: #374151;">
                {!! nl2br(e($article->getData()['introduction'])) !!}
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
        
        <!-- PRESSÕES PRINCIPAIS -->
        <div class="highlight-box">
            <div class="highlight-title">🎯 Pressões Ideais para {{ $vehicleName }}</div>
            <div class="pressure-grid">
                <div class="pressure-card">
                    <div class="pressure-label">🔄 Pneus Dianteiros</div>
                    <div class="pressure-value">{{ $pressureSpecs['pressure_empty_front'] ?? '32' }}</div>
                    <div class="pressure-unit">PSI</div>
                </div>
                <div class="pressure-card">
                    <div class="pressure-label">🔙 Pneus Traseiros</div>
                    <div class="pressure-value">{{ $pressureSpecs['pressure_empty_rear'] ?? '32' }}</div>
                    <div class="pressure-unit">PSI</div>
                </div>
            </div>
            <p style="color: #1e40af; font-weight: 500; font-size: 14px; margin-top: 16px;">
                Verificação mensal recomendada • Sempre com pneus frios
            </p>
        </div>
        


        <!-- ESPECIFICAÇÕES POR VERSÃO (se existir nos mocks) -->
        @if(!empty($tireSpecs))
        <div class="content-section">
            <div class="section-header">
                <span class="section-icon">🚗</span>
                <span class="section-title">Especificações dos Pneus por Versão</span>
            </div>
            <div class="version-grid">
                @foreach($tireSpecs as $spec)
                <div class="version-card">
                    <div class="version-header">{{ $spec['version'] ?? 'Versão Principal' }}</div>
                    <div class="version-body">
                        @if(!empty($spec['tire_size']))
                        <div class="spec-row">
                            <span class="spec-label">Medida dos Pneus:</span>
                            <span class="spec-value">{{ $spec['tire_size'] }}</span>
                        </div>
                        @endif
                        <div class="spec-row">
                            <span class="spec-label">Dianteiro (Normal):</span>
                            <span class="spec-value">{{ $spec['front_normal'] ?? $pressureSpecs['pressure_empty_front'] ?? '32' }}</span>
                        </div>
                        <div class="spec-row">
                            <span class="spec-label">Traseiro (Normal):</span>
                            <span class="spec-value">{{ $spec['rear_normal'] ?? $pressureSpecs['pressure_empty_rear'] ?? '32' }}</span>
                        </div>
                        <div class="spec-row">
                            <span class="spec-label">Dianteiro (Carregado):</span>
                            <span class="spec-value">{{ $spec['front_loaded'] ?? $pressureSpecs['pressure_max_front'] ?? '32' }}</span>
                        </div>
                        <div class="spec-row">
                            <span class="spec-label">Traseiro (Carregado):</span>
                            <span class="spec-value">{{ $spec['rear_loaded'] ?? $pressureSpecs['pressure_max_rear'] ?? '35' }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- TABELA CARGA COMPLETA (se existir nos mocks) -->
        @if(!empty($fullLoadTable['conditions']))
        <div class="content-section">
            <div class="section-header">
                <span class="section-icon">📊</span>
                <span class="section-title">{{ $fullLoadTable['title'] ?? 'Tabela de Carga Completa' }}</span>
            </div>
            <p style="color: #4b5563; margin-bottom: 16px;">
                {{ $fullLoadTable['description'] ?? 'Pressões para uso com carga máxima do veículo.' }}
            </p>
            <table class="load-table">
                <thead>
                    <tr>
                        <th>Situação</th>
                        <th>Ocupantes</th>
                        <th>Bagagem</th>
                        <th>Dianteiros</th>
                        <th>Traseiros</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($fullLoadTable['conditions'] as $condition)
                    <tr>
                        <td><strong>{{ $condition['condition'] ?? $condition['version'] ?? 'Uso' }}</strong></td>
                        <td>{{ $condition['occupants'] ?? '4-5' }}</td>
                        <td>{{ $condition['luggage'] ?? $condition['baggage'] ?? 'Normal' }}</td>
                        <td><span class="pressure-badge">{{ $condition['front_pressure'] ?? $condition['pressure_front'] ?? '32' }}</span></td>
                        <td><span class="pressure-badge">{{ $condition['rear_pressure'] ?? $condition['pressure_rear'] ?? '35' }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
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
                {{ $labelLocation['description'] ?? 'Localizações mais comuns para encontrar as informações oficiais.' }}
            </p>
            <div class="location-grid">
                <div class="location-card">
                    <div class="location-priority">Principal</div>
                    <div class="location-desc">{{ $labelLocation['main_location'] ?? 'Porta do motorista' }}</div>
                </div>
                @foreach($labelLocation['alternative_locations'] ?? ['Manual do proprietário', 'Porta-luvas'] as $index => $location)
                <div class="location-card">
                    <div class="location-priority">{{ $index === 0 ? 'Alternativo' : 'Outro' }}</div>
                    <div class="location-desc">{{ $location }}</div>
                </div>
                @endforeach
            </div>
            @if(!empty($labelLocation['note']))
            <div class="alert-box alert-info" style="margin-top: 16px;">
                <strong>💡 Dica:</strong> {{ $labelLocation['note'] }}
            </div>
            @endif
        </div>
        @endif

        <!-- CONDIÇÕES ESPECIAIS (se existir nos mocks) -->
        @if(!empty($specialConditions))
        <div class="content-section">
            <div class="section-header">
                <span class="section-icon">⚖️</span>
                <span class="section-title">Condições Especiais de Uso</span>
            </div>
            <div class="conditions-grid">
                @foreach($specialConditions as $condition)
                <div class="condition-card">
                    <div class="condition-header">{{ $condition['condition'] ?? $condition['title'] }}</div>
                    <div class="condition-body">
                        <div class="condition-adjustment">
                            <div class="adjustment-label">Ajuste Recomendado</div>
                            <div class="adjustment-value">{{ $condition['recommended_adjustment'] ?? $condition['adjustment'] }}</div>
                        </div>
                        @if(!empty($condition['application']))
                        <p style="margin: 12px 0; font-size: 14px;"><strong>Aplicação:</strong> {{ $condition['application'] }}</p>
                        @endif
                        @if(!empty($condition['justification']))
                        <p style="margin: 0; font-size: 14px; color: #6b7280;"><strong>Justificativa:</strong> {{ $condition['justification'] }}</p>
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
                @if(!empty($unitConversion['conversion_table']))
                    @foreach(array_slice($unitConversion['conversion_table'], 0, 3) as $conversion)
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
                        <div class="conversion-unit">{{ $pressureSpecs['pressure_empty_front'] ?? '32' }} PSI</div>
                        <div class="conversion-value">{{ number_format(($pressureSpecs['pressure_empty_front'] ?? 32) / 14.22, 1) }} kgf/cm²</div>
                    </div>
                    <div class="conversion-item">
                        <div class="conversion-unit">{{ $pressureSpecs['pressure_empty_front'] ?? '32' }} PSI</div>
                        <div class="conversion-value">{{ number_format(($pressureSpecs['pressure_empty_front'] ?? 32) / 14.5, 1) }} Bar</div>
                    </div>
                @endif
            </div>
            <p style="text-align: center; font-size: 12px; color: #059669; margin-top: 12px;">
                <strong>Fórmulas:</strong> PSI ÷ 14,22 = kgf/cm² • PSI ÷ 14,5 = Bar
            </p>
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

        <!-- BENEFÍCIOS DA CALIBRAGEM (se existir nos mocks) -->
        @if(!empty($contentData['beneficios_calibragem']))
        <div class="content-section">
            <div class="section-header">
                <span class="section-icon">✅</span>
                <span class="section-title">Benefícios da Calibragem Correta</span>
            </div>
            <div class="benefits-grid">
                @foreach($contentData['beneficios_calibragem'] as $benefit)
                <div class="benefit-card {{ $benefit['categoria'] ?? 'economy' }}">
                    <div class="benefit-title">{{ $benefit['titulo'] ?? $benefit['title'] }}</div>
                    <div class="benefit-list">
                        {{ $benefit['descricao'] ?? $benefit['description'] }}
                        @if(!empty($benefit['aspectos']) && is_array($benefit['aspectos']))
                        <ul style="margin: 8px 0; padding-left: 16px;">
                            @foreach($benefit['aspectos'] as $aspecto)
                            <li>{{ $aspecto }}</li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- DICAS DE MANUTENÇÃO (se existir nos mocks) -->
        @if(!empty($contentData['dicas_manutencao']) || !empty($careRecommendations))
        <div class="content-section">
            <div class="section-header">
                <span class="section-icon">🔧</span>
                <span class="section-title">Dicas de Manutenção</span>
            </div>
            @foreach($contentData['dicas_manutencao'] ?? $careRecommendations as $dica)
            <div class="alert-box alert-info" style="margin-bottom: 16px;">
                <h4 style="margin: 0 0 8px; font-weight: 600;">{{ $dica['categoria'] ?? $dica['category'] }}</h4>
                <p style="margin: 0; font-size: 14px;">{{ $dica['descricao'] ?? $dica['description'] }}</p>
                @if(!empty($dica['itens']) && is_array($dica['itens']))
                <ul style="margin: 8px 0 0 16px; font-size: 14px;">
                    @foreach($dica['itens'] as $item)
                    <li>{{ $item }}</li>
                    @endforeach
                </ul>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        <!-- ALERTAS CRÍTICOS (se existir nos mocks) -->
        @if(!empty($contentData['alertas_criticos']))
        <div class="content-section">
            <div class="section-header">
                <span class="section-icon">⚠️</span>
                <span class="section-title">Alertas Importantes</span>
            </div>
            @foreach($contentData['alertas_criticos'] as $alerta)
            <div class="alert-box alert-{{ $alerta['tipo'] === 'crítico' ? 'critical' : 'warning' }}">
                <h4 style="margin: 0 0 8px; font-weight: 600;">{{ $alerta['titulo'] ?? $alerta['title'] }}</h4>
                <p style="margin: 0;">{{ $alerta['descricao'] ?? $alerta['description'] }}</p>
            </div>
            @endforeach
        </div>
        @endif

        <!-- PROCEDIMENTO DE CALIBRAGEM (se existir nos mocks) -->
        @if(!empty($contentData['procedimento_calibragem']))
        <div class="content-section">
            <div class="section-header">
                <span class="section-icon">📋</span>
                <span class="section-title">Procedimento de Calibragem</span>
            </div>
            <div class="procedure-steps">
                @foreach($contentData['procedimento_calibragem'] as $index => $step)
                <div class="procedure-step">
                    <div class="step-number">{{ $index + 1 }}</div>
                    <div class="step-content">
                        <h4 class="step-title">{{ $step['titulo'] ?? $step['title'] ?? 'Passo ' . ($index + 1) }}</h4>
                        <p class="step-desc">{{ $step['descricao'] ?? $step['description'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- SISTEMA TPMS (se aplicável) -->
        @if($hasTpms)
        <div class="alert-box alert-info">
            <h4 style="margin: 0 0 8px; font-weight: 600;">📡 Sistema TPMS</h4>
            <p style="margin: 0;">Este veículo possui sistema TPMS que monitora automaticamente a pressão dos pneus e alerta no painel quando há variações significativas.</p>
        </div>
        @endif

        <!-- RECURSOS ELÉTRICOS (se aplicável) -->
        @if($isElectric)
        <div class="alert-box alert-success">
            <h4 style="margin: 0 0 8px; font-weight: 600;">🔋 Veículo Elétrico</h4>
            <p style="margin: 0;">Pressão correta é ainda mais importante em veículos elétricos, podendo aumentar a autonomia em até 15-20km por carga.</p>
        </div>
        @endif

        <!-- FAQ COMPLETO dos mocks -->
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

        <!-- IMPACTO NO DESEMPENHO (se existir nos mocks) -->
        @if(!empty($pressureImpact))
        <div class="content-section">
            <div class="section-header">
                <span class="section-icon">📊</span>
                <span class="section-title">Impacto da Pressão no Desempenho</span>
            </div>
            <div class="benefits-grid">
                @foreach($pressureImpact as $impact)
                <div class="benefit-card {{ $impact['type'] ?? 'economy' }}">
                    <div class="benefit-title">{{ $impact['title'] ?? $impact['titulo'] }}</div>
                    <div class="benefit-list">
                        @if(!empty($impact['items']) && is_array($impact['items']))
                        <ul style="margin: 0; padding-left: 16px;">
                            @foreach($impact['items'] as $item)
                            <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                        @elseif(!empty($impact['description']))
                        <p style="margin: 0;">{{ $impact['description'] }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
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

        <!-- CONSIDERAÇÕES FINAIS COMPLETAS dos mocks -->
        @if(!empty($article->getData()['final_considerations']))
        <div class="">
            <div class="section-header">
                <span class="section-icon">📝</span>
                <span class="section-title">Considerações Finais</span>
            </div>
            <div style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-left: 4px solid #2563eb; padding: 24px; border-radius: 8px; line-height: 1.8; font-size: 16px;">
                {!! nl2br(e($article->getData()['final_considerations'])) !!}
            </div>
        </div>
        @endif
        
        <!-- RESUMO EXECUTIVO FINAL -->
        <div class="highlight-box">
            <div class="highlight-title">🚗 Resumo Executivo</div>
            <div style="text-align: left; max-width: 600px; margin: 0 auto;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <h4 style="margin: 0 0 8px; font-weight: 600; color: #1e40af;">Pressões Principais:</h4>
                        <p style="margin: 0; font-size: 14px;">
                            Dianteiros: <strong>{{ $pressureSpecs['pressure_empty_front'] ?? '32' }} PSI</strong><br>
                            Traseiros: <strong>{{ $pressureSpecs['pressure_empty_rear'] ?? '32' }} PSI</strong>
                        </p>
                    </div>
                    <div>
                        <h4 style="margin: 0 0 8px; font-weight: 600; color: #1e40af;">Carga Completa:</h4>
                        <p style="margin: 0; font-size: 14px;">
                            Dianteiros: <strong>{{ $pressureSpecs['pressure_max_front'] ?? $pressureSpecs['pressure_empty_front'] ?? '32' }} PSI</strong><br>
                            Traseiros: <strong>{{ $pressureSpecs['pressure_max_rear'] ?? '35' }} PSI</strong>
                        </p>
                    </div>
                </div>
                
                <div style="border-top: 1px solid #93c5fd; padding-top: 16px;">
                    <h4 style="margin: 0 0 12px; font-weight: 600; color: #1e40af;">Lembre-se Sempre:</h4>
                    <ul style="margin: 0; padding-left: 20px; font-size: 14px; color: #1e40af;">
                        <li>Verificar mensalmente (ou quinzenalmente se premium)</li>
                        <li>Sempre com pneus frios (3 horas parados mínimo)</li>
                        <li>Incluir o estepe na verificação @if(!empty($pressureSpecs['pressure_spare']))({{ $pressureSpecs['pressure_spare'] }} PSI)@endif</li>
                        <li>Ajustar conforme carga e condições de uso</li>
                        @if($hasTpms)<li>Aguardar recalibração do TPMS após ajustes</li>@endif
                        @if($isElectric)<li>Pressão correta aumenta autonomia elétrica</li>@endif
                    </ul>
                </div>
            </div>
        </div>

        <!-- NOTA TÉCNICA FINAL -->
        <div class="alert-box alert-info" style="margin-top: 32px;">
            <p style="margin: 0; font-size: 13px; text-align: center;">
                <strong>📋 Nota Técnica:</strong> As informações deste guia são baseadas nas especificações oficiais do {{ $vehicleName }}. 
                Em caso de dúvidas específicas, consulte sempre o manual do proprietário ou um profissional qualificado. 
                A calibragem correta é fundamental para segurança, economia e desempenho do veículo.
            </p>
        </div>

        <!-- Nota informativa -->
        @include('auto-info-center::article.partials.info_note_manual')
        
        <!-- Footer do artigo -->
        @include('auto-info-center::article.partials.article_footer')
    </article>
</div>
@endsection