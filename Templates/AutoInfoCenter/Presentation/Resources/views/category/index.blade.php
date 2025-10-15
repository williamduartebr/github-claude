@extends('auto-info-center::layouts.app')

@push('head')
<link rel="canonical" href="{{ route('info.category.index') }}" />

@php
    // Construir lista de categorias para o Schema
    $categoryItems = [];
    $position = 1;
    
    foreach($categories as $category) {
        if($category->to_follow === true) {
            $categoryItems[] = [
                '@type' => 'ListItem',
                'position' => $position,
                'name' => $category->name,
                'url' => route('info.category.show', $category->slug)
            ];
            $position++;
        }
    }

    $webPageSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        'name' => 'Informações Automotivas - Guia Completo | Mercado Veículos',
        'description' => 'Encontre informações detalhadas sobre manutenção automotiva, calibragem de pneus, óleos recomendados e muito mais para todos os modelos de veículos.',
        'url' => route('info.category.index'),
        'isPartOf' => [
            '@type' => 'WebSite',
            'name' => 'Mercado Veículos',
            'url' => 'https://mercadoveiculos.com'
        ],
        'speakable' => [
            '@type' => 'SpeakableSpecification',
            'cssSelector' => ['h1', 'h2']
        ],
        'mainEntity' => [
            '@type' => 'ItemList',
            'itemListElement' => $categoryItems
        ],
        'breadcrumb' => [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Início',
                    'item' => url('/')
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'Informações',
                    'item' => route('info.category.index')
                ]
            ]
        ]
    ];
@endphp

<!-- Structured Data - WebPage -->
<script type="application/ld+json">
{!! json_encode($webPageSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('content')
<!-- Breadcrumbs -->
@include('auto-info-center::category.partials.breadcrumb')

<!-- Conteúdo Principal -->
<main id="main-content" class="container mx-auto px-4 py-8 mb-8" itemscope itemtype="https://schema.org/CollectionPage">
    <header class="mb-8 text-center md:text-left">
        <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-3 font-montserrat" itemprop="headline">Centro de
            Informações Automotivas</h1>
        <p class="text-base md:text-lg text-gray-600 max-w-4xl mx-auto md:mx-0 font-roboto" itemprop="description">
            Encontre informações detalhadas sobre manutenção, especificações e cuidados para o seu veículo. Nossos
            guias são elaborados com base nas recomendações dos fabricantes e experiência de especialistas.</p>
    </header>

    <section class="container mx-auto">
        [ADSENSE-1]
    </section>

    <!-- Grade de Categorias -->
    @include('auto-info-center::partials.categories', ['categories' => $categories])

</main>
@endsection