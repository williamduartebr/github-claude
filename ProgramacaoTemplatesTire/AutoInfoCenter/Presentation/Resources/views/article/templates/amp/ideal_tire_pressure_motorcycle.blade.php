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
        white-space: nowrap;
    }
    
    .pressure-unit {
        font-size: 12px;
        color: #6b7280;
        font-weight: 500;
    }
    
    /* Especificações dos pneus */
    .tire-specs-section {
        background: linear-gradient(135deg, #f8fafc, #e2e8f0);
        border-radius: 12px;
        padding: 24px;
        margin: 24px 0;
        border: 1px solid #cbd5e1;
    }
    
    .tire-specs-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-top: 16px;
    }
    
    .tire-spec-card {
        background: white;
        border-radius: 8px;
        padding: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .tire-spec-header {
        font-weight: 600;
        color: #991b1b;
        margin-bottom: 12px;
        font-size: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .spec-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        padding: 4px 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 14px;
    }
    
    .spec-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }
    
    .spec-label {
        color: #6b7280;
        font-weight: 500;
    }
    
    .spec-value {
        font-weight: 600;
        color: #151C25;
        background-color: #fee2e2;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 12px;
    }
    
    /* Tabela de pressões principal */
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
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .pressure-table th {
        background: linear-gradient(135deg, #DC2626, #991b1b);
        color: white;
        padding: 14px 12px;
        text-align: center;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .pressure-table th:first-child {
        text-align: left;
        padding-left: 16px;
    }
    
    .pressure-table td {
        padding: 12px;
        text-align: center;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    
    .condition-cell {
        text-align: left !important;
        padding-left: 16px !important;
        font-weight: 500;
        color: #374151;
        min-width: 180px;
    }
    
    .condition-icon {
        margin-right: 8px;
        font-size: 16px;
    }
    
    .pressure-cell {
        font-weight: 600;
        min-width: 100px;
    }
    
    .pressure-highlight {
        background: linear-gradient(135deg, #DC2626, #991b1b);
        color: white;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 700;
        display: inline-block;
        white-space: nowrap;
        min-width: 80px;
        text-align: center;
    }
    
    .observation-cell {
        text-align: left !important;
        max-width: 200px;
        color: #4b5563;
        font-size: 12px;
        line-height: 1.4;
        padding-left: 8px !important;
    }
    
    .pressure-table tr:nth-child(even) {
        background-color: #f8fafc;
    }
    
    .pressure-table tr:hover {
        background-color: rgba(220, 38, 38, 0.05);
    }
    
    /* Considerações especiais */
    .considerations-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin: 24px 0;
    }
    
    .consideration-card {
        background: linear-gradient(135deg, #fef2f2, #fee2e2);
        border: 1px solid #fca5a5;
        border-radius: 12px;
        padding: 20px;
        border-left: 4px solid #DC2626;
    }
    
    .consideration-title {
        font-weight: 600;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        color: #991b1b;
        font-size: 16px;
        gap: 10px;
    }
    
    .consideration-description {
        color: #7f1d1d;
        font-weight: 500;
        line-height: 1.6;
        margin-bottom: 12px;
    }
    
    .consideration-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .consideration-list li {
        margin-bottom: 8px;
        padding-left: 20px;
        position: relative;
        color: #7f1d1d;
        font-size: 14px;
        line-height: 1.5;
    }
    
    .consideration-list li::before {
        content: '•';
        color: #DC2626;
        font-weight: bold;
        position: absolute;
        left: 0;
    }
    
    /* Conversão de unidades */
    .conversion-section {
        background: linear-gradient(135deg, #ecfdf5, #d1fae5);
        border: 1px solid #10b981;
        border-radius: 12px;
        padding: 24px;
        margin: 24px 0;
    }
    
    .conversion-title {
        font-size: 18px;
        font-weight: 600;
        color: #065f46;
        margin-bottom: 16px;
        text-align: center;
    }
    
    .conversion-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .conversion-table th {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        padding: 12px;
        text-align: center;
        font-weight: 600;
        font-size: 13px;
    }
    
    .conversion-table td {
        padding: 10px 12px;
        text-align: center;
        border-bottom: 1px solid #f1f5f9;
        font-weight: 600;
    }
    
    .conversion-table tr:nth-child(even) {
        background-color: #f0fdf4;
    }
    
    .conversion-table .highlight-pressure {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        border-radius: 4px;
        padding: 4px 8px;
    }
    
    /* FAQ */
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
    
    /* Alertas importantes */
    .safety-alert {
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 20px;
        border-left: 4px solid;
    }
    
    .safety-alert.critical {
        background-color: #fef2f2;
        border-color: #DC2626;
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
        display: flex;
        align-items: center;
        gap: 8px;
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
    
    /* Legenda */
    .table-legend {
        background: #f8fafc;
        border-radius: 8px;
        padding: 16px;
        margin-top: 16px;
        border: 1px solid #e2e8f0;
    }
    
    .table-legend h3 {
        margin: 0 0 12px 0;
        color: #1f2937;
        font-size: 16px;
    }
    
    .legend-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 12px;
    }
    
    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px;
        background: white;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
    }
    
    .legend-icon {
        font-size: 18px;
        flex-shrink: 0;
    }
    
    .legend-item span:last-child {
        font-size: 13px;
        color: #374151;
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
        
        .tire-specs-grid {
            grid-template-columns: 1fr;
        }
        
        .considerations-grid {
            grid-template-columns: 1fr;
        }
        
        .pressure-table {
            font-size: 12px;
            min-width: 500px;
        }
        
        .pressure-table th,
        .pressure-table td {
            padding: 8px 6px;
        }
        
        .condition-cell,
        .observation-cell {
            padding-left: 12px !important;
        }
        
        .pressure-highlight {
            padding: 4px 10px;
            font-size: 12px;
            min-width: 70px;
        }
        
        .ad-container {
            margin: 20px 0;
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
        
        .legend-grid {
            grid-template-columns: 1fr;
        }
    }
    
    @media (max-width: 480px) {
        h1 {
            font-size: 22px;
        }
        
        h2 {
            font-size: 18px;
        }
        
        .pressure-table {
            min-width: 450px;
            font-size: 11px;
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
        
        .pressure-table-container {
            padding: 12px;
        }
    }
</style>
@endsection

@section('content')
<div class="container">
    <article>
        <!-- Cabeçalho -->
        <h1>{{ $article->getData()['seo_data']['h1'] ?? $article->getData()['title'] ?? $article->title }}</h1>
        <div class="article-meta">
            @if(!empty($article->getData()['formated_updated_at']))
            <p>Atualizado em: {{ $article->getData()['formated_updated_at'] }}</p>
            @endif
        </div>
        
        <!-- Introdução -->
        @if(!empty($article->getData()['introduction']))
        <p>{{ $article->getData()['introduction'] }}</p>
        @endif
        
        <!-- Alerta de Segurança Crítico -->
        <div class="critical-safety-banner">
            <div class="critical-banner-title">ATENÇÃO: Segurança em Motocicletas</div>
            <div class="critical-banner-content">
                A calibragem incorreta dos pneus em motocicletas pode ser fatal. Sempre mantenha as pressões dentro das especificações recomendadas pela fabricante.
            </div>
        </div>
        
        <!-- 🥇 ANÚNCIO 1: Após introdução -->
        <div class="ad-container">
            <div class="ad-label">Publicidade</div>
            <amp-ad width="100vw" height="320"
                type="adsense"
                data-ad-client="{{ config('services.google_adsense.id') }}"
                data-ad-slot="7414648059"   
                data-auto-format="rspv"
                data-full-width>
                <div overflow></div>
            </amp-ad>
        </div>
        
        <!-- Especificações dos Pneus -->
        @if(!empty($article->getData()['tire_specifications']))
        <h2>🏍️ Especificações dos Pneus</h2>
        
        <div class="tire-specs-section">
            <div class="tire-specs-grid">
                @if(!empty($article->getData()['tire_specifications']['front_tire']))
                <div class="tire-spec-card">
                    <div class="tire-spec-header">
                        <span>🔄</span>
                        Pneu Dianteiro
                    </div>
                    @php $frontTire = $article->getData()['tire_specifications']['front_tire'] @endphp
                    @if(!empty($frontTire['size']))
                    <div class="spec-item">
                        <span class="spec-label">Medida:</span>
                        <span class="spec-value">{{ $frontTire['size'] }}</span>
                    </div>
                    @endif
                    @if(!empty($frontTire['load_index']))
                    <div class="spec-item">
                        <span class="spec-label">Índice de Carga:</span>
                        <span class="spec-value">{{ $frontTire['load_index'] }}</span>
                    </div>
                    @endif
                    @if(!empty($frontTire['speed_rating']))
                    <div class="spec-item">
                        <span class="spec-label">Índice de Velocidade:</span>
                        <span class="spec-value">{{ $frontTire['speed_rating'] }}</span>
                    </div>
                    @endif
                </div>
                @endif
                
                @if(!empty($article->getData()['tire_specifications']['rear_tire']))
                <div class="tire-spec-card">
                    <div class="tire-spec-header">
                        <span>🔙</span>
                        Pneu Traseiro
                    </div>
                    @php $rearTire = $article->getData()['tire_specifications']['rear_tire'] @endphp
                    @if(!empty($rearTire['size']))
                    <div class="spec-item">
                        <span class="spec-label">Medida:</span>
                        <span class="spec-value">{{ $rearTire['size'] }}</span>
                    </div>
                    @endif
                    @if(!empty($rearTire['load_index']))
                    <div class="spec-item">
                        <span class="spec-label">Índice de Carga:</span>
                        <span class="spec-value">{{ $rearTire['load_index'] }}</span>
                    </div>
                    @endif
                    @if(!empty($rearTire['speed_rating']))
                    <div class="spec-item">
                        <span class="spec-label">Índice de Velocidade:</span>
                        <span class="spec-value">{{ $rearTire['speed_rating'] }}</span>
                    </div>
                    @endif
                </div>
                @endif
            </div>
            
            @if(!empty($article->getData()['tire_specifications']['observation']))
            <div class="info-note" style="margin-top: 16px;">
                <strong>📌 Importante:</strong> {{ $article->getData()['tire_specifications']['observation'] }}
            </div>
            @endif
        </div>
        @endif
        
        <!-- Tabela de Pressão - CORRIGIDA PARA REPLICAR O TEMPLATE NORMAL -->
        @if(!empty($article->getData()['pressure_table']))
        <h2>📊 Tabela de Pressão dos Pneus (PSI - {{ $article->getData()['vehicle_info']['full_name'] ?? 'Motocicleta' }})</h2>

        <div class="pressure-table-container">
            @php $pressureTable = $article->getData()['pressure_table'] @endphp
            
            <table class="pressure-table">
                <thead>
                    <tr>
                        <th>Condição de Uso</th>
                        <th>Pneu Dianteiro<br>{{ $article->getData()['tire_specifications']['front_tire']['size'] ?? '110/70 R17' }}</th>
                        <th>Pneu Traseiro<br>{{ $article->getData()['tire_specifications']['rear_tire']['size'] ?? '140/70 R17' }}</th>
                        <th>Observações</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Pressões Oficiais -->
                    @if(!empty($pressureTable['official_pressures']))
                    @php $official = $pressureTable['official_pressures'] @endphp
                    
                    @if(!empty($official['solo_rider']))
                    <tr style="background-color: white;">
                        <td class="condition-cell">
                            <span class="condition-icon">🏍️</span>
                            <strong>Piloto Solo</strong><br>
                            <small style="font-size: 12px; color: #6b7280;">{{ $official['solo_rider']['observation'] ?? 'Uso normal' }}</small>
                        </td>
                        <td class="pressure-cell">
                            <span class="pressure-highlight">{{ $official['solo_rider']['front'] }}</span>
                        </td>
                        <td class="pressure-cell">
                            <span class="pressure-highlight">{{ $official['solo_rider']['rear'] }}</span>
                        </td>
                        <td class="observation-cell">Uso diário</td>
                    </tr>
                    @endif

                    @if(!empty($official['with_passenger']))
                    <tr style="background-color: #f8fafc;">
                        <td class="condition-cell">
                            <span class="condition-icon">👥</span>
                            <strong>Piloto + Garupa</strong><br>
                            <small style="font-size: 12px; color: #6b7280;">{{ $official['with_passenger']['observation'] ?? 'Com passageiro' }}</small>
                        </td>
                        <td class="pressure-cell">
                            <span class="pressure-highlight">{{ $official['with_passenger']['front'] }}</span>
                        </td>
                        <td class="pressure-cell">
                            <span class="pressure-highlight">{{ $official['with_passenger']['rear'] }}</span>
                        </td>
                        <td class="observation-cell">Carga dupla</td>
                    </tr>
                    @endif
                    @endif

                    <!-- Condições Especiais -->
                    @if(!empty($pressureTable['special_conditions']))
                    @foreach($pressureTable['special_conditions'] as $condition)
                    <tr style="background-color: {{ $loop->even ? '#f8fafc' : 'white' }};">
                        <td class="condition-cell">
                            @switch($condition['icon_class'])
                                @case('home')
                                    <span class="condition-icon">🏙️</span>
                                    @break
                                @case('map')
                                    <span class="condition-icon">🛣️</span>
                                    @break
                                @case('zap')
                                    <span class="condition-icon">🏁</span>
                                    @break
                                @case('cloud-rain')
                                    <span class="condition-icon">🌧️</span>
                                    @break
                                @case('user')
                                    <span class="condition-icon">⚖️</span>
                                    @break
                                @default
                                    <span class="condition-icon">📍</span>
                            @endswitch
                            <strong>{{ $condition['situation'] }}</strong>
                        </td>
                        <td class="pressure-cell">
                            <span class="pressure-highlight">{{ $condition['front_pressure'] }}</span>
                        </td>
                        <td class="pressure-cell">
                            <span class="pressure-highlight">{{ $condition['rear_pressure'] }}</span>
                        </td>
                        <td class="observation-cell">{{ $condition['observation'] }}</td>
                    </tr>
                    @endforeach
                    @endif
                </tbody>
            </table>

            <!-- Nota importante no final da tabela -->
            <div style="background: #f8fafc; padding: 16px; margin-top: 16px; border-radius: 8px; border-left: 4px solid #DC2626;">
                <p style="margin: 0; font-size: 14px; color: #374151;">
                    <span style="font-weight: 600;">⚠️ Importante:</span> 
                    Sempre verifique com pneus frios (pelo menos 3 horas parados). No calor brasileiro (35°C+), a pressão pode aumentar 4-6 PSI durante a pilotagem.
                </p>
            </div>
        </div>

        <!-- Legenda das Condições -->
        <div class="table-legend">
            <h3>📋 Legenda das Condições:</h3>
            <div class="legend-grid">
                <div class="legend-item">
                    <span class="legend-icon">🏍️</span>
                    <span><strong>Uso Normal:</strong> Pilotagem urbana e rodoviária padrão</span>
                </div>
                <div class="legend-item">
                    <span class="legend-icon">👥</span>
                    <span><strong>Com Garupa:</strong> Piloto + passageiro em trajetos normais</span>
                </div>
                <div class="legend-item">
                    <span class="legend-icon">🛣️</span>
                    <span><strong>Rodoviária:</strong> Viagens longas em alta velocidade</span>
                </div>
                <div class="legend-item">
                    <span class="legend-icon">🏁</span>
                    <span><strong>Esportiva:</strong> Pilotagem agressiva, apenas experientes</span>
                </div>
                <div class="legend-item">
                    <span class="legend-icon">🌧️</span>
                    <span><strong>Chuva:</strong> Condições de piso molhado</span>
                </div>
                <div class="legend-item">
                    <span class="legend-icon">⚖️</span>
                    <span><strong>Piloto Pesado:</strong> Acima de 90kg com equipamentos</span>
                </div>
            </div>
        </div>
        @endif
        
        <!-- 🥇 ANÚNCIO 2: Após tabela -->
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
        
        <!-- Considerações Especiais -->
        @if(!empty($article->getData()['special_considerations']))
        <h2>⚠️ Ajustes para Condições Especiais</h2>
        
        <div class="considerations-grid">
            @foreach($article->getData()['special_considerations'] as $consideration)
            <div class="consideration-card">
                <div class="consideration-title">
                    @switch($consideration['icon_class'])
                        @case('thermometer')
                            🌡️
                            @break
                        @case('package')
                            🎒
                            @break
                        @case('target')
                            🏁
                            @break
                        @default
                            ⚠️
                    @endswitch
                    {{ $consideration['title'] ?? 'Consideração Especial' }}
                </div>
                <div class="consideration-description">{{ $consideration['description'] ?? '' }}</div>
                
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
            @endforeach
        </div>
        @endif
        
        <!-- Conversão de Unidades -->
        @if(!empty($article->getData()['unit_conversion']))
        <h2>🔄 Conversão de Unidades</h2>
        
        <div class="conversion-section">
            <div class="conversion-title">Tabela de Conversão para Motocicletas</div>
            @php $conversion = $article->getData()['unit_conversion'] @endphp
            
            @if(!empty($conversion['conversion_table']) && is_array($conversion['conversion_table']))
            <table class="conversion-table">
                <thead>
                    <tr>
                        <th>PSI (Brasil)</th>
                        <th>kgf/cm²</th>
                        <th>Bar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($conversion['conversion_table'] as $row)
                    <tr>
                        <td>
                            @if($row['is_recommended'] ?? false)
                            <span class="highlight-pressure">{{ $row['psi'] ?? '' }}</span>
                            @else
                            {{ $row['psi'] ?? '' }}
                            @endif
                        </td>
                        <td>{{ $row['kgf_cm2'] ?? '' }}</td>
                        <td>{{ $row['bar'] ?? '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
            
            @if(!empty($conversion['observation']))
            <div class="info-note" style="margin-top: 16px;">
                <strong>💡 Nota:</strong> {{ $conversion['observation'] }}
            </div>
            @endif
        </div>
        @endif
        
        <!-- Onde Encontrar as Informações -->
        @if(!empty($article->getData()['information_location']))
        <h2>📍 Onde Encontrar as Pressões</h2>
        
        <div class="considerations-grid">
            @php $location = $article->getData()['information_location'] @endphp
            
            @if(!empty($location['owner_manual']))
            <div class="consideration-card">
                <div class="consideration-title">
                    📖 Manual do Proprietário
                </div>
                <div class="consideration-description">{{ $location['owner_manual']['location'] ?? 'Seção de Especificações Técnicas' }}</div>
                <ul class="consideration-list">
                    @if(!empty($location['owner_manual']['section']))
                    <li><strong>Seção:</strong> {{ $location['owner_manual']['section'] }}</li>
                    @endif
                    @if(!empty($location['owner_manual']['approximate_page']))
                    <li><strong>Página aproximada:</strong> {{ $location['owner_manual']['approximate_page'] }}</li>
                    @endif
                </ul>
            </div>
            @endif
            
            @if(!empty($location['motorcycle_label']))
            <div class="consideration-card">
                <div class="consideration-title">
                    🏷️ Etiqueta da Motocicleta
                </div>
                <div class="consideration-description">{{ $location['motorcycle_label']['main_location'] ?? 'Localização principal da etiqueta' }}</div>
                @if(!empty($location['motorcycle_label']['alternative_locations']) && is_array($location['motorcycle_label']['alternative_locations']))
                <ul class="consideration-list">
                    @foreach($location['motorcycle_label']['alternative_locations'] as $altLocation)
                    <li>{{ $altLocation }}</li>
                    @endforeach
                </ul>
                @endif
            </div>
            @endif
        </div>
        
        @if(!empty($location['important_tip']))
        <div class="critical-safety-banner">
            <div class="critical-banner-title">💡 Dica Importante</div>
            <div class="critical-banner-content">{{ $location['important_tip'] }}</div>
        </div>
        @endif
        @endif
        
        <!-- 🥇 ANÚNCIO 3: Após localização -->
        <div class="ad-container">
            <div class="ad-label">Publicidade</div>
            <amp-ad width="100vw" height="320"
                type="adsense"
                data-ad-client="{{ config('services.google_adsense.id') }}"
                data-ad-slot="1402260703"
                data-auto-format="rspv"
                data-full-width>
                <div overflow></div>
            </amp-ad>
        </div>
        
        <!-- Cuidados Específicos para Motos -->
        @if(!empty($article->getData()['maintenance_tips']))
        <h2>🛠️ Cuidados Específicos para Motocicletas</h2>
        
        <div class="considerations-grid">
            @foreach($article->getData()['maintenance_tips'] as $tip)
            <div class="consideration-card">
                <div class="consideration-title">
                    @switch($tip['icon_class'])
                        @case('calendar')
                            📅
                            @break
                        @case('sun')
                            🌡️
                            @break
                        @case('tool')
                            🔧
                            @break
                        @default
                            💡
                    @endswitch
                    {{ $tip['category'] ?? 'Dica' }}
                </div>
                
                @if(!empty($tip['frequency']))
                <div class="consideration-description">
                    <strong>Frequência:</strong> {{ $tip['frequency'] }}
                </div>
                @endif
                
                @if(!empty($tip['items']) && is_array($tip['items']))
                <ul class="consideration-list">
                    @foreach($tip['items'] as $item)
                    <li>{{ $item }}</li>
                    @endforeach
                </ul>
                @endif
            </div>
            @endforeach
        </div>
        @endif
        
        <!-- Benefícios da Calibragem Correta -->
        @if(!empty($article->getData()['calibration_benefits']))
        <h2>✅ Benefícios da Calibragem Correta</h2>
        
        <div class="considerations-grid">
            @foreach($article->getData()['calibration_benefits'] as $benefit)
            <div class="consideration-card">
                <div class="consideration-title">
                    @switch($benefit['icon_class'])
                        @case('shield')
                            🛡️
                            @break
                        @case('zap')
                            ⚡
                            @break
                        @case('dollar-sign')
                            💰
                            @break
                        @case('clock')
                            🔧
                            @break
                        @default
                            ✅
                    @endswitch
                    {{ $benefit['title'] ?? 'Benefício' }}
                </div>
                <div class="consideration-description">{{ $benefit['description'] ?? '' }}</div>
                
                @if(!empty($benefit['aspects']) && is_array($benefit['aspects']))
                <ul class="consideration-list">
                    @foreach($benefit['aspects'] as $aspect)
                    <li>{{ $aspect }}</li>
                    @endforeach
                </ul>
                @endif
                
                @if(!empty($benefit['estimated_savings']))
                <div class="info-note" style="margin-top: 12px;">
                    <strong>💰 Economia estimada:</strong> {{ $benefit['estimated_savings'] }}
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @endif
        
        <!-- Procedimento de Calibragem -->
        @if(!empty($article->getData()['calibration_procedure']))
        <h2>🔧 Como Calibrar Corretamente</h2>
        
        <div class="considerations-grid">
            @foreach($article->getData()['calibration_procedure'] as $step)
            <div class="consideration-card">
                <div class="consideration-title">
                    {{ $step['number'] ?? '1' }}. {{ $step['title'] ?? 'Passo' }}
                </div>
                <div class="consideration-description">{{ $step['description'] ?? '' }}</div>
                
                @if(!empty($step['details']) && is_array($step['details']))
                <ul class="consideration-list">
                    @foreach($step['details'] as $detail)
                    <li>{{ $detail }}</li>
                    @endforeach
                </ul>
                @endif
            </div>
            @endforeach
        </div>
        @endif
        
        <!-- Alertas Críticos -->
        @if(!empty($article->getData()['critical_alerts']))
        <h2>🚨 Alertas Críticos de Segurança</h2>
        
        @foreach($article->getData()['critical_alerts'] as $alert)
        <div class="safety-alert {{ strtolower($alert['type'] ?? 'critical') }}">
            <div class="alert-title">
                @switch(strtolower($alert['type'] ?? 'critical'))
                    @case('crítico')
                    @case('critical')
                        🚨
                        @break
                    @case('importante')
                    @case('important')
                        ⚠️
                        @break
                    @case('atenção')
                    @case('warning')
                        ⚠️
                        @break
                    @default
                        ℹ️
                @endswitch
                {{ $alert['title'] ?? 'Alerta de Segurança' }}
            </div>
            <p>{{ $alert['description'] ?? '' }}</p>
            @if(!empty($alert['consequence']))
            <p><strong>Consequência:</strong> {{ $alert['consequence'] }}</p>
            @endif
        </div>
        @endforeach
        @endif
        
        <!-- Perguntas Frequentes -->
        @if(!empty($article->getData()['faq']))
        <h2>❓ Perguntas Frequentes sobre {{ $article->getData()['vehicle_info']['full_name'] ?? 'Pressão dos Pneus' }}</h2>
        
        <amp-accordion expand-single-section>
            @foreach($article->getData()['faq'] as $faq)
            <section>
                <h4>{{ $faq['pergunta'] ?? '' }}</h4>
                <div class="faq-content">
                    <p>{{ $faq['resposta'] ?? '' }}</p>
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
        @if(!empty($article->getData()['final_considerations']))
        <h2>📝 Considerações Finais</h2>
        
        <div class="info-note">
            {{ $article->getData()['final_considerations'] }}
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

        <!-- Nota informativa -->
        @include('auto-info-center::article.partials.info_note_manual')
        
        <!-- Footer do artigo -->
        @include('auto-info-center::article.partials.article_footer')
    </article>
</div>
@endsection