{{--
Componente: INTRO (Introdução do Artigo)

CAMPO "context": USE APENAS QUANDO
- Informação exclusiva/diferente do Brasil
- Impacto grande em decisão/custo
- Não está no texto principal
- Usuário precisa saber logo no início

NÃO USE quando:
- Informação genérica/universal
- Já está no texto principal
- Dados simples (use blocos cost/table)

@author Claude Sonnet 4
@version 1.1 - Context opcional e criterioso
--}}

<div class="mb-8">
    {{-- Texto principal --}}
    @if(!empty($block['content']['text']))
        <div class="prose prose-lg max-w-none mb-6">
            <p class="text-lg text-gray-800 leading-relaxed">
                {!! nl2br(e($block['content']['text'])) !!}
            </p>
        </div>
    @endif

    {{-- Highlight (destaque importante) --}}
    @if(!empty($block['content']['highlight']))
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4 rounded-r-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-semibold text-yellow-800">
                        {{ $block['content']['highlight'] }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Contexto brasileiro (OPCIONAL - use com critério) --}}
    @if(!empty($block['content']['context']))
        <div class="bg-blue-50/50 border-l-4 border-blue-400 p-4 rounded-r-lg">
            <div class="flex items-start">
                <div class="flex-shrink-0 mt-0.5">
                    <svg class="h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-800 font-medium mb-1">
                        💡 Importante Saber (Brasil)
                    </p>
                    <p class="text-sm text-blue-700 leading-relaxed">
                        {!! nl2br(e($block['content']['context'])) !!}
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>