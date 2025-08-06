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
    
    /* Tabela principal de óleos */
    .oil-main-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        margin-bottom: 24px;
        background-color: #fff;
    }
    
    .oil-main-table th {
        background: linear-gradient(135deg, #0E368A, #1a4da8);
        color: white;
        text-align: left;
        padding: 10px;
        font-weight: 600;
        font-size: 12px;
    }
    
    .oil-main-table td {
        padding: 10px;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: top;
    }
    
    .oil-main-table tr:nth-child(even) td {
        background-color: #f8fafc;
    }
    
    .oil-main-table tr:last-child td {
        border-bottom: none;
    }
    
    /* Cards de especificações de óleo */
    .oil-spec-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        background-color: #fff;
    }
    
    .oil-spec-header {
        background: linear-gradient(135deg, #0E368A, #1a4da8);
        color: white;
        padding: 16px;
        font-weight: 600;
        font-size: 16px;
    }
    
    .oil-spec-body {
        padding: 20px;
    }
    
    .oil-spec-body h4 {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 12px;
        color: #151C25;
        display: flex;
        align-items: center;
    }
    
    .oil-spec-body h4:before {
        content: '⚙️';
        margin-right: 8px;
    }
    
    .oil-characteristics {
        margin: 0;
        padding-left: 20px;
        list-style: none;
        margin-bottom: 16px;
    }
    
    .oil-characteristics li {
        margin-bottom: 8px;
        color: #4a5568;
        position: relative;
        padding-left: 16px;
    }
    
    .oil-characteristics li:before {
        content: '✓';
        color: #0E368A;
        position: absolute;
        left: 0;
        font-weight: bold;
    }
    
    .brands-box {
        background-color: #f8fafc;
        padding: 12px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        font-size: 14px;
    }
    
    .brands-label {
        font-weight: 600;
        color: #151C25;
        margin-bottom: 4px;
    }
    
    /* Tabela de intervalos de troca */
    .interval-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 20px;
        background-color: #fff;
    }
    
    .interval-header {
        padding: 16px;
        font-weight: 600;
        font-size: 16px;
        color: white;
    }
    
    .interval-header.normal {
        background: linear-gradient(135deg, #10b981, #059669);
    }
    
    .interval-header.severe {
        background: linear-gradient(135deg, #f59e0b, #d97706);
    }
    
    .interval-header.old {
        background: linear-gradient(135deg, #6b7280, #4b5563);
    }
    
    .interval-body {
        padding: 20px;
    }
    
    .interval-time {
        font-size: 18px;
        font-weight: 700;
        color: #151C25;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
    }
    
    .interval-time:before {
        content: '🕐';
        margin-right: 8px;
    }
    
    .interval-conditions {
        margin: 0;
        padding-left: 20px;
        list-style: none;
        margin-bottom: 12px;
    }
    
    .interval-conditions li {
        margin-bottom: 6px;
        color: #4a5568;
        position: relative;
        padding-left: 16px;
        font-size: 14px;
    }
    
    .interval-conditions li:before {
        content: '•';
        position: absolute;
        left: 0;
        font-weight: bold;
    }
    
    .interval-conditions.normal li:before {
        color: #10b981;
    }
    
    .interval-conditions.severe li:before {
        color: #f59e0b;
    }
    
    .interval-conditions.old li:before {
        color: #6b7280;
    }
    
    .interval-note {
        font-size: 12px;
        color: #6b7280;
        font-style: italic;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid #e2e8f0;
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
        background: linear-gradient(135deg, rgba(251, 191, 36, 0.1), rgba(245, 158, 11, 0.1));
        padding: 16px;
        border-radius: 8px;
        font-size: 14px;
        margin: 24px 0;
        border-left: 4px solid #f59e0b;
        color: #92400e;
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
        
        .oil-spec-body,
        .interval-body {
            padding: 16px;
        }
        
        .oil-main-table {
            font-size: 12px;
        }
        
        .oil-main-table th,
        .oil-main-table td {
            padding: 8px;
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
        
        .oil-main-table th,
        .oil-main-table td {
            padding: 6px;
            font-size: 11px;
        }
        
        .interval-time {
            font-size: 16px;
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
        
        <!-- Tabela Principal de Óleos -->
        @if(!empty($article->oil_table) && is_array($article->oil_table) && count($article->oil_table) > 0)
        <h2>📋 Tabela de Óleo por Geração e Motor</h2>
        
        <table class="oil-main-table">
            <thead>
                <tr>
                    <th>Geração</th>
                    <th>Período</th>
                    <th>Motor</th>
                    <th>Óleo Recomendado</th>
                    <th>Capacidade</th>
                    <th>Intervalo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($article->oil_table as $oilEntry)
                <tr>
                    <td><strong>{{ $oilEntry['geracao'] ?? 'N/A' }}</strong></td>
                    <td>{{ $oilEntry['periodo'] ?? 'N/A' }}</td>
                    <td>{{ $oilEntry['motor'] ?? 'N/A' }}</td>
                    <td><strong>{{ $oilEntry['oleo_recomendado'] ?? 'N/A' }}</strong></td>
                    <td>{{ $oilEntry['capacidade'] ?? 'N/A' }}</td>
                    <td>{{ $oilEntry['intervalo_troca'] ?? 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="info-note">
            ⚠️ <strong>Importante:</strong> As capacidades listadas incluem a troca do filtro de óleo. Para trocas sem substituição do filtro, reduza o volume em aproximadamente 0,2-0,3 litros para carros ou 0,1 litros para motos.
        </div>
        @endif
        
        <!-- Especificações Detalhadas por Tipo de Óleo -->
        @if(!empty($article->oil_specifications) && is_array($article->oil_specifications) && count($article->oil_specifications) > 0)
        <h2>🔧 Especificações Detalhadas por Tipo de Óleo</h2>
        
        @foreach($article->oil_specifications as $spec)
        @if(!empty($spec['tipo_oleo']))
        <div class="oil-spec-card">
            <div class="oil-spec-header">{{ $spec['tipo_oleo'] }}</div>
            <div class="oil-spec-body">
                <h4>{{ $spec['aplicacao'] ?? 'Aplicação' }}</h4>
                
                @if(!empty($spec['caracteristicas']) && is_array($spec['caracteristicas']) && count($spec['caracteristicas']) > 0)
                <ul class="oil-characteristics">
                    @foreach($spec['caracteristicas'] as $caracteristica)
                    @if(!empty($caracteristica))
                    <li>{{ $caracteristica }}</li>
                    @endif
                    @endforeach
                </ul>
                @endif
                
                @if(!empty($spec['marcas_recomendadas']))
                <div class="brands-box">
                    <div class="brands-label">Marcas recomendadas:</div>
                    @if(is_array($spec['marcas_recomendadas']))
                        {{ implode(', ', $spec['marcas_recomendadas']) }}
                    @else
                        {{ $spec['marcas_recomendadas'] }}
                    @endif
                </div>
                @endif
            </div>
        </div>
        @endif
        @endforeach
        @endif
        
        <!-- 🥇 ANÚNCIO 2: Após especificações -->
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
       
        <!-- Filtros de Óleo Recomendados -->
        @if(!empty($article->oil_filters) && is_array($article->oil_filters) && count($article->oil_filters) > 0)
        <h2>🔍 Filtros de Óleo Recomendados</h2>
        
        <table class="oil-main-table">
            <thead>
                <tr>
                    <th>Geração</th>
                    <th>Motor</th>
                    <th>Código Original</th>
                    <th>Equivalentes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($article->oil_filters as $filter)
                <tr>
                    <td>{{ $filter['geracao'] ?? 'N/A' }}</td>
                    <td>{{ $filter['motor'] ?? 'N/A' }}</td>
                    <td><strong>{{ $filter['codigo_original'] ?? 'N/A' }}</strong></td>
                    <td>
                        @if(!empty($filter['equivalentes_aftermarket']))
                            @if(is_array($filter['equivalentes_aftermarket']))
                                {{ implode(', ', $filter['equivalentes_aftermarket']) }}
                            @else
                                {{ $filter['equivalentes_aftermarket'] }}
                            @endif
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
        
        <!-- Intervalos de Troca por Condição de Uso -->
        @if(!empty($article->maintenance_intervals) && is_array($article->maintenance_intervals) && count($article->maintenance_intervals) > 0)
        <h2>⏰ Intervalos de Troca por Condição de Uso</h2>
        
        @foreach($article->maintenance_intervals as $interval)
        @if(!empty($interval['tipo_uso']))
        @php
            $headerClass = 'normal';
            if (!empty($interval['cor_badge'])) {
                switch($interval['cor_badge']) {
                    case 'yellow':
                        $headerClass = 'severe';
                        break;
                    case 'gray':
                        $headerClass = 'old';
                        break;
                    default:
                        $headerClass = 'normal';
                }
            }
        @endphp
        
        <div class="interval-card">
            <div class="interval-header {{ $headerClass }}">
                {{ $interval['tipo_uso'] }}
            </div>
            <div class="interval-body">
                <div class="interval-time">{{ $interval['intervalo'] ?? 'N/A' }}</div>
                
                @if(!empty($interval['condicoes']) && is_array($interval['condicoes']) && count($interval['condicoes']) > 0)
                <h5 style="font-size: 14px; font-weight: 600; margin-bottom: 8px; color: #151C25;">Condições de uso:</h5>
                <ul class="interval-conditions {{ $headerClass }}">
                    @foreach($interval['condicoes'] as $condicao)
                    @if(!empty($condicao))
                    <li>{{ $condicao }}</li>
                    @endif
                    @endforeach
                </ul>
                @endif
                
                @if(!empty($interval['observacoes']))
                <div class="interval-note">{{ $interval['observacoes'] }}</div>
                @endif
            </div>
        </div>
        @endif
        @endforeach
        @endif

        <!-- 🥈 ANÚNCIO 3: Após intervalos -->
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