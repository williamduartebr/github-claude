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
                    Guias {{ $make['name'] }}
                </h1>
                <p class="text-sm text-gray-600 font-roboto">
                    Especificações técnicas completas para veículos {{ $make['name'] }}. 
                    Selecione uma categoria para acessar guias detalhados de óleo, calibragem, pneus, 
                    consumo e muito mais. Informações baseadas em manuais oficiais dos fabricantes.
                </p>

                {{-- ESTATÍSTICAS --}}
                <div class="grid grid-cols-3 gap-4 mt-6">
                    <div>
                        <div class="text-2xl font-bold text-blue-600 font-montserrat">{{ $stats['total_guides'] }}</div>
                        <div class="text-xs text-gray-600 font-roboto">Guias disponíveis</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-blue-600 font-montserrat">{{ $stats['total_models'] }}</div>
                        <div class="text-xs text-gray-600 font-roboto">Modelos</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-blue-600 font-montserrat">{{ $stats['total_categories'] }}</div>
                        <div class="text-xs text-gray-600 font-roboto">Categorias</div>
                    </div>
                </div>
            </div>

            <div class="mt-6 md:mt-0">
                <img src="{{ $make['logo'] }}" class="w-40 h-auto opacity-90" alt="{{ $make['name'] }}">
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

    {{-- CATEGORIAS DISPONÍVEIS --}}
    <section class="mb-12">
        <h2 class="text-xl font-semibold mb-4 font-montserrat">Categorias de Guias {{ $make['name'] }}</h2>
        <p class="text-sm text-gray-600 mb-4 font-roboto">
            Selecione uma categoria para ver os guias de {{ $make['name'] }}
        </p>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
            @foreach($categories as $category)
            <a href="{{ $category['url'] }}"
                class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-lg hover:border-blue-500 transition-all">
                <div class="flex items-center gap-3">
                    <div class="p-3 rounded-full {{ $category['icon_bg_color'] ?? 'bg-blue-50' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" 
                             class="w-8 h-8 {{ $category['icon_text_color'] ?? 'text-blue-600' }}" 
                             fill="none" 
                             viewBox="0 0 24 24" 
                             stroke="currentColor" 
                             aria-hidden="true">
                            {!! $category['icon_svg'] ?? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />' !!}
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
    </section>


    {{-- MODELOS POPULARES --}}
    @if(count($popularModels) > 0)
    <section class="mb-12">
        <h2 class="text-xl font-semibold mb-4 font-montserrat">Modelos Populares {{ $make['name'] }}</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach($popularModels as $model)
            <a href="{{ $model['url'] }}"
                class="block bg-white border border-gray-200 rounded-lg p-4 hover:shadow hover:border-blue-500 transition-all text-center">
                <div class="font-semibold text-sm font-montserrat">{{ $model['name'] }}</div>
                <div class="text-xs text-gray-500 mt-1 font-roboto">
                    {{ $model['guides_count'] }} guias
                </div>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- TODOS OS MODELOS (Tabela compacta) --}}
    @if(count($allModels) > 0)
    <section class="mb-6">
        <h2 class="text-xl font-semibold mb-4 font-montserrat">Todos os Modelos {{ $make['name'] }}</h2>
        <p class="text-sm text-gray-600 mb-4 font-roboto">
            Escolha o modelo para acessar os guias disponíveis.
        </p>

        <div class="overflow-x-auto bg-white border border-gray-200 rounded-lg shadow-sm">
            <table class="min-w-full text-sm font-roboto">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-montserrat border-b border-gray-200">Modelo</th>
                        <th class="px-4 py-2 text-center font-montserrat border-b border-gray-200">Guias</th>
                        <th class="px-4 py-2 text-right font-montserrat border-b border-gray-200"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allModels as $index => $model)
                    <tr class="border-t border-gray-200 {{ $index % 2 === 1 ? 'bg-gray-50' : '' }}">
                        <td class="px-4 py-3 font-medium">{{ $model['name'] }}</td>
                        <td class="px-4 py-3 text-center text-gray-600">{{ $model['guides_count'] }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ $model['url'] }}"
                                class="text-blue-600 hover:underline text-xs font-semibold">
                                Ver guias →
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
    @endif

</div>

{{-- CONTEÚDO SEO --}}
<div class="container mx-auto px-4 sm:px-6 lg:px-8 pb-10">
    <section class="bg-gray-50 border border-gray-200 rounded-lg p-6">
        <h2 class="text-lg font-semibold mb-3 font-montserrat">Sobre os Guias {{ $make['name'] }}</h2>
        <div class="text-sm text-gray-700 font-roboto space-y-2">
            <p>
                Encontre especificações técnicas completas para veículos {{ $make['name'] }}. 
                Nossos guias cobrem informações essenciais como tipo de óleo recomendado, 
                calibragem de pneus, consumo médio e muito mais.
            </p>
            <p>
                Todos os dados são baseados em manuais oficiais e especificações dos fabricantes, 
                garantindo informações precisas para manutenção e uso correto do seu veículo.
            </p>
        </div>
    </section>
</div>

@endsection
