@extends('shared::layouts.app')

{{-- ✅ STRUCTURED DATA (Schema.org) --}}
@if(!empty($structured_data))
@push('head')
<script type="application/ld+json">
{!! json_encode($structured_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush
@endif

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
                    @if($index < count($breadcrumbs) - 1) <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-3 w-3 mx-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

@section('content')
{{-- HERO SECTION --}}
<section class="hero-bg border-b border-gray-200"
    style="background-image: linear-gradient(90deg, rgba(0,0,0,0.05), rgba(0,0,0,0.02));">
    <div class="container mx-auto px-4 py-12">
        <div class="md:flex md:items-center md:justify-between gap-8">

            {{-- INFORMAÇÕES PRINCIPAIS --}}
            <div class="max-w-3xl">
                {{-- TÍTULO PRINCIPAL --}}
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3 font-montserrat">
                    Guias Técnicos {{ $model['full_name'] }}
                </h1>

                {{-- SUBTÍTULO --}}
                <p class="text-base text-gray-700 mb-4 font-roboto">
                    {{ $model['description'] }}
                </p>

                {{-- BADGE ANO --}}
                <div class="flex items-center gap-3 mb-6">
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800 font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        {{ $stats['years_range'] }}
                    </span>

                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-green-100 text-green-800 font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        {{ $stats['total_guides'] }} guias
                    </span>

                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-purple-100 text-purple-800 font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        {{ $model['category'] }}
                    </span>
                </div>

                {{-- ESTATÍSTICAS QUICK --}}
                <div class="grid grid-cols-3 gap-4 mt-6 max-w-md">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600 font-montserrat">{{ $stats['total_guides'] }}</div>
                        <div class="text-xs text-gray-600 font-roboto">Guias técnicos</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600 font-montserrat">{{
                            $stats['categories_with_guides'] }}</div>
                        <div class="text-xs text-gray-600 font-roboto">Categorias</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600 font-montserrat">{{ $stats['total_versions'] }}
                        </div>
                        <div class="text-xs text-gray-600 font-roboto">Versões</div>
                    </div>
                </div>
            </div>

            {{-- LOGO DA MARCA --}}
            <div class="mt-6 md:mt-0">
                <img src="{{ $make['logo'] }}" class="w-40 h-auto opacity-90" alt="Logo {{ $make['name'] }}">
            </div>
        </div>
    </div>
</section>

{{-- BANNER --}}
<div class="container mx-auto px-4 sm:px-6 lg:px-8 my-12">
    <div class="w-full bg-gray-300 rounded-lg flex items-center justify-center" style="min-height: 280px;">
        <span class="text-gray-700 text-sm font-roboto">Banner - Mock Ad</span>
    </div>
