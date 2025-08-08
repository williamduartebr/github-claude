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
        border-bottom: 2px solid #2563eb;
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
        background-color: #2563eb;
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
    
    /* Destaque da pressão ideal */
    .ideal-pressure-highlight {
        background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        border: 2px solid #2563eb;
        border-radius: 16px;
        padding: 24px;
        margin: 24px 0;
        text-align: center;
        position: relative;
    }
    
    .ideal-pressure-highlight::before {
        content: '🎯';
        font-size: 28px;
        position: absolute;
        top: 16px;
        left: 20px;
    }
    
    .ideal-pressure-title {
        font-size: 20px;
        font-weight: 700;
        color: #1e40af;
        margin-bottom: 16px;
        padding-left: 40px;
    }
    
    .pressure-values-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-top: 16px;
    }
    
    .pressure-value-card {
        background-color: rgba(255, 255, 255, 0.8);
        border-radius: 12px;
        padding: 16px;
        border: 1px solid #93c5fd;
    }
    
    .pressure-position {
        font-size: 14px;
        color: #1e40af;
        font-weight: 600;
        margin-bottom: 8px;
    }
    
    .pressure-value {
        font-size: 24px;
        font-weight: 700;
        color: #1d4ed8;
        margin-bottom: 4px;
    }
    
    .pressure-unit {
        font-size: 12px;
        color: #6b7280;
        font-weight: 500;
    }
    
    /* Especificações por versão */
    .version-specs-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }
    
    .version-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        background-color: #fff;
    }
    
    .version-header {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: white;
        padding: 16px;
        font-weight: 600;
        font-size: 16px;
        text-align: center;
    }
    
    .version-body {
        padding: 20px;
    }
    
    .spec-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        padding: 8px 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 14px;
    }
    
    .spec-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }
    
    .spec-label {
        color: #718096;
        font-weight: 500;
    }
    
    .spec-value {
        font-weight: 600;
        color: #151C25;
        background-color: #eff6ff;
        padding: 4px 8px;
        border-radius: 4px;
        border: 1px solid #bfdbfe;
    }
    
    /* Localização da etiqueta */
    .label-location-card {
        background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
        border: 1px solid #0284c7;
        border-radius: 12px;
        padding: 24px;
        margin: 24px 0;
        position: relative;
    }
    
    .label-location-title {
        font-size: 18px;
        font-weight: 600;
        color: #0c4a6e;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
    }
    
    .label-location-icon {
        margin-right: 12px;
        font-size: 20px;
    }
    
    .location-options {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-top: 16px;
    }
    
    .location-option {
        background-color: rgba(255, 255, 255, 0.8);
        border: 1px solid #7dd3fc;
        border-radius: 8px;
        padding: 12px;
        text-align: center;
    }
    
    .location-priority {
        font-size: 11px;
        background-color: #0284c7;
        color: white;
        padding: 2px 6px;
        border-radius: 10px;
        margin-bottom: 6px;
        display: inline-block;
        font-weight: 600;
    }
    
    .location-description {
        font-size: 13px;
        color: #0c4a6e;
        font-weight: 500;
    }
    
    /* Condições especiais */
    .special-conditions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }
    
    .condition-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        background-color: #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .condition-header {
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        color: white;
        padding: 14px 16px;
        font-weight: 600;
        font-size: 14px;
        text-align: center;
    }
    
    .condition-body {
        padding: 16px;
    }
    
    .condition-pressure {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px;
        margin: 12px 0;
        text-align: center;
    }
    
    .condition-pressure-label {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 4px;
    }
    
    .condition-pressure-value {
        font-size: 18px;
        font-weight: 700;
        color: #4f46e5;
    }
    
    .condition-description {
        font-size: 13px;
        color: #4b5563;
        line-height: 1.6;
    }
    
    /* Conversor de unidades */
    .unit-converter {
        background: linear-gradient(135deg, #ecfdf5, #d1fae5);
        border: 1px solid #10b981;
        border-radius: 12px;
        padding: 24px;
        margin: 24px 0;
    }
    
    .converter-title {
        font-size: 18px;
        font-weight: 600;
        color: #065f46;
        margin-bottom: 16px;
        text-align: center;
    }
    
    .conversion-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 16px;
        margin-bottom: 16px;
    }
    
    .conversion-item {
        background-color: rgba(255, 255, 255, 0.8);
        border: 1px solid #6ee7b7;
        border-radius: 8px;
        padding: 12px;
        text-align: center;
    }
    
    .conversion-unit {
        font-size: 12px;
        color: #065f46;
        font-weight: 600;
        margin-bottom: 4px;
    }
    
    .conversion-value {
        font-size: 16px;
        font-weight: 700;
        color: #047857;
    }
    
    .conversion-note {
        font-size: 12px;
        color: #059669;
        text-align: center;
        font-style: italic;
    }
    
    /* Impacto no desempenho */
    .performance-impact-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }
    
    .impact-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        background-color: #fff;
        text-align: center;
        position: relative;
    }
    
    .impact-card.safety {
        border-left: 4px solid #ef4444;
        background: linear-gradient(135deg, #fef2f2, #fee2e2);
    }
    
    .impact-card.economy {
        border-left: 4px solid #10b981;
        background: linear-gradient(135deg, #ecfdf5, #d1fae5);
    }
    
    .impact-card.performance {
        border-left: 4px solid #f59e0b;
        background: linear-gradient(135deg, #fffbeb, #fef3c7);
    }
    
    .impact-card.comfort {
        border-left: 4px solid #8b5cf6;
        background: linear-gradient(135deg, #faf5ff, #f3e8ff);
    }
    
    .impact-title {
        font-weight: 600;
        margin-bottom: 16px;
        font-size: 16px;
        color: #151C25;
    }
    
    .impact-percentage {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 8px;
        line-height: 1;
    }
    
    .impact-percentage.positive {
        color: #059669;
    }
    
    .impact-percentage.negative {
        color: #dc2626;
    }
    
    .impact-percentage.neutral {
        color: #d97706;
    }
    
    .impact-description {
        font-size: 13px;
        color: #4b5563;
        line-height: 1.4;
    }
    
    /* Alertas importantes */
    .safety-alert {
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 20px;
        border-left: 4px solid;
    }
    
    .safety-alert.critical {
        background-color: #fef2f2;
        border-color: #ef4444;
    }
    
    .safety-alert.warning {
        background-color: #fffbeb;
        border-color: #f59e0b;
    }
    
    .safety-alert.info {
        background-color: #eff6ff;
        border-color: #3b82f6;
    }
    
    .alert-title {
        font-weight: 600;
        margin-bottom: 8px;
        color: #151C25;
        font-size: 16px;
    }
    
    /* Procedimento rápido */
    .quick-procedure {
        background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
        border-radius: 12px;
        padding: 24px;
        margin: 24px 0;
        border: 1px solid #cbd5e1;
    }
    
    .procedure-step {
        display: flex;
        margin-bottom: 20px;
        align-items: flex-start;
    }
    
    .procedure-step:last-child {
        margin-bottom: 0;
    }
    
    .step-number {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 16px;
        flex-shrink: 0;
        font-weight: 700;
        font-size: 16px;
        box-shadow: 0 2px 4px rgba(37, 99, 235, 0.3);
    }
    
    .step-content {
        flex: 1;
    }
    
    .step-content h3 {
        margin: 0 0 8px;
        font-size: 16px;
        font-weight: 600;
        color: #151C25;
    }
    
    .step-content p {
        margin-bottom: 0;
        color: #475569;
        line-height: 1.6;
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
        color: #2563eb;
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
        
        .version-specs-grid,
        .special-conditions-grid {
            grid-template-columns: 1fr;
        }
        
        .pressure-values-grid {
            grid-template-columns: 1fr;
        }
        
        .location-options {
            grid-template-columns: 1fr;
        }
        
        .conversion-grid {
            grid-template-columns: 1fr 1fr;
        }
        
        .performance-impact-grid {
            grid-template-columns: 1fr;
        }
        
        .step-number {
            width: 32px;
            height: 32px;
            font-size: 14px;
        }
        
        .ad-container {
            margin: 24px 0;
            padding: 12px;
        }
        
        .ideal-pressure-title {
            font-size: 18px;
            padding-left: 20px;
        }
        
        .ideal-pressure-highlight::before {
            left: 12px;
            font-size: 24px;
        }
    }
    
    @media (max-width: 480px) {
        h1 {
            font-size: 22px;
        }
        
        h2 {
            font-size: 18px;
        }
        
        .spec-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
        }
        
        .spec-value {
            align-self: flex-end;
        }
        
        .conversion-grid {
            grid-template-columns: 1fr;
        }
        
        .pressure-value {
            font-size: 20px;
        }
        
        .impact-percentage {
            font-size: 28px;
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
        
        <!-- Destaque da Pressão Ideal -->
        @if(!empty($article->ideal_pressure))
        <div class="ideal-pressure-highlight">
            <div class="ideal-pressure-title">Pressão Ideal para {{ $article->vehicle_info['full_name'] ?? 'seu veículo' }}</div>
            <div class="pressure-values-grid">
                @if(!empty($article->ideal_pressure['front_pressure']))
                <div class="pressure-value-card">
                    <div class="pressure-position">🔄 Pneus Dianteiros</div>
                    <div class="pressure-value">{{ str_replace(' PSI', '', $article->ideal_pressure['front_pressure']) }}</div>
                    <div class="pressure-unit">PSI (libras por pol²)</div>
                </div>
                @endif
                @if(!empty($article->ideal_pressure['rear_pressure']))
                <div class="pressure-value-card">
                    <div class="pressure-position">🔙 Pneus Traseiros</div>
                    <div class="pressure-value">{{ str_replace(' PSI', '', $article->ideal_pressure['rear_pressure']) }}</div>
                    <div class="pressure-unit">PSI (libras por pol²)</div>
                </div>
                @endif
            </div>
            @if(!empty($article->ideal_pressure['observation']))
            <p style="margin-top: 16px; color: #1e40af; font-weight: 500; font-size: 14px;">{{ $article->ideal_pressure['observation'] }}</p>
            @endif
        </div>
        @endif
        
        <!-- 🥇 ANÚNCIO 1: Após pressão ideal -->
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
        
        <!-- Especificações por Versão -->
        @if(!empty($article->vehicle_versions))
        <h2>🚗 Especificações por Versão</h2>
        
        <div class="version-specs-grid">
            @foreach($article->vehicle_versions as $version)
            <div class="version-card">
                <div class="version-header">{{ $version['name'] }}</div>
                <div class="version-body">
                    @if(!empty($version['tire_size']))
                    <div class="spec-item">
                        <span class="spec-label">Medida dos Pneus:</span>
                        <span class="spec-value">{{ $version['tire_size'] }}</span>
                    </div>
                    @endif
                    @if(!empty($version['front_pressure']))
                    <div class="spec-item">
                        <span class="spec-label">Pressão Dianteira:</span>
                        <span class="spec-value">{{ $version['front_pressure'] }}</span>
                    </div>
                    @endif
                    @if(!empty($version['rear_pressure']))
                    <div class="spec-item">
                        <span class="spec-label">Pressão Traseira:</span>
                        <span class="spec-value">{{ $version['rear_pressure'] }}</span>
                    </div>
                    @endif
                    @if(!empty($version['engine']))
                    <div class="spec-item">
                        <span class="spec-label">Motor:</span>
                        <span class="spec-value">{{ $version['engine'] }}</span>
                    </div>
                    @endif
                    @if(!empty($version['fuel_type']))
                    <div class="spec-item">
                        <span class="spec-label">Combustível:</span>
                        <span class="spec-value">{{ $version['fuel_type'] }}</span>
                    </div>
                    @endif
                    @if(!empty($version['weight']))
                    <div class="spec-item">
                        <span class="spec-label">Peso:</span>
                        <span class="spec-value">{{ $version['weight'] }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
        
        <!-- Localização da Etiqueta -->
        @if(!empty($article->label_location))
        <h2>📍 Onde Encontrar a Etiqueta de Pressão</h2>
        
        <div class="label-location-card">
            <div class="label-location-title">
                <span class="label-location-icon">🔍</span>
                Localizações Mais Comuns
            </div>
            <p style="color: #0c4a6e; margin-bottom: 16px;">{{ $article->label_location['description'] ?? 'A etiqueta com as pressões recomendadas está localizada em um destes locais:' }}</p>
            
            @if(!empty($article->label_location['locations']))
            <div class="location-options">
                @foreach($article->label_location['locations'] as $location)
                <div class="location-option">
                    <div class="location-priority">{{ $location['priority'] ?? 'Comum' }}</div>
                    <div class="location-description">{{ $location['location'] }}</div>
                </div>
                @endforeach
            </div>
            @else
            <div class="location-options">
                <div class="location-option">
                    <div class="location-priority">Principal</div>
                    <div class="location-description">Batente da porta do motorista</div>
                </div>
                <div class="location-option">
                    <div class="location-priority">Alternativo</div>
                    <div class="location-description">Coluna B (entre as portas)</div>
                </div>
                <div class="location-option">
                    <div class="location-priority">Raro</div>
                    <div class="location-description">Porta do combustível</div>
                </div>
                <div class="location-option">
                    <div class="location-priority">Manual</div>
                    <div class="location-description">Manual do proprietário</div>
                </div>
            </div>
            @endif
            
            @if(!empty($article->label_location['tip']))
            <div class="info-note" style="margin-top: 16px;">
                <strong>💡 Dica:</strong> {{ $article->label_location['tip'] }}
            </div>
            @endif
        </div>
        @endif
        
        <!-- Condições Especiais de Uso -->
        @if(!empty($article->special_conditions) && is_array($article->special_conditions))
        <h2>⚖️ Condições Especiais de Uso</h2>
        
        <div class="special-conditions-grid">
            @foreach($article->special_conditions as $condition)
            <div class="condition-card">
                <div class="condition-header">{{ $condition['condition'] ?? 'Condição Especial' }}</div>
                <div class="condition-body">
                    <div class="condition-pressure">
                        <div class="condition-pressure-label">Pressão Recomendada</div>
                        <div class="condition-pressure-value">{{ $condition['recommended_pressure'] ?? 'Consulte manual' }}</div>
                    </div>
                    <div class="condition-description">{{ $condition['description'] ?? '' }}</div>
                    @if(!empty($condition['warning']))
                    <div class="info-note" style="margin-top: 12px;">
                        <strong>⚠️ Atenção:</strong> {{ $condition['warning'] }}
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
        
        <!-- 🥇 ANÚNCIO 2: Após condições especiais -->
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
        
        <!-- Conversor de Unidades -->
        @if(!empty($article->unit_conversion) && is_array($article->unit_conversion))
        <h2>🔄 Conversão de Unidades</h2>
        
        <div class="unit-converter">
            <div class="converter-title">Tabela de Conversão para {{ $article->ideal_pressure['front_pressure'] ?? '32 PSI' }}</div>
            <div class="conversion-grid">
                @foreach($article->unit_conversion as $unit)
                <div class="conversion-item">
                    <div class="conversion-unit">{{ $unit['unit'] ?? 'N/A' }}</div>
                    <div class="conversion-value">{{ $unit['value'] ?? 'N/A' }}</div>
                </div>
                @endforeach
            </div>
            <div class="conversion-note">
                *Use sempre a unidade especificada no seu manômetro
            </div>
        </div>
        @endif
        
        <!-- Impacto no Desempenho -->
        @if(!empty($article->performance_impact) && is_array($article->performance_impact))
        <h2>📊 Impacto da Pressão Correta no Desempenho</h2>
        
        <div class="performance-impact-grid">
            @foreach($article->performance_impact as $impact)
            <div class="impact-card {{ strtolower($impact['category'] ?? 'general') }}">
                <div class="impact-title">{{ $impact['title'] ?? 'Benefício' }}</div>
                <div class="impact-percentage {{ $impact['trend'] ?? 'neutral' }}">
                    {{ $impact['percentage'] ?? '0%' }}
                </div>
                <div class="impact-description">{{ $impact['description'] ?? '' }}</div>
            </div>
            @endforeach
        </div>
        
        <div class="info-note">
            <strong>📈 Economia comprovada:</strong> Manter a pressão correta pode gerar economia de até R$ 800 por ano em combustível e aumentar a vida útil dos pneus em até 40%.
        </div>
        @endif
        
        <!-- Alertas de Segurança -->
        @if(!empty($article->safety_alerts))
        <h2>⚠️ Alertas Importantes</h2>
        
        @foreach($article->safety_alerts as $alert)
        <div class="safety-alert {{ strtolower($alert['type']) }}">
            <div class="alert-title">
                @switch(strtolower($alert['type']))
                    @case('crítico')
                        🚨 {{ $alert['title'] }}
                        @break
                    @case('importante')
                        ⚠️ {{ $alert['title'] }}
                        @break
                    @default
                        ℹ️ {{ $alert['title'] }}
                @endswitch
            </div>
            <p>{{ $alert['description'] ?? '' }}</p>
            @if(!empty($alert['consequence']))
            <p><strong>Consequência:</strong> {{ $alert['consequence'] }}</p>
            @endif
        </div>
        @endforeach
        @endif
        
        <!-- Procedimento Rápido de Verificação -->
        @if(!empty($article->quick_procedure))
        <h2>⚡ Procedimento Rápido de Verificação</h2>
        
        <div class="quick-procedure">
            @foreach($article->quick_procedure as $step)
            <div class="procedure-step">
                <div class="step-number">{{ $step['number'] }}</div>
                <div class="step-content">
                    <h3>{{ $step['title'] }}</h3>
                    <p>{{ $step['description'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
        
        <div class="info-note">
            <strong>⏰ Frequência recomendada:</strong> Verifique a pressão dos pneus pelo menos uma vez por mês, sempre com os pneus frios (antes de rodar ou após 3 horas parado).
        </div>
        @endif
        
        <!-- 🥈 ANÚNCIO 3: Após procedimento -->
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
        
        <!-- Cuidados Específicos para o Brasil -->
        @if(!empty($article->brazil_specific_care))
        <h2>🇧🇷 Cuidados Específicos para o Brasil</h2>
        
        @foreach($article->brazil_specific_care as $careGroup)
        <div class="safety-alert info">
            <div class="alert-title">{{ $careGroup['title'] }}</div>
            <p>{{ $careGroup['description'] ?? '' }}</p>
            @if(!empty($careGroup['tips']))
            <ul style="margin: 12px 0; padding-left: 20px;">
                @foreach($careGroup['tips'] as $tip)
                <li style="margin-bottom: 6px; color: #1e40af;">{{ $tip }}</li>
                @endforeach
            </ul>
            @endif
        </div>
        @endforeach
        @endif
        
        <!-- Quando Ajustar a Pressão -->
        @if(!empty($article->pressure_adjustment_guide))
        <h2>🎯 Quando e Como Ajustar a Pressão</h2>
        
        @foreach($article->pressure_adjustment_guide as $guide)
        <div class="safety-alert {{ strtolower($guide['type'] ?? 'info') }}">
            <div class="alert-title">{{ $guide['situation'] }}</div>
            <p><strong>Ação:</strong> {{ $guide['action'] ?? '' }}</p>
            <p><strong>Pressão:</strong> {{ $guide['recommended_pressure'] ?? '' }}</p>
            @if(!empty($guide['explanation']))
            <p><strong>Por quê:</strong> {{ $guide['explanation'] }}</p>
            @endif
        </div>
        @endforeach
        @endif
        
        <!-- Perguntas Frequentes -->
        @if(!empty($article->faq))
        <h2>❓ Perguntas Frequentes sobre {{ $article->vehicle_info['full_name'] ?? 'Pressão dos Pneus' }}</h2>
        
        <amp-accordion>
            @foreach($article->faq as $faq)
            <section>
                <h4>{{ $faq['pergunta'] }}</h4>
                <div class="faq-content">
                    <p>{{ $faq['resposta'] }}</p>
                </div>
            </section>
            @endforeach
        </amp-accordion>
        @endif
        
        <!-- Resumo das Vantagens -->
        @if(!empty($article->benefits_summary))
        <h2>✅ Resumo dos Benefícios da Pressão Correta</h2>
        
        <div class="performance-impact-grid">
            @foreach($article->benefits_summary as $benefit)
            <div class="impact-card {{ strtolower($benefit['category']) }}">
                <div class="impact-title">{{ $benefit['title'] }}</div>
                <div class="impact-percentage positive">{{ $benefit['value'] }}</div>
                <div class="impact-description">{{ $benefit['description'] }}</div>
            </div>
            @endforeach
        </div>
        @endif
        
        <!-- Ferramentas Recomendadas -->
        @if(!empty($article->recommended_tools))
        <h2>🛠️ Ferramentas Recomendadas</h2>
        
        <div class="special-conditions-grid">
            @foreach($article->recommended_tools as $tool)
            <div class="condition-card">
                <div class="condition-header">{{ $tool['name'] }}</div>
                <div class="condition-body">
                    <div class="condition-pressure">
                        <div class="condition-pressure-label">Preço Médio</div>
                        <div class="condition-pressure-value">{{ $tool['price_range'] }}</div>
                    </div>
                    <div class="condition-description">{{ $tool['description'] ?? '' }}</div>
                    @if(!empty($tool['recommendation']))
                    <div class="info-note" style="margin-top: 12px;">
                        <strong>💡 Nossa recomendação:</strong> {{ $tool['recommendation'] }}
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
        
        <!-- Considerações Finais -->
        @if(!empty($article->final_considerations))
        <h2>📝 Considerações Finais</h2>
        <div style="background: linear-gradient(135deg, #eff6ff, #dbeafe); padding: 20px; border-radius: 8px; border-left: 4px solid #2563eb;">
            <p style="color: #1e40af; line-height: 1.7; font-weight: 500;">{{ $article->final_considerations }}</p>
        </div>
        @endif
        
        <!-- Lembrete de Manutenção -->
        <div class="ideal-pressure-highlight">
            <div class="ideal-pressure-title">📅 Lembre-se Sempre</div>
            <div style="text-align: left; padding-left: 40px;">
                <p style="color: #1e40af; margin-bottom: 8px;"><strong>✓</strong> Verifique mensalmente a pressão</p>
                <p style="color: #1e40af; margin-bottom: 8px;"><strong>✓</strong> Sempre com pneus frios</p>
                <p style="color: #1e40af; margin-bottom: 8px;"><strong>✓</strong> Inclua o estepe na verificação</p>
                <p style="color: #1e40af; margin-bottom: 0;"><strong>✓</strong> Ajuste conforme a carga do veículo</p>
            </div>
        </div>
        
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