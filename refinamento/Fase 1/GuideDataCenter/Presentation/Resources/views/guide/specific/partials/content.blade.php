{{-- partials/content.blade.php --}}

@if(!empty($guide['content_blocks']) && is_array($guide['content_blocks']))
    {{-- Sistema de blocos dinâmicos --}}
    <div class="mx-auto px-4 sm:px-6 lg:px-8 mb-12">
        @foreach($guide['content_blocks'] as $block)
            @if(!empty($block['type']))
                @php
                $componentPath = "guide-data-center::blocks.{$block['type']}";
                @endphp

                @if(view()->exists($componentPath))
                    @include($componentPath, ['block' => $block])
                @endif
            @endif
        @endforeach
    </div>

@elseif(!empty($guide['content']) || !empty($guide['payload']))
    {{-- FALLBACK: Conteúdo básico --}}
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 mb-12">
        @if(!empty($guide['content']))
        <div class="prose max-w-none mb-8">
            {!! $guide['content'] !!}
        </div>
        @endif

        @if(!empty($guide['payload']))
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 font-montserrat">Especificações</h2>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($guide['payload'] as $key => $value)
                    @if(!is_array($value))
                    <div class="border-b border-gray-200 pb-2">
                        <dt class="text-sm font-medium text-gray-500">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $value }}</dd>
                    </div>
                    @endif
                @endforeach
            </dl>
        </div>
        @endif
    </div>
@endif
