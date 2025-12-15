{{--
Componente: TEXT (Texto Corrido)

Uso: Bloco de texto com heading opcional

Estrutura esperada:
- heading: string (opcional)
- content: string

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

        @if(!empty($block['data']['content']))
        <div class="prose max-w-none">
            <p class="text-sm md:text-base text-gray-700 leading-relaxed font-roboto">
                {!! nl2br(e($block['data']['content'])) !!}
            </p>
        </div>
        @endif
    </section>
</div>