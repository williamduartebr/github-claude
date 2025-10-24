@if(!empty($article->critical_parts) && is_array($article->critical_parts) &&
count($article->critical_parts) > 0)
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
        ⚠️ Componentes Críticos em Veículos Elétricos
    </h2>

    <div class="bg-white rounded-lg border shadow-sm p-6 border-l-4 border-blue-500">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($article->critical_parts as $part)
            @if(!empty($part['componente']))
            <div class="flex items-start p-4 bg-blue-50 rounded-lg border border-blue-200">
                <div
                    class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center mr-3 flex-shrink-0 mt-1">
                    <span class="text-lg">🔋</span>
                </div>
                <div>
                    <h3 class="font-medium text-gray-900 mb-1">{{ $part['componente'] }}</h3>
                    @if(!empty($part['intervalo_recomendado']))
                    <p class="text-sm text-gray-700 mb-2">
                        <span class="font-medium">🔧 Recomendação:</span> {{ $part['intervalo_recomendado']
                        }}
                    </p>
                    @endif
                    @if(!empty($part['observacao']))
                    <p class="text-sm text-gray-600">{{ $part['observacao'] }}</p>
                    @endif
                </div>
            </div>
            @endif
            @endforeach
        </div>
    </div>
</section>
@endif