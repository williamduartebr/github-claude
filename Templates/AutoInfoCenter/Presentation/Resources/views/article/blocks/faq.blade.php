{{--
Componente: FAQ (Perguntas Frequentes)

Uso: Seção de perguntas e respostas com Schema.org

Estrutura esperada:
{
  "block_type": "faq",
  "heading": "Perguntas Frequentes",
  "content": {
    "questions": [
      {
        "question": "Pergunta do usuário?",
        "answer": "Resposta completa e prática",
        "related_topics": ["Tópico 1", "Tópico 2"] (opcional)
      }
    ]
  }
}

@author Claude Sonnet 4
@version 1.0
--}}

<div class="mb-8">
    {{-- Heading --}}
    @if(!empty($block['heading']))
        <h2 class="text-2xl font-semibold text-[#151C25] mb-6 flex items-center">
            <svg class="h-7 w-7 text-blue-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ $block['heading'] }}
        </h2>
    @endif

    {{-- Questions --}}
    @if(!empty($block['content']['questions']) && is_array($block['content']['questions']))
        <div class="space-y-6">
            @foreach($block['content']['questions'] as $faq)
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                    {{-- Question --}}
                    @if(!empty($faq['question']))
                        <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-6 py-4 rounded-t-lg border-b border-blue-200">
                            <h3 class="text-lg font-semibold text-[#151C25] flex items-start">
                                <span class="text-blue-500 mr-2 text-xl">Q:</span>
                                <span>{{ $faq['question'] }}</span>
                            </h3>
                        </div>
                    @endif

                    {{-- Answer --}}
                    @if(!empty($faq['answer']))
                        <div class="px-6 py-4">
                            <div class="flex items-start">
                                <span class="text-green-500 mr-2 text-xl font-bold">A:</span>
                                <p class="text-gray-800 leading-relaxed flex-1">
                                    {!! nl2br(e($faq['answer'])) !!}
                                </p>
                            </div>
                        </div>
                    @endif

                    {{-- Related Topics --}}
                    @if(!empty($faq['related_topics']) && is_array($faq['related_topics']))
                        <div class="px-6 pb-4">
                            <div class="bg-gray-50 rounded-md p-3">
                                <p class="text-xs text-gray-600 font-semibold mb-2">
                                    📚 Tópicos Relacionados:
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($faq['related_topics'] as $topic)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $topic }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>