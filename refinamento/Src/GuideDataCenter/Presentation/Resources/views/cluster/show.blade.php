{{-- 
    View: Cluster Show
    Exibe cluster de guias para um veículo específico
--}}
@extends('layouts.app')

@section('title', $title ?? 'Cluster de Guias')

@section('meta')
    <meta name="description" content="Encontre todos os guias relacionados para {{ $make ?? '' }} {{ $model ?? '' }}">
    <meta name="robots" content="index, follow">
@endsection

@section('content')
<div class="cluster-page">
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
            @if(isset($make) && isset($model))
                <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                    <a href="{{ route('guide.byModel', ['make' => $make, 'model' => $model]) }}" itemprop="item">
                        <span itemprop="name">{{ ucfirst(str_replace('-', ' ', $make)) }} {{ ucfirst(str_replace('-', ' ', $model)) }}</span>
                    </a>
                    <meta itemprop="position" content="3">
                </li>
            @endif
            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" aria-current="page">
                <span itemprop="name">Cluster</span>
                <meta itemprop="position" content="4">
            </li>
        </ol>
    </nav>

    {{-- Header --}}
    <header class="cluster-header">
        <h1>{{ $title ?? 'Cluster de Guias' }}</h1>
        
        @if(isset($make) && isset($model))
            <p class="cluster-subtitle">
                Todos os guias organizados por tipo para 
                <strong>{{ ucfirst(str_replace('-', ' ', $make)) }} {{ ucfirst(str_replace('-', ' ', $model)) }}</strong>
                @if(isset($year) && $year)
                    ({{ $year }})
                @endif
            </p>
        @endif
    </header>

    {{-- Clusters by Type --}}
    @if(isset($clusters) && $clusters->isNotEmpty())
        <div class="clusters-container">
            @foreach($clusters as $type => $typeClusters)
                <section class="cluster-section">
                    <h2 class="section-title">
                        {{ $typeClusters->first()->getClusterTypeLabel() ?? ucfirst($type) }}
                        <span class="count">({{ $typeClusters->count() }})</span>
                    </h2>
                    
                    <div class="cluster-grid">
                        @foreach($typeClusters as $cluster)
                            <div class="cluster-card">
                                <h3 class="card-title">
                                    {{ $cluster->getVehicleName() }}
                                </h3>
                                
                                @if($cluster->relatedGuides)
                                    <div class="card-stats">
                                        <span class="stat">
                                            {{ $cluster->getRelatedGuidesCount() }} guias relacionados
                                        </span>
                                        <span class="stat">
                                            {{ $cluster->getInternalLinksCount() }} links internos
                                        </span>
                                    </div>
                                @endif

                                {{-- Internal Links --}}
                                @if($formattedLinks = $cluster->getFormattedLinks())
                                    <ul class="internal-links">
                                        @foreach(array_slice($formattedLinks, 0, 5) as $link)
                                            <li>
                                                <a href="{{ $link['url'] }}" title="{{ $link['title'] }}">
                                                    {{ $link['anchor'] }}
                                                </a>
                                            </li>
                                        @endforeach
                                        
                                        @if(count($formattedLinks) > 5)
                                            <li class="more-links">
                                                <a href="{{ $cluster->url }}">
                                                    +{{ count($formattedLinks) - 5 }} mais...
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                @endif

                                <a href="{{ $cluster->url }}" class="card-link">
                                    Ver cluster completo →
                                </a>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <h2>Nenhum cluster encontrado</h2>
            <p>Não há clusters de guias disponíveis para este veículo.</p>
        </div>
    @endif

    {{-- Related Guides --}}
    @if(isset($guides) && $guides->isNotEmpty())
        <section class="related-guides">
            <h2>Guias Relacionados</h2>
            
            <div class="guide-grid">
                @foreach($guides as $guide)
                    <article class="guide-card">
                        @if($guide->featuredImage)
                            <figure class="card-image">
                                <a href="{{ $guide->url }}">
                                    <img 
                                        src="{{ $guide->featuredImage }}" 
                                        alt="{{ $guide->title }}"
                                        loading="lazy"
                                    >
                                </a>
                            </figure>
                        @endif

                        <div class="card-content">
                            <h3 class="card-title">
                                <a href="{{ $guide->url }}">
                                    {{ $guide->title }}
                                </a>
                            </h3>

                            <div class="card-meta">
                                <span class="meta-vehicle">
                                    {{ $guide->getVehicleName() }}
                                </span>
                            </div>

                            @if($guide->excerpt)
                                <p class="card-excerpt">
                                    {{ $guide->excerpt }}
                                </p>
                            @endif

                            <a href="{{ $guide->url }}" class="card-link">
                                Ler guia →
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Navigation --}}
    <nav class="cluster-navigation">
        @if(isset($make) && isset($model))
            <a href="{{ route('guide.byModel', ['make' => $make, 'model' => $model]) }}" class="btn btn-outline">
                ← Ver listagem de guias
            </a>
        @endif
        
        <a href="{{ route('guide.index') }}" class="btn btn-primary">
            Ver todos os guias
        </a>
    </nav>
</div>

{{-- Schema.org --}}
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "CollectionPage",
    "name": "{{ $title ?? 'Cluster de Guias' }}",
    "description": "Cluster de guias organizados por tipo para {{ $make ?? '' }} {{ $model ?? '' }}",
    "url": "{{ url()->current() }}",
    @if(isset($guides) && $guides->isNotEmpty())
    "mainEntity": {
        "@type": "ItemList",
        "numberOfItems": {{ $guides->count() }},
        "itemListElement": [
            @foreach($guides as $index => $guide)
            {
                "@type": "ListItem",
                "position": {{ $index + 1 }},
                "url": "{{ $guide->url }}",
                "name": "{{ $guide->title }}"
            }@if(!$loop->last),@endif
            @endforeach
        ]
    }
    @endif
}
</script>
@endsection
