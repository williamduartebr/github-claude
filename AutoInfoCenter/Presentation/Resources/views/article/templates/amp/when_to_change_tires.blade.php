@extends('auto-info-center::layouts.amp')

@section('amp-head')
<script async custom-element="amp-accordion" src="https://cdn.ampproject.org/v0/amp-accordion-0.1.js"></script>
<script async custom-element="amp-form" src="https://cdn.ampproject.org/v0/amp-form-0.1.js"></script>
<style amp-custom>
    /* Reset e base */
    * {
        box-sizing: border-box;
    }
    
    body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        line-height: 1.6;
        color: #333;
        margin: 0;
        padding: 0;
        background-color: #fff;
    }
    
    .container {
        max-width: 800px;
        margin: 0 auto;
        padding: 16px;
    }
    
    /* Typography */
    h1 {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 16px;
        color: #151C25;
        line-height: 1.3;
    }
    
    h2 {
        font-size: 22px;
        font-weight: 600;
        margin: 32px 0 16px;
        padding-bottom: 8px;
        border-bottom: 2px solid #0E368A;
        color: #151C25;
        position: relative;
    }
    
    h2:after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 50px;
        height: 2px;
        background-color: #0E368A;
    }
    
    h3 {
        font-size: 18px;
        font-weight: 600;
        margin: 20px 0 12px;
        color: #151C25;
    }
    
    p {
        margin-bottom: 16px;
        line-height: 1.7;
    }
    
    /* Meta info */
    .article-meta {
        color: #718096;
        font-size: 14px;
        margin-bottom: 20px;
        padding: 8px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    
    /* Ad containers */
    .ad-container {
        text-align: center;
        margin: 32px 0;
        padding: 16px;
        background-color: #f8fafc;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }
    
    .ad-label {
        font-size: 11px;
        color: #999;
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 500;
    }
    
    /* Cards de sintomas */
    .symptom-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        background-color: #fff;
    }
    
    .symptom-card-header {
        padding: 16px;
        display: flex;
        align-items: center;
    }
    
    .symptom-card-header.high-severity {
        background: linear-gradient(135deg, #fef2f2, #fee2e2);
        border-left: 4px solid #dc2626;
    }
    
    .symptom-card-header.medium-severity {
        background: linear-gradient(135deg, #fffbeb, #fef3c7);
        border-left: 4px solid #f59e0b;
    }
    
    .symptom-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        flex-shrink: 0;
    }
    
    .symptom-icon.high-severity {
        background-color: #fee2e2;
        color: #dc2626;
    }
    
    .symptom-icon.medium-severity {
        background-color: #fef3c7;
        color: #f59e0b;
    }
    
    .symptom-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 4px;
        color: #151C25;
    }
    
    .severity-badge {
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 500;
        text-transform: uppercase;
    }
    
    .severity-badge.high {
        background-color: #fee2e2;
        color: #991b1b;
    }
    
    .severity-badge.medium {
        background-color: #fef3c7;
        color: #92400e;
    }
    
    .symptom-body {
        padding: 16px;
        background-color: #fff;
    }
    
    .symptom-description {
        color: #4a5568;
        margin-bottom: 12px;
    }
    
    .symptom-action {
        background-color: #f8fafc;
        padding: 12px;
        border-radius: 8px;
        border-left: 3px solid #0E368A;
        font-size: 14px;
        color: #1e40af;
    }
    
    /* Fatores de durabilidade */
    .durability-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 16px;
        background-color: #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .durability-header {
        background: linear-gradient(135deg, #0E368A, #1a4da8);
        color: white;
        padding: 16px;
        font-weight: 600;
        font-size: 16px;
    }
    
    .durability-body {
        padding: 16px;
    }
    
    .impact-bar {
        display: flex;
        align-items: center;
        margin: 12px 0;
    }
    
    .impact-label {
        font-size: 13px;
        color: #718096;
        min-width: 100px;
    }
    
    .impact-progress {
        flex: 1;
        height: 6px;
        background-color: #e2e8f0;
        border-radius: 3px;
        margin: 0 12px;
        overflow: hidden;
    }
    
    .impact-fill {
        height: 100%;
        border-radius: 3px;
    }
    
    .impact-fill.positive {
        background: linear-gradient(90deg, #10b981, #34d399);
    }
    
    .impact-fill.negative {
        background: linear-gradient(90deg, #ef4444, #f87171);
    }
    
    .impact-value {
        font-size: 13px;
        font-weight: 600;
        min-width: 40px;
        text-align: right;
    }
    
    .impact-value.positive {
        color: #059669;
    }
    
    .impact-value.negative {
        color: #dc2626;
    }
    
    /* Timeline de verificação */
    .verification-timeline {
        position: relative;
        margin: 20px 0;
    }
    
    .timeline-item {
        display: flex;
        margin-bottom: 24px;
        align-items: flex-start;
    }
    
    .timeline-marker {
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, #0E368A, #1a4da8);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 16px;
        flex-shrink: 0;
        font-weight: 700;
        font-size: 14px;
        position: relative;
    }
    
    .timeline-marker::after {
        content: '';
        position: absolute;
        top: 32px;
        left: 50%;
        transform: translateX(-50%);
        width: 2px;
        height: 24px;
        background: linear-gradient(to bottom, #0E368A, transparent);
    }
    
    .timeline-item:last-child .timeline-marker::after {
        display: none;
    }
    
    .timeline-content {
        flex: 1;
        background-color: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 16px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .timeline-title {
        font-size: 16px;
        font-weight: 600;
        color: #151C25;
        margin-bottom: 8px;
    }
    
    .importance-badge {
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 500;
        margin-left: 8px;
    }
    
    .importance-badge.high {
        background-color: #fee2e2;
        color: #991b1b;
    }
    
    .importance-badge.medium {
        background-color: #dbeafe;
        color: #1e40af;
    }
    
    /* Tabela de tipos de pneus */
    .tire-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        margin-bottom: 24px;
    }
    
    .tire-table th {
        background: linear-gradient(135deg, #0E368A, #1a4da8);
        color: white;
        text-align: left;
        padding: 12px 8px;
        font-weight: 600;
        font-size: 13px;
    }
    
    .tire-table td {
        padding: 12px 8px;
        border-bottom: 1px solid #e2e8f0;
        background-color: #fff;
        font-size: 13px;
    }
    
    .tire-table tr:nth-child(even) td {
        background-color: #f8fafc;
    }
    
    .tire-table tr:last-child td {
        border-bottom: none;
    }
    
    /* Sinais críticos */
    .critical-sign {
        background: linear-gradient(135deg, #fef2f2, #fee2e2);
        border: 1px solid #fecaca;
        border-left: 4px solid #dc2626;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 16px;
    }
    
    .critical-header {
        display: flex;
        align-items: center;
        margin-bottom: 12px;
    }
    
    .critical-icon {
        width: 24px;
        height: 24px;
        background-color: #fee2e2;
        color: #dc2626;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 8px;
        flex-shrink: 0;
    }
    
    .critical-title {
        font-size: 16px;
        font-weight: 600;
        color: #991b1b;
    }
    
    .limits-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin: 12px 0;
    }
    
    .limit-box {
        background-color: #fff;
        padding: 8px;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        text-align: center;
    }
    
    .limit-label {
        font-size: 11px;
        color: #6b7280;
        margin-bottom: 2px;
    }
    
    .limit-value {
        font-size: 16px;
        font-weight: 700;
    }
    
    .limit-value.legal {
        color: #dc2626;
    }
    
    .limit-value.recommended {
        color: #ea580c;
    }
    
    /* Especificações do veículo */
    .vehicle-specs {
        background: linear-gradient(135deg, #f8fafc, #e2e8f0);
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 24px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .specs-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
    }
    
    .specs-section {
        padding: 20px;
    }
    
    .specs-section:first-child {
        border-right: 1px solid #cbd5e1;
    }
    
    .specs-title {
        font-size: 16px;
        font-weight: 600;
        color: #151C25;
        margin-bottom: 16px;
    }
    
    .spec-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        font-size: 14px;
    }
    
    .spec-label {
        color: #6b7280;
    }
    
    .spec-value {
        font-weight: 600;
        color: #151C25;
        background-color: #f8fafc;
        padding: 2px 8px;
        border-radius: 4px;
    }
    
    .pressure-display {
        background: linear-gradient(135deg, rgba(14, 54, 138, 0.1), rgba(14, 54, 138, 0.05));
        border: 2px solid rgba(14, 54, 138, 0.2);
        border-radius: 8px;
        padding: 12px;
        text-align: center;
        margin-bottom: 8px;
    }
    
    .pressure-label {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 4px;
    }
    
    .pressure-value {
        font-size: 20px;
        font-weight: 700;
        color: #0E368A;
    }
    
    .pressure-value.loaded {
        font-size: 16px;
        color: #4b5563;
    }
    
    /* FAQs com accordion */
    amp-accordion {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 24px;
    }
    
    amp-accordion section {
        border-bottom: 1px solid #e2e8f0;
    }
    
    amp-accordion section:last-child {
        border-bottom: none;
    }
    
    amp-accordion h4 {
        font-size: 16px;
        padding: 16px;
        margin: 0;
        background-color: #f8fafc;
        font-weight: 600;
        color: #151C25;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }
    
    amp-accordion h4:hover {
        background-color: #e2e8f0;
    }
    
    amp-accordion .faq-content {
        padding: 16px;
        background-color: #fff;
        color: #4a5568;
        line-height: 1.6;
    }
    
    /* Manutenção preventiva */
    .maintenance-card {
        background-color: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 16px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .maintenance-header {
        display: flex;
        align-items: center;
        margin-bottom: 12px;
    }
    
    .maintenance-icon {
        width: 32px;
        height: 32px;
        background-color: rgba(14, 54, 138, 0.1);
        color: #0E368A;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        flex-shrink: 0;
    }
    
    .maintenance-title {
        font-size: 15px;
        font-weight: 600;
        color: #151C25;
    }
    
    .maintenance-detail {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 8px;
    }
    
    .maintenance-benefit {
        background-color: #f0fdf4;
        border: 1px solid #bbf7d0;
        padding: 8px;
        border-radius: 6px;
        font-size: 13px;
        color: #166534;
        margin-top: 8px;
    }
    
    .care-list {
        background-color: #f8fafc;
        border-radius: 8px;
        padding: 16px;
        margin-top: 16px;
    }
    
    .care-list h4 {
        font-size: 16px;
        font-weight: 600;
        color: #151C25;
        margin-bottom: 12px;
    }
    
    .care-list ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .care-list li {
        padding: 4px 0;
        color: #4a5568;
        font-size: 14px;
        position: relative;
        padding-left: 20px;
    }
    
    .care-list li::before {
        content: '•';
        color: #0E368A;
        font-weight: bold;
        position: absolute;
        left: 0;
    }
    
    /* Procedimento */
    .procedure-step {
        display: flex;
        margin-bottom: 24px;
        align-items: flex-start;
    }
    
    .step-number {
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, #0E368A, #1a4da8);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 16px;
        flex-shrink: 0;
        font-weight: 700;
        font-size: 14px;
    }
    
    .step-content {
        flex: 1;
        background-color: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .step-header {
        background: linear-gradient(135deg, #0E368A, #1a4da8);
        color: white;
        padding: 12px 16px;
        font-weight: 600;
        font-size: 15px;
    }
    
    .step-body {
        padding: 16px;
    }
    
    .step-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .step-list li {
        padding: 6px 0;
        color: #4a5568;
        font-size: 14px;
        position: relative;
        padding-left: 24px;
    }
    
    .step-list li::before {
        content: counter(step-counter);
        counter-increment: step-counter;
        position: absolute;
        left: 0;
        background-color: #0E368A;
        color: white;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        font-size: 10px;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .pressures-box {
        background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        border: 1px solid #93c5fd;
        border-radius: 8px;
        padding: 12px;
        margin: 12px 0;
    }
    
    .pressures-title {
        font-size: 14px;
        font-weight: 600;
        color: #1e40af;
        margin-bottom: 8px;
    }
    
    .pressures-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }
    
    .pressure-item {
        display: flex;
        justify-content: space-between;
        font-size: 13px;
    }
    
    .pressure-item-label {
        color: #1e40af;
    }
    
    .pressure-item-value {
        font-weight: 600;
        color: #1e3a8a;
    }
    
    /* Footer */
    .article-footer {
        font-size: 12px;
        color: #718096;
        margin-top: 32px;
        padding-top: 16px;
        border-top: 1px solid #e2e8f0;
        background-color: #f8fafc;
        padding: 16px;
        border-radius: 8px;
    }
    
    .article-footer p {
        margin-bottom: 4px;
    }
    
    .article-footer a {
        color: #0E368A;
        text-decoration: none;
    }
    
    .article-footer a:hover {
        text-decoration: underline;
    }
    
    /* Responsivo */
    @media (max-width: 768px) {
        .container {
            padding: 12px;
        }
        
        h1 {
            font-size: 24px;
        }
        
        h2 {
            font-size: 20px;
        }
        
        .specs-grid {
            grid-template-columns: 1fr;
        }
        
        .specs-section:first-child {
            border-right: none;
            border-bottom: 1px solid #cbd5e1;
        }
        
        .limits-grid {
            grid-template-columns: 1fr;
        }
        
        .pressures-grid {
            grid-template-columns: 1fr;
        }
        
        .tire-table {
            font-size: 12px;
        }
        
        .tire-table th,
        .tire-table td {
            padding: 8px 4px;
        }
        
        .timeline-marker {
            width: 28px;
            height: 28px;
            font-size: 12px;
        }
        
        .ad-container {
            margin: 24px 0;
            padding: 12px;
        }
    }
    
    @media (max-width: 480px) {
        h1 {
            font-size: 22px;
        }
        
        h2 {
            font-size: 18px;
        }
        
        .symptom-card-header {
            flex-direction: column;
            text-align: center;
        }
        
        .symptom-icon {
            margin-right: 0;
            margin-bottom: 8px;
        }
        
        .pressure-value {
            font-size: 18px;
        }
        
        .tire-table th,
        .tire-table td {
            padding: 6px 3px;
            font-size: 11px;
        }
    }
</style>
@endsection

@section('content')
<div class="container">
    <article>
        <!-- Cabeçalho -->
        <h1>{{ $article->title }}</h1>
        <div class="article-meta">
            @if(!empty($article->formated_updated_at))
            <p>Atualizado em: {{ $article->formated_updated_at }}</p>
            @endif
        </div>
        
        <!-- Introdução -->
        @if(!empty($article->introduction))
        <p>{{ $article->introduction }}</p>
        @endif
        
        <!-- 🥇 ANÚNCIO 1: Após introdução -->
        <div class="ad-container">
            <div class="ad-label">Publicidade</div>
            <amp-ad width="100vw" height="320"
                type="adsense"
                data-ad-client="{{ Config::get('services.google_adsense.id') }}"
                data-ad-slot="7414648059"   
                data-auto-format="rspv"
                data-full-width>
                <div overflow></div>
            </amp-ad>
        </div>
        
        <!-- Sintomas de Desgaste -->
        @if(!empty($article->wear_symptoms) && is_array($article->wear_symptoms) && count($article->wear_symptoms) > 0)
        <h2>⚠️ Sintomas de Pneus que Precisam de Substituição</h2>
        
        @foreach($article->wear_symptoms as $symptom)
        @if(!empty($symptom['title']))
        <div class="symptom-card">
            <div class="symptom-card-header {{ $symptom['severity'] === 'alta' ? 'high-severity' : 'medium-severity' }}">
                <div class="symptom-icon {{ $symptom['severity'] === 'alta' ? 'high-severity' : 'medium-severity' }}">
                    @if($symptom['severity'] === 'alta')
                    ⚠️
                    @else
                    ℹ️
                    @endif
                </div>
                <div>
                    <div class="symptom-title">{{ $symptom['title'] }}</div>
                    <span class="severity-badge {{ $symptom['severity'] === 'alta' ? 'high' : 'medium' }}">
                        Severidade {{ ucfirst($symptom['severity']) }}
                    </span>
                </div>
            </div>
            <div class="symptom-body">
                @if(!empty($symptom['description']))
                <p class="symptom-description">{{ $symptom['description'] }}</p>
                @endif
                @if(!empty($symptom['action']))
                <div class="symptom-action">
                    <strong>Ação recomendada:</strong> {{ $symptom['action'] }}
                </div>
                @endif
            </div>
        </div>
        @endif
        @endforeach
        @endif
        
        <!-- Fatores de Durabilidade -->
        @if(!empty($article->durability_factors) && is_array($article->durability_factors) && count($article->durability_factors) > 0)
        <h2>📊 Fatores que Afetam a Durabilidade dos Pneus</h2>
        
        @foreach($article->durability_factors as $factor)
        @if(!empty($factor['title']))
        <div class="durability-card">
            <div class="durability-header">{{ $factor['title'] }}</div>
            <div class="durability-body">
                @if(!empty($factor['impact']))
                <div class="impact-bar">
                    <span class="impact-label">Impacto:</span>
                    <div class="impact-progress">
                        @php
                        $impactValue = (int) filter_var($factor['impact'], FILTER_SANITIZE_NUMBER_INT);
                        $width = min(abs($impactValue), 100);
                        @endphp
                        <div class="impact-fill {{ $factor['is_positive'] ? 'positive' : 'negative' }}" style="width: {{ $width }}%"></div>
                    </div>
                    <span class="impact-value {{ $factor['is_positive'] ? 'positive' : 'negative' }}">
                        {{ $factor['impact'] }}
                    </span>
                </div>
                @endif
                
                @if(!empty($factor['description']))
                <p>{{ $factor['description'] }}</p>
                @endif
                
                @if(!empty($factor['recommendation']))
                <div style="background-color: #dbeafe; padding: 12px; border-radius: 8px; margin-top: 12px;">
                    <strong style="color: #1e40af;">Recomendação:</strong> 
                    <span style="color: #1e40af;">{{ $factor['recommendation'] }}</span>
                </div>
                @endif
                
                @if(!empty($factor['pressure_recommendation']))
                <div style="margin-top: 8px; font-size: 13px; color: #6b7280;">
                    <strong>Pressão recomendada:</strong> {{ $factor['pressure_recommendation'] }}
                </div>
                @endif
            </div>
        </div>
        @endif
        @endforeach
        @endif
        
        <!-- 🥇 ANÚNCIO 2: Após fatores de durabilidade -->
        <div class="ad-container">
            <div class="ad-label">Publicidade</div>
            <amp-ad width="100vw" height="320"
                type="adsense"
                data-ad-client="{{ Config::get('services.google_adsense.id') }}"
                data-ad-slot="8344586349"
                data-auto-format="rspv"
                data-full-width>
                <div overflow></div>
            </amp-ad>
        </div>
        
        <!-- Cronograma de Verificação -->
        @if(!empty($article->verification_schedule) && is_array($article->verification_schedule) && count($article->verification_schedule) > 0)
        <h2>🗓️ Cronograma de Verificação e Manutenção</h2>
        
        <div class="verification-timeline">
            @foreach($article->verification_schedule as $index => $schedule)
            @if(!empty($schedule['title']))
            <div class="timeline-item">
                <div class="timeline-marker">{{ $index + 1 }}</div>
                <div class="timeline-content">
                    <div class="timeline-title">
                        {{ $schedule['title'] }}
                        <span class="importance-badge {{ $schedule['importance'] === 'alta' || $schedule['importance'] === 'essencial' || $schedule['importance'] === 'obrigatória' ? 'high' : 'medium' }}">
                            {{ ucfirst($schedule['importance']) }}
                        </span>
                    </div>
                    @if(!empty($schedule['description']))
                    <p>{{ $schedule['description'] }}</p>
                    @endif
                </div>
            </div>
            @endif
            @endforeach
        </div>
        @endif
        
        <!-- Tipos de Pneus -->
        @if(!empty($article->tire_types) && is_array($article->tire_types) && count($article->tire_types) > 0)
        <h2>🚗 Tipos de Pneus e Quilometragem Esperada</h2>
        
        <table class="tire-table">
            <thead>
                <tr>
                    <th>Tipo de Pneu</th>
                    <th>Quilometragem</th>
                    <th>Aplicação</th>
                    <th>Observações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($article->tire_types as $tire)
                @if(!empty($tire['type']))
                <tr>
                    <td><strong>{{ $tire['type'] }}</strong></td>
                    <td>{{ $tire['expected_mileage'] ?? 'N/A' }}</td>
                    <td>{{ $tire['application'] ?? 'N/A' }}</td>
                    <td>{{ $tire['observations'] ?? 'N/A' }}</td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
        @endif
        
        <!-- Sinais Críticos -->
        @if(!empty($article->critical_signs) && is_array($article->critical_signs) && count($article->critical_signs) > 0)
        <h2>🚨 Sinais Críticos para Substituição Imediata</h2>
        
        @foreach($article->critical_signs as $sign)
        @if(!empty($sign['title']))
        <div class="critical-sign">
            <div class="critical-header">
                <div class="critical-icon">⚠️</div>
                <div class="critical-title">{{ $sign['title'] }}</div>
            </div>
            
            @if(!empty($sign['legal_limit']) || !empty($sign['recommended_limit']))
            <div class="limits-grid">
                @if(!empty($sign['legal_limit']))
                <div class="limit-box">
                    <div class="limit-label">Limite Legal</div>
                    <div class="limit-value legal">{{ $sign['legal_limit'] }}</div>
                </div>
                @endif
                @if(!empty($sign['recommended_limit']))
                <div class="limit-box">
                    <div class="limit-label">Limite Recomendado</div>
                    <div class="limit-value recommended">{{ $sign['recommended_limit'] }}</div>
                </div>
                @endif
            </div>
            @endif
            
            @if(!empty($sign['test']))
            <p><strong>Como testar:</strong> {{ $sign['test'] }}</p>
            @endif
            
            @if(!empty($sign['types']) && is_array($sign['types']) && count($sign['types']) > 0)
            <div style="margin: 12px 0;">
                <strong>Tipos de danos estruturais:</strong>
                <ul style="margin: 8px 0; padding-left: 20px;">
                    @foreach($sign['types'] as $type)
                    @if(!empty($type))
                    <li style="color: #dc2626;">{{ $type }}</li>
                    @endif
                    @endforeach
                </ul>
            </div>
            @endif
            
            @if(!empty($sign['patterns']) && is_array($sign['patterns']) && count($sign['patterns']) > 0)
            <div style="margin: 12px 0;">
                <strong>Padrões de desgaste irregular:</strong>
                <ul style="margin: 8px 0; padding-left: 20px;">
                    @foreach($sign['patterns'] as $pattern)
                    @if(!empty($pattern))
                    <li style="color: #ea580c;">{{ $pattern }}</li>
                    @endif
                    @endforeach
                </ul>
            </div>
            @endif
            
            @if(!empty($sign['action']))
            <div style="background-color: #fff; border: 1px solid #fecaca; padding: 12px; border-radius: 8px; margin-top: 12px;">
                <strong style="color: #dc2626;">Ação obrigatória:</strong> 
                <span style="color: #dc2626;">{{ $sign['action'] }}</span>
            </div>
            @endif
        </div>
        @endif
        @endforeach
        @endif
        
        <!-- 🥇 ANÚNCIO 3: Após sinais críticos -->
        <div class="ad-container">
            <div class="ad-label">Publicidade</div>
            <amp-ad width="100vw" height="320"
                type="adsense"
                data-ad-client="{{ Config::get('services.google_adsense.id') }}"
                data-ad-slot="1402260703"
                data-auto-format="rspv"
                data-full-width>
                <div overflow></div>
            </amp-ad>
        </div>
        
        <!-- Manutenção Preventiva -->
        @if(!empty($article->preventive_maintenance) && is_array($article->preventive_maintenance))
        <h2>🔧 Manutenção Preventiva dos Pneus</h2>
        
        @if(!empty($article->preventive_maintenance['tasks']) && is_array($article->preventive_maintenance['tasks']))
        @foreach($article->preventive_maintenance['tasks'] as $task)
        @if(!empty($task['frequency']))
        <div class="maintenance-card">
            <div class="maintenance-header">
                <div class="maintenance-icon">⏰</div>
                <div class="maintenance-title">{{ $task['frequency'] }}</div>
            </div>
            
            @if(!empty($task['moment']))
            <div class="maintenance-detail"><strong>Quando:</strong> {{ $task['moment'] }}</div>
            @endif
            
            @if(!empty($task['pattern']))
            <div class="maintenance-detail"><strong>Padrão:</strong> {{ $task['pattern'] }}</div>
            @endif
            
            @if(!empty($task['tolerance']))
            <div class="maintenance-detail"><strong>Tolerância:</strong> {{ $task['tolerance'] }}</div>
            @endif
            
            @if(!empty($task['signs']))
            <div class="maintenance-detail"><strong>Sinais:</strong> {{ $task['signs'] }}</div>
            @endif
            
            @if(!empty($task['benefit']))
            <div class="maintenance-benefit">
                <strong>Benefício:</strong> {{ $task['benefit'] }}
            </div>
            @endif
            
            @if(!empty($task['importance']))
            <div style="background-color: #fffbeb; border: 1px solid #fed7aa; padding: 8px; border-radius: 6px; font-size: 13px; color: #92400e; margin-top: 8px;">
                <strong>Importância:</strong> {{ $task['importance'] }}
            </div>
            @endif
        </div>
        @endif
        @endforeach
        @endif
        
        @if(!empty($article->preventive_maintenance['general_care']) && is_array($article->preventive_maintenance['general_care']))
        <div class="care-list">
            <h4>Cuidados Gerais</h4>
            <ul>
                @foreach($article->preventive_maintenance['general_care'] as $care)
                @if(!empty($care))
                <li>{{ $care }}</li>
                @endif
                @endforeach
            </ul>
        </div>
        @endif
        @endif
        
        <!-- Procedimento de Verificação -->
        @if(!empty($article->verification_procedure) && is_array($article->verification_procedure) && count($article->verification_procedure) > 0)
        <h2>📋 Procedimento de Verificação dos Pneus</h2>
        
        @foreach($article->verification_procedure as $index => $procedure)
        @if(!empty($procedure['title']))
        <div class="procedure-step">
            <div class="step-number">{{ $index + 1 }}</div>
            <div class="step-content">
                <div class="step-header">{{ $procedure['title'] }}</div>
                <div class="step-body">
                    @if(!empty($procedure['steps']) && is_array($procedure['steps']))
                    <div style="margin-bottom: 12px;">
                        <strong>Passos:</strong>
                        <ol class="step-list" style="counter-reset: step-counter;">
                            @foreach($procedure['steps'] as $step)
                            @if(!empty($step))
                            <li>{{ $step }}</li>
                            @endif
                            @endforeach
                        </ol>
                    </div>
                    @endif
                    
                    @if(!empty($procedure['pressures']) && is_array($procedure['pressures']))
                    <div class="pressures-box">
                        <div class="pressures-title">Pressões Recomendadas:</div>
                        <div class="pressures-grid">
                            @foreach($procedure['pressures'] as $key => $pressure)
                            @if(!empty($pressure))
                            <div class="pressure-item">
                                <span class="pressure-item-label">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                <span class="pressure-item-value">{{ $pressure }}</span>
                            </div>
                            @endif
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    @if(!empty($procedure['tolerance']))
                    <div style="font-size: 13px; color: #6b7280; margin-top: 8px;">
                        <strong>Tolerância:</strong> {{ $procedure['tolerance'] }}
                    </div>
                    @endif
                    
                    @if(!empty($procedure['verify']) && is_array($procedure['verify']))
                    <div style="margin-top: 12px;">
                        <strong>Itens a verificar:</strong>
                        <ul style="margin: 8px 0; padding-left: 20px;">
                            @foreach($procedure['verify'] as $item)
                            @if(!empty($item))
                            <li style="color: #4a5568; font-size: 14px;">{{ $item }}</li>
                            @endif
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    
                    @if(!empty($procedure['procedure']) && is_array($procedure['procedure']))
                    <div style="margin-top: 12px;">
                        <strong>Procedimento detalhado:</strong>
                        <ul style="margin: 8px 0; padding-left: 20px;">
                            @foreach($procedure['procedure'] as $step)
                            @if(!empty($step))
                            <li style="color: #4a5568; font-size: 14px;">{{ $step }}</li>
                            @endif
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
        @endforeach
        @endif
        
        <!-- Especificações do Veículo -->
        @if(!empty($article->vehicle_data) && is_array($article->vehicle_data))
        <h2>📊 Especificações do {{ $article->vehicle_full_name ?? 'Veículo' }}</h2>
        
        <div class="vehicle-specs">
            <div class="specs-grid">
                <div class="specs-section">
                    <div class="specs-title">Informações Básicas</div>
                    @if(!empty($article->vehicle_data['tire_size']))
                    <div class="spec-item">
                        <span class="spec-label">Medida dos Pneus:</span>
                        <span class="spec-value">{{ $article->vehicle_data['tire_size'] }}</span>
                    </div>
                    @endif
                    @if(!empty($article->vehicle_data['vehicle_category']))
                    <div class="spec-item">
                        <span class="spec-label">Categoria:</span>
                        <span class="spec-value">{{ translate_vehicle_type($article->vehicle_data['vehicle_category']) }}</span>
                    </div>
                    @endif
                    @if(!empty($article->vehicle_data['vehicle_type']))
                    <div class="spec-item">
                        <span class="spec-label">Tipo:</span>
                        <span class="spec-value">{{ translate_vehicle_type($article->vehicle_data['vehicle_type']) }}</span>
                    </div>
                    @endif
                </div>
                <div class="specs-section">
                    <div class="specs-title">Pressões Recomendadas</div>
                    @if(!empty($article->vehicle_data['pressure_display']))
                    <div class="pressure-display">
                        <div class="pressure-label">Veículo Vazio</div>
                        <div class="pressure-value">{{ $article->vehicle_data['pressure_display'] }}</div>
                    </div>
                    @endif
                    @if(!empty($article->vehicle_data['pressure_loaded_display']))
                    <div class="pressure-display">
                        <div class="pressure-label">Com Carga</div>
                        <div class="pressure-value loaded">{{ $article->vehicle_data['pressure_loaded_display'] }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
        
        <!-- Perguntas Frequentes -->
        @if(!empty($article->faq) && is_array($article->faq) && count($article->faq) > 0)
        <h2>❓ Perguntas Frequentes</h2>
        
        <amp-accordion>
            @foreach($article->faq as $question)
            @if(!empty($question['pergunta']) && !empty($question['resposta']))
            <section>
                <h4>{{ $question['pergunta'] }}</h4>
                <div class="faq-content">
                    <p>{{ $question['resposta'] }}</p>
                </div>
            </section>
            @endif
            @endforeach
        </amp-accordion>
        @endif
        
        <!-- Considerações Finais -->
        @if(!empty($article->final_considerations))
        <h2>📝 Considerações Finais</h2>
        <p>{{ $article->final_considerations }}</p>
        @endif
        
        <!-- Footer info -->
        <div class="article-footer">
            @if(!empty($article->formated_updated_at))
            <p><strong>Atualizado em:</strong> {{ $article->formated_updated_at }}</p>
            @endif
            <p><strong>Por:</strong> Equipe Editorial Mercado Veículos</p>
            <p><a href="{{ route('info.article.show', $article->slug) }}">Ver versão completa do artigo</a></p>
        </div>
    </article>
</div>
@endsection