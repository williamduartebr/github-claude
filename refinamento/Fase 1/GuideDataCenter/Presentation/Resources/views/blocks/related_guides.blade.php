{{--
Componente: RELATED_GUIDES (Guias Relacionados)

Uso: Grid com outros guias do mesmo veículo

Estrutura esperada:
- heading: string
- guides: array [{name, icon, url}]

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

        @if(!empty($block['data']['guides']) && is_array($block['data']['guides']))
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 text-sm font-roboto">
            @foreach($block['data']['guides'] as $guide)
            <a href="{{ $guide['url'] ?? '#' }}"
                class="block bg-gray-50 border border-gray-200 p-3 rounded hover:shadow hover:border-blue-500 transition-all text-center">
                @if(!empty($guide['icon']))
                <span class="text-2xl mb-1 block">{{ $guide['icon'] }}</span>
                @endif
                <span class="text-sm">{{ $guide['name'] ?? '' }}</span>
            </a>
            @endforeach
        </div>
        @endif
    </section>
</div>