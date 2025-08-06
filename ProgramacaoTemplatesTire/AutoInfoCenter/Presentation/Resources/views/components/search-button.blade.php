@props(['class' => ''])

<div class="search-component" id="search-component">
    <button 
        {{ $attributes->merge(['class' => 'text-yellow-400 p-2 '.$class, 'id' => 'search-button']) }}
        aria-label="Buscar no site">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
            stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
    </button>

    <!-- Modal de Busca -->
    <div
        id="search-modal"
        class="fixed inset-0 w-screen h-screen bg-black/85 z-50 flex items-center justify-center transform transition-all duration-300 scale-0 opacity-0 pointer-events-none">
        
        <div class="w-full max-w-3xl mx-2 md:mx-0.5 p-4 md:p-8 bg-white dark:bg-gray-800 rounded-lg shadow-2xl">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white font-montserrat">Buscar no Mercado Veículos</h2>
                <button id="close-search-modal" class="text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <form action="/busca" method="get">
                {{-- <form action="{{ route('search', [], false) }}" method="get"> --}}
                <div class="flex flex-col md:flex-row gap-3">
                    <div class="flex-grow">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input 
                                type="text" 
                                name="q" 
                                id="search-input"
                                class="w-full py-4 pl-10 pr-4 text-lg bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                                placeholder="O que você está procurando?" 
                                required>
                        </div>
                    </div>
                    <button type="submit" 
                        class="px-6 py-4 text-white bg-blue-900 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-lg transition duration-300 ease-in-out transform hover:scale-105">
                        Buscar
                    </button>
                </div>
                
    
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elementos do DOM
    const searchButton = document.getElementById('search-button');
    const searchModal = document.getElementById('search-modal');
    const closeButton = document.getElementById('close-search-modal');
    const searchInput = document.getElementById('search-input');
    const body = document.body;
    
    // Função para abrir o modal
    function openSearchModal() {
        searchModal.classList.remove('scale-0', 'opacity-0', 'pointer-events-none');
        searchModal.classList.add('scale-100', 'opacity-100', 'pointer-events-auto');
        body.style.overflow = 'hidden'; // Impede rolagem
        
        // Foca no input após a animação
        setTimeout(() => {
            searchInput.focus();
        }, 300);
    }
    
    // Função para fechar o modal
    function closeSearchModal() {
        searchModal.classList.remove('scale-100', 'opacity-100', 'pointer-events-auto');
        searchModal.classList.add('scale-0', 'opacity-0', 'pointer-events-none');
        body.style.overflow = ''; // Restaura rolagem
    }
    
    // Event listeners
    searchButton.addEventListener('click', function(e) {
        e.preventDefault();
        openSearchModal();
    });
    
    closeButton.addEventListener('click', function(e) {
        e.preventDefault();
        closeSearchModal();
    });
    
    // Fechar ao pressionar Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !searchModal.classList.contains('scale-0')) {
            closeSearchModal();
        }
    });
    
    // Fechar ao clicar fora do modal
    searchModal.addEventListener('click', function(e) {
        if (e.target === searchModal) {
            closeSearchModal();
        }
    });
});
</script>
@endpush