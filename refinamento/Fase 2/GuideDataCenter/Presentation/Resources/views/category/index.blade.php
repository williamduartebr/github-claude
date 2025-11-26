@extends('auto-info-center::layouts.app')

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
    
    <style>
        .chip {
            padding: .375rem .75rem;
            border-radius: 9999px;
            font-size: .875rem;
        }
    </style>
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

{{-- HERO --}}
<section class="bg-white border-b border-gray-200">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="md:flex md:items-center md:justify-between gap-6">
            <div class="max-w-2xl">
                <h1 class="text-3xl font-bold text-gray-900 mb-3 font-montserrat">
                    Guia de {{ $category['name'] }}
                </h1>
                <p class="text-sm text-gray-600 font-roboto">
                    {{ $category['description'] }}
                </p>

                {{-- CHIPS DE CATEGORIAS RELACIONADAS --}}
                <div class="mt-4 flex gap-3 flex-wrap font-roboto">
                    <a href="{{ route('guide.category', ['category' => $category['slug']]) }}" 
                       class="chip bg-blue-600 text-white">
                        Categoria: {{ $category['name'] }}
                    </a>
                    @foreach($relatedCategories as $related)
                        <a href="{{ route('guide.category', ['category' => $related['slug']]) }}" 
                           class="chip bg-gray-100 hover:bg-gray-200">
                            {{ $related['name'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="mt-6 md:mt-0">
                <img src="{{ $heroImage }}" 
                     alt="{{ $category['name'] }}" 
                     class="w-64 rounded shadow">
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
    
    {{-- SEARCH / FILTER --}}
    <section class="bg-white border border-gray-200 rounded-lg p-4 mb-8">
        <form action="/guias/{{ $category['slug'] }}/search" method="get"
            class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
            <div class="md:col-span-2">
                <label class="text-xs text-gray-600 font-roboto">Marca</label>
                <select name="brand"
                    class="mt-1 block w-full border border-gray-200 rounded px-3 py-2 text-sm font-roboto focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todas as marcas</option>
                    @foreach($makes as $make)
                        <option value="{{ $make['slug'] }}">{{ $make['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="text-xs text-gray-600 font-roboto">Modelo</label>
                <input name="model" type="text" placeholder="Ex: Corolla, Civic"
                    class="mt-1 block w-full border border-gray-200 rounded px-3 py-2 text-sm font-roboto focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>

            <div class="md:col-span-1">
                <label class="text-xs text-gray-600 font-roboto">Ano</label>
                <input name="year" type="number" min="1900" max="2099" placeholder="2003"
                    class="mt-1 block w-full border border-gray-200 rounded px-3 py-2 text-sm font-roboto focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>

            <div class="md:col-span-1">
                <button type="submit"
                    class="w-full bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700 font-roboto">
                    Buscar
                </button>
            </div>
        </form>
        <p class="text-xs text-gray-500 mt-2 font-roboto">
            Dica: Pesquise por marca + modelo + ano para ver o guia completo.
        </p>
    </section>

    {{-- GUIAS POPULARES --}}
    @if(count($popularGuides) > 0)
        <section class="mb-8">
            <h2 class="text-lg font-semibold mb-3 font-montserrat">Guias populares</h2>

            {{-- PAGINAÇÃO (MOCK) --}}
            <div class="flex items-center justify-center gap-2 mt-8 mb-4 font-roboto">
                <button class="px-3 py-1 bg-gray-200 rounded text-sm hover:bg-gray-300">«</button>
                <button class="px-3 py-1 bg-blue-600 text-white rounded text-sm">1</button>
                <button class="px-3 py-1 bg-gray-200 rounded text-sm hover:bg-gray-300">2</button>
                <button class="px-3 py-1 bg-gray-200 rounded text-sm hover:bg-gray-300">3</button>
                <button class="px-3 py-1 bg-gray-200 rounded text-sm hover:bg-gray-300">»</button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach($popularGuides as $guide)
                    <a href="{{ $guide['url'] }}"
                        class="block bg-white border border-gray-200 rounded-lg p-4 hover:shadow hover:border-blue-500 transition-all font-roboto">
                        <div class="text-sm font-semibold font-montserrat">{{ $guide['title'] }}</div>
                        <div class="text-xs text-gray-500 mt-1">{{ $guide['specs'] }}</div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- MARCAS - GUIAS POR MARCA --}}
    <section class="mb-8 bg-white border border-gray-200 rounded-lg p-6">
        <h2 class="text-lg font-semibold mb-3 font-montserrat">Marcas – guias de {{ strtolower($category['name']) }}</h2>
        <p class="text-sm text-gray-600 mb-4 font-roboto">
            Escolha uma marca para ver a lista de modelos suportados e guias detalhados.
        </p>

        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3 font-roboto">
            @foreach($makes as $make)
                <a href="/guias/{{ $category['slug'] }}/{{ $make['slug'] }}"
                    class="block bg-gray-50 border border-gray-200 rounded p-3 text-center hover:shadow hover:border-blue-500 transition-all">
                    {{ $make['name'] }}
                </a>
            @endforeach
        </div>
    </section>

    {{-- CONTEÚDO EVERGREEN --}}
    @if($evergreenContent)
        <section class="mb-8">
            <div class="bg-white border border-gray-200 rounded-lg p-6 text-sm text-gray-700 font-roboto">
                <h2 class="text-lg font-semibold mb-3 font-montserrat">{{ $evergreenContent['title'] }}</h2>
                <p class="mb-2">{{ $evergreenContent['text'] }}</p>
                @if(isset($evergreenContent['note']))
                    <p class="text-xs text-gray-500">{{ $evergreenContent['note'] }}</p>
                @endif
            </div>
        </section>
    @endif

    {{-- FAQ --}}
    @if(count($faqs) > 0)
        <section class="mb-10 bg-white border border-gray-200 rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-3 font-montserrat">
                Perguntas frequentes ({{ $category['name'] }})
            </h2>

            <div class="space-y-3 text-sm font-roboto">
                @foreach($faqs as $faq)
                    <div>
                        <strong class="font-montserrat">{{ $faq['question'] }}</strong>
                        <p class="text-gray-600 mt-1">{{ $faq['answer'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- NOTA TÉCNICA --}}
    <section class="mb-6 text-xs text-gray-500 font-roboto">
        <p>Obs: esta página é a raiz da categoria {{ $category['name'] }}. As páginas dos guias são geradas por marca e modelo
        (ex.: <code>/guias/{{ $category['slug'] }}/toyota/corolla-2003</code>).</p>
    </section>

</div>

{{-- BANNER RESPONSIVO (MOCK) --}}
<div class="container mx-auto px-4 my-6">
    <div class="w-full bg-gray-300 rounded-lg flex items-center justify-center" style="min-height: 280px;">
        <span class="text-gray-700 text-sm font-roboto">Banner - Mock Ad</span>
    </div>
</div>

@endsection