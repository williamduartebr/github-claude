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
        @if(!empty($article->vehicle_full_name))
        <meta itemprop="vehicleEngine" content="{{ $article->vehicle_full_name }}" />
        @endif
        <meta itemprop="category" content="Manutenção de Veículos Híbridos" />

        <article class="max-w-4xl mx-auto pt-6 pb-12">

            @include('auto-info-center::article.partials.review-schedule.hybrid.header')        

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

            <!-- Resumo das Revisões -->
            @include('auto-info-center::article.partials.review-schedule.hybrid.overview_schedule')
       
            <!-- Timeline de Revisões Detalhadas -->
            @include('auto-info-center::article.partials.review-schedule.hybrid.detailed_schedule')

            <!-- Banner de Anúncio 2 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-2]
            </div>

            <!-- Manutenção Preventiva -->
            @include('auto-info-center::article.partials.review-schedule.hybrid.preventive_maintenance')

            <!-- Peças que Exigem Atenção -->
            @include('auto-info-center::article.partials.review-schedule.hybrid.critical_parts')
  
            <!-- Banner de Anúncio 3 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-3]
            </div>

            <!-- Garantia e Recomendações -->
            @include('auto-info-center::article.partials.review-schedule.hybrid.warranty_info')

            <!-- Perguntas Frequentes -->
            @include('auto-info-center::article.partials.review-schedule.hybrid.faq')

            <!-- Conclusão -->
            @if(!empty($article->final_considerations))
            <section class="mb-12 bg-gradient-to-r from-green-50 to-blue-50 rounded-lg p-6 border-l-4 border-green-500">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-4">🌱 Considerações Finais</h2>
                <p class="text-gray-800 mb-4">{{ $article->final_considerations }}</p>
            </section>
            @endif

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
@include('auto-info-center::article.partials.review-schedule.hybrid.styles')
@endpush

@push('scripts')
@include('auto-info-center::article.partials.review-schedule.hybrid.scripts')
@endpush