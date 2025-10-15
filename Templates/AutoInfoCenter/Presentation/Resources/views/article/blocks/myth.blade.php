{{--
Componente: MYTH (Mito vs Realidade)

Uso: Desmistificar crenças comuns

Estrutura esperada:
{
  "block_type": "myth",
  "heading": "Mitos vs Realidade",
  "content": {
    "items": [
      {
        "myth": "Afirmação comum",
        "reality": "true | false | partial",
        "reality_label": "✅ Verdade | ❌ Mito | ⚠️ Parcialmente",
        "explanation": "Explicação técnica"
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
        <h2 class="text-2xl font-semibold text-[#151C25] mb-6">
            {{ $block['heading'] }}
        </h2>
    @endif
    @dd($block['content'])

    {{-- Items --}}
    @if(!empty($block['content']['items']) && is_array($block['content']['items']))
        <div class="space-y-6">
            @foreach($block['content']['items'] as $item)
                @php
                    $reality = $item['reality'] ?? 'partial';
                    $bgColor = match($reality) {
                        'true' => 'bg-green-50',
                        'false' => 'bg-red-50',
                        default => 'bg-yellow-50'
                    };
                    $borderColor = match($reality) {
                        'true' => 'border-green-400',
                        'false' => 'border-red-400',
                        default => 'border-yellow-400'
                    };
                @endphp

                <div class="{{ $bgColor }} border-l-4 {{ $borderColor }} rounded-r-lg p-6 shadow-sm">
                    {{-- Myth --}}
                    @if(!empty($item['myth']))
                        <div class="mb-4">
                            <div class="flex items-start">
                                <span class="text-2xl mr-3">💭</span>
                                <div>
                                    <p class="text-xs text-gray-600 font-semibold uppercase tracking-wide mb-1">
                                        Afirmação Comum:
                                    </p>
                                    <p class="text-gray-800 font-medium leading-relaxed">
                                        "{{ $item['myth'] }}"
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Reality Label --}}
                    @if(!empty($item['reality_label']))
                        <div class="mb-4 pb-4 border-b-2 {{ $borderColor }}">
                            <div class="inline-flex items-center px-3 py-1 rounded-full {{ $bgColor }} border {{ $borderColor }}">
                                <span class="text-sm font-bold">
                                    {{ $item['reality_label'] }}
                                </span>
                            </div>
                        </div>
                    @endif

                    {{-- Explanation --}}
                    @if(!empty($item['explanation']))
                        <div>
                            <p class="text-xs text-gray-600 font-semibold uppercase tracking-wide mb-2">
                                A Verdade:
                            </p>
                            <p class="text-gray-800 leading-relaxed">
                                {{ $item['explanation'] }}
                            </p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>