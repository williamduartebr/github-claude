@extends('guide-data-center::layouts.app')

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

@php
    // Construir lista de categorias para o Schema
    $categoryItems = [];
    $position = 1;
    
    foreach($categories as $category) {
        $categoryItems[] = [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => $category['name'],
            'url' => $category['url']
        ];
        $position++;
    }

    $webPageSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        'name' => 'Guias Automotivos - Especificações Técnicas | Mercado Veículos',
        'description' => 'Guias técnicos completos para todos os veículos: óleo, pneus, calibragem, revisões, consumo, problemas comuns e muito mais.',
        'url' => route('guide.index'),
        'isPartOf' => [
            '@type' => 'WebSite',
            'name' => 'Mercado Veículos',
            'url' => 'https://mercadoveiculos.com'
        ],
        'speakable' => [
            '@type' => 'SpeakableSpecification',
            'cssSelector' => ['h1', 'h2']
        ],
        'mainEntity' => [
            '@type' => 'ItemList',
            'itemListElement' => $categoryItems
        ],
        'breadcrumb' => [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Início',
                    'item' => url('/')
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'Guias',
                    'item' => route('guide.index')
                ]
            ]
        ]
    ];
@endphp

<!-- Structured Data - WebPage -->
<script type="application/ld+json">
{!! json_encode($webPageSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
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

{{-- HERO --}}
<main id="main-content" class="container mx-auto px-4 py-8 mb-8" itemscope itemtype="https://schema.org/CollectionPage">
    <header class="mb-8 text-center md:text-left">
        <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-3 font-montserrat" itemprop="headline">
            Guias Automotivos
        </h1>
        <p class="text-base md:text-lg text-gray-600 max-w-4xl mx-auto md:mx-0 font-roboto" itemprop="description">
            Acesse guias técnicos completos para qualquer veículo: óleo, pneus, calibragem, consumo, revisão,
            baterias, manutenção, fluidos e muito mais. Selecione uma categoria e filtre por marca e modelo.
        </p>
    </header>

    {{-- BANNER RESPONSIVO (MOCK) --}}
    <section class="container mx-auto mb-8">
        <div class="w-full bg-gray-300 rounded-lg flex items-center justify-center" style="min-height: 280px;">
            <span class="text-gray-700 text-sm font-roboto">Banner - Mock Ad</span>
        </div>
    </section>

    {{-- CATEGORIAS POPULARES --}}
    <section id="categorias" class="mb-12">
        <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-6 font-montserrat">Categorias Populares</h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach($categories as $category)
            <a href="{{ $category['url'] }}" 
               class="block p-4 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300">
                <div class="flex flex-col items-center text-center">
                    <div class="mb-3 p-3 rounded-full {{ $category['icon_bg_color'] ?? 'bg-blue-50' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" 
                             class="w-8 h-8 {{ $category['icon_text_color'] ?? 'text-blue-600' }}" 
                             fill="none" 
                             viewBox="0 0 24 24" 
                             stroke="currentColor" 
                             aria-hidden="true">
                            {!! $category['icon_svg'] ?? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />' !!}
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-gray-800 font-roboto">{{ $category['name'] }}</h3>
                </div>
            </a>
            @endforeach
        </div>
    </section>

    {{-- GUIAS POR MARCA --}}
    <section id="marcas" class="mb-12">
        <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-6 font-montserrat">Guias por Marca</h2>

        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-4">
            @foreach($makes as $make)
            <a href="{{ $make['url'] }}" 
               class="block p-4 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300">
                <div class="flex flex-col items-center text-center">
                    <div class="mb-2 w-12 h-12 rounded flex items-center justify-center">
                          <img src="{{ asset(sprintf('images/statics/logos/%s', $make['make_logo'] )) }}" alt="Logo {{ $make['name'] }}"
                        class="w-12 h-12 mb-2 object-contain">
                    </div>
                    <h3 class="text-sm font-medium text-gray-800 font-roboto">{{ $make['name'] }}</h3>
                </div>
            </a>
            @endforeach
        </div>
    </section>

    {{-- MODELOS POPULARES (ENTRADA RÁPIDA) --}}
    <section id="modelos-populares" class="mb-16">
        <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-6 font-montserrat">Guias para Modelos Populares</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($popularModels as $model)
            <article class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                <a href="{{ $model['url'] }}" class="block group">
                    <img src="{{ $model['image'] }}" 
                         alt="{{ $model['name'] }}"
                         class="w-full h-40 object-cover group-hover:opacity-90 transition-opacity">
                    <div class="p-4">
                        <h3 class="text-lg font-semibold font-montserrat">{{ $model['name'] }}</h3>
                        <p class="text-xs text-gray-500 font-roboto">{{ $model['description'] }}</p>
                    </div>
                </a>
            </article>
            @endforeach
        </div>
    </section>

    {{-- BANNER RESPONSIVO (MOCK) --}}
    <section class="container mx-auto mb-8">
        <div class="w-full bg-gray-300 rounded-lg flex items-center justify-center" style="min-height: 280px;">
            <span class="text-gray-700 text-sm font-roboto">Banner - Mock Ad</span>
        </div>
    </section>

</main>

@endsection