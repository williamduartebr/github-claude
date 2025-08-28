{{--
Template AMP: tire_pressure_guide_motorcycle.blade.php
Guia completo de calibragem para motocicletas - Versão AMP otimizada
--}}

@extends('auto-info-center::layouts.amp')

@section('amp-head')
<script async custom-element="amp-accordion" src="https://cdn.ampproject.org/v0/amp-accordion-0.1.js"></script>
<style amp-custom>
/* CSS OTIMIZADO PARA GUIA DE CALIBRAGEM MOTOCICLETAS */
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;line-height:1.6;color:#333;margin:0;background:#fff}
.container{max-width:800px;margin:0 auto;padding:16px}

/* TYPOGRAPHY */
h1{font-size:28px;font-weight:700;margin-bottom:16px;color:#151C25;line-height:1.3}
h2{font-size:22px;font-weight:600;margin:32px 0 16px;padding-bottom:8px;border-bottom:2px solid #DC2626;color:#151C25}
h3{font-size:18px;font-weight:600;margin:20px 0 12px;color:#151C25}
h4{font-size:16px;font-weight:600;margin:16px 0 8px;color:#1f2937}
p{margin-bottom:16px;line-height:1.7}

/* SEÇÕES DE CONTEÚDO */
.content-section{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:24px;margin:24px 0;box-shadow:0 2px 4px rgba(0,0,0,0.05)}
.section-header{display:flex;align-items:center;margin-bottom:20px}
.section-icon{font-size:24px;margin-right:12px}
.section-title{font-size:18px;font-weight:600;color:#1f2937}

/* ESPECIFICAÇÕES TÉCNICAS MOTOCICLETAS */
.motorcycle-specs{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin:20px 0}
.motorcycle-spec{background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:16px;position:relative}
.motorcycle-spec.front{border-color:#f59e0b;background:#fffbeb}
.motorcycle-spec.rear{border-color:#dc2626;background:#fef2f2}
.tire-type{font-size:14px;font-weight:600;color:#DC2626;margin-bottom:12px;text-transform:uppercase;display:flex;align-items:center}
.tire-type.front{color:#d97706}
.tire-type.rear{color:#dc2626}
.spec-row{display:flex;justify-content:space-between;margin-bottom:8px;padding-bottom:4px;border-bottom:1px solid rgba(0,0,0,0.1)}
.spec-row:last-child{border-bottom:none;margin-bottom:0}
.spec-label{font-size:13px;color:#6b7280;font-weight:500}
.spec-value{font-size:13px;font-weight:600;color:#1f2937}
.power-indicator{position:absolute;top:-8px;right:12px;background:#dc2626;color:white;padding:4px 8px;border-radius:12px;font-size:10px;font-weight:600}

/* TABELA DE PRESSÕES MOTOCICLETAS */
.motorcycle-pressure-table{width:100%;border-collapse:collapse;margin:20px 0;border-radius:8px;overflow:hidden;box-shadow:0 2px 4px rgba(0,0,0,0.1)}
.motorcycle-pressure-table th{background:linear-gradient(135deg,#DC2626,#b91c1c);color:white;padding:12px 8px;text-align:center;font-size:12px;font-weight:600}
.motorcycle-pressure-table td{padding:10px 8px;border-bottom:1px solid #f1f5f9;text-align:center;font-size:12px}
.motorcycle-pressure-table tr:nth-child(even){background:#fef2f2}
.motorcycle-pressure-table tr.urban{background:#f0fdf4}
.motorcycle-pressure-table tr.sport{background:#fef2f2}
.motorcycle-pressure-table tr.touring{background:#eff6ff}
.pressure-value{background:#fecaca;color:#7f1d1d;padding:4px 8px;border-radius:12px;font-weight:700;font-size:11px;display:inline-block}
.pressure-value.front{background:#fed7aa;color:#9a3412}
.pressure-value.rear{background:#fecaca;color:#7f1d1d}

/* PROCEDIMENTO MOTOCICLETAS */
.motorcycle-procedure{margin:20px 0}
.motorcycle-step{display:flex;margin-bottom:24px;align-items:flex-start}
.motorcycle-step-number{flex-shrink:0;width:40px;height:40px;background:linear-gradient(135deg,#DC2626,#b91c1c);color:white;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:16px;margin-right:16px}
.motorcycle-step-content{flex:1}
.motorcycle-step-title{font-size:16px;font-weight:600;color:#1f2937;margin-bottom:8px}
.motorcycle-step-desc{font-size:14px;color:#4b5563;margin-bottom:12px}
.motorcycle-safety-note{background:#fef2f2;border-left:3px solid #dc2626;padding:8px 12px;margin:8px 0;font-size:12px;color:#7f1d1d;font-weight:500}
.motorcycle-safety-note:before{content:"⚠️ ";font-weight:bold}
.motorcycle-tips{list-style:none;padding:0;margin:0}
.motorcycle-tips li{background:#f0f9ff;border-left:3px solid #0EA5E9;padding:8px 12px;margin-bottom:6px;font-size:13px}
.motorcycle-tips li:before{content:"🏍️";margin-right:8px}

/* RECOMENDAÇÕES DE PILOTAGEM */
.riding-recommendations{display:grid;grid-template-columns:1fr;gap:16px;margin:20px 0}
.riding-card{border:1px solid #e5e7eb;border-radius:8px;padding:16px;position:relative;overflow:hidden}
.riding-card.urban{background:linear-gradient(135deg,#f0fdf4,#dcfce7);border-color:#22c55e}
.riding-card.highway{background:linear-gradient(135deg,#eff6ff,#dbeafe);border-color:#3b82f6}
.riding-card.sport{background:linear-gradient(135deg,#fef2f2,#fecaca);border-color:#ef4444}
.riding-card.track{background:linear-gradient(135deg,#fdf2f8,#fce7f3);border-color:#ec4899}
.riding-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}
.riding-title{font-size:14px;font-weight:600;color:#1f2937;display:flex;align-items:center}
.riding-pressure{background:rgba(255,255,255,0.9);color:#1f2937;padding:4px 8px;border-radius:12px;font-weight:700;font-size:12px}
.riding-desc{font-size:13px;color:#374151;margin-bottom:8px}
.riding-tip{background:rgba(255,255,255,0.7);padding:8px 12px;border-radius:6px;font-size:12px;color:#1f2937}
.riding-frequency{font-size:11px;color:#6b7280;margin-top:8px;font-style:italic}
.safety-level{position:absolute;top:8px;right:8px;padding:2px 6px;border-radius:10px;font-size:10px;font-weight:600}
.safety-level.alto{background:#fecaca;color:#7f1d1d}
.safety-level.medio{background:#fed7aa;color:#9a3412}
.safety-level.baixo{background:#d1fae5;color:#14532d}

/* COMPARATIVO DE IMPACTOS */
.impact-comparison{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;margin:20px 0}
.impact-scenario{border:1px solid #e5e7eb;border-radius:8px;padding:16px;text-align:center}
.impact-scenario.baixa{background:#fef2f2;border-color:#fca5a5;color:#7f1d1d}
.impact-scenario.ideal{background:#f0fdf4;border-color:#86efac;color:#14532d}
.impact-scenario.alta{background:#fffbeb;border-color:#fcd34d;color:#92400e}
.impact-icon{font-size:32px;margin-bottom:12px}
.impact-scenario-title{font-size:14px;font-weight:600;margin-bottom:8px}
.impact-effects{list-style:none;padding:0;margin:8px 0}
.impact-effects li{font-size:11px;margin-bottom:4px;padding-left:12px;position:relative}
.impact-effects li:before{content:"•";position:absolute;left:0;font-weight:bold}

/* PNEUS ALTERNATIVOS MOTOCICLETAS */
.motorcycle-alternatives{display:grid;grid-template-columns:1fr;gap:16px;margin:20px 0}
.alternative-category{border:1px solid #e5e7eb;border-radius:8px;padding:16px;background:#fff}
.alternative-category.sport{background:#fef2f2;border-color:#fca5a5}
.alternative-category.touring{background:#eff6ff;border-color:#93c5fd}
.alternative-category.street{background:#f0fdf4;border-color:#86efac}
.alternative-title{font-size:14px;font-weight:600;color:#1f2937;margin-bottom:12px;text-transform:uppercase;letter-spacing:0.5px;display:flex;align-items:center}
.alternative-brands{display:grid;grid-template-columns:repeat(auto-fit,minmax(100px,1fr));gap:8px}
.alternative-brand{background:#f1f5f9;padding:8px 12px;border-radius:6px;text-align:center;font-size:12px;font-weight:500;color:#334155}

/* EQUIPAMENTOS MOTOCICLETAS */
.motorcycle-equipment{display:grid;grid-template-columns:1fr;gap:12px;margin:20px 0}
.equipment-item{border:1px solid #e5e7eb;border-radius:8px;padding:16px;background:#f8fafc}
.equipment-item.motorcycle-specific{background:#fef2f2;border-color:#fca5a5}
.equipment-header{display:flex;align-items:center;justify-content:between;margin-bottom:8px}
.equipment-name{font-size:14px;font-weight:600;color:#1f2937;flex:1}
.equipment-importance{font-size:11px;padding:2px 6px;border-radius:10px;font-weight:600}
.importance-essential{background:#fecaca;color:#7f1d1d}
.importance-important{background:#fed7aa;color:#9a3412}
.importance-recommended{background:#d1fae5;color:#14532d}
.importance-optional{background:#e5e7eb;color:#374151}
.equipment-desc{font-size:12px;color:#6b7280;margin-bottom:8px}
.equipment-price{font-size:11px;color:#059669;font-weight:600}
.motorcycle-specific-badge{background:#dc2626;color:white;padding:2px 6px;border-radius:10px;font-size:10px;margin-left:8px}

/* CUIDADOS ESPECIAIS */
.special-care{display:grid;grid-template-columns:1fr;gap:16px;margin:20px 0}
.care-card{border:1px solid #e5e7eb;border-radius:8px;padding:16px;background:#f8fafc}
.care-header{display:flex;align-items:center;margin-bottom:12px}
.care-icon{width:28px;height:28px;border-radius:50%;background:#DC2626;color:white;display:flex;align-items:center;justify-content:center;margin-right:12px;font-size:14px}
.care-title{font-size:14px;font-weight:600;color:#1f2937}
.care-frequency{font-size:11px;color:#6b7280;background:#fff;padding:2px 6px;border-radius:10px;margin-left:8px}
.care-items{list-style:none;padding:0;margin:8px 0 0}
.care-items li{font-size:12px;color:#374151;margin-bottom:4px;padding-left:12px;position:relative}
.care-items li:before{content:"🔧";position:absolute;left:0}

/* SINAIS DE PROBLEMAS */
.problem-signs{display:grid;grid-template-columns:1fr;gap:12px;margin:20px 0}
.problem-card{border:1px solid #e5e7eb;border-radius:8px;padding:16px}
.problem-card.alta{background:#fef2f2;border-color:#fca5a5}
.problem-card.media{background:#fffbeb;border-color:#fcd34d}
.problem-card.baixa{background:#eff6ff;border-color:#93c5fd}
.problem-header{display:flex;align-items:center;margin-bottom:8px}
.problem-icon{margin-right:8px;font-size:16px}
.problem-symptom{font-size:14px;font-weight:600;color:#1f2937}
.problem-urgency{font-size:10px;padding:2px 6px;border-radius:10px;margin-left:8px}
.urgency-alta{background:#fecaca;color:#7f1d1d}
.urgency-media{background:#fed7aa;color:#9a3412}
.urgency-baixa{background:#dbeafe;color:#1e40af}
.problem-desc{font-size:12px;color:#6b7280;margin-bottom:8px}
.problem-solutions{list-style:none;padding:0;margin:0}
.problem-solutions li{font-size:11px;margin-bottom:3px;padding-left:12px;position:relative}
.problem-solutions li:before{content:"→";position:absolute;left:0;font-weight:bold;color:#dc2626}

/* ALERTAS DE SEGURANÇA MOTOCICLETAS */
.motorcycle-alerts{margin:20px 0}
.motorcycle-alert{border-radius:8px;padding:16px;margin:16px 0;border-left:4px solid}
.motorcycle-alert.critico{background:#fef2f2;border-color:#dc2626;color:#7f1d1d}
.motorcycle-alert.warning{background:#fffbeb;border-color:#f59e0b;color:#92400e}
.motorcycle-alert.info{background:#eff6ff;border-color:#3b82f6;color:#1e3a8a}
.alert-title{font-weight:600;margin-bottom:8px;font-size:14px;display:flex;align-items:center}
.alert-desc{font-size:13px;margin-bottom:8px}
.alert-consequences{list-style:none;padding:0;margin:8px 0}
.alert-consequences li{font-size:12px;margin-bottom:4px;padding-left:16px;position:relative}
.alert-consequences li:before{content:"⚠️";position:absolute;left:0}
.alert-actions{list-style:none;padding:0;margin:0}
.alert-actions li{font-size:12px;margin-bottom:4px;padding-left:16px;position:relative}
.alert-actions li:before{content:"✓";position:absolute;left:0;font-weight:bold;color:#16a34a}

/* MANUTENÇÃO PREVENTIVA */
.maintenance-grid{display:grid;grid-template-columns:1fr;gap:16px;margin:20px 0}
.maintenance-card{border:1px solid #e5e7eb;border-radius:8px;padding:16px;background:#f0fdf4;border-color:#86efac}
.maintenance-header{display:flex;align-items:center;margin-bottom:12px}
.maintenance-icon{width:28px;height:28px;border-radius:50%;background:#16a34a;color:white;display:flex;align-items:center;justify-content:center;margin-right:12px;font-size:14px}
.maintenance-title{font-size:14px;font-weight:600;color:#1f2937}
.maintenance-difficulty{font-size:10px;padding:2px 6px;border-radius:10px;margin-left:8px;background:#d1fae5;color:#14532d}
.maintenance-tips{list-style:none;padding:0;margin:8px 0 0}
.maintenance-tips li{font-size:12px;color:#374151;margin-bottom:4px;padding-left:12px;position:relative}
.maintenance-tips li:before{content:"🔧";position:absolute;left:0}

/* FAQ MOTOCICLETAS */
.motorcycle-faq amp-accordion section{border-bottom:1px solid #e5e7eb}
.motorcycle-faq amp-accordion h4{background:#fef2f2;margin:0;padding:16px;font-size:14px;font-weight:600;color:#1f2937;cursor:pointer;border-left:3px solid #dc2626}
.motorcycle-faq amp-accordion h4:hover{background:#fecaca}
.faq-content{padding:16px;font-size:13px;color:#374151;line-height:1.6}

/* CONSIDERAÇÕES FINAIS MOTOCICLETAS */
.motorcycle-final{background:linear-gradient(135deg,#fef2f2,#fecaca);border:1px solid #dc2626;border-radius:12px;padding:24px;margin:24px 0;text-align:center}
.motorcycle-final-title{font-size:18px;font-weight:700;color:#7f1d1d;margin-bottom:16px}
.motorcycle-final-text{font-size:14px;color:#991b1b;line-height:1.7}

/* RESPONSIVIDADE MOTOCICLETAS */
@media (max-width: 480px) {
    .motorcycle-specs{grid-template-columns:1fr}
    .motorcycle-pressure-table{font-size:10px}
    .motorcycle-pressure-table th,.motorcycle-pressure-table td{padding:6px 4px}
    .impact-comparison{grid-template-columns:1fr}
    .alternative-brands{grid-template-columns:1fr}
    .riding-recommendations{grid-template-columns:1fr}
}
</style>
@endsection

@section('content')
@php
// Extração de dados das ViewModels para motocicletas
$vehicleInfo = $article->getData()['vehicle_info'] ?? [];
$vehicleName = $vehicleInfo['full_name'] ?? 'Motocicleta';
$introduction = $article->getData()['introduction'] ?? '';
$tireSpecs = $article->getData()['tire_specifications'] ?? [];
$pressureTable = $article->getData()['pressure_table'] ?? [];
$calibrationProcedure = $article->getData()['calibration_procedure'] ?? [];
$usageRecommendations = $article->getData()['usage_recommendations'] ?? [];
$impactComparison = $article->getData()['impact_comparison'] ?? [];
$alternativeTires = $article->getData()['alternative_tires'] ?? [];
$requiredEquipment = $article->getData()['required_equipment'] ?? [];
$specialCare = $article->getData()['special_care'] ?? [];
$problemSigns = $article->getData()['problem_signs'] ?? [];
$safetyAlerts = $article->getData()['safety_alerts'] ?? [];
$maintenanceTips = $article->getData()['maintenance_tips'] ?? [];
$faq = $article->getData()['faq'] ?? [];
$finalConsiderations = $article->getData()['final_considerations'] ?? '';

// Flags auxiliares para motocicletas
$isSport = $tireSpecs['is_sport'] ?? false;
$enginePower = $tireSpecs['engine_power'] ?? '';
$isPremium = $vehicleInfo['is_premium'] ?? false;
@endphp

<div class="container">
    <!-- CABEÇALHO MOTOCICLETAS -->
    <header>
        <h1>{{ $article->getData()['seo_data']['h1'] ?? "Como Calibrar Pneus da {$vehicleName} – Guia para Motociclistas" }}</h1>
        <p style="color: #6b7280; font-size: 14px; margin-bottom: 20px;">
            🏍️ Guia especializado para motociclistas | Atualizado em: {{ $article->getData()['formated_updated_at'] ?? '14 de agosto de 2025' }}
        </p>
    </header>

    <!-- INTRODUÇÃO -->
    @if(!empty($introduction))
    <div style="margin-bottom: 32px;">
        <p style="font-size: 16px; color: #374151; line-height: 1.7;">
            {!! nl2br(e($introduction)) !!}
        </p>
    </div>
    @endif

    <!-- ESPECIFICAÇÕES TÉCNICAS DOS PNEUS MOTOCICLETAS -->
    @if(!empty($tireSpecs))
    <div class="content-section">
        <div class="section-header">
            <span class="section-icon">🔧</span>
            <span class="section-title">{{ $tireSpecs['title'] ?? 'Especificações Técnicas dos Pneus Originais' }}</span>
        </div>
        <p style="color: #6b7280; margin-bottom: 20px;">{{ $tireSpecs['description'] ?? '' }}</p>
        
        @if(!empty($enginePower))
        <div style="background: #fef2f2; border: 1px solid #fca5a5; border-radius: 8px; padding: 12px; margin-bottom: 20px; text-align: center;">
            <strong style="color: #dc2626;">🏍️ Potência do Motor: {{ $enginePower }}</strong>
        </div>
        @endif
        
        <div class="motorcycle-specs">
            <!-- Pneu Dianteiro -->
            @if(!empty($tireSpecs['front_tire']))
            <div class="motorcycle-spec front">
                @if(!empty($enginePower))
                <div class="power-indicator">{{ $enginePower }}</div>
                @endif
                <div class="tire-type front">🔸 Pneu Dianteiro</div>
                @foreach([
                    'size' => 'Medida',
                    'type' => 'Tipo',
                    'brand' => 'Marca Original', 
                    'load_index' => 'Índice Carga',
                    'speed_rating' => 'Índice Velocidade',
                    'recommended_pressure' => 'Pressão Recomendada',
                    'max_pressure' => 'Pressão Máxima'
                ] as $key => $label)
                    @if(!empty($tireSpecs['front_tire'][$key]))
                    <div class="spec-row">
                        <span class="spec-label">{{ $label }}:</span>
                        <span class="spec-value">{{ $tireSpecs['front_tire'][$key] }}</span>
                    </div>
                    @endif
                @endforeach
                @if(!empty($tireSpecs['front_tire']['characteristics']))
                <p style="font-size: 11px; color: #6b7280; margin-top: 8px; font-style: italic;">
                    {{ $tireSpecs['front_tire']['characteristics'] }}
                </p>
                @endif
            </div>
            @endif

            <!-- Pneu Traseiro -->
            @if(!empty($tireSpecs['rear_tire']))
            <div class="motorcycle-spec rear">
                <div class="tire-type rear">🔻 Pneu Traseiro</div>
                @foreach([
                    'size' => 'Medida',
                    'type' => 'Tipo',
                    'brand' => 'Marca Original',
                    'load_index' => 'Índice Carga', 
                    'speed_rating' => 'Índice Velocidade',
                    'recommended_pressure' => 'Pressão Recomendada',
                    'max_pressure' => 'Pressão Máxima'
                ] as $key => $label)
                    @if(!empty($tireSpecs['rear_tire'][$key]))
                    <div class="spec-row">
                        <span class="spec-label">{{ $label }}:</span>
                        <span class="spec-value">{{ $tireSpecs['rear_tire'][$key] }}</span>
                    </div>
                    @endif
                @endforeach
                @if(!empty($tireSpecs['rear_tire']['characteristics']))
                <p style="font-size: 11px; color: #6b7280; margin-top: 8px; font-style: italic;">
                    {{ $tireSpecs['rear_tire']['characteristics'] }}
                </p>
                @endif
            </div>
            @endif
        </div>

        @if(!empty($tireSpecs['note']))
        <div class="motorcycle-alert info">
            <div class="alert-title">📋 Observação Técnica</div>
            <div class="alert-desc">{{ $tireSpecs['note'] }}</div>
        </div>
        @endif

        @if($isSport)
        <div class="motorcycle-alert critico">
            <div class="alert-title">🏁 Motocicleta Esportiva - Atenção Especial</div>
            <div class="alert-desc">
                Para motocicletas esportivas, use APENAS pneus com especificação Z ou W. 
                Pressão inadequada pode causar perda de aderência a altas velocidades.
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- TABELA DE PRESSÕES POR CONDIÇÃO -->
    @if(!empty($pressureTable['conditions']))
    <div class="content-section">
        <div class="section-header">
            <span class="section-icon">📊</span>
            <span class="section-title">{{ $pressureTable['title'] ?? 'Tabela de Pressões por Condição de Uso' }}</span>
        </div>
        <p style="color: #6b7280; margin-bottom: 20px;">{{ $pressureTable['description'] ?? '' }}</p>

        <table class="motorcycle-pressure-table">
            <thead>
                <tr>
                    <th>Situação de Pilotagem</th>
                    <th>Ocupação</th>
                    <th>Bagagem</th>
                    <th>Dianteiro</th>
                    <th>Traseiro</th>
                    <th>Observação</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pressureTable['conditions'] as $condition)
                @php
                $rowClass = '';
                $situation = strtolower($condition['situation'] ?? '');
                if (str_contains($situation, 'urbano')) $rowClass = 'urban';
                elseif (str_contains($situation, 'esportiv')) $rowClass = 'sport';
                elseif (str_contains($situation, 'viagem')) $rowClass = 'touring';
                @endphp
                <tr class="{{ $rowClass }}">
                    <td style="font-weight: 600;">
                        @switch($rowClass)
                            @case('urban')
                                🏙️ {{ $condition['situation'] ?? '' }}
                                @break
                            @case('sport')
                                🏁 {{ $condition['situation'] ?? '' }}
                                @break
                            @case('touring')
                                🛣️ {{ $condition['situation'] ?? '' }}
                                @break
                            @default
                                🏍️ {{ $condition['situation'] ?? '' }}
                        @endswitch
                    </td>
                    <td>{{ $condition['occupants'] ?? '' }}</td>
                    <td>{{ $condition['luggage'] ?? '' }}</td>
                    <td><span class="pressure-value front">{{ $condition['front_pressure'] ?? '' }}</span></td>
                    <td><span class="pressure-value rear">{{ $condition['rear_pressure'] ?? '' }}</span></td>
                    <td style="font-size: 11px;">{{ $condition['note'] ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- PROCEDIMENTO COMPLETO DE CALIBRAGEM MOTOCICLETAS -->
    @if(!empty($calibrationProcedure['steps']))
    <div class="content-section">
        <div class="section-header">
            <span class="section-icon">📋</span>
            <span class="section-title">{{ $calibrationProcedure['title'] ?? 'Procedimento Completo de Calibragem' }}</span>
        </div>
        <p style="color: #6b7280; margin-bottom: 20px;">{{ $calibrationProcedure['description'] ?? '' }}</p>

        <div class="motorcycle-procedure">
            @foreach($calibrationProcedure['steps'] as $step)
            <div class="motorcycle-step">
                <div class="motorcycle-step-number">{{ $step['number'] ?? '1' }}</div>
                <div class="motorcycle-step-content">
                    <h4 class="motorcycle-step-title">{{ $step['title'] ?? 'Passo' }}</h4>
                    <p class="motorcycle-step-desc">{{ $step['description'] ?? '' }}</p>
                    @if(!empty($step['safety_note']))
                    <div class="motorcycle-safety-note">{{ $step['safety_note'] }}</div>
                    @endif
                    @if(!empty($step['tips']) && is_array($step['tips']))
                    <ul class="motorcycle-tips">
                        @foreach($step['tips'] as $tip)
                        <li>{{ $tip }}</li>
                        @endforeach
                    </ul>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- RECOMENDAÇÕES POR ESTILO DE PILOTAGEM -->
    @if(!empty($usageRecommendations['categories']))
    <div class="content-section">
        <div class="section-header">
            <span class="section-icon">🎯</span>
            <span class="section-title">{{ $usageRecommendations['title'] ?? 'Recomendações por Estilo de Pilotagem' }}</span>
        </div>
        <p style="color: #6b7280; margin-bottom: 20px;">{{ $usageRecommendations['description'] ?? '' }}</p>

        <div class="riding-recommendations">
            @foreach($usageRecommendations['categories'] as $usage)
            @php
            $ridingClass = 'urban';
            $category = strtolower($usage['category'] ?? '');
            if (str_contains($category, 'rodoviário') || str_contains($category, 'estrada')) $ridingClass = 'highway';
            if (str_contains($category, 'esportiv')) $ridingClass = 'sport';
            if (str_contains($category, 'track')) $ridingClass = 'track';
            
            $safetyLevel = $usage['safety_level'] ?? 'baixo';
            @endphp
            <div class="riding-card {{ $ridingClass }}">
                <div class="riding-header">
                    <div class="riding-title">
                        @switch($ridingClass)
                            @case('urban')
                                🏙️ {{ $usage['category'] }}
                                @break
                            @case('highway')
                                🛣️ {{ $usage['category'] }}
                                @break
                            @case('sport')
                                🏁 {{ $usage['category'] }}
                                @break
                            @case('track')
                                🏁 {{ $usage['category'] }}
                                @break
                            @default
                                🏍️ {{ $usage['category'] }}
                        @endswitch
                    </div>
                    <div class="riding-pressure">{{ $usage['recommended_pressure'] ?? '' }}</div>
                </div>
                <div class="safety-level {{ $safetyLevel }}">
                    Risco: {{ ucfirst($safetyLevel) }}
                </div>
                <p class="riding-desc">{{ $usage['description'] ?? '' }}</p>
                @if(!empty($usage['technical_tip']))
                <div class="riding-tip">🔧 {{ $usage['technical_tip'] }}</div>
                @endif
                @if(!empty($usage['verification_frequency']))
                <p class="riding-frequency">Verificar: {{ $usage['verification_frequency'] }}</p>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- COMPARATIVO DE IMPACTOS NA PILOTAGEM -->
    @if(!empty($impactComparison['scenarios']))
    <div class="content-section">
        <div class="section-header">
            <span class="section-icon">⚖️</span>
            <span class="section-title">{{ $impactComparison['title'] ?? 'Comparativo de Impactos na Pilotagem' }}</span>
        </div>
        <p style="color: #6b7280; margin-bottom: 20px;">{{ $impactComparison['description'] ?? '' }}</p>

        <div class="impact-comparison">
            @foreach($impactComparison['scenarios'] as $type => $scenario)
                @if(!empty($scenario))
                <div class="impact-scenario {{ str_replace('_pressure', '', $type) }}">
                    <div class="impact-icon">{{ $scenario['icon'] ?? '📊' }}</div>
                    <h4 class="impact-scenario-title">{{ $scenario['title'] ?? ucfirst($type) }}</h4>
                    <p style="font-size: 12px; margin-bottom: 8px;">{{ $scenario['description'] ?? '' }}</p>
                    @if(!empty($scenario['effects']))
                    <ul class="impact-effects">
                        @foreach($scenario['effects'] as $effect)
                        <li>{{ $effect }}</li>
                        @endforeach
                    </ul>
                    @endif
                </div>
                @endif
            @endforeach
        </div>

        @if(!empty($impactComparison['safety_warnings']))
        <div class="motorcycle-alert critico">
            <div class="alert-title">⚠️ Avisos Críticos de Segurança</div>
            <ul class="alert-consequences">
                @foreach($impactComparison['safety_warnings'] as $warning)
                <li>{{ $warning }}</li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
    @endif

    <!-- PNEUS ALTERNATIVOS PARA MOTOCICLETAS -->
    @if(!empty($alternativeTires['categories']))
    <div class="content-section">
        <div class="section-header">
            <span class="section-icon">🔄</span>
            <span class="section-title">{{ $alternativeTires['title'] ?? 'Pneus Alternativos para Motocicletas' }}</span>
        </div>
        <p style="color: #6b7280; margin-bottom: 20px;">{{ $alternativeTires['description'] ?? '' }}</p>

        <div class="motorcycle-alternatives">
            @foreach($alternativeTires['categories'] as $category => $brands)
                @if(!empty($brands) && is_array($brands))
                <div class="alternative-category {{ $category }}">
                    <div class="alternative-title">
                        @switch($category)
                            @case('sport')
                                🏁 Esportivos
                                @break
                            @case('touring')
                                🛣️ Touring
                                @break
                            @case('street')
                                🏙️ Street
                                @break
                            @case('premium')
                                👑 Premium
                                @break
                            @case('budget')
                                💰 Econômicos
                                @break
                            @case('seasonal')
                                🌦️ Sazonais
                                @break
                            @default
                                {{ ucfirst($category) }}
                        @endswitch
                    </div>
                    <div class="alternative-brands">
                        @foreach($brands as $brand)
                        <div class="alternative-brand">{{ $brand }}</div>
                        @endforeach
                    </div>
                </div>
                @endif
            @endforeach
        </div>

        @if(!empty($alternativeTires['note']))
        <div class="motorcycle-alert info">
            <div class="alert-title">📝 Observação Importante</div>
            <div class="alert-desc">{{ $alternativeTires['note'] }}</div>
        </div>
        @endif

        @if(!empty($alternativeTires['compatibility_warning']))
        <div class="motorcycle-alert warning">
            <div class="alert-title">⚠️ Aviso de Compatibilidade</div>
            <div class="alert-desc">{{ $alternativeTires['compatibility_warning'] }}</div>
        </div>
        @endif
    </div>
    @endif

    <!-- EQUIPAMENTOS ESPECÍFICOS PARA MOTOCICLETAS -->
    @if(!empty($requiredEquipment['items']))
    <div class="content-section">
        <div class="section-header">
            <span class="section-icon">🧰</span>
            <span class="section-title">{{ $requiredEquipment['title'] ?? 'Equipamentos Específicos para Motocicletas' }}</span>
        </div>
        <p style="color: #6b7280; margin-bottom: 20px;">{{ $requiredEquipment['description'] ?? '' }}</p>

        <div class="motorcycle-equipment">
            @foreach($requiredEquipment['items'] as $equipment)
            <div class="equipment-item {{ $equipment['motorcycle_specific'] ? 'motorcycle-specific' : '' }}">
                <div class="equipment-header">
                    <div class="equipment-name">
                        @php
                        $item = strtolower($equipment['name'] ?? '');
                        $icon = '🔧';
                        if (str_contains($item, 'calibrador') || str_contains($item, 'medidor')) $icon = '📏';
                        if (str_contains($item, 'compressor')) $icon = '💨';
                        if (str_contains($item, 'cavalete')) $icon = '🏍️';
                        if (str_contains($item, 'luva')) $icon = '🧤';
                        @endphp
                        {{ $icon }} {{ $equipment['name'] ?? '' }}
                        @if($equipment['motorcycle_specific'] ?? false)
                        <span class="motorcycle-specific-badge">MOTO</span>
                        @endif
                    </div>
                    @if(!empty($equipment['importance']))
                    @php
                    $importanceClass = 'importance-optional';
                    $importance = strtolower($equipment['importance']);
                    if (str_contains($importance, 'essencial')) $importanceClass = 'importance-essential';
                    elseif (str_contains($importance, 'importante')) $importanceClass = 'importance-important';
                    elseif (str_contains($importance, 'recomendado')) $importanceClass = 'importance-recommended';
                    @endphp
                    <span class="equipment-importance {{ $importanceClass }}">{{ $equipment['importance'] }}</span>
                    @endif
                </div>
                @if(!empty($equipment['description']))
                <p class="equipment-desc">{{ $equipment['description'] }}</p>
                @endif
                @if(!empty($equipment['estimated_price']))
                <p class="equipment-price">Preço estimado: {{ $equipment['estimated_price'] }}</p>
                @endif
                @if(!empty($equipment['tips']) && is_array($equipment['tips']))
                <ul style="list-style: none; padding: 0; margin: 8px 0 0;">
                    @foreach($equipment['tips'] as $tip)
                    <li style="font-size: 11px; color: #6b7280; margin-bottom: 2px; padding-left: 12px; position: relative;">
                        <span style="position: absolute; left: -0.7em;">🏍️</span>{{ $tip }}
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- CUIDADOS ESPECÍFICOS PARA MOTOCICLETAS -->
    @if(!empty($specialCare['categories']))
    <div class="content-section">
        <div class="section-header">
            <span class="section-icon">🛡️</span>
            <span class="section-title">{{ $specialCare['title'] ?? 'Cuidados Específicos para Motocicletas' }}</span>
        </div>
        <p style="color: #6b7280; margin-bottom: 20px;">{{ $specialCare['description'] ?? '' }}</p>

        <div class="special-care">
            @foreach($specialCare['categories'] as $care)
            <div class="care-card">
                <div class="care-header">
                    <div class="care-icon">🔧</div>
                    <div class="care-title">{{ $care['title'] ?? $care['category'] ?? '' }}</div>
                    @if(!empty($care['frequency']))
                    <div class="care-frequency">{{ $care['frequency'] }}</div>
                    @endif
                </div>
                <p style="font-size: 13px; color: #6b7280; margin-bottom: 8px;">{{ $care['description'] ?? '' }}</p>
                @if(!empty($care['care_items']) && is_array($care['care_items']))
                <ul class="care-items">
                    @foreach($care['care_items'] as $item)
                    <li>{{ $item }}</li>
                    @endforeach
                </ul>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- SINAIS DE PROBLEMAS NOS PNEUS -->
    @if(!empty($problemSigns['warning_signs']))
    <div class="content-section">
        <div class="section-header">
            <span class="section-icon">🚨</span>
            <span class="section-title">{{ $problemSigns['title'] ?? 'Sinais de Problemas nos Pneus' }}</span>
        </div>
        <p style="color: #6b7280; margin-bottom: 20px;">{{ $problemSigns['description'] ?? '' }}</p>

        <div class="problem-signs">
            @foreach($problemSigns['warning_signs'] as $sign)
            <div class="problem-card {{ $sign['urgency'] ?? 'media' }}">
                <div class="problem-header">
                    <span class="problem-icon">
                        @switch($sign['urgency'] ?? 'media')
                            @case('alta')
                                🚨
                                @break
                            @case('media')
                                ⚠️
                                @break
                            @case('baixa')
                                ℹ️
                                @break
                            @default
                                ⚠️
                        @endswitch
                    </span>
                    <span class="problem-symptom">{{ $sign['symptom'] ?? '' }}</span>
                    <span class="problem-urgency urgency-{{ $sign['urgency'] ?? 'media' }}">
                        {{ ucfirst($sign['urgency'] ?? 'Média') }}
                    </span>
                </div>
                <p class="problem-desc">{{ $sign['description'] ?? '' }}</p>
                @if(!empty($sign['solutions']) && is_array($sign['solutions']))
                <ul class="problem-solutions">
                    @foreach($sign['solutions'] as $solution)
                    <li>{{ $solution }}</li>
                    @endforeach
                </ul>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- ALERTAS CRÍTICOS DE SEGURANÇA PARA MOTOCICLISTAS -->
    @if(!empty($safetyAlerts['alerts']))
    <div class="content-section">
        <div class="section-header">
            <span class="section-icon">⚠️</span>
            <span class="section-title">{{ $safetyAlerts['title'] ?? 'Alertas Críticos de Segurança' }}</span>
        </div>
        <p style="color: #6b7280; margin-bottom: 20px;">{{ $safetyAlerts['description'] ?? '' }}</p>

        <div class="motorcycle-alerts">
            @foreach($safetyAlerts['alerts'] as $alert)
            <div class="motorcycle-alert {{ $alert['type'] ?? 'warning' }}">
                <div class="alert-title">
                    @switch($alert['type'] ?? 'warning')
                        @case('critico')
                        @case('critical')
                            🚨 {{ $alert['title'] ?? 'Alerta Crítico' }}
                            @break
                        @case('warning')
                        @case('aviso')
                            ⚠️ {{ $alert['title'] ?? 'Atenção' }}
                            @break
                        @default
                            ℹ️ {{ $alert['title'] ?? 'Informação' }}
                    @endswitch
                </div>
                <div class="alert-desc">{{ $alert['description'] ?? '' }}</div>
                @if(!empty($alert['consequences']))
                <p style="font-weight: 600; margin: 8px 0 4px; font-size: 12px;">Consequências para motociclistas:</p>
                <ul class="alert-consequences">
                    @foreach($alert['consequences'] as $consequence)
                    <li>{{ $consequence }}</li>
                    @endforeach
                </ul>
                @endif
                @if(!empty($alert['actions']))
                <p style="font-weight: 600; margin: 8px 0 4px; font-size: 12px;">Ações necessárias:</p>
                <ul class="alert-actions">
                    @foreach($alert['actions'] as $action)
                    <li>{{ $action }}</li>
                    @endforeach
                </ul>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- MANUTENÇÃO PREVENTIVA DOS PNEUS -->
    @if(!empty($maintenanceTips['categories']))
    <div class="content-section">
        <div class="section-header">
            <span class="section-icon">🔧</span>
            <span class="section-title">{{ $maintenanceTips['title'] ?? 'Manutenção Preventiva dos Pneus' }}</span>
        </div>
        <p style="color: #6b7280; margin-bottom: 20px;">{{ $maintenanceTips['description'] ?? '' }}</p>

        <div class="maintenance-grid">
            @foreach($maintenanceTips['categories'] as $tip)
            <div class="maintenance-card">
                <div class="maintenance-header">
                    <div class="maintenance-icon">🔧</div>
                    <div class="maintenance-title">{{ $tip['title'] ?? $tip['category'] ?? '' }}</div>
                    @if(!empty($tip['difficulty']))
                    <div class="maintenance-difficulty">{{ ucfirst($tip['difficulty']) }}</div>
                    @endif
                </div>
                <p style="font-size: 13px; color: #6b7280; margin-bottom: 8px;">{{ $tip['description'] ?? '' }}</p>
                @if(!empty($tip['frequency']))
                <p style="font-size: 11px; color: #16a34a; font-weight: 600; margin-bottom: 8px;">
                    Frequência: {{ $tip['frequency'] }}
                </p>
                @endif
                @if(!empty($tip['tips']) && is_array($tip['tips']))
                <ul class="maintenance-tips">
                    @foreach($tip['tips'] as $tipItem)
                    <li>{{ $tipItem }}</li>
                    @endforeach
                </ul>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- FAQ COMPLETO PARA MOTOCICLETAS -->
    @if(!empty($faq))
    <div class="content-section motorcycle-faq">
        <div class="section-header">
            <span class="section-icon">❓</span>
            <span class="section-title">Perguntas Frequentes sobre {{ $vehicleName }}</span>
        </div>
        <amp-accordion expand-single-section>
            @foreach($faq as $pergunta)
            <section>
                <h4>🏍️ {{ $pergunta['pergunta'] ?? $pergunta['question'] ?? '' }}</h4>
                <div class="faq-content">
                    <p>{{ $pergunta['resposta'] ?? $pergunta['answer'] ?? '' }}</p>
                </div>
            </section>
            @endforeach
        </amp-accordion>
    </div>
    @endif

    <!-- CONSIDERAÇÕES FINAIS PARA MOTOCICLETAS -->
    @if(!empty($finalConsiderations))
    <div class="motorcycle-final">
        <div class="motorcycle-final-title">🏍️ Considerações Finais para Motociclistas</div>
        <div class="motorcycle-final-text">
            {!! nl2br(e($finalConsiderations)) !!}
        </div>
    </div>
    @endif

    <!-- INFORMAÇÕES DA MOTOCICLETA -->
    <div class="content-section">
        <div class="section-header">
            <span class="section-icon">📋</span>
            <span class="section-title">Resumo das Informações</span>
        </div>
        <div style="background: #fef2f2; padding: 16px; border-radius: 8px; border: 1px solid #fca5a5;">
            <p style="margin: 0; font-size: 13px; color: #374151;">
                <strong>Motocicleta:</strong> {{ $vehicleName }}<br>
                @if(!empty($vehicleInfo['segment']))
                <strong>Categoria:</strong> {{ $vehicleInfo['segment'] }}<br>
                @endif
                @if(!empty($vehicleInfo['fuel_type']))
                <strong>Combustível:</strong> {{ $vehicleInfo['fuel_type'] }}<br>
                @endif
                @if(!empty($enginePower))
                <strong>Potência:</strong> {{ $enginePower }}<br>
                @endif
                <strong>Tipo:</strong> {{ $isSport ? 'Esportiva' : 'Street/Touring' }}<br>
                <strong>Última atualização:</strong> {{ $article->getData()['formated_updated_at'] ?? '14 de agosto de 2025' }}
            </p>
        </div>
    </div>

    <!-- FOOTER INFORMATIVO MOTOCICLETAS -->
    <footer style="text-align: center; padding: 20px 0; border-top: 1px solid #e5e7eb; margin-top: 40px;">
        <p style="font-size: 12px; color: #6b7280; margin: 0;">
            🏍️ Guia técnico especializado baseado nas especificações oficiais do fabricante.<br>
            Para motocicletas, sempre priorize a segurança e consulte profissionais qualificados.
        </p>
        <p style="font-size: 11px; color: #9ca3af; margin: 8px 0 0;">
            ⚠️ Pilotagem responsável salva vidas. Use sempre equipamentos de proteção.
        </p>
    </footer>
</div>
@endsection