@if(!empty($article->verification_procedure) && is_array($article->verification_procedure) &&
count($article->verification_procedure) > 0)
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
        Procedimento de Verificação dos Pneus
    </h2>

    <div class="space-y-6">
        @foreach($article->verification_procedure as $index => $procedure)
        @if(!empty($procedure['title']))
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            <div class="bg-[#0E368A] text-white px-6 py-3">
                <h3 class="font-semibold flex items-center">
                    <span
                        class="bg-white text-[#0E368A] rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold mr-3">
                        {{ $index + 1 }}
                    </span>
                    {{ $procedure['title'] }}
                </h3>
            </div>
            <div class="p-6">
                @if(!empty($procedure['steps']) && is_array($procedure['steps']))
                <div class="mb-4">
                    <h4 class="font-medium text-gray-900 mb-2">Passos:</h4>
                    <ol class="space-y-2">
                        @foreach($procedure['steps'] as $step)
                        @if(!empty($step))
                        <li class="flex items-start">
                            <span class="text-[#0E368A] mr-2">{{ $loop->index + 1 }}.</span>
                            <span class="text-gray-700">{{ $step }}</span>
                        </li>
                        @endif
                        @endforeach
                    </ol>
                </div>
                @endif

                @if(!empty($procedure['pressures']) && is_array($procedure['pressures']))
                <div class="bg-blue-50 p-4 rounded-lg mb-4">
                    <h4 class="font-medium text-blue-900 mb-2">Pressões Recomendadas:</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                        @foreach($procedure['pressures'] as $key => $pressure)
                        @if(!empty($pressure))
                        <div class="flex justify-between md:justify-start">
                            <span class="text-blue-700 mr-2">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                            <span class="font-medium text-blue-900">{{ $pressure }}</span>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
                @endif

                @if(!empty($procedure['tolerance']))
                <div class="text-sm text-gray-600">
                    <strong>Tolerância:</strong> {{ $procedure['tolerance'] }}
                </div>
                @endif

                @if(!empty($procedure['verify']) && is_array($procedure['verify']))
                <div class="mt-4">
                    <h4 class="font-medium text-gray-900 mb-2">Itens a verificar:</h4>
                    <ul class="space-y-1">
                        @foreach($procedure['verify'] as $item)
                        @if(!empty($item))
                        <li class="flex items-center text-sm text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500 mr-2" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            {{ $item }}
                        </li>
                        @endif
                        @endforeach
                    </ul>
                </div>
                @endif

                @if(!empty($procedure['procedure']) && is_array($procedure['procedure']))
                <div class="mt-4">
                    <h4 class="font-medium text-gray-900 mb-2">Procedimento detalhado:</h4>
                    <ul class="space-y-1">
                        @foreach($procedure['procedure'] as $step)
                        @if(!empty($step))
                        <li class="flex items-start text-sm text-gray-600">
                            <span class="text-[#0E368A] mr-2">•</span>
                            {{ $step }}
                        </li>
                        @endif
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
        </div>
        @endif
        @endforeach
    </div>
</section>
@endif