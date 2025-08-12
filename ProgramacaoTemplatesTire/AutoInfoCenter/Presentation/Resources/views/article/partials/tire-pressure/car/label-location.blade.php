{{-- 
Partial: tire-pressure/car/label-location.blade.php
Seção sobre localização da etiqueta de pressão dos pneus
--}}

@php
    $labelLocation = $article->getData()['label_location'] ?? [];
    $vehicleInfo = $article->getData()['vehicle_info'] ?? [];
@endphp

@if(!empty($labelLocation))
<section class="mb-12">
    <div class="bg-gradient-to-br from-amber-600 to-orange-700 text-white rounded-lg p-8 relative overflow-hidden">
        <!-- Ícone decorativo -->
        <div class="absolute top-4 right-4 text-6xl opacity-20">
            🏷️
        </div>

        <div class="relative z-10">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold">Onde Encontrar a Etiqueta</h2>
                    <p class="text-amber-100 text-sm">
                        Localização oficial das especificações no {{ $vehicleInfo['full_name'] ?? 'seu veículo' }}
                    </p>
                </div>
            </div>

            <!-- Localização Principal -->
            @if(!empty($labelLocation['primary_location']))
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6 border border-white/20">
                <div class="flex items-center mb-3">
                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mr-3">
                        <span class="text-white font-bold text-sm">1°</span>
                    </div>
                    <h3 class="font-semibold text-lg">Local Principal</h3>
                </div>
                <p class="text-amber-100 text-lg font-medium mb-2">
                    {{ $labelLocation['primary_location']['location'] ?? 'Batente da porta do motorista' }}
                </p>
                <p class="text-amber-200 text-sm">
                    {{ $labelLocation['primary_location']['description'] ?? 'Abra a porta do motorista e procure na lateral do batente (parte do chassi)' }}
                </p>
            </div>
            @endif
        </div>
    </div>

    <!-- Localizações Alternativas -->
    @if(!empty($labelLocation['alternative_locations']))
    <div class="mt-6 bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-6 h-6 text-orange-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
            </svg>
            Localizações Alternativas
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($labelLocation['alternative_locations'] as $index => $location)
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center mb-3">
                    <div class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center mr-3">
                        <span class="text-white font-bold text-sm">{{ $index + 2 }}°</span>
                    </div>
                    <h4 class="font-semibold text-gray-800">{{ $location['title'] ?? 'Local Alternativo' }}</h4>
                </div>
                <p class="text-gray-700 text-sm mb-2 font-medium">
                    {{ $location['location'] ?? 'Tampa do combustível' }}
                </p>
                <p class="text-gray-600 text-xs">
                    {{ $location['description'] ?? 'Verifique na parte interna da tampa do combustível' }}
                </p>
                @if(!empty($location['probability']))
                <div class="mt-2">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                        {{ $location['probability'] === 'alta' ? 'bg-green-100 text-green-800' : 
                           ($location['probability'] === 'média' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                        Probabilidade {{ $location['probability'] }}
                    </span>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Guia Visual -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            Como Identificar a Etiqueta
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Características Visuais -->
            <div>
                <h4 class="font-semibold text-blue-800 mb-3">🔍 Características da Etiqueta:</h4>
                <ul class="space-y-2 text-sm text-blue-700">
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 mt-2"></span>
                        <span>Adesivo retangular ou quadrado, geralmente branco</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 mt-2"></span>
                        <span>Contém números em PSI, Bar ou kPa</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 mt-2"></span>
                        <span>Pode ter desenho de pneu ou carro</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 mt-2"></span>
                        <span>Texto em várias línguas (multiidioma)</span>
                    </li>
                </ul>
            </div>

            <!-- Informações na Etiqueta -->
            <div>
                <h4 class="font-semibold text-blue-800 mb-3">📋 Informações Presentes:</h4>
                <ul class="space-y-2 text-sm text-blue-700">
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 mt-2"></span>
                        <span>Pressão para pneus dianteiros</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 mt-2"></span>
                        <span>Pressão para pneus traseiros</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 mt-2"></span>
                        <span>Medida dos pneus originais</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 mt-2"></span>
                        <span>Pressão para carga completa</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 mt-2"></span>
                        <span>Pressão do pneu estepe</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Dicas de Busca -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Dica 1 -->
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center mb-2">
                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mr-3">
                    <span class="text-white text-sm">💡</span>
                </div>
                <h4 class="font-semibold text-green-800">Use Lanterna</h4>
            </div>
            <p class="text-green-700 text-sm">
                A etiqueta pode estar em local escuro. Use a lanterna do celular para facilitar a localização.
            </p>
        </div>

        <!-- Dica 2 -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-center mb-2">
                <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center mr-3">
                    <span class="text-white text-sm">🔄</span>
                </div>
                <h4 class="font-semibold text-yellow-800">Procure dos Dois Lados</h4>
            </div>
            <p class="text-yellow-700 text-sm">
                Verifique tanto a porta do motorista quanto a do passageiro. Pode estar em qualquer lado.
            </p>
        </div>

        <!-- Dica 3 -->
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
            <div class="flex items-center mb-2">
                <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center mr-3">
                    <span class="text-white text-sm">📖</span>
                </div>
                <h4 class="font-semibold text-purple-800">Manual do Proprietário</h4>
            </div>
            <p class="text-purple-700 text-sm">
                Se não encontrar, consulte o manual. Pode ter diagrama com a localização exata.
            </p>
        </div>
    </div>

    <!-- Nota Especial -->
    @if(!empty($labelLocation['note']))
    <div class="mt-6 bg-amber-50 border border-amber-200 rounded-lg p-4">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-amber-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <div class="text-sm">
                <p class="font-medium text-amber-800 mb-1">📌 Nota Específica para {{ $vehicleInfo['full_name'] ?? 'este modelo' }}:</p>
                <p class="text-amber-700">{{ $labelLocation['note'] }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- E se não encontrar -->
    <div class="mt-6 bg-red-50 border border-red-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-red-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            E se não encontrar a etiqueta?
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <h4 class="font-semibold text-red-800 mb-3">🆘 Alternativas:</h4>
                <ul class="space-y-2 text-sm text-red-700">
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2 mt-2"></span>
                        <span>Use as pressões desta página (dados oficiais)</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2 mt-2"></span>
                        <span>Consulte concessionária autorizada</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2 mt-2"></span>
                        <span>Verifique manual do proprietário</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2 mt-2"></span>
                        <span>Etiqueta pode ter sido removida ou danificada</span>
                    </li>
                </ul>
            </div>

            <div>
                <h4 class="font-semibold text-red-800 mb-3">⚠️ Cuidados:</h4>
                <ul class="space-y-2 text-sm text-red-700">
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2 mt-2"></span>
                        <span>Não use pressões de outros modelos</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2 mt-2"></span>
                        <span>Pressões incorretas comprometem segurança</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2 mt-2"></span>
                        <span>Evite "chutes" ou aproximações</span>
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2 mt-2"></span>
                        <span>Quando em dúvida, procure ajuda profissional</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>
@endif