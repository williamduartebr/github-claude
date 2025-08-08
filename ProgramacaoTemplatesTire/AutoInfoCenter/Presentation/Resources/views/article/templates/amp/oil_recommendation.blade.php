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

    /* Cards de recomendação de óleo */
    .oil-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        transition: box-shadow 0.2s ease;
    }

    .oil-card:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .oil-card-header {
        background: linear-gradient(135deg, #0E368A, #1a4da8);
        color: white;
        padding: 16px;
        font-weight: 600;
        font-size: 16px;
    }

    .oil-card-header.alt {
        background: linear-gradient(135deg, #4a5568, #2d3748);
    }

    .oil-card-body {
        padding: 20px;
        background-color: #fff;
    }

    .oil-card-title {
        text-align: center;
        font-weight: 700;
        font-size: 18px;
        margin: 0 0 20px;
        color: #0E368A;
    }

    .oil-spec {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        padding: 8px 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 14px;
    }

    .oil-spec:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }

    .oil-spec-label {
        color: #718096;
        font-weight: 500;
    }

    .oil-spec-value {
        font-weight: 600;
        color: #151C25;
        background-color: #f8fafc;
        padding: 4px 8px;
        border-radius: 4px;
    }

    /* Tabela de especificações */
    .spec-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        margin-bottom: 24px;
    }

    .spec-table th {
        background: linear-gradient(135deg, #0E368A, #1a4da8);
        color: white;
        text-align: left;
        padding: 12px;
        font-weight: 600;
    }

    .spec-table td {
        padding: 12px;
        border-bottom: 1px solid #e2e8f0;
        background-color: #fff;
    }

    .spec-table tr:nth-child(even) td {
        background-color: #f8fafc;
    }

    .spec-table tr:last-child td {
        border-bottom: none;
    }

    /* Benefícios */
    .benefit {
        margin-bottom: 20px;
        padding: 16px;
        background-color: #f8fafc;
        border-radius: 8px;
        border-left: 4px solid #0E368A;
    }

    .benefit-title {
        font-weight: 600;
        margin-bottom: 8px;
        color: #0E368A;
        font-size: 16px;
    }

    .benefit p {
        margin-bottom: 0;
        color: #4a5568;
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

    /* Caixas de uso condicional */
    .usage-box {
        background-color: #f8fafc;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid #e2e8f0;
        position: relative;
    }

    .usage-title {
        font-weight: 600;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        color: #151C25;
        font-size: 16px;
    }

    .usage-icon {
        width: 24px;
        height: 24px;
        margin-right: 10px;
        flex-shrink: 0;
    }

    .usage-list {
        margin: 16px 0;
        padding-left: 20px;
    }

    .usage-list li {
        margin-bottom: 8px;
        color: #4a5568;
    }

    .usage-tip {
        background: linear-gradient(135deg, #fff8e6, #fef3c7);
        border-left: 4px solid #f59e0b;
        padding: 16px;
        font-size: 14px;
        border-radius: 8px;
        margin: 20px 0;
        color: #92400e;
    }

    /* Procedimento de troca */
    .step {
        display: flex;
        margin-bottom: 24px;
        align-items: flex-start;
    }

    .step-number {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #0E368A, #1a4da8);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 16px;
        flex-shrink: 0;
        font-weight: 700;
        font-size: 16px;
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
        color: #4a5568;
        line-height: 1.6;
    }

    /* Nota informativa */
    .info-note {
        background: linear-gradient(135deg, rgba(14, 54, 138, 0.05), rgba(14, 54, 138, 0.1));
        padding: 16px;
        border-radius: 8px;
        font-size: 14px;
        margin: 24px 0;
        border-left: 4px solid #0E368A;
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

        .oil-card-body,
        .usage-box {
            padding: 16px;
        }

        .spec-table {
            font-size: 13px;
        }

        .spec-table th,
        .spec-table td {
            padding: 8px;
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
    }

    @media (max-width: 480px) {
        h1 {
            font-size: 22px;
        }

        h2 {
            font-size: 18px;
        }

        .oil-card-title {
            font-size: 16px;
        }

        .oil-spec {
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
        }

        .oil-spec-value {
            align-self: flex-end;
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
            <amp-ad width="100vw" height="320" type="adsense"
                data-ad-client="{{ Config::get('services.google_adsense.id') }}" data-ad-slot="7414648059"
                data-auto-format="rspv" data-full-width>
                <div overflow></div>
            </amp-ad>
        </div>

        <!-- Recomendações de Óleo -->
        <h2>Óleos Recomendados para {{ $article->vehicle_info['make'] ?? 'Veículo' }} {{ $article->vehicle_info['model']
            ?? '' }}</h2>

        <!-- Óleo Recomendado pela Fabricante -->
        @if(!empty($article->manufacturer_recommendation) && is_array($article->manufacturer_recommendation))
        <div class="oil-card">
            <div class="oil-card-header">✅ Recomendação Oficial</div>
            <div class="oil-card-body">
                <h3 class="oil-card-title">{{ $article->manufacturer_recommendation['nome_oleo'] ?? 'N/A' }}</h3>
                @if(!empty($article->manufacturer_recommendation['classificacao']))
                <div class="oil-spec">
                    <span class="oil-spec-label">Classificação:</span>
                    <span class="oil-spec-value">{{ $article->manufacturer_recommendation['classificacao'] }}</span>
                </div>
                @endif
                @if(!empty($article->manufacturer_recommendation['viscosidade']))
                <div class="oil-spec">
                    <span class="oil-spec-label">Viscosidade:</span>
                    <span class="oil-spec-value">{{ $article->manufacturer_recommendation['viscosidade'] }}</span>
                </div>
                @endif
                @if(!empty($article->manufacturer_recommendation['especificacao']))
                <div class="oil-spec">
                    <span class="oil-spec-label">Especificação:</span>
                    <span class="oil-spec-value">{{ $article->manufacturer_recommendation['especificacao'] }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Alternativa Premium -->
        @if(!empty($article->premium_alternative) && is_array($article->premium_alternative))
        <div class="oil-card">
            <div class="oil-card-header alt">⭐ Alternativa Premium</div>
            <div class="oil-card-body">
                <h3 class="oil-card-title">{{ $article->premium_alternative['nome_oleo'] ?? 'N/A' }}</h3>
                @if(!empty($article->premium_alternative['classificacao']))
                <div class="oil-spec">
                    <span class="oil-spec-label">Classificação:</span>
                    <span class="oil-spec-value">{{ $article->premium_alternative['classificacao'] }}</span>
                </div>
                @endif
                @if(!empty($article->premium_alternative['viscosidade']))
                <div class="oil-spec">
                    <span class="oil-spec-label">Viscosidade:</span>
                    <span class="oil-spec-value">{{ $article->premium_alternative['viscosidade'] }}</span>
                </div>
                @endif
                @if(!empty($article->premium_alternative['especificacao']))
                <div class="oil-spec">
                    <span class="oil-spec-label">Especificação:</span>
                    <span class="oil-spec-value">{{ $article->premium_alternative['especificacao'] }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Opção Econômica -->
        @if(!empty($article->economic_option) && is_array($article->economic_option))
        <div class="oil-card">
            <div class="oil-card-header alt">💰 Opção Econômica</div>
            <div class="oil-card-body">
                <h3 class="oil-card-title">{{ $article->economic_option['nome_oleo'] ?? 'N/A' }}</h3>
                @if(!empty($article->economic_option['classificacao']))
                <div class="oil-spec">
                    <span class="oil-spec-label">Classificação:</span>
                    <span class="oil-spec-value">{{ $article->economic_option['classificacao'] }}</span>
                </div>
                @endif
                @if(!empty($article->economic_option['viscosidade']))
                <div class="oil-spec">
                    <span class="oil-spec-label">Viscosidade:</span>
                    <span class="oil-spec-value">{{ $article->economic_option['viscosidade'] }}</span>
                </div>
                @endif
                @if(!empty($article->economic_option['especificacao']))
                <div class="oil-spec">
                    <span class="oil-spec-label">Especificação:</span>
                    <span class="oil-spec-value">{{ $article->economic_option['especificacao'] }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Especificações Técnicas -->
        @if(!empty($article->specifications) && is_array($article->specifications))
        <h2>📋 Especificações Técnicas</h2>
        <table class="spec-table">
            <tr>
                <th>Especificação</th>
                <th>Valor</th>
            </tr>
            @if(!empty($article->specifications['capacidade_oleo']))
            <tr>
                <td>Capacidade de Óleo</td>
                <td>{{ $article->specifications['capacidade_oleo'] }}</td>
            </tr>
            @endif
            @if(!empty($article->specifications['viscosidade']))
            <tr>
                <td>Viscosidade</td>
                <td>{{ $article->specifications['viscosidade'] }}</td>
            </tr>
            @endif
            @if(!empty($article->specifications['especificacao_minima']))
            <tr>
                <td>Especificação Mínima</td>
                <td>{{ $article->specifications['especificacao_minima'] }}</td>
            </tr>
            @endif
            @if(!empty($article->specifications['intervalo_troca']))
            <tr>
                <td>Intervalo de Troca</td>
                <td>{{ $article->specifications['intervalo_troca'] }}</td>
            </tr>
            @endif
            @if(!empty($article->specifications['filtro_oleo']))
            <tr>
                <td>Filtro de Óleo</td>
                <td>{{ $article->specifications['filtro_oleo'] }}</td>
            </tr>
            @endif
        </table>
        @endif

        <!-- Benefícios -->
        @if(!empty($article->benefits) && is_array($article->benefits) && count($article->benefits) > 0)
        <h2>✨ Benefícios do Óleo Correto</h2>
        @foreach($article->benefits as $benefit)
        @if(!empty($benefit['titulo']))
        <div class="benefit">
            <h3 class="benefit-title">{{ $benefit['titulo'] }}</h3>
            @if(!empty($benefit['descricao']))
            <p>{{ $benefit['descricao'] }}</p>
            @endif
        </div>
        @endif
        @endforeach
        @endif

        <!-- 🥇 ANÚNCIO 2: Após "Benefícios" -->
        <div class="ad-container">
            <div class="ad-label">Publicidade</div>
            <amp-ad width="100vw" height="320" type="adsense"
                data-ad-client="{{ Config::get('services.google_adsense.id') }}" data-ad-slot="8344586349"
                data-auto-format="rspv" data-full-width>
                <div overflow></div>
            </amp-ad>
        </div>

        <!-- Condições de Uso -->
        @if(!empty($article->usage_conditions) && is_array($article->usage_conditions))
        <h2>⚙️ Condições Especiais de Uso</h2>

        @if(!empty($article->usage_conditions['severo']) && is_array($article->usage_conditions['severo']))
        <div class="usage-box">
            <div class="usage-title">
                <amp-img class="usage-icon"
                    src="https://mercadoveiculos.s3.us-east-1.amazonaws.com/statics/icons/icon-warning.svg" width="24"
                    height="24" alt="Uso Severo"></amp-img>
                Uso Severo
            </div>
            @if(!empty($article->usage_conditions['severo']['condicoes']) &&
            is_array($article->usage_conditions['severo']['condicoes']))
            <ul class="usage-list">
                @foreach($article->usage_conditions['severo']['condicoes'] as $condition)
                @if(!empty($condition))
                <li>{{ $condition }}</li>
                @endif
                @endforeach
            </ul>
            @endif
            @if(!empty($article->usage_conditions['severo']['recomendacao']))
            <p><strong>Recomendação:</strong> {{ $article->usage_conditions['severo']['recomendacao'] }}</p>
            @endif
        </div>
        @endif

        @if(!empty($article->usage_conditions['normal']) && is_array($article->usage_conditions['normal']))
        <div class="usage-box">
            <div class="usage-title">
                <amp-img class="usage-icon"
                    src="https://mercadoveiculos.s3.us-east-1.amazonaws.com/statics/icons/icon-check.svg" width="24"
                    height="24" alt="Uso Normal"></amp-img>
                Uso Normal
            </div>
            @if(!empty($article->usage_conditions['normal']['condicoes']) &&
            is_array($article->usage_conditions['normal']['condicoes']))
            <ul class="usage-list">
                @foreach($article->usage_conditions['normal']['condicoes'] as $condition)
                @if(!empty($condition))
                <li>{{ $condition }}</li>
                @endif
                @endforeach
            </ul>
            @endif
            @if(!empty($article->usage_conditions['normal']['recomendacao']))
            <p><strong>Recomendação:</strong> {{ $article->usage_conditions['normal']['recomendacao'] }}</p>
            @endif
        </div>
        @endif

        @if(!empty($article->usage_conditions['dica_adicional']))
        <div class="usage-tip">
            <strong>💡 Dica importante:</strong> {{ $article->usage_conditions['dica_adicional'] }}
        </div>
        @endif
        @endif

        <!-- 🥈 ANÚNCIO 3: Após "Condições de Uso" -->
        <div class="ad-container">
            <div class="ad-label">Publicidade</div>
            <amp-ad width="100vw" height="320" type="adsense"
                data-ad-client="{{ Config::get('services.google_adsense.id') }}" data-ad-slot="1402260703"
                data-auto-format="rspv" data-full-width>
                <div overflow></div>
            </amp-ad>
        </div>

        <!-- Procedimento de Troca -->
        @if(!empty($article->change_procedure) && is_array($article->change_procedure) &&
        count($article->change_procedure) > 0)
        <h2>🔧 Procedimento de Troca</h2>

        @foreach($article->change_procedure as $index => $step)
        @if(!empty($step['passo']))
        <div class="step">
            <div class="step-number">{{ $index + 1 }}</div>
            <div class="step-content">
                <h3>{{ $step['passo'] }}</h3>
                @if(!empty($step['descricao']))
                <p>{{ $step['descricao'] }}</p>
                @endif
            </div>
        </div>
        @endif
        @endforeach

        @if(!empty($article->environmental_note))
        <div class="info-note">
            🌱 {{ $article->environmental_note }}
        </div>
        @endif
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