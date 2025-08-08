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
    
    /* Alerta de segurança crítico para motos */
    .critical-safety-banner {
        background: linear-gradient(135deg, #fef2f2, #fee2e2);
        border: 3px solid #DC2626;
        border-radius: 16px;
        padding: 24px;
        margin: 24px 0;
        position: relative;
        box-shadow: 0 4px 6px rgba(220, 38, 38, 0.1);
    }
    
    .critical-safety-banner::before {
        content: '🚨';
        font-size: 32px;
        position: absolute;
        top: 20px;
        left: 20px;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }
    
    .critical-banner-title {
        font-size: 20px;
        font-weight: 700;
        color: #991b1b;
        margin-bottom: 12px;
        padding-left: 50px;
    }
    
    .critical-banner-content {
        color: #7f1d1d;
        font-weight: 500;
        padding-left: 50px;
        font-size: 16px;
    }
    
    /* Destaque da pressão ideal para motos */
    .motorcycle-pressure-highlight {
        background: linear-gradient(135deg, #fef2f2, #fee2e2);
        border: 2px solid #DC2626;
        border-radius: 16px;
        padding: 24px;
        margin: 24px 0;
        text-align: center;
        position: relative;
    }
    
    .motorcycle-pressure-highlight::before {
        content: '🏍️';
        font-size: 28px;
        position: absolute;
        top: 16px;
        left: 20px;
    }
    
    .motorcycle-pressure-title {
        font-size: 20px;
        font-weight: 700;
        color: #991b1b;
        margin-bottom: 16px;
        padding-left: 40px;
    }
    
    .motorcycle-pressure-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-top: 16px;
    }
    
    .motorcycle-pressure-card {
        background-color: rgba(255, 255, 255, 0.9);
        border-radius: 12px;
        padding: 16px;
        border: 2px solid #fca5a5;
        box-shadow: 0 2px 4px rgba(220, 38, 38, 0.1);
    }
    
    .pressure-position {
        font-size: 14px;
        color: #991b1b;
        font-weight: 600;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
    }
    
    .pressure-position .icon {
        margin-right: 6px;
        font-size: 16px;
    }
    
    .pressure-value {
        font-size: 24px;
        font-weight: 700;
        color: #DC2626;
        margin-bottom: 4px;
    }
    
    .pressure-unit {
        font-size: 12px;
        color: #6b7280;
        font-weight: 500;
    }
    
    /* Especificações detalhadas */
    .motorcycle-specs-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        background-color: #fff;
        margin-bottom: 20px;
    }
    
    .motorcycle-specs-header {
        background: linear-gradient(135deg, #DC2626, #b91c1c);
        color: white;
        padding: 16px;
        font-weight: 600;
        font-size: 16px;
        text-align: center;
    }
    
    .motorcycle-specs-body {
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
        background-color: #fef2f2;
        padding: 4px 8px;
        border-radius: 4px;
        border: 1px solid #fecaca;
    }
    
    /* Tabela de pressões por condição */
    .pressure-conditions-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        margin-bottom: 24px;
    }
    
    .pressure-conditions-table th {
        background: linear-gradient(135deg, #DC2626, #b91c1c);
        color: white;
        text-align: center;
        padding: 12px 8px;
        font-weight: 600;
        font-size: 13px;
    }
    
    .pressure-conditions-table td {
        padding: 12px 8px;
        text-align: center;
        border-bottom: 1px solid #e2e8f0;
        background-color: #fff;
    }
    
    .pressure-conditions-table tr:nth-child(even) td {
        background-color: #fef2f2;
    }
    
    .pressure-conditions-table tr:last-child td {
        border-bottom: none;
    }
    
    .pressure-highlight {
        font-weight: 700;
        color: #DC2626;
        background-color: #fee2e2;
        padding: 6px 8px;
        border-radius: 4px;
        border: 1px solid #fca5a5;
    }
    
    .condition-name {
        font-weight: 600;
        color: #374151;
        text-align: left;
    }
    
    /* Considerações especiais para motos */
    .motorcycle-considerations {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }
    
    .consideration-card {
        background: linear-gradient(135deg, #fef2f2, #fee2e2);
        border: 1px solid #fca5a5;
        border-radius: 12px;
        padding: 20px;
        border-left: 4px solid #DC2626;
        position: relative;
    }
    
    .consideration-title {
        font-weight: 600;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        color: #991b1b;
        font-size: 16px;
    }
    
    .consideration-icon {
        width: 24px;
        height: 24px;
        margin-right: 10px;
        flex-shrink: 0;
    }
    
    .consideration-description {
        color: #7f1d1d;
        font-weight: 500;
        line-height: 1.6;
    }
    
    .consideration-tip {
        background-color: rgba(255, 255, 255, 0.7);
        border-radius: 6px;
        padding: 8px;
        margin-top: 8px;
        font-size: 13px;
        font-weight: 600;
        color: #991b1b;
    }
    
    /* Benefícios da calibragem correta */
    .benefits-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }
    
    .benefit-card {
        background-color: #fff;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        transition: transform 0.2s ease;
    }
    
    .benefit-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .benefit-card.safety {
        border-color: #DC2626;
        background: linear-gradient(135deg, #fef2f2, #fee2e2);
    }
    
    .benefit-card.performance {
        border-color: #f59e0b;
        background: linear-gradient(135deg, #fffbeb, #fef3c7);
    }
    
    .benefit-card.economy {
        border-color: #10b981;
        background: linear-gradient(135deg, #ecfdf5, #d1fae5);
    }
    
    .benefit-icon {
        font-size: 32px;
        margin-bottom: 12px;
        display: block;
    }
    
    .benefit-title {
        font-weight: 600;
        margin-bottom: 8px;
        font-size: 16px;
        color: #151C25;
    }
    
    .benefit-description {
        font-size: 13px;
        color: #4b5563;
        line-height: 1.5;
    }
    
    /* Procedimento de calibragem em 3 passos */
    .three-step-procedure {
        background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
        border-radius: 16px;
        padding: 28px;
        margin: 24px 0;
        border: 1px solid #cbd5e1;
        position: relative;
    }
    
    .procedure-title {
        text-align: center;
        font-size: 20px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 24px;
    }
    
    .three-steps-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 24px;
    }
    
    .step-card {
        background-color: #fff;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        border: 2px solid #e2e8f0;
        position: relative;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .step-number-badge {
        position: absolute;
        top: -12px;
        left: 50%;
        transform: translateX(-50%);
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #DC2626, #b91c1c);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 16px;
        box-shadow: 0 2px 4px rgba(220, 38, 38, 0.3);
    }
    
    .step-icon {
        font-size: 28px;
        margin: 20px 0 12px;
        display: block;
    }
    
    .step-title {
        font-weight: 600;
        margin-bottom: 8px;
        color: #1e293b;
        font-size: 16px;
    }
    
    .step-description {
        font-size: 13px;
        color: #475569;
        line-height: 1.5;
    }
    
    /* Alertas críticos */
    .critical-alerts-section {
        margin: 32px 0;
    }
    
    .critical-alert {
        background: linear-gradient(135deg, #fef2f2, #fee2e2);
        border: 2px solid #DC2626;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 16px;
        position: relative;
        border-left: 6px solid #DC2626;
    }
    
    .critical-alert-title {
        font-weight: 600;
        color: #991b1b;
        margin-bottom: 8px;
        font-size: 16px;
        display: flex;
        align-items: center;
    }
    
    .critical-alert-icon {
        margin-right: 8px;
        font-size: 18px;
    }
    
    .critical-alert-content {
        color: #7f1d1d;
        font-weight: 500;
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
        background-color: #fef2f2;
        font-weight: 600;
        color: #151C25;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }
    
    amp-accordion h4:hover {
        background-color: #fee2e2;
    }
    
    amp-accordion .faq-content {
        padding: 16px;
        background-color: #fff;
        color: #4a5568;
        line-height: 1.6;
    }
    
    /* Nota informativa */
    .info-note {
        background: linear-gradient(135deg, rgba(220, 38, 38, 0.05), rgba(220, 38, 38, 0.1));
        padding: 16px;
        border-radius: 8px;
        font-size: 14px;
        margin: 24px 0;
        border-left: 4px solid #DC2626;
        color: #991b1b;
        font-weight: 500;
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
        
        .motorcycle-pressure-grid {
            grid-template-columns: 1fr;
        }
        
        .motorcycle-considerations {
            grid-template-columns: 1fr;
        }
        
        .benefits-grid {
            grid-template-columns: 1fr;
        }
        
        .three-steps-grid {
            grid-template-columns: 1fr;
        }
        
        .pressure-conditions-table {
            font-size: 13px;
        }
        
        .pressure-conditions-table th,
        .pressure-conditions-table td {
            padding: 8px 4px;
        }
        
        .ad-container {
            margin: 24px 0;
            padding: 12px;
        }
        
        .motorcycle-pressure-title {
            font-size: 18px;
            padding-left: 20px;
        }
        
        .motorcycle-pressure-highlight::before,
        .critical-safety-banner::before {
            left: 12px;
            font-size: 24px;
        }
        
        .critical-banner-title,
        .critical-banner-content {
            padding-left: 30px;
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
        
        .pressure-value {
            font-size: 20px;
        }
        
        .critical-banner-title {
            font-size: 18px;
        }
        
        .critical-banner-content {
            font-size: 14px;
        }
        
        .pressure-conditions-table th,
        .pressure-conditions-table td {
            padding: 6px 2px;
            font-size: 12px;
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
        
        <!-- Alerta de Segurança Crítico -->
        @if(!empty($article->critical_safety_alert))
        <div class="critical-safety-banner">
            <div class="critical-banner-title">{{ $article->critical_safety_alert['title'] ?? 'ATENÇÃO: Segurança em Motocicletas' }}</div>
            <div class="critical-banner-content">
                {{ $article->critical_safety_alert['message'] ?? 'A calibragem incorreta dos pneus em motocicletas pode ser fatal. Sempre mantenha as pressões dentro das especificações recomendadas pela fabricante.' }}
            </div>
        </div>
        @endif
        
        <!-- Destaque da Pressão Ideal -->
        @if(!empty($article->ideal_pressure))
        <div class="motorcycle-pressure-highlight">
            <div class="motorcycle-pressure-title">Pressão Ideal para {{ $article->vehicle_info['full_name'] ?? 'sua motocicleta' }}</div>
            <div class="motorcycle-pressure-grid">
                @if(!empty($article->ideal_pressure['front_pressure']))
                <div class="motorcycle-pressure-card">
                    <div class="pressure-position">
                        <span class="icon">🔄</span>
                        Pneu Dianteiro
                    </div>
                    <div class="pressure-value">{{ str_replace(' PSI', '', $article->ideal_pressure['front_pressure']) }}</div>
                    <div class="pressure-unit">PSI (libras por pol²)</div>
                </div>
                @endif
                @if(!empty($article->ideal_pressure['rear_pressure']))
                <div class="motorcycle-pressure-card">
                    <div class="pressure-position">
                        <span class="icon">🔙</span>
                        Pneu Traseiro
                    </div>
                    <div class="pressure-value">{{ str_replace(' PSI', '', $article->ideal_pressure['rear_pressure']) }}</div>
                    <div class="pressure-unit">PSI (libras por pol²)</div>
                </div>
                @endif
            </div>
            @if(!empty($article->ideal_pressure['observation']))
            <p style="margin-top: 16px; color: #991b1b; font-weight: 500; font-size: 14px;">{{ $article->ideal_pressure['observation'] }}</p>
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
        
        <!-- Especificações Detalhadas -->
        @if(!empty($article->tire_specifications))
        <h2>🏍️ Especificações dos Pneus</h2>
        
        <div class="motorcycle-specs-card">
            <div class="motorcycle-specs-header">Dados Técnicos dos Pneus</div>
            <div class="motorcycle-specs-body">
                @if(!empty($article->tire_specifications['front_tire_size']))
                <div class="spec-item">
                    <span class="spec-label">Pneu Dianteiro:</span>
                    <span class="spec-value">{{ $article->tire_specifications['front_tire_size'] }}</span>
                </div>
                @endif
                @if(!empty($article->tire_specifications['rear_tire_size']))
                <div class="spec-item">
                    <span class="spec-label">Pneu Traseiro:</span>
                    <span class="spec-value">{{ $article->tire_specifications['rear_tire_size'] }}</span>
                </div>
                @endif
                @if(!empty($article->tire_specifications['original_brand']))
                <div class="spec-item">
                    <span class="spec-label">Marca Original:</span>
                    <span class="spec-value">{{ $article->tire_specifications['original_brand'] }}</span>
                </div>
                @endif
                @if(!empty($article->tire_specifications['load_index']))
                <div class="spec-item">
                    <span class="spec-label">Índice de Carga:</span>
                    <span class="spec-value">{{ $article->tire_specifications['load_index'] }}</span>
                </div>
                @endif
                @if(!empty($article->tire_specifications['speed_rating']))
                <div class="spec-item">
                    <span class="spec-label">Índice de Velocidade:</span>
                    <span class="spec-value">{{ $article->tire_specifications['speed_rating'] }}</span>
                </div>
                @endif
                @if(!empty($article->tire_specifications['construction']))
                <div class="spec-item">
                    <span class="spec-label">Construção:</span>
                    <span class="spec-value">{{ $article->tire_specifications['construction'] }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif
        
        <!-- Tabela de Pressões por Condição -->
        @if(!empty($article->pressure_table) && is_array($article->pressure_table))
        <h2>📊 Pressões por Condição de Uso</h2>
        
        <table class="pressure-conditions-table">
            <tr>
                <th>Condição</th>
                <th>Dianteiro</th>
                <th>Traseiro</th>
                <th>Observação</th>
            </tr>
            @foreach($article->pressure_table as $condition)
            <tr>
                <td class="condition-name">{{ $condition['condition'] ?? 'N/A' }}</td>
                <td><span class="pressure-highlight">{{ $condition['front_pressure'] ?? 'N/A' }}</span></td>
                <td><span class="pressure-highlight">{{ $condition['rear_pressure'] ?? 'N/A' }}</span></td>
                <td>{{ $condition['observation'] ?? '' }}</td>
            </tr>
            @endforeach
        </table>
        @endif
        
        <!-- Considerações Especiais para Motocicletas -->
        @if(!empty($article->special_considerations) && is_array($article->special_considerations))
        <h2>⚠️ Considerações Especiais para Motocicletas</h2>
        
        <div class="motorcycle-considerations">
            @foreach($article->special_considerations as $consideration)
            <div class="consideration-card">
                <div class="consideration-title">
                    <amp-img class="consideration-icon" 
                        src="https://mercadoveiculos.s3.us-east-1.amazonaws.com/statics/icons/icon-{{ $consideration['icon_class'] ?? 'warning' }}.svg" 
                        width="24" height="24" alt="{{ $consideration['title'] ?? 'Cuidado' }}"></amp-img>
                    {{ $consideration['title'] ?? 'Cuidado Especial' }}
                </div>
                <div class="consideration-description">{{ $consideration['description'] ?? '' }}</div>
                @if(!empty($consideration['tip']))
                <div class="consideration-tip">💡 {{ $consideration['tip'] }}</div>
                @endif
            </div>
            @endforeach
        </div>
        @endif
        
        <!-- 🥇 ANÚNCIO 2: Após considerações -->
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
        
        <!-- Benefícios da Calibragem Correta -->
        @if(!empty($article->benefits) && is_array($article->benefits))
        <h2>✅ Benefícios da Calibragem Correta</h2>
        
        <div class="benefits-grid">
            @foreach($article->benefits as $benefit)
            <div class="benefit-card {{ strtolower($benefit['category'] ?? 'general') }}">
                <span class="benefit-icon">{{ $benefit['icon'] ?? '✅' }}</span>
                <div class="benefit-title">{{ $benefit['title'] ?? 'Benefício' }}</div>
                <div class="benefit-description">{{ $benefit['description'] ?? '' }}</div>
            </div>
            @endforeach
        </div>
        @endif
        
        <!-- Procedimento de Calibragem em 3 Passos -->
        <h2>🔧 Procedimento de Calibragem em 3 Passos</h2>
        
        <div class="three-step-procedure">
            <div class="procedure-title">Siga estes 3 passos simples para calibrar corretamente</div>
            <div class="three-steps-grid">
                <div class="step-card">
                    <div class="step-number-badge">1</div>
                    <span class="step-icon">🌡️</span>
                    <div class="step-title">Pneus Frios</div>
                    <div class="step-description">Verifique sempre com os pneus frios, antes de rodar ou após 3 horas parado. O calor altera a pressão.</div>
                </div>
                <div class="step-card">
                    <div class="step-number-badge">2</div>
                    <span class="step-icon">📏</span>
                    <div class="step-title">Meça a Pressão</div>
                    <div class="step-description">Use um manômetro confiável. Remova a tampa da válvula e pressione firmemente o medidor.</div>
                </div>
                <div class="step-card">
                    <div class="step-number-badge">3</div>
                    <span class="step-icon">⚖️</span>
                    <div class="step-title">Ajuste se Necessário</div>
                    <div class="step-description">Se estiver fora do especificado, adicione ou retire ar até atingir a pressão correta.</div>
                </div>
            </div>
        </div>
        
        <!-- Alertas Críticos -->
        @if(!empty($article->critical_alerts) && is_array($article->critical_alerts))
        <div class="critical-alerts-section">
            <h2>🚨 Alertas Críticos de Segurança</h2>
            
            @foreach($article->critical_alerts as $alert)
            <div class="critical-alert">
                <div class="critical-alert-title">
                    <span class="critical-alert-icon">{{ $alert['icon'] ?? '⚠️' }}</span>
                    {{ $alert['title'] ?? 'Alerta de Segurança' }}
                </div>
                <div class="critical-alert-content">
                    {{ $alert['description'] ?? '' }}
                    @if(!empty($alert['consequence']))
                    <br><strong>Consequência:</strong> {{ $alert['consequence'] }}
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
        
        <!-- 🥈 ANÚNCIO 3: Após alertas críticos -->
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
        
        <!-- Frequência de Verificação -->
        @if(!empty($article->maintenance_schedule))
        <h2>📅 Cronograma de Manutenção</h2>
        
        <div class="motorcycle-considerations">
            @foreach($article->maintenance_schedule as $schedule)
            <div class="consideration-card">
                <div class="consideration-title">
                    <amp-img class="consideration-icon" 
                        src="https://mercadoveiculos.s3.us-east-1.amazonaws.com/statics/icons/icon-{{ $schedule['icon_class'] ?? 'calendar' }}.svg" 
                        width="24" height="24" alt="{{ $schedule['frequency'] ?? 'Cronograma' }}"></amp-img>
                    {{ $schedule['frequency'] ?? 'Verificação' }}
                </div>
                <div class="consideration-description">{{ $schedule['description'] ?? '' }}</div>
                @if(!empty($schedule['tip']))
                <div class="consideration-tip">💡 {{ $schedule['tip'] }}</div>
                @endif
            </div>
            @endforeach
        </div>
        @endif
        
        <!-- Equipamentos Recomendados -->
        @if(!empty($article->recommended_equipment) && is_array($article->recommended_equipment))
        <h2>🛠️ Equipamentos Recomendados</h2>
        
        <div class="benefits-grid">
            @foreach($article->recommended_equipment as $equipment)
            <div class="benefit-card">
                <span class="benefit-icon">{{ $equipment['icon'] ?? '🔧' }}</span>
                <div class="benefit-title">{{ $equipment['name'] ?? 'Equipamento' }}</div>
                <div class="benefit-description">
                    {{ $equipment['description'] ?? '' }}
                    @if(!empty($equipment['price_range']))
                    <br><strong>Preço:</strong> {{ $equipment['price_range'] }}
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
        
        <!-- Sinais de Problema -->
        @if(!empty($article->problem_indicators) && is_array($article->problem_indicators))
        <h2>🔍 Sinais de Problemas na Calibragem</h2>
        
        <div class="critical-alerts-section">
            @foreach($article->problem_indicators as $indicator)
            <div class="critical-alert">
                <div class="critical-alert-title">
                    <span class="critical-alert-icon">{{ $indicator['icon'] ?? '🔍' }}</span>
                    {{ $indicator['problem'] ?? 'Problema Detectado' }}
                </div>
                <div class="critical-alert-content">
                    <strong>Sinais:</strong> {{ $indicator['signs'] ?? '' }}
                    @if(!empty($indicator['action']))
                    <br><strong>Ação:</strong> {{ $indicator['action'] }}
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
        
        <!-- Perguntas Frequentes -->
        @if(!empty($article->faq) && is_array($article->faq))
        <h2>❓ Perguntas Frequentes sobre {{ $article->vehicle_info['full_name'] ?? 'Pressão dos Pneus' }}</h2>
        
        <amp-accordion>
            @foreach($article->faq as $faq)
            <section>
                <h4>{{ $faq['pergunta'] ?? '' }}</h4>
                <div class="faq-content">
                    <p>{{ $faq['resposta'] ?? '' }}</p>
                </div>
            </section>
            @endforeach
        </amp-accordion>
        @endif
        
        <!-- Conversão de Unidades para Motocicletas -->
        @if(!empty($article->unit_conversions) && is_array($article->unit_conversions))
        <h2>🔄 Conversão de Unidades</h2>
        
        <div class="motorcycle-specs-card">
            <div class="motorcycle-specs-header">Conversão de Pressões</div>
            <div class="motorcycle-specs-body">
                @foreach($article->unit_conversions as $conversion)
                <div class="spec-item">
                    <span class="spec-label">{{ $conversion['unit'] ?? 'N/A' }}:</span>
                    <span class="spec-value">{{ $conversion['value'] ?? 'N/A' }}</span>
                </div>
                @endforeach
            </div>
        </div>
        
        <div class="info-note">
            <strong>💡 Dica importante:</strong> Use sempre a unidade especificada no seu manômetro. PSI é a unidade mais comum em motocicletas.
        </div>
        @endif
        
        <!-- Cuidados Específicos para o Brasil -->
        @if(!empty($article->brazil_considerations) && is_array($article->brazil_considerations))
        <h2>🇧🇷 Cuidados Específicos para o Brasil</h2>
        
        <div class="motorcycle-considerations">
            @foreach($article->brazil_considerations as $consideration)
            <div class="consideration-card">
                <div class="consideration-title">
                    <amp-img class="consideration-icon" 
                        src="https://mercadoveiculos.s3.us-east-1.amazonaws.com/statics/icons/icon-{{ $consideration['icon_class'] ?? 'brazil' }}.svg" 
                        width="24" height="24" alt="{{ $consideration['title'] ?? 'Brasil' }}"></amp-img>
                    {{ $consideration['title'] ?? 'Cuidado no Brasil' }}
                </div>
                <div class="consideration-description">{{ $consideration['description'] ?? '' }}</div>
                @if(!empty($consideration['tip']))
                <div class="consideration-tip">🇧🇷 {{ $consideration['tip'] }}</div>
                @endif
            </div>
            @endforeach
        </div>
        @endif
        
        <!-- Considerações Finais -->
        @if(!empty($article->final_considerations))
        <h2>📝 Considerações Finais</h2>
        <div style="background: linear-gradient(135deg, #fef2f2, #fee2e2); padding: 20px; border-radius: 8px; border-left: 4px solid #DC2626;">
            <p style="color: #991b1b; line-height: 1.7; font-weight: 500;">{{ $article->final_considerations }}</p>
        </div>
        @endif
        
        <!-- Lembrete Final de Segurança -->
        <div class="critical-safety-banner">
            <div class="critical-banner-title">🏍️ Lembre-se Sempre</div>
            <div style="text-align: left; padding-left: 50px;">
                <p style="color: #991b1b; margin-bottom: 8px; font-weight: 500;"><strong>✓</strong> Verifique semanalmente a pressão</p>
                <p style="color: #991b1b; margin-bottom: 8px; font-weight: 500;"><strong>✓</strong> Sempre com pneus frios</p>
                <p style="color: #991b1b; margin-bottom: 8px; font-weight: 500;"><strong>✓</strong> Use manômetro confiável</p>
                <p style="color: #991b1b; margin-bottom: 8px; font-weight: 500;"><strong>✓</strong> Ajuste conforme a carga</p>
                <p style="color: #991b1b; margin-bottom: 0; font-weight: 500;"><strong>✓</strong> Em caso de dúvida, consulte o manual</p>
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