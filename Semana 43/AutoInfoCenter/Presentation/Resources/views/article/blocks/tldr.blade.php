{{--
Componente: TLDR (Too Long; Didn't Read - Resposta Rápida)

Uso: Caixa de resposta rápida/resumo executivo do artigo

Estrutura esperada:
{
  "block_type": "tldr",
  "heading": "Resposta Rápida",
  "content": {
    "answer": "Resposta direta e objetiva em 1-2 linhas",
    "key_points": [
      "Ponto principal 1",
      "Ponto principal 2",
      "Ponto principal 3"
    ]
  }
}

@author Claude Sonnet 4.5
@version 2.0 - Design moderno com gradientes e animações
--}}

@if(!empty($block['heading']))
    <hr class="my-12 border-t border-gray-200" />
@endif

<div class="relative mb-10">
    {{-- Container principal com gradiente --}}
    <div class="relative bg-gradient-to-br from-blue-500 via-blue-600 to-indigo-700 rounded-2xl shadow-2xl overflow-hidden">
        {{-- Padrão decorativo de fundo --}}
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white rounded-full transform translate-x-1/2 -translate-y-1/2"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-white rounded-full transform -translate-x-1/2 translate-y-1/2"></div>
        </div>

        <div class="relative p-8">
            {{-- Heading com ícone --}}
            @if(!empty($block['heading']))
                <div class="flex items-center mb-6">
                    <div class="flex-shrink-0 w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center mr-4 shadow-lg">
                        <svg class="h-7 w-7 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-white">
                        {{ $block['heading'] }}
                    </h3>
                </div>
            @endif

            {{-- Resposta direta em destaque --}}
            @if(!empty($block['content']['answer']))
                <div class="bg-white/95 backdrop-blur-sm rounded-xl p-6 mb-6 shadow-xl border border-white/20">
                    <p class="text-lg text-gray-900 font-semibold leading-relaxed">
                        {{ $block['content']['answer'] }}
                    </p>
                </div>
            @endif

            {{-- Pontos-chave em cards modernos --}}
            @if(!empty($block['content']['key_points']) && is_array($block['content']['key_points']))
                <div class="space-y-3">
                    @foreach($block['content']['key_points'] as $index => $point)
                        <div class="group bg-white/90 backdrop-blur-sm rounded-lg p-4 hover:bg-white hover:shadow-lg transition-all duration-300 border border-white/30">
                            <div class="flex items-start">
                                {{-- Número do ponto --}}
                                <div class="flex-shrink-0 w-8 h-8 bg-gradient-to-br from-green-400 to-emerald-500 rounded-lg flex items-center justify-center mr-4 shadow-md group-hover:scale-110 transition-transform">
                                    <span class="text-white font-bold text-sm">{{ $index + 1 }}</span>
                                </div>
                                
                                {{-- Texto do ponto --}}
                                <p class="text-gray-800 leading-relaxed flex-1 pt-1">
                                    {{ $point }}
                                </p>

                                {{-- Ícone de check animado no hover --}}
                                <svg class="h-6 w-6 text-green-500 opacity-0 group-hover:opacity-100 transition-opacity ml-2 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Footer com call-to-action --}}
            <div class="mt-6 pt-6 border-t border-white/20">
                <div class="flex items-center justify-between text-white/90">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-sm font-semibold">
                            TL;DR
                        </span>
                    </div>
                    <div class="flex items-center">
                        <svg class="h-4 w-4 mr-2 animate-pulse" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-sm">Continue lendo para detalhes completos</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Badge decorativo "Resposta Rápida" --}}
    <div class="absolute -top-4 -right-4 bg-gradient-to-br from-yellow-400 to-orange-500 text-white px-6 py-2 rounded-full shadow-xl transform rotate-12 hidden md:block">
        <div class="flex items-center">
            <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
            </svg>
            <span class="font-bold text-sm uppercase tracking-wider">Quick Answer</span>
        </div>
    </div>
</div>