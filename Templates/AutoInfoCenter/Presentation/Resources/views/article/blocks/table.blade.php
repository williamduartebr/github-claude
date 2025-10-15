{{--
Componente: TABLE (Tabela Comparativa)

Uso: Dados tabulares com headers, rows e footer opcional

Estrutura esperada:
{
  "block_type": "table",
  "heading": "Título da Tabela",
  "content": {
    "description": "Descrição breve (opcional)",
    "headers": ["Coluna 1", "Coluna 2", "Coluna 3"],
    "rows": [
      ["Dado 1.1", "Dado 1.2", "Dado 1.3"],
      ["Dado 2.1", "Dado 2.2", "Dado 2.3"]
    ],
    "footer": "Nota de rodapé (opcional)"
  }
}

@author Claude Sonnet 4
@version 1.0
--}}

<div class="mb-8">
    {{-- Heading --}}
    @if(!empty($block['heading']))
        <h3 class="text-xl font-semibold text-[#151C25] mb-4">
            {{ $block['heading'] }}
        </h3>
    @endif

    {{-- Description --}}
    @if(!empty($block['content']['description']))
        <p class="text-gray-600 mb-4">
            {{ $block['content']['description'] }}
        </p>
    @endif

    {{-- Tabela --}}
    @if(!empty($block['content']['headers']) && !empty($block['content']['rows']))
        <div class="overflow-x-auto shadow-sm rounded-lg mb-4">
            <table class="min-w-full divide-y divide-gray-200">
                {{-- Header --}}
                <thead class="bg-gray-100">
                    <tr>
                        @foreach($block['content']['headers'] as $header)
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                {{ $header }}
                            </th>
                        @endforeach
                    </tr>
                </thead>

                {{-- Body --}}
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($block['content']['rows'] as $rowIndex => $row)
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
    @endif

    {{-- Footer note --}}
    @if(!empty($block['content']['footer']))
        <p class="text-sm text-gray-500 italic mt-2">
            * {{ $block['content']['footer'] }}
        </p>
    @endif
</div>