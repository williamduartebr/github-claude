<section class="mb-12">
    <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b-2 border-[#0E368A]/30">
        🔧 Procedimento de Calibragem para Pickups
    </h2>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8">
        <div class="space-y-6">
            @php
            $steps = [
            [
            'number' => '1',
            'title' => 'Prepare o Veículo',
            'description' => 'Estacione em local seguro e plano. Aguarde pelo menos 3 horas após dirigir
            para garantir que os pneus estejam frios. Pickups esquentam mais os pneus devido ao peso.',
            'icon' => '🚗'
            ],
            [
            'number' => '2',
            'title' => 'Verifique a Carga',
            'description' => 'Determine se a caçamba está vazia ou carregada. Para pickup sem carga use
            pressões normais. Com carga na caçamba, use as pressões de carga completa.',
            'icon' => '⚖️'
            ],
            [
            'number' => '3',
            'title' => 'Remova Tampas de Válvula',
            'description' => 'Retire as tampas das válvulas dos pneus. Mantenha-as seguras para não perder.
            Em pickups, verifique se não há sujeira acumulada nas válvulas.',
            'icon' => '🔧'
            ],
            [
            'number' => '4',
            'title' => 'Calibre Dianteiros Primeiro',
            'description' => 'Use a pressão recomendada para os dianteiros (geralmente menor). Conecte
            firmemente o calibrador e adicione ar conforme necessário.',
            'icon' => '⬆️'
            ],
            [
            'number' => '5',
            'title' => 'Calibre Traseiros',
            'description' => 'Ajuste os traseiros com pressão mais alta (fundamental em pickups). Eles
            suportam o peso da caçamba e precisam de pressão maior para estabilidade.',
            'icon' => '⬇️'
            ],
            [
            'number' => '6',
            'title' => 'Verifique o Estepe',
            'description' => 'Não esqueça do estepe! Pickups usam muito o estepe em situações de trabalho.
            Mantenha-o sempre na pressão correta.',
            'icon' => '🛞'
            ],
            [
            'number' => '7',
            'title' => 'Reset TPMS (se aplicável)',
            'description' => 'Se sua pickup tem TPMS, pode ser necessário resetar o sistema após calibragem.
            Consulte o manual para procedimento específico.',
            'icon' => '📡'
            ],
            [
            'number' => '8',
            'title' => 'Teste de Dirigibilidade',
            'description' => 'Faça um teste de direção em baixa velocidade. Pickups bem calibradas têm
            direção estável e não "puxam" para um lado.',
            'icon' => '🛣️'
            ]
            ];
            @endphp

            @foreach($steps as $step)
            <div class="flex items-start">
                <div class="flex-shrink-0 mr-6">
                    <div
                        class="w-12 h-12 bg-[#0E368A] rounded-full flex items-center justify-center text-white font-bold text-lg">
                        {{ $step['number'] }}
                    </div>
                </div>
                <div class="flex-1">
                    <div class="flex items-center mb-2">
                        <span class="text-xl mr-3">{{ $step['icon'] }}</span>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $step['title'] }}</h3>
                    </div>
                    <p class="text-gray-700 leading-relaxed">{{ $step['description'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>