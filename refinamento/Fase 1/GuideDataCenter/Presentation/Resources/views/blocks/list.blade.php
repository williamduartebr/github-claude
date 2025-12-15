{{--
Componente: LIST (Lista)

Uso: Lista com bullets ou ícones

Estrutura esperada:
- heading: string
- items: array (strings)
- icon: string (opcional - default: check)

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
        <ul class="space-y-3 font-roboto">
            @foreach($block['data']['items'] as $item)
            <li class="flex items-start">
                <svg class="h-5 w-5 text-blue-500 mr-2 mt-0.5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                <span class="text-sm md:text-base text-gray-700">{{ $item }}</span>
            </li>
            @endforeach
        </ul>
        @endif
    </section>
</div>