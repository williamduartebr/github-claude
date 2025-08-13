{{--
Template AMP: ideal_tire_pressure_car.blade.php
Template principal AMP otimizado para pressão ideal de pneus de CARROS
ATUALIZADO com includes dos partials AMP (quando criados)
--}}

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
    
    /* Destaque da pressão ideal para carros */
    .ideal-pressure-highlight {
        background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        border: 2px solid #2563eb;
        border-radius: 16px;
        padding: 24px;
        margin: 24px 0;
        position: relative;
    }
    
    .ideal-pressure-title {
        font-size: 18px;
        font-weight: 700;
        color: #1e40af;
        text-align: center;
        margin-bottom: 16px;
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
        background-color: #eff6ff;
        font-weight: 600;
        color: #151C25;
        cursor: pointer;
    }
    
    amp-accordion .faq-content {
        padding: 16px;
        background-color: #fff;
        color: #4a5568;
        line-height: 1.6;
    }
    
    /* Alertas importantes */
    .info-note {
        background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        border: 1px solid #3b82f6;
        border-radius: 8px;
        padding: 16px;
        margin: 16px 0;
        color: #1e40af;
    }
    
    /* Tabelas */
    .pressure-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin: 16px 0;
    }
    
    .pressure-table th {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: white;
        padding: 12px 8px;
        text-align: center;
        font-weight: 600;
        font-size: 12px;
    }
    
    .pressure-table td {
        padding: 10px 8px;
        text-align: center;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13px;
    }
    
    .pressure-table tr:nth-child(even) {
        background-color: #f8fafc;
    }
    
    .pressure-cell {
        font-weight: 600;
        color: #1f2937;
    }
    
    .pressure-highlight {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: white;
        border-radius: 4px;
        padding: 4px 8px;
        font-weight: 700;
    }
    
    .observation-cell {
        font-size: 11px;
        color: #6b7280;
    }
    
    /* Responsividade */
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
        
        h3 {
            font-size: 16px;
        }
        
        .pressure-table th,
        .pressure-table td {
            padding: 8px 4px;
            font-size: 11px;
        }
    }
</style>
@endsection

@section('content')
<div class="container">
    <article>
        <!-- Cabeçalho -->
        <header>
            <h1>{{ $article->getData()['seo_data']['h1'] ?? $article->getData()['title'] }}</h1>
            <div class="article-meta">
                Atualizado em: {{ $article->getData()['formated_updated_at'] ?? '12 de agosto de 2025' }}
            </div>
        </header>
        
        <!-- Introdução -->
        @if(!empty($article->getData()['introduction']))
        <div style="margin-bottom: 24px;">
            <p style="font-size: 16px; line-height: 1.6;">
                {!! nl2br(e($article->getData()['introduction'])) !!}
            </p>
        </div>
        @endif
        
        <!-- 🥇 ANÚNCIO 1: Após introdução -->
        <div class="ad-container">
            <div class="ad-label">Publicidade</div>
            <amp-ad width="100vw" height="320"
                type="adsense"
                data-ad-client="{{ config('services.google_adsense.id') }}"
                data-ad-slot="1234567890"
                data-auto-format="rspv"
                data-full-width>
                <div overflow></div>
            </amp-ad>
        </div>
        
        <!-- 🆕 INCLUINDO OS PARTIALS AMP PARA CARROS (quando criados) -->
        
        <!-- Dados do Veículo -->
        @includeWhen(true, 'auto-info-center::article.partials.tire-pressure.amp.car.vehicle-data')
        
        <!-- Especificações por Versão -->
        @includeWhen(true, 'auto-info-center::article.partials.tire-pressure.amp.car.specifications-by-version')
        
        <!-- Conversão de Pressão -->
        @includeWhen(true, 'auto-info-center::article.partials.tire-pressure.amp.car.pressure-conversion')
        
        <!-- 🥈 ANÚNCIO 2: Após conversão -->
        <div class="ad-container">
            <div class="ad-label">Publicidade</div>
            <amp-ad width="100vw" height="320"
                type="adsense"
                data-ad-client="{{ config('services.google_adsense.id') }}"
                data-ad-slot="2345678901"
                data-auto-format="rspv"
                data-full-width>
                <div overflow></div>
            </amp-ad>
        </div>
        
        <!-- Tabela de Carga Completa -->
        @includeWhen(true, 'auto-info-center::article.partials.tire-pressure.amp.car.full-load-table')
        
        <!-- Informações do Estepe -->
        @includeWhen(true, 'auto-info-center::article.partials.tire-pressure.amp.car.spare-tire')
        
        <!-- 🥉 ANÚNCIO 3: Após tabelas -->
        <div class="ad-container">
            <div class="ad-label">Publicidade</div>
            <amp-ad width="100vw" height="320"
                type="adsense"
                data-ad-client="{{ config('services.google_adsense.id') }}"
                data-ad-slot="3456789012"
                data-auto-format="rspv"
                data-full-width>
                <div overflow></div>
            </amp-ad>
        </div>
        
        <!-- Procedimento de Calibragem -->
        @includeWhen(true, 'auto-info-center::article.partials.tire-pressure.amp.car.calibration-procedure')
        
        <!-- Alertas de Segurança -->
        @includeWhen(true, 'auto-info-center::article.partials.tire-pressure.amp.car.safety-alerts')
        
        <!-- Seções Compartilhadas -->
        @includeWhen(true, 'auto-info-center::article.partials.tire-pressure.amp.shared.climate-adjustments')
        
        @includeWhen(true, 'auto-info-center::article.partials.tire-pressure.amp.shared.tpms-info')
        
        <!-- FAQ -->
        @if(!empty($article->getData()['faq']))
        <h2>❓ Perguntas Frequentes sobre {{ $article->getData()['vehicle_info']['model_name'] ?? 'Pressão dos Pneus' }}</h2>
        
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
        
        <!-- FAQ Compartilhada -->
        @includeWhen(true, 'auto-info-center::article.partials.tire-pressure.amp.shared.faq-section')
        
        <!-- 🥇 ANÚNCIO 4: Antes das considerações finais -->
        <div class="ad-container">
            <div class="ad-label">Publicidade</div>
            <amp-ad width="100vw" height="320"
                type="adsense"
                data-ad-client="{{ config('services.google_adsense.id') }}"
                data-ad-slot="4567890123"
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
        
        <!-- Lembrete Final de Manutenção -->
        <div class="ideal-pressure-highlight">
            <div class="ideal-pressure-title">🚗 Lembre-se Sempre</div>
            <div style="text-align: left; padding-left: 40px;">
                <p style="color: #1e40af; margin-bottom: 8px; font-weight: 500;"><strong>✓</strong> Verifique mensalmente a pressão</p>
                <p style="color: #1e40af; margin-bottom: 8px; font-weight: 500;"><strong>✓</strong> Sempre com pneus frios</p>
                <p style="color: #1e40af; margin-bottom: 8px; font-weight: 500;"><strong>✓</strong> Inclua o estepe na verificação</p>
                <p style="color: #1e40af; margin-bottom: 8px; font-weight: 500;"><strong>✓</strong> Ajuste conforme a carga do veículo</p>
                <p style="color: #1e40af; margin-bottom: 0; font-weight: 500;"><strong>✓</strong> Consulte sempre o manual do proprietário</p>
            </div>
        </div>
        

        <!-- Nota informativa -->
        @include('auto-info-center::article.partials.info_note_manual')
        
        <!-- Footer do artigo -->
        @include('auto-info-center::article.partials.article_footer')
    </article>
</div>
@endsection