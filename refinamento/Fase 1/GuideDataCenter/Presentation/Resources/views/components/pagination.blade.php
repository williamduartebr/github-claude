{{--
    Component de Paginação SEO-Friendly
    
    Props esperadas:
    - $pagination: array com dados de paginação do ViewModel
    
    Features SEO:
    - Links reais (não buttons)
    - rel="prev" e rel="next"
    - rel="nofollow" em páginas > 3
    - Canonical automático
--}}

@if($pagination['total_pages'] > 1)
<nav aria-label="Paginação" class="flex items-center justify-center gap-2 mt-8 mb-4 font-roboto">
    
    {{-- Botão Anterior --}}
    @if($pagination['has_prev'])
        <a href="{{ $pagination['prev_url'] }}" 
           rel="prev"
           class="px-3 py-1 bg-gray-200 rounded text-sm hover:bg-gray-300 transition-colors"
           aria-label="Página anterior">
            «
        </a>
    @else
        <span class="px-3 py-1 bg-gray-100 rounded text-sm text-gray-400 cursor-not-allowed">
            «
        </span>
    @endif

    {{-- Números das Páginas --}}
    @foreach($pagination['pages'] as $page)
        @if($page['number'] === '...')
            <span class="px-3 py-1 text-gray-400 text-sm">...</span>
        @elseif($page['is_current'])
            <span class="px-3 py-1 bg-blue-600 text-white rounded text-sm font-semibold" 
                  aria-current="page">
                {{ $page['number'] }}
            </span>
        @else
            <a href="{{ $page['url'] }}" 
               @if($page['number'] > 3) rel="nofollow" @endif
               class="px-3 py-1 bg-gray-200 rounded text-sm hover:bg-gray-300 transition-colors"
               aria-label="Ir para página {{ $page['number'] }}">
                {{ $page['number'] }}
            </a>
        @endif
    @endforeach

    {{-- Botão Próximo --}}
    @if($pagination['has_next'])
        <a href="{{ $pagination['next_url'] }}" 
           rel="next"
           class="px-3 py-1 bg-gray-200 rounded text-sm hover:bg-gray-300 transition-colors"
           aria-label="Próxima página">
            »
        </a>
    @else
        <span class="px-3 py-1 bg-gray-100 rounded text-sm text-gray-400 cursor-not-allowed">
            »
        </span>
    @endif
</nav>

{{-- Meta tags SEO para crawlers (colocar no <head>) --}}
@push('seo_tags')
    {{-- Canonical --}}
    <link rel="canonical" href="{{ $pagination['current_page'] === 1 ? $pagination['base_url'] : $pagination['base_url'] . '?page=' . $pagination['current_page'] }}" />
    
    {{-- Prev/Next para crawlers --}}
    @if($pagination['has_prev'])
        <link rel="prev" href="{{ $pagination['prev_url'] }}" />
    @endif
    
    @if($pagination['has_next'])
        <link rel="next" href="{{ $pagination['next_url'] }}" />
    @endif
    
    {{-- Robots meta (noindex em páginas > 3) --}}
    @if($pagination['current_page'] > 3)
        <meta name="robots" content="noindex, follow" />
    @endif
@endpush

{{-- Informação para screen readers --}}
<div class="sr-only" aria-live="polite">
    Página {{ $pagination['current_page'] }} de {{ $pagination['total_pages'] }}. 
    Total de {{ $pagination['total_guides'] }} guias.
</div>
@endif