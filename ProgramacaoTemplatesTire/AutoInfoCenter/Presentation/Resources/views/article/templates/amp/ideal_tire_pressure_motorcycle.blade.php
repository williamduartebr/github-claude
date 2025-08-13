{{--
Template AMP: ideal_tire_pressure_motorcycle.blade.php
Template principal AMP otimizado para pressão ideal de pneus de MOTOCICLETAS
ATUALIZADO com includes dos partials AMP criados
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
    }
    
    .critical-banner-title {
        font-size: 18px;
        font-weight: 700;
        color: #991b1b;
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
        background-color: #fef2f2;
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
        
        <!-- 🆕 INCLUINDO OS PARTIALS AMP CRIADOS -->
        
        <!-- Dados do Veículo (quando criarmos) -->
        @includeWhen(true, 'auto-info-center::article.partials.tire-pressure.amp.motorcycle.vehicle-data')
        
        <!-- Especificações dos Pneus (quando criarmos) -->
        @includeWhen(true, 'auto-info-center::article.partials.tire-pressure.amp.motorcycle.tire-specifications')
        
        <!-- Conversão de Pressão ✅ CRIADO -->
        @include('auto-info-center::article.partials.tire-pressure.amp.motorcycle.pressure-conversion')
        
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
        
        <!-- Tabela de Pressão (quando criarmos) -->
        @includeWhen(true, 'auto-info-center::article.partials.tire-pressure.amp.motorcycle.pressure-table')
        
        <!-- Alertas de Segurança ✅ CRIADO -->
        @include('auto-info-center::article.partials.tire-pressure.amp.motorcycle.safety-alerts')
        
        <!-- 🥉 ANÚNCIO 3: Após alertas -->
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
        
        <!-- Procedimento de Calibragem ✅ CRIADO -->
        @include('auto-info-center::article.partials.tire-pressure.amp.motorcycle.calibration-procedure')
        
        <!-- Dicas de Manutenção (quando criarmos) -->
        @includeWhen(true, 'auto-info-center::article.partials.tire-pressure.amp.motorcycle.maintenance-tips')
        
        <!-- Avisos para Motos Esportivas (quando criarmos) -->
        @if(method_exists($article, 'isSportMotorcycle') && $article->isSportMotorcycle())
            @includeWhen(true, 'auto-info-center::article.partials.tire-pressure.amp.motorcycle.sport-motorcycle-warning')
        @endif
        
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