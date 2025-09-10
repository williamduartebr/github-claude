{{--
Template Desktop Otimizado: ideal_tire_pressure_pickup.blade.php
Adaptado para pickups - Mantém estrutura original com melhorias específicas
--}}

@extends('auto-info-center::layouts.app')

@push('head')
<link rel="amphtml" href="{{ route('info.article.show.amp', $article->slug) }}">
<link rel="canonical" href="{{ route('info.article.show', $article->slug) }}">

<script type="application/ld+json">
    {!! json_encode($article->structured_data) !!}
</script>
@endpush

@section('content')

@include('auto-info-center::article.partials.breadcrumb')

<!-- Conteúdo Principal -->
<main id="main-content" class="container mx-auto px-4">
    <div itemscope itemtype="https://schema.org/Article">
        <meta itemprop="vehicleEngine" content="{{ $article->getData()['vehicle_info']['full_name'] ?? '' }}" />
        <meta itemprop="category" content="Manutenção Automotiva - Pickup" />

        <!-- Tag Article -->
        <article class="max-w-4xl mx-auto pt-6 pb-12">
            @php
                // Variáveis específicas do template pickup
                $vehicleInfo = $article->getData()['vehicle_info'] ?? [];
                $pressureSpecs = $article->getData()['pressure_specifications'] ?? [];
                $contentData = $article->getData()['content'] ?? [];
                $tireSpecs = $article->getData()['tire_specifications_by_version'] ?? [];
                $fullLoadTable = $article->getData()['full_load_table'] ?? [];
                $labelLocation = $article->getData()['label_location'] ?? [];
                $specialConditions = $article->getData()['special_conditions'] ?? [];
                $unitConversion = $article->getData()['unit_conversion'] ?? [];
                $careRecommendations = $article->getData()['care_recommendations'] ?? [];
                $pressureImpact = $article->getData()['pressure_impact'] ?? [];
                $faq = $article->getData()['faq'] ?? $contentData['perguntas_frequentes'] ?? [];
                
                // Identificadores de pickup
                $vehicleName = $vehicleInfo['full_name'] ?? $article->getData()['title'] ?? 'pickup';
                $hasTpms = $vehicleInfo['has_tpms'] ?? false;
                $isElectric = $vehicleInfo['is_electric'] ?? false;
                $isPremium = $vehicleInfo['is_premium'] ?? false;
                $isPickup = true; // Template específico para pickups
            @endphp

            <!-- Cabeçalho Minimalista -->
            <div class="mb-8">
                <div class="border-b-2 border-[#0E368A] pb-4">
                    <div class="flex items-center mb-3">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-orange-500 text-white mr-3">
                            🚛 PICKUP
                        </span>
                        @if($hasTpms)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-500 text-white">
                            📡 TPMS
                        </span>
                        @endif
                    </div>
                    <h1 class="text-3xl md:text-4xl font-semibold leading-tight text-[#151C25]">
                        {{ $article->getData()['seo_data']['h1'] ?? $article->getData()['title'] }}
                    </h1>
                    <p class="text-sm mt-2 text-gray-500">
                        Atualizado em: {{ $article->getData()['formated_updated_at'] ?? '17 de julho de 2025' }}
                    </p>
                </div>
            </div>

            <!-- Introdução -->
            @if(!empty($article->getData()['introduction']))
            <div class="mb-10">
                <p class="text-lg text-gray-800 leading-relaxed">
                    {!! nl2br(e($article->getData()['introduction'])) !!}
                </p>
            </div>
            @endif

            <!-- Banner de Anúncio 1 -->
            <div class="my-8">
                [ADSENSE-ARTICLE-1]
            </div>

            <!-- Destaque da Pressão Ideal PARA PICKUP - ADAPTADO -->
            <section class="mb-12">
                <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-2xl border-2 border-orange-200 p-8 shadow-lg">
                    <div class="text-center mb-6">
                        <h2 class="text-2xl font-bold text-orange-800 mb-2 flex items-center justify-center">
                            🚛 Pressões Ideais para {{ $vehicleName }}
                        </h2>
                        <p class="text-orange-700 font-medium">
                            Verificação {{ $isPremium ? 'semanal' : 'quinzenal' }} recomendada • Sempre com pneus frios
                        </p>
                    </div>
                    
                    <!-- Layout adaptativo para pickup -->
                    @if(!empty($pressureSpecs['pressure_max_front']) && !empty($pressureSpecs['pressure_max_rear']))
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 max-w-4xl mx-auto">
                        <!-- Dianteiros Normal -->
                        <div class="bg-white rounded-xl border border-blue-200 p-4 text-center shadow-sm">
                            <div class="text-xs font-semibold text-blue-600 mb-1">DIANTEIROS (Normal)</div>
                            <div class="text-2xl font-bold text-blue-800 mb-1">{{ $pressureSpecs['pressure_empty_front'] ?? '35' }}</div>
                            <div class="text-xs text-blue-600 font-medium mb-2">PSI</div>
                            <div class="text-xs text-gray-500">Sem carga</div>
                        </div>
                        
                        <!-- Traseiros Normal -->
                        <div class="bg-white rounded-xl border border-blue-200 p-4 text-center shadow-sm">
                            <div class="text-xs font-semibold text-blue-600 mb-1">TRASEIROS (Normal)</div>
                            <div class="text-2xl font-bold text-blue-800 mb-1">{{ $pressureSpecs['pressure_empty_rear'] ?? '40' }}</div>
                            <div class="text-xs text-blue-600 font-medium mb-2">PSI</div>
                            <div class="text-xs text-gray-500">Sem carga</div>
                        </div>
                        
                        <!-- Com Carga -->
                        <div class="bg-white rounded-xl border border-orange-200 p-4 text-center shadow-sm col-span-2 md:col-span-1">
                            <div class="text-xs font-semibold text-orange-600 mb-1">COM CARGA</div>
                            <div class="text-2xl font-bold text-orange-800 mb-1">{{ $pressureSpecs['loaded_pressure_display'] ?? $pressureSpecs['pressure_max_front'].'/'.$pressureSpecs['pressure_max_rear'] }}</div>
                            <div class="text-xs text-orange-600 font-medium mb-2">PSI</div>
                            <div class="text-xs text-gray-500">Caçamba carregada</div>
                        </div>
                    </div>
                    @else
                    <!-- Layout padrão para 2 colunas -->
                    <div class="grid grid-cols-2 gap-4 max-w-lg mx-auto">
                        <div class="bg-white rounded-xl border border-blue-200 p-4 text-center shadow-sm">
                            <div class="text-xs font-semibold text-blue-600 mb-1">📄 DIANTEIROS</div>
                            <div class="text-2xl font-bold text-blue-800 mb-1">{{ $pressureSpecs['pressure_empty_front'] ?? '35' }}</div>
                            <div class="text-xs text-blue-600 font-medium">PSI</div>
                        </div>
                        <div class="bg-white rounded-xl border border-blue-200 p-4 text-center shadow-sm">
                            <div class="text-xs font-semibold text-blue-600 mb-1">📙 TRASEIROS</div>
                            <div class="text-2xl font-bold text-blue-800 mb-1">{{ $pressureSpecs['pressure_empty_rear'] ?? '40' }}</div>
                            <div class="text-xs text-blue-600 font-medium">PSI</div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Estepe separado se existir -->
                    @if(!empty($pressureSpecs['pressure_spare']))
                    <div class="mt-6 flex justify-center">
                        <div class="bg-white rounded-xl border border-green-200 p-4 text-center shadow-sm min-w-[140px]">
                            <div class="text-xs font-semibold text-green-600 mb-1">🛞 PNEU ESTEPE</div>
                            <div class="text-2xl font-bold text-green-800 mb-1">{{ $pressureSpecs['pressure_spare'] }}</div>
                            <div class="text-xs text-green-600 font-medium">PSI</div>
                        </div>
                    </div>
                    @endif
                </div>
            </section>

            <!-- Alerta específico para pickups -->
            <section class="mb-12">
                <div class="bg-amber-50 border-l-4 border-amber-400 rounded-lg p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 mr-4">
                            <div class="h-8 w-8 bg-amber-400 rounded-full flex items-center justify-center">
                                <span class="text-white font-bold text-sm">!</span>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-semibold text-amber-800 mb-2">🚛 Importante para Pickups</h3>
                            <p class="text-amber-700 text-sm leading-relaxed">
                                Pickups têm pressões traseiras mais altas devido à capacidade de carga. 
                                <strong>Sempre ajuste conforme o peso transportado na caçamba</strong> para manter 
                                estabilidade e segurança. A diferença de pressão entre eixos é normal e necessária.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Especificações dos Pneus Originais e Localização da Etiqueta -->
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    🔧 Especificações dos Pneus Originais - Pickup
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Especificações Técnicas -->
                    @if(!empty($tireSpecs))
                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center mb-4">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/15 flex items-center justify-center mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0E368A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Medidas de Pneus por Versão</h3>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full border-collapse">
                                <thead>
                                    <tr class="bg-[#0E368A] text-white">
                                        <th class="py-2 px-3 text-left font-medium text-xs">Versão</th>
                                        <th class="py-2 px-3 text-left font-medium text-xs">Medidas de Pneus</th>
                                        <th class="py-2 px-3 text-left font-medium text-xs">Normal/Carga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tireSpecs as $spec)
                                    <tr class="border-b border-gray-200 {{ $spec['css_class'] ?? '' }}">
                                        <td class="py-2 px-3 text-xs">{{ $spec['version'] ?? 'Principal' }}</td>
                                        <td class="py-2 px-3 text-xs font-mono">{{ $spec['tire_size'] ?? '' }}</td>
                                        <td class="py-2 px-3 text-xs">
                                            <div class="space-y-1">
                                                <div>Normal: <span class="text-blue-600 font-semibold">{{ $spec['front_normal'] ?? $pressureSpecs['pressure_empty_front'] ?? '35' }}/{{ $spec['rear_normal'] ?? $pressureSpecs['pressure_empty_rear'] ?? '40' }}</span></div>
                                                <div>Carga: <span class="text-orange-600 font-semibold">{{ $spec['front_loaded'] ?? $pressureSpecs['pressure_max_front'] ?? '38' }}/{{ $spec['rear_loaded'] ?? $pressureSpecs['pressure_max_rear'] ?? '45' }}</span></div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <p class="text-xs text-gray-700">
                                <span class="font-medium">Observação:</span> Pressões diferenciadas são essenciais para pickups devido à capacidade de carga variável na caçamba.
                            </p>
                        </div>
                    </div>
                    @endif

                    <!-- Localização da Etiqueta - USANDO PARTIAL MODULAR INLINE -->
                    @if(!empty($labelLocation))
                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center mb-4">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/15 flex items-center justify-center mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0E368A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Localização da Etiqueta de Pressão</h3>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="h-6 w-6 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-3 flex-shrink-0 mt-0.5">
                                    <span class="text-xs font-semibold text-[#0E368A]">1</span>
                                </div>
                                <p class="text-sm text-gray-700">{{ $labelLocation['description'] ?? $labelLocation['main_location'] ?? 'Porta do motorista' }}</p>
                            </div>

                            @foreach($labelLocation['alternative_locations'] ?? [] as $index => $altLocation)
                            <div class="flex items-start">
                                <div class="h-6 w-6 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-3 flex-shrink-0 mt-0.5">
                                    <span class="text-xs font-semibold text-[#0E368A]">{{ $index + 2 }}</span>
                                </div>
                                <p class="text-sm text-gray-700">{{ $altLocation }}</p>
                            </div>
                            @endforeach
                        </div>

                        @if($labelLocation['note'] ?? '')
                        <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#E06600] mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-sm font-medium text-gray-800">{{ $labelLocation['note'] }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </section>

            <!-- Tabela de Carga Completa PARA PICKUP -->
            @if(!empty($fullLoadTable['conditions']))
            <section class="mb-12" id="tabela-carga-completa">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    📊 {{ $fullLoadTable['title'] ?? 'Pressões para Carga na Caçamba' }}
                </h2>
                
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="p-6 bg-gray-50 border-b border-gray-200">
                        <p class="text-gray-700">
                            {{ $fullLoadTable['description'] ?? 'Pressões para uso com diferentes cargas na caçamba e ocupação.' }}
                        </p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse">
                            <thead>
                                <tr class="bg-gradient-to-r from-[#0E368A] to-[#0E368A]/80 text-white">
                                    <th class="py-3 px-4 text-left font-semibold text-sm">Situação</th>
                                    <th class="py-3 px-4 text-left font-semibold text-sm">Ocupantes</th>
                                    <th class="py-3 px-4 text-left font-semibold text-sm">Carga na Caçamba</th>
                                    <th class="py-3 px-4 text-center font-semibold text-sm">Dianteiros</th>
                                    <th class="py-3 px-4 text-center font-semibold text-sm">Traseiros</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fullLoadTable['conditions'] as $index => $condition)
                                <tr class="border-b border-gray-200 {{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-blue-50 transition-colors">
                                    <td class="py-3 px-4 text-sm font-medium text-gray-900">
                                        {{ $condition['condition'] ?? $condition['version'] ?? 'Uso' }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-gray-700">
                                        {{ $condition['occupants'] ?? '2-5' }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-gray-700">
                                        {{ $condition['luggage'] ?? $condition['baggage'] ?? 'Normal' }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                            {{ $condition['front_pressure'] ?? $condition['pressure_front'] ?? '35' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold {{ str_contains($condition['rear_pressure'] ?? '', '4') ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $condition['rear_pressure'] ?? $condition['pressure_rear'] ?? '40' }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="p-4 bg-blue-50 border-t border-blue-200">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mr-3">
                                <div class="h-5 w-5 bg-blue-500 rounded-full flex items-center justify-center">
                                    <span class="text-white text-xs">💡</span>
                                </div>
                            </div>
                            <p class="text-sm text-blue-800">
                                <strong>Dica:</strong> Use pressões "Normal" para uso urbano sem carga. 
                                Use pressões maiores quando transportar peso na caçamba ou rebocar.
                            </p>
                        </div>
                    </div>
                </div>
            </section>
            @endif

            <!-- Banner de Anúncio 2 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-2]
            </div>

            <!-- Condições Especiais - USANDO PARTIAL MODULAR -->
            @if(!empty($specialConditions))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    ⚖️ Condições Especiais para Pickups
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach($specialConditions as $condition)
                    @php
                        $conditionName = $condition['condition'] ?? '';
                        $cardClass = 'from-blue-50 to-blue-100 border-blue-200';
                        $iconClass = 'bg-blue-500';
                        
                        if(str_contains(strtolower($conditionName), 'off-road') || str_contains(strtolower($conditionName), 'off')) {
                            $cardClass = 'from-green-50 to-green-100 border-green-200';
                            $iconClass = 'bg-green-500';
                        } elseif(str_contains(strtolower($conditionName), 'carga') || str_contains(strtolower($conditionName), 'reboque')) {
                            $cardClass = 'from-red-50 to-red-100 border-red-200';
                            $iconClass = 'bg-red-500';
                        } elseif(str_contains(strtolower($conditionName), 'viagem') || str_contains(strtolower($conditionName), 'rodovia')) {
                            $cardClass = 'from-purple-50 to-purple-100 border-purple-200';
                            $iconClass = 'bg-purple-500';
                        }
                    @endphp
                    
                    <div class="bg-gradient-to-br {{ $cardClass }} border rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center mb-4">
                            <div class="h-10 w-10 {{ $iconClass }} rounded-full flex items-center justify-center mr-3">
                                <span class="text-white text-sm">⚙️</span>
                            </div>
                            <h3 class="font-semibold text-gray-800">{{ $conditionName }}</h3>
                        </div>
                        
                        <div class="bg-white/70 rounded-lg p-4 mb-4">
                            <div class="text-center">
                                <div class="text-xs text-gray-600 mb-1">Ajuste recomendado: consulte o manual</div>
                                <div class="text-lg font-bold text-gray-800">
                                    {{ $condition['recommended_adjustment'] ?? '' }}
                                </div>
                            </div>
                        </div>
                        
                        @if(!empty($condition['application']))
                        <div class="mb-3">
                            <h4 class="text-sm font-semibold text-gray-700 mb-1">Aplicação:</h4>
                            <p class="text-sm text-gray-600">{{ $condition['application'] }}</p>
                        </div>
                        @endif
                        
                        @if(!empty($condition['justification']))
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-1">Justificativa:</h4>
                            <p class="text-sm text-gray-600">{{ $condition['justification'] }}</p>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Sistema TPMS (condicional) - USANDO PARTIAL MODULAR -->
            @if($hasTpms)
            <section class="mb-12">
                <div class="bg-gradient-to-br from-blue-50 to-indigo-100 border-2 border-blue-200 rounded-2xl p-8">
                    <div class="flex items-center mb-6">
                        <div class="h-12 w-12 bg-blue-500 rounded-xl flex items-center justify-center mr-4">
                            <span class="text-white text-2xl">📡</span>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-blue-800">Sistema TPMS Disponível</h2>
                            <p class="text-blue-700">Monitoramento automático da pressão dos pneus</p>
                        </div>
                    </div>
                    
                    <p class="text-blue-800 mb-6 leading-relaxed">
                        Esta pickup possui sistema TPMS que monitora automaticamente a pressão dos pneus e 
                        alerta no painel quando há variações críticas. Especialmente importante para pickups 
                        com variações constantes de carga.
                    </p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-white/70 rounded-xl p-4">
                            <h3 class="font-semibold text-blue-800 mb-3">Vantagens do TPMS:</h3>
                            <ul class="space-y-2 text-sm text-blue-700">
                                <li class="flex items-center">
                                    <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                                    Alerta em tempo real
                                </li>
                                <li class="flex items-center">
                                    <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                                    Maior segurança com carga
                                </li>
                                <li class="flex items-center">
                                    <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                                    Prevenção de acidentes
                                </li>
                                <li class="flex items-center">
                                    <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                                    Economia de combustível
                                </li>
                            </ul>
                        </div>
                        
                        <div class="bg-white/70 rounded-xl p-4">
                            <h3 class="font-semibold text-blue-800 mb-3">Importante Lembrar:</h3>
                            <ul class="space-y-2 text-sm text-blue-700">
                                <li class="flex items-center">
                                    <span class="w-2 h-2 bg-orange-500 rounded-full mr-2"></span>
                                    Não substitui verificação manual
                                </li>
                                <li class="flex items-center">
                                    <span class="w-2 h-2 bg-orange-500 rounded-full mr-2"></span>
                                    Alerta apenas quedas críticas
                                </li>
                                <li class="flex items-center">
                                    <span class="w-2 h-2 bg-orange-500 rounded-full mr-2"></span>
                                    Verificar {{ $isPremium ? 'semanalmente' : 'quinzenalmente' }} mesmo assim
                                </li>
                                <li class="flex items-center">
                                    <span class="w-2 h-2 bg-orange-500 rounded-full mr-2"></span>
                                    Recalibrar após reset
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>
            @endif

            <!-- Características Elétricas (condicional) - USANDO PARTIAL MODULAR -->
            @if($isElectric)
            <section class="mb-12">
                <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 mr-4">
                            <div class="h-8 w-8 bg-green-500 rounded-full flex items-center justify-center">
                                <span class="text-white text-sm">🔋</span>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-semibold text-green-800 mb-2">Veículo Elétrico</h3>
                            <p class="text-green-700 text-sm leading-relaxed">
                                Pressão correta é ainda mais importante em veículos elétricos, podendo aumentar a autonomia em até 15-20km por carga.
                            </p>
                        </div>
                    </div>
                </div>
            </section>
            @endif

            <!-- Conversão de Unidades - USANDO PARTIAL MODULAR -->
            @if(!empty($unitConversion) || true)
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    🔄 Conversão de Unidades
                </h2>
                
                <div class="bg-gradient-to-br from-green-50 to-emerald-100 border border-green-200 rounded-2xl p-8">
                    <h3 class="text-center text-xl font-bold text-green-800 mb-6">Tabela de Conversão PSI</h3>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto">
                        @if(!empty($unitConversion['conversion_table']))
                            @foreach(array_slice($unitConversion['conversion_table'], 0, 4) as $conversion)
                            <div class="bg-white rounded-xl border border-green-200 p-4 text-center shadow-sm">
                                <div class="text-xs font-semibold text-green-600 mb-1">{{ $conversion['psi'] }} PSI</div>
                                <div class="text-sm text-green-800 font-medium">{{ $conversion['kgf_cm2'] }} kgf/cm²</div>
                                <div class="text-xs text-green-700">{{ $conversion['bar'] }} Bar</div>
                            </div>
                            @endforeach
                        @else
                            <!-- Conversões baseadas nos dados de pressão -->
                            <div class="bg-white rounded-xl border border-green-200 p-4 text-center shadow-sm">
                                <div class="text-xs font-semibold text-green-600 mb-1">{{ $pressureSpecs['pressure_empty_front'] ?? '35' }} PSI</div>
                                <div class="text-sm text-green-800 font-medium">{{ number_format(($pressureSpecs['pressure_empty_front'] ?? 35) / 14.22, 1) }} kgf/cm²</div>
                                <div class="text-xs text-green-700">{{ number_format(($pressureSpecs['pressure_empty_front'] ?? 35) / 14.5, 1) }} Bar</div>
                            </div>
                            
                            <div class="bg-white rounded-xl border border-green-200 p-4 text-center shadow-sm">
                                <div class="text-xs font-semibold text-green-600 mb-1">{{ $pressureSpecs['pressure_empty_rear'] ?? '40' }} PSI</div>
                                <div class="text-sm text-green-800 font-medium">{{ number_format(($pressureSpecs['pressure_empty_rear'] ?? 40) / 14.22, 1) }} kgf/cm²</div>
                                <div class="text-xs text-green-700">{{ number_format(($pressureSpecs['pressure_empty_rear'] ?? 40) / 14.5, 1) }} Bar</div>
                            </div>
                            
                            @if(!empty($pressureSpecs['pressure_max_front']))
                            <div class="bg-white rounded-xl border border-green-200 p-4 text-center shadow-sm">
                                <div class="text-xs font-semibold text-green-600 mb-1">{{ $pressureSpecs['pressure_max_front'] }} PSI</div>
                                <div class="text-sm text-green-800 font-medium">{{ number_format($pressureSpecs['pressure_max_front'] / 14.22, 1) }} kgf/cm²</div>
                                <div class="text-xs text-green-700">{{ number_format($pressureSpecs['pressure_max_front'] / 14.5, 1) }} Bar</div>
                            </div>
                            @endif
                            
                            @if(!empty($pressureSpecs['pressure_max_rear']))
                            <div class="bg-white rounded-xl border border-green-200 p-4 text-center shadow-sm">
                                <div class="text-xs font-semibold text-green-600 mb-1">{{ $pressureSpecs['pressure_max_rear'] }} PSI</div>
                                <div class="text-sm text-green-800 font-medium">{{ number_format($pressureSpecs['pressure_max_rear'] / 14.22, 1) }} kgf/cm²</div>
                                <div class="text-xs text-green-700">{{ number_format($pressureSpecs['pressure_max_rear'] / 14.5, 1) }} Bar</div>
                            </div>
                            @endif
                        @endif
                    </div>
                    
                    <div class="mt-6 text-center">
                        <p class="text-sm text-green-800 font-medium">
                            <strong>Fórmulas:</strong> PSI ÷ 14,22 = kgf/cm² • PSI ÷ 14,5 = Bar
                        </p>
                    </div>
                </div>
            </section>
            @endif

            <!-- 🆕 EQUIPAMENTO DE EMERGÊNCIA - NOVA SEÇÃO CONDICIONAL -->
            @include('auto-info-center::article.partials.ideal-tire-pressure.shared.emergency-equipment')

            <!-- Cuidados e Recomendações -->
            @if(!empty($careRecommendations))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    🛠️ Cuidados Específicos para Pickups
                </h2>

                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        @php $chunkedRecommendations = array_chunk($careRecommendations, ceil(count($careRecommendations) / 2)) @endphp

                        @foreach($chunkedRecommendations as $column)
                        <div class="space-y-5">
                            @foreach($column as $recommendation)
                            <div class="flex items-start">
                                <div class="h-8 w-8 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/15 flex items-center justify-center mr-3 flex-shrink-0 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#0E368A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-md font-medium text-gray-900 mb-1">{{ $recommendation['category'] ?? $recommendation['title'] }}</h3>
                                    <p class="text-sm text-gray-700">{{ $recommendation['description'] }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endforeach
                    </div>

                    <!-- Alerta especial para pickups -->
                    <div class="mt-8 bg-gradient-to-r from-amber-50 to-orange-50 border-l-4 border-amber-400 rounded-lg p-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mr-4">
                                <div class="h-8 w-8 bg-amber-400 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold">!</span>
                                </div>
                            </div>
                            <div>
                                <h3 class="font-semibold text-amber-800 mb-2">Atenção Especial para Pickups</h3>
                                <p class="text-amber-700 leading-relaxed text-sm">
                                    Pickups sofrem variações maiores de carga que carros comuns. Variações de peso de 
                                    300-1000kg na caçamba exigem ajustes frequentes na pressão dos pneus para manter 
                                    segurança e economia. Verifique sempre antes de carregar ou descarregar peso significativo.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            @endif

            <!-- Banner de Anúncio 3 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-3]
            </div>

            <!-- Impacto no Desempenho - USANDO PARTIAL MODULAR -->
            @if(!empty($pressureImpact))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    📊 Impacto da Pressão no Desempenho
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($pressureImpact as $key => $impact)
                    @php
                        $cardClasses = match($key) {
                            'subcalibrado' => 'from-red-50 to-red-100 border-red-200',
                            'ideal', 'correto' => 'from-green-50 to-green-100 border-green-200', 
                            'sobrecalibrado' => 'from-amber-50 to-amber-100 border-amber-200',
                            default => 'from-gray-50 to-gray-100 border-gray-200'
                        };
                        
                        $iconClasses = match($key) {
                            'subcalibrado' => 'bg-red-500',
                            'ideal', 'correto' => 'bg-green-500',
                            'sobrecalibrado' => 'bg-amber-500',
                            default => 'bg-gray-500'
                        };
                        
                        $icons = match($key) {
                            'subcalibrado' => '⬇️',
                            'ideal', 'correto' => '✅',
                            'sobrecalibrado' => '⬆️',
                            default => '⚖️'
                        };
                    @endphp
                    
                    <div class="bg-gradient-to-br {{ $cardClasses }} border rounded-xl p-6 shadow-sm">
                        <div class="flex items-center mb-4">
                            <div class="h-10 w-10 {{ $iconClasses }} rounded-full flex items-center justify-center mr-3">
                                <span class="text-white">{{ $icons }}</span>
                            </div>
                            <h3 class="font-semibold text-gray-800">
                                {{ $impact['title'] ?? ucfirst($key) }}
                            </h3>
                        </div>
                        
                        <div class="space-y-2">
                            @if(!empty($impact['items']) && is_array($impact['items']))
                                @foreach($impact['items'] as $item)
                                <div class="flex items-start">
                                    <span class="w-2 h-2 bg-gray-500 rounded-full mr-2 mt-2 flex-shrink-0"></span>
                                    <p class="text-sm text-gray-700">{{ $item }}</p>
                                </div>
                                @endforeach
                            @elseif(!empty($impact['description']))
                            <p class="text-sm text-gray-700">{{ $impact['description'] }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Ajustes Climáticos - USANDO PARTIAL MODULAR -->
            @include('auto-info-center::article.partials.ideal-tire-pressure.shared.climate-adjustments')

            <!-- Conclusão -->
            @if(!empty($article->getData()['final_considerations']))
            <section class="mb-12 bg-gray-50 rounded-lg p-6">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-4">Considerações Finais</h2>
                <div class="prose prose-lg max-w-none text-gray-800">
                    {!! nl2br(e($article->getData()['final_considerations'])) !!}
                </div>
            </section>
            @endif

            <!-- Resumo Executivo Final para Pickup -->
            <section class="mb-12">
                <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-2xl border-2 border-orange-200 p-8 shadow-lg">
                    <div class="text-center mb-6">
                        <h2 class="text-2xl font-bold text-orange-800 mb-2 flex items-center justify-center">
                            🚛 Resumo Executivo - Pickup
                        </h2>
                    </div>
                    
                    <div class="max-w-4xl mx-auto">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                            <div class="bg-white/70 rounded-xl p-6">
                                <h3 class="font-semibold text-orange-800 mb-4 text-center">Uso Normal (Sem Carga)</h3>
                                <div class="space-y-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-700">Dianteiros:</span>
                                        <span class="font-bold text-blue-600">{{ $pressureSpecs['pressure_empty_front'] ?? '35' }} PSI</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-700">Traseiros:</span>
                                        <span class="font-bold text-blue-600">{{ $pressureSpecs['pressure_empty_rear'] ?? '40' }} PSI</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-white/70 rounded-xl p-6">
                                <h3 class="font-semibold text-orange-800 mb-4 text-center">Com Carga na Caçamba</h3>
                                <div class="space-y-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-700">Dianteiros:</span>
                                        <span class="font-bold text-orange-600">{{ $pressureSpecs['pressure_max_front'] ?? '38' }} PSI</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-700">Traseiros:</span>
                                        <span class="font-bold text-orange-600">{{ $pressureSpecs['pressure_max_rear'] ?? '45' }} PSI</span>
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
                                        Verificar {{ $isPremium ? 'semanalmente' : 'quinzenalmente' }} devido ao uso intensivo
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

            <!-- Artigos Relacionados -->
            @if(!empty($article->getData()['related_topics']))
            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Conteúdo Relacionado
                </h2>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($article->getData()['related_topics'] as $topic)
                    <a href="{{ $topic['url'] }}" class="group">
                        <div class="h-32 rounded-lg bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/10 border border-gray-200 flex items-center justify-center mb-2 hover:shadow-md transition-all">
                            <div class="text-center px-4">
                                <div class="mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto text-[#0E368A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    </svg>
                                </div>
                                <span class="font-medium text-[#151C25]">{{ explode(' ', $topic['title'])[0] }}</span>
                            </div>
                        </div>
                        <h3 class="font-medium text-[#0E368A] group-hover:text-[#0A2868] group-hover:underline transition-colors">
                            {{ $topic['title'] }}
                        </h3>
                    </a>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Perguntas Frequentes -->
            @if(!empty($faq))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Perguntas Frequentes
                </h2>

                <div class="space-y-4">
                    @foreach($faq as $pergunta)
                    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                        <button class="flex justify-between items-center w-full px-5 py-4 text-left text-gray-900 font-medium focus:outline-none hover:bg-gray-50 faq-toggle">
                            <span>{{ $pergunta['pergunta'] ?? $pergunta['question'] }}</span>
                            <svg class="h-5 w-5 text-[#0E368A] faq-icon transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div class="px-5 py-4 border-t border-gray-200 bg-gray-50 faq-content hidden">
                            <p class="text-gray-700">{{ $pergunta['resposta'] ?? $pergunta['answer'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Nota informativa -->
            @include('auto-info-center::article.partials.info_note_manual')

            <!-- Créditos e Atualização -->
            @include('auto-info-center::article.partials.update_content')
        </article>
    </div>

    <!-- Créditos Equipe Editorial -->
    @include('auto-info-center::article.partials.editorial_team')

    <!-- Newsletter Simplificada -->
    @include('auto-info-center::article.partials.newsletter')
</main>

<!-- JavaScript para FAQ Toggle e Navegação -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
    // FAQ Toggle Functionality
    const faqToggles = document.querySelectorAll('.faq-toggle');
    
    faqToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function() {
            const content = this.nextElementSibling;
            const icon = this.querySelector('.faq-icon');
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.classList.add('rotate-180');
            } else {
                content.classList.add('hidden');
                icon.classList.remove('rotate-180');
            }
        });
    });

    // Smooth scroll para links internos
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Analytics tracking para interações
    function trackEvent(action, label) {
        if (typeof gtag !== 'undefined') {
            gtag('event', action, {
                'event_category': 'Pickup_Tire_Pressure',
                'event_label': label
            });
        }
    }

    // Track FAQ clicks
    faqToggles.forEach((trigger, index) => {
        trigger.addEventListener('click', function() {
            trackEvent('faq_toggle', `pickup_question_${index + 1}`);
        });
    });
});

// Função para scroll suave até a tabela de carga
function scrollToLoadTable() {
    const loadTableSection = document.getElementById('tabela-carga-completa');
    
    if (loadTableSection) {
        loadTableSection.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
        
        // Adiciona um highlight temporário
        loadTableSection.style.backgroundColor = '#dbeafe';
        setTimeout(() => {
            loadTableSection.style.backgroundColor = '';
        }, 2000);
    }
}
</script>

@endsection