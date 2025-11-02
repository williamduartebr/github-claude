<section aria-labelledby="artigos-titulo">
    <h2 id="artigos-titulo" class="sr-only">Artigos sobre {{ $category->name }}</h2>

    @if($articles->isEmpty())
    <div class="text-center py-12">
        <div class="max-w-md mx-auto">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum artigo encontrado</h3>
            <p class="mt-1 text-sm text-gray-500">
                @if(!empty($filters))
                Não encontramos artigos com os filtros selecionados. Tente remover alguns filtros.
                @else
                Ainda não temos artigos para esta categoria.
                @endif
            </p>
        </div>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        @foreach($articles as $index => $article)

        <article class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-100 overflow-hidden group flex flex-col h-full"
            itemprop="hasPart" itemscope itemtype="https://schema.org/Article">
            
            <div class="p-6 flex flex-col flex-grow">
                <div class="flex items-center justify-between mb-4">
                    <span class="inline-block bg-blue-100 text-blue-800 text-xs font-semibold px-3 py-1.5 rounded-full font-montserrat">
                        {{ $category->name }}
                    </span>
                    
                    @if($index < 2)
                    <span class="inline-block bg-blue-600 text-white text-xs font-bold px-2.5 py-1 rounded-full font-montserrat">
                        POPULAR
                    </span>
                    @elseif($article->created_at->diffInDays() < 7)
                    <span class="inline-block bg-green-600 text-white text-xs font-bold px-2.5 py-1 rounded-full font-montserrat">
                        NOVO
                    </span>
                    @else
                    <span class="text-xs text-gray-500 font-roboto">
                        @if(!empty($article->metadata['reading_time']))
                        {{ $article->metadata['reading_time'] }} min
                        @endif
                    </span>
                    @endif
                </div>

                <h3 class="text-xl font-bold text-gray-900 mb-3 font-montserrat leading-tight group-hover:text-blue-700 transition-colors line-clamp-3"
                    itemprop="headline">
                    <a href="{{ route('info.article.show', $article->slug) }}" class="hover:underline">
                        {{ $article->title }}
                    </a>
                </h3>

                <p class="text-gray-600 text-xs font-roboto mb-3 line-clamp-3 flex-grow" itemprop="abstract">                     
                    @if($article->seo_data['meta_description'])
                    {{ Str::limit(strip_tags($article->seo_data['meta_description']), 150) }}
                    @endif
                </p>

                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                    <time datetime="{{ $article->created_at->format('Y-m-d') }}"
                        class="flex items-center text-xs text-gray-500 font-roboto"
                        itemprop="datePublished">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        {{ $article->created_at->translatedFormat('d M Y') }}
                    </time>
                    <a href="{{ route('info.article.show', $article->slug) }}"
                        class="inline-flex items-center text-blue-700 hover:text-blue-900 font-semibold text-sm font-montserrat transition-all group-hover:gap-1">
                        Ler mais
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        </article>
        @endforeach
    </div>
    @endif

    @if($pagination['total_pages'] > 1)
    <div class="flex justify-center mt-8" role="navigation" aria-label="Paginação">
        <nav class="inline-flex rounded-md shadow-sm overflow-hidden text-sm font-roboto">
            @if($pagination['has_prev'])
            <a href="{{ request()->fullUrlWithQuery(['page' => $pagination['prev_page']]) }}"
                class="py-2 px-4 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-l-md font-medium transition-colors hidden sm:block"
                rel="prev" aria-label="Página anterior">Anterior</a>
            @endif

            @php
            $start = max(1, $pagination['current_page'] - 2);
            $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
            @endphp

            @if($start > 1)
            <a href="{{ request()->fullUrlWithQuery(['page' => 1]) }}"
                class="py-2 px-4 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors"
                aria-label="Página 1">1</a>
            @if($start > 2)
            <span class="py-2 px-4 bg-white border border-gray-300 text-gray-500">...</span>
            @endif
            @endif

            @for($page = $start; $page <= $end; $page++)
            @if($page == $pagination['current_page'])
            <span class="py-2 px-4 bg-blue-700 text-white font-semibold border border-blue-700"
                aria-label="Página {{ $page }}" aria-current="page">{{ $page }}</span>
            @else
            <a href="{{ request()->fullUrlWithQuery(['page' => $page]) }}"
                class="py-2 px-4 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors"
                aria-label="Página {{ $page }}">{{ $page }}</a>
            @endif
            @endfor

            @if($end < $pagination['total_pages'])
            @if($end < $pagination['total_pages'] - 1)
            <span class="py-2 px-4 bg-white border border-gray-300 text-gray-500">...</span>
            @endif
            <a href="{{ request()->fullUrlWithQuery(['page' => $pagination['total_pages']]) }}"
                class="py-2 px-4 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors"
                aria-label="Página {{ $pagination['total_pages'] }}">{{ $pagination['total_pages'] }}</a>
            @endif

            @if($pagination['has_next'])
            <a href="{{ request()->fullUrlWithQuery(['page' => $pagination['next_page']]) }}"
                class="py-2 px-4 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-r-md font-medium transition-colors"
                rel="next" aria-label="Próxima página">Próxima</a>
            @endif
        </nav>
    </div>

    <div class="text-center mt-4 text-sm text-gray-600 font-roboto">
        Mostrando {{ ($pagination['current_page'] - 1) * $pagination['per_page'] + 1 }} -
        {{ min($pagination['current_page'] * $pagination['per_page'], $pagination['total']) }}
        de {{ $pagination['total'] }} artigos
    </div>
    @endif

    <div class="container mx-auto px-4 md:px-0 pt-6">
        [ADSENSE-2]
    </div>
</section>