{{--
Componente: CLUSTER (Links Essenciais)

Uso: Links para conteúdos essenciais do veículo

Estrutura esperada:
- heading: string
- items: array [{title, icon, url}]

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
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-sm font-roboto">
            @foreach($block['data']['items'] as $item)
            <a href="{{ $item['url'] ?? '#' }}"
                class="block bg-gray-50 border border-gray-200 rounded p-4 hover:shadow-sm hover:border-blue-500 transition-all">
                @if(!empty($item['icon']))
                {{ $item['icon'] }}
                @endif
                <strong>{{ $item['title'] ?? '' }}</strong>
            </a>
            @endforeach
        </div>
        @endif
    </section>
</div>