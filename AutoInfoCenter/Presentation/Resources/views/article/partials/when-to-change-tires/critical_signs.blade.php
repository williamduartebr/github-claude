@if(!empty($article->critical_signs) && is_array($article->critical_signs) && count($article->critical_signs) > 0)
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
        Sinais Críticos para Substituição Imediata
    </h2>

    <div class="space-y-6">
        @foreach($article->critical_signs as $sign)
        @if(!empty($sign['title']))
        <div class="bg-red-50 border border-red-200 rounded-lg p-6">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0 h-8 w-8 rounded-full bg-red-100 flex items-center justify-center mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-red-900">{{ $sign['title'] }}</h3>
            </div>

            @if(!empty($sign['legal_limit']) || !empty($sign['recommended_limit']))
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                @if(!empty($sign['legal_limit']))
                <div class="bg-white p-3 rounded border">
                    <span class="text-sm font-medium text-gray-700">Limite Legal:</span>
                    <span class="text-lg font-bold text-red-600 ml-2">{{ $sign['legal_limit'] }}</span>
                </div>
                @endif
                @if(!empty($sign['recommended_limit']))
                <div class="bg-white p-3 rounded border">
                    <span class="text-sm font-medium text-gray-700">Limite Recomendado:</span>
                    <span class="text-lg font-bold text-orange-600 ml-2">{{ $sign['recommended_limit'] }}</span>
                </div>
                @endif
            </div>
            @endif

            @if(!empty($sign['test']))
            <div class="mb-4">
                <p class="text-sm text-gray-700"><strong>Como testar:</strong> {{ $sign['test'] }}</p>
            </div>
            @endif

            @if(!empty($sign['types']) && is_array($sign['types']) && count($sign['types']) > 0)
            <div class="mb-4">
                <p class="text-sm font-medium text-gray-700 mb-2">Tipos de danos estruturais:</p>
                <ul class="space-y-1">
                    @foreach($sign['types'] as $type)
                    @if(!empty($type))
                    <li class="flex items-center text-sm text-gray-600">
                        <span class="text-red-500 mr-2">•</span>
                        {{ $type }}
                    </li>
                    @endif
                    @endforeach
                </ul>
            </div>
            @endif

            @if(!empty($sign['patterns']) && is_array($sign['patterns']) && count($sign['patterns']) > 0)
            <div class="mb-4">
                <p class="text-sm font-medium text-gray-700 mb-2">Padrões de desgaste irregular:</p>
                <ul class="space-y-1">
                    @foreach($sign['patterns'] as $pattern)
                    @if(!empty($pattern))
                    <li class="flex items-center text-sm text-gray-600">
                        <span class="text-orange-500 mr-2">•</span>
                        {{ $pattern }}
                    </li>
                    @endif
                    @endforeach
                </ul>
            </div>
            @endif

            @if(!empty($sign['action']))
            <div class="bg-white border border-red-200 p-3 rounded">
                <p class="text-sm text-red-800"><strong>Ação obrigatória:</strong> {{ $sign['action'] }}</p>
            </div>
            @endif
        </div>
        @endif
        @endforeach
    </div>
</section>
@endif