@extends('auto-info-center::layouts.app')

@push('head')
<link rel="canonical" href="{{ route('info.article.show', $article->slug) }}">

@if(!empty($article->structured_data))
<script type="application/ld+json">
    {!! json_encode($article->structured_data) !!}
</script>
@endif
@endpush

@section('content')

@include('auto-info-center::article.partials.breadcrumb')

<main id="main-content" class="container mx-auto px-4">
    <div itemscope itemtype="https://schema.org/TechArticle">
        @if(!empty($article->vehicle_info))
        <meta itemprop="vehicleEngine"
            content="{{ $article->vehicle_info['make'] ?? '' }} {{ $article->vehicle_info['model'] ?? '' }} {{ $article->vehicle_info['engine'] ?? '' }}" />
        @endif
        <meta itemprop="category" content="{{ $article->category['name'] ?? 'Pneus Recomendados' }}" />

        <article class="max-w-4xl mx-auto pt-6 pb-12">
            
            @include('auto-info-center::article.partials.tire-recommendation.header')  

            <div class="mb-8 mt-2 block md:hidden">
                <h1 class="text-3xl font-semibold leading-tight text-gray-900">{{ $article->title }}</h1>
                @if(!empty($article->formated_updated_at))
                <p class="text-sm mt-2 text-gray-600">Atualizado em: {{ $article->formated_updated_at }}</p>
                @endif
            </div>

            <!-- Introdução -->
            @if(!empty($article->introduction))
            <div class="mb-10">
                <p class="text-lg text-gray-800 leading-relaxed">
                    {{ $article->introduction }}
                </p>
            </div>
            @endif

            <!-- Banner de Anúncio 1 -->
            <div class="my-8">
                [ADSENSE-ARTICLE-1]
            </div>

            <!-- Especificações Oficiais -->
            @include('auto-info-center::article.partials.tire-recommendation.official_specs')  

            <!-- Melhores Pneus Dianteiros -->
            @include('auto-info-center::article.partials.tire-recommendation.front_tires')


            <!-- Banner de Anúncio 2 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-2]
            </div>

            <!-- Melhores Pneus Traseiros -->
            @include('auto-info-center::article.partials.tire-recommendation.rear_tires')

            <!-- Comparativo por Tipo de Uso -->

            @include('auto-info-center::article.partials.tire-recommendation.usage_comparison')

            <!-- Banner de Anúncio 3 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-3]
            </div>

            <!-- Guia de Desgaste e Substituição -->
            @include('auto-info-center::article.partials.tire-recommendation.wear_guide')

            <!-- Dicas de Manutenção -->
            @include('auto-info-center::article.partials.tire-recommendation.maintenance_tips')

            <!-- Perguntas Frequentes -->
            @include('auto-info-center::article.partials.tire-recommendation.faq')

            <!-- Conclusão -->
            @if(!empty($article->final_considerations))
            <section class="mb-12 bg-gray-50 rounded-lg p-6">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-4">Considerações Finais</h2>
                <p class="text-gray-800 mb-4">{{ $article->final_considerations }}</p>
            </section>
            @endif

            <!-- Artigos Relacionados -->
            {{-- @include('auto-info-center::article.partials.related_content') --}}

            <!-- Nota informativa -->
            @include('auto-info-center::article.partials.info_note_manual')

            <!-- Créditos e Atualização -->
            @include('auto-info-center::article.partials.credits-and-correction')
        </article>
    </div>

    <!-- Créditos Equipe Editorial -->
    @include('auto-info-center::article.partials.editorial_team')

    <!-- Newsletter Simplificada -->
    @include('auto-info-center::article.partials.newsletter')
</main>
@endsection

@push('styles')
@include('auto-info-center::article.partials.tire-recommendation.styles')
@endpush

@push('scripts')
@include('auto-info-center::article.partials.tire-recommendation.scripts')
@endpush