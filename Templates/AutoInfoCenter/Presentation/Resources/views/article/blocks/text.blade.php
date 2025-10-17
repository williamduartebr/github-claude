{{--
Componente: TEXT (Bloco de Texto Simples)

Uso: Parágrafos de texto corrido com ênfase opcional

Estrutura esperada (baseada nos JSONs reais):
{
  "block_type": "text",
  "heading": "Título da Seção",
  "subheading": "Subtítulo (opcional)",
  "content": {
    "text": "Texto corrido completo com múltiplos parágrafos separados por \n\n",
    "paragraphs": ["Parágrafo 1", "Parágrafo 2"],  // Alternativa
    "emphasis": "Frase importante destacada (opcional)"
  }
}

@author Claude Sonnet 4.5
@version 2.0 - Refatorado para compatibilidade com JSONs reais
--}}

@if(!empty($block['heading']))
    <hr class="my-12 border-t border-gray-200" />
@endif

<div class="mb-8">
    {{-- Heading --}}
    @if(!empty($block['heading']))
        <h2 class="text-2xl font-semibold text-[#151C25] mb-4">
            {{ $block['heading'] }}
        </h2>
    @endif

    {{-- Subheading --}}
    @if(!empty($block['subheading']))
        <h3 class="text-xl text-gray-600 font-medium mb-3">
            {{ $block['subheading'] }}
        </h3>
    @endif

    {{-- Conteúdo Principal --}}
    @php
        // Prioridade: 'text' (string única) > 'paragraphs' (array)
        $textContent = $block['content']['text'] ?? null;
        $paragraphs = $block['content']['paragraphs'] ?? [];
    @endphp

    @if(!empty($textContent))
        {{-- Texto único com quebras de linha --}}
        <div class="prose prose-lg max-w-none">
            @foreach(explode("\n\n", $textContent) as $paragraph)
                @if(trim($paragraph))
                    <p class="text-gray-800 leading-relaxed mb-4">
                        {!! nl2br(e(trim($paragraph))) !!}
                    </p>
                @endif
            @endforeach
        </div>
    @elseif(!empty($paragraphs) && is_array($paragraphs))
        {{-- Array de parágrafos --}}
        <div class="prose prose-lg max-w-none">
            @foreach($paragraphs as $paragraph)
                <p class="text-gray-800 leading-relaxed mb-4">
                    {!! nl2br(e($paragraph)) !!}
                </p>
            @endforeach
        </div>
    @endif

    {{-- Ênfase (destaque) --}}
    @if(!empty($block['content']['emphasis']))
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mt-4 rounded-r-lg">
            <p class="text-gray-800 font-medium flex items-start">
                <span class="text-xl mr-2">💡</span>
                <span>{{ $block['content']['emphasis'] }}</span>
            </p>
        </div>
    @endif
</div>