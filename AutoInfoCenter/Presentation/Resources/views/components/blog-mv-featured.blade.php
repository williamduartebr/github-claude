@if($featuredPost)
<!-- Destaque do Blog -->
<section class="mb-12 bg-gradient-to-r from-blue-800 to-blue-900 text-white rounded-lg overflow-hidden shadow-lg" aria-labelledby="destaque-titulo">
    <div class="grid grid-cols-1 lg:grid-cols-2">
        <div class="p-8 md:p-10 lg:p-12 flex flex-col justify-center">
            <span class="text-yellow-400 uppercase text-sm font-semibold tracking-wide font-montserrat">Destaque</span>
            <h2 id="destaque-titulo" class="text-2xl md:text-3xl font-bold mt-2 mb-4 font-montserrat">
                <a href="{{ $featuredPost['link'] }}" class="hover:text-blue-100 transition-colors" rel="noreferrer">
                    {{ $featuredPost['title'] }}
                </a>
            </h2>
            <p class="mb-6 text-blue-100 font-roboto">{{ Str::limit($featuredPost['description'], 180) }}</p>
            <a href="{{ $featuredPost['link'] }}" class="inline-flex items-center font-medium text-yellow-400 hover:text-yellow-300 transition-colors font-montserrat" rel="noreferrer">
                Ler artigo completo
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </a>
        </div>
        <div class="hidden lg:block relative">
            <img src="{{ $featuredPost['imageUrl'] }}" alt="{{ $featuredPost['title'] }}" class="w-full h-full object-cover" onerror="this.src='https://mercadoveiculos.com/blog/wp-content/uploads/2024/04/cropped-logo-32x32.png';">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-900/60 to-transparent"></div>
        </div>
    </div>
</section>
@endif