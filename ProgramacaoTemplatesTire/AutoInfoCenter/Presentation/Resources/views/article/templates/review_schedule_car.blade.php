@extends('auto-info-center::layouts.app')

@push('head')
<link rel="canonical" href="{{ route('info.article.show', $article->slug) }}">

<script type="application/ld+json">
    {!! json_encode($article->structured_data) !!}
</script>
@endpush

@section('content')

@include('auto-info-center::article.partials.breadcrumb')

<main id="main-content" class="container mx-auto px-4">
    <div itemscope itemtype="https://schema.org/TechArticle">
        <meta itemprop="vehicleEngine" content="{{ $article->vehicle_full_name }}" />
        <meta itemprop="category" content="Manutenção Automotiva" />

        <article class="max-w-4xl mx-auto pt-6 pb-12">

            @include('auto-info-center::article.partials.review-schedule.car.header')

            <!-- Introdução -->
            <div class="mb-10">
                <p class="text-lg text-gray-800 leading-relaxed">
                    {{ $article->introduction }}
                </p>
            </div>

            <!-- Banner de Anúncio 1 -->
            <div class="my-8">
                [ADSENSE-ARTICLE-1]
            </div>

            <!-- Resumo das Revisões -->
            @include('auto-info-center::article.partials.review-schedule.car.overview_schedule')

            <!-- Timeline de Revisões Detalhadas -->
            @include('auto-info-center::article.partials.review-schedule.car.detailed_schedule')

            <!-- Banner de Anúncio 2 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-2]
            </div>

            <!-- Manutenção Preventiva -->
            @include('auto-info-center::article.partials.review-schedule.car.preventive_maintenance')
            
            
            <!-- Peças que Exigem Atenção -->
            @include('auto-info-center::article.partials.review-schedule.car.critical_parts')

            <!-- Banner de Anúncio 3 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-3]
            </div>

            <!-- Garantia e Recomendações -->
            @include('auto-info-center::article.partials.review-schedule.car.warranty_info')

            <!-- Perguntas Frequentes -->
            @include('auto-info-center::article.partials.review-schedule.car.faq')

            <!-- Conclusão -->
            <section class="mb-12 bg-gray-50 rounded-lg p-6">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-4">Considerações Finais</h2>
                <p class="text-gray-800 mb-4">{{ $article->final_considerations }}</p>
            </section>

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
@include('auto-info-center::article.partials.review-schedule.car.styles')
@endpush

@push('scripts')
@include('auto-info-center::article.partials.review-schedule.car.scripts')
@endpush