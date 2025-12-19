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

<!-- DISCLAIMER IMPORTANTE -->
<div class="container mx-auto px-4 sm:px-6 lg:px-8 mt-6">
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg shadow-sm">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                    fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-semibold text-yellow-800">
                    Importante: As informações são para fins informativos. Consulte sempre o manual do seu veículo e um
                    profissional qualificado antes de realizar manutenções.
                </p>
            </div>
        </div>
    </div>
</div>

{{-- BANNER --}}
<div class="container mx-auto px-4 sm:px-6 lg:px-8 my-10">
    <div class="w-full bg-gray-300 rounded-lg flex items-center justify-center" style="min-height: 280px;">
        <span class="text-gray-700 text-sm font-roboto">Banner - Mock Ad</span>
    </div>
</div>