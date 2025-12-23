@extends('shared::layouts.app')

{{-- SEO: Canonical --}}
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
@endpush

@section('content')

{{-- BREADCRUMBS (será renderizado pelo layout se tiver @include) --}}
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
                    <span itemprop="name" class="text-gray-700">{{ $crumb['name'] }}</span>
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

{{-- HERO SECTION --}}
<section class="hero-bg border-b border-gray-200"
    style="background-image: linear-gradient(90deg, rgba(0,0,0,0.05), rgba(0,0,0,0.02));">
    <div class="container mx-auto px-4 py-10">
        <div class="md:flex md:items-center md:justify-between gap-8">

            <div class="max-w-2xl">
                <h1 class="text-3xl font-bold text-gray-900 mb-3 font-montserrat">Catálogo de Veículos</h1>

                <p class="text-sm text-gray-600 font-roboto">
                    Navegue por todas as marcas e modelos de veículos indexados no Mercado Veículos.
                    Acesse informações detalhadas: fichas técnicas, anos, versões, motores e guias de manutenção.
                </p>
            </div>

            <div class="mt-6 md:mt-0">
                <div
                    class="flex flex-col gap-5 bg-gray-100 border border-gray-300 rounded-lg p-4 text-center text-sm text-gray-700 font-roboto">
                    <span><strong class="block font-montserrat">{{ $stats['total_makes'] }}</strong> marcas</span>

                    <span><strong class="block font-montserrat mt-1">{{ $stats['total_models'] }}+</strong>
                        modelos</span>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- BANNER PUBLICITÁRIO 1 (MOCK) --}}
<div class="container mx-auto px-4 mt-10 mb-4">
    <div class="w-full bg-gray-300 rounded-lg flex items-center justify-center" style="min-height:280px">
        <span class="text-gray-700 text-sm font-roboto">Banner - Mock Ad</span>
    </div>
</div>

{{-- MARCAS DESTACADAS (GRID DE LOGOS) --}}
<div class="container mx-auto px-4 py-8">

    <section class="mb-12">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold font-montserrat">Marcas em destaque</h2>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach($featuredMakes as $make)
            <a href="{{ $make['url'] }}"
                class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg hover:border-blue-500 transition-all duration-300 flex flex-col items-center justify-center group">

                {{-- Logo da marca --}}
                <div class="w-28 md:w-32 h-28 md:h-32 mb-3 flex items-center justify-center">
                    @if($make['logo'])
                    <img src="{{ sprintf('%s/%s', Config::get('aws.s3.logo'), $make['logo']) }}"
                        alt="Logo {{ $make['name'] }}" class="max-w-full max-h-full object-contain">
                    @else
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <span class="text-blue-600 font-bold text-xl font-montserrat">
                            {{ substr($make['name'], 0, 1) }}
                        </span>
                    </div>
                    @endif
                </div>

                <h3
                    class="text-center font-semibold text-gray-900 group-hover:text-blue-600 transition-colors font-montserrat text-sm">
                    {{ $make['name'] }}
                </h3>
            </a>
            @endforeach
        </div>
    </section>

    {{-- MODELOS POPULARES --}}
    <section class="mb-12">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold font-montserrat">Modelos populares</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($popularModels as $model)
            <article class="bg-white border border-gray-300 rounded-lg shadow-sm overflow-hidden">
                <a href="{{ $model['url'] }}" class="block group">
                    <img src="{{ $model['image'] }}" alt="{{ $model['name'] }}"
                        class="h-44 w-full object-cover group-hover:opacity-90">
                    <div class="p-4">
                        <h3 class="text-lg font-semibold font-montserrat">{{ $model['name'] }}</h3>
                        <p class="text-xs text-gray-500 font-roboto">
                            {{ $model['year_start'] }}–{{ $model['year_end'] }}
                        </p>
                    </div>
                </a>
            </article>
            @endforeach
        </div>
    </section>

    {{-- TABELA DE TODAS AS MARCAS --}}
    <section class="mb-4">
        <h2 class="text-xl font-semibold mb-4 font-montserrat">Todas as marcas</h2>

        <p class="text-sm text-gray-600 mb-4 font-roboto">
            Lista completa de todas as marcas de veículos indexadas no Mercado Veículos.
        </p>

        <div class="overflow-x-auto bg-white border border-gray-300 rounded-lg shadow-sm">
            <table class="w-full text-sm border-collapse font-roboto">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-montserrat">Marca</th>
                        <th class="px-4 py-2 text-left font-montserrat">Origem</th>
                        <th class="px-4 py-2 text-left font-montserrat">Ação</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($allMakes as $index => $make)
                    <tr class="border-t border-gray-300 {{ $index % 2 === 1 ? 'bg-gray-50' : '' }}">
                        <td class="px-4 py-3">{{ $make['name'] }}</td>
                        <td class="px-4 py-3">{{ $make['country_origin'] }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ $make['url'] }}" class="text-blue-600 hover:underline">
                                Ver modelos
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

</div>

{{-- BANNER PUBLICITÁRIO 2 (MOCK) --}}
<div class="container mx-auto px-4 mb-10">
    <div class="w-full bg-gray-300 rounded-lg flex items-center justify-center" style="min-height:280px">
        <span class="text-gray-700 text-sm font-roboto">Banner - Mock Ad</span>
    </div>
</div>

@endsection