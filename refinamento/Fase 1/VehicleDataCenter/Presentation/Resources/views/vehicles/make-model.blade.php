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

{{-- Schema.org --}}
@if(isset($structuredData))
<script type="application/ld+json">
{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endif
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

{{-- HERO DO MODELO --}}
<section class="bg-white border-b border-gray-200">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="md:flex md:items-center md:justify-between gap-8">
            <div class="max-w-2xl">
                <h1 class="text-3xl font-bold text-gray-900 mb-3 font-montserrat">
                    Guias {{ $make['name'] }} {{ $model['name'] }}
                </h1>
                <p class="text-sm text-gray-600 leading-relaxed font-roboto">
                    {{ $model['description'] }}
                </p>

                {{-- Estatísticas --}}
                <div class="grid grid-cols-3 gap-4 mt-6">
                    <div>
                        <div class="text-2xl font-bold text-blue-600 font-montserrat">{{ $stats['total_guides'] }}</div>
                        <div class="text-xs text-gray-600 font-roboto">Guias disponíveis</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-blue-600 font-montserrat">{{ $stats['categories_count'] }}</div>
                        <div class="text-xs text-gray-600 font-roboto">Categorias</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-blue-600 font-montserrat">{{ $stats['years_count'] }}</div>
                        <div class="text-xs text-gray-600 font-roboto">Anos cobertos</div>
                    </div>
                </div>
            </div>

            <div class="mt-6 md:mt-0">
                <img src="{{ sprintf('%s/%s', Config::get('aws.s3.logo'), $make['slug'] . '.svg') }}"
                    class="w-40 h-auto opacity-90" alt="{{ $make['name'] }}">
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

    {{-- CATEGORIAS COM GUIAS DISPONÍVEIS --}}
    <section id="categorias" class="mb-12">
        <h2 class="text-xl font-semibold mb-4 font-montserrat">
            Guias disponíveis por categoria
        </h2>
        <p class="text-sm text-gray-600 mb-4 font-roboto">
            Selecione uma categoria para acessar os guias técnicos do {{ $model['name'] }}
        </p>

        @if(count($categoriesWithGuides) > 0)
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
            @foreach($categoriesWithGuides as $category)
            <a href="{{ $category['url'] }}"
                class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-lg hover:border-blue-500 transition-all">
                <div class="flex items-center gap-3">
                    <div class="p-3 rounded-full {{ $category['icon_bg_color'] }}">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="w-8 h-8 {{ $category['icon_text_color'] }}" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            {!! $category['icon_svg'] !!}
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="font-semibold text-sm font-montserrat">{{ $category['name'] }}</div>
                        <div class="text-xs text-gray-500 font-roboto">{{ $category['guides_count'] }} guias</div>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        @else
        <div class="bg-white border border-gray-200 rounded-lg p-6 text-center">
            <p class="text-gray-600 font-roboto">
                Nenhum guia disponível no momento para este modelo.
            </p>
        </div>
        @endif
    </section>

    {{-- TODAS AS CATEGORIAS (DISPONÍVEIS + NÃO DISPONÍVEIS) --}}
    @if(count($allCategories) > 0)
    <section id="todas-categorias" class="mb-12">
        <h2 class="text-xl font-semibold mb-4 font-montserrat">
            Todas as categorias de guias
        </h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
            @foreach($allCategories as $category)
            <a href="{{ $category['url'] }}"
                class="block bg-{{ $category['has_guides'] ? 'white' : 'gray-50' }} border border-gray-200 rounded-lg p-3 hover:shadow hover:border-blue-500 transition-all {{ $category['has_guides'] ? '' : 'opacity-50' }}">
                <div class="text-sm font-semibold font-montserrat">{{ $category['name'] }}</div>
                @if($category['has_guides'])
                <div class="text-xs text-blue-600 font-roboto mt-1">{{ $category['guides_count'] }} disponíveis</div>
                @else
                <div class="text-xs text-gray-400 font-roboto mt-1">Em breve</div>
                @endif
            </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- ANOS DISPONÍVEIS --}}
    @if(count($yearsList) > 0)
    <section id="anos" class="mb-12">
        <h2 class="text-xl font-semibold mb-4 font-montserrat">Anos disponíveis</h2>
        <p class="text-sm text-gray-600 mb-4 font-roboto">
            Guias organizados por ano de fabricação
        </p>

        <div class="flex flex-wrap gap-2 text-sm font-roboto">
            @foreach($yearsList as $yearItem)
            <a href="{{ $yearItem['url'] }}"
                class="px-4 py-2 {{ $yearItem['guides_count'] > 0 ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-gray-200 text-gray-500' }} rounded-lg transition-colors">
                {{ $yearItem['year'] }}
                @if($yearItem['guides_count'] > 0)
                <span class="text-xs opacity-75">({{ $yearItem['guides_count'] }})</span>
                @endif
            </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- BANNER RESPONSIVO --}}
    <div class="my-12">
        <div class="w-full bg-gray-300 rounded-lg flex items-center justify-center" style="min-height: 280px;">
            <span class="text-gray-700 text-sm font-roboto">Banner - Mock Ad</span>
        </div>
    </div>

    {{-- MODELOS RELACIONADOS --}}
    @if(count($relatedModels) > 0)
    <section id="modelos-relacionados" class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
        <h2 class="text-xl font-bold mb-4 font-montserrat">
            Outros modelos {{ $make['name'] }}
        </h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
            @foreach($relatedModels as $relatedModel)
            <a href="{{ $relatedModel['url'] }}"
                class="block bg-gray-50 border border-gray-200 rounded-lg p-4 hover:shadow hover:border-blue-500 transition-all">
                <div class="font-semibold text-sm font-montserrat">{{ $relatedModel['name'] }}</div>
                <div class="text-xs text-gray-500 mt-1 font-roboto">
                    {{ $relatedModel['guides_count'] }} guias
                </div>
            </a>
            @endforeach
        </div>
    </section>
    @endif

</div>

{{-- SOBRE O MODELO --}}
<div class="container mx-auto px-4 sm:px-6 lg:px-8 pb-10">
    <section class="bg-gray-50 border border-gray-200 rounded-lg p-6">
        <h2 class="text-lg font-semibold mb-3 font-montserrat">
            Sobre os guias do {{ $make['name'] }} {{ $model['name'] }}
        </h2>
        <div class="text-sm text-gray-700 font-roboto space-y-2">
            <p>
                Encontre todas as especificações técnicas para o {{ $make['name'] }} {{ $model['name'] }}.
                Nossos guias cobrem informações essenciais como tipo de óleo recomendado,
                calibragem de pneus, consumo médio, revisões programadas e muito mais.
            </p>
            <p>
                Todos os dados são baseados em manuais oficiais e especificações dos fabricantes,
                garantindo informações precisas para manutenção e uso correto do seu veículo.
            </p>
        </div>
    </section>
</div>

@endsection