</div>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 pt-10">

    {{-- CATEGORIAS DE GUIAS DISPONÍVEIS --}}
    <section class="mb-12">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-semibold font-montserrat">Guias Disponíveis por Categoria</h2>
            <span class="text-sm text-gray-500 font-roboto">{{ $stats['categories_with_guides'] }} de {{
                $stats['total_categories'] }} categorias</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            @foreach($categoriesWithGuides as $category)
            <a href="{{ $category['url'] }}"
                class="bg-white border-2 rounded-lg p-4 hover:shadow-lg transition-all duration-300
                       {{ $category['has_guides'] ? 'border-blue-200 hover:border-blue-500' : 'border-gray-200 opacity-60' }}">
                <div class="flex flex-col items-center text-center">
                    {{-- ÍCONE DA CATEGORIA --}}
                    <div class="p-3 rounded-full {{ $category['icon_bg_color'] }} mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 {{ $category['icon_text_color'] }}"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            {!! $category['icon_svg'] !!}
                        </svg>
                    </div>

                    {{-- NOME DA CATEGORIA --}}
                    <h3 class="text-sm font-medium text-gray-800 font-roboto mb-1">{{ $category['name'] }}</h3>

                    {{-- CONTADOR DE GUIAS --}}
                    @if($category['has_guides'])
                    <div class="flex items-center gap-1 text-xs text-blue-600 font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>{{ $category['guides_count'] }}</span>
                    </div>

                    @if($category['latest_year'])
                    <div class="text-xs text-gray-500 mt-1">Até {{ $category['latest_year'] }}</div>
                    @endif
                    @else
                    <div class="text-xs text-gray-400">Em breve</div>
                    @endif
                </div>
            </a>
            @endforeach
        </div>
    </section>

    {{-- ANOS DISPONÍVEIS --}}
    @if(count($yearsList) > 0)
    <section class="mb-12">
        <h2 class="text-2xl font-semibold mb-6 font-montserrat">Guias por Ano</h2>
        <p class="text-sm text-gray-600 mb-4 font-roboto">
            Selecione o ano do seu {{ $model['full_name'] }} para ver guias específicos e versões disponíveis.
        </p>

        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-3">
            @foreach($yearsList as $yearData)
            <a href="{{ $yearData['url'] }}"
                class="bg-white border-2 rounded-lg p-4 text-center hover:shadow-md transition-all duration-300
                       {{ $yearData['has_guides'] ? 'border-blue-200 hover:border-blue-500' : 'border-gray-200 opacity-50' }}">
                <div class="text-xl font-bold text-gray-800 font-montserrat">{{ $yearData['year'] }}</div>
                @if($yearData['has_guides'])
                <div class="text-xs text-blue-600 font-medium mt-1">{{ $yearData['guides_count'] }} guias</div>
                @else
                <div class="text-xs text-gray-400 mt-1">—</div>
                @endif
            </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- NAVEGAÇÃO RÁPIDA --}}
    <section class="mb-12 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4 font-montserrat text-blue-900">
            Navegação Rápida
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- VER TODOS OS GUIAS DA MARCA --}}
            <a href="{{ $make['url'] }}"
                class="flex items-center gap-3 p-4 bg-white rounded-lg hover:shadow-md transition-shadow">
                <div class="p-2 rounded-full bg-blue-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <div>
                    <div class="font-medium text-gray-900">Todos os modelos {{ $make['name'] }}</div>
                    <div class="text-xs text-gray-500">Ver outros guias da marca</div>
                </div>
            </a>

            {{-- VER FICHA TÉCNICA COMPLETA --}}
            <a href="{{ route('vehicles.model', ['make' => $make['slug'], 'model' => $model['slug']]) }}"
                class="flex items-center gap-3 p-4 bg-white rounded-lg hover:shadow-md transition-shadow">
                <div class="p-2 rounded-full bg-green-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                </div>
                <div>
                    <div class="font-medium text-gray-900">Ficha Técnica Completa</div>
                    <div class="text-xs text-gray-500">Especificações detalhadas do veículo</div>
                </div>
            </a>
        </div>
    </section>

    {{-- MODELOS RELACIONADOS --}}
    @if(count($relatedModels) > 0)
    <section class="mb-12">
        <h2 class="text-2xl font-semibold mb-6 font-montserrat">Outros Modelos {{ $make['name'] }}</h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
            @foreach($relatedModels as $related)
            <a href="{{ $related['url'] }}"
                class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <h3 class="font-medium text-gray-900 font-roboto">{{ $related['name'] }}</h3>
                <p class="text-xs text-gray-500 mt-1">{{ $related['guides_count'] }} guias</p>
            </a>
            @endforeach
        </div>
    </section>
    @endif

</div>

{{-- BANNER FINAL --}}
<div class="container mx-auto px-4 sm:px-6 lg:px-8 mb-16">
    <div class="w-full bg-gray-300 rounded-lg flex items-center justify-center" style="min-height: 280px;">
        <span class="text-gray-700 text-sm font-roboto">Banner - Mock Ad</span>
    </div>
</div>

@endsection