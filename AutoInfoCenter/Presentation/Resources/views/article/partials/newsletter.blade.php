<section class="max-w-4xl mx-auto mb-12 md:mb-16">
    <div class="bg-[#151C25] text-white rounded-lg shadow-md p-6">
        <div class="md:flex md:items-center">
            <div class="md:w-2/3 md:pr-8 mb-6 md:mb-0">
                <h2 class="text-2xl font-semibold mb-3">Receba Dicas de Manutenção</h2>
                <p class="text-gray-200 leading-relaxed">Cadastre-se para receber guias exclusivos, alertas de recall e
                    dicas personalizadas para {{ $article->vehicle_info['make'] ?? '' }} {{ $article->vehicle_info['model'] ?? '' }}.</p>
            </div>
            <div class="md:w-1/3">
                <form class="flex flex-col space-y-3">
                    <input type="email"
                        class="w-full px-4 py-3 rounded-lg text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#E06600] border-0"
                        placeholder="Seu email">
                    <button type="submit"
                        class="bg-[#E06600] hover:bg-[#B35200] text-white font-medium px-6 py-3 rounded-lg transition-colors">
                        Cadastrar
                    </button>
                    <p class="text-xs text-gray-400">Seus dados estão seguros conosco. Consulte nossa <a
                            href="{{ route('privacy-policy') }}" rel="nofollow" target="_blank"
                            class="underline hover:text-white">política de privacidade</a>.</p>
                </form>
            </div>
        </div>
    </div>
</section>
