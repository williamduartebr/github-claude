{{--
Template: tire_calibration_motorcycle.blade.php
Template principal otimizado para pressão ideal de pneus de MOTOCICLETAS
Estrutura modular com includes específicos para características de motos
--}}

@extends('auto-info-center::layouts.app')

@push('head')
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
            @include('auto-info-center::article.partials.tire-calibration.motorcycle.vehicle-data')

            <!-- Especificações de Pneus por Versão (Motorcycle Specific) -->
            @include('auto-info-center::article.partials.tire-calibration.motorcycle.tire-specifications')

            <!-- Tabela Principal de Pressões (Apenas o Piloto/Garupa) -->
            @include('auto-info-center::article.partials.tire-calibration.motorcycle.pressure-table')


            <!-- Alertas Críticos de Segurança (Motorcycle Specific) -->
            @include('auto-info-center::article.partials.tire-calibration.motorcycle.critical-safety-alerts')


            <!-- Avisos para Motos Esportivas (Conditional) -->
            @if(method_exists($article, 'isSportMotorcycle') && $article->isSportMotorcycle())
            @include('auto-info-center::article.partials.tire-calibration.motorcycle.sport-motorcycle-warning')
            @elseif(($article->getData()['vehicle_info']['category'] ?? '') === 'sport')
            @include('auto-info-center::article.partials.tire-calibration.motorcycle.sport-motorcycle-warning')
            @endif

            <!-- Procedimento de Calibragem (Motorcycle Specific) -->
            @include('auto-info-center::article.partials.tire-calibration.motorcycle.calibration-procedure')

            <!-- Banner de Anúncio 2 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-2]
            </div>

            @include('auto-info-center::article.partials.tire-calibration.motorcycle.label-location')

            <!-- Conversão de Unidades (Motorcycle Specific) -->
            @include('auto-info-center::article.partials.tire-calibration.motorcycle.pressure-conversion')

            <!-- Ajustes Climáticos (Shared) -->
            @include('auto-info-center::article.partials.tire-calibration.motorcycle.climate-adjustments')

            <!-- Dicas de Manutenção (Motorcycle Specific) -->
            @include('auto-info-center::article.partials.tire-calibration.motorcycle.maintenance-tips')

            <!-- Banner de Anúncio 3 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-3]
            </div>

            <!-- Perguntas Frequentes -->
            @include('auto-info-center::article.partials.tire-calibration.motorcycle.faq')

            <!-- Considerações Finais -->
            @include('auto-info-center::article.partials.tire-calibration.motorcycle.final-considerations')

            <!-- Banner de Anúncio 4 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-4]
            </div>

            <!-- Nota informativa -->
            @include('auto-info-center::article.partials.info_note_manual')

            <!-- Créditos e Link para Correção -->
            @include('auto-info-center::article.partials.credits-and-correction')

        </article>
    </div>

    <!-- Créditos Equipe Editorial -->
    @include('auto-info-center::article.partials.editorial_team')

    <!-- Newsletter Simplificada -->
    @include('auto-info-center::article.partials.newsletter')
</main>

@endsection

@push('scripts')
@include('auto-info-center::article.partials.tire-calibration.motorcycle.scripts')
@endpush