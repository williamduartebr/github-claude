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
        border-bottom: 2px solid #DC2626;
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
        background-color: #DC2626;
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
    
    /* Tabela de pressões */
    .pressure-table-container {
        background: linear-gradient(135deg, rgba(220, 38, 38, 0.05), rgba(220, 38, 38, 0.1));
        border-radius: 12px;
        padding: 20px;
        margin: 24px 0;
        overflow-x: auto;
        border: 1px solid rgba(220, 38, 38, 0.2);
    }
    
    .pressure-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        margin: 0;
        min-width: 600px;
    }
    
    .pressure-table th {
        background-color: #DC2626;
        color: white;
        padding: 12px 8px;
        text-align: center;
        font-weight: 600;
        font-size: 13px;
    }
    
    .pressure-table th:first-child {
        border-radius: 8px 0 0 0;
        text-align: left;
        padding-left: 12px;
    }
    
    .pressure-table th:last-child {
        border-radius: 0 8px 0 0;
    }
    
    .pressure-table td {
        padding: 10px 8px;
        text-align: center;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .pressure-table td:first-child {
        text-align: left;
        font-weight: 500;
        padding-left: 12px;
        color: #374151;
    }
    
    .pressure-table tr:nth-child(even) {
        background-color: rgba(255, 255, 255, 0.5);
    }
    
    .pressure-table tr:hover {
        background-color: rgba(220, 38, 38, 0.05);
    }
    
    /* Alertas de segurança */
    .safety-alert {
        margin: 24px 0;
        border-radius: 8px;
        padding: 16px;
        border-left: 4px solid;
    }
    
    .safety-alert.danger {
        background-color: #fef2f2;
        border-left-color: #dc2626;
        color: #991b1b;
    }
    
    .safety-alert.warning {
        background-color: #fef3c7;
        border-left-color: #f59e0b;
        color: #92400e;
    }
    
    .safety-alert.info {
        background-color: #dbeafe;
        border-left-color: #3b82f6;
        color: #1e40af;
    }
    
    .alert-title {
        font-weight: 600;
        font-size: 16px;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    /* Cards de recomendação */
    .recommendation-card {
        background: linear-gradient(135deg, #f8fafc, #e2e8f0);
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 16px;
        border: 1px solid #cbd5e0;
    }
    
    .recommendation-title {
        font-weight: 600;
        font-size: 16px;
        color: #2d3748;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .recommendation-icon {
        border-radius: 50%;
        flex-shrink: 0;
    }
    
    /* Sistema TPMS */
    .tpms-card {
        background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
        border-radius: 8px;
        overflow: hidden;
        margin: 24px 0;
        border: 1px solid #0ea5e9;
    }
    
    .tpms-header {
        background-color: #0ea5e9;
        color: white;
        padding: 12px 16px;
        font-weight: 600;
        font-size: 16px;
    }
    
    .tpms-body {
        padding: 16px;
    }
    
    .tpms-feature {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 8px;
    }
    
    .tpms-bullet {
        width: 6px;
        height: 6px;
        background-color: #0ea5e9;
        border-radius: 50%;
        flex-shrink: 0;
    }
    
    /* Procedimento de calibragem */
    .calibration-steps {
        background: #f8fafc;
        border-radius: 8px;
        padding: 20px;
        margin: 24px 0;
    }
    
    .step-card {
        background: white;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 12px;
        display: flex;
        align-items: flex-start;
        gap: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    .step-card:last-child {
        margin-bottom: 0;
    }
    
    .step-number {
        background: linear-gradient(135deg, #DC2626, #991b1b);
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 16px;
        flex-shrink: 0;
    }
    
    .step-content {
        flex: 1;
    }
    
    .step-title {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 4px;
    }
    
    .step-description {
        color: #4a5568;
        font-size: 14px;
        line-height: 1.5;
        margin: 0;
    }
    
    /* Comparativo de impactos */
    .impact-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 16px;
        margin: 24px 0;
    }
    
    .impact-card {
        background: white;
        border-radius: 8px;
        padding: 16px;
        border: 2px solid;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .impact-card.subcalibrado {
        border-color: #dc2626;
        background: linear-gradient(135deg, #fef2f2, #fee2e2);
    }
    
    .impact-card.ideal {
        border-color: #059669;
        background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    }
    
    .impact-card.sobrecalibrado {
        border-color: #f59e0b;
        background: linear-gradient(135deg, #fffbeb, #fef3c7);
    }
    
    .impact-header {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
    }
    
    .impact-title {
        font-weight: 600;
        font-size: 16px;
        color: #1f2937;
    }
    
    .impact-content p {
        margin-bottom: 8px;
        font-size: 14px;
        color: #374151;
    }
    
    .impact-content strong {
        color: #1f2937;
    }
    
    /* FAQ Accordion */
    amp-accordion {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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
        background-color: #fafafa;
        font-weight: 600;
        color: #151C25;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }
    
    amp-accordion h4:hover {
        background-color: #f5f5f5;
    }
    
    amp-accordion .faq-content {
        padding: 16px;
        background-color: #fff;
        color: #4a5568;
        line-height: 1.6;
    }
    
     /* Nota informativa */
    .info-note {
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.05), rgba(37, 99, 235, 0.1));
        padding: 16px;
        border-radius: 8px;
        font-size: 14px;
        margin: 24px 0;
        border-left: 4px solid #2563eb;
        color: #1e40af;
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
        color: #DC2626;
        text-decoration: none;
    }
    
    .article-footer a:hover {
        text-decoration: underline;
    }
    
    /* Ícones SVG inline (para performance) */
    .icon-urban {
        fill: #4f46e5;
    }
    
    .icon-highway {
        fill: #059669;
    }
    
    .icon-family {
        fill: #dc2626;
    }
    
    .icon-cargo {
        fill: #f59e0b;
    }
    
    .icon-check {
        fill: #059669;
    }
    
    .icon-alert {
        fill: #dc2626;
    }
    
    .icon-info {
        fill: #3b82f6;
    }
    
    .icon-wrench {
        fill: #6b7280;
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
        
        .pressure-table {
            font-size: 12px;
            min-width: 500px;
        }
        
        .pressure-table th,
        .pressure-table td {
            padding: 8px 4px;
        }
        
        .recommendation-card,
        .step-card {
            padding: 12px;
        }
        
        .step-number {
            width: 32px;
            height: 32px;
            font-size: 14px;
        }
        
        .impact-grid {
            grid-template-columns: 1fr;
        }
        
        .ad-container {
            margin: 20px 0;
            padding: 12px;
        }
    }
    
    @media (max-width: 480px) {
        .pressure-table {
            min-width: 400px;
            font-size: 11px;
        }
        
        .step-card {
            flex-direction: column;
            text-align: center;
        }
        
        .step-number {
            align-self: center;
        }
    }
</style>
@endsection

@section('structured-data')
<script type="application/ld+json">
{!! json_encode($article->structured_data ?? []) !!}
</script>
@endsection

@section('content')
<article class="container">
    <!-- Título principal -->
    <h1>{{ $article->seo_data['h1'] ?? $article->title }}</h1>
    
    <!-- Meta informações -->
    <div class="article-meta">
        <span>📅 Atualizado em {{ $article->updated_at->format('d/m/Y') }}</span> • 
        <span>⏱️ {{ $article->reading_time ?? 5 }} min de leitura</span>
    </div>
    
    <!-- Introdução -->
    @if(!empty($article->introduction))
    <div class="intro-content">
        {!! $article->introduction !!}
    </div>
    @endif
    
    <!-- 🥇 ANÚNCIO 1: Após introdução -->
    <div class="ad-container">
        <div class="ad-label">Publicidade</div>
        <amp-ad width="100vw" height="320"
            type="adsense"
            data-ad-client="{{ config('services.google_adsense.id') }}"
            data-ad-slot="7284018545"
            data-auto-format="rspv"
            data-full-width>
            <div overflow></div>
        </amp-ad>
    </div>
    
    <!-- Tabela de Pressões Oficial -->
    @if(!empty($article->pressure_table))
    <h2>📋 Tabela Oficial de Pressões</h2>
    
    <div class="pressure-table-container">
        <table class="pressure-table">
            <thead>
                <tr>
                    <th>Condição de Uso</th>
                    <th>Dianteira</th>
                    <th>Traseira</th>
                    <th>Estepe</th>
                </tr>
            </thead>
            <tbody>
                @foreach($article->pressure_table as $condition)
                <tr>
                    <td>{{ $condition['condition'] ?? '' }}</td>
                    <td>{{ $condition['front_pressure'] ?? '' }}</td>
                    <td>{{ $condition['rear_pressure'] ?? '' }}</td>
                    <td>{{ $condition['spare_pressure'] ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
    
    <!-- Procedimento de Calibragem -->
    @if(!empty($article->calibration_procedure))
    <h2>🔧 Procedimento Correto de Calibragem</h2>
    
    <div class="calibration-steps">
        @foreach($article->calibration_procedure as $index => $step)
        <div class="step-card">
            <div class="step-number">{{ $index + 1 }}</div>
            <div class="step-content">
                <div class="step-title">{{ $step['title'] ?? "Passo " . ($index + 1) }}</div>
                <p class="step-description">{{ $step['description'] ?? '' }}</p>
            </div>
        </div>
        @endforeach
    </div>
    @endif
    
    <!-- Sistema TPMS -->
    @if(!empty($article->tpms_system) && $article->tpms_system['has_tpms'])
    <h2>📡 Sistema TPMS (Monitoramento de Pressão)</h2>
    
    <div class="tpms-card">
        <div class="tpms-header">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="3"></circle>
                <path d="M12 1v6m0 6v6m11-7h-6m-6 0H1"></path>
            </svg>
            {{ $article->tpms_system['type'] ?? 'Sistema TPMS Ativo' }}
        </div>
        <div class="tpms-body">
            <h3>Como Funciona:</h3>
            @if(!empty($article->tpms_system['features']))
            @foreach($article->tpms_system['features'] as $feature)
            <div class="tpms-feature">
                <div class="tpms-bullet"></div>
                <p>{{ $feature }}</p>
            </div>
            @endforeach
            @endif
            
            @if(!empty($article->tpms_system['reset_procedure']))
            <h3>Procedimento de Reset:</h3>
            @foreach($article->tpms_system['reset_procedure'] as $index => $step)
            <div class="tpms-feature">
                <div class="tpms-bullet"></div>
                <p><strong>{{ $index + 1 }}.</strong> {{ $step }}</p>
            </div>
            @endforeach
            @endif
        </div>
    </div>
    @endif
    
    <!-- 🥇 ANÚNCIO 2: Após TPMS -->
    <div class="ad-container">
        <div class="ad-label">Publicidade</div>
        <amp-ad width="100vw" height="320"
            type="adsense"
            data-ad-client="{{ config('services.google_adsense.id') }}"
            data-ad-slot="8344586349"
            data-auto-format="rspv"
            data-full-width>
            <div overflow></div>
        </amp-ad>
    </div>
    
    <!-- Recomendações por Uso -->
    @if(!empty($article->usage_recommendations))
    <h2>💡 Recomendações por Versão e Uso</h2>
    
    @foreach($article->usage_recommendations as $recommendation)
    <div class="recommendation-card">
        <div class="recommendation-title">
            @switch($recommendation['category'] ?? '')
                @case('Uso Urbano Diário')
                    <svg class="recommendation-icon icon-urban" width="24" height="24" viewBox="0 0 24 24">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                        <polyline points="3.27,6.96 12,12.01 20.73,6.96"/>
                        <line x1="12" y1="22.08" x2="12" y2="12"/>
                    </svg>
                    @break
                @case('Rodovias e Estradas')
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="rgba(100,205,138,1)"><path d="M4 6.14286V18.9669L9.06476 16.7963L15.0648 19.7963L20 17.6812V4.85714L21.303 4.2987C21.5569 4.18992 21.8508 4.30749 21.9596 4.56131C21.9862 4.62355 22 4.69056 22 4.75827V19L15 22L9 19L2.69696 21.7013C2.44314 21.8101 2.14921 21.6925 2.04043 21.4387C2.01375 21.3765 2 21.3094 2 21.2417V7L4 6.14286ZM16.2426 11.2426L12 15.4853L7.75736 11.2426C5.41421 8.89949 5.41421 5.10051 7.75736 2.75736C10.1005 0.414214 13.8995 0.414214 16.2426 2.75736C18.5858 5.10051 18.5858 8.89949 16.2426 11.2426ZM12 12.6569L14.8284 9.82843C16.3905 8.26633 16.3905 5.73367 14.8284 4.17157C13.2663 2.60948 10.7337 2.60948 9.17157 4.17157C7.60948 5.73367 7.60948 8.26633 9.17157 9.82843L12 12.6569Z"></path></svg>
                    @break
                @case('Família Completa')
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="rgba(234,113,46,1)"><path d="M12 10C14.2091 10 16 8.20914 16 6 16 3.79086 14.2091 2 12 2 9.79086 2 8 3.79086 8 6 8 8.20914 9.79086 10 12 10ZM5.5 13C6.88071 13 8 11.8807 8 10.5 8 9.11929 6.88071 8 5.5 8 4.11929 8 3 9.11929 3 10.5 3 11.8807 4.11929 13 5.5 13ZM21 10.5C21 11.8807 19.8807 13 18.5 13 17.1193 13 16 11.8807 16 10.5 16 9.11929 17.1193 8 18.5 8 19.8807 8 21 9.11929 21 10.5ZM12 11C14.7614 11 17 13.2386 17 16V22H7V16C7 13.2386 9.23858 11 12 11ZM5 15.9999C5 15.307 5.10067 14.6376 5.28818 14.0056L5.11864 14.0204C3.36503 14.2104 2 15.6958 2 17.4999V21.9999H5V15.9999ZM22 21.9999V17.4999C22 15.6378 20.5459 14.1153 18.7118 14.0056 18.8993 14.6376 19 15.307 19 15.9999V21.9999H22Z"></path></svg>
                    @break
                @default
                    <svg class="recommendation-icon icon-info" width="24" height="24" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 16v-4m0-4h.01"/>
                    </svg>
            @endswitch
            {{ $recommendation['category'] }}
        </div>
        <p><strong>Pressão:</strong> {{ $recommendation['recommended_pressure'] ?? 'Conforme tabela' }}</p>
        <p>{{ $recommendation['description'] ?? '' }}</p>
        @if(!empty($recommendation['tip']))
        <div class="safety-alert info">
            <div class="alert-title">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                Dica técnica:
            </div>
            {{ $recommendation['tip'] }}
        </div>
        @endif
    </div>
    @endforeach
    @endif
    
    <!-- Comparativo de Impactos -->
    @if(!empty($article->calibration_impacts))
    <h2>⚖️ Comparativo de Impactos da Pressão</h2>
    
    <div class="impact-grid">
        @if(!empty($article->calibration_impacts['under_inflated']))
        <div class="impact-card subcalibrado">
            <div class="impact-header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="8" y1="12" x2="16" y2="12"/>
                </svg>
                <div class="impact-title">⬇️ Pressão Baixa (Subcalibrado)</div>
            </div>
            <div class="impact-content">
                @php $underInflated = $article->calibration_impacts['under_inflated'] @endphp
                @if($underInflated['fuel_consumption'])
                <p><strong>Consumo:</strong> {{ $underInflated['fuel_consumption'] }}</p>
                @endif
                @if($underInflated['wear_pattern'])
                <p><strong>Desgaste:</strong> {{ $underInflated['wear_pattern'] }}</p>
                @endif
                @if($underInflated['handling'])
                <p><strong>Dirigibilidade:</strong> {{ $underInflated['handling'] }}</p>
                @endif
                @if($underInflated['temperature'])
                <p><strong>Temperatura:</strong> {{ $underInflated['temperature'] }}</p>
                @endif
            </div>
        </div>
        @endif
        
        @if(!empty($article->calibration_impacts['over_inflated']))
        <div class="impact-card sobrecalibrado">
            <div class="impact-header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                <div class="impact-title">🔺 Pressão Alta (Sobrecalibrado)</div>
            </div>
            <div class="impact-content">
                @php $overInflated = $article->calibration_impacts['over_inflated'] @endphp
                @if($overInflated['fuel_consumption'])
                <p><strong>Consumo:</strong> {{ $overInflated['fuel_consumption'] }}</p>
                @endif
                @if($overInflated['wear_pattern'])
                <p><strong>Desgaste:</strong> {{ $overInflated['wear_pattern'] }}</p>
                @endif
                @if($overInflated['handling'])
                <p><strong>Dirigibilidade:</strong> {{ $overInflated['handling'] }}</p>
                @endif
                @if($overInflated['comfort'])
                <p><strong>Conforto:</strong> {{ $overInflated['comfort'] }}</p>
                @endif
            </div>
        </div>
        @endif
        
        @if(!empty($article->calibration_impacts['ideal_calibration']))
        <div class="impact-card ideal">
            <div class="impact-header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22,4 12,14.01 9,11.01"/>
                </svg>
                <div class="impact-title">✅ Calibragem Ideal</div>
            </div>
            <div class="impact-content">
                @php $ideal = $article->calibration_impacts['ideal_calibration'] @endphp
                @if($ideal['fuel_consumption'])
                <p><strong>Consumo:</strong> {{ $ideal['fuel_consumption'] }}</p>
                @endif
                @if($ideal['wear_pattern'])
                <p><strong>Desgaste:</strong> {{ $ideal['wear_pattern'] }}</p>
                @endif
                @if($ideal['handling'])
                <p><strong>Dirigibilidade:</strong> {{ $ideal['handling'] }}</p>
                @endif
                @if($ideal['safety'])
                <p><strong>Segurança:</strong> {{ $ideal['safety'] }}</p>
                @endif
            </div>
        </div>
        @endif
    </div>
    @endif
    
    <!-- Dicas de Manutenção -->
    @if(!empty($article->maintenance_tips))
    <h2>🛠️ Dicas para Maximizar Economia e Durabilidade</h2>
    
    @foreach($article->maintenance_tips as $tipGroup)
    <div class="recommendation-card">
        <div class="recommendation-title">
            @switch($tipGroup['icon_class'] ?? 'wrench')
                @case('frequency')
                @case('clock')
                    <svg class="recommendation-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12,6 12,12 16,14"/>
                    </svg>
                    @break
                @case('equipment')
                @case('tool')
                    <svg class="recommendation-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
                        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                    </svg>
                    @break
                @case('care')
                @case('shield')
                    <svg class="recommendation-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    @break
                @case('temperature')
                @case('thermometer')
                    <svg class="recommendation-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
                        <path d="M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z"/>
                    </svg>
                    @break
                @default
                    <svg class="recommendation-icon icon-wrench" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
                        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                    </svg>
            @endswitch
            {{ $tipGroup['category'] ?? 'Dica Importante' }}
        </div>
        
        @if(!empty($tipGroup['items']))
        @foreach($tipGroup['items'] as $item)
        <div class="tpms-feature">
            <div class="tpms-bullet"></div>
            <p>{{ $item }}</p>
        </div>
        @endforeach
        @endif
        
        @if(!empty($tipGroup['description']))
        <p>{{ $tipGroup['description'] }}</p>
        @endif
        
        @if(!empty($tipGroup['recommendation']))
        <div class="safety-alert info">
            <div class="alert-title">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                Recomendação:
            </div>
            {{ $tipGroup['recommendation'] }}
        </div>
        @endif
    </div>
    @endforeach
    @endif
    
    <!-- 🥇 ANÚNCIO 3: Após dicas de manutenção -->
    <div class="ad-container">
        <div class="ad-label">Publicidade</div>
        <amp-ad width="100vw" height="320"
            type="adsense"
            data-ad-client="{{ config('services.google_adsense.id') }}"
            data-ad-slot="9856471203"
            data-auto-format="rspv"
            data-full-width>
            <div overflow></div>
        </amp-ad>
    </div>
    
    <!-- Equipamentos Necessários -->
    @if(!empty($article->required_equipment))
    <h2>🔧 Equipamentos Necessários</h2>
    
    @foreach($article->required_equipment as $equipment)
    <div class="recommendation-card">
        <div class="recommendation-title">
            @switch($equipment['type'] ?? '')
                @case('Calibrador Digital')
                    <svg class="recommendation-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                        <line x1="8" y1="21" x2="16" y2="21"/>
                        <line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                    @break
                @case('Compressor Portátil')
                    <svg class="recommendation-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polygon points="10,8 16,12 10,16 10,8"/>
                    </svg>
                    @break
                @case('Kit de Reparo')
                    <svg class="recommendation-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2">
                        <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    @break
                @default
                    <svg class="recommendation-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
                        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                    </svg>
            @endswitch
            {{ $equipment['name'] ?? $equipment['type'] ?? 'Equipamento' }}
        </div>
        
        @if(!empty($equipment['importance']))
        <div class="equipment-importance {{ strtolower(str_replace(' ', '-', $equipment['importance'])) }}">
            <strong>Importância:</strong> {{ $equipment['importance'] }}
        </div>
        @endif
        
        @if(!empty($equipment['characteristics']))
        <p><strong>Características:</strong> {{ $equipment['characteristics'] }}</p>
        @endif
        
        @if(!empty($equipment['average_price']))
        <p style="color: #059669; font-weight: 600;"><strong>Preço médio:</strong> {{ $equipment['average_price'] }}</p>
        @endif
        
        @if(!empty($equipment['recommendation']))
        <div class="safety-alert info">
            <div class="alert-title">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                Recomendação:
            </div>
            {{ $equipment['recommendation'] }}
        </div>
        @endif
    </div>
    @endforeach
    @endif
    
    <!-- Alertas Críticos -->
    @if(!empty($article->critical_alerts))
    <h2>⚠️ Alertas de Segurança</h2>
    
    @foreach($article->critical_alerts as $alert)
    <div class="safety-alert {{ $alert['type'] ?? 'danger' }}">
        <div class="alert-title">
            @switch($alert['type'] ?? 'danger')
                @case('critical')
                @case('danger')
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/>
                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                    @break
                @case('warning')
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    @break
                @case('info')
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 16v-4m0-4h.01"/>
                    </svg>
                    @break
            @endswitch
            {{ $alert['title'] ?? 'Atenção Importante' }}
        </div>
        <p>{{ $alert['message'] ?? $alert['description'] ?? '' }}</p>
        
        @if(!empty($alert['consequences']))
        <p><strong>Consequências:</strong> {{ $alert['consequences'] }}</p>
        @endif
        
        @if(!empty($alert['action']))
        <p><strong>Ação recomendada:</strong> {{ $alert['action'] }}</p>
        @endif
    </div>
    @endforeach
    @endif
    
    <!-- FAQ -->
    @if(!empty($article->faq))
    <h2>❓ Perguntas Frequentes</h2>

    <amp-accordion expand-single-section>
        @foreach($article->faq as $faq)
        <section>
            <h4>
                {{ $faq['pergunta'] }}</h4>
            <div class="faq-content">
                <p>{{ $faq['resposta'] }}</p>
            </div>
        </section>
        @endforeach
    </amp-accordion>

    @endif
    
    <!-- 🥇 ANÚNCIO 4: Antes das considerações finais -->
    <div class="ad-container">
        <div class="ad-label">Publicidade</div>
        <amp-ad width="100vw" height="320"
            type="adsense"
            data-ad-client="{{ config('services.google_adsense.id') }}"
            data-ad-slot="4527893610"
            data-auto-format="rspv"
            data-full-width>
            <div overflow></div>
        </amp-ad>
    </div>
    
    <!-- Considerações Finais -->
    @if(!empty($article->final_considerations))
    <h2>📝 Considerações Finais</h2>
    
    <div class="info-note">
        {!! $article->final_considerations !!}
    </div>
    @endif
    
    <!-- Nota informativa -->
    @include('auto-info-center::article.partials.info_note_manual')
    
    <!-- Footer info -->
    <div class="article-footer">
        @if(!empty($article->formated_updated_at))
        <p><strong>📅 Atualizado em:</strong> {{ $article->formated_updated_at }}</p>
        @endif
        <p><strong>✍️ Por:</strong> Equipe Editorial Mercado Veículos</p>
        <p><a href="{{ route('info.article.show', $article->slug) }}">🔗 Ver versão completa do artigo</a></p>
    </div>
</article>
@endsection