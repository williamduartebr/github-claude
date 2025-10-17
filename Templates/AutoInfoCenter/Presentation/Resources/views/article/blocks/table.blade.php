{{--
Componente: TABLE (Tabela Comparativa)

Uso: Dados tabulares com headers, rows e metadados opcionais

Estrutura esperada (DUAL - suporta 2 formatos):

FORMATO 1: Estrutura Direta (Lubrax JSON)
{
  "block_type": "table",
  "heading": "Veículos Testados e Resultados",
  "content": {
    "headers": ["Coluna 1", "Coluna 2", "Coluna 3"],
    "rows": [
      ["Dado 1.1", "Dado 1.2", "Dado 1.3"],
      ["Dado 2.1", "Dado 2.2", "Dado 2.3"]
    ],
    "caption": "Resultados após 6 meses...",
    "footer": "Nota de rodapé (opcional)"
  }
}

FORMATO 2: Estrutura Aninhada (Velas JSON)
{
  "block_type": "table",
  "heading": "Resultados de Durabilidade",
  "content": {
    "intro": "Após atingir a marca...",
    "description": "Descrição breve (opcional)",
    "table": {
      "headers": ["Coluna 1", "Coluna 2"],
      "rows": [
        ["Dado 1.1", "Dado 1.2"]
      ]
    },
    "caption": "Legenda opcional",
    "footer": "Nota de rodapé",
    "conclusion": "92% das velas ainda estavam..."
  }
}

@author Claude Sonnet 4.5
@version 2.0 - Suporta estrutura direta e aninhada
--}}

@if(!empty($block['heading']))
    <hr class="my-12 border-t border-gray-200" />
@endif

<div class="mb-8">
    {{-- Heading --}}
    @if(!empty($block['heading']))
        <h3 class="text-xl font-semibold text-[#151C25] mb-4">
            {{ $block['heading'] }}
        </h3>
    @endif

    {{-- Intro (se houver) --}}
    @if(!empty($block['content']['intro']))
        <p class="text-gray-700 leading-relaxed mb-4">
            {{ $block['content']['intro'] }}
        </p>
    @endif

    {{-- Description --}}
    @if(!empty($block['content']['description']))
        <p class="text-gray-600 mb-4">
            {{ $block['content']['description'] }}
        </p>
    @endif

    @php
        // Suporta AMBAS estruturas: direta OU aninhada em "table"
        $tableData = $block['content']['table'] ?? $block['content'];
        $headers = $tableData['headers'] ?? [];
        $rows = $tableData['rows'] ?? [];
    @endphp

    {{-- Tabela --}}
    @if(!empty($headers) && !empty($rows))
        <div class="overflow-x-auto shadow-sm rounded-lg mb-4">
            <table class="min-w-full divide-y divide-gray-200">
                {{-- Header --}}
                <thead class="bg-gray-100">
                    <tr>
                        @foreach($headers as $header)
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                {{ $header }}
                            </th>
                        @endforeach
                    </tr>
                </thead>

                {{-- Body --}}
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($rows as $rowIndex => $row)
                        <tr class="{{ $rowIndex % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                            @foreach($row as $cell)
                                <td class="px-6 py-4 text-sm text-gray-800">
                                    {{ $cell }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        {{-- Fallback se não houver dados --}}
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg">
            <p class="text-sm text-yellow-800">
                ⚠️ Tabela sem dados para exibir
            </p>
        </div>
    @endif

    {{-- Caption --}}
    @if(!empty($block['content']['caption']))
        <p class="text-sm text-gray-600 italic mt-2 text-center">
            {{ $block['content']['caption'] }}
        </p>
    @endif

    {{-- Footer note --}}
    @if(!empty($block['content']['footer']))
        <p class="text-sm text-gray-500 italic mt-2">
            * {{ $block['content']['footer'] }}
        </p>
    @endif

    {{-- Conclusion (se houver) --}}
    @if(!empty($block['content']['conclusion']))
        <div class="mt-4 p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-lg">
            <p class="text-sm text-gray-800 leading-relaxed">
                <strong>Conclusão:</strong> {{ $block['content']['conclusion'] }}
            </p>
        </div>
    @endif
</div>