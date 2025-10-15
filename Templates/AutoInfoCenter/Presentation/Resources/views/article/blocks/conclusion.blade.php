{{--
Componente: CONCLUSION (Conclusão Final)

Uso: Resumo e fechamento do artigo

Estrutura esperada:
{
  "block_type": "conclusion",
  "content": {
    "summary": "Resumo do artigo em 2-3 linhas",
    "key_takeaway": "Principal aprendizado do artigo",
    "cta": "Call-to-action implícito (opcional)"
  }
}

@author Claude Sonnet 4
@version 1.0
--}}

<div class="mb-8">
    {{-- Container principal --}}
    <div class="bg-gradient-to-br from-blue-50 via-blue-100 to-indigo-100 border-2 border-blue-300 rounded-xl shadow-lg p-8">
        {{-- Ícone decorativo --}}
        <div class="flex justify-center mb-6">
            <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center shadow-md">
                <svg class="w-10 h-10 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        {{-- Heading --}}
        <h2 class="text-2xl font-bold text-center text-[#151C25] mb-6">
            Conclusão
        </h2>

        {{-- Summary --}}
        @if(!empty($block['content']['summary']))
            <div class="mb-6">
                <p class="text-lg text-gray-800 leading-relaxed text-center">
                    {!! nl2br(e($block['content']['summary'])) !!}
                </p>
            </div>
        @endif

        {{-- Key Takeaway (Destaque) --}}
        @if(!empty($block['content']['key_takeaway']))
            <div class="bg-white border-2 border-blue-400 rounded-lg p-6 mb-6 shadow-md">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-500 mr-3 mt-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-blue-600 font-bold uppercase tracking-wide mb-2">
                            💡 Principal Aprendizado
                        </p>
                        <p class="text-gray-900 font-semibold leading-relaxed">
                            {{ $block['content']['key_takeaway'] }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- CTA (opcional) --}}
        @if(!empty($block['content']['cta']))
            <div class="text-center">
                <p class="text-gray-700 leading-relaxed">
                    {{ $block['content']['cta'] }}
                </p>
            </div>
        @endif

        {{-- Rodapé decorativo --}}
        <div class="mt-6 pt-6 border-t-2 border-blue-300">
            <div class="flex items-center justify-center space-x-4 text-sm text-gray-600">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-blue-500 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                    </svg>
                    <span>Artigo atualizado</span>
                </div>
                <span>•</span>
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-blue-500 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    <span>Informações verificadas</span>
                </div>
            </div>
        </div>
    </div>
</div>