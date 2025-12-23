@extends('shared::layouts.app')

{{-- SEO: Canonical e Open Graph --}}
@push('head')
<link rel="canonical" href="{{ $seo['canonical'] }}" />
<link rel="alternate" hreflang="pt-BR" href="{{ $seo['canonical'] }}" />

<meta property="og:type" content="website" />
<meta property="og:title" content="{{ $seo['title'] }}" />
<meta property="og:description" content="{{ $seo['description'] }}" />
<meta property="og:image" content="{{ $seo['og_image'] }}" />
<meta property="og:url" content="{{ $seo['canonical'] }}" />
<meta property="og:site_name" content="Mercado Veículos" />

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seo['title'] }}">
<meta name="twitter:description" content="{{ $seo['description'] }}">
<meta name="twitter:image" content="{{ $seo['og_image'] }}">
@endpush

@section('content')

{{-- BREADCRUMBS --}}
@if(isset($breadcrumbs))
<div class="bg-gray-100 border-b border-gray-200">
    <div class="container mx-auto px-4 py-2 whitespace-nowrap overflow-x-auto">
        <nav class="text-xs md:text-sm font-roboto">
            <ol class="inline-flex" itemscope itemtype="https://schema.org/BreadcrumbList">
                @foreach($breadcrumbs as $index => $crumb)
                <li class="flex items-center" itemprop="itemListElement" itemscope
                    itemtype="https://schema.org/ListItem">
                    @if($crumb['url'])
                    <a href="{{ $crumb['url'] }}" class="text-blue-600 hover:underline" itemprop="item">
                        <span itemprop="name">{{ $crumb['name'] }}</span>
                    </a>
                    <meta itemprop="position" content="{{ $index + 1 }}" />
                    @if(!$loop->last)
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mx-1 text-gray-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
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
@endif

{{-- HERO DO ANO com Thumbnail --}}
<section class="bg-white border-b border-gray-200">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="md:flex md:items-start md:justify-between gap-8">
            {{-- Conteúdo Principal --}}
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-gray-900 mb-3 font-montserrat">
                    {{ $make['name'] }} {{ $model['name'] }} {{ $year }}
                </h1>

                <p class="text-sm text-gray-600 leading-relaxed mb-4 font-roboto">
                    Conheça as {{ $stats['versions_count'] }} {{ $stats['versions_count'] === 1 ? 'versão disponível' :
                    'versões disponíveis' }} do {{ $make['name'] }} {{ $model['name'] }} {{ $year }}: fichas técnicas
                    completas, especificações, motores, consumo e guias de manutenção.
                </p>

                {{-- Estatísticas Rápidas --}}
                <div class="flex flex-wrap gap-3 mb-5">
                    <div class="bg-blue-50 border border-blue-200 rounded px-3 py-2 text-sm">
                        <span class="font-semibold text-blue-900">{{ $stats['versions_count'] }}</span>
                        <span class="text-blue-700">{{ $stats['versions_count'] === 1 ? 'versão' : 'versões' }}</span>
                    </div>

                    @if(isset($stats['fuel_types']))
                    <div class="bg-green-50 border border-green-200 rounded px-3 py-2 text-sm">
                        <span class="text-green-700">{{ implode(', ', $stats['fuel_types']) }}</span>
                    </div>
                    @endif
                </div>

                {{-- Ações --}}
                <div class="flex flex-wrap gap-3">
                    <a href="#versoes"
                        class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 font-roboto">
                        Ver versões
                    </a>
                    <a href="{{ route('vehicles.model', ['make' => $make['slug'], 'model' => $model['slug']]) }}"
                        class="px-4 py-2 border border-gray-300 text-sm rounded hover:bg-gray-100 font-roboto">
                        ← Outros anos
                    </a>
                </div>
            </div>

            {{-- Thumbnail do Veículo --}}
            <div class="flex-shrink-0 mt-6 md:mt-0">
                {{--
                ⚠️ TODO: Adicionar imagem real do banco quando disponível
                Padrão: <img src="{{ $yearImage }}" alt="...">
                --}}
                <img src="{{ asset('/images/placeholder/corolla-full-hero.jpeg') }}"
                    alt="{{ $make['name'] }} {{ $model['name'] }} {{ $year }}"
                    class="rounded-lg shadow-md max-w-xs w-full">
                <p class="text-xs text-gray-500 mt-2 text-center font-roboto">Imagem ilustrativa</p>
            </div>
        </div>
    </div>
</section>

{{-- BANNER --}}
<div class="container mx-auto px-4 sm:px-6 lg:px-8 mt-12">
    <div class="w-full bg-gray-300 rounded-lg flex items-center justify-center" style="min-height: 280px;">
        <span class="text-gray-700 text-sm font-roboto">Banner - Mock Ad</span>
    </div>
