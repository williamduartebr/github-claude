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
        <h1 class="text-3xl font-bold text-gray-900 mb-3 font-montserrat">
            {{ $category['name'] }} – {{ $make['name'] }} {{ $model['name'] }} {{ $year }}
        </h1>
        <p class="text-sm text-gray-600 font-roboto">
            Escolha a versão do seu {{ $make['name'] }} {{ $model['name'] }} {{ $year }} para ver o guia completo de {{
            strtolower($category['name']) }}
            com especificações, recomendações e informações técnicas.
        </p>

        @if($stats['total_versions'] > 0)
        <div class="mt-4 text-sm text-gray-500 font-roboto">
            🚗 {{ $stats['total_versions'] }} {{ $stats['total_versions'] === 1 ? 'versão disponível' : 'versões
            disponíveis' }}
        </div>
        @endif
    </div>
</section>

{{-- BANNER RESPONSIVO (MOCK) --}}
<div class="container mx-auto px-4 my-6">
    <div class="w-full bg-gray-300 rounded-lg flex items-center justify-center" style="min-height: 280px;">
        <span class="text-gray-700 text-sm font-roboto">Banner - Mock Ad</span>
    </div>
</div>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- LISTA DE VERSÕES DISPONÍVEIS --}}
    @if(count($availableVersions) > 0)
    <section class="mb-16">
        <h2 class="text-xl font-semibold mb-4 font-montserrat">
            Versões disponíveis – {{ $make['name'] }} {{ $model['name'] }} {{ $year }}
        </h2>

        <p class="text-sm text-gray-600 mb-4 font-roboto">
            Escolha a versão do seu veículo para ver o guia completo.
        </p>

        <div class="overflow-x-auto bg-white border border-gray-200 rounded-lg shadow-sm">
            <table class="min-w-full text-sm font-roboto">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-montserrat border-b border-gray-200">Versão</th>
                        <th class="px-4 py-2 text-left font-montserrat border-b border-gray-200">Motor</th>
                        <th class="px-4 py-2 text-left font-montserrat border-b border-gray-200">Guia</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($availableVersions as $index => $versionData)
                    <tr class="border-t border-gray-200 {{ $index % 2 === 1 ? 'bg-gray-50' : '' }}">
                        <td class="px-4 py-3">
                            <strong>{{ $versionData['version'] }}</strong>
                        </td>
                        <td class="px-4 py-3">{{ $versionData['engine'] }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ $versionData['url'] }}" class="text-blue-600 hover:underline font-medium">
                                Ver guia →
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
    @else
    {{-- NENHUMA VERSÃO DISPONÍVEL --}}
    <section class="mb-16">
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <div class="flex items-center">
                <svg class="h-6 w-6 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    <h3 class="text-lg font-semibold text-yellow-900 font-montserrat">
                        Nenhuma versão disponível no momento
                    </h3>
                    <p class="text-sm text-yellow-800 mt-1 font-roboto">
                        Ainda não temos guias para este ano. Estamos trabalhando para adicionar este conteúdo em breve.
                    </p>
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- BANNER RESPONSIVO (MOCK) --}}
    <div class="my-6">
        <div class="w-full bg-gray-300 rounded-lg flex items-center justify-center" style="min-height: 280px;">
            <span class="text-gray-700 text-sm font-roboto">Banner - Mock Ad</span>
        </div>
    </div>

    {{-- LINKS PARA OUTRAS CATEGORIAS --}}
    <section class="bg-white border border-gray-200 rounded-lg p-6 mb-16">
        <h2 class="text-xl font-semibold mb-4 font-montserrat">
            Outras categorias – {{ $make['name'] }} {{ $model['name'] }} {{ $year }}
        </h2>

        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3 text-sm font-roboto">
            @foreach($complementaryCategories as $cat)
            <a href="/guias/{{ $cat['slug'] }}/{{ $make['slug'] }}/{{ $model['slug'] }}/{{ $year }}"
                class="block bg-gray-50 border border-gray-200 rounded p-3 hover:shadow hover:border-blue-500 transition-all text-center">
                {{ $cat['name'] }}
            </a>
            @endforeach
        </div>
    </section>

</div>

@endsection