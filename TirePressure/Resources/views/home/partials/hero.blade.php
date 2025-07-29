<!-- Hero Banner -->
<section class="relative bg-blue-900 text-white py-16" aria-labelledby="hero-heading">

    @if (Agent::isDesktop())
    <div class="absolute inset-0 z-0">
        <img loading="lazy" src="https://mercadoveiculos.s3.us-east-1.amazonaws.com/statics/bg-home-1920x1080.jpg"
            alt="Fundo" class="w-full h-full object-cover opacity-5" aria-hidden="true">
    </div>

    @endif

    <div class="container mx-auto px-4 relative z-10">
        <div class="max-w-3xl mx-auto text-center">
            <h1 id="hero-heading" class="text-3xl md:text-4xl lg:text-5xl font-bold mb-4 font-montserrat">Seu Guia
                Automotivo Completo
            </h1>
            <p class="text-xl md:text-2xl mb-8 font-roboto">Tudo sobre calibragem de pneus, óleos recomendados e
                manutenção para seu veículo</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('info.category.index') }}"
                    class="bg-yellow-500 hover:bg-yellow-600 text-blue-900 font-medium py-3 px-6 rounded-md transition-colors text-base md:text-lg font-montserrat focus:ring-2 focus:ring-yellow-300 focus:ring-offset-2">Guias
                    de Manutenção</a>
                <a href="/blog"
                    class="bg-white hover:bg-gray-100 text-blue-900 font-medium py-3 px-6 rounded-md transition-colors text-base md:text-lg font-montserrat focus:ring-2 focus:ring-white focus:ring-offset-2">Artigos
                    do Blog</a>
            </div>
        </div>
    </div>
</section>