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
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
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

                if (empty($article->vehicle_info['vehicle_type'])) {
                    $imageDefault = 'default-car.png';
                } else {
                    $imageDefault = \Str::slug( sprintf("%s-%s", $category_slug, $article->vehicle_info['vehicle_type'] ));
                }

                @endphp

                <img src="https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/{{  $imageDefault  }}.png"
                    alt="{{ $altText }}" class="w-full h-40 sm:h-48 object-cover" width="300" height="200"
                    loading="lazy" decoding="async" itemprop="image"
                    onerror="this.src='https:\/\/mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/default-car.png'">

                {{-- <img src="/images/{{ $imageName }}" alt="{{ $altText }}" class="w-full h-40 sm:h-48 object-cover"
                    width="300" height="200" loading="lazy" decoding="async" itemprop="image"
                    onerror="this.src='/images/default-car.jpg'"> --}}

                @if($index < 2) <div
                    class="absolute top-0 right-0 bg-blue-600 text-white text-xs font-bold px-2 py-1 m-2 rounded font-montserrat">
                    POPULAR
            </div>
            @elseif($article->created_at->diffInDays() < 7) <div
                class="absolute top-0 right-0 bg-green-600 text-white text-xs font-bold px-2 py-1 m-2 rounded font-montserrat">
                NOVO
    </div>
    @endif
    </div>

    <div class="p-4 flex flex-col flex-grow">
        <h3 class="text-lg md:text-xl font-semibold mb-2 font-montserrat" itemprop="headline">
            <a href="{{ route('info.article.show', $article->slug) }}" class="text-blue-700 hover:underline">
                {{ $article->title }}
            </a>
        </h3>

        <p class="text-sm md:text-base text-gray-600 mb-4 flex-grow font-roboto" itemprop="abstract">
            @if(!empty($article->content['introducao']))
            {{ Str::limit(strip_tags($article->content['introducao']), 120) }}
            @else
            Guia completo sobre {{ strtolower($category->name) }}
            @if(!empty($article->vehicle_info['make']) && !empty($article->vehicle_info['model']))
            para {{ $article->vehicle_info['make'] }} {{ $article->vehicle_info['model'] }}
            @endif
            . Informações técnicas e recomendações especializadas.
            @endif
        </p>

        <div class="flex items-center justify-between mt-2">
            <span class="text-xs text-gray-500 font-roboto" itemprop="datePublished"
                content="{{ $article->created_at->format('Y-m-d') }}">
                Publicado em: {{ $article->created_at->format('d/m/Y') }}
            </span>
            <a href="{{ route('info.article.show', $article->slug) }}"
                class="text-blue-600 hover:underline text-sm font-medium font-montserrat">
                Leia mais »
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

    <!-- INSERIR BANNER AQUI - POSIÇÃO 2 -->
    <div class="container mx-auto px-4 md:px-0 pt-6">
        [ADSENSE-2]
    </div>
</section>