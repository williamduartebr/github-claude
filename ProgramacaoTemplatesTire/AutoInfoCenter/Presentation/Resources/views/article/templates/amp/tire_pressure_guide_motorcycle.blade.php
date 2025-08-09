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
    .critical-safety-alert {
        background: linear-gradient(135deg, #fef2f2, #fee2e2);
        border: 2px solid #DC2626;
        border-radius: 12px;
        padding: 20px;
        margin: 24px 0;
        position: relative;
    }

    .critical-safety-alert::before {
        content: '🚨';
        font-size: 24px;
        position: absolute;
        top: 16px;
        left: 16px;
    }

    .critical-alert-title {
        font-size: 18px;
        font-weight: 700;
        color: #991b1b;
        margin-bottom: 12px;
        padding-left: 40px;
    }

    .critical-alert-content {
        color: #7f1d1d;
        font-weight: 500;
        padding-left: 40px;
    }

    /* Especificações dos pneus para motos */
    .motorcycle-tire-specs {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 24px;
    }

    .tire-spec-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        background-color: #fff;
    }

    .tire-spec-header {
        background: linear-gradient(135deg, #DC2626, #b91c1c);
        color: white;
        padding: 16px;
        font-weight: 600;
        font-size: 16px;
        display: flex;
        align-items: center;
    }

    .tire-spec-header .icon {
        margin-right: 10px;
        font-size: 18px;
    }

    .tire-spec-body {
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

    /* Tabela de pressões para motos */
    .motorcycle-pressure-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        margin-bottom: 24px;
    }

    .motorcycle-pressure-table th {
        background: linear-gradient(135deg, #DC2626, #b91c1c);
        color: white;
        text-align: center;
        padding: 12px;
        font-weight: 600;
        font-size: 13px;
    }

    .motorcycle-pressure-table td {
        padding: 12px;
        text-align: center;
        border-bottom: 1px solid #e2e8f0;
        background-color: #fff;
    }

    .motorcycle-pressure-table tr:nth-child(even) td {
        background-color: #fef2f2;
    }

    .motorcycle-pressure-table tr:last-child td {
        border-bottom: none;
    }

    .pressure-highlight {
        font-weight: 700;
        color: #DC2626;
        background-color: #fee2e2;
        padding: 6px 8px;
        border-radius: 4px;
    }

    /* Recomendações específicas para motos */
    .motorcycle-recommendation {
        background-color: #fef2f2;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        border-left: 4px solid #DC2626;
        position: relative;
    }

    .recommendation-title {
        font-weight: 600;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        color: #991b1b;
        font-size: 16px;
    }

    .recommendation-icon {
        width: 24px;
        height: 24px;
        margin-right: 10px;
        flex-shrink: 0;
    }

    .recommendation-pressure {
        background-color: #DC2626;
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-weight: 700;
        display: inline-block;
        margin: 8px 0;
    }

    /* Gráfico de impacto específico para motos */
    .motorcycle-impact-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .motorcycle-impact-card {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        background-color: #fff;
        text-align: center;
        position: relative;
    }

    .motorcycle-impact-card.subcalibrado {
        border-color: #DC2626;
        background: linear-gradient(135deg, #fef2f2, #fee2e2);
    }

    .motorcycle-impact-card.ideal {
        border-color: #059669;
        background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    }

    .motorcycle-impact-card.sobrecalibrado {
        border-color: #f59e0b;
        background: linear-gradient(135deg, #fffbeb, #fef3c7);
    }

    .impact-title {
        font-weight: 700;
        margin-bottom: 16px;
        font-size: 16px;
    }

    .impact-metric {
        margin-bottom: 16px;
    }

    .impact-metric-label {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 6px;
        font-weight: 500;
    }

    .impact-value {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .impact-description {
        font-size: 11px;
        color: #4b5563;
        line-height: 1.4;
    }

    /* Pneus alternativos para motos */
    .alternative-tires-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .alternative-tire-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        background-color: #fff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .alternative-tire-header {
        background: linear-gradient(135deg, #4b5563, #374151);
        color: white;
        padding: 16px;
        font-weight: 600;
        font-size: 14px;
        text-align: center;
    }

    .alternative-tire-body {
        padding: 20px;
    }

    .tire-pressure-display {
        background-color: #f3f4f6;
        border-radius: 8px;
        padding: 12px;
        margin: 12px 0;
        text-align: center;
    }

    .pressure-front-rear {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .pressure-value {
        font-weight: 700;
        color: #DC2626;
        font-size: 14px;
    }

    .tire-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 12px;
    }

    .tire-tag {
        background-color: #e5e7eb;
        color: #374151;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 500;
    }

    /* Procedimento de calibragem para motos */
    .motorcycle-procedure {
        background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        border-radius: 12px;
        padding: 24px;
        border: 1px solid #e2e8f0;
        position: relative;
    }

    .procedure-step {
        display: flex;
        margin-bottom: 24px;
        align-items: flex-start;
    }

    .procedure-step:last-child {
        margin-bottom: 0;
    }

    .step-number {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #DC2626, #b91c1c);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 16px;
        flex-shrink: 0;
        font-weight: 700;
        font-size: 16px;
        box-shadow: 0 2px 4px rgba(220, 38, 38, 0.3);
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
        margin-bottom: 8px;
        color: #4a5568;
        line-height: 1.6;
    }

    .step-tips {
        background-color: #fef2f2;
        border-left: 4px solid #DC2626;
        padding: 12px;
        margin-top: 12px;
        border-radius: 4px;
    }

    .step-tips ul {
        margin: 0;
        padding-left: 16px;
    }

    .step-tips li {
        font-size: 14px;
        color: #7f1d1d;
        margin-bottom: 4px;
        font-weight: 500;
    }

    /* Equipamentos necessários para motos */
    .equipment-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .equipment-card {
        background-color: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .equipment-item {
        font-weight: 600;
        color: #151C25;
        margin-bottom: 8px;
    }

    .equipment-importance {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .equipment-importance.essencial {
        background-color: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .equipment-importance.muito-util {
        background-color: #fff7ed;
        color: #c2410c;
        border: 1px solid #fed7aa;
    }

    .equipment-importance.recomendado {
        background-color: #f0fdf4;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    /* Sinais de problemas para motos */
    .problem-signs-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
        margin-bottom: 24px;
    }

    .problem-sign-card {
        border-left: 4px solid #DC2626;
        background-color: #fef2f2;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid #fecaca;
    }

    .problem-sign-title {
        font-weight: 600;
        color: #991b1b;
        margin-bottom: 12px;
        font-size: 16px;
        display: flex;
        align-items: center;
    }

    .problem-sign-icon {
        margin-right: 8px;
        font-size: 18px;
    }

    .problem-signs-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .problem-signs-list li {
        margin-bottom: 8px;
        padding-left: 20px;
        position: relative;
        color: #7f1d1d;
        font-weight: 500;
    }

    .problem-signs-list li::before {
        content: '⚠️';
        position: absolute;
        left: 0;
        top: 0;
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

        .motorcycle-tire-specs {
            grid-template-columns: 1fr;
        }

        .tire-spec-body,
        .motorcycle-recommendation {
            padding: 16px;
        }

        .motorcycle-pressure-table {
            font-size: 13px;
        }

        .motorcycle-pressure-table th,
        .motorcycle-pressure-table td {
            padding: 8px 4px;
        }

        .step-number {
            width: 36px;
            height: 36px;
            font-size: 14px;
        }

        .motorcycle-impact-grid {
            grid-template-columns: 1fr;
        }

        .alternative-tires-grid {
            grid-template-columns: 1fr;
        }

        .equipment-grid {
            grid-template-columns: 1fr;
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

        .spec-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
        }

        .spec-value {
            align-self: flex-end;
        }

        .pressure-front-rear {
            flex-direction: column;
            gap: 8px;
        }

        .critical-alert-title,
        .critical-alert-content {
            padding-left: 20px;
        }

        .critical-safety-alert::before {
            left: 8px;
        }
    }
</style>

<!-- CSS adicional para a tabela (adicionar ao style amp-custom existente) -->
<style>
    .pressure-table-container {
        background: linear-gradient(135deg, rgba(220, 38, 38, 0.05), rgba(220, 38, 38, 0.1));
        border-radius: 12px;
        padding: 20px;
        margin: 24px 0;
        overflow-x: auto;
        border: 1px solid rgba(220, 38, 38, 0.2);
    }

    .motorcycle-pressure-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        margin: 0;
        min-width: 700px;
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .motorcycle-pressure-table th {
        background: linear-gradient(135deg, #DC2626, #991b1b);
        color: white;
        padding: 14px 10px;
        text-align: center;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .motorcycle-pressure-table th:first-child {
        text-align: left;
        padding-left: 16px;
    }

    .motorcycle-pressure-table td {
        padding: 12px 10px;
        text-align: center;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .condition-cell {
        text-align: left !important;
        padding-left: 16px !important;
        font-weight: 500;
        color: #374151;
    }

    .condition-icon {
        margin-right: 8px;
        font-size: 16px;
    }

    .occupants-cell {
        text-align: left !important;
        min-width: 140px;
    }

    .occupants-text {
        font-weight: 500;
        color: #1f2937;
    }

    .luggage-info {
        color: #6b7280;
        font-style: italic;
    }

    .pressure-cell {
        font-weight: 600;
        min-width: 100px;
    }

    .pressure-highlight {
        background: linear-gradient(135deg, #DC2626, #991b1b);
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
        display: inline-block;
        min-width: 80px;
    }

    .observation-cell {
        text-align: left !important;
        max-width: 200px;
        color: #4b5563;
        font-size: 12px;
        line-height: 1.4;
    }

    .motorcycle-pressure-table tr:nth-child(even) {
        background-color: #f8fafc;
    }

    .motorcycle-pressure-table tr:hover {
        background-color: rgba(220, 38, 38, 0.05);
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
        .motorcycle-pressure-table {
            font-size: 12px;
            min-width: 600px;
        }

        .motorcycle-pressure-table th,
        .motorcycle-pressure-table td {
            padding: 8px 6px;
        }

        .condition-cell,
        .observation-cell {
            padding-left: 12px !important;
        }

        .pressure-highlight {
            padding: 4px 8px;
            font-size: 11px;
            min-width: 70px;
        }

        .legend-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .motorcycle-pressure-table {
            min-width: 500px;
            font-size: 11px;
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

        <!-- Alerta de Segurança Crítico para Motocicletas -->
        @if(!empty($article->critical_alerts))
        @php $criticalAlert = collect($article->critical_alerts)->firstWhere('is_critical', true) @endphp
        @if($criticalAlert)
        <div class="critical-safety-alert">
            <div class="critical-alert-title">{{ $criticalAlert['title'] }}</div>
            <div class="critical-alert-content">
                {{ $criticalAlert['description'] }} {{ $criticalAlert['consequence'] }}
            </div>
        </div>
        @endif
        @endif

        <!-- 🥇 ANÚNCIO 1: Após introdução -->
        <div class="ad-container">
            <div class="ad-label">Publicidade</div>
            <amp-ad width="100vw" height="320" type="adsense"
                data-ad-client="{{ Config::get('services.google_adsense.id') }}" data-ad-slot="7414648059"
                data-auto-format="rspv" data-full-width>
                <div overflow></div>
            </amp-ad>
        </div>

        <!-- Especificações dos Pneus da Motocicleta -->
        @if(!empty($article->tire_specifications))
        <h2>🏍️ Especificações dos Pneus Originais</h2>

        <div class="motorcycle-tire-specs">
            @if(!empty($article->tire_specifications['front_tire']))
            <div class="tire-spec-card">
                <div class="tire-spec-header">
                    <span class="icon">🔄</span>
                    Pneu Dianteiro
                </div>
                <div class="tire-spec-body">
                    @php $frontTire = $article->tire_specifications['front_tire'] @endphp
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
                    @if(!empty($frontTire['construction']))
                    <div class="spec-item">
                        <span class="spec-label">Construção:</span>
                        <span class="spec-value">{{ $frontTire['construction'] }}</span>
                    </div>
                    @endif
                    @if(!empty($frontTire['original_brands']))
                    <div class="spec-item">
                        <span class="spec-label">Marca Original:</span>
                        <span class="spec-value">{{ $frontTire['original_brands'] }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            @if(!empty($article->tire_specifications['rear_tire']))
            <div class="tire-spec-card">
                <div class="tire-spec-header">
                    <span class="icon">🔙</span>
                    Pneu Traseiro
                </div>
                <div class="tire-spec-body">
                    @php $rearTire = $article->tire_specifications['rear_tire'] @endphp
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
                    @if(!empty($rearTire['construction']))
                    <div class="spec-item">
                        <span class="spec-label">Construção:</span>
                        <span class="spec-value">{{ $rearTire['construction'] }}</span>
                    </div>
                    @endif
                    @if(!empty($rearTire['original_brands']))
                    <div class="spec-item">
                        <span class="spec-label">Marca Original:</span>
                        <span class="spec-value">{{ $rearTire['original_brands'] }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        @if(!empty($article->tire_specifications['observation']))
        <div class="info-note">
            <strong>⚠️ Importante:</strong> {{ $article->tire_specifications['observation'] }}
        </div>
        @endif
        @endif


        <!-- Tabela de Pressões Recomendadas -->
        @if(!empty($article->pressure_table))
        <h2>📊 Pressões Recomendadas</h2>

        <div class="pressure-table-container">
            <table class="motorcycle-pressure-table">
                <thead>
                    <tr>
                        <th>Condição de Uso</th>
                        <th>Ocupantes</th>
                        <th>Dianteiro</th>
                        <th>Traseiro</th>
                        <th>Observações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($article->pressure_table as $condition)
                    <tr class="{{ $condition['css_class'] ?? 'bg-white' }}">
                        <td class="condition-cell">
                            @switch($condition['condition'] ?? '')
                            @case('Uso Solo (Rua)')
                            <span class="condition-icon">🏍️</span>
                            @break
                            @case('Com Passageiro (Rua)')
                            <span class="condition-icon">👥</span>
                            @break
                            @case('Pilotagem Esportiva')
                            <span class="condition-icon">🏁</span>
                            @break
                            @case('Com Carga ou Bagagem')
                            <span class="condition-icon">🎒</span>
                            @break
                            @default
                            <span class="condition-icon">🏍️</span>
                            @endswitch
                            <strong>{{ $condition['condition'] ?? '' }}</strong>
                        </td>
                        <td class="occupants-cell">
                            <span class="occupants-text">{{ $condition['occupants'] ?? '' }}</span>
                            @if(!empty($condition['luggage']))
                            <br><small class="luggage-info">{{ $condition['luggage'] }}</small>
                            @endif
                        </td>
                        <td class="pressure-cell">
                            <span class="pressure-highlight">{{ $condition['front_pressure'] ?? '' }}</span>
                        </td>
                        <td class="pressure-cell">
                            <span class="pressure-highlight">{{ $condition['rear_pressure'] ?? '' }}</span>
                        </td>
                        <td class="observation-cell">
                            <small>{{ $condition['observation'] ?? '' }}</small>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Legenda da Tabela -->
        <div class="table-legend">
            <h3>📋 Legenda:</h3>
            <div class="legend-grid">
                <div class="legend-item">
                    <span class="legend-icon">🏍️</span>
                    <span><strong>Uso Solo:</strong> Pilotagem urbana e rodoviária normal</span>
                </div>
                <div class="legend-item">
                    <span class="legend-icon">👥</span>
                    <span><strong>Com Passageiro:</strong> Piloto + garupa em trajetos normais</span>
                </div>
                <div class="legend-item">
                    <span class="legend-icon">🏁</span>
                    <span><strong>Esportiva:</strong> Pilotagem mais agressiva, curvas e acelerações</span>
                </div>
                <div class="legend-item">
                    <span class="legend-icon">🎒</span>
                    <span><strong>Com Carga:</strong> Viagens longas ou bagagem pesada</span>
                </div>
            </div>
        </div>


        @endif

        <!-- Recomendações por Tipo de Uso -->
        @if(!empty($article->usage_recommendations))
        <h2>💡 Recomendações por Tipo de Uso</h2>

        @foreach($article->usage_recommendations as $recommendation)
        <div class="motorcycle-recommendation">
            <div class="recommendation-title">

                {{ $recommendation['category'] }}
            </div>
            <div class="recommendation-pressure">{{ $recommendation['recommended_pressure'] ?? 'Conforme tabela' }}
            </div>
            <p>{{ $recommendation['description'] ?? '' }}</p>
            @if(!empty($recommendation['technical_tip']))
            <div class="info-note">
                <strong>🔧 Dica técnica:</strong> {{ $recommendation['technical_tip'] }}
            </div>
            @endif
        </div>
        @endforeach
        @endif

        <!-- 🥇 ANÚNCIO 2: Após recomendações -->
        <div class="ad-container">
            <div class="ad-label">Publicidade</div>
            <amp-ad width="100vw" height="320" type="adsense"
                data-ad-client="{{ Config::get('services.google_adsense.id') }}" data-ad-slot="8344586349"
                data-auto-format="rspv" data-full-width>
                <div overflow></div>
            </amp-ad>
        </div>

        <!-- Gráfico de Impacto da Calibragem -->
        @if(!empty($article->impact_comparison))
        <h2>📈 Impacto da Calibragem no Desempenho</h2>

        <div class="motorcycle-impact-grid">
            @php $comparison = $article->impact_comparison @endphp

            <!-- Subcalibrado -->
            @if(!empty($comparison['under_inflated']))
            <div class="motorcycle-impact-card subcalibrado">
                <h3 class="impact-title">❌ Subcalibrado</h3>
                <div class="impact-metric">
                    <div class="impact-metric-label">Desgaste dos Pneus</div>
                    <div class="impact-value" style="color: #DC2626;">{{ $comparison['under_inflated']['wear'] }}%</div>
                    <div class="impact-description">Maior desgaste</div>
                </div>
                <div class="impact-metric">
                    <div class="impact-metric-label">Consumo de Combustível</div>
                    <div class="impact-value" style="color: #DC2626;">+{{ $comparison['under_inflated']['consumption']
                        }}%</div>
                    <div class="impact-description">Aumento no consumo</div>
                </div>
                <div class="impact-metric">
                    <div class="impact-metric-label">Estabilidade</div>
                    <div class="impact-value" style="color: #DC2626;">{{
                        $comparison['under_inflated']['stability_asphalt'] }}%</div>
                    <div class="impact-description">Redução da estabilidade</div>
                </div>
            </div>
            @endif

            <!-- Calibragem Ideal -->
            @if(!empty($comparison['ideal']))
            <div class="motorcycle-impact-card ideal">
                <h3 class="impact-title">✅ Calibragem Ideal</h3>
                <div class="impact-metric">
                    <div class="impact-metric-label">Desgaste dos Pneus</div>
                    <div class="impact-value" style="color: #059669;">{{ $comparison['ideal']['wear'] }}%</div>
                    <div class="impact-description">Desgaste mínimo</div>
                </div>
                <div class="impact-metric">
                    <div class="impact-metric-label">Consumo de Combustível</div>
                    <div class="impact-value" style="color: #059669;">{{ $comparison['ideal']['consumption'] }}%</div>
                    <div class="impact-description">Consumo otimizado</div>
                </div>
                <div class="impact-metric">
                    <div class="impact-metric-label">Estabilidade</div>
                    <div class="impact-value" style="color: #059669;">{{ $comparison['ideal']['stability_asphalt'] }}%
                    </div>
                    <div class="impact-description">Máxima estabilidade</div>
                </div>
            </div>
            @endif

            <!-- Sobrecalibrado -->
            @if(!empty($comparison['over_inflated']))
            <div class="motorcycle-impact-card sobrecalibrado">
                <h3 class="impact-title">⚠️ Sobrecalibrado</h3>
                <div class="impact-metric">
                    <div class="impact-metric-label">Desgaste dos Pneus</div>
                    <div class="impact-value" style="color: #f59e0b;">{{ $comparison['over_inflated']['wear'] }}%</div>
                    <div class="impact-description">Desgaste no centro</div>
                </div>
                <div class="impact-metric">
                    <div class="impact-metric-label">Conforto</div>
                    <div class="impact-value" style="color: #f59e0b;">{{ $comparison['over_inflated']['comfort'] }}%
                    </div>
                    <div class="impact-description">Menor conforto</div>
                </div>
                <div class="impact-metric">
                    <div class="impact-metric-label">Aderência</div>
                    <div class="impact-value" style="color: #f59e0b;">{{
                        $comparison['over_inflated']['stability_asphalt'] }}%</div>
                    <div class="impact-description">Menor área de contato</div>
                </div>
            </div>
            @endif
        </div>

        <div class="info-note">
            <strong>⚠️ Importante para motocicletas:</strong> Em motos, pequenas variações de pressão têm impacto muito
            maior na estabilidade que em carros. Uma diferença de 3-5 PSI pode comprometer seriamente a segurança,
            especialmente em curvas e frenagens.
        </div>
        @endif

        <!-- Adaptações para Pneus Alternativos -->
        @if(!empty($article->alternative_tires))
        <h2>🔄 Adaptações para Pneus Alternativos</h2>

        <div class="alternative-tires-grid">
            @foreach($article->alternative_tires as $alternative)
            <div class="alternative-tire-card">
                <div class="alternative-tire-header">{{ $alternative['category'] }}</div>
                <div class="alternative-tire-body">
                    <div class="tire-pressure-display">
                        <div class="pressure-front-rear">
                            <div>
                                <strong>Dianteiro:</strong>
                                <span class="pressure-value">{{ $alternative['front_pressure'] ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <strong>Traseiro:</strong>
                                <span class="pressure-value">{{ $alternative['rear_pressure'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                    <p style="font-size: 14px; color: #4b5563;">{{ $alternative['description'] ?? '' }}</p>
                    @if(!empty($alternative['tags']))
                    <div class="tire-tags">
                        @foreach($alternative['tags'] as $tag)
                        <span class="tire-tag">{{ $tag }}</span>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Procedimento de Calibragem -->
        @if(!empty($article->calibration_procedure))
        <h2>🔧 Procedimento de Calibragem Correto</h2>

        <div class="motorcycle-procedure">
            @foreach($article->calibration_procedure as $step)
            <div class="procedure-step">
                <div class="step-number">{{ $step['number'] }}</div>
                <div class="step-content">
                    <h3>{{ $step['title'] }}</h3>
                    <p>{{ $step['description'] }}</p>
                    @if(!empty($step['tips']))
                    <div class="step-tips">
                        <ul>
                            @foreach($step['tips'] as $tip)
                            <li>{{ $tip }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <!-- 🥈 ANÚNCIO 3: Após procedimento -->
        <div class="ad-container">
            <div class="ad-label">Publicidade</div>
            <amp-ad width="100vw" height="320" type="adsense"
                data-ad-client="{{ Config::get('services.google_adsense.id') }}" data-ad-slot="1402260703"
                data-auto-format="rspv" data-full-width>
                <div overflow></div>
            </amp-ad>
        </div>

        <!-- Equipamentos Necessários -->
        @if(!empty($article->required_equipment))
        <h2>🛠️ Equipamentos Necessários</h2>

        <div class="equipment-grid">
            @foreach($article->required_equipment as $equipment)
            <div class="equipment-card">
                <div class="equipment-item">{{ $equipment['item'] ?? '' }}</div>
                <div
                    class="equipment-importance {{ strtolower(str_replace(' ', '-', $equipment['importance'] ?? 'normal')) }}">
                    {{ $equipment['importance'] ?? 'Normal' }}
                </div>
                @if(!empty($equipment['characteristics']))
                <p style="font-size: 13px; color: #6b7280; margin-bottom: 8px;">{{ $equipment['characteristics'] }}</p>
                @endif
                @if(!empty($equipment['average_price']))
                <p style="font-size: 14px; font-weight: 600; color: #059669;">{{ $equipment['average_price'] }}</p>
                @endif
                @if(!empty($equipment['recommendation']))
                <p style="font-size: 12px; color: #4b5563; font-style: italic;">{{ $equipment['recommendation'] }}</p>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        <!-- Cuidados Especiais -->
        @if(!empty($article->special_care))
        <h2>⚠️ Cuidados Especiais para Motocicletas</h2>

        @foreach($article->special_care as $careGroup)
        <div class="motorcycle-recommendation">
            <div class="recommendation-title">
                {{ $careGroup['category'] }}
            </div>
            @if(!empty($careGroup['care_items']))
            <ul style="margin: 16px 0; padding-left: 20px;">
                @foreach($careGroup['care_items'] as $item)
                <li style="margin-bottom: 8px; color: #7f1d1d; font-weight: 500;">{{ $item }}</li>
                @endforeach
            </ul>
            @endif
        </div>
        @endforeach
        @endif

        <!-- Sinais de Problemas -->
        @if(!empty($article->problem_signs))
        <h2>🚨 Sinais de Problemas na Calibragem</h2>

        <div class="problem-signs-grid">
            @foreach($article->problem_signs as $problem)
            <div class="problem-sign-card">
                <div class="problem-sign-title">
                    <span class="problem-sign-icon">⚠️</span>
                    {{ $problem['title'] }}
                </div>
                <ul class="problem-signs-list">
                    @foreach($problem['signs'] as $sign)
                    <li>{{ $sign }}</li>
                    @endforeach
                </ul>
            </div>
            @endforeach
        </div>
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

        <!-- Considerações Finais -->
        @if(!empty($article->final_considerations))
            <h2>📝 Considerações Finais</h2>
        
            <div class="info-note">
            {!! $article->final_considerations !!}
            </div>
        @endif

        <!-- Nota informativa -->
        @include('auto-info-center::article.partials.info_note_manual')

        <!-- Footer do artigo -->
        @include('auto-info-center::article.partials.article_footer')
    </article>
</div>
@endsection