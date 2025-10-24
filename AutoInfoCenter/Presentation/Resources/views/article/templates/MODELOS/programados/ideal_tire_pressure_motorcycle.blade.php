{{--
Template: ideal_tire_pressure_motorcycle.blade.php
Template principal otimizado para pressão ideal de pneus de MOTOCICLETAS
Estrutura modular com includes específicos para características de motos
--}}

@extends('auto-info-center::layouts.app')

@push('head')
<link rel="amphtml" href="{{ route('info.article.show.amp', $article->slug) }}">
<link rel="canonical" href="{{ route('info.article.show', $article->slug) }}">

<script type="application/ld+json">
    {!! json_encode($article->structured_data) !!}
</script>
@endpush

@section('content')

@include('auto-info-center::article.partials.breadcrumb')

<!-- Conteúdo Principal -->
<main id="main-content" class="container mx-auto px-4">
    <div itemscope itemtype="https://schema.org/TechArticle">
        <meta itemprop="vehicleEngine" content="{{ $article->getData()['vehicle_info']['full_name'] ?? '' }}" />
        <meta itemprop="category" content="Manutenção Motociclística" />

        <!-- Tag Article -->
        <article class="max-w-4xl mx-auto pt-6 pb-12">
            <!-- Cabeçalho Minimalista -->
            <div class="mb-8">
                <div class="border-b-2 border-[#DC2626] pb-4">
                    <h1 class="text-3xl md:text-4xl font-semibold leading-tight text-[#151C25]">
                        {{ $article->getData()['seo_data']['h1'] ?? $article->getData()['title'] }}
                    </h1>
                    <p class="text-sm mt-2 text-gray-500">
                        Atualizado em: {{ $article->getData()['formated_updated_at'] ?? '17 de julho de 2025' }}
                    </p>
                </div>
            </div>

            <!-- Introdução -->
            @if(!empty($article->getData()['introduction']))
            <div class="mb-10">
                <p class="text-lg text-gray-800 leading-relaxed">
                    {!! nl2br(e($article->getData()['introduction'])) !!}
                </p>
            </div>
            @endif

            <!-- Banner de Anúncio 1 -->
            <div class="my-8">
                [ADSENSE-ARTICLE-1]
            </div>

            <!-- Dados Principais do Veículo (Motorcycle Specific) -->
            @include('auto-info-center::article.partials.ideal-tire-pressure.motorcycle.vehicle-data')

            <!-- Especificações de Pneus por Versão (Motorcycle Specific) -->
            @include('auto-info-center::article.partials.ideal-tire-pressure.motorcycle.tire-specifications')

            <!-- Tabela Principal de Pressões (Apenas o Piloto/Garupa) -->
            @include('auto-info-center::article.partials.ideal-tire-pressure.motorcycle.pressure-table')

            <!-- Alertas Críticos de Segurança (Motorcycle Specific) -->
            @include('auto-info-center::article.partials.ideal-tire-pressure.motorcycle.critical-safety-alerts')

            <!-- Avisos para Motos Esportivas (Conditional) -->
            @if(method_exists($article, 'isSportMotorcycle') && $article->isSportMotorcycle())
            @include('auto-info-center::article.partials.ideal-tire-pressure.motorcycle.sport-motorcycle-warning')
            @elseif(($article->getData()['vehicle_info']['category'] ?? '') === 'sport')
            @include('auto-info-center::article.partials.ideal-tire-pressure.motorcycle.sport-motorcycle-warning')
            @endif

            <!-- Procedimento de Calibragem (Motorcycle Specific) -->
            @include('auto-info-center::article.partials.ideal-tire-pressure.motorcycle.calibration-procedure')

            <!-- Banner de Anúncio 2 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-2]
            </div>

            <!-- Conversão de Unidades (Motorcycle Specific) -->
            @include('auto-info-center::article.partials.ideal-tire-pressure.motorcycle.pressure-conversion')

            <!-- Ajustes Climáticos (Shared) -->
            @include('auto-info-center::article.partials.ideal-tire-pressure.shared.climate-adjustments')

            <!-- Dicas de Manutenção (Motorcycle Specific) -->
            @include('auto-info-center::article.partials.ideal-tire-pressure.motorcycle.maintenance-tips')

            <!-- Banner de Anúncio 3 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-3]
            </div>

            <!-- Perguntas Frequentes -->
            @if(!empty($article->getData()['faq']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Perguntas Frequentes
                </h2>

                <div class="space-y-4">
                    @foreach($article->getData()['faq'] as $item)
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">
                            {{ $item['question'] ?? $item['pergunta'] ?? 'Pergunta não disponível' }}
                        </h3>
                        <div class="text-sm text-gray-700 prose prose-sm max-w-none">
                            {!! nl2br(e($item['answer'] ?? $item['resposta'] ?? 'Resposta não disponível')) !!}
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Considerações Finais -->
            @if(!empty($article->getData()['final_considerations']))
            <section class="mb-12">
                <div class="bg-gradient-to-r from-[#DC2626] to-red-700 text-white rounded-lg p-8">
                    <div class="flex items-center mb-4">
                        <span class="text-3xl mr-4">🏁</span>
                        <h2 class="text-2xl font-bold">Considerações Finais</h2>
                    </div>
                    <div class="text-red-100 leading-relaxed">
                        {!! nl2br(e($article->getData()['final_considerations'])) !!}
                    </div>
                </div>
            </section>
            @endif

            <!-- Banner de Anúncio 4 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-4]
            </div>

            <!-- Nota informativa -->
            @include('auto-info-center::article.partials.info_note_manual')

            <!-- Créditos e Atualização -->
            @include('auto-info-center::article.partials.update_content')

        </article>
    </div>

    <!-- Créditos Equipe Editorial -->
    @include('auto-info-center::article.partials.editorial_team')

    <!-- Newsletter Simplificada -->
    @include('auto-info-center::article.partials.newsletter')
</main>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Conversão de pressão automática para motos
    const conversionInputs = document.querySelectorAll('.pressure-conversion-input');
    conversionInputs.forEach(input => {
        input.addEventListener('input', function() {
            const value = parseFloat(this.value);
            if (!isNaN(value)) {
                if (this.dataset.from === 'psi') {
                    const kgfResult = document.querySelector('#kgf-result');
                    const barResult = document.querySelector('#bar-result');
                    if (kgfResult) kgfResult.textContent = (value * 0.070307).toFixed(2);
                    if (barResult) barResult.textContent = (value * 0.068948).toFixed(2);
                }
            }
        });
    });

    // Destaque para alertas críticos
    const criticalAlerts = document.querySelectorAll('.critical-alert');
    criticalAlerts.forEach(alert => {
        alert.addEventListener('mouseenter', function() {
            this.classList.add('scale-105');
            this.style.transition = 'transform 0.2s ease';
        });
        alert.addEventListener('mouseleave', function() {
            this.classList.remove('scale-105');
        });
    });

    // Auto-scroll para seção de calibragem em motos esportivas
    const sportWarningButton = document.querySelector('#sport-calibration-guide');
    if (sportWarningButton) {
        sportWarningButton.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector('#calibration-procedure').scrollIntoView({
                behavior: 'smooth'
            });
        });
    }

    // FAQ Toggle (se houver FAQ)
    const faqToggles = document.querySelectorAll('.faq-toggle');
    faqToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function() {
            const content = this.nextElementSibling;
            const icon = this.querySelector('.faq-icon');
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.classList.add('rotate-180');
            } else {
                content.classList.add('hidden');
                icon.classList.remove('rotate-180');
            }
        });
    });
});
</script>
@endpush