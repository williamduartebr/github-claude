@extends('auto-info-center::layouts.app')

@push('head')
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-5108844086542870"
    crossorigin="anonymous"></script>

<title>{{ $article->getData()['seo_data']['title'] ?? $article->getData()['title'] }}</title>
<meta name="description" content="{{ $article->getData()['seo_data']['meta_description'] ?? '' }}">

<!-- Canonical e Idiomas -->
<link rel="canonical" href="{{ $article->getData()['canonical_url'] ?? '' }}" />
<link rel="alternate" hreflang="pt-BR" href="{{ $article->getData()['canonical_url'] ?? '' }}" />

<!-- Open Graph / Facebook -->
<meta property="og:type" content="article" />
<meta property="og:title" content="{{ $article->getData()['seo_data']['og_title'] ?? $article->getData()['title'] }}" />
<meta property="og:description" content="{{ $article->getData()['seo_data']['og_description'] ?? '' }}" />
<meta property="og:image" content="{{ $article->getData()['seo_data']['og_image'] ?? '' }}" />
<meta property="og:url" content="{{ $article->getData()['canonical_url'] ?? '' }}" />
<meta property="og:site_name" content="Mercado Veículos" />

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $article->getData()['seo_data']['og_title'] ?? $article->getData()['title'] }}">
<meta name="twitter:description" content="{{ $article->getData()['seo_data']['og_description'] ?? '' }}">
<meta name="twitter:image" content="{{ $article->getData()['seo_data']['og_image'] ?? '' }}">

<!-- Preload de recursos críticos -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" href="https://mercadoveiculos.s3.amazonaws.com/statics/logos/logo-mercadoveiculos-write.svg" as="image">

