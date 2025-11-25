{{-- 
    View: Category Index
    Lista guias por categoria
--}}
@extends('layouts.app')

@section('title', isset($category) ? $category->metaTitle : 'Categorias de Guias')

@section('meta')
    @if(isset($category))
        <meta name="description" content="{{ $category->metaDescription }}">
        <link rel="canonical" href="{{ $category->url }}">
        
        {{-- Schema.org --}}
        <script type="application/ld+json">
            {!! json_encode($category->getStructuredData(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
    @else
        <meta name="description" content="Explore todas as categorias de guias automotivos">
    @endif
@endsection

@section('content')
<div class="category-page">
    {{-- Breadcrumb --}}
    @if(isset($category))
        <nav class="breadcrumb" aria-label="Navegação">
            <ol class="breadcrumb-list" itemscope itemtype="https://schema.org/BreadcrumbList">
                @foreach($category->getBreadcrumb() as $index => $item)
                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"
                        @if($loop->last) aria-current="page" @endif>
                        @if(!$loop->last)
                            <a href="{{ $item['url'] }}" itemprop="item">
                                <span itemprop="name">{{ $item['name'] }}</span>
                            </a>
                        @else
                            <span itemprop="name">{{ $item['name'] }}</span>
                        @endif
                        <meta itemprop="position" content="{{ $index + 1 }}">
                    </li>
                @endforeach
            </ol>
        </nav>
    @endif

    {{-- Category Header --}}
    @if(isset($category))
        <header class="category-header">
            @if($category->icon)
                <span class="category-icon">{{ $category->icon }}</span>
            @endif
            
            <h1>{{ $category->name }}</h1>
            
            @if($category->description)
                <p class="category-description">
                    {{ $category->description }}
                </p>
            @endif

            @if($category->guidesCount > 0)
                <span class="category-count">
                    {{ $category->guidesCount }} {{ $category->guidesCount === 1 ? 'guia' : 'guias' }} disponíveis
                </span>
            @endif
        </header>
    @else
        {{-- Categories Grid (quando não há categoria selecionada) --}}
        <header class="listing-header">
            <h1>Categorias de Guias</h1>
            <p>Escolha uma categoria para ver os guias disponíveis</p>
        </header>
        
        @if(isset($categories) && $categories->isNotEmpty())
            <div class="categories-grid">
                @foreach($categories as $cat)
                    <a href="{{ $cat->url }}" class="category-card">
                        @if($cat->icon)
                            <span class="card-icon">{{ $cat->icon }}</span>
                        @endif
                        
                        @if($cat->image)
                            <img src="{{ $cat->image }}" alt="{{ $cat->name }}" class="card-image">
                        @endif
                        
                        <h2 class="card-title">{{ $cat->name }}</h2>
                        
                        @if($cat->description)
                            <p class="card-description">{{ Str::limit($cat->description, 100) }}</p>
                        @endif
                        
                        @if($cat->guidesCount > 0)
                            <span class="card-count">{{ $cat->guidesCount }} guias</span>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif
    @endif

    {{-- Guides List --}}
    @if($guides->isNotEmpty())
        <section class="category-guides">
            <h2 class="section-title">Guias em {{ $category->name ?? 'Esta Categoria' }}</h2>
            
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

                            <footer class="card-footer">
                                <span class="card-date">{{ $guide->createdAt }}</span>
                                <a href="{{ $guide->url }}" class="card-link">
                                    Ler mais →
                                </a>
                            </footer>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if(isset($pagination) && $pagination)
                <nav class="pagination-wrapper" aria-label="Paginação">
                    {{ $pagination->withQueryString()->links() }}
                </nav>
            @endif
        </section>
    @elseif(isset($category))
        <div class="empty-state">
            <h2>Nenhum guia nesta categoria</h2>
            <p>Ainda não há guias disponíveis na categoria {{ $category->name }}.</p>
            
            <div class="empty-actions">
                <a href="{{ route('guide.index') }}" class="btn btn-primary">
                    Ver todos os guias
                </a>
            </div>
        </div>
    @endif
</div>

{{-- ItemList Schema --}}
@if($guides->isNotEmpty())
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "ItemList",
    "name": "{{ $category->name ?? 'Guias' }}",
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
</script>
@endif
@endsection
