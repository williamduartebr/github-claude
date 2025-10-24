@if(!empty($article->oil_specifications) && is_array($article->oil_specifications) &&
count($article->oil_specifications) > 0)
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
        Especificações Detalhadas por Tipo de Óleo
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($article->oil_specifications as $spec)
        @if(!empty($spec['tipo_oleo']))
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            <div class="bg-[#0E368A] text-white px-4 py-3">
                <h3 class="font-medium">{{ $spec['tipo_oleo'] }}</h3>
            </div>
            <div class="p-5">
                <div class="flex items-center mb-4">
                    <div class="bg-[#0E368A]/10 w-12 h-12 rounded-full flex items-center justify-center mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0E368A]" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900">{{ $spec['aplicacao'] ?? 'Aplicação' }}</h4>
                        <p class="text-sm text-gray-600 mt-1">Especificações técnicas</p>
                    </div>
                </div>

                @if(!empty($spec['caracteristicas']) && is_array($spec['caracteristicas']) &&
                count($spec['caracteristicas']) > 0)
                <ul class="space-y-2 text-gray-700 mb-4">
                    @foreach($spec['caracteristicas'] as $caracteristica)
                    @if(!empty($caracteristica))
                    <li class="flex items-start">
                        <div
                            class="flex-shrink-0 h-5 w-5 rounded-full bg-[#0E368A]/20 flex items-center justify-center mt-0.5 mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-[#0E368A]" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <span>{{ $caracteristica }}</span>
                    </li>
                    @endif
                    @endforeach
                </ul>
                @endif

                @if(!empty($spec['marcas_recomendadas']))
                <div class="mt-4 bg-gray-50 p-3 rounded-md text-sm">
                    <span class="font-medium">Marcas recomendadas:</span>
                    @if(is_array($spec['marcas_recomendadas']))
                    {{ implode(', ', $spec['marcas_recomendadas']) }}
                    @else
                    {{ $spec['marcas_recomendadas'] }}
                    @endif
                </div>
                @endif
            </div>
        </div>
        @endif
        @endforeach
    </div>
</section>
@endif