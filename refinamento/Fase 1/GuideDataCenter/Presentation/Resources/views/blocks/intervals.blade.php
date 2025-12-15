{{--
Componente: INTERVALS (Intervalos/Condições)

Uso: Intervalos de troca/manutenção ou condições de uso

Estrutura esperada:
- heading: string
- conditions: array [{label, value}]
- note: string (opcional)

@author Claude Sonnet 4.5
@version 1.0
--}}

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-2">
    <section class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 mb-8">
        @if(!empty($block['data']['heading']))
        <h2 class="text-xl font-bold mb-4 font-montserrat">
            {{ $block['data']['heading'] }}
        </h2>
        @endif

        @if(!empty($block['data']['conditions']) && is_array($block['data']['conditions']))
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm font-roboto">
            @foreach($block['data']['conditions'] as $condition)
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <p class="font-medium text-gray-900">{{ $condition['label'] ?? '' }}</p>
                <p class="text-xs text-gray-600 mt-1">{{ $condition['value'] ?? '' }}</p>
            </div>
            @endforeach
        </div>
        @endif

        @if(!empty($block['data']['note']))
        <div class="mt-4 p-3 bg-yellow-50 border-l-4 border-yellow-400 rounded-r">
            <p class="text-xs text-yellow-800 font-roboto">
                ⚠️ <strong>{{ $block['data']['note'] }}</strong>
            </p>
        </div>
        @endif
    </section>
</div>