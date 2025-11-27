{{-- VehicleDataCenter/Presentation/Resources/views/vehicles/year.blade.php --}}

@extends('auto-info-center::layouts.app')

@section('title', $seo['title'])
@section('meta_description', $seo['description'])

{{-- SEO: Canonical, Open Graph e Schema.org --}}
@push('head')
    <link rel="canonical" href="{{ $seo['canonical'] }}" />
    <link rel="alternate" hreflang="pt-BR" href="{{ $seo['canonical'] }}" />
    
    {{-- Open Graph --}}
    <meta property="og:type" content="website" />
    <meta property="og:title" content="{{ $seo['title'] }}" />
    <meta property="og:description" content="{{ $seo['description'] }}" />
    <meta property="og:image" content="{{ $seo['og_image'] }}" />
    <meta property="og:url" content="{{ $seo['canonical'] }}" />
    <meta property="og:site_name" content="Mercado Veículos" />
    
    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seo['title'] }}">
    <meta name="twitter:description" content="{{ $seo['description'] }}">
    <meta name="twitter:image" content="{{ $seo['og_image'] }}">
    
    {{-- Schema.org - ItemList --}}
    <script type="application/ld+json">
        {!! json_encode($schemaOrg, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush

@section('content')

{{-- BREADCRUMBS --}}
@if(isset($breadcrumbs))
    @section('breadcrumbs')
        <div class="bg-gray-100 border-b border-gray-200">
            <div class="container mx-auto px-4 py-2 whitespace-nowrap overflow-x-auto">
                <nav class="text-xs md:text-sm font-roboto">
                    <ol class="inline-flex" itemscope itemtype="https://schema.org/BreadcrumbList">
                        @foreach($breadcrumbs as $index => $crumb)
                            <li class="flex items-center" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                                @if($crumb['url'])
                                    <a href="{{ $crumb['url'] }}" class="text-blue-600 hover:underline" itemprop="item">
                                        <span itemprop="name">{{ $crumb['name'] }}</span>
                                    </a>
                                    <meta itemprop="position" content="{{ $index + 1 }}" />
                                    @if(!$loop->last)
                                        <svg class="h-3 w-3 mx-1 text-gray-400" fill="none" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    @endif
                                @else
                                    <span class="text-gray-700" itemprop="name">{{ $crumb['name'] }}</span>
                                    <meta itemprop="position" content="{{ $index + 1 }}" />
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </nav>
            </div>
        </div>
    @endsection
@endif

{{-- HERO DO ANO --}}
<section class="bg-gradient-to-r from-blue-50 to-gray-50 border-b border-gray-200">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3 font-montserrat">
                {{ $fullTitle }}
            </h1>
            
            <p class="text-base text-gray-600 mb-6 font-roboto">
                {{ $description }}
            </p>

            {{-- ESTATÍSTICAS DO ANO --}}
            <div class="flex flex-wrap justify-center gap-4 mb-6">
                <div class="bg-white border border-gray-200 rounded-lg px-4 py-3 shadow-sm">
                    <span class="text-2xl font-bold text-blue-600 font-montserrat">{{ $stats['total_versions'] }}</span>
                    <span class="text-sm text-gray-600 ml-2 font-roboto">
                        {{ $stats['total_versions'] === 1 ? 'versão' : 'versões' }}
                    </span>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg px-4 py-3 shadow-sm">
                    <span class="text-2xl font-bold text-green-600 font-montserrat">{{ $stats['fuel_types'] }}</span>
                    <span class="text-sm text-gray-600 ml-2 font-roboto">
                        {{ $stats['fuel_types'] === 1 ? 'combustível' : 'combustíveis' }}
                    </span>
                </div>

                @if($stats['price_range'])
                    <div class="bg-white border border-gray-200 rounded-lg px-4 py-3 shadow-sm">
                        <span class="text-xs text-gray-500 block font-roboto">Faixa de preço</span>
                        <span class="text-sm font-semibold text-gray-900 font-montserrat">
                            {{ $stats['price_range']['min'] }} - {{ $stats['price_range']['max'] }}
                        </span>
                    </div>
                @endif
            </div>

            {{-- NAVEGAÇÃO ENTRE ANOS --}}
            <div class="flex justify-center gap-3">
                @if($nearbyYears['previous']['exists'])
                    <a href="{{ $nearbyYears['previous']['url'] }}" 
                       class="px-4 py-2 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors text-sm font-roboto">
                        ← {{ $nearbyYears['previous']['year'] }}
                    </a>
                @endif

                <a href="{{ route('vehicles.model', ['make' => $make['slug'], 'model' => $model['slug']]) }}" 
                   class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors text-sm font-roboto">
                    Todos os anos
                </a>

                @if($nearbyYears['next']['exists'])
                    <a href="{{ $nearbyYears['next']['url'] }}" 
                       class="px-4 py-2 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors text-sm font-roboto">
                        {{ $nearbyYears['next']['year'] }} →
                    </a>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- BANNER PUBLICITÁRIO (MOCK) --}}
