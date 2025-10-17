{{--
Componente: FAQ (Perguntas Frequentes)

Uso: Seção de perguntas e respostas com accordion nativo e Schema.org

Estrutura esperada:
{
  "block_type": "faq",
  "heading": "Perguntas Frequentes",
  "content": {
    "questions": [
      {
        "question": "Pergunta do usuário?",
        "answer": "Resposta completa e prática",
        "related_topics": ["Tópico 1", "Tópico 2"]
      }
    ]
  }
}

@author Claude Sonnet 4.5
@version 3.0 - Accordion nativo HTML com <details> e Schema.org
--}}
@if(!empty($block['heading']))
    <hr class="my-12 border-t border-gray-200" />
@endif

<div class="my-10">
    {{-- Heading --}}
    @if(!empty($block['heading']))
        <h2 class="text-2xl font-bold text-[#151C25] mb-4 flex items-center">
            <svg class="h-7 w-7 text-[#EC6608] mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ $block['heading'] }}
        </h2>
    @endif

    {{-- Questions Accordion --}}
    @if(!empty($block['content']['questions']) && is_array($block['content']['questions']))
        <div class="space-y-4">
            @foreach($block['content']['questions'] as $index => $faq)
                <details class="group bg-white border-2 border-gray-200 rounded-xl shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden">
                    
                    {{-- Question (Summary) --}}
                    <summary class="flex items-center justify-between cursor-pointer p-5 bg-gradient-to-r from-blue-50 to-blue-100 group-open:bg-gradient-to-r group-open:from-blue-100 group-open:to-blue-200 transition-colors select-none">
                        <div class="flex items-start flex-1 pr-4">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-[#EC6608] text-white flex items-center justify-center font-bold text-sm mr-3 mt-0.5">
                                {{ $index + 1 }}
                            </div>
                            <h3 class="text-base md:text-lg font-bold text-[#151C25] leading-snug">
                                {{ $faq['question'] }}
                            </h3>
                        </div>
                        
                        {{-- Ícone Expand/Collapse --}}
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-white border-2 border-[#EC6608] flex items-center justify-center transition-transform group-open:rotate-180">
                            <svg class="h-5 w-5 text-[#EC6608]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </summary>

                    {{-- Answer (Content) --}}
                    <div class="p-6 bg-white border-t border-gray-200">
                        <div class="flex items-start mb-4">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 flex items-center justify-center mr-3">
                                <svg class="h-5 w-5 text-green-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-gray-800 leading-relaxed text-[15px]">
                                    {!! nl2br(e($faq['answer'])) !!}
                                </p>
                            </div>
                        </div>

                        {{-- Related Topics --}}
                        @if(!empty($faq['related_topics']) && is_array($faq['related_topics']))
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <p class="text-xs text-gray-600 font-bold uppercase tracking-wide mb-2 flex items-center">
                                        <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z" />
                                        </svg>
                                        Tópicos Relacionados:
                                    </p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($faq['related_topics'] as $topic)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                                                {{ $topic }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </details>
            @endforeach
        </div>

        {{-- Call to Action --}}
        <div class="mt-6 p-5 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl border-2 border-gray-200">
            <div class="flex items-start">
                <svg class="h-6 w-6 text-[#EC6608] mr-3 mt-0.5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <div>
                    <p class="text-sm font-bold text-gray-800 mb-1">Não encontrou sua dúvida?</p>
                    <p class="text-sm text-gray-600">
                        Deixe um comentário abaixo ou entre em contato conosco. Nossa equipe terá prazer em ajudar!
                    </p>
                </div>
            </div>
        </div>
    @else
        {{-- Fallback --}}
        <div class="bg-gray-50 border-2 border-gray-200 rounded-xl p-8 text-center">
            <svg class="h-12 w-12 text-gray-400 mx-auto mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-gray-500 text-sm font-medium">
                Nenhuma pergunta frequente disponível no momento.
            </p>
        </div>
    @endif
</div>

{{-- CSS customizado para melhorar a aparência do accordion --}}
@push('styles')
<style>
    /* Remove o marcador padrão do summary */
    details summary::-webkit-details-marker,
    details summary::marker {
        display: none;
    }

    /* Animação suave ao abrir */
    details[open] > div {
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Hover effect no summary */
    details summary:hover {
        background: linear-gradient(to right, rgb(219 234 254), rgb(191 219 254));
    }

    /* Focus state para acessibilidade */
    details summary:focus {
        outline: 2px solid #EC6608;
        outline-offset: 2px;
    }
</style>
@endpush