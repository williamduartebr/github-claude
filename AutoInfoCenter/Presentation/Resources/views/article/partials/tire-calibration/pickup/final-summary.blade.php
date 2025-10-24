<section class="mb-12">
    <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-2xl border-2 border-orange-200 p-8 shadow-lg">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-orange-800 mb-2 flex items-center justify-center">
                🚛 Resumo Final - Pickup
            </h2>
        </div>

        <div class="max-w-4xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <div class="bg-white/70 rounded-xl p-6">
                    <h3 class="font-semibold text-orange-800 mb-4 text-center">Uso Normal (Sem Carga)</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700">Dianteiros:</span>
                            <span class="font-bold text-blue-600">{{ $pressureSpecs['pressure_empty_front']
                                ?? '35' }} PSI</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700">Traseiros:</span>
                            <span class="font-bold text-blue-600">{{ $pressureSpecs['pressure_empty_rear']
                                ?? '40' }} PSI</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white/70 rounded-xl p-6">
                    <h3 class="font-semibold text-orange-800 mb-4 text-center">Com Carga na Caçamba</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700">Dianteiros:</span>
                            <span class="font-bold text-orange-600">{{ $pressureSpecs['pressure_max_front']
                                ?? '38' }} PSI</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700">Traseiros:</span>
                            <span class="font-bold text-orange-600">{{ $pressureSpecs['pressure_max_rear']
                                ?? '45' }} PSI</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white/70 rounded-xl p-6">
                <h3 class="font-semibold text-orange-800 mb-4 flex items-center justify-center">
                    <span class="mr-2">📝</span>
                    Lembre-se Sempre (Pickups)
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <ul class="space-y-2 text-sm text-orange-700">
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-orange-500 rounded-full mr-2 mt-2 flex-shrink-0"></span>
                            Verificar {{ $isPremium ? 'semanalmente' : 'quinzenalmente' }} devido ao uso
                            intensivo
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-orange-500 rounded-full mr-2 mt-2 flex-shrink-0"></span>
                            Sempre com pneus frios (3 horas parados mínimo)
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-orange-500 rounded-full mr-2 mt-2 flex-shrink-0"></span>
                            Ajustar conforme peso na caçamba (fundamental!)
                        </li>
                        @if(!empty($pressureSpecs['pressure_spare']))
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-orange-500 rounded-full mr-2 mt-2 flex-shrink-0"></span>
                            Incluir o estepe na verificação ({{ $pressureSpecs['pressure_spare'] }} PSI)
                        </li>
                        @endif
                    </ul>
                    <ul class="space-y-2 text-sm text-orange-700">
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-orange-500 rounded-full mr-2 mt-2 flex-shrink-0"></span>
                            Pressões diferentes para off-road quando aplicável
                        </li>
                        @if($hasTpms)
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-orange-500 rounded-full mr-2 mt-2 flex-shrink-0"></span>
                            Aguardar recalibração do TPMS após ajustes
                        </li>
                        @endif
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-orange-500 rounded-full mr-2 mt-2 flex-shrink-0"></span>
                            Considerar peso do reboque quando aplicável
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-orange-500 rounded-full mr-2 mt-2 flex-shrink-0"></span>
                            Verificar após uso off-road intenso
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>