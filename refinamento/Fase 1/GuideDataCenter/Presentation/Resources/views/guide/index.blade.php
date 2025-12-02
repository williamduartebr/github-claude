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
@endsection
@endif

{{-- HERO --}}
<section class="bg-white border-b border-gray-200">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <h1 class="text-3xl font-bold text-gray-900 mb-3 font-montserrat">Guias Automotivos</h1>

        <p class="text-sm text-gray-600 max-w-2xl font-roboto">
            Acesse guias técnicos completos para qualquer veículo: óleo, pneus, calibragem, consumo, revisão,
            baterias,
            manutenção, fluidos e muito mais.
            Selecione uma categoria e filtre por marca e modelo.
        </p>
    </div>
</section>

{{-- BANNER RESPONSIVO (MOCK) --}}
<div class="container mx-auto px-4 my-6">
    <div class="w-full bg-gray-300 rounded-lg flex items-center justify-center" style="min-height: 280px;">
        <span class="text-gray-700 text-sm font-roboto">Banner - Mock Ad</span>
    </div>
</div>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 pt-10">

    {{-- CATEGORIAS DE GUIAS --}}
    <section id="categorias" class="mb-12">
        <h2 class="text-xl font-semibold mb-4 font-montserrat">Categorias</h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 text-sm font-roboto">
            @foreach($categories as $category)
            <a href="{{ $category['url'] }}"
                class="block bg-white border border-gray-200 rounded p-4 hover:shadow hover:border-blue-500 transition-all">
                {{ $category['name'] }}
            </a>
            @endforeach
        </div>
    </section>

    {{-- MARCAS SUPORTADAS --}}
    <section id="marcas" class="mb-12">
        <h2 class="text-xl font-semibold mb-4 font-montserrat">Guias por marca</h2>

        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-4 font-roboto">
            @foreach($makes as $make)
            <a href="{{ $make['url'] }}"
                class="block bg-white border border-gray-200 rounded p-4 hover:shadow hover:border-blue-500 transition-all text-center">
                {{ $make['name'] }}
            </a>
            @endforeach
        </div>
    </section>

    {{-- MODELOS POPULARES (ENTRADA RÁPIDA) --}}
    <section id="modelos-populares" class="mb-16">
        <h2 class="text-xl font-semibold mb-4 font-montserrat">Guias para modelos populares</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($popularModels as $model)
            <article
                class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                <a href="{{ $model['url'] }}" class="block group">
                    <img src="{{ $model['image'] }}" alt="{{ $model['name'] }}"
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

</div>

{{-- BANNER RESPONSIVO (MOCK) --}}
<div class="container mx-auto px-4 my-6">
    <div class="w-full bg-gray-300 rounded-lg flex items-center justify-center" style="min-height: 280px;">
        <span class="text-gray-700 text-sm font-roboto">Banner - Mock Ad</span>
    </div>
</div>

@endsection