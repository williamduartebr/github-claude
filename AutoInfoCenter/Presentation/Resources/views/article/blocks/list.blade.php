{{--
Componente: LIST (Lista)

Uso: Lista em formato de cards quando items possuem title e description,
     ou bullets/numbered quando são strings simples

Estrutura esperada:
{
  "block_type": "list",
  "heading": "Título da Lista",
  "content": {
    "intro": "Texto introdutório (opcional)",
    "list_type": "ordered | bullet | checklist",
    "items": [
      {
        "title": "Título do Card",
        "description": "Descrição detalhada"
      }
    ],
    "conclusion": "Texto de conclusão (opcional)"
  }
}

@author Claude Sonnet 4.5
@version 2.0
--}}

@if(!empty($block['heading']))
    <hr class="my-12 border-t border-gray-200" />
@endif

<div class="mb-10">
    {{-- Heading --}}
    @if(!empty($block['heading']))
        <h3 class="text-2xl font-bold text-[#151C25] mb-4">
            {{ $block['heading'] }}
        </h3>
    @endif

    {{-- Intro (se existir) --}}
    @if(!empty($block['content']['intro']))
        <p class="text-gray-700 leading-relaxed mb-6">
            {{ $block['content']['intro'] }}
        </p>
    @endif

    {{-- Conteúdo Principal --}}
    @if(!empty($block['content']['items']) && is_array($block['content']['items']))
        @php
            $listType = $block['content']['list_type'] ?? 'bullet';
            $firstItem = $block['content']['items'][0] ?? null;
            $isCardFormat = is_array($firstItem) && isset($firstItem['title']) && isset($firstItem['description']);
        @endphp

        @if($isCardFormat)
            {{-- Layout de CARDS para items com title + description --}}
            <div class="grid gap-6 md:grid-cols-1 lg:grid-cols-2">
                @foreach($block['content']['items'] as $index => $item)
                    <div class="group relative bg-white rounded-xl border-2 border-gray-200 hover:border-[#EC6608] 
                                transition-all duration-300 hover:shadow-lg overflow-hidden">
                        
                        {{-- Badge de número (se ordered) --}}
                        @if($listType === 'ordered')
                            <div class="absolute top-4 right-4 w-8 h-8 bg-[#EC6608] text-white 
                                        rounded-full flex items-center justify-center font-bold text-sm 
                                        group-hover:scale-110 transition-transform">
                                {{ $index + 1 }}
                            </div>
                        @endif

                        <div class="p-6">
                            {{-- Título do Card --}}
                            <h4 class="text-lg font-bold text-[#151C25] mb-3 pr-10 
                                       group-hover:text-[#EC6608] transition-colors">
                                {{ $item['title'] }}
                            </h4>

                            {{-- Descrição --}}
                            <p class="text-gray-700 leading-relaxed text-[15px]">
                                {{ $item['description'] }}
                            </p>
                        </div>

                        {{-- Indicador visual de hover --}}
                        <div class="h-1 bg-gradient-to-r from-[#EC6608] to-orange-400 
                                    transform scale-x-0 group-hover:scale-x-100 
                                    transition-transform duration-300 origin-left">
                        </div>
                    </div>
                @endforeach
            </div>

        @elseif($listType === 'ordered')
            {{-- Lista NUMERADA para strings simples --}}
            <ol class="space-y-3">
                @foreach($block['content']['items'] as $index => $item)
                    <li class="flex items-start group">
                        <span class="flex-shrink-0 w-7 h-7 bg-[#EC6608] text-white 
                                     rounded-full flex items-center justify-center 
                                     font-bold text-sm mr-3 mt-0.5">
                            {{ $index + 1 }}
                        </span>
                        <p class="text-gray-800 leading-relaxed pt-0.5">
                            {{ is_array($item) ? ($item['text'] ?? $item[0] ?? '') : $item }}
                        </p>
                    </li>
                @endforeach
            </ol>

        @elseif($listType === 'checklist')
            {{-- CHECKLIST para strings simples --}}
            <div class="space-y-3">
                @foreach($block['content']['items'] as $item)
                    <div class="flex items-start group">
                        <div class="flex-shrink-0 mt-0.5">
                            <svg class="h-6 w-6 text-green-500 group-hover:scale-110 transition-transform" 
                                 xmlns="http://www.w3.org/2000/svg" 
                                 viewBox="0 0 20 20" 
                                 fill="currentColor">
                                <path fill-rule="evenodd" 
                                      d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" 
                                      clip-rule="evenodd" />
                            </svg>
                        </div>
                        <p class="ml-3 text-gray-800 leading-relaxed">
                            {!! is_array($item) ? ($item['text'] ?? markdown($item[0]) ?? '') : markdown($item) !!}
                        </p>
                    </div>
                @endforeach
            </div>

        @else
            {{-- Lista BULLET (padrão) para strings simples --}}
            <ul class="space-y-3">
                @foreach($block['content']['items'] as $item)
                    <li class="flex items-start group">
                        <span class="flex-shrink-0 w-2 h-2 bg-[#EC6608] rounded-full 
                                     mr-3 mt-2 group-hover:scale-150 transition-transform">
                        </span>
                        <p class="text-gray-800 leading-relaxed">
                            {!! is_array($item) ? ($item['text'] ?? markdown($item[0]) ?? '') : markdown($item) !!}
                        </p>
                    </li>
                @endforeach
            </ul>
        @endif
    @endif

    {{-- Conclusão (se existir) --}}
    @if(!empty($block['content']['conclusion']))
        <div class="mt-6 p-4 bg-gray-50 border-l-4 border-[#EC6608] rounded-r-lg">
            <p class="text-gray-800 leading-relaxed font-medium">
                {{ $block['content']['conclusion'] }}
            </p>
        </div>
    @endif
</div>