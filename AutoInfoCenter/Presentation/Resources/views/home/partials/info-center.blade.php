<section class="mb-12" aria-labelledby="info-titulo">
    <div class="flex justify-between items-center mb-6">
        <h2 id="info-titulo" class="text-2xl md:text-3xl font-bold text-gray-800 font-montserrat">Centro de
            Informações Automotivas</h2>
        <a href="{{ route('info.category.index') }}"
            class="text-blue-600 hover:text-blue-800 flex items-center font-montserrat">
            Ver todas as categorias
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" viewBox="0 0 20 20" fill="currentColor"
                aria-hidden="true">
                <path fill-rule="evenodd"
                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                    clip-rule="evenodd" />
            </svg>
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($categories as $category)
        @if (isset($category['to_follow']) && $category['to_follow'] === true)
        <div class="bg-white rounded-lg shadow-md overflow-hidden" itemscope itemtype="https://schema.org/ItemList">
            <meta itemprop="itemListOrder" content="Unordered" />
            <meta itemprop="numberOfItems" content="{{ count($category['articles']) + 1 }}" />

            <div class="bg-blue-700 text-white p-4">
                <h2 class="text-xl font-semibold font-montserrat" itemprop="name">
                    <a href="{{ route('info.category.show', $category['slug'])}}" class="hover:underline">
                        {{ $category['name'] }}
                    </a>
                </h2>
            </div>

            <div class="p-4">
                <ul class="space-y-2 font-roboto">
                    @foreach($category['articles'] as $index => $article)
                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <meta itemprop="position" content="{{ $index + 1 }}" />
                        <a href="{{ route('info.article.show', $article['slug'])}}"
                            class="text-blue-600 hover:underline" itemprop="url">
                            <span itemprop="name">{{ clean_title( $article['title'] ) }}</span>
                        </a>
                    </li>
                    @endforeach

                    @if(count($category['articles']) > 0)
                    <li class="pt-2 font-montserrat" itemprop="itemListElement" itemscope
                        itemtype="https://schema.org/ListItem">
                        <meta itemprop="position" content="{{ count($category['articles']) + 1 }}" />
                        <a href="{{ route('info.category.all-models', $category['slug']) }}"
                            class="text-sm font-medium text-blue-800 hover:underline flex items-center" itemprop="url">
                            <span itemprop="name">Ver todos os modelos</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
        @endif
        @endforeach
    </div>

    <!-- Link para todas as categorias - versão mobile (visível apenas em telas menores) -->
    <div class="mt-6 text-center md:hidden">
        <a href="{{ route('info.category.index') }}"
            class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-md transition-colors font-montserrat focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            Ver todas as categorias
        </a>
    </div>
</section>