@if(!empty($article->preventive_maintenance) && is_array($article->preventive_maintenance))
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
        🔧 Manutenção Preventiva Especial para Híbridos
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Verificações mensais -->
        @if(!empty($article->preventive_maintenance['verificacoes_mensais']) &&
        is_array($article->preventive_maintenance['verificacoes_mensais']) &&
        count($article->preventive_maintenance['verificacoes_mensais']) > 0)
        <div class="bg-white rounded-lg border p-5 border-l-4 border-green-400">
            <div class="flex items-center mb-4">
                <div
                    class="h-12 w-12 rounded-full bg-gradient-to-br from-green-100 to-green-200 flex items-center justify-center mr-3">
                    <span class="text-2xl">📅</span>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Verificações Mensais</h3>
            </div>

            <ul class="space-y-2">
                @foreach($article->preventive_maintenance['verificacoes_mensais'] as $item)
                @if(!empty($item))
                <li class="flex items-start">
                    <div
                        class="h-5 w-5 rounded-full bg-green-100 flex items-center justify-center mr-2 flex-shrink-0 mt-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-green-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <p class="text-sm text-gray-700">{{ $item }}</p>
                </li>
                @endif
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Verificações trimestrais -->
        @if(!empty($article->preventive_maintenance['verificacoes_trimestrais']) &&
        is_array($article->preventive_maintenance['verificacoes_trimestrais']) &&
        count($article->preventive_maintenance['verificacoes_trimestrais']) > 0)
        <div class="bg-white rounded-lg border p-5 border-l-4 border-blue-400">
            <div class="flex items-center mb-4">
                <div
                    class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center mr-3">
                    <span class="text-2xl">🔄</span>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Verificações Trimestrais</h3>
            </div>

            <ul class="space-y-2">
                @foreach($article->preventive_maintenance['verificacoes_trimestrais'] as $item)
                @if(!empty($item))
                <li class="flex items-start">
                    <div
                        class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mr-2 flex-shrink-0 mt-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-blue-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <p class="text-sm text-gray-700">{{ $item }}</p>
                </li>
                @endif
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Verificações anuais -->
        @if(!empty($article->preventive_maintenance['verificacoes_anuais']) &&
        is_array($article->preventive_maintenance['verificacoes_anuais']) &&
        count($article->preventive_maintenance['verificacoes_anuais']) > 0)
        <div class="bg-white rounded-lg border p-5 border-l-4 border-yellow-400">
            <div class="flex items-center mb-4">
                <div
                    class="h-12 w-12 rounded-full bg-gradient-to-br from-yellow-100 to-yellow-200 flex items-center justify-center mr-3">
                    <span class="text-2xl">⚡</span>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Verificações Anuais</h3>
            </div>

            <ul class="space-y-2">
                @foreach($article->preventive_maintenance['verificacoes_anuais'] as $item)
                @if(!empty($item))
                <li class="flex items-start">
                    <div
                        class="h-5 w-5 rounded-full bg-yellow-100 flex items-center justify-center mr-2 flex-shrink-0 mt-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-yellow-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <p class="text-sm text-gray-700">{{ $item }}</p>
                </li>
                @endif
                @endforeach
            </ul>
        </div>
        @endif
    </div>
</section>
@endif