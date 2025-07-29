<header class="bg-[#0A2868] text-white">
    <div class="container mx-auto px-4 py-3 flex flex-wrap justify-between items-center">
        <div class="flex items-center">
            <a href="{{ url('/') }}" class="flex items-center" aria-label="Página inicial Mercado Veículos">
                <img src="https://mercadoveiculos.s3.amazonaws.com/statics/logos/logo-mercadoveiculos-write.svg"
                    alt="Mercado Veículos" class="h-10 mr-2" width="260" height="48">
            </a>
        </div>
        <div class="flex items-center mt-2 sm:mt-0">
            <x-info::search-button class="ml-4" />
        </div>
    </div>

    <nav class="bg-[#0E368A]" aria-label="Menu principal">
        <div class="container mx-auto px-4 py-2 flex justify-between items-center">
            <button class="text-white flex items-center p-2" aria-expanded="false" aria-controls="mobile-menu">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <span class="ml-2 text-sm md:text-base font-roboto">Menu</span>
            </button>

            <div class="hidden sm:flex space-x-4 text-sm font-roboto" id="desktop-menu">
                <!-- Conteúdo principal (SEM nofollow) -->
                {{-- <a href="/veiculos/" class="text-white hover:text-yellow-300 p-2">Veículos</a> --}}
                <a href="{{ route('info.category.index') }}"
                    class="text-white hover:text-yellow-300 p-2">Informações</a>
                {{-- <a href="/noticias/" class="text-white hover:text-yellow-300 p-2">Notícias</a>
                <a href="/comparativos/" class="text-white hover:text-yellow-300 p-2">Comparativos</a> --}}
                <a href="{{ url('/blog') }}" class="text-white hover:text-yellow-300 p-2">Blog</a>

                <div class="relative group">
                    <button class="text-white hover:text-yellow-300 p-2 flex items-center">
                        Sobre
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div
                        class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                        <div class="py-2">
                            <!-- PRIORIDADE 1: (SEM nofollow) -->
                            <a href="{{ route('about') }}"
                                class="block px-4 py-2 text-gray-800 hover:bg-gray-100 text-sm">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-3 text-blue-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                        </path>
                                    </svg>
                                    <div>
                                        <div class="font-medium">Sobre Nós</div>
                                        <div class="text-xs text-gray-500">Nossa história e propósito</div>
                                    </div>
                                </div>
                            </a>

                            <a href="{{ route('editorial-team') }}"
                                class="block px-4 py-2 text-gray-800 hover:bg-gray-100 text-sm">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-3 text-green-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                        </path>
                                    </svg>
                                    <div>
                                        <div class="font-medium">Nossa Equipe</div>
                                        <div class="text-xs text-gray-500">Especialistas automotivos</div>
                                    </div>
                                </div>
                            </a>

                            <a href="{{ route('editorial-methodology') }}"
                                class="block px-4 py-2 text-gray-800 hover:bg-gray-100 text-sm">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-3 text-purple-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div>
                                        <div class="font-medium">Metodologia Editorial</div>
                                        <div class="text-xs text-gray-500">Como garantimos qualidade</div>
                                    </div>
                                </div>
                            </a>

                            {{--
                            <hr class="my-2">

                            <a href="/sobre/historia" rel="nofollow"
                                class="block px-4 py-2 text-gray-800 hover:bg-gray-100 text-sm">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-3 text-orange-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div>
                                        <div class="font-medium">Nossa História</div>
                                        <div class="text-xs text-gray-500">Trajetória no setor</div>
                                    </div>
                                </div>
                            </a>

                            <a href="/sobre/parcerias" rel="nofollow"
                                class="block px-4 py-2 text-gray-800 hover:bg-gray-100 text-sm">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-3 text-red-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0H8m8 0v6l-3.5 3.5L8 12V6">
                                        </path>
                                    </svg>
                                    <div>
                                        <div class="font-medium">Parcerias</div>
                                        <div class="text-xs text-gray-500">Colaborações estratégicas</div>
                                    </div>
                                </div>
                            </a> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="mobile-menu" class="hidden bg-blue-700 pb-2 px-4" aria-labelledby="mobile-menu-button">
            <div class="flex flex-col space-y-2 text-sm font-roboto">
                {{-- <a href="/veiculos/" class="text-white hover:text-yellow-300 py-2">Veículos</a> --}}
                <a href="{{ route('info.category.index') }}"
                    class="text-white hover:text-yellow-300 py-2">Informações</a>
                {{-- <a href="/noticias/" class="text-white hover:text-yellow-300 py-2">Notícias</a> --}}
                {{-- <a href="/comparativos/" class="text-white hover:text-yellow-300 py-2">Comparativos</a> --}}
                <a href="{{ url('/blog')}}" class="text-white hover:text-yellow-300 py-2">Blog</a>

                <!-- Submenu Sobre no Mobile -->
                <div class="border-t border-blue-600 mt-2 pt-2">
                    <div class="text-blue-200 text-xs uppercase tracking-wide mb-2">Sobre</div>
                    <a href="{{ route('about') }}" class="text-white hover:text-yellow-300 py-1 pl-4 block">Sobre
                        Nós</a>
                    <a href="{{ route('editorial-team') }}"
                        class="text-white hover:text-yellow-300 py-1 pl-4 block">Nossa Equipe</a>
                    <a href="{{ route('editorial-team') }}"
                        class="text-white hover:text-yellow-300 py-1 pl-4 block">Metodologia</a>
                    {{-- <a href="/sobre/historia" rel="nofollow"
                        class="text-white hover:text-yellow-300 py-1 pl-4 block">História</a>
                    <a href="/sobre/parcerias" rel="nofollow"
                        class="text-white hover:text-yellow-300 py-1 pl-4 block">Parcerias</a> --}}
                </div>
            </div>
        </div>
    </nav>
</header>

@push('styles')

<style>
    /* Dropdown hover effect */
    .group:hover .group-hover\:opacity-100 {
        opacity: 1;
    }

    .group:hover .group-hover\:visible {
        visibility: visible;
    }

    /* Smooth transitions */
    .transition-all {
        transition-property: all;
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        transition-duration: 200ms;
    }

    /* Dropdown positioning */
    .absolute {
        position: absolute;
    }

    .right-0 {
        right: 0;
    }

    .mt-2 {
        margin-top: 0.5rem;
    }

    .z-50 {
        z-index: 50;
    }

    /* Mobile menu toggle */
    @media (max-width: 640px) {
        .group:hover .absolute {
            position: static;
            opacity: 1;
            visibility: visible;
            background: rgba(30, 64, 175, 0.9);
            margin-top: 0.5rem;
            border-radius: 0.5rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuButton = document.querySelector('[aria-controls="mobile-menu"]');
        const mobileMenu = document.getElementById('mobile-menu');
        
        if (mobileMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', function() {
                const isExpanded = mobileMenuButton.getAttribute('aria-expanded') === 'true';
                
                mobileMenuButton.setAttribute('aria-expanded', !isExpanded);
                mobileMenu.classList.toggle('hidden');
            });
        }
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            if (mobileMenu && !mobileMenu.contains(event.target) && !mobileMenuButton.contains(event.target)) {
                mobileMenu.classList.add('hidden');
                mobileMenuButton.setAttribute('aria-expanded', 'false');
            }
        });
    });
</script>
@endpush