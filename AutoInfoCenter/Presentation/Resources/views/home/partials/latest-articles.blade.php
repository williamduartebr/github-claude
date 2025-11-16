<hr class="border-gray-200">

<section class="my-12" aria-labelledby="ultimos-artigos-titulo">
    <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-8 gap-4">
        <div>
            <h2 id="ultimos-artigos-titulo" class="text-2xl md:text-3xl font-bold text-gray-800 mb-3 font-montserrat">
                Últimos Artigos Publicados
            </h2>
            <p class="text-gray-600 text-base font-roboto">Fique por dentro das novidades automotivas</p>
        </div>
        <a href="{{ route('info.recent-articles') }}" class="hidden md:flex text-blue-700 hover:text-blue-900 items-center text-base transition-colors group">
            Ver todos
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 group-hover:translate-x-1 transition-transform" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
            </svg>
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($recentArticles as $article)
        <article class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-100 overflow-hidden group flex flex-col h-full">
            <div class="p-6 flex flex-col flex-grow">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="inline-block bg-blue-100 text-blue-800 text-xs font-semibold px-3 py-1.5 rounded-full font-montserrat">
                            {{ $article->category_name ?? 'Artigo' }}
                        </span>
                        
                        @if($article->subcategory_name)
                        <span class="inline-block bg-emerald-100 text-emerald-800 text-xs font-semibold px-3 py-1.5 rounded-full font-montserrat">
                            {{ $article->subcategory_name }}
                        </span>
                        @endif
                    </div>
                    
                    @if(isset($article->metadata['reading_time']))
                    <span class="text-xs text-gray-500 font-roboto whitespace-nowrap">
                        {{ $article->metadata['reading_time'] }} min
                    </span>
                    @endif
                </div>

                <h3 class="text-xl font-bold text-gray-700 mb-3 font-montserrat leading-tight group-hover:text-blue-700 transition-colors line-clamp-3">
                    <a href="{{ route('info.article.show', $article->slug) }}" class="hover:underline">
                        {{ $article->title }}
                    </a>
                </h3>

                <p class="text-gray-600 text-sm font-roboto mb-4 line-clamp-3 flex-grow">
                    {{ $article->seo_data['meta_description'] ?? $article->content['introducao'] ?? '' }}
                </p>

                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                    <time datetime="{{ $article->created_at->translatedFormat('Y-m-d') }}" class="flex items-center text-xs text-gray-500 font-roboto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        {{ $article->created_at->translatedFormat('d M Y') }}
                    </time>
                    <a href="{{ route('info.article.show', $article->slug) }}" class="inline-flex items-center text-blue-700 hover:text-blue-900 font-semibold text-sm font-montserrat transition-all group-hover:gap-1">
                        Ler mais
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        </article>
        @empty
        <div class="col-span-full text-center py-12">
            <p class="text-gray-500">Nenhum artigo disponível no momento.</p>
        </div>
        @endforelse
    </div>

    <!-- Link para todos os artigos - versão mobile -->
    <div class="mt-10 text-center md:hidden">
        <a href="{{ route('info.recent-articles') }}" class="inline-flex items-center justify-center bg-blue-700 hover:bg-blue-800 text-white font-semibold py-3 px-8 rounded-lg transition-all font-montserrat shadow-md hover:shadow-xl w-full sm:w-auto">
            Ver todos os artigos
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
            </svg>
        </a>
    </div>
</section>
