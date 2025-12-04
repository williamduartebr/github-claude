@extends('vehicle-data-center::layouts.app')

@section('title', $seo['title'])
@section('meta_description', $seo['description'])

{{-- SEO: Canonical e Open Graph --}}
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

{{-- Twitter --}}
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

{{-- HERO DA MARCA --}}
<section class="hero-bg border-b border-gray-200"
    style="background-image: linear-gradient(90deg, rgba(0,0,0,0.05), rgba(0,0,0,0.02));">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="md:flex md:items-center md:justify-between gap-8">
            <div class="max-w-2xl">
                <h1 class="text-3xl font-bold text-gray-900 mb-3 font-montserrat">{{ $make['name'] }}</h1>
                <p class="text-sm text-gray-600 font-roboto">
                    {{ $make['description'] }}
                </p>

                <div class="mt-5 flex flex-wrap gap-3">
                    <a href="#modelos-populares"
                        class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 font-roboto">
                        Modelos populares
                    </a>
                    <a href="#guias-{{ $make['slug'] }}"
                        class="px-4 py-2 border border-gray-300 text-sm rounded hover:bg-gray-100 font-roboto">
                        Guias da {{ $make['name'] }}
                    </a>
                </div>
            </div>

            <div class="flex-shrink-0 mt-6 md:mt-0">
                @if($make['logo'])
                <img src="{{ asset(" /images/statics/logos/". $make['logo']) }}" alt="{{ $make['name'] }}"
                    class="w-40 h-auto opacity-90">
                @else
                <div class="w-40 h-40 bg-gray-200 rounded-lg flex items-center justify-center">
                    <span class="text-4xl font-bold text-gray-400 font-montserrat">
                        {{ substr($make['name'], 0, 1) }}
                    </span>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- BANNER RESPONSIVO (MOCK GOOGLE ADS) --}}
<div class="container mx-auto px-4 my-6">
    <div class="w-full bg-gray-300 rounded-lg flex items-center justify-center" style="min-height: 280px;">
        <span class="text-gray-700 text-sm font-roboto">Banner - Mock Ad</span>
    </div>
</div>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- GUIAS POR MARCA --}}
    <section id="guias-{{ $make['slug'] }}" class="mb-12">
        <h2 class="text-xl font-semibold mb-4 font-montserrat">Guias técnicos da {{ $make['name'] }}</h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 text-sm font-roboto">
            @foreach($guideCategories as $category)
            <a href="{{ $category['url'] }}"
                class="block bg-white border border-gray-200 rounded p-3 hover:shadow hover:border-blue-500 transition-all">
                {{ $category['name'] }}
            </a>
            @endforeach
        </div>
    </section>

    {{-- MODELOS POPULARES --}}
    <section id="modelos-populares" class="mb-10">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold font-montserrat">Modelos populares</h2>
            <a href="#todos-os-modelos" class="text-sm text-blue-600 hover:underline font-roboto">Ver todos →</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($popularModels as $model)
            <article
                class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                <a href="{{ $model['url'] }}" class="block group">
                    <img src="{{ asset(" /images/placeholder/corolla.jpeg") }}"
                        alt="{{ $make['name'] }} {{ $model['name'] }}"
                        class="h-44 w-full object-cover group-hover:opacity-90 transition-opacity" />

                    {{-- <img src="{{ $model['image'] }}" alt="{{ $make['name'] }} {{ $model['name'] }}"
                        class="h-44 w-full object-cover group-hover:opacity-90 transition-opacity" /> --}}


                    <div class="p-4">
                        <h3 class="text-lg font-medium font-montserrat">{{ $model['name'] }}</h3>
                        <p class="text-xs text-gray-500 mt-1 font-roboto">
                            {{ $model['year_start'] }}–{{ $model['year_end'] }}
                        </p>
                    </div>
                </a>
            </article>
            @endforeach
        </div>
    </section>

    {{-- TODOS OS MODELOS → TABELA --}}
    <section id="todos-os-modelos" class="mb-16">
        <h2 class="text-xl font-semibold mb-4 font-montserrat">Todos os modelos {{ $make['name'] }}</h2>

        <p class="text-sm text-gray-600 mb-4 font-roboto">
            Lista completa dos modelos {{ $make['name'] }} indexados no Mercado Veículos.
        </p>

        <div class="overflow-x-auto bg-white border border-gray-200 rounded-lg shadow-sm">
            <table class="min-w-full text-sm border-collapse font-roboto">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-montserrat border-b border-gray-200">Modelo</th>
                        <th class="px-4 py-2 text-left font-montserrat border-b border-gray-200">Segmento</th>
                        <th class="px-4 py-2 text-left font-montserrat border-b border-gray-200">Período</th>
                        <th class="px-4 py-2 text-left font-montserrat border-b border-gray-200">Ação</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($allModels as $index => $model)
                    <tr class="border-t border-gray-200 {{ $index % 2 === 1 ? 'bg-gray-50' : '' }}">
                        <td class="px-4 py-3">{{ $model['name'] }}</td>
                        <td class="px-4 py-3">{{ $model['category'] }}</td>
                        <td class="px-4 py-3">{{ $model['year_start'] }}–{{ $model['year_end'] }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ $model['url'] }}" class="text-blue-600 hover:underline">
                                Abrir
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