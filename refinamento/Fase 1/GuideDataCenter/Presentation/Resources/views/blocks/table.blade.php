{{--
Componente: TABLE (Tabela Comparativa)

Uso: Dados tabulares com headers e rows

Estrutura esperada:
- heading: string
- headers: array
- rows: array
- caption: string (opcional)
- footer: string (opcional)

@author Claude Sonnet 4.5
@version 1.0
--}}

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-2">
    <section class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 mb-8">
        @if(!empty($block['data']['heading']))
        <h2 class="text-xl font-bold mb-4 font-montserrat">
            {{ $block['data']['heading'] }}
        </h2>
        @endif

        @if(!empty($block['data']['headers']) && !empty($block['data']['rows']))
        <div class="overflow-x-auto shadow-sm rounded-lg mb-4">
            <table class="min-w-full divide-y divide-gray-200">
                {{-- Header --}}
                <thead class="bg-gray-100">
                    <tr>
                        @foreach($block['data']['headers'] as $header)
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            {{ $header }}
                        </th>
                        @endforeach
                    </tr>
                </thead>

                {{-- Body --}}
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($block['data']['rows'] as $rowIndex => $row)
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

        @if(!empty($block['data']['caption']))
        <p class="text-sm text-gray-600 italic mt-2 text-center">
            {{ $block['data']['caption'] }}
        </p>
        @endif

        @if(!empty($block['data']['footer']))
        <p class="text-sm text-gray-500 italic mt-2">
            * {{ $block['data']['footer'] }}
        </p>
        @endif
    </section>
</div>