{{--
View: Guide Show
Exibe um guia completo com SEO, conteúdo e clusters relacionados
--}}
@extends('shared::layouts.app')


@section('title', $guide->seo?->title ?? $guide->title)

@section('meta')
@if($guide->seo)
{!! $guide->seo->getMetaTags() !!}
{!! $guide->seo->getSchemaJsonLd() !!}
@else
<meta name="description" content="{{ $guide->title }}">
@endif

{{-- Schema.org Article --}}
<script type="application/ld+json">
    {!! json_encode($guide->getStructuredData(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endsection

@section('content')
<article class="guide-article" itemscope itemtype="https://schema.org/Article">
    {{-- Breadcrumb --}}
    <nav class="breadcrumb" aria-label="Navegação">
        <ol class="breadcrumb-list" itemscope itemtype="https://schema.org/BreadcrumbList">
            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <a href="{{ url('/') }}" itemprop="item">
                    <span itemprop="name">Home</span>
                </a>
                <meta itemprop="position" content="1">
            </li>
            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <a href="{{ route('guide.index') }}" itemprop="item">
                    <span itemprop="name">Guias</span>
                </a>
                <meta itemprop="position" content="2">
            </li>
            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <a href="{{ route('guide.byModel', ['make' => $guide->makeSlug, 'model' => $guide->modelSlug]) }}"
                    itemprop="item">
                    <span itemprop="name">{{ $guide->make }} {{ $guide->model }}</span>
                </a>
                <meta itemprop="position" content="3">
            </li>
            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" aria-current="page">
                <span itemprop="name">{{ $guide->title }}</span>
                <meta itemprop="position" content="4">
            </li>
        </ol>
    </nav>

    {{-- Header --}}
    <header class="guide-header">
        <h1 class="guide-title" itemprop="headline">
            {{ $guide->seo?->h1 ?? $guide->title }}
        </h1>

        <div class="guide-meta">
            <span class="guide-vehicle">
                <strong>Veículo:</strong>
                <a href="{{ route('guide.byModel', ['make' => $guide->makeSlug, 'model' => $guide->modelSlug]) }}">
                    {{ $guide->make }} {{ $guide->model }}
                </a>
                @if($guide->version)
                {{ $guide->version }}
                @endif
            </span>

            @if($guide->yearRange)
            <span class="guide-years">
                <strong>Anos:</strong> {{ $guide->yearRange }}
            </span>
            @endif

            <span class="guide-date">
                <strong>Publicado:</strong>
                <time datetime="{{ $guide->createdAt }}" itemprop="datePublished">
                    {{ \Carbon\Carbon::parse($guide->createdAt)->format('d/m/Y') }}
                </time>
            </span>
        </div>
    </header>

    {{-- Featured Image --}}
    @if($guide->getFeaturedImage())
    <figure class="guide-featured-image">
        <img src="{{ $guide->getFeaturedImage() }}" alt="{{ $guide->title }}" itemprop="image" loading="lazy">
    </figure>
    @endif

    {{-- Content --}}
    <div class="guide-content" itemprop="articleBody">
        @if($guide->getContent())
        {!! $guide->getContent() !!}
        @endif

        {{-- Sections --}}
        @if($sections = $guide->getSections())
        @foreach($sections as $section)
        <section class="guide-section">
            @if(isset($section['title']))
            <h2>{{ $section['title'] }}</h2>
            @endif

            @if(isset($section['content']))
            {!! $section['content'] !!}
            @endif
        </section>
        @endforeach
        @endif
    </div>

    {{-- FAQs --}}
    @if($faqs = $guide->getFaqs())
    <section class="guide-faqs" itemscope itemtype="https://schema.org/FAQPage">
        <h2>Perguntas Frequentes</h2>

        <div class="faq-list">
            @foreach($faqs as $faq)
            <div class="faq-item" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
                <h3 class="faq-question" itemprop="name">
                    {{ $faq['question'] ?? '' }}
                </h3>
                <div class="faq-answer" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                    <div itemprop="text">
                        {!! $faq['answer'] ?? '' !!}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- Clusters / Related Guides --}}
    @if($guide->clusters->isNotEmpty())
    <aside class="guide-related">
        <h2>Guias Relacionados</h2>

        @foreach($guide->clusters->groupBy('clusterType') as $type => $typeClusters)
        <div class="cluster-group">
            <h3>{{ $typeClusters->first()->getClusterTypeLabel() }}</h3>

            <ul class="related-links">
                @foreach($typeClusters as $cluster)
                @foreach($cluster->getFormattedLinks() as $link)
                <li>
                    <a href="{{ $link['url'] }}">
                        {{ $link['anchor'] }}
                    </a>
                </li>
                @endforeach
                @endforeach
            </ul>
        </div>
        @endforeach
    </aside>
    @endif
</article>

{{-- Navigation --}}
<nav class="guide-navigation">
    <a href="{{ route('guide.byModel', ['make' => $guide->makeSlug, 'model' => $guide->modelSlug]) }}"
        class="btn btn-outline">
        ← Ver todos os guias para {{ $guide->make }} {{ $guide->model }}
    </a>

    <a href="{{ route('guide.cluster', ['make' => $guide->makeSlug, 'model' => $guide->modelSlug]) }}"
        class="btn btn-outline">
        Ver cluster completo →
    </a>
</nav>
@endsection