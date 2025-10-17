{{--
Componente: CONCLUSION (Conclusão Final)

Uso: Resumo e fechamento do artigo com visual impactante

Estrutura esperada (baseada nos JSONs reais):
{
  "block_type": "conclusion",
  "heading": "Conclusão: Vale a Pena?",
  "content": {
    "summary": "Resumo do artigo em 2-3 linhas",
    "key_takeaways": [
      "Principal aprendizado 1",
      "Principal aprendizado 2"
    ],
    "key_takeaway": "Alternativa singular (opcional)",
    "final_thought": "Pensamento final e recomendação",
    "cta": "Call-to-action (opcional)",
    "call_to_action": "Alternativa para CTA (opcional)"
  }
}

@author Claude Sonnet 4.5
@version 2.0 - Design moderno e limpo
--}}

<div class="mb-10">
    {{-- Container principal com gradiente sutil --}}
    <div class="relative bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 rounded-2xl shadow-xl p-8 md:p-10 border border-blue-200 overflow-hidden">
        
        {{-- Decoração de fundo --}}
        <div class="absolute top-0 right-0 w-64 h-64 bg-blue-100 rounded-full opacity-20 blur-3xl -mr-32 -mt-32"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-indigo-100 rounded-full opacity-20 blur-3xl -ml-32 -mb-32"></div>
        
        {{-- Conteúdo principal --}}
        <div class="relative z-10">
            {{-- Ícone e Heading --}}
            <div class="flex items-center justify-center mb-6">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg transform -rotate-3 hover:rotate-0 transition-transform duration-300">
                    <svg class="w-9 h-9 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            @if(!empty($block['heading']))
                <h2 class="text-3xl md:text-4xl font-bold text-center text-gray-900 mb-8">
                    {{ $block['heading'] }}
                </h2>
            @else
                <h2 class="text-3xl md:text-4xl font-bold text-center text-gray-900 mb-8">
                    Conclusão
                </h2>
            @endif

            {{-- Summary --}}
            @if(!empty($block['content']['summary']))
                <div class="mb-8">
                    <p class="text-lg md:text-xl text-gray-700 leading-relaxed text-center max-w-4xl mx-auto">
                        {{ $block['content']['summary'] }}
                    </p>
                </div>
            @endif

            {{-- Key Takeaways --}}
            @php
                $takeaways = $block['content']['key_takeaways'] ?? [];
                $singleTakeaway = $block['content']['key_takeaway'] ?? null;
            @endphp

            @if(!empty($takeaways) && is_array($takeaways))
                <div class="bg-white/80 backdrop-blur-sm rounded-xl p-6 md:p-8 mb-8 shadow-md border border-blue-200">
                    <div class="flex items-center mb-5">
                        <svg class="h-6 w-6 text-blue-600 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        <p class="text-sm font-bold text-blue-900 uppercase tracking-wider">
                            Principais Conclusões
                        </p>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-3">
                        @foreach($takeaways as $takeaway)
                            <div class="flex items-start group">
                                <div class="flex-shrink-0 mt-1">
                                    <div class="w-6 h-6 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                                        <svg class="w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                                <p class="ml-3 text-gray-800 leading-relaxed">
                                    {{ $takeaway }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif(!empty($singleTakeaway))
                <div class="bg-white/80 backdrop-blur-sm rounded-xl p-6 md:p-8 mb-8 shadow-md border border-blue-200">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-blue-600 mr-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-blue-600 font-bold uppercase tracking-wide mb-2">
                                💡 Principal Conclusão
                            </p>
                            <p class="text-gray-900 font-semibold text-lg leading-relaxed">
                                {{ $singleTakeaway }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Final Thought --}}
            @if(!empty($block['content']['final_thought']))
                <div class="mb-8">
                    <p class="text-base md:text-lg text-gray-700 leading-relaxed text-center max-w-4xl mx-auto">
                        {{ $block['content']['final_thought'] }}
                    </p>
                </div>
            @endif

            {{-- Call to Action --}}
            @php
                $cta = $block['content']['cta'] ?? $block['content']['call_to_action'] ?? null;
            @endphp

            @if(!empty($cta))
                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl p-6 text-center shadow-lg">
                    <p class="text-white text-base md:text-lg leading-relaxed font-medium">
                        {{ $cta }}
                    </p>
                </div>
            @endif

            {{-- Footer Info --}}
            <div class="mt-8 pt-6 border-t-2 border-blue-200">
                <div class="flex flex-wrap items-center justify-center gap-6 text-sm text-gray-600">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-blue-500 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                        </svg>
                        <span class="font-medium">Artigo Atualizado</span>
                    </div>
                    <span class="text-gray-400">•</span>
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-green-500 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span class="font-medium">Informações Verificadas</span>
                    </div>
                    <span class="text-gray-400">•</span>
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-purple-500 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span class="font-medium">Baseado em Testes Reais</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>