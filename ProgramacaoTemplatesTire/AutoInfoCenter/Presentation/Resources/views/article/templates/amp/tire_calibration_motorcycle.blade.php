{{--
Template AMP: tire_calibration_motorcycle.blade.php
VERSÃO FINAL CORRIGIDA - Com busca inteligente de dados
--}}

@extends('auto-info-center::layouts.amp')

@section('amp-head')
<script async custom-element="amp-accordion" src="https://cdn.ampproject.org/v0/amp-accordion-0.1.js"></script>
<style amp-custom>
/* CSS LIMPO BASEADO NO TEMPLATE DE CARROS */
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;line-height:1.6;color:#333;margin:0;background:#fff}
.container{max-width:800px;margin:0 auto;padding:16px}

/* TYPOGRAPHY */
h1{font-size:28px;font-weight:700;margin-bottom:16px;color:#151C25;line-height:1.3}
h2{font-size:22px;font-weight:600;margin:32px 0 16px;padding-bottom:8px;border-bottom:2px solid #2563eb;color:#151C25}
h3{font-size:18px;font-weight:600;margin:20px 0 12px;color:#151C25}
p{margin-bottom:16px;line-height:1.7}

/* COMPONENTES PRINCIPAIS */
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

/* ESPECIFICAÇÕES DOS PNEUS */
.tire-specs-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin:20px 0}
.tire-spec-card{border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;background:#fff}
.tire-spec-header{background:linear-gradient(135deg,#2563eb,#1d4ed8);color:white;padding:16px;font-weight:600;text-align:center}
.tire-spec-body{padding:20px}
.spec-row{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f1f5f9}
.spec-row:last-child{border-bottom:none}
.spec-label{font-size:14px;color:#6b7280;font-weight:500}
.spec-value{font-size:14px;font-weight:600;color:#1f2937;background:#eff6ff;padding:4px 8px;border-radius:4px}

/* TABELA DE PRESSÕES */
.pressure-table{width:100%;border-collapse:collapse;margin:20px 0;border-radius:8px;overflow:hidden;box-shadow:0 2px 4px rgba(0,0,0,0.1)}
.pressure-table th{background:linear-gradient(135deg,#2563eb,#1d4ed8);color:white;padding:12px 8px;text-align:center;font-size:13px;font-weight:600}
.pressure-table td{padding:10px 8px;border-bottom:1px solid #f1f5f9;text-align:center;font-size:13px}
.pressure-table tr:nth-child(even){background:#f8fafc}
.pressure-badge{background:#dbeafe;color:#1e40af;padding:4px 8px;border-radius:12px;font-weight:700;font-size:11px}

/* CONSIDERAÇÕES ESPECIAIS */
.considerations-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;margin:20px 0}
.consideration-card{border:1px solid #e5e7eb;border-radius:8px;background:#fff;overflow:hidden}
.consideration-header{background:linear-gradient(135deg,#6366f1,#4f46e5);color:white;padding:14px 16px;font-weight:600;text-align:center}
.consideration-body{padding:16px}
.consideration-list{list-style:none;padding:0;margin:12px 0}
.consideration-list li{margin-bottom:8px;padding-left:20px;position:relative;color:#4b5563;font-size:14px;line-height:1.5}
.consideration-list li::before{content:'•';color:#2563eb;font-weight:bold;position:absolute;left:0}

/* CONVERSÃO DE UNIDADES */
.conversion-container{background:linear-gradient(135deg,#ecfdf5,#d1fae5);border:1px solid #10b981;border-radius:12px;padding:24px;margin:24px 0}
.conversion-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:16px;margin:16px 0}
.conversion-item{background:rgba(255,255,255,0.8);border:1px solid #6ee7b7;border-radius:8px;padding:12px;text-align:center}
.conversion-unit{font-size:12px;color:#065f46;font-weight:600;margin-bottom:4px}
.conversion-value{font-size:16px;font-weight:700;color:#047857}

/* BENEFÍCIOS DA CALIBRAGEM */
.benefits-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px;margin:20px 0}
.benefit-card{border-radius:8px;padding:20px;text-align:center;border-left:4px solid}
.benefit-card.safety{border-color:#ef4444;background:linear-gradient(135deg,#fef2f2,#fee2e2)}
.benefit-card.economy{border-color:#10b981;background:linear-gradient(135deg,#ecfdf5,#d1fae5)}
.benefit-card.performance{border-color:#f59e0b;background:linear-gradient(135deg,#fffbeb,#fef3c7)}
.benefit-card.durability{border-color:#3b82f6;background:linear-gradient(135deg,#eff6ff,#dbeafe)}
.benefit-title{font-weight:600;margin-bottom:12px;color:#1f2937}
.benefit-list{text-align:left;font-size:14px;line-height:1.6}

/* ALERTAS */
.alert-box{border-radius:8px;padding:16px;margin:16px 0;border-left:4px solid}
.alert-critical{background:#fef2f2;border-color:#ef4444}
.alert-warning{background:#fffbeb;border-color:#f59e0b}
.alert-info{background:#eff6ff;border-color:#3b82f6}
.alert-success{background:#f0fdf4;border-color:#10b981}

/* PROCEDIMENTO DE CALIBRAGEM */
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
.tire-specs-grid,.considerations-grid,.benefits-grid{grid-template-columns:1fr}
.pressure-grid{grid-template-columns:1fr}
.conversion-grid{grid-template-columns:1fr 1fr}
}
</style>
@endsection

@section('content')
<div class="container">
    <article>
        @php
            // BUSCA INTELIGENTE DE DADOS - Múltiplas fontes
            $vehicleInfo = $article->getData()['vehicle_info'] ?? [];
            $pressureSpecs = $article->getData()['pressure_specifications'] ?? [];
            $contentData = $article->getData()['content'] ?? [];
            
            // BUSCA DA TABELA DE PRESSÕES - Ordem de prioridade
            $pressureTable = null;
            if (!empty($article->getData()['pressure_table'])) {
                $pressureTable = $article->getData()['pressure_table'];
            } elseif (!empty($contentData['tabela_pressoes'])) {
                $pressureTable = $contentData['tabela_pressoes'];
            } elseif (!empty($article->content['tabela_pressoes'])) {
                $pressureTable = $article->content['tabela_pressoes'];
            }
            
            // BUSCA DE OUTROS DADOS
            $tireSpecs = $article->getData()['tire_specifications'] ?? [];
            $specialConsiderations = $article->getData()['special_considerations'] ?? [];
            $unitConversion = $article->getData()['unit_conversion'] ?? [];
            $informationLocation = $article->getData()['information_location'] ?? [];
            $maintenanceTips = $article->getData()['maintenance_tips'] ?? [];
            $calibrationBenefits = $article->getData()['calibration_benefits'] ?? [];
            $calibrationProcedure = $article->getData()['calibration_procedure'] ?? [];
            $criticalAlerts = $article->getData()['critical_alerts'] ?? [];
            $faq = $article->getData()['faq'] ?? $contentData['perguntas_frequentes'] ?? [];
            
            $vehicleName = $vehicleInfo['full_name'] ?? $article->getData()['title'] ?? 'motocicleta';
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
            <div class="highlight-title">🏍️ Pressões Ideais para {{ $vehicleName }}</div>
            <div class="pressure-grid">
                <div class="pressure-card">
                    <div class="pressure-label">🔄 Pneu Dianteiro</div>
                    <div class="pressure-value">{{ $pressureSpecs['pressure_empty_front'] ?? '33' }}</div>
                    <div class="pressure-unit">PSI</div>
                </div>
                <div class="pressure-card">
                    <div class="pressure-label">🔙 Pneu Traseiro</div>
                    <div class="pressure-value">{{ $pressureSpecs['pressure_empty_rear'] ?? '36' }}</div>
                    <div class="pressure-unit">PSI</div>
                </div>
            </div>
            <p style="color: #1e40af; font-weight: 500; font-size: 14px; margin-top: 16px;">
                Verificação semanal obrigatória • Sempre com pneus frios
            </p>
        </div>


        <!-- ESPECIFICAÇÕES DOS PNEUS -->
        @if(!empty($tireSpecs))
        <div class="content-section">
            <div class="section-header">
                <span class="section-icon">🏍️</span>
                <span class="section-title">Especificações dos Pneus</span>
            </div>
            <div class="tire-specs-grid">
                @if(!empty($tireSpecs['front_tire']))
                <div class="tire-spec-card">
                    <div class="tire-spec-header">Pneu Dianteiro</div>
                    <div class="tire-spec-body">
                        @php $frontTire = $tireSpecs['front_tire'] @endphp
                        @if(!empty($frontTire['tire_size']))
                        <div class="spec-row">
                            <span class="spec-label">Medida:</span>
                            <span class="spec-value">{{ $frontTire['tire_size'] }}</span>
                        </div>
                        @endif
                        @if(!empty($frontTire['load_speed_index']))
                        <div class="spec-row">
                            <span class="spec-label">Índice Carga/Velocidade:</span>
                            <span class="spec-value">{{ $frontTire['load_speed_index'] }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
                
                @if(!empty($tireSpecs['rear_tire']))
                <div class="tire-spec-card">
                    <div class="tire-spec-header">Pneu Traseiro</div>
                    <div class="tire-spec-body">
                        @php $rearTire = $tireSpecs['rear_tire'] @endphp
                        @if(!empty($rearTire['tire_size']))
                        <div class="spec-row">
                            <span class="spec-label">Medida:</span>
                            <span class="spec-value">{{ $rearTire['tire_size'] }}</span>
                        </div>
                        @endif
                        @if(!empty($rearTire['load_speed_index']))
                        <div class="spec-row">
                            <span class="spec-label">Índice Carga/Velocidade:</span>
                            <span class="spec-value">{{ $rearTire['load_speed_index'] }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
            
            @if(!empty($tireSpecs['observation']))
            <div class="alert-box alert-info">
                <strong>📌 Importante:</strong> {{ $tireSpecs['observation'] }}
            </div>
            @endif
        </div>
        @endif


        <!-- TABELA DE PRESSÕES - VERSÃO FINAL LIMPA -->
        @if(!empty($pressureTable))
        <div class="content-section">
            <div class="section-header">
                <span class="section-icon">📊</span>
                <span class="section-title">Tabela de Pressões dos Pneus (PSI)</span>
            </div>
            
            <div style="overflow-x: auto;">
                <table class="pressure-table">
                    <thead>
                        <tr>
                            <th style="text-align: left; padding-left: 16px;">Condição de Uso</th>
                            <th>Dianteiro</th>
                            <th>Traseiro</th>
                            <th>Observações</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Pressões Oficiais --}}
                        @if(!empty($pressureTable['official_pressures']))
                        @foreach($pressureTable['official_pressures'] as $key => $pressure)
                            <tr style="background: {{ $loop->even ? '#f8fafc' : 'white' }};">
                                <td style="text-align: left; padding-left: 16px; font-weight: 500;">
                                    @if($pressure['condition'] === 'piloto_solo')
                                        <span style="margin-right: 8px; font-size: 16px;">🏍️</span>
                                        <strong>Piloto Solo</strong><br>
                                        <small style="font-size: 12px; color: #6b7280;">{{ $pressure['observation'] ?? 'Uso normal' }}</small>
                                    @elseif($pressure['condition'] === 'piloto_garupa')
                                        <span style="margin-right: 8px; font-size: 16px;">👥</span>
                                        <strong>Piloto + Garupa</strong><br>
                                        <small style="font-size: 12px; color: #6b7280;">{{ $pressure['observation'] ?? 'Com passageiro' }}</small>
                                    @else
                                        <span style="margin-right: 8px; font-size: 16px;">⚙️</span>
                                        <strong>{{ ucfirst(str_replace('_', ' ', $pressure['condition'])) }}</strong><br>
                                        <small style="font-size: 12px; color: #6b7280;">{{ $pressure['observation'] ?? '' }}</small>
                                    @endif
                                </td>
                                <td><span class="pressure-badge">{{ $pressure['front'] }}</span></td>
                                <td><span class="pressure-badge">{{ $pressure['rear'] }}</span></td>
                                <td style="text-align: left; max-width: 200px; color: #4b5563; font-size: 12px;">
                                    {{ $pressure['observation'] ?? 'Pressão oficial' }}
                                </td>
                            </tr>
                        @endforeach
                        @endif

                        {{-- Condições Especiais --}}
                        @if(!empty($pressureTable['special_conditions']))
                        @foreach($pressureTable['special_conditions'] as $condition)
                        <tr style="background: {{ ($loop->index + 2) % 2 === 0 ? '#f8fafc' : 'white' }};">
                            <td style="text-align: left; padding-left: 16px; font-weight: 500;">
                                <span style="margin-right: 8px; font-size: 16px;">
                                    @switch($condition['icon_class'] ?? 'default')
                                        @case('home') 🏠 @break
                                        @case('map') 🗺️ @break
                                        @case('user') 👤 @break
                                        @case('package') 📦 @break
                                        @case('cloud-rain') 🌧️ @break
                                        @default 📍
                                    @endswitch
                                </span>
                                <strong>{{ $condition['situation'] ?? 'Condição Especial' }}</strong>
                                @if(!empty($condition['terrain']))
                                <br><small style="font-size: 12px; color: #6b7280;">{{ $condition['terrain'] }}</small>
                                @endif
                            </td>
                            <td><span class="pressure-badge">{{ $condition['front_pressure'] }}</span></td>
                            <td><span class="pressure-badge">{{ $condition['rear_pressure'] }}</span></td>
                            <td style="text-align: left; max-width: 200px; color: #4b5563; font-size: 12px;">
                                {{ $condition['observation'] ?? '' }}
                                @if(!empty($condition['ideal_temperature']))
                                <br><em style="color: #10b981; font-size: 11px;">{{ $condition['ideal_temperature'] }}</em>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        @endif

                        {{-- Fallback para dados básicos --}}
                        @if(empty($pressureTable['official_pressures']) && empty($pressureTable['special_conditions']))
                        <tr style="background: white;">
                            <td style="text-align: left; padding-left: 16px; font-weight: 500;">
                                <span style="margin-right: 8px; font-size: 16px;">🏍️</span>
                                <strong>Uso Normal</strong><br>
                                <small style="font-size: 12px; color: #6b7280;">Piloto solo</small>
                            </td>
                            <td><span class="pressure-badge">{{ $pressureSpecs['pressure_empty_front'] ?? '28' }} PSI</span></td>
                            <td><span class="pressure-badge">{{ $pressureSpecs['pressure_empty_rear'] ?? '30' }} PSI</span></td>
                            <td style="text-align: left; max-width: 200px; color: #4b5563; font-size: 12px;">Uso diário</td>
                        </tr>
                        <tr style="background: #f8fafc;">
                            <td style="text-align: left; padding-left: 16px; font-weight: 500;">
                                <span style="margin-right: 8px; font-size: 16px;">👥</span>
                                <strong>Com Garupa</strong><br>
                                <small style="font-size: 12px; color: #6b7280;">Piloto + passageiro</small>
                            </td>
                            <td><span class="pressure-badge">{{ $pressureSpecs['pressure_max_front'] ?? $pressureSpecs['pressure_empty_front'] ?? '28' }} PSI</span></td>
                            <td><span class="pressure-badge">{{ $pressureSpecs['pressure_max_rear'] ?? '32' }} PSI</span></td>
                            <td style="text-align: left; max-width: 200px; color: #4b5563; font-size: 12px;">Carga dupla</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Nota importante da tabela -->
            <div style="background: #f8fafc; padding: 16px; margin-top: 16px; border-radius: 8px; border-left: 4px solid #2563eb;">
                <p style="margin: 0; font-size: 14px; color: #374151;">
                    <span style="font-weight: 600;">⚠️ Importante:</span> 
                    Sempre verifique com pneus frios (pelo menos 3 horas parados). No calor brasileiro (35°C+), a pressão pode aumentar 4-6 PSI durante a pilotagem.
                </p>
            </div>
        </div>
        @endif

        <!-- CONVERSÃO DE UNIDADES -->
        @if(!empty($unitConversion) || true)
        <div class="conversion-container">
            <h3 style="text-align: center; color: #065f46; margin-bottom: 16px;">🔄 Conversão de Unidades</h3>
            <div class="conversion-grid">
                @if(!empty($unitConversion['conversion_table']))
                    @foreach(array_slice($unitConversion['conversion_table'], 0, 4) as $conversion)
                    <div class="conversion-item">
                        <div class="conversion-unit">{{ $conversion['psi'] }} PSI</div>
                        <div class="conversion-value">{{ $conversion['kgf_cm2'] }} kgf/cm²</div>
                    </div>
                    @endforeach
                @else
                    <div class="conversion-item">
                        <div class="conversion-unit">{{ $pressureSpecs['pressure_empty_front'] ?? '33' }} PSI</div>
                        <div class="conversion-value">{{ number_format(($pressureSpecs['pressure_empty_front'] ?? 33) / 14.22, 1) }} kgf/cm²</div>
                    </div>
                    <div class="conversion-item">
                        <div class="conversion-unit">{{ $pressureSpecs['pressure_empty_rear'] ?? '36' }} PSI</div>
                        <div class="conversion-value">{{ number_format(($pressureSpecs['pressure_empty_rear'] ?? 36) / 14.22, 1) }} kgf/cm²</div>
                    </div>
                @endif
            </div>
            <p style="text-align: center; font-size: 12px; color: #059669; margin-top: 12px;">
                <strong>Fórmula:</strong> PSI ÷ 14,22 = kgf/cm²
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

        <!-- CONSIDERAÇÕES ESPECIAIS -->
        @if(!empty($specialConsiderations))
        <div class="content-section">
            <div class="section-header">
                <span class="section-icon">⚠️</span>
                <span class="section-title">Considerações Especiais</span>
            </div>
            <div class="considerations-grid">
                @foreach($specialConsiderations as $consideration)
                <div class="consideration-card">
                    <div class="consideration-header">{{ $consideration['title'] ?? 'Consideração' }}</div>
                    <div class="consideration-body">
                        @if(!empty($consideration['description']))
                        <p style="color: #4b5563; margin-bottom: 12px;">{{ $consideration['description'] }}</p>
                        @endif
                        
                        @if(!empty($consideration['factors']) && is_array($consideration['factors']))
                        <ul class="consideration-list">
                            @foreach($consideration['factors'] as $factor)
                            <li>{{ $factor }}</li>
                            @endforeach
                        </ul>
                        @endif
                        
                        @if(!empty($consideration['types']) && is_array($consideration['types']))
                        <ul class="consideration-list">
                            @foreach($consideration['types'] as $type)
                            <li>{{ $type }}</li>
                            @endforeach
                        </ul>
                        @endif
                        
                        @if(!empty($consideration['orientations']) && is_array($consideration['orientations']))
                        <ul class="consideration-list">
                            @foreach($consideration['orientations'] as $orientation)
                            <li>{{ $orientation }}</li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- BENEFÍCIOS DA CALIBRAGEM -->
        @if(!empty($calibrationBenefits))
        <div class="content-section">
            <div class="section-header">
                <span class="section-icon">✅</span>
                <span class="section-title">Benefícios da Calibragem Correta</span>
            </div>
            <div class="benefits-grid">
                @foreach($calibrationBenefits as $benefit)
                <div class="benefit-card {{ $benefit['category'] ?? 'economy' }}">
                    <div class="benefit-title">{{ $benefit['title'] ?? 'Benefício' }}</div>
                    <div class="benefit-list">
                        @if(!empty($benefit['description']))
                        <p style="margin-bottom: 12px;">{{ $benefit['description'] }}</p>
                        @endif
                        @if(!empty($benefit['aspects']) && is_array($benefit['aspects']))
                        <ul style="margin: 0; padding-left: 16px;">
                            @foreach($benefit['aspects'] as $aspect)
                            <li>{{ $aspect }}</li>
                            @endforeach
                        </ul>
                        @endif
                        @if(!empty($benefit['estimated_savings']))
                        <p style="margin-top: 12px; font-weight: 600; color: #047857;">
                            💰 {{ $benefit['estimated_savings'] }}
                        </p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- PROCEDIMENTO DE CALIBRAGEM -->
        @if(!empty($calibrationProcedure))
        <div class="content-section">
            <div class="section-header">
                <span class="section-icon">🔧</span>
                <span class="section-title">Como Calibrar Corretamente</span>
            </div>
            <div class="procedure-steps">
                @foreach($calibrationProcedure as $step)
                <div class="procedure-step">
                    <div class="step-number">{{ $step['number'] ?? '1' }}</div>
                    <div class="step-content">
                        <h4 class="step-title">{{ $step['title'] ?? 'Passo' }}</h4>
                        <p class="step-desc">{{ $step['description'] ?? '' }}</p>
                        @if(!empty($step['details']) && is_array($step['details']))
                        <ul class="consideration-list" style="margin-top: 8px;">
                            @foreach($step['details'] as $detail)
                            <li>{{ $detail }}</li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- ALERTAS CRÍTICOS -->
        @if(!empty($criticalAlerts))
        <div class="content-section">
            <div class="section-header">
                <span class="section-icon">🚨</span>
                <span class="section-title">Alertas Importantes</span>
            </div>
            @foreach($criticalAlerts as $alert)
            <div class="alert-box alert-{{ strtolower($alert['type'] ?? 'critical') }}">
                <h4 style="margin: 0 0 8px; font-weight: 600;">{{ $alert['title'] ?? 'Alerta' }}</h4>
                <p style="margin: 0;">{{ $alert['description'] ?? '' }}</p>
                @if(!empty($alert['consequence']))
                <p style="margin: 8px 0 0;"><strong>Consequência:</strong> {{ $alert['consequence'] }}</p>
                @endif
            </div>
            @endforeach
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

        <!-- FAQ -->
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

        <!-- Resumo Rápido -->
        <div class="highlight-box">
            <div class="highlight-title">🏍️ Resumo Rápido</div>
            <div style="text-align: left; max-width: 600px; margin: 0 auto;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <h4 style="margin: 0 0 8px; font-weight: 600; color: #1e40af;">Piloto Solo:</h4>
                        <p style="margin: 0; font-size: 14px;">
                            Dianteiro: <strong>{{ $pressureSpecs['pressure_empty_front'] ?? '33' }} PSI</strong><br>
                            Traseiro: <strong>{{ $pressureSpecs['pressure_empty_rear'] ?? '36' }} PSI</strong>
                        </p>
                    </div>
                    <div>
                        <h4 style="margin: 0 0 8px; font-weight: 600; color: #1e40af;">Piloto + Garupa:</h4>
                        <p style="margin: 0; font-size: 14px;">
                            Dianteiro: <strong>{{ $pressureSpecs['pressure_max_front'] ?? $pressureSpecs['pressure_empty_front'] ?? '35' }} PSI</strong><br>
                            Traseiro: <strong>{{ $pressureSpecs['pressure_max_rear'] ?? '38' }} PSI</strong>
                        </p>
                    </div>
                </div>
                
                <div style="border-top: 1px solid #93c5fd; padding-top: 16px;">
                    <h4 style="margin: 0 0 12px; font-weight: 600; color: #1e40af;">Lembre-se Sempre:</h4>
                    <ul style="margin: 0; padding-left: 20px; font-size: 14px; color: #1e40af;">
                        <li>Verificação semanal obrigatória para motocicletas</li>
                        <li>Sempre com pneus frios (3 horas parados mínimo)</li>
                        <li>Use manômetro confiável e específico para motos</li>
                        <li>Ajuste conforme peso do piloto e garupa</li>
                        <li>Em motos esportivas, precisão é questão de vida</li>
                        <li>Pressão incorreta pode causar acidentes fatais</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- ALERTA FINAL DE SEGURANÇA -->
        <div class="alert-box alert-critical" style="margin-top: 32px;">
            <h4 style="margin: 0 0 8px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                🚨 Segurança em Primeiro Lugar
            </h4>
            <p style="margin: 0; font-size: 14px;">
                <strong>📋 Checklist de Segurança:</strong> Verifique semanalmente a pressão • Sempre com pneus frios • 
                Use manômetro confiável • Ajuste conforme a carga • Em caso de dúvida, consulte o manual. 
                Sua vida vale mais que alguns minutos na calibragem.
            </p>
        </div>

        <!-- Nota informativa -->
        @include('auto-info-center::article.partials.info_note_manual')
        
        <!-- Footer do artigo -->
        @include('auto-info-center::article.partials.article_footer')
    </article>
</div>
@endsection