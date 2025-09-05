{{--
Partial: ideal-tire-pressure/car/full-load-table.blade.php
Tabela detalhada de pressões para diferentes condições de carga
--}}

@php
$fullLoadTable = $article->getData()['full_load_table'] ?? [];
$conditions = $fullLoadTable['conditions'] ?? [];
$vehicleInfo = $article->getData()['vehicle_info'] ?? [];
@endphp

@if(!empty($conditions))
<section class="mb-12">
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-t-lg p-6">
        <div class="flex items-center">
            <span class="text-3xl mr-4">📊</span>
            <div>
                <h2 class="text-2xl font-bold mb-2">
                    {{ $fullLoadTable['title'] ?? 'Pressões para Carga Completa' }}
                </h2>
                <p class="text-blue-100 text-sm">
                    {{ $fullLoadTable['description'] ?? 'Use estes valores quando o veículo estiver com lotação máxima e
                    bagagem.' }}
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-b-lg border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead>
                    <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <th class="py-3 px-4 text-left font-medium text-sm text-gray-700">Condição de Uso</th>
                        <th class="py-3 px-4 text-center font-medium text-sm text-gray-700">
                            <div class="flex items-center justify-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Ocupantes
                            </div>
                        </th>
                        <th class="py-3 px-4 text-center font-medium text-sm text-gray-700">
                            <div class="flex items-center justify-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                Bagagem
                            </div>
                        </th>
                        <th class="py-3 px-4 text-center font-medium text-sm text-gray-700">
                            <div class="flex items-center justify-center">
                                🔄 Dianteiros
                            </div>
                        </th>
                        <th class="py-3 px-4 text-center font-medium text-sm text-gray-700">
                            <div class="flex items-center justify-center">
                                🔙 Traseiros
                            </div>
                        </th>
                        <th class="py-3 px-4 text-center font-medium text-sm text-gray-700">Observação</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($conditions as $index => $condition)
                    <tr
                        class="border-b border-gray-200 hover:bg-gray-50 transition-colors {{ $condition['css_class'] ?? '' }}">
                        <!-- Condição de Uso -->
                        <td class="py-4 px-4 text-sm font-semibold text-gray-900">
                            <div class="flex items-center">
                                <div
                                    class="w-8 h-8 rounded-full {{ $index === 0 ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600' }} flex items-center justify-center mr-3 text-xs font-bold">
                                    {{ $index + 1 }}
                                </div>
                                <div>
                                    <div class="font-semibold">{{ $condition['condition'] ?? \Str::of($condition['version'])->title() ??
                                        'Padrão' }}</div>
                                    @if(!empty($condition['description']))
                                    <div class="text-xs text-gray-600 mt-1">{{ $condition['description'] }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <!-- Ocupantes -->
                        <td class="py-4 px-4 text-sm text-center text-gray-700">
                            <div class="flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-500 mr-1" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {{ $condition['occupants'] ?? '1-3' }}
                            </div>
                        </td>

                        <!-- Bagagem -->
                        <td class="py-4 px-4 text-sm text-center text-gray-700">
                            <div class="flex items-center justify-center">
                                <svg class="w-4 h-4 text-orange-500 mr-1" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                {{ $condition['luggage'] ?? 'Sem bagagem' }}
                            </div>
                        </td>

                        <!-- Pressão Dianteira -->
                        <td class="py-4 px-4 text-sm text-center">
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-green-100 text-green-800">
                                {{ $condition['front_pressure'] ?? 'N/A' }}
                            </span>
                        </td>

                        <!-- Pressão Traseira -->
                        <td class="py-4 px-4 text-sm text-center">
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-blue-100 text-blue-800">
                                {{ $condition['rear_pressure'] ?? 'N/A' }}
                            </span>
                        </td>

                        <!-- Observação -->
                        <td class="py-4 px-4 text-sm text-gray-600">
                            @if(!empty($condition['note']))
                            <div class="flex items-start">
                                <svg class="w-4 h-4 text-yellow-500 mr-1 mt-0.5 flex-shrink-0" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span class="text-xs">{{ $condition['note'] }}</span>
                            </div>
                            @else
                            <span class="text-gray-400">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Dicas Importantes -->
    <div class="mt-6 space-y-4">
        <!-- Dica sobre verificação -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-yellow-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd" />
                </svg>
                <div class="text-sm">
                    <p class="font-medium text-yellow-800 mb-1">⚡ Verificação Importante:</p>
                    <p class="text-yellow-700">
                        Sempre calibre os pneus com eles frios (após pelo menos 3 horas parados).
                        A pressão aumenta naturalmente durante a condução devido ao aquecimento.
                    </p>
                </div>
            </div>
        </div>

        @if($vehicleInfo['is_electric'] ?? false)
        <!-- Dica específica para veículos elétricos -->
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-green-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.477.859h4z" />
                </svg>
                <div class="text-sm">
                    <p class="font-medium text-green-800 mb-1">🔋 Dica para Veículo Elétrico:</p>
                    <p class="text-green-700">
                        Pressões corretas são ainda mais importantes em veículos elétricos, pois impactam diretamente na
                        autonomia da bateria.
                        Pressões baixas podem reduzir a autonomia em até 10%.
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!-- Dica de economia -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm">
                    <p class="font-medium text-blue-800 mb-1">💰 Economia de Combustível:</p>
                    <p class="text-blue-700">
                        Pressões adequadas podem melhorar o consumo de combustível em até 3% e aumentar a vida útil dos
                        pneus em até 25%.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
@endif