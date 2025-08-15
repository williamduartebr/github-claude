{{--
Template AMP: tire_pressure_guide_car.blade.php
Guia completo de calibragem para carros - Versão AMP REDESENHADA --force
Design moderno e atraente com fontes maiores e melhor usabilidade
--}}

@extends('auto-info-center::layouts.amp')

@section('amp-head')
<script async custom-element="amp-accordion" src="https://cdn.ampproject.org/v0/amp-accordion-0.1.js"></script>
<style amp-custom>
/* CSS MODERNO E ATRAENTE PARA GUIA DE CALIBRAGEM CARROS */
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;line-height:1.6;color:#1f2937;margin:0;background:linear-gradient(135deg,#f8fafc,#e2e8f0)}
.container{max-width:900px;margin:0 auto;padding:20px}

/* TYPOGRAPHY MELHORADA */
h1{font-size:32px;font-weight:800;margin-bottom:20px;color:#0E368A;line-height:1.2;text-shadow:0 2px 4px rgba(14,54,138,0.1)}
h2{font-size:26px;font-weight:700;margin:40px 0 20px;padding:16px 0 12px;border-bottom:3px solid #0E368A;color:#0E368A;background:linear-gradient(135deg,#f0f9ff,#e0f2fe);border-radius:8px 8px 0 0;padding-left:16px}
h3{font-size:22px;font-weight:600;margin:24px 0 16px;color:#1e40af}
h4{font-size:18px;font-weight:600;margin:20px 0 12px;color:#1f2937}
p{margin-bottom:18px;line-height:1.7;font-size:16px}

/* SEÇÕES DE CONTEÚDO REDESENHADAS */
.content-section{background:linear-gradient(135deg,#ffffff,#f8fafc);border:2px solid #e0f2fe;border-radius:16px;padding:28px;margin:32px 0;box-shadow:0 8px 25px rgba(14,54,138,0.1);position:relative;overflow:hidden}
.content-section:before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,#0E368A,#1e40af,#3b82f6)}
.section-header{display:flex;align-items:center;margin-bottom:24px;padding-bottom:16px;border-bottom:2px solid #e0f2fe}
.section-icon{font-size:28px;margin-right:16px;padding:12px;background:linear-gradient(135deg,#0E368A,#1e40af);color:white;border-radius:12px;box-shadow:0 4px 12px rgba(14,54,138,0.3)}
.section-title{font-size:22px;font-weight:700;color:#0E368A;letter-spacing:-0.5px}

/* ESPECIFICAÇÕES TÉCNICAS CARROS */
.car-specs{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin:24px 0}
.car-spec{background:linear-gradient(135deg,#eff6ff,#dbeafe);border:2px solid #93c5fd;border-radius:16px;padding:20px;position:relative;transition:transform 0.3s ease}
.car-spec:hover{transform:translateY(-2px)}
.car-spec.front{border-color:#f59e0b;background:linear-gradient(135deg,#fffbeb,#fef3c7)}
.car-spec.rear{border-color:#0E368A;background:linear-gradient(135deg,#eff6ff,#dbeafe)}
.tire-type{font-size:16px;font-weight:700;color:#0E368A;margin-bottom:16px;text-transform:uppercase;display:flex;align-items:center;letter-spacing:1px}
.tire-type.front{color:#d97706}
.tire-type.rear{color:#0E368A}
.spec-row{display:flex;justify-content:space-between;margin-bottom:12px;padding:8px 0;border-bottom:1px solid rgba(14,54,138,0.1)}
.spec-row:last-child{border-bottom:none;margin-bottom:0}
.spec-label{font-size:15px;color:#6b7280;font-weight:500}
.spec-value{font-size:15px;font-weight:700;color:#1f2937;background:rgba(255,255,255,0.8);padding:4px 8px;border-radius:6px}
.feature-badge{position:absolute;top:12px;right:12px;background:#0E368A;color:white;padding:6px 12px;border-radius:20px;font-size:12px;font-weight:600}

/* TABELA DE PRESSÕES CARROS */
.car-pressure-table{width:100%;border-collapse:collapse;margin:24px 0;border-radius:12px;overflow:hidden;box-shadow:0 8px 25px rgba(14,54,138,0.1)}
.car-pressure-table th{background:linear-gradient(135deg,#0E368A,#1e40af);color:white;padding:16px 12px;text-align:center;font-size:14px;font-weight:700;letter-spacing:0.5px}
.car-pressure-table td{padding:14px 12px;border-bottom:1px solid #f1f5f9;text-align:center;font-size:14px;background:#fff}
.car-pressure-table tr:nth-child(even) td{background:#f8fafc}
.car-pressure-table tr.normal{background:linear-gradient(135deg,#f0fdf4,#dcfce7)}
.car-pressure-table tr.load{background:linear-gradient(135deg,#eff6ff,#dbeafe)}
.car-pressure-table tr.sport{background:linear-gradient(135deg,#fef2f2,#fecaca)}
.pressure-value{background:linear-gradient(135deg,#0E368A,#1e40af);color:white;padding:6px 12px;border-radius:20px;font-weight:700;font-size:13px;display:inline-block;box-shadow:0 2px 8px rgba(14,54,138,0.3)}
.pressure-value.front{background:linear-gradient(135deg,#f59e0b,#d97706)}
.pressure-value.rear{background:linear-gradient(135deg,#0E368A,#1e40af)}

/* PROCEDIMENTO CARROS */
.car-procedure{margin:24px 0}
.car-step{display:flex;margin-bottom:28px;align-items:flex-start;background:linear-gradient(135deg,#ffffff,#f8fafc);border:2px solid #e0f2fe;border-radius:16px;padding:20px;transition:transform 0.3s ease}
.car-step:hover{transform:translateY(-2px)}
.car-step-number{flex-shrink:0;width:48px;height:48px;background:linear-gradient(135deg,#0E368A,#1e40af);color:white;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:18px;margin-right:20px;box-shadow:0 4px 12px rgba(14,54,138,0.3)}
.car-step-content{flex:1}
.car-step-title{font-size:18px;font-weight:700;color:#0E368A;margin-bottom:12px}
.car-step-desc{font-size:15px;color:#4b5563;margin-bottom:16px;line-height:1.6}
.car-tips{list-style:none;padding:0;margin:0}
.car-tips li{background:linear-gradient(135deg,#f0f9ff,#e0f2fe);border-left:4px solid #0EA5E9;padding:12px 16px;margin-bottom:8px;font-size:14px;border-radius:8px}
.car-tips li:before{content:"💡";margin-right:10px;font-size:16px}

/* SISTEMA TPMS */
.tpms-box{background:linear-gradient(135deg,#eff6ff,#dbeafe);border:3px solid #3b82f6;border-radius:20px;padding:28px;margin:28px 0;text-align:center;position:relative;overflow:hidden}
.tpms-box:before{content:'';position:absolute;top:0;left:0;right:0;height:6px;background:linear-gradient(90deg,#3b82f6,#1d4ed8,#1e40af)}
.tpms-title{font-size:22px;font-weight:800;color:#1e40af;margin-bottom:16px}
.tpms-benefits{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:20px}
.tpms-benefit{background:rgba(255,255,255,0.9);padding:16px;border-radius:12px;text-align:center;border:2px solid #93c5fd}
.tpms-benefit-title{font-size:14px;font-weight:700;color:#1e40af;margin-bottom:8px}
.tpms-benefit-desc{font-size:13px;color:#6b7280}

/* IMPACTOS DA CALIBRAGEM */
.impact-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;margin:28px 0}
.impact-card{border:2px solid #e5e7eb;border-radius:16px;padding:20px;background:linear-gradient(135deg,#ffffff,#f8fafc);transition:transform 0.3s ease,box-shadow 0.3s ease}
.impact-card:hover{transform:translateY(-4px);box-shadow:0 12px 30px rgba(0,0,0,0.1)}
.impact-header{display:flex;align-items:center;margin-bottom:16px}
.impact-icon{width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin-right:16px;font-size:18px;box-shadow:0 4px 12px rgba(0,0,0,0.2)}
.impact-icon.economy{background:linear-gradient(135deg,#dcfce7,#bbf7d0);color:#16a34a}
.impact-icon.safety{background:linear-gradient(135deg,#fef2f2,#fecaca);color:#dc2626}
.impact-icon.comfort{background:linear-gradient(135deg,#eff6ff,#dbeafe);color:#2563eb}
.impact-icon.durability{background:linear-gradient(135deg,#fef3c7,#fed7aa);color:#d97706}
.impact-title{font-size:16px;font-weight:700;color:#1f2937}
.impact-desc{font-size:14px;color:#6b7280;margin-bottom:16px;line-height:1.6}
.impact-benefits{list-style:none;padding:0;margin:0}
.impact-benefits li{font-size:13px;color:#374151;margin-bottom:6px;padding-left:20px;position:relative}
.impact-benefits li:before{content:"✓";position:absolute;left:0;color:#16a34a;font-weight:bold;font-size:14px}

/* RECOMENDAÇÕES DE USO CARROS */
.usage-grid{display:grid;grid-template-columns:1fr;gap:20px;margin:28px 0}
.usage-card{border:2px solid #e5e7eb;border-radius:16px;padding:20px;position:relative;overflow:hidden;transition:transform 0.3s ease}
.usage-card:hover{transform:translateY(-2px)}
.usage-card.urban{background:linear-gradient(135deg,#f0fdf4,#dcfce7);border-color:#22c55e}
.usage-card.highway{background:linear-gradient(135deg,#eff6ff,#dbeafe);border-color:#3b82f6}
.usage-card.sport{background:linear-gradient(135deg,#fef2f2,#fecaca);border-color:#ef4444}
.usage-card.eco{background:linear-gradient(135deg,#f0fdf4,#dcfce7);border-color:#22c55e}
.usage-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px}
.usage-title{font-size:16px;font-weight:700;color:#1f2937;display:flex;align-items:center}
.usage-pressure{background:rgba(255,255,255,0.95);color:#1f2937;padding:6px 14px;border-radius:20px;font-weight:800;font-size:14px;box-shadow:0 2px 8px rgba(0,0,0,0.1)}
.usage-desc{font-size:14px;color:#374151;margin-bottom:12px;line-height:1.6}
.usage-tip{background:rgba(255,255,255,0.8);padding:12px 16px;border-radius:10px;font-size:13px;color:#1f2937;border-left:4px solid #0EA5E9}
.usage-frequency{font-size:12px;color:#6b7280;margin-top:12px;font-style:italic;font-weight:500}

/* EQUIPAMENTOS CARROS */
.equipment-grid{display:grid;grid-template-columns:1fr;gap:16px;margin:28px 0}
.equipment-item{border:2px solid #e5e7eb;border-radius:16px;padding:20px;background:linear-gradient(135deg,#ffffff,#f8fafc);transition:transform 0.3s ease}
.equipment-item:hover{transform:translateY(-2px)}
.equipment-header{display:flex;align-items:center;justify-content:between;margin-bottom:12px}
.equipment-name{font-size:16px;font-weight:700;color:#1f2937;flex:1}
.equipment-importance{font-size:12px;padding:4px 10px;border-radius:15px;font-weight:700;letter-spacing:0.5px}
.importance-essential{background:#fecaca;color:#7f1d1d}
.importance-important{background:#fed7aa;color:#9a3412}
.importance-recommended{background:#d1fae5;color:#14532d}
.importance-optional{background:#e5e7eb;color:#374151}
.equipment-desc{font-size:14px;color:#6b7280;margin-bottom:12px;line-height:1.6}
.equipment-price{font-size:13px;color:#059669;font-weight:600}

/* ALERTAS DE SEGURANÇA CARROS */
.car-alerts{margin:28px 0}
.car-alert{border-radius:16px;padding:20px;margin:20px 0;border-left:6px solid;position:relative;overflow:hidden}
.car-alert.critico{background:linear-gradient(135deg,#fef2f2,#fecaca);border-color:#dc2626;color:#7f1d1d}
.car-alert.warning{background:linear-gradient(135deg,#fffbeb,#fef3c7);border-color:#f59e0b;color:#92400e}
.car-alert.info{background:linear-gradient(135deg,#eff6ff,#dbeafe);border-color:#3b82f6;color:#1e3a8a}
.alert-title{font-weight:700;margin-bottom:12px;font-size:16px;display:flex;align-items:center}
.alert-desc{font-size:14px;margin-bottom:12px;line-height:1.6}
.alert-actions{list-style:none;padding:0;margin:12px 0 0}
.alert-actions li{font-size:13px;margin-bottom:6px;padding-left:20px;position:relative}
.alert-actions li:before{content:"→";position:absolute;left:0;font-weight:bold}

/* PNEUS ALTERNATIVOS CARROS */
.alternatives-grid{display:grid;grid-template-columns:1fr;gap:20px;margin:28px 0}
.alternative-category{border:2px solid #e5e7eb;border-radius:16px;padding:20px;background:linear-gradient(135deg,#ffffff,#f8fafc)}
.alternative-title{font-size:16px;font-weight:700;color:#1f2937;margin-bottom:16px;text-transform:uppercase;letter-spacing:1px;display:flex;align-items:center}
.alternative-brands{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px}
.alternative-brand{background:linear-gradient(135deg,#f1f5f9,#e2e8f0);padding:12px 16px;border-radius:10px;text-align:center;font-size:13px;font-weight:600;color:#334155;border:2px solid #cbd5e1;transition:transform 0.2s ease}
.alternative-brand:hover{transform:scale(1.05)}

/* FAQ CARROS */
.car-faq amp-accordion section{border-bottom:2px solid #e0f2fe}
.car-faq amp-accordion h4{background:linear-gradient(135deg,#f0f9ff,#e0f2fe);margin:0;padding:20px;font-size:16px;font-weight:700;color:#0E368A;cursor:pointer;border-left:4px solid #0E368A}
.car-faq amp-accordion h4:hover{background:linear-gradient(135deg,#dbeafe,#bfdbfe)}
.faq-content{padding:20px;font-size:14px;color:#374151;line-height:1.7;background:#ffffff}

/* CONSIDERAÇÕES FINAIS CARROS */
.car-final{background:linear-gradient(135deg,#f0f9ff,#e0f2fe);border:3px solid #0E368A;border-radius:20px;padding:32px;margin:32px 0;text-align:center;position:relative;overflow:hidden}
.car-final:before{content:'';position:absolute;top:0;left:0;right:0;height:6px;background:linear-gradient(90deg,#0E368A,#1e40af,#3b82f6)}
.car-final-title{font-size:24px;font-weight:800;color:#0E368A;margin-bottom:20px}
.car-final-text{font-size:16px;color:#1e40af;line-height:1.7}

/* RESPONSIVIDADE CARROS */
@media (max-width: 768px) {
    h1{font-size:28px}
    h2{font-size:22px}
    .container{padding:16px}
    .car-specs{grid-template-columns:1fr}
    .car-pressure-table{font-size:12px}
    .car-pressure-table th,.car-pressure-table td{padding:10px 6px}
    .tpms-benefits{grid-template-columns:1fr}
    .impact-grid{grid-template-columns:1fr}
    .alternative-brands{grid-template-columns:repeat(auto-fit,minmax(100px,1fr))}
    .car-step{flex-direction:column;text-align:center}
    .car-step-number{margin-right:0;margin-bottom:16px}
}

@media (max-width: 480px) {
    h1{font-size:24px}
    .section-title{font-size:18px}
    .content-section{padding:20px}
    .car-pressure-table{font-size:11px}
    .alternative-brands{grid-template-columns:1fr}
}
</style>
@endsection

@section('content')
@php
// Extração de dados das ViewModels
$vehicleInfo = $article->getData()['vehicle_info'] ?? [];
$vehicleName = $vehicleInfo['full_name'] ?? 'Veículo';
$introduction = $article->getData()['introduction'] ?? '';
$tireSpecs = $article->getData()['tire_specifications'] ?? [];
$pressureTable = $article->getData()['pressure_table'] ?? [];
$calibrationProcedure = $article->getData()['calibration_procedure'] ?? [];
$tpmsSystem = $article->getData()['tpms_system'] ?? [];
$calibrationImpacts = $article->getData()['calibration_impacts'] ?? [];
$maintenanceTips = $article->getData()['maintenance_tips'] ?? [];
$safetyAlerts = $article->getData()['safety_alerts'] ?? [];
$usageRecommendations = $article->getData()['usage_recommendations'] ?? [];
$requiredEquipment = $article->getData()['required_equipment'] ?? [];
$alternativeTires = $article->getData()['alternative_tires'] ?? [];
$faq = $article->getData()['faq'] ?? [];
$finalConsiderations = $article->getData()['final_considerations'] ?? '';

// Flags auxiliares
$hasTpms = $tpmsSystem['has_system'] ?? false;
$isElectric = $vehicleInfo['is_electric'] ?? false;
$isPremium = $vehicleInfo['is_premium'] ?? false;
@endphp

<div class="container">
    <!-- CABEÇALHO MODERNO -->
    <header>
        <h1>{{ $article->getData()['seo_data']['h1'] ?? "Como Calibrar Pneus do {$vehicleName} – Guia Completo" }}</h1>
        <p style="color: #6b7280; font-size: 16px; margin-bottom: 24px; text-align: center;">
            🚗 Guia técnico profissional | Atualizado em: {{ $article->getData()['formated_updated_at'] ?? '14 de agosto de 2025' }}
        </p>
    </header>

    <!-- INTRODUÇÃO -->
    @if(!empty($introduction))
    <div style="margin-bottom: 40px; background: linear-gradient(135deg, #ffffff, #f8fafc); padding: 24px; border-radius: 16px; border: 2px solid #e0f2fe;">
        <p style="font-size: 18px; color: #374151; line-height: 1.7; margin: 0; text-align: center;">
            {!! nl2br(e($introduction)) !!}
        </p>
    </div>
    @endif

    <!-- ESPECIFICAÇÕES TÉCNICAS DOS PNEUS -->
    @if(!empty($tireSpecs))
    <div class="content-section">
        <div class="section-header">
            <span class="section-icon">🔧</span>
            <span class="section-title">{{ $tireSpecs['title'] ?? 'Especificações Técnicas dos Pneus Originais' }}</span>
        </div>
        <p style="color: #6b7280; margin-bottom: 24px; font-size: 16px;">{{ $tireSpecs['description'] ?? '' }}</p>
        
        @if($hasTpms)
        <div class="feature-badge">TPMS</div>
        @endif
        
        <div class="car-specs">
            <!-- Pneu Dianteiro -->
            @if(!empty($tireSpecs['front_tire']))

            class="car-spec front">
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
                <p style="font-size: 13px; color: #6b7280; margin-top: 12px; font-style: italic; background: rgba(255,255,255,0.8); padding: 8px; border-radius: 6px;">
                    {{ $tireSpecs['front_tire']['characteristics'] }}
                </p>
                @endif
            </div>
            @endif

            <!-- Pneu Traseiro -->
            @if(!empty($tireSpecs['rear_tire']))
            <div class="car-spec rear">
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
                <p style="font-size: 13px; color: #6b7280; margin-top: 12px; font-style: italic; background: rgba(255,255,255,0.8); padding: 8px; border-radius: 6px;">
                    {{ $tireSpecs['rear_tire']['characteristics'] }}
                </p>
                @endif
            </div>
            @endif
        </div>

        @if(!empty($tireSpecs['note']))
        <div class="car-alert info">
            <div class="alert-title">📋 Observação Técnica</div>
            <div class="alert-desc">{{ $tireSpecs['note'] }}</div>
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
        <p style="color: #6b7280; margin-bottom: 24px; font-size: 16px;">{{ $pressureTable['description'] ?? '' }}</p>

        <table class="car-pressure-table">
            <thead>
                <tr>
                    <th>Situação de Uso</th>
                    <th>Ocupantes</th>
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
                if (str_contains($situation, 'normal') || str_contains($situation, 'diário')) $rowClass = 'normal';
                elseif (str_contains($situation, 'carga') || str_contains($situation, 'viagem')) $rowClass = 'load';
                elseif (str_contains($situation, 'esportiv')) $rowClass = 'sport';
                @endphp
                <tr class="{{ $rowClass }}">
                    <td style="font-weight: 600;">
                        @switch($rowClass)
                            @case('normal')
                                🚗 {{ $condition['situation'] ?? '' }}
                                @break
                            @case('load')
                                🧳 {{ $condition['situation'] ?? '' }}
                                @break
                            @case('sport')
                                🏁 {{ $condition['situation'] ?? '' }}
                                @break
                            @default
                                🚙 {{ $condition['situation'] ?? '' }}
                        @endswitch
                    </td>
                    <td>{{ $condition['occupants'] ?? '' }}</td>
                    <td>{{ $condition['luggage'] ?? '' }}</td>
                    <td><span class="pressure-value front">{{ $condition['front_pressure'] ?? '' }}</span></td>
                    <td><span class="pressure-value rear">{{ $condition['rear_pressure'] ?? '' }}</span></td>
                    <td style="font-size: 13px;">{{ $condition['note'] ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- PROCEDIMENTO COMPLETO DE CALIBRAGEM -->
    @if(!empty($calibrationProcedure['steps']))
    <div class="content-section">
        <div class="section-header">
            <span class="section-icon">📋</span>
            <span class="section-title">{{ $calibrationProcedure['title'] ?? 'Procedimento Completo de Calibragem' }}</span>
        </div>
        <p style="color: #6b7280; margin-bottom: 24px; font-size: 16px;">{{ $calibrationProcedure['description'] ?? '' }}</p>

        <div class="car-procedure">
            @foreach($calibrationProcedure['steps'] as $step)

            <div class="car-step">
                <div class="car-step-number">{{ $step['number'] ?? '1' }}</div>
                <div class="car-step-content">
                    <h4 class="car-step-title">{{ $step['title'] ?? 'Passo' }}</h4>
                    <p class="car-step-desc">{{ $step['description'] ?? '' }}</p>
                    @if(!empty($step['tips']) && is_array($step['tips']))
                    <ul class="car-tips">
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

    <!-- SISTEMA TPMS -->
    @if($hasTpms && !empty($tpmsSystem))
    <div class="content-section">
        <div class="tpms-box">
            <div class="tpms-title">📡 {{ $tpmsSystem['title'] ?? 'Sistema TPMS Integrado' }}</div>
            <p style="color: #1e40af; font-size: 16px; margin-bottom: 20px;">{{ $tpmsSystem['description'] ?? '' }}</p>
            
            @if(!empty($tpmsSystem['benefits']))
            <div class="tpms-benefits">
                @foreach($tpmsSystem['benefits'] as $benefit)
                <div class="tpms-benefit">
                    <div class="tpms-benefit-title">{{ $benefit['title'] ?? '' }}</div>
                    <div class="tpms-benefit-desc">{{ $benefit['description'] ?? '' }}</div>
                </div>
                @endforeach
            </div>
            @endif

            @if(!empty($tpmsSystem['reset_procedure']))
            <div class="car-alert info" style="margin-top: 20px;">
                <div class="alert-title">🔄 Reset do Sistema TPMS</div>
                <div class="alert-desc">{{ $tpmsSystem['reset_procedure'] }}</div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- IMPACTOS DA CALIBRAGEM NO DESEMPENHO -->
    @if(!empty($calibrationImpacts['categories']))
    <div class="content-section">
        <div class="section-header">
            <span class="section-icon">📈</span>
            <span class="section-title">{{ $calibrationImpacts['title'] ?? 'Impactos da Calibragem no Desempenho' }}</span>
        </div>
        <p style="color: #6b7280; margin-bottom: 24px; font-size: 16px;">{{ $calibrationImpacts['description'] ?? '' }}</p>

        <div class="impact-grid">
            @foreach($calibrationImpacts['categories'] as $category)
            <div class="impact-card">
                <div class="impact-header">
                    <div class="impact-icon {{ strtolower($category['name']) }}">
                        @switch(strtolower($category['name']))
                            @case('economia')
                                💰
                                @break
                            @case('segurança')
                            @case('seguranca')
                                🛡️
                                @break
                            @case('conforto')
                                😊
                                @break
                            @case('durabilidade')
                                ⏰
                                @break
                            @default
                                📊
                        @endswitch
                    </div>
                    <div class="impact-title">{{ $category['title'] ?? ucfirst($category['name']) }}</div>
                </div>
                <p class="impact-desc">{{ $category['description'] ?? '' }}</p>
                @if(!empty($category['benefits']))
                <ul class="impact-benefits">
                    @foreach($category['benefits'] as $benefit)
                    <li>{{ $benefit }}</li>
                    @endforeach
                </ul>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- RECOMENDAÇÕES POR TIPO DE USO -->
    @if(!empty($usageRecommendations['categories']))
    <div class="content-section">
        <div class="section-header">
            <span class="section-icon">🎯</span>
            <span class="section-title">{{ $usageRecommendations['title'] ?? 'Recomendações por Tipo de Uso' }}</span>
        </div>
        <p style="color: #6b7280; margin-bottom: 24px; font-size: 16px;">{{ $usageRecommendations['description'] ?? '' }}</p>

        <div class="usage-grid">
            @foreach($usageRecommendations['categories'] as $usage)
            @php
            $usageClass = 'urban';
            $category = strtolower($usage['category'] ?? '');
            if (str_contains($category, 'rodoviário') || str_contains($category, 'estrada')) $usageClass = 'highway';
            if (str_contains($category, 'esportiv')) $usageClass = 'sport';
            if (str_contains($category, 'eco')) $usageClass = 'eco';
            @endphp
            <div class="usage-card {{ $usageClass }}">
                <div class="usage-header">
                    <div class="usage-title">
                        @switch($usageClass)
                            @case('urban')
                                🏙️ {{ $usage['category'] }}
                                @break
                            @case('highway')
                                🛣️ {{ $usage['category'] }}
                                @break
                            @case('sport')
                                🏁 {{ $usage['category'] }}
                                @break
                            @case('eco')
                                🌱 {{ $usage['category'] }}
                                @break
                            @default
                                🚗 {{ $usage['category'] }}
                        @endswitch
                    </div>
                    <div class="usage-pressure">{{ $usage['recommended_pressure'] ?? '' }}</div>
                </div>
                <p class="usage-desc">{{ $usage['description'] ?? '' }}</p>
                @if(!empty($usage['technical_tip']))
                <div class="usage-tip">💡 {{ $usage['technical_tip'] }}</div>
                @endif
                @if(!empty($usage['verification_frequency']))
                <p class="usage-frequency">Verificar: {{ $usage['verification_frequency'] }}</p>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- EQUIPAMENTOS NECESSÁRIOS -->
    @if(!empty($requiredEquipment['items']))
    <div class="content-section">
        <div class="section-header">
            <span class="section-icon">🧰</span>
            <span class="section-title">{{ $requiredEquipment['title'] ?? 'Equipamentos Necessários' }}</span>
        </div>
        <p style="color: #6b7280; margin-bottom: 24px; font-size: 16px;">{{ $requiredEquipment['description'] ?? '' }}</p>

        <div class="equipment-grid">
            @foreach($requiredEquipment['items'] as $equipment)
            <div class="equipment-item">
                <div class="equipment-header">
                    <div class="equipment-name">
                        @php
                        $item = strtolower($equipment['name'] ?? '');
                        $icon = '🔧';
                        if (str_contains($item, 'calibrador') || str_contains($item, 'medidor')) $icon = '📏';
                        if (str_contains($item, 'compressor')) $icon = '💨';
                        if (str_contains($item, 'lanterna')) $icon = '🔦';
                        if (str_contains($item, 'luva')) $icon = '🧤';
                        @endphp
                        {{ $icon }} {{ $equipment['name'] ?? '' }}
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
                <ul style="list-style: none; padding: 0; margin: 12px 0 0;">
                    @foreach($equipment['tips'] as $tip)
                    <li style="font-size: 13px; color: #6b7280; margin-bottom: 4px; padding-left: 16px; position: relative; background: linear-gradient(135deg, #f0f9ff, #e0f2fe); padding: 8px 16px; border-radius: 8px; margin-bottom: 6px;">
                        <span style="position: absolute; left: 8px;">💡</span>{{ $tip }}
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- ALERTAS CRÍTICOS DE SEGURANÇA -->
    @if(!empty($safetyAlerts['alerts']))
    <div class="content-section">
        <div class="section-header">
            <span class="section-icon">⚠️</span>
            <span class="section-title">{{ $safetyAlerts['title'] ?? 'Alertas Críticos de Segurança' }}</span>
        </div>
        <p style="color: #6b7280; margin-bottom: 24px; font-size: 16px;">{{ $safetyAlerts['description'] ?? '' }}</p>

        <div class="car-alerts">
            @foreach($safetyAlerts['alerts'] as $alert)
            <div class="car-alert {{ $alert['type'] ?? 'warning' }}">
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
                <p style="font-weight: 600; margin: 12px 0 6px; font-size: 14px;">Consequências:</p>
                <ul class="alert-actions">
                    @foreach($alert['consequences'] as $consequence)
                    <li>{{ $consequence }}</li>
                    @endforeach
                </ul>
                @endif
                @if(!empty($alert['actions']))
                <p style="font-weight: 600; margin: 12px 0 6px; font-size: 14px;">Ações necessárias:</p>
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

    <!-- PNEUS ALTERNATIVOS RECOMENDADOS -->
    @if(!empty($alternativeTires['categories']))
    <div class="content-section">
        <div class="section-header">
            <span class="section-icon">🔄</span>
            <span class="section-title">{{ $alternativeTires['title'] ?? 'Pneus Alternativos Recomendados' }}</span>
        </div>
        <p style="color: #6b7280; margin-bottom: 24px; font-size: 16px;">{{ $alternativeTires['description'] ?? '' }}</p>

        <div class="alternatives-grid">
            @foreach($alternativeTires['categories'] as $category => $brands)
                @if(!empty($brands) && is_array($brands))
                <div class="alternative-category">
                    <div class="alternative-title">
                        @switch($category)
                            @case('premium')
                                👑 Premium
                                @break
                            @case('performance')
                                🏁 Performance
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
        <div class="car-alert info">
            <div class="alert-title">📝 Observação Importante</div>
            <div class="alert-desc">{{ $alternativeTires['note'] }}</div>
        </div>
        @endif
    </div>
    @endif

    <!-- RECURSOS ELÉTRICOS (se aplicável) -->
    @if($isElectric)
    <div class="content-section">
        <div class="car-alert info">
            <div class="alert-title">🔋 Veículo Elétrico - Considerações Especiais</div>
            <div class="alert-desc">
                Em veículos elétricos, a pressão correta dos pneus é ainda mais crítica, podendo aumentar a autonomia 
                em até 15-20km por carga. O peso das baterias exige pressões ligeiramente superiores às recomendadas 
                para veículos convencionais.
            </div>
        </div>
    </div>
    @endif

    <!-- FAQ COMPLETO -->
    @if(!empty($faq))
    <div class="content-section car-faq">
        <div class="section-header">
            <span class="section-icon">❓</span>
            <span class="section-title">Perguntas Frequentes sobre {{ $vehicleName }}</span>
        </div>
        <amp-accordion expand-single-section>
            @foreach($faq as $pergunta)
            <section>
                <h4>🚗 {{ $pergunta['pergunta'] ?? $pergunta['question'] ?? '' }}</h4>
                <div class="faq-content">
                    <p>{{ $pergunta['resposta'] ?? $pergunta['answer'] ?? '' }}</p>
                </div>
            </section>
            @endforeach
        </amp-accordion>
    </div>
    @endif

    <!-- CONSIDERAÇÕES FINAIS -->
    @if(!empty($finalConsiderations))
    <div class="car-final">
        <div class="car-final-title">🎯 Considerações Finais</div>
        <div class="car-final-text">
            {!! nl2br(e($finalConsiderations)) !!}
        </div>
    </div>
    @endif

    <!-- INFORMAÇÕES DO VEÍCULO -->
    <div class="content-section">
        <div class="section-header">
            <span class="section-icon">📋</span>
            <span class="section-title">Resumo das Informações</span>
        </div>
        <div style="background: linear-gradient(135deg, #f8fafc, #e2e8f0); padding: 20px; border-radius: 12px; border: 2px solid #cbd5e1;">
            <p style="margin: 0; font-size: 15px; color: #374151; line-height: 1.6;">
                <strong>Veículo:</strong> {{ $vehicleName }}<br>
                @if(!empty($vehicleInfo['segment']))
                <strong>Categoria:</strong> {{ $vehicleInfo['segment'] }}<br>
                @endif
                @if(!empty($vehicleInfo['fuel_type']))
                <strong>Combustível:</strong> {{ $vehicleInfo['fuel_type'] }}<br>
                @endif
                <strong>Sistema TPMS:</strong> {{ $hasTpms ? 'Sim' : 'Não' }}<br>
                <strong>Tipo:</strong> {{ $isElectric ? 'Elétrico' : ($isPremium ? 'Premium' : 'Convencional') }}<br>
                <strong>Última atualização:</strong> {{ $article->getData()['formated_updated_at'] ?? '14 de agosto de 2025' }}
            </p>
        </div>
    </div>

    <!-- FOOTER INFORMATIVO -->
    <footer style="text-align: center; padding: 24px 0; border-top: 3px solid #e0f2fe; margin-top: 40px; background: linear-gradient(135deg, #f8fafc, #e2e8f0); border-radius: 16px;">
        <p style="font-size: 14px; color: #6b7280; margin: 0; line-height: 1.6;">
            🚗 Guia técnico baseado nas especificações oficiais do fabricante.<br>
            Para dúvidas específicas, consulte sempre o manual do proprietário.
        </p>
        <p style="font-size: 12px; color: #9ca3af; margin: 12px 0 0;">
            ⚡ Pressão correta = Economia + Segurança + Conforto
        </p>
    </footer>
</div>
@endsection 