<div class="container mx-auto px-4 my-6">
    <div class="w-full bg-gray-300 rounded-lg flex items-center justify-center" style="min-height: 280px;">
        <span class="text-gray-700 text-sm font-roboto">Banner - Mock Ad</span>
    </div>
</div>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-10">
    
    {{-- GUIAS RÁPIDOS --}}
    <section class="mb-12">
        <h2 class="text-xl font-semibold mb-4 font-montserrat">Guias do {{ $fullTitle }}</h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3">
            @foreach($quickGuides as $guide)
                <a href="{{ $guide['url'] }}" 
                   class="block bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md hover:border-blue-500 transition-all text-center">
                    <div class="text-2xl mb-2">{{ $guide['icon'] }}</div>
                    <h3 class="text-sm font-semibold font-montserrat">{{ $guide['name'] }}</h3>
                    <p class="text-xs text-gray-500 mt-1 font-roboto">{{ $guide['description'] }}</p>
                </a>
            @endforeach
        </div>
    </section>

    {{-- VERSÕES AGRUPADAS POR COMBUSTÍVEL --}}
    <section class="mb-12">
        <h2 class="text-xl font-semibold mb-6 font-montserrat">Escolha a versão</h2>

        @foreach($versionsByFuel as $fuelGroup)
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-800 mb-4 font-montserrat flex items-center">
                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm mr-3">
                        {{ $fuelGroup['fuel_type'] }}
                    </span>
                    <span class="text-sm text-gray-500 font-normal font-roboto">
                        {{ $fuelGroup['count'] }} {{ $fuelGroup['count'] === 1 ? 'versão' : 'versões' }}
                    </span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($fuelGroup['versions'] as $version)
                        <a href="{{ $version['url'] }}" 
                           class="block bg-white border border-gray-200 rounded-lg p-5 hover:shadow-lg hover:border-blue-500 transition-all group">
                            <h4 class="text-lg font-semibold text-gray-900 group-hover:text-blue-600 transition-colors font-montserrat">
                                {{ $version['name'] }}
                            </h4>
                            
                            <div class="mt-2 space-y-1 text-sm text-gray-600 font-roboto">
                                <p class="flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    {{ $version['engine_info'] }}
                                </p>
                                <p class="flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                    </svg>
                                    {{ $version['transmission'] }}
                                </p>
                                
                                @if($version['power_hp'])
                                    <p class="text-blue-600 font-medium">
                                        ⚡ {{ $version['power_hp'] }} cv
                                    </p>
                                @endif

                                @if($version['price_formatted'] !== 'Consulte')
                                    <p class="text-green-600 font-semibold">
                                        💰 {{ $version['price_formatted'] }}
                                    </p>
                                @endif
                            </div>

                            <div class="mt-4 text-blue-600 text-sm font-medium group-hover:underline font-roboto">
                                Ver ficha técnica completa →
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </section>

    {{-- BANNER PUBLICITÁRIO (MOCK) --}}
    <div class="my-8">
        <div class="w-full bg-gray-300 rounded-lg flex items-center justify-center" style="min-height: 280px;">
            <span class="text-gray-700 text-sm font-roboto">Banner - Mock Ad</span>
        </div>
    </div>

    {{-- COMPARAÇÃO RÁPIDA (TABELA) --}}
    <section class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 mb-12">
        <h2 class="text-xl font-semibold mb-4 font-montserrat">Comparação rápida</h2>
        
        <p class="text-sm text-gray-600 mb-4 font-roboto">
            Compare as especificações de todas as versões do {{ $fullTitle }}
        </p>

        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse font-roboto">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left border-b border-gray-200 font-montserrat">Versão</th>
                        <th class="px-4 py-3 text-left border-b border-gray-200 font-montserrat">Motor</th>
                        <th class="px-4 py-3 text-left border-b border-gray-200 font-montserrat">Combustível</th>
                        <th class="px-4 py-3 text-left border-b border-gray-200 font-montserrat">Câmbio</th>
                        <th class="px-4 py-3 text-left border-b border-gray-200 font-montserrat">Potência</th>
                        <th class="px-4 py-3 text-left border-b border-gray-200 font-montserrat">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($versions as $index => $version)
                        <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors {{ $index % 2 === 1 ? 'bg-gray-50' : '' }}">
                            <td class="px-4 py-3 font-medium">{{ $version['name'] }}</td>
                            <td class="px-4 py-3">{{ $version['engine_info'] }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                                    {{ $version['fuel_type'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ $version['transmission'] }}</td>
                            <td class="px-4 py-3">
                                @if($version['power_hp'])
                                    <span class="text-blue-600 font-semibold">{{ $version['power_hp'] }} cv</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ $version['url'] }}" class="text-blue-600 hover:underline">
                                    Ver ficha →
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

</div>

@endsection
