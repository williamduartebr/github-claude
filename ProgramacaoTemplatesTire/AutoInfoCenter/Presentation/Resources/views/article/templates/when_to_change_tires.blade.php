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
            content="{{ $article->vehicle_info['make'] ?? '' }} {{ $article->vehicle_info['model'] ?? '' }} {{ $article->vehicle_info['year'] ?? '' }}" />
        @endif
        <meta itemprop="category" content="{{ $article->category['name'] ?? 'Pneus e Rodas' }}" />

        <article class="max-w-4xl mx-auto pt-6 pb-12">

            <!-- Hero Image - Desktop -->
            @include('auto-info-center::article.partials.when-to-change-tires.hero')

            <!-- Title - Mobile -->
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

            <!-- Sintomas de Desgaste dos Pneus -->
            @include('auto-info-center::article.partials.when-to-change-tires.wear_symptoms')

            <!-- Fatores que Afetam a Durabilidade -->
            @include('auto-info-center::article.partials.when-to-change-tires.durability_factors')

            <!-- Banner de Anúncio 2 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-2]
            </div>

            <!-- Cronograma de Verificação -->
            @include('auto-info-center::article.partials.when-to-change-tires.verification_schedule')

            <!-- Tipos de Pneus e Quilometragem -->
            @include('auto-info-center::article.partials.when-to-change-tires.tire_types')

            <!-- Sinais Críticos para Substituição -->
            @include('auto-info-center::article.partials.when-to-change-tires.critical_signs')      

            <!-- Banner de Anúncio 3 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-3]
            </div>

            <!-- Manutenção Preventiva -->
            @include('auto-info-center::article.partials.when-to-change-tires.preventive_maintenance')      
           

            <!-- Procedimento de Verificação -->
            @include('auto-info-center::article.partials.when-to-change-tires.verification_procedure')
  
            <!-- Dados do Veículo -->
            @include('auto-info-center::article.partials.when-to-change-tires.vehicle_data')
    
            <!-- Perguntas Frequentes -->
            @include('auto-info-center::article.partials.when-to-change-tires.faq')
          

            <!-- Considerações Finais -->
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
  

    <!-- Newsletter Simplificada -->
    @include('auto-info-center::article.partials.newsletter')
</main>
@endsection

@push('styles')
@include('auto-info-center::article.partials.when-to-change-tires.styles')
@endpush

@push('scripts')
@include('auto-info-center::article.partials.when-to-change-tires.scripts')
@endpush