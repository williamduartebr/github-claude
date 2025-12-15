{{--
Componente: HERO (Cabeçalho)

Uso: Título, descrição e badges do guia

Estrutura esperada:
- title: string
- description: string
- badges: array [{text, color}]

@author Claude Sonnet 4.5
@version 1.0
--}}

<section class="bg-white border-b border-gray-200">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-2">
        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3 font-montserrat mt-6">
            {{ $block['data']['title'] ?? '' }}
        </h1>

        @if(!empty($block['data']['description']))
        <p class="text-sm md:text-base text-gray-600 max-w-2xl font-roboto mb-4">
            {{ $block['data']['description'] }}
        </p>
        @endif

        {{-- BADGES DE QUALIDADE --}}
        @if(!empty($block['data']['badges']) && is_array($block['data']['badges']))
        <div class="flex flex-wrap items-center gap-3 mb-4">
            @foreach($block['data']['badges'] as $badge)
            @php
            $colors = [
            'green' => 'bg-green-100 text-green-800 border-green-200',
            'blue' => 'bg-blue-100 text-blue-800 border-blue-200',
            'yellow' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'red' => 'bg-red-100 text-red-800 border-red-200',
            ];
            $colorClass = $colors[$badge['color'] ?? 'blue'] ?? 'bg-gray-100 text-gray-800 border-gray-200';
            @endphp
            <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border {{ $colorClass }}">
                {{ $badge['text'] }}
            </div>
            @endforeach
        </div>
        @endif
    </div>
</section>

{{-- BANNER --}}
<div class="container mx-auto px-4 my-10">
    <div class="w-full bg-gray-300 rounded-lg flex items-center justify-center" style="min-height: 280px;">
        <span class="text-gray-700 text-sm font-roboto">Banner - Mock Ad</span>
    </div>
</div>