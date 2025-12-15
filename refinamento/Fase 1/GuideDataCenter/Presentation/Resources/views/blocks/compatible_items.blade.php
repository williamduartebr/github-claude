{{--
Componente: COMPATIBLE_ITEMS (Itens Compatíveis)

Uso: Lista de produtos/itens compatíveis

Estrutura esperada:
- heading: string
- items: array [{name, spec}]
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

        @if(!empty($block['data']['items']) && is_array($block['data']['items']))
        <ul class="space-y-2 text-sm text-gray-700 font-roboto">
            @foreach($block['data']['items'] as $item)
            <li class="bg-gray-50 border border-gray-200 p-3 rounded flex items-center">
                <svg class="h-4 w-4 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>
                    <strong>{{ $item['name'] ?? '' }}</strong>
                    @if(!empty($item['spec']))
                    — {{ $item['spec'] }}
                    @endif
                </span>
            </li>
            @endforeach
        </ul>
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