@if(!empty($article->warranty_info) && is_array($article->warranty_info))
<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
        🛡️ Garantia e Cuidados para Veículos Elétricos
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Informações de Garantia -->
        <div class="bg-white rounded-lg border p-5 border-l-4 border-blue-500">
            <div class="flex items-center mb-4">
                <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                    <span class="text-2xl">🛡️</span>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Garantias Elétricas</h3>
            </div>

            <div class="space-y-4">
                @if(!empty($article->warranty_info['prazo_garantia_geral']))
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-sm text-gray-700">
                        <span class="font-semibold">Garantia Geral:</span> {{
                        $article->warranty_info['prazo_garantia_geral'] }}
                    </p>
                </div>
                @endif

                @if(!empty($article->warranty_info['garantia_bateria']))
                <div class="bg-blue-50 p-3 rounded-lg border-l-4 border-blue-400">
                    <p class="text-sm text-gray-700">
                        <span class="font-semibold">🔋 Garantia da Bateria:</span> {{
                        $article->warranty_info['garantia_bateria'] }}
                    </p>
                </div>
                @endif

                @if(!empty($article->warranty_info['garantia_motor_eletrico']))
                <div class="bg-blue-50 p-3 rounded-lg border-l-4 border-blue-400">
                    <p class="text-sm text-gray-700">
                        <span class="font-semibold">⚡ Garantia do Motor Elétrico:</span> {{
                        $article->warranty_info['garantia_motor_eletrico'] }}
                    </p>
                </div>
                @endif
            </div>

            @if(!empty($article->warranty_info['observacoes_importantes']))
            <div class="mt-4 pt-4 border-t border-gray-200">
                <p class="text-sm text-gray-700">
                    <span class="font-semibold">💡 Importante:</span> {{
                    $article->warranty_info['observacoes_importantes'] }}
                </p>
            </div>
            @endif
        </div>

        <!-- Dicas para Prolongar a Vida Útil -->
        @if(!empty($article->warranty_info['dicas_preservacao']) &&
        is_array($article->warranty_info['dicas_preservacao']) && count($article->warranty_info['dicas_preservacao']) >
        0)
        <div class="bg-white rounded-lg border p-5 border-l-4 border-blue-500">
            <div class="flex items-center mb-4">
                <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                    <span class="text-2xl">🔋</span>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Dicas de Preservação</h3>
            </div>

            <ul class="space-y-3">
                @foreach($article->warranty_info['dicas_preservacao'] as $dica)
                @if(!empty($dica))
                <li class="flex items-start">
                    <div
                        class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mr-2 flex-shrink-0 mt-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-blue-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <p class="text-sm text-gray-700">{{ $dica }}</p>
                </li>
                @endif
                @endforeach
            </ul>
        </div>
        @endif
    </div>

    <!-- Alerta de Importância -->
    @if(!empty($article->vehicle_full_name))
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-5">
        <div class="flex">
            <div class="flex-shrink-0 mr-4">
                <span class="text-3xl">🔋</span>
            </div>
            <div>
                <h3 class="text-lg font-medium text-blue-700 mb-2">⚡ Manutenção Simplificada em Veículos Elétricos</h3>
                <p class="text-gray-700 mb-3">
                    O {{ $article->vehicle_full_name }} possui menos componentes móveis que veículos convencionais,
                    resultando em manutenção mais simples e econômica. Não há necessidade de troca de óleo,
                    filtros de combustível ou velas de ignição.
                </p>
                <p class="text-gray-700">
                    💡 <strong>Vantagem:</strong> A manutenção foca principalmente na bateria, sistemas elétricos e
                    componentes básicos como pneus e freios. Isso resulta em custos operacionais significativamente
                    menores.
                </p>
            </div>
        </div>
    </div>
    @endif
</section>
@endif