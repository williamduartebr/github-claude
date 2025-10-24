@php
$extractedEntities = $article->getData()['extracted_entities'] ?? [];
@endphp

@if(!empty($extractedEntities))
<section class="mb-12">
    <div class="bg-gradient-to-r from-gray-50 to-blue-50 rounded-xl p-6 border border-gray-200">
        <h3 class="text-xl font-semibold text-[#151C25] mb-4 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0E368A] mr-3" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            🚛 Informações do Veículo
        </h3>

        <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
            <div
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 divide-y md:divide-y-0 md:divide-x divide-gray-200">
                @if(!empty($extractedEntities['marca']))
                <div class="p-6 text-center">
                    <div class="flex flex-col items-center">
                        <div
                            class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Marca</dt>
                        <dd class="text-lg font-semibold text-gray-900">{{ $extractedEntities['marca'] }}</dd>
                    </div>
                </div>
                @endif

                @if(!empty($extractedEntities['modelo']))
                <div class="p-6 text-center">
                    <div class="flex flex-col items-center">
                        <div
                            class="h-12 w-12 rounded-full bg-gradient-to-br from-green-100 to-green-200 flex items-center justify-center mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Modelo</dt>
                        <dd class="text-lg font-semibold text-gray-900">{{ $extractedEntities['modelo'] }}</dd>
                    </div>
                </div>
                @endif

                @if(!empty($extractedEntities['categoria']))
                <div class="p-6 text-center">
                    <div class="flex flex-col items-center">
                        <div
                            class="h-12 w-12 rounded-full bg-gradient-to-br from-purple-100 to-purple-200 flex items-center justify-center mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                        </div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Categoria</dt>
                        <dd class="text-lg font-semibold text-gray-900">{{ $extractedEntities['categoria'] }}</dd>
                    </div>
                </div>
                @endif

                @if(!empty($extractedEntities['pneus']))
                <div class="p-6 text-center">
                    <div class="flex flex-col items-center">
                        <div
                            class="h-12 w-12 rounded-full bg-gradient-to-br from-orange-100 to-orange-200 flex items-center justify-center mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12a3 3 0 116 0 3 3 0 01-6 0z" />
                            </svg>
                        </div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Pneus</dt>
                        <dd class="text-lg font-semibold text-gray-900 font-mono">{{ $extractedEntities['pneus'] }}</dd>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endif