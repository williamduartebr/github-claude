{{--
Componente: TEXT (Bloco de Texto Simples)

Uso: Parágrafos de texto corrido com ênfase opcional

Estrutura esperada:
{
  "block_type": "text",
  "heading": "Título da Seção",
  "subheading": "Subtítulo (opcional)",
  "content": {
    "paragraphs": [
      "Parágrafo 1 com explicação...",
      "Parágrafo 2 com mais detalhes..."
    ],
    "emphasis": "Frase importante destacada (opcional)"
  }
}

@author Claude Sonnet 4
@version 1.0
--}}

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

    {{-- Parágrafos --}}
    @if(!empty($block['content']['paragraphs']) && is_array($block['content']['paragraphs']))
        <div class="prose prose-lg max-w-none">
            @foreach($block['content']['paragraphs'] as $paragraph)
                <p class="text-gray-800 leading-relaxed mb-4">
                    {!! nl2br(e($paragraph)) !!}
                </p>
            @endforeach
        </div>
    @endif

    {{-- Ênfase (destaque) --}}
    @if(!empty($block['content']['emphasis']))
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mt-4">
            <p class="text-gray-800 font-medium">
                💡 {{ $block['content']['emphasis'] }}
            </p>
        </div>
    @endif
</div>