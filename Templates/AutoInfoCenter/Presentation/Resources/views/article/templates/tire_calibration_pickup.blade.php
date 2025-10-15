{{--
Template Desktop: tire_calibration_pickup.blade.php
Especializado para pickups - Baseado na estrutura tire_calibration_car.blade.php
Otimizado para pickups com pressões diferenciadas e capacidade de carga
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

<main id="main-content" class="container mx-auto px-4">
    <div itemscope itemtype="https://schema.org/TechArticle">
        <meta itemprop="vehicleEngine" content="{{ $article->vehicle_full_name }}" />
        <meta itemprop="category" content="Calibragem de Pneus - Pickup" />

        <article class="max-w-4xl mx-auto pt-6 pb-12">
            @php
            // Processa dados específicos do template pickup
            $vehicleInfo = $article->getData()['vehicle_info'] ?? [];
            $pressureSpecs = $article->getData()['pressure_specifications'] ?? [];
            $contentData = $article->getData()['content'] ?? [];

            $emergencyEquipment = $article->getData()['emergency_equipment'] ?? [];

            // Variáveis específicas de pickup
            $vehicleName = $vehicleInfo['full_name'] ?? $article->getData()['title'] ?? 'pickup';
            $hasTpms = $vehicleInfo['has_tpms'] ?? false;
            $isElectric = $vehicleInfo['is_electric'] ?? false;
            $isPremium = $vehicleInfo['is_premium'] ?? false;
            $isPickup = true; // Template específico para pickups

            $imageDefault = \Str::slug(sprintf("%s-%s", $article->category['slug'] ?? 'calibragem',
            $article->vehicle_info['vehicle_type'] ?? 'pickup'));
            @endphp

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
            {{--
            <!-- Header com imagem específica para pickup -->
            <div class="relative rounded-lg overflow-hidden mb-8 mt-2 hidden md:block">
                <img src="https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/tire_calibration_pickup.png"
                    alt="{{ $article->title }}" class="w-full h-64 object-cover"
                    onerror="this.src='https:\/\/mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/tire_calibration_pickup.png'">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
                <div class="absolute bottom-0 left-0 p-6 text-white">
                    <div class="flex items-center mb-2">
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-orange-500 text-white mr-3">
                            🚛 PICKUP
                        </span>
                        @if($hasTpms)
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-500 text-white">
                            📡 TPMS
                        </span>
                        @endif
                    </div>
                    <h1 class="text-3xl md:text-4xl font-semibold leading-tight">{{ $article->title }}</h1>
                    @if(!empty($article->formated_updated_at))
                    <p class="text-sm mt-2 opacity-90">Atualizado em: {{ $article->formated_updated_at }}</p>
                    @endif
                </div>
            </div>

            <!-- Header mobile -->
            <div class="mb-8 mt-2 block md:hidden">
                <div class="flex items-center mb-3">
                    <span
                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-orange-500 text-white mr-2">
                        🚛 PICKUP
                    </span>
                    @if($hasTpms)
                    <span
                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-blue-500 text-white">
                        📡 TPMS
                    </span>
                    @endif
                </div>
                <h1 class="text-3xl font-semibold leading-tight text-gray-900">{{ $article->title }}</h1>
                @if(!empty($article->formated_updated_at))
                <p class="text-sm mt-2 text-gray-600">Atualizado em: {{ $article->formated_updated_at }}</p>
                @endif
            </div>

            <!-- Introdução -->
            <div class="mb-10">
                <p class="text-lg text-gray-800 leading-relaxed">
                    {{ $article->introduction }}
                </p>
            </div> --}}

            <!-- Banner de Anúncio 1 -->
            <div class="my-8">
                [ADSENSE-ARTICLE-1]
            </div>

            <!-- PRESSÕES PRINCIPAIS PARA PICKUP - Destaque especial -->
            @include('auto-info-center::article.partials.tire-calibration.pickup.main-pressures')

            <!-- Include do componente específico para pickup -->
            @include('auto-info-center::article.partials.tire-calibration.pickup.vehicle-data-pickup', [
            'contentData' => $contentData,
            'vehicleData' => $article->getData()
            ])

            <!-- Alerta específico para pickups -->
            @include('auto-info-center::article.partials.tire-calibration.pickup.alert-pickups')

            <!-- Especificações Técnicas - Pickup -->
            @include('auto-info-center::article.partials.tire-calibration.pickup.specifications-by-version')

            <!-- Info Section - Localização da Etiqueta -->
            @include('auto-info-center::article.partials.tire-calibration.pickup.label-location')

            <!-- Tabela de Carga Completa -->
            @include('auto-info-center::article.partials.tire-calibration.pickup.conditions')

            <!-- Banner de Anúncio 2 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-2]
            </div>

            @include('auto-info-center::article.partials.ideal-tire-pressure.shared.label-location')

            <!-- Condições Especiais para Pickups -->
            @include('auto-info-center::article.partials.tire-calibration.pickup.specials-conditions')

            <!-- Sistema TPMS (se aplicável) -->
            @include('auto-info-center::article.partials.tire-calibration.pickup.tpms')

            <!-- Conversão de Unidades -->
            @include('auto-info-center::article.partials.tire-calibration.pickup.unit-conversions')

            <!-- Procedimento de Calibragem Específico para Pickups -->
            @include('auto-info-center::article.partials.tire-calibration.pickup.calibration-procedure')

            <!-- Banner de Anúncio 3 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-3]
            </div>

            <!-- Cuidados Específicos para Pickups -->
            @include('auto-info-center::article.partials.tire-calibration.pickup.care-recommendations')

            <!-- Impacto da Pressão no Desempenho -->
            @include('auto-info-center::article.partials.tire-calibration.pickup.pressure-impact')

            <!-- Perguntas Frequentes -->
            @include('auto-info-center::article.partials.tire-calibration.pickup.faq')

            <!-- Considerações Finais -->
            @include('auto-info-center::article.partials.tire-calibration.pickup.final-considerations')

            <!-- Resumo Executivo Final para Pickup -->
            @include('auto-info-center::article.partials.tire-calibration.pickup.final-summary')

            <!-- Nota Técnica Final -->
            @include('auto-info-center::article.partials.tire-calibration.pickup.final-technical-note')

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

@push('styles')
@include('auto-info-center::article.partials.tire-calibration.pickup.styles')
@endpush

@push('scripts')
@include('auto-info-center::article.partials.tire-calibration.pickup.scripts')
@endpush