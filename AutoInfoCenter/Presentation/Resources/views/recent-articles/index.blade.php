@extends('auto-info-center::layouts.app')

@push('head')
<link rel="canonical" href="{{ route('info.recent-articles') }}" />
@endpush

@section('content')

<!-- Breadcrumbs -->
@include('auto-info-center::partials.breadcrumb-recent', [
    'isRecentArticles' => true
])

<!-- Conteúdo Principal -->
<main id="main-content" class="container mx-auto px-4 py-6">
    <div itemscope itemtype="https://schema.org/CollectionPage">
        <meta itemprop="author" content="Mercado Veículos">
        <meta itemprop="datePublished" content="{{ now()->utc()->toAtomString() }}">
        <meta itemprop="dateModified" content="{{ now()->utc()->toAtomString() }}">

        <header class="mb-6">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-3 font-montserrat" itemprop="headline">
                Últimos Artigos Publicados
            </h1>
            <p class="text-base md:text-lg text-gray-600 max-w-4xl font-roboto" itemprop="description">
                Confira os artigos mais recentes sobre manutenção automotiva, especificações técnicas, dicas práticas e guias completos para todos os modelos de veículos. Conteúdo atualizado regularmente para mantê-lo informado.
            </p>
        </header>

        <!-- INSERIR BANNER AQUI - POSIÇÃO 1 -->
        <div class="container mx-auto px-4 md:px-0 pt-0 py-6">
            [ADSENSE-1]
        </div>

        <!-- Lista de Artigos -->
        @include('auto-info-center::partials.articles-list', [
            'articles' => $articles,
            'pagination' => $pagination,
            'showCategory' => true,
            'emptyMessage' => 'Ainda não temos artigos publicados.'
        ])

        @include('auto-info-center::partials.info-section')

    </div>
</main>
@endsection
