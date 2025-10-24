<!-- FOOTER-->
<footer class="bg-gray-800 text-white py-8">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8">
            
            <!-- COLUNA 1: SOBRE NÓS -->
            <div>
                <h3 class="text-lg font-semibold mb-4 font-montserrat">Sobre Nós</h3>
                <p class="text-gray-400 text-sm font-roboto mb-4">
                    O Mercado Veículos é o seu portal completo de informações automotivas, com foco em manutenção e cuidados para todos os modelos.
                </p>
                
                <!-- LINKS ESTRATÉGICOS -->
                <ul class="space-y-2 text-gray-400 text-sm font-roboto mb-4">
                    <li>
                        <a href="{{ route('about') }}" class="hover:text-white transition-colors flex items-center">
                            <svg class="w-3 h-3 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            Nossa História
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('editorial-team') }}" class="hover:text-white transition-colors flex items-center">
                            <svg class="w-3 h-3 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Nossa Equipe
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('editorial-methodology') }}" class="hover:text-white transition-colors flex items-center">
                            <svg class="w-3 h-3 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Metodologia Editorial
                        </a>
                    </li>
                </ul>
                
                <!-- REDES SOCIAIS -->
                <div class="flex space-x-4">
                    <a href="https://www.facebook.com/mercadoveiculospro" target="_blank" rel="noopener" class="text-gray-400 hover:text-white transition-colors" aria-label="Facebook">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd"></path>
                        </svg>
                    </a>
                    <a href="https://www.instagram.com/mercadoveiculospro" target="_blank" rel="noopener" class="text-gray-400 hover:text-white transition-colors" aria-label="Instagram">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"></path>
                        </svg>
                    </a>
                </div>
            </div>
            
            <!-- COLUNA 2: CATEGORIAS -->
            <div>
                <h3 class="text-lg font-semibold mb-4 font-montserrat">Categorias</h3>
                <ul class="space-y-2 text-gray-400 text-sm font-roboto">
                    <!-- PRINCIPAIS -->
                    <li><a href="{{ route('info.category.show', 'oleo-recomendado') }}" class="hover:text-white transition-colors">Óleo Recomendado</a></li>
                    <li><a href="{{ route('info.category.show', 'filtros') }}" class="hover:text-white transition-colors">Filtros</a></li>
                    <li><a href="{{ route('info.category.show', 'freios') }}" class="hover:text-white transition-colors">Sistema de Freios</a></li>
                    <li><a href="{{ route('info.category.show', 'suspensao') }}" class="hover:text-white transition-colors">Suspensão</a></li>
                    
                    <!-- SECUNDÁRIAS -->
                    <li><a href="{{ route('info.category.show', 'calibragem-pneus') }}" rel="nofollow" class="hover:text-white transition-colors">Calibragem de Pneus</a></li>
                    <li><a href="{{ route('info.category.show', 'revisoes-programadas') }}" rel="nofollow" class="hover:text-white transition-colors">Revisões Programadas</a></li>
                    
                    <!-- LINK PARA VER TODAS -->
                    <li class="pt-2 border-t border-gray-700">
                        <a href="{{ route('info.category.index') }}" class="hover:text-white transition-colors font-medium text-yellow-400">
                            Ver todas as categorias →
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- COLUNA 3: MARCAS (links estratégicos) -->
            <div>
                <h3 class="text-lg font-semibold mb-4 font-montserrat">Marcas Populares</h3>
                <ul class="space-y-2 text-gray-400 text-sm font-roboto">
                    <!-- MARCAS PRINCIPAIS -->
                    {{-- <li><a href="/marca/toyota" class="hover:text-white transition-colors">Toyota</a></li>
                    <li><a href="/marca/volkswagen" class="hover:text-white transition-colors">Volkswagen</a></li>
                    <li><a href="/marca/fiat" class="hover:text-white transition-colors">Fiat</a></li>
                    <li><a href="/marca/chevrolet" class="hover:text-white transition-colors">Chevrolet</a></li>
                    <li><a href="/marca/honda" class="hover:text-white transition-colors">Honda</a></li>
                    <li><a href="/marca/hyundai" class="hover:text-white transition-colors">Hyundai</a></li> --}}

                    <li class="hover:text-white transition-colors opacity-30">Toyota</a></li>
                    <li class="hover:text-white transition-colors opacity-30">Volkswagen</a></li>
                    <li class="hover:text-white transition-colors opacity-30">Fiat</a></li>
                    <li class="hover:text-white transition-colors opacity-30">Chevrolet</a></li>
                    <li class="hover:text-white transition-colors opacity-30">Honda</a></li>
                    <li class="hover:text-white transition-colors opacity-30">Hyundai</a></li>
                    
                    <!-- LINK PARA VER TODAS -->
                    <li class="pt-2 border-t border-gray-700">
                        {{-- <a href="/marcas" class="hover:text-white transition-colors font-medium text-yellow-400">
                            Ver todas as marcas →
                        </a> --}}

                        <span class="hover:text-white transition-colors font-medium text-yellow-400 opacity-30">
                            Ver todas as marcas →
                        </span>
                    </li>
                </ul>
            </div>
            
            <!-- COLUNA 4: RECURSOS & CONTATO -->
            <div>
                <h3 class="text-lg font-semibold mb-4 font-montserrat">Recursos</h3>
                <ul class="space-y-2 text-gray-400 text-sm font-roboto">
                    <!-- RECURSOS -->
                    {{-- <li><a href="/comparativos" class="hover:text-white transition-colors">Comparativos</a></li>
                    <li><a href="/guias" class="hover:text-white transition-colors">Guias Técnicos</a></li>
                    <li><a href="/diagnosticos" class="hover:text-white transition-colors">Diagnósticos</a></li> --}}

                    <li class="hover:text-white transition-colors opacity-30">Comparativos</a></li>
                    <li class="hover:text-white transition-colors opacity-30">Guias Técnicos</a></li>
                    <li class="hover:text-white transition-colors opacity-30">Diagnósticos</a></li>
                    
                    <!-- SEPARADOR -->
                    <li class="pt-3 border-t border-gray-700">
                        <span class="text-xs uppercase tracking-wide text-gray-500">Contato & Legal</span>
                    </li>
                    
                    <!-- CONTATO & POLÍTICAS -->
                    <li class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <a href="{{ route('contact-us') }}" rel="nofollow" class="hover:text-white transition-colors">Fale Conosco</a>
                    </li>
                    <li class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4" />
                        </svg>
                        <a href="{{ route('privacy-policy') }}" rel="nofollow" class="hover:text-white transition-colors">Privacidade</a>
                    </li>
                    <li class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <a href="{{ route('terms-of-use') }}" rel="nofollow" class="hover:text-white transition-colors">Termos de Uso</a>
                    </li>
                </ul>
                
                <!-- NEWSLETTER -->
                <div class="mt-6">
                    <h4 class="text-sm font-semibold mb-2 font-montserrat">Newsletter Técnica</h4>
                    <p class="text-xs text-gray-500 mb-3">Receba dicas de manutenção semanalmente</p>
                    <form class="flex" action="/newsletter" method="POST">
                        @csrf
                        <label for="footer-email" class="sr-only">Seu e-mail</label>
                        <input type="email" id="footer-email" name="email" placeholder="Seu e-mail" required
                            class="px-3 py-1 bg-white text-sm text-gray-800 rounded-l-md w-full font-roboto focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button type="submit"
                            class="bg-yellow-500 hover:bg-yellow-600 px-3 py-1 rounded-r-md text-sm text-blue-900 font-semibold font-montserrat focus:outline-none focus:ring-2 focus:ring-yellow-400 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- RODAPÉ INFERIOR -->
        <div class="border-t border-gray-700 mt-8 pt-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <!-- Copyright -->
                <div class="text-center md:text-left mb-4 md:mb-0">
                    <p class="text-sm font-roboto text-gray-400">© {{ date('Y') }} Mercado Veículos. Todos os direitos reservados.</p>
                    <p class="text-xs mt-1 font-roboto text-gray-500">Desenvolvido com ❤️ no Brasil</p>
                </div>
                
                <!-- Links rápidos legais -->
                <div class="flex space-x-4 text-xs text-gray-500">
                    <a href="{{ route('sitemap.index') }}" rel="nofollow" class="hover:text-gray-400 transition-colors">Sitemap</a>
                    <a href="{{ route('rss.index') }}" rel="nofollow" class="hover:text-gray-400 transition-colors">RSS</a>
                    <a href="{{ route('about') }}" class="hover:text-gray-400 transition-colors">Sobre</a>
                </div>
            </div>
            

            <div class="mt-4 text-center">
                <p class="text-xs text-gray-500">
                    Informações técnicas confiáveis • Metodologia editorial rigorosa • 
                    <span class="text-yellow-400">{{ number_format($articlesCount) }}+</span> artigos especializados
                </p>
            </div>
        </div>
    </div>
</footer>
