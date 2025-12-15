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
        <div class="md:flex md:items-center md:justify-between gap-8">
            <div class="max-w-2xl">
                <h1 class="text-3xl font-bold text-gray-900 mb-3 font-montserrat">
                    {{ $category['name'] }} – {{ $make['name'] }}
                </h1>
                <p class="text-sm text-gray-600 font-roboto">
                    Selecione um modelo {{ $make['name'] }} e acesse o guia completo de {{ strtolower($category['name'])
                    }}:
                    especificações, recomendações e informações válidas para todas as versões e motores do ano
                    selecionado.
                </p>
            </div>

            <div class="mt-6 md:mt-0">
                <img src="{{ $make['logo'] }}" class="w-40 h-auto opacity-90" alt="{{ $make['name'] }}">
            </div>
        </div>
    </div>
</section>

{{-- BANNER RESPONSIVO (MOCK) --}}
<div class="container mx-auto px-4 sm:px-6 lg:px-8 mt-12">
    <div class="w-full bg-gray-300 rounded-lg flex items-center justify-center" style="min-height: 280px;">
        <span class="text-gray-700 text-sm font-roboto">Banner - Mock Ad</span>
    </div>
</div>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- SEARCH FILTER --}}
    <section class="bg-white border border-gray-200 rounded-lg p-4 mb-10">
        <form class="grid grid-cols-1 md:grid-cols-6 gap-3">
            <div class="md:col-span-3">
                <label class="text-xs text-gray-600 font-roboto">Modelo {{ $make['name'] }}</label>
                <input type="text" placeholder="Ex: Corolla, Hilux, Yaris"
                    class="mt-1 w-full border border-gray-200 rounded px-3 py-2 text-sm font-roboto focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="md:col-span-2">
                <label class="text-xs text-gray-600 font-roboto">Ano</label>
                <input type="number" placeholder="Ex: 2003"
                    class="mt-1 w-full border border-gray-200 rounded px-3 py-2 text-sm font-roboto focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="md:col-span-1 flex items-end">
                <button class="w-full bg-blue-600 text-white text-sm px-3 py-2 rounded hover:bg-blue-700 font-roboto">
                    Buscar
                </button>
            </div>
        </form>
        <p class="text-xs text-gray-500 mt-2 font-roboto">
            Pesquise por modelo + ano para acessar o guia completo.
        </p>
    </section>

    {{-- MODELOS POPULARES --}}
    @if(count($popularModels) > 0)
    <section class="mb-12">
        <h2 class="text-xl font-semibold mb-4 font-montserrat">Modelos populares {{ $make['name'] }}</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($popularModels as $model)
            <a href="{{ $model['url'] }}"
                class="block bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden hover:shadow hover:border-blue-500 transition-all">
                <img src="{{ $model['image'] }}" alt="{{ $make['name'] }} {{ $model['name'] }}"
                    class="w-full h-40 object-cover">
                <div class="p-4 text-sm font-roboto">
                    <div class="font-semibold font-montserrat">{{ $model['name'] }}</div>
                    {{-- <div class="text-xs text-gray-500">{{ $model['description'] }}</div> --}}
                </div>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- BANNER --}}
    <div class="my-12">
        <div class="w-full bg-gray-300 rounded-lg flex items-center justify-center" style="min-height: 280px;">
            <span class="text-gray-700 text-sm font-roboto">Banner - Mock Ad</span>
        </div>
    </div>

    {{-- LISTA COMPLETA DE MODELOS --}}
    <section class="mb-16">
        <h2 class="text-xl font-semibold mb-4 font-montserrat">
            Modelos {{ $make['name'] }} – Guias de {{ strtolower($category['name']) }}
        </h2>

        <p class="text-sm text-gray-600 mb-4 font-roboto">
            Escolha o modelo para abrir a lista de anos.
        </p>

        <div class="overflow-x-auto bg-white border border-gray-200 rounded-lg shadow-sm">
            <table class="min-w-full text-sm font-roboto">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-montserrat border-b border-gray-200">Modelo</th>
                        <th class="px-4 py-2 text-left font-montserrat border-b border-gray-200">Segmento</th>
                        <th class="px-4 py-2 text-left font-montserrat border-b border-gray-200">Anos</th>
                        <th class="px-4 py-2 text-left font-montserrat border-b border-gray-200">Guia</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allModels as $index => $model)
                    <tr class="border-t border-gray-200 {{ $index % 2 === 1 ? 'bg-gray-50' : '' }}">
                        <td class="px-4 py-3">{{ $model['name'] }}</td>
                        {{-- <td class="px-4 py-3">{{ $model['segment'] }}</td> --}}
                        {{-- <td class="px-4 py-3">{{ $model['years'] }}</td> --}}
                        <td class="px-4 py-3">
                            <a href="{{ $model['url'] }}" class="text-blue-600 hover:underline">Abrir</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    {{-- LINKS PARA OUTRAS CATEGORIAS DA MARCA --}}
    <section class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4 font-montserrat">
            Guias complementares da {{ $make['name'] }}
        </h2>

        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3 text-sm font-roboto">
            @foreach($complementaryCategories as $cat)
            <a href="/guias/{{ $cat['slug'] }}/{{ $make['slug'] }}"
                class="block bg-gray-50 border border-gray-200 rounded p-3 hover:shadow hover:border-blue-500 transition-all">
                {{ $cat['name'] }}
            </a>
            @endforeach
        </div>
    </section>

</div>

@endsection