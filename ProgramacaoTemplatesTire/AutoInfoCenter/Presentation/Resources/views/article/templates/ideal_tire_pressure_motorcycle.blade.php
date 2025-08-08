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

            <!-- Alerta de Segurança para Motocicletas -->
            @if(!empty($article->getData()['critical_alerts']))
            @php $criticalAlert = collect($article->getData()['critical_alerts'])->firstWhere('is_critical', true) @endphp
            @if($criticalAlert)
            <div class="mb-8 bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0 mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-md font-medium text-red-800 mb-1">⚠️ {{ $criticalAlert['title'] }}</h3>
                        <p class="text-sm text-red-700">
                            {{ $criticalAlert['description'] }} {{ $criticalAlert['consequence'] }}
                        </p>
                    </div>
                </div>
            </div>
            @endif
            @endif

            <!-- Informações da Moto -->
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Especificações dos Pneus Originais
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Especificações Técnicas -->
                    @if(!empty($article->getData()['tire_specifications']))
                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center mb-4">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-[#DC2626]/5 to-[#DC2626]/15 flex items-center justify-center mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#DC2626]" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Medidas dos Pneus {{ $article->getData()['vehicle_info']['full_name'] ?? 'da Motocicleta' }}</h3>
                        </div>

                        <div class="space-y-4">
                            @if(!empty($article->getData()['tire_specifications']['front_tire']))
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="font-medium text-gray-900 mb-2">🏍️ Pneu Dianteiro</h4>
                                @php $frontTire = $article->getData()['tire_specifications']['front_tire'] @endphp
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-600">Medida:</span>
                                        <span class="font-medium ml-2">{{ $frontTire['size'] }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Índice:</span>
                                        <span class="font-medium ml-2">{{ $frontTire['load_index'] }}{{ $frontTire['speed_rating'] }}</span>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if(!empty($article->getData()['tire_specifications']['rear_tire']))
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="font-medium text-gray-900 mb-2">🏍️ Pneu Traseiro</h4>
                                @php $rearTire = $article->getData()['tire_specifications']['rear_tire'] @endphp
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-600">Medida:</span>
                                        <span class="font-medium ml-2">{{ $rearTire['size'] }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Índice:</span>
                                        <span class="font-medium ml-2">{{ $rearTire['load_index'] }}{{ $rearTire['speed_rating'] }}</span>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <p class="text-xs text-gray-700">
                                <span class="font-medium">Importante:</span> Use sempre pneus radiais com especificação adequada. Pneus convencionais não suportam a velocidade máxima da motocicleta.
                            </p>
                        </div>
                    </div>
                    @endif

                    <!-- Onde Encontrar a Pressão -->
                    @if(!empty($article->getData()['information_location']))
                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center mb-4">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-[#DC2626]/5 to-[#DC2626]/15 flex items-center justify-center mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#DC2626]" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Onde Encontrar as Pressões</h3>
                        </div>

                        @php $location = $article->getData()['information_location'] @endphp
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="h-6 w-6 rounded-full bg-[#DC2626]/10 flex items-center justify-center mr-3 flex-shrink-0 mt-0.5">
                                    <span class="text-xs font-semibold text-[#DC2626]">1</span>
                                </div>
                                <p class="text-sm text-gray-700">
                                    <span class="font-medium">{{ $location['motorcycle_label']['main_location'] ?? 'Etiqueta da Moto' }}:</span> 
                                    {{ $location['important_tip'] ?? 'Consulte manual ou etiqueta para valores oficiais.' }}
                                </p>
                            </div>

                            @if(!empty($location['owner_manual']))
                            <div class="flex items-start">
                                <div class="h-6 w-6 rounded-full bg-[#DC2626]/10 flex items-center justify-center mr-3 flex-shrink-0 mt-0.5">
                                    <span class="text-xs font-semibold text-[#DC2626]">2</span>
                                </div>
                                <p class="text-sm text-gray-700">
                                    <span class="font-medium">Manual do Proprietário:</span> 
                                    {{ $location['owner_manual']['section'] ?? 'Seção Especificações' }} 
                                    @if($location['owner_manual']['approximate_page']) - {{ $location['owner_manual']['approximate_page'] }} @endif
                                </p>
                            </div>
                            @endif

                            @foreach($location['motorcycle_label']['alternative_locations'] ?? [] as $index => $altLocation)
                            <div class="flex items-start">
                                <div class="h-6 w-6 rounded-full bg-[#DC2626]/10 flex items-center justify-center mr-3 flex-shrink-0 mt-0.5">
                                    <span class="text-xs font-semibold text-[#DC2626]">{{ $index + 3 }}</span>
                                </div>
                                <p class="text-sm text-gray-700">{{ $altLocation }}</p>
                            </div>
                            @endforeach
                        </div>

                        <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#DC2626] mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-sm font-medium text-gray-800">
                                    {{ $location['important_tip'] ?? 'Sempre use os valores oficiais como referência principal.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </section>

            <!-- Tabela de Pressão -->
            @if(!empty($article->getData()['pressure_table']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Tabela de Pressão dos Pneus (PSI - {{ $article->getData()['vehicle_info']['full_name'] ?? 'Motocicleta' }})
                </h2>

                <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                    @php $pressureTable = $article->getData()['pressure_table'] @endphp
                    
                    <!-- Pressões Oficiais -->
                    @if(!empty($pressureTable['official_pressures']))
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse">
                            <thead>
                                <tr class="bg-[#DC2626] text-white">
                                    <th class="py-3 px-4 text-left font-medium text-sm">Condição de Uso</th>
                                    <th class="py-3 px-4 text-center font-medium text-sm">Pneu Dianteiro<br>{{ $article->getData()['tire_specifications']['front_tire']['size'] ?? '' }}</th>
                                    <th class="py-3 px-4 text-center font-medium text-sm">Pneu Traseiro<br>{{ $article->getData()['tire_specifications']['rear_tire']['size'] ?? '' }}</th>
                                    <th class="py-3 px-4 text-center font-medium text-sm">Observações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $official = $pressureTable['official_pressures'] @endphp
                                @if(!empty($official['solo_rider']))
                                <tr class="border-b border-gray-200 bg-white">
                                    <td class="py-3 px-4 text-sm font-medium">🏍️ Piloto Solo<br><span class="text-gray-600 text-xs">{{ $official['solo_rider']['observation'] ?? 'Uso normal' }}</span></td>
                                    <td class="py-3 px-4 text-sm text-center font-bold text-[#DC2626]">{{ $official['solo_rider']['front'] }}</td>
                                    <td class="py-3 px-4 text-sm text-center font-bold text-[#DC2626]">{{ $official['solo_rider']['rear'] }}</td>
                                    <td class="py-3 px-4 text-sm text-center">Uso diário</td>
                                </tr>
                                @endif

                                @if(!empty($official['with_passenger']))
                                <tr class="border-b border-gray-200 bg-gray-50">
                                    <td class="py-3 px-4 text-sm font-medium">👥 Piloto + Garupa<br><span class="text-gray-600 text-xs">{{ $official['with_passenger']['observation'] ?? 'Com passageiro' }}</span></td>
                                    <td class="py-3 px-4 text-sm text-center font-bold text-[#DC2626]">{{ $official['with_passenger']['front'] }}</td>
                                    <td class="py-3 px-4 text-sm text-center font-bold text-[#DC2626]">{{ $official['with_passenger']['rear'] }}</td>
                                    <td class="py-3 px-4 text-sm text-center">Carga dupla</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    @endif

                    <!-- Condições Especiais -->
                    @if(!empty($pressureTable['special_conditions']))
                    <div class="border-t border-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full border-collapse">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="py-2 px-3 text-left font-medium text-xs text-gray-700">Situação Especial</th>
                                        <th class="py-2 px-3 text-center font-medium text-xs text-gray-700">Dianteiro</th>
                                        <th class="py-2 px-3 text-center font-medium text-xs text-gray-700">Traseiro</th>
                                        <th class="py-2 px-3 text-center font-medium text-xs text-gray-700">Observação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pressureTable['special_conditions'] as $condition)
                                    <tr class="border-b border-gray-100 {{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                                        <td class="py-2 px-3 text-xs font-medium">
                                            @switch($condition['icon_class'])
                                                @case('home')
                                                    🏙️
                                                    @break
                                                @case('map')
                                                    🛣️
                                                    @break
                                                @case('zap')
                                                    🏁
                                                    @break
                                                @case('cloud-rain')
                                                    🌧️
                                                    @break
                                                @default
                                                    📍
                                            @endswitch
                                            {{ $condition['situation'] }}
                                        </td>
                                        <td class="py-2 px-3 text-xs text-center font-bold">{{ $condition['front_pressure'] }}</td>
                                        <td class="py-2 px-3 text-xs text-center font-bold">{{ $condition['rear_pressure'] }}</td>
                                        <td class="py-2 px-3 text-xs text-center">{{ $condition['observation'] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    <div class="p-4 bg-gray-50 text-sm text-gray-700">
                        <span class="font-medium">⚠️ Importante:</span> Sempre verifique com pneus frios (pelo menos 3 horas parados). No calor brasileiro (35°C+), a pressão pode aumentar 4-6 PSI durante a pilotagem.
                    </div>
                </div>
            </section>
            @endif

            <!-- Banner de Anúncio 2 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-2]
            </div>

            <!-- Considerações Especiais -->
            @if(!empty($article->getData()['special_considerations']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Ajustes para Condições Especiais
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($article->getData()['special_considerations'] as $consideration)
                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center mb-4">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-[#DC2626]/5 to-[#DC2626]/15 flex items-center justify-center mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#DC2626]" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    @switch($consideration['icon_class'])
                                        @case('thermometer')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                            @break
                                        @case('package')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            @break
                                        @case('target')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            @break
                                        @default
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    @endswitch
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">{{ $consideration['title'] }}</h3>
                        </div>

                        <p class="text-sm text-gray-700 mb-4">{{ $consideration['description'] }}</p>

                        @if(!empty($consideration['factors']))
                        <div class="space-y-2">
                            @foreach($consideration['factors'] as $factor)
                            <div class="flex items-start">
                                <div class="h-4 w-4 rounded-full bg-[#DC2626]/20 flex items-center justify-center mr-2 flex-shrink-0 mt-1">
                                    <div class="h-1 w-1 rounded-full bg-[#DC2626]"></div>
                                </div>
                                <p class="text-sm text-gray-700">{{ $factor }}</p>
                            </div>
                            @endforeach
                        </div>
                        @endif

                        @if(!empty($consideration['types']))
                        <div class="mt-4 space-y-2">
                            @foreach($consideration['types'] as $type)
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-sm text-gray-700">{{ $type }}</p>
                            </div>
                            @endforeach
                        </div>
                        @endif

                        @if(!empty($consideration['orientations']))
                        <div class="mt-4 space-y-2">
                            @foreach($consideration['orientations'] as $orientation)
                            <div class="bg-blue-50 rounded-lg p-3 border border-blue-200">
                                <p class="text-sm text-blue-700">{{ $orientation }}</p>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Conversão de Unidades -->
            @if(!empty($article->getData()['unit_conversion']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Conversão de Unidades
                </h2>

                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Tabela de Conversão -->
                        @php $conversion = $article->getData()['unit_conversion'] @endphp
                        <div>
                            <div class="flex items-center mb-4">
                                <div class="h-12 w-12 rounded-full bg-gradient-to-br from-[#DC2626]/5 to-[#DC2626]/15 flex items-center justify-center mr-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#DC2626]" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900">Conversão Rápida para Motos</h3>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full border-collapse">
                                    <thead>
                                        <tr class="bg-[#DC2626] text-white">
                                            <th class="py-2 px-3 text-center font-medium text-xs">PSI (Brasil)</th>
                                            <th class="py-2 px-3 text-center font-medium text-xs">kgf/cm²</th>
                                            <th class="py-2 px-3 text-center font-medium text-xs">Bar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($conversion['conversion_table'] ?? [] as $row)
                                        <tr class="border-b border-gray-200 {{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                                            <td class="py-2 px-3 text-xs text-center font-bold {{ $row['is_recommended'] ? 'text-[#DC2626]' : '' }}">
                                                {{ $row['psi'] }}
                                            </td>
                                            <td class="py-2 px-3 text-xs text-center">{{ $row['kgf_cm2'] }}</td>
                                            <td class="py-2 px-3 text-xs text-center">{{ $row['bar'] }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Dicas de Calibragem -->
                        <div>
                            <div class="flex items-center mb-4">
                                <div class="h-12 w-12 rounded-full bg-gradient-to-br from-[#DC2626]/5 to-[#DC2626]/15 flex items-center justify-center mr-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#DC2626]" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900">Dicas para Calibragem</h3>
                            </div>

                            <div class="space-y-4">
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h4 class="font-medium text-gray-900 mb-2">🕐 Quando Calibrar</h4>
                                    <ul class="text-sm text-gray-700 space-y-1">
                                        <li>• <span class="font-medium">Semanalmente</span> (obrigatório para motos)</li>
                                        <li>• Sempre pela manhã (pneus frios)</li>
                                        <li>• Antes de viagens longas</li>
                                        <li>• Após mudanças bruscas de temperatura</li>
                                    </ul>
                                </div>

                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h4 class="font-medium text-gray-900 mb-2">📍 Onde Calibrar</h4>
                                    <ul class="text-sm text-gray-700 space-y-1">
                                        <li>• Postos com calibradores digitais</li>
                                        <li>• Oficinas especializadas em motos</li>
                                        <li>• Compressor próprio (recomendado)</li>
                                        <li>• Evite calibradores analógicos antigos</li>
                                    </ul>
                                </div>

                                <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                                    <h4 class="font-medium text-red-800 mb-2">⚠️ Nunca Faça</h4>
                                    <ul class="text-sm text-red-700 space-y-1">
                                        <li>• Calibrar com pneus quentes</li>
                                        <li>• Usar pressões de carro em moto</li>
                                        <li>• Deixar sem calibrar por +7 dias</li>
                                        <li>• Ignorar diferenças dianteiro/traseiro</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($conversion['observation'])
                    <div class="mt-6 p-3 bg-blue-50 rounded-lg border border-blue-200">
                        <p class="text-sm text-blue-800">
                            <span class="font-medium">Nota:</span> {{ $conversion['observation'] }}
                        </p>
                    </div>
                    @endif
                </div>
            </section>
            @endif

            <!-- Cuidados Específicos para Motos -->
            @if(!empty($article->getData()['maintenance_tips']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Cuidados Específicos para Motocicletas
                </h2>

                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        @php $tips = $article->getData()['maintenance_tips'] @endphp
                        @php $chunkedTips = array_chunk($tips, ceil(count($tips) / 2)) @endphp
                        
                        @foreach($chunkedTips as $column)
                        <div class="space-y-5">
                            @foreach($column as $tip)
                            <div class="flex items-start">
                                <div class="h-8 w-8 rounded-full bg-gradient-to-br from-[#DC2626]/5 to-[#DC2626]/15 flex items-center justify-center mr-3 flex-shrink-0 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#DC2626]" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        @switch($tip['icon_class'])
                                            @case('calendar')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                @break
                                            @case('sun')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                                @break
                                            @case('tool')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100-4m0 4v2m0-6V4" />
                                                @break
                                            @default
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        @endswitch
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-md font-medium text-gray-900 mb-1">{{ $tip['category'] }}</h3>
                                    <p class="text-xs text-gray-600 mb-2">{{ $tip['frequency'] }}</p>
                                    <ul class="text-sm text-gray-700 space-y-1">
                                        @foreach($tip['items'] as $item)
                                        <li class="flex items-start">
                                            <div class="h-2 w-2 rounded-full bg-[#DC2626]/40 mr-2 flex-shrink-0 mt-2"></div>
                                            <span class="text-sm">{{ $item }}</span>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endforeach
                    </div>

                    <!-- Alerta Final -->
                    <div class="mt-6 bg-[#DC2626]/5 border border-[#DC2626]/20 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0 mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#DC2626]" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-md font-medium text-[#DC2626] mb-1">🚨 SEGURANÇA CRÍTICA</h3>
                                <p class="text-sm text-gray-700">
                                    <span class="font-medium">Em motos, pneus são questão de vida ou morte.</span>
                                    Uma queda de 5 PSI pode causar instabilidade a 80 km/h. No calor brasileiro,
                                    pneus subcalibrados podem estourar. NUNCA negligencie a calibragem semanal.
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

            <!-- Benefícios da Calibragem Correta -->
            @if(!empty($article->getData()['calibration_benefits']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Benefícios da Calibragem Correta
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($article->getData()['calibration_benefits'] as $benefit)
                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center mb-4">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-{{ $benefit['color_class'] }}-100 to-{{ $benefit['color_class'] }}-200 flex items-center justify-center mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-{{ $benefit['color_class'] }}-500" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    @switch($benefit['icon_class'])
                                        @case('shield')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                            @break
                                        @case('zap')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                                            @break
                                        @case('dollar-sign')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            @break
                                        @case('clock')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            @break
                                        @default
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M5 13l4 4L19 7" />
                                    @endswitch
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">{{ $benefit['title'] }}</h3>
                        </div>

                        <p class="text-sm text-gray-700 mb-4">{{ $benefit['description'] }}</p>

                        @if(!empty($benefit['aspects']))
                        <ul class="space-y-2 mb-4">
                            @foreach($benefit['aspects'] as $aspect)
                            <li class="flex items-start">
                                <div class="h-5 w-5 rounded-full bg-{{ $benefit['color_class'] }}-100 flex items-center justify-center mr-2 flex-shrink-0 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-{{ $benefit['color_class'] }}-500" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-700">{{ $aspect }}</p>
                            </li>
                            @endforeach
                        </ul>
                        @endif

                        @if($benefit['estimated_savings'])
                        <div class="bg-{{ $benefit['color_class'] }}-50 rounded-lg p-3 border border-{{ $benefit['color_class'] }}-200">
                            <p class="text-sm font-medium text-{{ $benefit['color_class'] }}-800">
                                💰 {{ $benefit['estimated_savings'] }}
                            </p>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Procedimento de Calibragem -->
            @if(!empty($article->getData()['calibration_procedure']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Como Calibrar Corretamente
                </h2>

                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach($article->getData()['calibration_procedure'] as $step)
                        <div class="text-center">
                            <div class="flex items-center justify-center mb-4">
                                <div class="h-16 w-16 rounded-full bg-gradient-to-br from-[#DC2626]/5 to-[#DC2626]/15 flex items-center justify-center">
                                    <span class="text-2xl font-bold text-[#DC2626]">{{ $step['number'] }}</span>
                                </div>
                            </div>
                            
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">{{ $step['title'] }}</h3>
                            <p class="text-sm text-gray-700 mb-4">{{ $step['description'] }}</p>
                            
                            @if(!empty($step['details']))
                            <ul class="text-xs text-gray-600 space-y-1 text-left bg-gray-50 rounded-lg p-3">
                                @foreach($step['details'] as $detail)
                                <li class="flex items-start">
                                    <div class="h-1 w-1 rounded-full bg-[#DC2626] mr-2 flex-shrink-0 mt-2"></div>
                                    <span>{{ $detail }}</span>
                                </li>
                                @endforeach
                            </ul>
                            @endif
                        </div>
                        @endforeach
                    </div>
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
                            <svg class="h-5 w-5 text-[#DC2626] faq-icon transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
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

            <!-- Conclusão -->
            @if(!empty($article->getData()['final_considerations']))
            <section class="mb-12 bg-gray-50 rounded-lg p-6">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-4">Considerações Finais</h2>
                <div class="prose prose-lg max-w-none text-gray-800">
                    {!! nl2br(e($article->getData()['final_considerations'])) !!}
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