@if(!empty($article->getData()['structured_data']))
<script type="application/ld+json">
{!! json_encode($article->getData()['structured_data'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endif
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

            <!-- Informações do Veículo -->
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Especificações dos Pneus Originais
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Especificações Técnicas -->
                    @if(!empty($article->getData()['tire_specifications_by_version']))
                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center mb-4">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/15 flex items-center justify-center mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0E368A]" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Medidas de Pneus por Versão</h3>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full border-collapse">
                                <thead>
                                    <tr class="bg-[#0E368A] text-white">
                                        <th class="py-2 px-3 text-left font-medium text-xs">Versão</th>
                                        <th class="py-2 px-3 text-left font-medium text-xs">Medidas de Pneus</th>
                                        <th class="py-2 px-3 text-left font-medium text-xs">Índice de Carga/Vel.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($article->getData()['tire_specifications_by_version'] as $spec)
                                    <tr class="border-b border-gray-200 {{ $spec['css_class'] }}">
                                        <td class="py-2 px-3 text-xs">{{ $spec['version'] }}</td>
                                        <td class="py-2 px-3 text-xs">{{ $spec['tire_size'] }}</td>
                                        <td class="py-2 px-3 text-xs">{{ $spec['load_speed_index'] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <p class="text-xs text-gray-700">
                                <span class="font-medium">Observação:</span> Para veículos equipados com rodas diferentes das originais, consulte o manual do proprietário ou a etiqueta de calibragem na coluna da porta do motorista.
                            </p>
                        </div>
                    </div>
                    @endif

                    <!-- Onde Encontrar a Etiqueta -->
                    @if(!empty($article->getData()['label_location']))
                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center mb-4">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/15 flex items-center justify-center mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0E368A]" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Localização da Etiqueta de Pressão</h3>
                        </div>

                        @php $location = $article->getData()['label_location'] @endphp
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="h-6 w-6 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-3 flex-shrink-0 mt-0.5">
                                    <span class="text-xs font-semibold text-[#0E368A]">1</span>
                                </div>
                                <p class="text-sm text-gray-700">{{ $location['description'] }}</p>
                            </div>

                            @foreach($location['alternative_locations'] ?? [] as $index => $altLocation)
                            <div class="flex items-start">
                                <div class="h-6 w-6 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-3 flex-shrink-0 mt-0.5">
                                    <span class="text-xs font-semibold text-[#0E368A]">{{ $index + 2 }}</span>
                                </div>
                                <p class="text-sm text-gray-700">{{ $altLocation }}</p>
                            </div>
                            @endforeach
                        </div>

                        @if($location['note'])
                        <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#E06600] mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-sm font-medium text-gray-800">{{ $location['note'] }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </section>

            <!-- Tabela de Pressão -->
            @if(!empty($article->getData()['tire_specifications_by_version']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Tabela de Pressão dos Pneus (PSI - Padrão Brasileiro)
                </h2>

                <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse">
                            <thead>
                                <tr class="bg-[#0E368A] text-white">
                                    <th class="py-3 px-4 text-left font-medium text-sm">Versão</th>
                                    <th class="py-3 px-4 text-center font-medium text-sm">Dianteiros<br>(até 3 pessoas)</th>
                                    <th class="py-3 px-4 text-center font-medium text-sm">Traseiros<br>(até 3 pessoas)</th>
                                    <th class="py-3 px-4 text-center font-medium text-sm">Dianteiros<br>(carga completa)</th>
                                    <th class="py-3 px-4 text-center font-medium text-sm">Traseiros<br>(carga completa)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($article->getData()['tire_specifications_by_version'] as $spec)
                                <tr class="border-b border-gray-200 {{ $spec['css_class'] }}">
                                    <td class="py-3 px-4 text-sm font-medium">
                                        {{ $spec['version'] }}<br>{{ $spec['tire_size'] }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-center">{{ $spec['front_normal'] }}</td>
                                    <td class="py-3 px-4 text-sm text-center">{{ $spec['rear_normal'] }}</td>
                                    <td class="py-3 px-4 text-sm text-center">{{ $spec['front_loaded'] }}</td>
                                    <td class="py-3 px-4 text-sm text-center">{{ $spec['rear_loaded'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 bg-gray-50 text-sm text-gray-700">
                        <span class="font-medium">Importante:</span> Pressões devem ser verificadas com os pneus frios (após pelo menos 3 horas de repouso ou menos de 2 km rodados). A pressão do pneu sobressalente deve ser de 36 PSI para todos os modelos.
                    </div>
                </div>
            </section>
            @endif

            <!-- Banner de Anúncio 2 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-2]
            </div>

            <!-- Condições Especiais de Uso -->
            @if(!empty($article->getData()['special_conditions']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Ajustes para Condições Especiais
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($article->getData()['special_conditions'] as $condition)
                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center mb-4">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/15 flex items-center justify-center mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0E368A]" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    @switch($condition['icon_class'])
                                        @case('trending-up')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                            @break
                                        @case('package')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            @break
                                        @case('dollar-sign')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            @break
                                        @default
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    @endswitch
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">{{ $condition['condition'] }}</h3>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4 mb-4">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm text-gray-600">Ajuste recomendado:</span>
                                <span class="text-sm font-semibold text-[#E06600]">{{ $condition['recommended_adjustment'] }}</span>
                            </div>
                        </div>

                        <p class="text-sm text-gray-700 mb-3">{{ $condition['application'] }}</p>
                        <p class="text-sm text-gray-700">{{ $condition['justification'] }}</p>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Conversão de Unidades -->
            @if(!empty($article->getData()['unit_conversion']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Conversão de Unidades - PSI (Padrão Brasileiro)
                </h2>

                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Tabela de Conversão -->
                        @php $conversion = $article->getData()['unit_conversion'] @endphp
                        <div>
                            <div class="flex items-center mb-4">
                                <div class="h-12 w-12 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/15 flex items-center justify-center mr-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0E368A]" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900">Tabela de Conversão Rápida</h3>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full border-collapse">
                                    <thead>
                                        <tr class="bg-[#0E368A] text-white">
                                            <th class="py-2 px-3 text-center font-medium text-xs">PSI (Brasil)</th>
                                            <th class="py-2 px-3 text-center font-medium text-xs">kgf/cm²</th>
                                            <th class="py-2 px-3 text-center font-medium text-xs">Bar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($conversion['conversion_table'] ?? [] as $row)
                                        <tr class="border-b border-gray-200 {{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                                            <td class="py-2 px-3 text-xs text-center font-medium">{{ $row['psi'] }}</td>
                                            <td class="py-2 px-3 text-xs text-center">{{ $row['kgf_cm2'] }}</td>
                                            <td class="py-2 px-3 text-xs text-center">{{ $row['bar'] }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Fórmulas de Conversão -->
                        <div>
                            <div class="flex items-center mb-4">
                                <div class="h-12 w-12 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/15 flex items-center justify-center mr-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0E368A]" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900">Fórmulas de Conversão</h3>
                            </div>

                            <div class="space-y-4">
                                @if(!empty($conversion['formulas']['psi_para_kgf']))
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h4 class="font-medium text-gray-900 mb-2">PSI para kgf/cm²:</h4>
                                    <p class="text-sm text-gray-700 font-mono bg-white p-2 rounded border">
                                        {{ $conversion['formulas']['psi_para_kgf'] }}
                                    </p>
                                </div>
                                @endif

                                @if(!empty($conversion['formulas']['kgf_para_psi']))
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h4 class="font-medium text-gray-900 mb-2">kgf/cm² para PSI:</h4>
                                    <p class="text-sm text-gray-700 font-mono bg-white p-2 rounded border">
                                        {{ $conversion['formulas']['kgf_para_psi'] }}
                                    </p>
                                </div>
                                @endif

                                @if(!empty($conversion['formulas']['psi_para_bar']))
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h4 class="font-medium text-gray-900 mb-2">PSI para Bar:</h4>
                                    <p class="text-sm text-gray-700 font-mono bg-white p-2 rounded border">
                                        {{ $conversion['formulas']['psi_para_bar'] }}
                                    </p>
                                </div>
                                @endif
                            </div>

                            @if($conversion['note'])
                            <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                <p class="text-sm text-blue-800">
                                    <span class="font-medium">Nota:</span> {{ $conversion['note'] }}
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
            @endif

            <!-- Cuidados e Recomendações -->
            @if(!empty($article->getData()['care_recommendations']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Cuidados e Recomendações para o Brasil
                </h2>

                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        @php $recommendations = $article->getData()['care_recommendations'] @endphp
                        @php $chunkedRecommendations = array_chunk($recommendations, ceil(count($recommendations) / 2)) @endphp
                        
                        @foreach($chunkedRecommendations as $column)
                        <div class="space-y-5">
                            @foreach($column as $recommendation)
                            <div class="flex items-start">
                                <div class="h-8 w-8 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/15 flex items-center justify-center mr-3 flex-shrink-0 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#0E368A]" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        @switch($recommendation['icon_class'])
                                            @case('clock')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                @break
                                            @case('thermometer')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                                @break
                                            @case('tool')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                @break
                                            @case('sun')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                                @break
                                            @case('cloud-rain')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M4 14l4-4 4 4m-4-4v12m8-8l-4-4m4 4h-4" />
                                                @break
                                            @case('rotate-cw')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                @break
                                            @default
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        @endswitch
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-md font-medium text-gray-900 mb-1">{{ $recommendation['category'] }}</h3>
                                    <p class="text-sm text-gray-700">{{ $recommendation['description'] }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endforeach
                    </div>

                    <!-- Alerta -->
                    <div class="mt-6 bg-[#E06600]/5 border border-[#E06600]/20 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0 mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#E06600]" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-md font-medium text-[#E06600] mb-1">Pressão e Segurança</h3>
                                <p class="text-sm text-gray-700">
                                    Pneus com 5 PSI abaixo do recomendado aumentam o consumo em 10% e reduzem a vida útil em até 30%. No calor brasileiro, pneus subcalibrados têm maior risco de estouro.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            @endif

            <!-- Banner de Anúncio 3 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-3]
            </div>

            <!-- Impacto da Pressão no Desempenho -->
            @if(!empty($article->getData()['pressure_impact']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Impacto da Pressão no Desempenho
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($article->getData()['pressure_impact'] as $impact)
                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center mb-4">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br {{ $impact['css_class'] }} flex items-center justify-center mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-{{ $impact['color'] }}-500" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    @switch($impact['icon_class'])
                                        @case('minus')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            @break
                                        @case('check')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M5 13l4 4L19 7" />
                                            @break
                                        @case('alert-triangle')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            @break
                                        @default
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    @endswitch
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">{{ $impact['title'] }}</h3>
                        </div>

                        <ul class="space-y-2">
                            @foreach($impact['items'] as $item)
                            <li class="flex items-start">
                                <div class="h-5 w-5 rounded-full bg-{{ $impact['color'] }}-100 flex items-center justify-center mr-2 flex-shrink-0 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-{{ $impact['color'] }}-500" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        @if($impact['color'] === 'green')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        @endif
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-700">{{ $item }}</p>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Conclusão -->
            @if(!empty($article->getData()['final_considerations']))
            <section class="mb-12 bg-gray-50 rounded-lg p-6">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-4">Considerações Finais</h2>
                <div class="prose prose-lg max-w-none text-gray-800">
                    {!! nl2br(e($article->getData()['final_considerations'])) !!}
                </div>
            </section>
            @endif

            <!-- Artigos Relacionados -->
            @if(!empty($article->getData()['related_topics']))
            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Conteúdo Relacionado
                </h2>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($article->getData()['related_topics'] as $topic)
                    <a href="{{ $topic['url'] }}" class="group">
                        <div class="h-32 rounded-lg bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/10 border border-gray-200 flex items-center justify-center mb-2 hover:shadow-md transition-all">
                            <div class="text-center px-4">
                                <div class="mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto text-[#0E368A]"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        @if(str_contains($topic['title'], 'Pneus'))
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        @elseif(str_contains($topic['title'], 'Rodízio'))
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        @elseif(str_contains($topic['title'], 'Consumo'))
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        @endif
                                    </svg>
                                </div>
                                <span class="font-medium text-[#151C25]">{{ explode(' ', $topic['title'])[0] }}</span>
                            </div>
                        </div>
                        <h3 class="font-medium text-[#0E368A] group-hover:text-[#0A2868] group-hover:underline transition-colors">
                            {{ $topic['title'] }}
                        </h3>
                    </a>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Perguntas Frequentes -->
            @if(!empty($article->getData()['faq']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Perguntas Frequentes
                </h2>

                <div class="space-y-4">
                    @foreach($article->getData()['faq'] as $faq)
                    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                        <button class="flex justify-between items-center w-full px-5 py-4 text-left text-gray-900 font-medium focus:outline-none hover:bg-gray-50 faq-toggle">
                            <span>{{ $faq['pergunta'] }}</span>
                            <svg class="h-5 w-5 text-[#0E368A] faq-icon transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div class="px-5 py-4 border-t border-gray-200 bg-gray-50 faq-content hidden">
                            <p class="text-gray-700">{{ $faq['resposta'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

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

<!-- JavaScript para FAQ Toggle -->
<script>
document.addEventListener('DOMContentLoaded', function() {
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

@endsection