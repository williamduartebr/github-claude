@extends('auto-info-center::layouts.app')

@push('head')
<link rel="canonical" href="{{ route('info.category.show', $category->slug) }}" />
@endpush

@section('content')

<!-- Breadcrumbs -->
@include('auto-info-center::category.partials.breadcrumb', [
'category' => $category,
'isAllModels' => false
])

<!-- Conteúdo Principal -->
<main id="main-content" class="container mx-auto px-4 py-6">
    <div itemscope itemtype="https://schema.org/CollectionPage">
        <meta itemprop="author" content="Mercado Veículos">
        <meta itemprop="datePublished" content="{{ $category->created_at->utc()->toAtomString() }}">
        <meta itemprop="dateModified" content="{{ $category->updated_at->utc()->toAtomString() }}">

        <header class="mb-6">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-3 font-montserrat" itemprop="headline">
                {{ $category->name }}
            </h1>
            <p class="text-base md:text-lg text-gray-600 max-w-4xl font-roboto" itemprop="description">
                {{ $category->description }}
            </p>
        </header>

        <!-- Filtros -->
        <div class="bg-white p-4 rounded-lg shadow-md mb-6">
            <h2 id="filtros-heading" class="text-lg font-semibold mb-3 font-montserrat">Filtrar por:</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" role="search"
                aria-labelledby="filtros-heading">
                <div>
                    <label for="marca" class="block text-sm font-medium text-gray-700 mb-1 font-roboto">Marca</label>
                    <select id="marca"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm font-roboto focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todas as marcas</option>
                        <option value="renault" {{ request('marca')=='renault' ? 'selected' : '' }}>Renault</option>
                        <option value="toyota" {{ request('marca')=='toyota' ? 'selected' : '' }}>Toyota</option>
                        <option value="volkswagen" {{ request('marca')=='volkswagen' ? 'selected' : '' }}>Volkswagen
                        </option>
                        <option value="fiat" {{ request('marca')=='fiat' ? 'selected' : '' }}>Fiat</option>
                        <option value="chevrolet" {{ request('marca')=='chevrolet' ? 'selected' : '' }}>Chevrolet
                        </option>
                        <option value="honda" {{ request('marca')=='honda' ? 'selected' : '' }}>Honda</option>
                        <option value="hyundai" {{ request('marca')=='hyundai' ? 'selected' : '' }}>Hyundai</option>
                        <option value="kia" {{ request('marca')=='kia' ? 'selected' : '' }}>Kia</option>
                        <option value="ram" {{ request('marca')=='ram' ? 'selected' : '' }}>RAM</option>
                        <option value="suzuki" {{ request('marca')=='suzuki' ? 'selected' : '' }}>Suzuki</option>
                    </select>
                </div>
                <div>
                    <label for="modelo" class="block text-sm font-medium text-gray-700 mb-1 font-roboto">Modelo</label>
                    <select id="modelo"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm font-roboto focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos os modelos</option>
                        <!-- Modelos serão carregados com JavaScript dependendo da marca selecionada -->
                    </select>
                </div>
                <div>
                    <label for="ano" class="block text-sm font-medium text-gray-700 mb-1 font-roboto">Ano</label>
                    <select id="ano"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm font-roboto focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos os anos</option>
                        <option value="2025" {{ request('ano')=='2025' ? 'selected' : '' }}>2025</option>
                        <option value="2024" {{ request('ano')=='2024' ? 'selected' : '' }}>2024</option>
                        <option value="2023" {{ request('ano')=='2023' ? 'selected' : '' }}>2023</option>
                        <option value="2022" {{ request('ano')=='2022' ? 'selected' : '' }}>2022</option>
                        <option value="2021" {{ request('ano')=='2021' ? 'selected' : '' }}>2021</option>
                        <option value="2020" {{ request('ano')=='2020' ? 'selected' : '' }}>2020</option>
                        <option value="2019" {{ request('ano')=='2019' ? 'selected' : '' }}>2019</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" id="aplicar-filtros"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-md transition-colors w-full text-sm font-montserrat focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Aplicar Filtros
                    </button>
                </div>
            </div>

            <!-- Filtros ativos -->
            @if(!empty($filters))
            <div class="mt-4 flex flex-wrap gap-2">
                <span class="text-sm text-gray-600 font-medium">Filtros ativos:</span>
                @foreach($filters as $key => $value)
                <span
                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ ucfirst($key) }}: {{ ucfirst($value) }}
                    <a href="{{ request()->fullUrlWithoutQuery($key) }}" class="ml-1 text-blue-600 hover:text-blue-800">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </a>
                </span>
                @endforeach
                <a href="{{ route('info.category.show', $category->slug) }}"
                    class="text-sm text-blue-600 hover:text-blue-800 underline">
                    Limpar todos
                </a>
            </div>
            @endif
        </div>

        <!-- INSERIR BANNER AQUI - POSIÇÃO 1 -->
        <div class="container mx-auto px-4 md:px-0 pt-0 py-6">
            [ADSENSE-1]
        </div>

        <!-- Lista de Artigos -->
        @include('auto-info-center::partials.articles-list')

        @include('auto-info-center::partials.info-section')

    </div>
</main>
@endsection

@include('auto-info-center::partials.category-scripts')