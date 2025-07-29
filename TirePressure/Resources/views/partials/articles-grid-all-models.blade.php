<section aria-labelledby="artigos-titulo-all">
    <h2 id="artigos-titulo-all" class="sr-only">Todos os artigos sobre {{ $category->name }}</h2>

    @if($articles->isEmpty())
    <div class="text-center py-16">
        <div class="max-w-md mx-auto">
            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">Nenhum artigo encontrado</h3>
            <p class="mt-2 text-sm text-gray-500">
                @if(!empty($filters))
                Não encontramos artigos com os filtros selecionados. Tente remover alguns filtros para ver mais
                resultados.
                @else
                Ainda não temos artigos para esta categoria.
                @endif
            </p>
            @if(!empty($filters))
            <div class="mt-4">
                <a href="{{ route('info.category.all-models', $category->slug) }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200">
                    Ver todos os artigos
                </a>
            </div>
            @endif
        </div>
    </div>
    @else
    <!-- Grid de 6 colunas para mostrar mais artigos -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-8">
        @foreach($articles as $index => $article)
        <article class="bg-white rounded-lg shadow-md overflow-hidden flex flex-col h-full" itemprop="hasPart" itemscope
            itemtype="https://schema.org/Article">
            <div class="relative">
                @php
                // Gera uma imagem baseada no veículo ou categoria
                $imageSlug = $article->vehicle_info['make_slug'] ?? 'default';
                $modelSlug = $article->vehicle_info['model_slug'] ?? '';
                $imageName = $imageSlug . ($modelSlug ? '-' . $modelSlug : '') . '.jpg';
                $altText = $article->vehicle_info['make'] ?? $category->name;
                if (!empty($article->vehicle_info['model'])) {
                $altText .= ' ' . $article->vehicle_info['model'];
                }

                $imageDefault = \Str::slug( sprintf("%s-%s", $category_slug, $article->vehicle_info['vehicle_type'] ));

                @endphp

                <img src="https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/{{  $imageDefault  }}.png"
                    alt="{{ $altText }}" class="w-full h-32 object-cover" width="200" height="128" loading="lazy"
                    decoding="async" itemprop="image"
                    onerror="this.src='https:\/\/mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/default-car.png'">

                {{-- <img src="/images/{{ $imageName }}" alt="{{ $altText }}" class="w-full h-32 object-cover"
                    width="200" height="128" loading="lazy" decoding="async" itemprop="image"
                    onerror="this.src='/images/default-car.jpg'"> --}}

                @if($index < 3) <div
                    class="absolute top-0 right-0 bg-blue-600 text-white text-xs font-bold px-2 py-1 m-1 rounded font-montserrat">
                    TOP
            </div>
            @elseif($article->created_at->diffInDays() < 7) <div
                class="absolute top-0 right-0 bg-green-600 text-white text-xs font-bold px-2 py-1 m-1 rounded font-montserrat">
                NOVO
    </div>
    @endif
    </div>

    <div class="p-3 flex flex-col flex-grow">
        <div class="mb-2">
            @if(!empty($article->vehicle_info['make']) && !empty($article->vehicle_info['model']))
            <span class="inline-block bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded font-roboto font-medium">
                {{ $article->vehicle_info['make'] }} {{ $article->vehicle_info['model'] }}
                @if(!empty($article->vehicle_info['year']) && $article->vehicle_info['year'] !== 'Todos')
                {{ $article->vehicle_info['year'] }}
                @endif
            </span>
            @endif
        </div>

        <h3 class="text-sm font-semibold mb-2 font-montserrat line-clamp-2" itemprop="headline">
            <a href="{{ route('info.article.show', $article->slug) }}" class="text-blue-700 hover:underline">
                {{ $article->title }}
            </a>
        </h3>

        <p class="text-xs text-gray-600 mb-3 flex-grow font-roboto line-clamp-3" itemprop="abstract">
            @if(!empty($article->content['introducao']))
            {{ Str::limit(strip_tags($article->content['introducao']), 80) }}
            @else
            Guia sobre {{ strtolower($category->name) }}
            @if(!empty($article->vehicle_info['make']) && !empty($article->vehicle_info['model']))
            para {{ $article->vehicle_info['make'] }} {{ $article->vehicle_info['model'] }}
            @endif
            @endif
        </p>

        <div class="flex items-center justify-between mt-auto">
            <span class="text-xs text-gray-500 font-roboto" itemprop="datePublished"
                content="{{ $article->created_at->format('Y-m-d') }}">
                {{ $article->created_at->format('d/m/Y') }}
            </span>
            <a href="{{ route('info.article.show', $article->slug) }}"
                class="text-blue-600 hover:underline text-xs font-medium font-montserrat">
                Ver guia →
            </a>
        </div>
    </div>
    </article>
    @endforeach
    </div>
    @endif

    <!-- Paginação -->
    @if($pagination['total_pages'] > 1)
    <div class="flex justify-center mt-8" role="navigation" aria-label="Paginação">
        <nav class="inline-flex rounded-md shadow overflow-hidden text-sm font-roboto">
            @if($pagination['has_prev'])
            <a href="{{ request()->fullUrlWithQuery(['page' => $pagination['prev_page']]) }}"
                class="py-2 px-3 bg-white border border-gray-300 text-gray-500 hover:bg-gray-50 rounded-l-md hidden sm:block"
                rel="prev" aria-label="Página anterior">Anterior</a>
            @endif

            @php
            $start = max(1, $pagination['current_page'] - 2);
            $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
            @endphp

            @if($start > 1)
            <a href="{{ request()->fullUrlWithQuery(['page' => 1]) }}"
                class="py-2 px-3 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50"
                aria-label="Página 1">1</a>
            @if($start > 2)
            <span class="py-2 px-3 bg-white border border-gray-300 text-gray-500">...</span>
            @endif
            @endif

            @for($page = $start; $page <= $end; $page++) @if($page==$pagination['current_page']) <span
                class="py-2 px-3 bg-blue-600 text-white font-medium border border-blue-600"
                aria-label="Página {{ $page }}" aria-current="page">{{ $page }}</span>
                @else
                <a href="{{ request()->fullUrlWithQuery(['page' => $page]) }}"
                    class="py-2 px-3 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50"
                    aria-label="Página {{ $page }}">{{ $page }}</a>
                @endif
                @endfor

                @if($end < $pagination['total_pages']) @if($end < $pagination['total_pages'] - 1) <span
                    class="py-2 px-3 bg-white border border-gray-300 text-gray-500">...</span>
                    @endif
                    <a href="{{ request()->fullUrlWithQuery(['page' => $pagination['total_pages']]) }}"
                        class="py-2 px-3 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50"
                        aria-label="Página {{ $pagination['total_pages'] }}">{{ $pagination['total_pages'] }}</a>
                    @endif

                    @if($pagination['has_next'])
                    <a href="{{ request()->fullUrlWithQuery(['page' => $pagination['next_page']]) }}"
                        class="py-2 px-3 bg-white border border-gray-300 text-gray-500 hover:bg-gray-50 rounded-r-md"
                        rel="next" aria-label="Próxima página">Próxima</a>
                    @endif
        </nav>
    </div>

    <!-- Informações da paginação -->
    <div class="text-center mt-4 text-sm text-gray-600">
        Mostrando {{ ($pagination['current_page'] - 1) * $pagination['per_page'] + 1 }} -
        {{ min($pagination['current_page'] * $pagination['per_page'], $pagination['total']) }}
        de {{ $pagination['total'] }} artigos
    </div>
    @endif
</section>

@push('styles')
<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endpush