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
    
    /* Cards de especificações de pneus */
    .tire-spec-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        background-color: #fff;
    }
    
    .tire-spec-header {
        background: linear-gradient(135deg, #0E368A, #1a4da8);
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
        background-color: #f8fafc;
        padding: 4px 8px;
        border-radius: 4px;
    }
    
    /* Cards de recomendação de pneus */
    .tire-recommendation-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        background-color: #fff;
    }
    
    .tire-card-header {
        background: linear-gradient(135deg, #0E368A, #1a4da8);
        color: white;
        padding: 16px;
        font-weight: 600;
        font-size: 16px;
    }
    
    .tire-card-header.secondary {
        background: linear-gradient(135deg, #4a5568, #2d3748);
    }
    
    .tire-card-body {
        padding: 20px;
    }
    
    .tire-badge {
        text-align: center;
        margin-bottom: 16px;
    }
    
    .tire-badge span {
        background-color: #E06600;
        color: white;
        font-size: 11px;
        font-weight: 600;
        padding: 4px 8px;
        border-radius: 4px;
        text-transform: uppercase;
    }
    
    .tire-name {
        text-align: center;
        font-weight: 700;
        font-size: 18px;
        margin: 0 0 20px;
        color: #0E368A;
    }
    
    .price-durability {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-top: 16px;
        font-size: 13px;
    }
    
    .price-item, .durability-item {
        background-color: #f8fafc;
        padding: 8px;
        border-radius: 6px;
        text-align: center;
        border: 1px solid #e2e8f0;
    }
    
    .price-item .label, .durability-item .label {
        display: block;
        font-size: 11px;
        color: #718096;
        margin-bottom: 4px;
        text-transform: uppercase;
    }
    
    .price-item .value {
        font-weight: 600;
        color: #059669;
    }
    
    .durability-item .value {
        font-weight: 600;
        color: #1d4ed8;
    }
    
    /* Tabela de comparação */
    .comparison-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        margin-bottom: 24px;
        background-color: #fff;
    }
    
    .comparison-table th {
        background: linear-gradient(135deg, #0E368A, #1a4da8);
        color: white;
        text-align: left;
        padding: 12px;
        font-weight: 600;
        font-size: 13px;
    }
    
    .comparison-table td {
        padding: 12px;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: top;
    }
    
    .comparison-table tr:nth-child(even) td {
        background-color: #f8fafc;
    }
    
    .comparison-table tr:last-child td {
        border-bottom: none;
    }
    
    /* Guia de desgaste */
    .wear-guide {
        background-color: #f8fafc;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid #e2e8f0;
    }
    
    .wear-section {
        margin-bottom: 24px;
    }
    
    .wear-section:last-child {
        margin-bottom: 0;
    }
    
    .wear-title {
        font-weight: 600;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        color: #151C25;
        font-size: 16px;
    }
    
    .wear-icon {
        width: 24px;
        height: 24px;
        margin-right: 10px;
        flex-shrink: 0;
        color: #0E368A;
    }
    
    .wear-list {
        margin: 0;
        padding-left: 20px;
        list-style: none;
    }
    
    .wear-list li {
        margin-bottom: 8px;
        color: #4a5568;
        position: relative;
        padding-left: 16px;
    }
    
    .wear-list li:before {
        content: '•';
        color: #0E368A;
        position: absolute;
        left: 0;
        font-weight: bold;
    }
    
    .wear-list li strong {
        color: #151C25;
    }
    
    /* Dicas de manutenção */
    .maintenance-tip {
        background-color: #f8fafc;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid #e2e8f0;
    }
    
    .tip-title {
        font-weight: 600;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        color: #151C25;
        font-size: 16px;
    }
    
    .tip-icon {
        width: 24px;
        height: 24px;
        margin-right: 10px;
        flex-shrink: 0;
        color: #0E368A;
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
        
        .tire-card-body,
        .wear-guide,
        .maintenance-tip {
            padding: 16px;
        }
        
        .comparison-table {
            font-size: 13px;
        }
        
        .comparison-table th,
        .comparison-table td {
            padding: 8px;
        }
        
        .price-durability {
            gap: 4px;
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
        
        .tire-name {
            font-size: 16px;
        }
        
        .spec-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
        }
        
        .spec-value {
            align-self: flex-end;
        }
        
        .comparison-table th,
        .comparison-table td {
            padding: 6px;
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
        
        <!-- Especificações Oficiais -->
        @if(!empty($article->official_specs) && is_array($article->official_specs))
        <h2>📋 Especificações Oficiais {{ $article->vehicle_info['make'] ?? 'do Veículo' }}</h2>
        
        <!-- Especificações Pneu Dianteiro -->
        @if(!empty($article->official_specs['pneu_dianteiro']) && is_array($article->official_specs['pneu_dianteiro']))
        <div class="tire-spec-card">
            <div class="tire-spec-header">
                <span class="icon">🔄</span>
                Pneu Dianteiro
            </div>
            <div class="tire-spec-body">
                @foreach($article->official_specs['pneu_dianteiro'] as $key => $value)
                @if(!empty($value))
                <div class="spec-item">
                    <span class="spec-label">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                    <span class="spec-value">{{ $value }}</span>
                </div>
                @endif
                @endforeach
            </div>
        </div>
        @endif

        <!-- Especificações Pneu Traseiro -->
        @if(!empty($article->official_specs['pneu_traseiro']) && is_array($article->official_specs['pneu_traseiro']))
        <div class="tire-spec-card">
            <div class="tire-spec-header">
                <span class="icon">🔙</span>
                Pneu Traseiro
            </div>
            <div class="tire-spec-body">
                @foreach($article->official_specs['pneu_traseiro'] as $key => $value)
                @if(!empty($value))
                <div class="spec-item">
                    <span class="spec-label">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                    <span class="spec-value">{{ $value }}</span>
                </div>
                @endif
                @endforeach
            </div>
        </div>
        @endif
        @endif
        
        <!-- Recomendações de Pneus Dianteiros -->
        @if(!empty($article->front_tires) && is_array($article->front_tires) && count($article->front_tires) > 0)
        <h2>🛞 Melhores Pneus Dianteiros</h2>
        
        @foreach($article->front_tires as $tire)
        @if(!empty($tire['nome_pneu']))
        <div class="tire-recommendation-card">
            <div class="tire-card-header">✅ {{ $tire['categoria'] ?? 'Recomendado' }}</div>
            <div class="tire-card-body">
                <div class="tire-badge">
                    <span>{{ $tire['categoria'] === 'Melhor Custo-Benefício' ? 'MAIS VENDIDO' : 'PREMIUM' }}</span>
                </div>
                <h3 class="tire-name">{{ $tire['nome_pneu'] }}</h3>
                
                @if(!empty($tire['medida']))
                <div class="spec-item">
                    <span class="spec-label">Medida:</span>
                    <span class="spec-value">{{ $tire['medida'] }}</span>
                </div>
                @endif
                @if(!empty($tire['indice_carga']))
                <div class="spec-item">
                    <span class="spec-label">Índice de Carga:</span>
                    <span class="spec-value">{{ $tire['indice_carga'] }}</span>
                </div>
                @endif
                @if(!empty($tire['indice_velocidade']))
                <div class="spec-item">
                    <span class="spec-label">Índice de Velocidade:</span>
                    <span class="spec-value">{{ $tire['indice_velocidade'] }}</span>
                </div>
                @endif
                @if(!empty($tire['tipo']))
                <div class="spec-item">
                    <span class="spec-label">Tipo:</span>
                    <span class="spec-value">{{ $tire['tipo'] }}</span>
                </div>
                @endif
                
                @if(!empty($tire['preco_medio']) || !empty($tire['durabilidade']))
                <div class="price-durability">
                    @if(!empty($tire['preco_medio']))
                    <div class="price-item">
                        <span class="label">Preço Médio</span>
                        <span class="value">{{ $tire['preco_medio'] }}</span>
                    </div>
                    @endif
                    @if(!empty($tire['durabilidade']))
                    <div class="durability-item">
                        <span class="label">Durabilidade</span>
                        <span class="value">{{ $tire['durabilidade'] }}</span>
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
        @endif
        @endforeach
        @endif
        
        <!-- Recomendações de Pneus Traseiros -->
        @if(!empty($article->rear_tires) && is_array($article->rear_tires) && count($article->rear_tires) > 0)
        <h2>🛞 Melhores Pneus Traseiros</h2>
        
        @foreach($article->rear_tires as $tire)
        @if(!empty($tire['nome_pneu']))
        <div class="tire-recommendation-card">
            <div class="tire-card-header secondary">⭐ {{ $tire['categoria'] ?? 'Recomendado' }}</div>
            <div class="tire-card-body">
                <div class="tire-badge">
                    <span>{{ $tire['categoria'] === 'Melhor Custo-Benefício' ? 'MAIS VENDIDO' : 'PREMIUM' }}</span>
                </div>
                <h3 class="tire-name">{{ $tire['nome_pneu'] }}</h3>
                
                @if(!empty($tire['medida']))
                <div class="spec-item">
                    <span class="spec-label">Medida:</span>
                    <span class="spec-value">{{ $tire['medida'] }}</span>
                </div>
                @endif
                @if(!empty($tire['indice_carga']))
                <div class="spec-item">
                    <span class="spec-label">Índice de Carga:</span>
                    <span class="spec-value">{{ $tire['indice_carga'] }}</span>
                </div>
                @endif
                @if(!empty($tire['indice_velocidade']))
                <div class="spec-item">
                    <span class="spec-label">Índice de Velocidade:</span>
                    <span class="spec-value">{{ $tire['indice_velocidade'] }}</span>
                </div>
                @endif
                @if(!empty($tire['tipo']))
                <div class="spec-item">
                    <span class="spec-label">Tipo:</span>
                    <span class="spec-value">{{ $tire['tipo'] }}</span>
                </div>
                @endif
                
                @if(!empty($tire['preco_medio']) || !empty($tire['durabilidade']))
                <div class="price-durability">
                    @if(!empty($tire['preco_medio']))
                    <div class="price-item">
                        <span class="label">Preço Médio</span>
                        <span class="value">{{ $tire['preco_medio'] }}</span>
                    </div>
                    @endif
                    @if(!empty($tire['durabilidade']))
                    <div class="durability-item">
                        <span class="label">Durabilidade</span>
                        <span class="value">{{ $tire['durabilidade'] }}</span>
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
        @endif
        @endforeach
        @endif
        
        <!-- 🥇 ANÚNCIO 2: Após pneus traseiros -->
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
       
        <!-- Comparativo por Tipo de Uso -->
        @if(!empty($article->usage_comparison) && is_array($article->usage_comparison) && count($article->usage_comparison) > 0)
        <h2>⚖️ Comparativo por Tipo de Uso</h2>
        
        <table class="comparison-table">
            <thead>
                <tr>
                    <th>Tipo de Uso</th>
                    <th>Melhor Dianteiro</th>
                    <th>Melhor Traseiro</th>
                    <th>Características</th>
                </tr>
            </thead>
            <tbody>
                @foreach($article->usage_comparison as $usage)
                @if(!empty($usage['tipo_uso']))
                <tr>
                    <td><strong>{{ $usage['tipo_uso'] }}</strong></td>
                    <td>{{ $usage['melhor_dianteiro'] ?? 'N/A' }}</td>
                    <td>{{ $usage['melhor_traseiro'] ?? 'N/A' }}</td>
                    <td>{{ $usage['caracteristicas'] ?? 'N/A' }}</td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
        @endif
        
        <!-- Guia de Desgaste e Substituição -->
        @if(!empty($article->wear_guide) && is_array($article->wear_guide))
        <h2>🔍 Guia de Desgaste e Substituição</h2>
        
        <div class="wear-guide">
            @if(!empty($article->wear_guide['indicadores_desgaste']) && is_array($article->wear_guide['indicadores_desgaste']) && count($article->wear_guide['indicadores_desgaste']) > 0)
            <div class="wear-section">
                <div class="wear-title">
                    <span class="wear-icon">⚠️</span>
                    Indicadores de Desgaste
                </div>
                <ul class="wear-list">
                    @foreach($article->wear_guide['indicadores_desgaste'] as $indicator)
                    @if(!empty($indicator['indicador']))
                    <li><strong>{{ $indicator['indicador'] }}:</strong> {{ $indicator['descricao'] ?? '' }}</li>
                    @endif
                    @endforeach
                </ul>
            </div>
            @endif
            
            @if(!empty($article->wear_guide['quando_substituir']) && is_array($article->wear_guide['quando_substituir']) && count($article->wear_guide['quando_substituir']) > 0)
            <div class="wear-section">
                <div class="wear-title">
                    <span class="wear-icon">🔄</span>
                    Quando Substituir
                </div>
                <ul class="wear-list">
                    @foreach($article->wear_guide['quando_substituir'] as $situation)
                    @if(!empty($situation['situacao']))
                    <li><strong>{{ $situation['situacao'] }}:</strong> {{ $situation['descricao'] ?? '' }}</li>
                    @endif
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
        @endif

        <!-- 🥈 ANÚNCIO 3: Após guia de desgaste -->
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
       
        <!-- Dicas de Manutenção -->
        @if(!empty($article->maintenance_tips) && is_array($article->maintenance_tips) && count($article->maintenance_tips) > 0)
        <h2>🛠️ Dicas de Manutenção</h2>
        
        @foreach($article->maintenance_tips as $tipCategory)
        @if(!empty($tipCategory['categoria']))
        <div class="maintenance-tip">
            <div class="tip-title">
                <span class="tip-icon">💡</span>
                {{ $tipCategory['categoria'] }}
            </div>
            @if(!empty($tipCategory['dicas']) && is_array($tipCategory['dicas']) && count($tipCategory['dicas']) > 0)
            <ul class="wear-list">
                @foreach($tipCategory['dicas'] as $tip)
                @if(!empty($tip))
                <li>{{ $tip }}</li>
                @endif
                @endforeach
            </ul>
            @endif
        </div>
        @endif
        @endforeach
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

        <!-- Nota informativa -->
        @include('auto-info-center::article.partials.info_note_manual')
        
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