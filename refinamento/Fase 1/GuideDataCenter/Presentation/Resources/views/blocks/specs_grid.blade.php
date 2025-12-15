{{--
Componente: SPECS_GRID (Grid de Especificações)

Uso: Grid com especificações técnicas (3-4 colunas)

Estrutura esperada:
- heading: string (opcional)
- specs: array [{label, value}]
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

        @if(!empty($block['data']['specs']) && is_array($block['data']['specs']))
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm font-roboto">
            @foreach($block['data']['specs'] as $spec)
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <p class="font-medium text-gray-900">{{ $spec['label'] ?? '' }}</p>
                <p class="text-xs text-gray-600 mt-1">{{ $spec['value'] ?? '' }}</p>
            </div>
            @endforeach
        </div>
        @endif

        @if(!empty($block['data']['note']))
        <div class="mt-4 p-3 bg-blue-50 border-l-4 border-blue-500 rounded-r">
            <p class="text-xs text-blue-800 font-roboto">
                💡 {{ $block['data']['note'] }}
            </p>
        </div>
        @endif
    </section>
</div>