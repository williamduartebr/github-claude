{{--
Template Desktop Otimizado: tire_calibration_car.blade.php
Usando dados embarcados das ViewModels e includes modulares DENTRO da estrutura existente
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
    <div itemscope itemtype="https://schema.org/Article">
        <meta itemprop="vehicleEngine" content="{{ $article->getData()['vehicle_info']['full_name'] ?? '' }}" />
        <meta itemprop="category" content="Manutenção Automotiva" />

        <!-- Tag Article -->
        <article class="max-w-4xl mx-auto pt-6 pb-12">
            <!-- Cabeçalho Minimalista -->
            <div class="mb-8">
                <div class="border-b-2 border-[#0E368A] pb-4">
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

            <!-- Destaque da Pressão Ideal - USANDO PARTIAL MODULAR -->
            @include('auto-info-center::article.partials.tire-calibration.car.car-vehicle-data')

            <!-- Especificações dos Pneus Originais e Localização da Etiqueta -->
            @include('auto-info-center::article.partials.tire-calibration.car.tire-specifications')

            <!-- Tabela de Pressão por Versão - USANDO PARTIAL MODULAR -->
            @if(!empty($article->getData()['tire_specifications_by_version']))
            @include('auto-info-center::article.partials.tire-calibration.car.specifications-by-version')
            @endif

            <!-- Tabela de Carga Completa - USANDO PARTIAL MODULAR -->
            @if(!empty($article->getData()['full_load_table']['conditions']))
            @include('auto-info-center::article.partials.tire-calibration.car.full-load-table')
            @endif

            <!-- Banner de Anúncio 2 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-2]
            </div>

            {{-- @include('auto-info-center::article.partials.tire-calibration.car.label-location') --}}

            <!-- Condições Especiais - USANDO PARTIAL MODULAR -->
            @if(!empty($article->getData()['special_conditions']))
            @include('auto-info-center::article.partials.tire-calibration.car.special-conditions')
            @endif

            <!-- Sistema TPMS (condicional) - USANDO PARTIAL MODULAR -->
            @if($article->getData()['vehicle_info']['has_tpms'] ?? false)
            @include('auto-info-center::article.partials.tire-calibration.car.tpms-section')
            @endif

            <!-- Características Elétricas (condicional) - USANDO PARTIAL MODULAR -->
            @if($article->getData()['vehicle_info']['is_electric'] ?? false)
            @include('auto-info-center::article.partials.tire-calibration.car.electric-features')
            @endif

            <!-- Conversão de Unidades - USANDO PARTIAL MODULAR -->
            @if(!empty($article->getData()['unit_conversion']))
            @include('auto-info-center::article.partials.tire-calibration.car.pressure-conversion')
            @endif

            <!-- 🆕 EQUIPAMENTO DE EMERGÊNCIA - NOVA SEÇÃO CONDICIONAL -->
            @include('auto-info-center::article.partials.tire-calibration.car.emergency-equipment')

            <!-- Cuidados e Recomendações -->
            @include('auto-info-center::article.partials.tire-calibration.car.care-recommendations')

            <!-- Banner de Anúncio 3 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-3]
            </div>

            <!-- Impacto no Desempenho - USANDO PARTIAL MODULAR -->
            @if(!empty($article->getData()['pressure_impact']))
            @include('auto-info-center::article.partials.tire-calibration.car.pressure-impact')
            @endif

            <!-- Ajustes Climáticos - USANDO PARTIAL MODULAR -->
            @include('auto-info-center::article.partials.tire-calibration.car.climate-adjustments')

            <!-- Conclusão -->
            @include('auto-info-center::article.partials.tire-calibration.car.final-considerations')
    

            {{-- @include('auto-info-center::article.partials.tire-calibration.related_topics_tire_calibration_car')
            --}}

            <!-- Perguntas Frequentes -->
            @include('auto-info-center::article.partials.tire-calibration.car.faq')

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
@include('auto-info-center::article.partials.tire-calibration.car.scripts')
@endpush
