@extends('vehicle-data-center::layouts.app')

@section('title', $seo['title'])
@section('meta_description', $seo['description'])

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
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mx-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

{{-- HERO DO MODELO --}}
<section class="hero-bg border-b border-gray-200" style="background-image: linear-gradient(90deg, rgba(0,0,0,0.05), rgba(0,0,0,0.02));">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="md:flex md:items-center md:justify-between gap-8">
            <div class="max-w-2xl">
                <h1 class="text-3xl font-bold text-gray-900 mb-3 font-montserrat">
                    {{ $make['name'] }} {{ $model['name'] }}
                </h1>
                <p class="text-sm text-gray-600 leading-relaxed font-roboto">
                    {{ $model['description'] }}
                </p>

                <div class="mt-5 flex flex-wrap gap-3">
                    <a href="#anos"
                        class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 font-roboto">
                        Ver anos
                    </a>
                    <a href="#guias"
                        class="px-4 py-2 border border-gray-300 text-sm rounded hover:bg-gray-100 font-roboto">
                        Guias do {{ $model['name'] }}
                    </a>
                </div>
            </div>

            <div class="flex-shrink-0 mt-6 md:mt-0">
                {{-- <img src="{{ $model['image'] }}" 
                     alt="{{ $make['name'] }} {{ $model['name'] }} - imagem ilustrativa"
                     class="rounded-lg shadow max-w-sm"> --}}

                       <img src="{{ asset("/images/placeholder/corolla-full-hero.jpeg") }}" 
                     alt="{{ $make['name'] }} {{ $model['name'] }} - imagem ilustrativa"
                     class="rounded-lg shadow max-w-sm">
            </div>
        </div>
    </div>
</section>

{{-- BANNER RESPONSIVO (MOCK) --}}
<div class="container mx-auto px-4 my-6">
    <div class="w-full bg-gray-300 rounded-lg flex items-center justify-center" style="min-height: 280px;">
        <span class="text-gray-700 text-sm font-roboto">Banner - Mock Ad</span>
    </div>
</div>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-10">
    
    {{-- GUIAS RÁPIDOS DO MODELO --}}
    <section id="guias" class="mb-12">
        <h2 class="text-xl font-semibold mb-4 font-montserrat">Guias rápidos do {{ $model['name'] }}</h2>

        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach($quickGuides as $guide)
                <a href="{{ $guide['url'] }}" 
                   class="block bg-white border border-gray-200 rounded-lg p-4 hover:shadow hover:border-blue-500 transition-all">
                    <h3 class="text-sm font-semibold font-montserrat">{{ $guide['name'] }}</h3>
                    <p class="text-xs text-gray-500 mt-1 font-roboto">{{ $guide['description'] }}</p>
                </a>
            @endforeach
        </div>
    </section>

    {{-- SELEÇÃO DE ANOS --}}
    <section id="anos" class="mb-12">
        <h2 class="text-xl font-semibold mb-4 font-montserrat">Escolha o ano</h2>

        <div class="flex flex-wrap gap-2 text-sm font-roboto">
            @foreach($yearsList as $index => $item)
                <a href="{{ $item['anchor'] }}" 
                   class="px-3 py-1 {{ $index === 0 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800' }} rounded-full hover:bg-blue-700 hover:text-white transition-colors">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </section>

    {{-- CATÁLOGO DE ANOS + VERSÕES --}}
    <section class="space-y-10">
        @foreach($versionsByYear as $yearBlock)
            <div id="{{ $yearBlock['anchor'] }}" class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                <h3 class="text-xl font-semibold mb-4 font-montserrat">{{ $yearBlock['title'] }}</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($yearBlock['versions'] as $version)
                        <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-500 hover:shadow transition-all">
                            <h4 class="font-semibold font-montserrat">{{ $version['name'] }}</h4>
                            @if(isset($version['engine']) || isset($version['transmission']))
                                <p class="text-xs text-gray-600 mb-2 font-roboto">
                                    {{ $version['engine'] ?? '' }}
                                    @if(isset($version['engine']) && isset($version['transmission']))
                                        •
                                    @endif
                                    {{ $version['transmission'] ?? '' }}
                                </p>
                            @endif
                            <a href="{{ $version['url'] }}"
                                class="text-blue-600 text-xs hover:underline font-roboto">Ver detalhes →</a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
        
        {{-- TODO: Se não houver versões mockadas, exibir mensagem --}}
        @if(count($versionsByYear) === 0)
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 text-center">
                <p class="text-gray-600 font-roboto">
                    Versões em breve. Estamos catalogando os dados deste modelo.
                </p>
            </div>
        @endif
    </section>

    {{-- BANNER RESPONSIVO (MOCK) --}}
    <div class="my-6">
        <div class="w-full bg-gray-300 rounded-lg flex items-center justify-center" style="min-height: 280px;">
            <span class="text-gray-700 text-sm font-roboto">Banner - Mock Ad</span>
        </div>
    </div>

    {{-- GUIAS COMPLETOS DO MODELO --}}
    <section id="guias-completos" class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 mb-12">
        <h2 class="text-xl font-bold mb-4 font-montserrat">
            Guias completos do {{ $make['name'] }} {{ $model['name'] }}
        </h2>

        <p class="text-sm text-gray-600 mb-4 font-roboto">
            Conteúdos técnicos evergreen categorizados para todos os anos do {{ $model['name'] }}.
        </p>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 text-sm font-roboto">
            @foreach($allGuideCategories as $category)
                <a href="{{ $category['url'] }}" 
                   class="block bg-gray-50 border border-gray-200 p-3 rounded hover:shadow hover:border-blue-500 transition-all">
                    {{ $category['name'] }}
                </a>
            @endforeach
        </div>
    </section>

    {{-- ARTIGOS DO MODELO (OPCIONAL - DADOS ESTÁTICOS) --}}
    {{-- TODO: Implementar se houver artigos/posts relacionados ao modelo --}}
    {{--
    <section class="mb-12">
        <h2 class="text-xl font-semibold mb-4 font-montserrat">Artigos sobre o {{ $model['name'] }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Cards de artigos aqui -->
        </div>
    </section>
    --}}

</div>

@endsection