</div>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- FILTRO POR COMBUSTÍVEL (Opcional) --}}
    @if(isset($fuel_filters) && count($fuel_filters) > 1)
    <section class="mb-8">
        <h2 class="text-lg font-semibold mb-3 font-montserrat">Filtrar por combustível</h2>
        <div class="flex flex-wrap gap-2">
            <button class="px-3 py-1 bg-blue-600 text-white text-sm rounded-full">
                Flex
            </button>
            <button class="px-3 py-1 bg-gray-200 text-gray-800 text-sm rounded-full hover:bg-gray-300">
                Gasolina
            </button>
            <button class="px-3 py-1 bg-gray-200 text-gray-800 text-sm rounded-full hover:bg-gray-300">
                Diesel
            </button>
        </div>
    </section>
    @endif

    {{-- ESCOLHA A VERSÃO com Thumbnails --}}
    <section id="versoes" class="mb-12">
        <h2 class="text-xl font-semibold mb-4 font-montserrat">Escolha a versão</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($versions as $version)
            <div
                class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:border-blue-500 hover:shadow-md transition-all">
                <a href="{{ $version['url'] }}" class="block">
                    {{-- Thumbnail da Versão --}}
                    <div class="relative h-40 bg-gray-100">
                        {{--
                        ⚠️ TODO: Adicionar imagem real do banco quando disponível
                        Padrão: <img src="{{ $version['image'] }}" alt="...">
                        --}}
                        <img src="{{ asset('/images/placeholder/corolla-full-hero.jpeg') }}"
                            alt="{{ $version['name'] }}" class="w-full h-full object-cover">

                        {{-- Badge de Combustível --}}
                        @if(isset($version['fuel']))
                        <div class="absolute top-2 right-2 bg-blue-600 text-white text-xs px-2 py-1 rounded">
                            {{ $version['fuel'] }}
                        </div>
                        @endif
                    </div>

                    {{-- Informações da Versão --}}
                    <div class="p-4">
                        <h3 class="font-semibold text-lg mb-1 font-montserrat">{{ $version['name'] }}</h3>

                        {{-- Especificações Breves --}}
                        <div class="text-xs text-gray-600 space-y-1 mb-3 font-roboto">
                            @if(isset($version['engine']))
                            <p>⚙️ {{ $version['engine'] }}</p>
                            @endif

                            @if(isset($version['transmission']))
                            <p>🔄 {{ $version['transmission'] }}</p>
                            @endif

                            @if(isset($version['power']))
                            <p>⚡ {{ $version['power'] }}</p>
                            @endif
                        </div>

                        {{-- Preço (se disponível) --}}
                        @if(isset($version['price']))
                        <div class="mb-3">
                            <p class="text-sm font-semibold text-gray-900 font-montserrat">
                                💰 {{ $version['price'] }}
                            </p>
                        </div>
                        @endif

                        {{-- CTA --}}
                        <div class="text-blue-600 text-sm hover:underline font-roboto">
                            Ver ficha técnica completa →
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>

        @if(count($versions) === 0)
        <div class="bg-white border border-gray-200 rounded-lg p-6 text-center">
            <p class="text-gray-600 font-roboto">
                Versões em breve. Estamos catalogando os dados deste ano.
            </p>
        </div>
        @endif
    </section>

    {{-- BANNER RESPONSIVO (MOCK) --}}
    <div class="my-12">
        <div class="w-full bg-gray-300 rounded-lg flex items-center justify-center" style="min-height: 280px;">
            <span class="text-gray-700 text-sm font-roboto">Banner - Mock Ad</span>
        </div>
    </div>

    {{-- GUIAS TÉCNICOS DO ANO --}}
    <section class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 mb-6">
        <h2 class="text-xl font-bold mb-4 font-montserrat">
            Guias técnicos do {{ $make['name'] }} {{ $model['name'] }} {{ $year }}
        </h2>

        <p class="text-sm text-gray-600 mb-4 font-roboto">
            Acesse guias completos de manutenção, especificações e cuidados para o seu veículo.
        </p>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 text-sm font-roboto">
            @foreach($guideCategories as $guide)
            <a href="{{ $guide['url'] }}"
                class="block bg-gray-50 border border-gray-200 p-3 rounded hover:shadow hover:border-blue-500 transition-all text-center">
                <div class="text-2xl mb-1">{{ $guide['icon'] }}</div>
                {{ $guide['name'] }}
            </a>
            @endforeach
        </div>
    </section>

</div>

@endsection