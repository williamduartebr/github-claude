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
        
        <!-- ✅ CORREÇÃO: Destaque da Pressão Ideal usando dados corretos -->
        @if(!empty($article->tire_specifications_by_version) && count($article->tire_specifications_by_version) > 0)
        @php $mainVersion = $article->tire_specifications_by_version[0] @endphp
        <div class="ideal-pressure-highlight">
            <div class="ideal-pressure-title">Pressão Ideal para {{ $article->vehicle_info['full_name'] ?? 'seu veículo' }}</div>
            <div class="pressure-values-grid">
                <div class="pressure-value-card">
                    <div class="pressure-position">🔄 Pneus Dianteiros</div>
                    <div class="pressure-value">{{ str_replace(' PSI', '', $mainVersion['front_normal']) }}</div>
                    <div class="pressure-unit">PSI (libras por pol²)</div>
                </div>
                <div class="pressure-value-card">
                    <div class="pressure-position">🔙 Pneus Traseiros</div>
                    <div class="pressure-value">{{ str_replace(' PSI', '', $mainVersion['rear_normal']) }}</div>
                    <div class="pressure-unit">PSI (libras por pol²)</div>
                </div>
            </div>
            <p style="margin-top: 16px; color: #1e40af; font-weight: 500; font-size: 14px;">Pressões para uso normal. Ajuste conforme carga e condições.</p>
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
        
        <!-- ✅ CORREÇÃO: Especificações por Versão usando dados corretos -->
        @if(!empty($article->tire_specifications_by_version))
        <h2>🚗 Especificações por Versão</h2>
        
        <div class="version-specs-grid">
            @foreach($article->tire_specifications_by_version as $version)
            <div class="version-card">
                <div class="version-header">{{ $version['version'] }}</div>
                <div class="version-body">
                    <div class="spec-item">
                        <span class="spec-label">Medida dos Pneus:</span>
                        <span class="spec-value">{{ $version['tire_size'] }}</span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Pressão Dianteira (Normal):</span>
                        <span class="spec-value">{{ $version['front_normal'] }}</span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Pressão Traseira (Normal):</span>
                        <span class="spec-value">{{ $version['rear_normal'] }}</span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Pressão Dianteira (Carregado):</span>
                        <span class="spec-value">{{ $version['front_loaded'] }}</span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Pressão Traseira (Carregado):</span>
                        <span class="spec-value">{{ $version['rear_loaded'] }}</span>
                    </div>
                    @if(!empty($version['load_speed_index']))
                    <div class="spec-item">
                        <span class="spec-label">Índice de Carga/Vel.:</span>
                        <span class="spec-value">{{ $version['load_speed_index'] }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
        
        <!-- ✅ CORREÇÃO: Localização da Etiqueta usando dados corretos -->
        @if(!empty($article->label_location))
        <h2>📍 Onde Encontrar a Etiqueta de Pressão</h2>
        
        <div class="label-location-card">
            <div class="label-location-title">
                <span class="label-location-icon">🔍</span>
                Localizações Mais Comuns
            </div>
            <p style="color: #0c4a6e; margin-bottom: 16px;">{{ $article->label_location['description'] }}</p>
            
            <div class="location-options">
                <div class="location-option">
                    <div class="location-priority">Principal</div>
                    <div class="location-description">{{ $article->label_location['main_location'] }}</div>
                </div>
                @foreach($article->label_location['alternative_locations'] ?? [] as $index => $location)
                <div class="location-option">
                    <div class="location-priority">{{ $index == 0 ? 'Alternativo' : ($index == 1 ? 'Manual' : 'Outro') }}</div>
                    <div class="location-description">{{ $location }}</div>
                </div>
                @endforeach
            </div>
            
            @if(!empty($article->label_location['note']))
            <div class="info-note" style="margin-top: 16px;">
                <strong>💡 Dica:</strong> {{ $article->label_location['note'] }}
            </div>
            @endif
        </div>
        @endif
        
        <!-- ✅ CORREÇÃO: Condições Especiais de Uso usando dados corretos -->
        @if(!empty($article->special_conditions))
        <h2>⚖️ Condições Especiais de Uso</h2>
        
        <div class="special-conditions-grid">
            @foreach($article->special_conditions as $condition)
            <div class="condition-card">
                <div class="condition-header">{{ $condition['condition'] }}</div>
                <div class="condition-body">
                    <div class="condition-pressure">
                        <div class="condition-pressure-label">Ajuste Recomendado</div>
                        <div class="condition-pressure-value">{{ $condition['recommended_adjustment'] }}</div>
                    </div>
                    <div class="condition-description">
                        <strong>Aplicação:</strong> {{ $condition['application'] }}<br>
                        <strong>Justificativa:</strong> {{ $condition['justification'] }}
                    </div>
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
        
        <!-- ✅ CORREÇÃO: Conversor de Unidades usando dados corretos -->
        @if(!empty($article->unit_conversion))
        <h2>🔄 Conversão de Unidades</h2>
        
        <div class="unit-converter">
            @php $firstVersion = !empty($article->tire_specifications_by_version) ? $article->tire_specifications_by_version[0] : null @endphp
            <div class="converter-title">Tabela de Conversão para {{ $firstVersion['front_normal'] ?? '32 PSI' }}</div>
            <div class="conversion-grid">
                @if(!empty($article->unit_conversion['conversion_table']))
                    @foreach($article->unit_conversion['conversion_table'] as $conversion)
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
                        <div class="conversion-unit">PSI (Brasil)</div>
                        <div class="conversion-value">32</div>
                    </div>
                    <div class="conversion-item">
                        <div class="conversion-unit">kgf/cm²</div>
                        <div class="conversion-value">2.25</div>
                    </div>
                    <div class="conversion-item">
                        <div class="conversion-unit">Bar</div>
                        <div class="conversion-value">2.21</div>
                    </div>
                @endif
            </div>
            <div class="conversion-note">
                {{ $article->unit_conversion['note'] ?? '*Use sempre a unidade especificada no seu manômetro' }}
            </div>
        </div>
        @endif
        
        <!-- ✅ CORREÇÃO: Impacto no Desempenho usando dados corretos -->
        @if(!empty($article->pressure_impact))
        <h2>📊 Impacto da Pressão no Desempenho</h2>
        
        <div class="performance-impact-grid">
            @foreach($article->pressure_impact as $impact)
            <div class="impact-card {{ $impact['type'] }}">
                <div class="impact-title">{{ $impact['title'] }}</div>
                <div class="impact-percentage {{ $impact['color'] === 'green' ? 'positive' : ($impact['color'] === 'red' ? 'negative' : 'neutral') }}">
                    @switch($impact['type'])
                        @case('subcalibrado')
                            ⚠️ RISCO
                            @break
                        @case('ideal')
                            ✅ IDEAL
                            @break
                        @case('sobrecalibrado')
                            ⚡ ATENÇÃO
                            @break
                        @default
                            📊 INFO
                    @endswitch
                </div>
                <div class="impact-description">
                    @foreach($impact['items'] as $item)
                        • {{ $item }}<br>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
        @endif
        
        <!-- Alertas de Segurança -->
        <div class="safety-alert critical">
            <div class="alert-title">🚨 Segurança Crítica</div>
            <p>Pneus com pressão incorreta podem causar acidentes graves. No Brasil, com temperaturas altas, a verificação deve ser ainda mais frequente.</p>
        </div>
        
        <!-- Procedimento Rápido de Verificação -->
        <h2>⚡ Procedimento Rápido de Verificação</h2>
        
        <div class="quick-procedure">
            <div class="procedure-step">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h3>Preparação</h3>
                    <p>Estacione em local plano e seguro. Aguarde pelo menos 3 horas após dirigir (pneus frios).</p>
                </div>
            </div>
            <div class="procedure-step">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h3>Verificação</h3>
                    <p>Use calibrador confiável. Meça a pressão de todos os pneus, incluindo o estepe.</p>
                </div>
            </div>
            <div class="procedure-step">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h3>Ajuste</h3>
                    <p>Calibre conforme necessidade. Confirme as pressões e finalize o procedimento.</p>
                </div>
            </div>
        </div>
        
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
        
        <!-- ✅ CORREÇÃO: Cuidados Específicos usando dados corretos -->
        @if(!empty($article->care_recommendations))
        <h2>🇧🇷 Cuidados Específicos para o Brasil</h2>
        
        @foreach($article->care_recommendations as $care)
        <div class="safety-alert info">
            <div class="alert-title">{{ $care['category'] }}</div>
            <p>{{ $care['description'] }}</p>
        </div>
        @endforeach
        @endif
        
        <!-- ✅ CORREÇÃO: Perguntas Frequentes usando dados corretos -->
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
        
        <!-- ✅ CORREÇÃO: Considerações Finais usando dados corretos -->
        @if(!empty($article->final_considerations))
        <h2>📝 Considerações Finais</h2>
        <div style="background: linear-gradient(135deg, #eff6ff, #dbeafe); padding: 20px; border-radius: 8px; border-left: 4px solid #2563eb;">
            <p style="color: #1e40af; line-height: 1.7; font-weight: 500;">{!! nl2br(e($article->final_considerations)) !!}</p>
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
        
        <!-- Nota informativa -->
        @include('auto-info-center::article.partials.info_note_manual')
        
        <!-- Footer do artigo -->
        @include('auto-info-center::article.partials.article_footer')
    </article>
</div>
@endsection