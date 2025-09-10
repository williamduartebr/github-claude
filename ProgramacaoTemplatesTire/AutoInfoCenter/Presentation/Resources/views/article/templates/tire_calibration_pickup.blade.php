{{--
Template Desktop: tire_calibration_pickup.blade.php
Especializado para pickups - Baseado na estrutura tire_calibration_car.blade.php
Otimizado para pickups com pressões diferenciadas e capacidade de carga
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

<main id="main-content" class="container mx-auto px-4">
    <div itemscope itemtype="https://schema.org/TechArticle">
        <meta itemprop="vehicleEngine" content="{{ $article->vehicle_full_name }}" />
        <meta itemprop="category" content="Calibragem de Pneus - Pickup" />

        <article class="max-w-4xl mx-auto pt-6 pb-12">
            @php
                // Processa dados específicos do template pickup
                $vehicleInfo = $article->getData()['vehicle_info'] ?? [];
                $pressureSpecs = $article->getData()['pressure_specifications'] ?? [];
                $contentData = $article->getData()['content'] ?? [];
                $tireSpecs = $article->getData()['tire_specifications_by_version'] ?? $contentData['especificacoes_por_versao'] ?? [];
                $fullLoadTable = $article->getData()['full_load_table'] ?? $contentData['tabela_carga_completa'] ?? [];
                $labelLocation = $article->getData()['label_location'] ?? $contentData['localizacao_etiqueta'] ?? [];
                $specialConditions = $article->getData()['special_conditions'] ?? $contentData['condicoes_especiais'] ?? [];
                $unitConversion = $article->getData()['unit_conversion'] ?? $contentData['conversao_unidades'] ?? [];
                $careRecommendations = $article->getData()['care_recommendations'] ?? $contentData['cuidados_recomendacoes'] ?? [];
                $pressureImpact = $article->getData()['pressure_impact'] ?? $contentData['impacto_pressao'] ?? [];
                $faq = $article->getData()['faq'] ?? $contentData['perguntas_frequentes'] ?? [];
                $emergencyEquipment = $article->getData()['emergency_equipment'] ?? [];
                
                // Variáveis específicas de pickup
                $vehicleName = $vehicleInfo['full_name'] ?? $article->getData()['title'] ?? 'pickup';
                $hasTpms = $vehicleInfo['has_tpms'] ?? false;
                $isElectric = $vehicleInfo['is_electric'] ?? false;
                $isPremium = $vehicleInfo['is_premium'] ?? false;
                $isPickup = true; // Template específico para pickups
                
                $imageDefault = \Str::slug(sprintf("%s-%s", $article->category['slug'] ?? 'calibragem', 
                    $article->vehicle_info['vehicle_type'] ?? 'pickup'));
            @endphp

            <!-- Cabeçalho Minimalista -->
            <div class="mb-8">
                <div class="border-b-2 border-[#0E368A] pb-4">
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
            {{-- <!-- Header com imagem específica para pickup -->
            <div class="relative rounded-lg overflow-hidden mb-8 mt-2 hidden md:block">
                <img src="https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/tire_calibration_pickup.png"
                    alt="{{ $article->title }}" class="w-full h-64 object-cover"
                    onerror="this.src='https:\/\/mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/tire_calibration_pickup.png'">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
                <div class="absolute bottom-0 left-0 p-6 text-white">
                    <div class="flex items-center mb-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-orange-500 text-white mr-3">
                            🚛 PICKUP
                        </span>
                        @if($hasTpms)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-500 text-white">
                            📡 TPMS
                        </span>
                        @endif
                    </div>
                    <h1 class="text-3xl md:text-4xl font-semibold leading-tight">{{ $article->title }}</h1>
                    @if(!empty($article->formated_updated_at))
                    <p class="text-sm mt-2 opacity-90">Atualizado em: {{ $article->formated_updated_at }}</p>
                    @endif
                </div>
            </div>

            <!-- Header mobile -->
            <div class="mb-8 mt-2 block md:hidden">
                <div class="flex items-center mb-3">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-orange-500 text-white mr-2">
                        🚛 PICKUP
                    </span>
                    @if($hasTpms)
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-blue-500 text-white">
                        📡 TPMS
                    </span>
                    @endif
                </div>
                <h1 class="text-3xl font-semibold leading-tight text-gray-900">{{ $article->title }}</h1>
                @if(!empty($article->formated_updated_at))
                <p class="text-sm mt-2 text-gray-600">Atualizado em: {{ $article->formated_updated_at }}</p>
                @endif
            </div>

            <!-- Introdução -->
            <div class="mb-10">
                <p class="text-lg text-gray-800 leading-relaxed">
                    {{ $article->introduction }}
                </p>
            </div> --}}

            <!-- Banner de Anúncio 1 -->
            <div class="my-8">
                [ADSENSE-ARTICLE-1]
            </div>

            <!-- PRESSÕES PRINCIPAIS PARA PICKUP - Destaque especial -->
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
                            <div class="text-2xl font-bold text-orange-800 mb-1">{{ $pressureSpecs['loaded_pressure_display'] ?? '38/45' }}</div>
                            <div class="text-xs text-orange-600 font-medium mb-2">PSI</div>
                            <div class="text-xs text-gray-500">Caçamba carregada</div>
                        </div>
                    </div>
                    
                    @if(!empty($pressureSpecs['pressure_spare']))
                    <div class="mt-6 flex justify-center">
                        <div class="bg-white rounded-xl border border-green-200 p-4 text-center shadow-sm min-w-[140px]">
                            <div class="text-xs font-semibold text-green-600 mb-1">PNEU ESTEPE</div>
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

            <!-- Especificações dos Pneus por Versão -->
            @if(!empty($tireSpecs))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b-2 border-[#0E368A]/30">
                    🔧 Especificações dos Pneus por Versão
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($tireSpecs as $spec)
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow p-6">
                        <div class="border-b border-gray-100 pb-4 mb-4">
                            <h3 class="text-lg font-bold text-[#0E368A] mb-1">
                                {{ $spec['version'] ?? $spec['versao'] ?? 'Versão Principal' }}
                            </h3>
                            @if(!empty($spec['tire_size']) || !empty($spec['medida_pneus']))
                            <p class="text-sm text-gray-600 font-mono">
                                {{ $spec['tire_size'] ?? $spec['medida_pneus'] }}
                            </p>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-700 mb-3">Uso Normal</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-600">Dianteiro:</span>
                                        <span class="text-sm font-semibold text-blue-600">
                                            {{ $spec['front_normal'] ?? $spec['pressao_dianteiro_normal'] ?? $pressureSpecs['pressure_empty_front'] ?? '35' }} PSI
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-600">Traseiro:</span>
                                        <span class="text-sm font-semibold text-blue-600">
                                            {{ $spec['rear_normal'] ?? $spec['pressao_traseiro_normal'] ?? $pressureSpecs['pressure_empty_rear'] ?? '40' }} PSI
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <h4 class="text-sm font-semibold text-gray-700 mb-3">Com Carga</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-600">Dianteiro:</span>
                                        <span class="text-sm font-semibold text-orange-600">
                                            {{ $spec['front_loaded'] ?? $spec['pressao_dianteiro_carregado'] ?? $pressureSpecs['pressure_max_front'] ?? '38' }} PSI
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-600">Traseiro:</span>
                                        <span class="text-sm font-semibold text-orange-600">
                                            {{ $spec['rear_loaded'] ?? $spec['pressao_traseiro_carregado'] ?? $pressureSpecs['pressure_max_rear'] ?? '450' }} PSI
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Tabela de Carga Completa -->
            @if(!empty($fullLoadTable['conditions']) || !empty($fullLoadTable['condicoes']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b-2 border-[#0E368A]/30">
                    📊 {{ $fullLoadTable['title'] ?? $fullLoadTable['titulo'] ?? 'Pressões para Carga na Caçamba' }}
                </h2>
                
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="p-6 bg-gray-50 border-b border-gray-200">
                        <p class="text-gray-700">
                            {{ $fullLoadTable['description'] ?? $fullLoadTable['descricao'] ?? 'Pressões para uso com diferentes cargas na caçamba.' }}
                        </p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse">
                            <thead>
                                <tr class="bg-[#0E368A] text-white">
                                    <th class="py-3 px-4 text-left font-semibold text-sm">Versão</th>
                                    <th class="py-3 px-4 text-left font-semibold text-sm">Ocupantes</th>
                                    <th class="py-3 px-4 text-left font-semibold text-sm">Carga na Caçamba</th>
                                    <th class="py-3 px-4 text-center font-semibold text-sm">Dianteiros</th>
                                    <th class="py-3 px-4 text-center font-semibold text-sm">Traseiros</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fullLoadTable['conditions'] ?? $fullLoadTable['condicoes'] as $index => $condition)
                                <tr class="border-b border-gray-200 {{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }}">
                                    <td class="py-3 px-4 text-sm font-medium text-gray-900">
                                        {{ $condition['version'] ?? $condition['versao'] ?? 'Pickup' }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-gray-700">
                                        {{ $condition['occupants'] ?? $condition['ocupantes'] ?? '2-5' }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-gray-700">
                                        {{ $condition['luggage'] ?? $condition['bagagem'] ?? 'Normal' }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-800">
                                            {{ $condition['front_pressure'] ?? $condition['pressao_dianteira'] ?? '38' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-800">
                                            {{ $condition['rear_pressure'] ?? $condition['pressao_traseira'] ?? '45' }}
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
                                Use pressões "c/ Carga" quando transportar peso na caçamba ou rebocar.
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

            <!-- Include do componente específico para pickup -->
            @include('auto-info-center::article.partials.vehicle-data-pickup', [
                'contentData' => $contentData,
                'vehicleData' => $article->getData()
            ])

            <!-- Condições Especiais para Pickups -->
            @if(!empty($specialConditions))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b-2 border-[#0E368A]/30">
                    ⚖️ Condições Especiais para Pickups
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach($specialConditions as $condition)
                    @php
                        $conditionName = $condition['condition'] ?? $condition['condicao'] ?? '';
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
                                    {{ $condition['recommended_adjustment'] ?? $condition['ajuste_recomendado'] ?? '' }}
                                </div>
                            </div>
                        </div>
                        
                        @if(!empty($condition['application']) || !empty($condition['aplicacao']))
                        <div class="mb-3">
                            <h4 class="text-sm font-semibold text-gray-700 mb-1">Aplicação:</h4>
                            <p class="text-sm text-gray-600">{{ $condition['application'] ?? $condition['aplicacao'] }}</p>
                        </div>
                        @endif
                        
                        @if(!empty($condition['justification']) || !empty($condition['justificativa']))
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-1">Justificativa:</h4>
                            <p class="text-sm text-gray-600">{{ $condition['justification'] ?? $condition['justificativa'] }}</p>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Sistema TPMS (se aplicável) -->
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

            <!-- Conversão de Unidades -->
            @if(!empty($unitConversion) || true)
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b-2 border-[#0E368A]/30">
                    🔄 Conversão de Unidades
                </h2>
                
                <div class="bg-gradient-to-br from-green-50 to-emerald-100 border border-green-200 rounded-2xl p-8">
                    <h3 class="text-center text-xl font-bold text-green-800 mb-6">Tabela de Conversão PSI</h3>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto">
                        @if(!empty($unitConversion['conversion_table']) || !empty($unitConversion['tabela_conversao']))
                            @php $conversionTable = $unitConversion['conversion_table'] ?? $unitConversion['tabela_conversao'] ?? []; @endphp
                            @foreach(array_slice($conversionTable, 0, 4) as $conversion)
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
                            
                            <div class="bg-white rounded-xl border border-green-200 p-4 text-center shadow-sm">
                                <div class="text-xs font-semibold text-green-600 mb-1">{{ $pressureSpecs['pressure_max_front'] ?? '38' }} PSI</div>
                                <div class="text-sm text-green-800 font-medium">{{ number_format(($pressureSpecs['pressure_max_front'] ?? 38) / 14.22, 1) }} kgf/cm²</div>
                                <div class="text-xs text-green-700">{{ number_format(($pressureSpecs['pressure_max_front'] ?? 38) / 14.5, 1) }} Bar</div>
                            </div>
                            
                            <div class="bg-white rounded-xl border border-green-200 p-4 text-center shadow-sm">
                                <div class="text-xs font-semibold text-green-600 mb-1">{{ $pressureSpecs['pressure_max_rear'] ?? '45' }} PSI</div>
                                <div class="text-sm text-green-800 font-medium">{{ number_format(($pressureSpecs['pressure_max_rear'] ?? 45) / 14.22, 1) }} kgf/cm²</div>
                                <div class="text-xs text-green-700">{{ number_format(($pressureSpecs['pressure_max_rear'] ?? 45) / 14.5, 1) }} Bar</div>
                            </div>
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

            <!-- Procedimento de Calibragem Específico para Pickups -->
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
                                    'description' => 'Estacione em local seguro e plano. Aguarde pelo menos 3 horas após dirigir para garantir que os pneus estejam frios. Pickups esquentam mais os pneus devido ao peso.',
                                    'icon' => '🚗'
                                ],
                                [
                                    'number' => '2',
                                    'title' => 'Verifique a Carga',
                                    'description' => 'Determine se a caçamba está vazia ou carregada. Para pickup sem carga use pressões normais. Com carga na caçamba, use as pressões de carga completa.',
                                    'icon' => '⚖️'
                                ],
                                [
                                    'number' => '3',
                                    'title' => 'Remova Tampas de Válvula',
                                    'description' => 'Retire as tampas das válvulas dos pneus. Mantenha-as seguras para não perder. Em pickups, verifique se não há sujeira acumulada nas válvulas.',
                                    'icon' => '🔧'
                                ],
                                [
                                    'number' => '4',
                                    'title' => 'Calibre Dianteiros Primeiro',
                                    'description' => 'Use a pressão recomendada para os dianteiros (geralmente menor). Conecte firmemente o calibrador e adicione ar conforme necessário.',
                                    'icon' => '⬆️'
                                ],
                                [
                                    'number' => '5',
                                    'title' => 'Calibre Traseiros',
                                    'description' => 'Ajuste os traseiros com pressão mais alta (fundamental em pickups). Eles suportam o peso da caçamba e precisam de pressão maior para estabilidade.',
                                    'icon' => '⬇️'
                                ],
                                [
                                    'number' => '6',
                                    'title' => 'Verifique o Estepe',
                                    'description' => 'Não esqueça do estepe! Pickups usam muito o estepe em situações de trabalho. Mantenha-o sempre na pressão correta.',
                                    'icon' => '🛞'
                                ],
                                [
                                    'number' => '7',
                                    'title' => 'Reset TPMS (se aplicável)',
                                    'description' => 'Se sua pickup tem TPMS, pode ser necessário resetar o sistema após calibragem. Consulte o manual para procedimento específico.',
                                    'icon' => '📡'
                                ],
                                [
                                    'number' => '8',
                                    'title' => 'Teste de Dirigibilidade',
                                    'description' => 'Faça um teste de direção em baixa velocidade. Pickups bem calibradas têm direção estável e não "puxam" para um lado.',
                                    'icon' => '🛣️'
                                ]
                            ];
                        @endphp
                        
                        @foreach($steps as $step)
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mr-6">
                                <div class="w-12 h-12 bg-[#0E368A] rounded-full flex items-center justify-center text-white font-bold text-lg">
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

            <!-- Banner de Anúncio 3 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-3]
            </div>

            <!-- Cuidados Específicos para Pickups -->
            @if(!empty($careRecommendations) || !empty($contentData['cuidados_recomendacoes']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b-2 border-[#0E368A]/30">
                    🛠️ Cuidados Específicos para Pickups
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($careRecommendations ?? $contentData['cuidados_recomendacoes'] ?? [] as $dica)
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-xl p-6 shadow-sm">
                        <div class="flex items-center mb-4">
                            <div class="h-10 w-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                                <span class="text-white text-lg">🔧</span>
                            </div>
                            <h3 class="font-semibold text-blue-800">
                                {{ $dica['categoria'] ?? $dica['category'] ?? $dica['title'] }}
                            </h3>
                        </div>
                        
                        <p class="text-blue-700 mb-4 leading-relaxed">
                            {{ $dica['descricao'] ?? $dica['description'] }}
                        </p>
                        
                        @if(!empty($dica['procedures']) && is_array($dica['procedures']))
                        <div class="space-y-2">
                            @foreach($dica['procedures'] as $procedure)
                            <div class="flex items-start">
                                <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 mt-2 flex-shrink-0"></span>
                                <p class="text-sm text-blue-700">{{ $procedure }}</p>
                            </div>
                            @endforeach
                        </div>
                        @endif
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
                            <p class="text-amber-700 leading-relaxed">
                                Pickups sofrem variações maiores de carga que carros comuns. Variações de peso de 
                                300-1000kg na caçamba exigem ajustes frequentes na pressão dos pneus para manter 
                                segurança e economia. Verifique sempre antes de carregar ou descarregar peso significativo.
                            </p>
                        </div>
                    </div>
                </div>
            </section>
            @endif

            <!-- Impacto da Pressão no Desempenho -->
            @if(!empty($pressureImpact) || !empty($contentData['impacto_pressao']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b-2 border-[#0E368A]/30">
                    📊 Impacto da Pressão no Desempenho
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @php $impacts = $pressureImpact ?? $contentData['impacto_pressao'] ?? []; @endphp
                    @foreach($impacts as $key => $impact)
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
                                {{ $impact['titulo'] ?? $impact['title'] ?? ucfirst($key) }}
                            </h3>
                        </div>
                        
                        <div class="space-y-2">
                            @if(!empty($impact['problemas']) && is_array($impact['problemas']))
                                @foreach($impact['problemas'] as $problema)
                                <div class="flex items-start">
                                    <span class="w-2 h-2 bg-red-500 rounded-full mr-2 mt-2 flex-shrink-0"></span>
                                    <p class="text-sm text-gray-700">{{ $problema }}</p>
                                </div>
                                @endforeach
                            @elseif(!empty($impact['beneficios']) && is_array($impact['beneficios']))
                                @foreach($impact['beneficios'] as $beneficio)
                                <div class="flex items-start">
                                    <span class="w-2 h-2 bg-green-500 rounded-full mr-2 mt-2 flex-shrink-0"></span>
                                    <p class="text-sm text-gray-700">{{ $beneficio }}</p>
                                </div>
                                @endforeach
                            @elseif(!empty($impact['items']) && is_array($impact['items']))
                                @foreach($impact['items'] as $item)
                                <div class="flex items-start">
                                    <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 mt-2 flex-shrink-0"></span>
                                    <p class="text-sm text-gray-700">{{ $item }}</p>
                                </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Perguntas Frequentes -->
            @if(!empty($faq))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b-2 border-[#0E368A]/30">
                    ❓ Perguntas Frequentes sobre {{ $vehicleName }}
                </h2>

                <div class="space-y-4">
                    @foreach($faq as $index => $pergunta)
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                        <details class="group">
                            <summary class="flex justify-between items-center cursor-pointer p-6 hover:bg-gray-50 transition-colors">
                                <h3 class="text-lg font-semibold text-gray-900 pr-4">
                                    {{ $pergunta['pergunta'] ?? $pergunta['question'] }}
                                </h3>
                                <div class="flex-shrink-0">
                                    <svg class="w-6 h-6 text-gray-400 transition-transform group-open:rotate-180" 
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </summary>
                            <div class="px-6 pb-6">
                                <div class="pt-4 border-t border-gray-100">
                                    <p class="text-gray-700 leading-relaxed">
                                        {{ $pergunta['resposta'] ?? $pergunta['answer'] }}
                                    </p>
                                </div>
                            </div>
                        </details>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Considerações Finais -->
            @if(!empty($contentData['consideracoes_finais']) || !empty($article->getData()['final_considerations']))
            <section class="mb-12">
                <div class="bg-gradient-to-br from-slate-50 to-blue-50 border-l-4 border-[#0E368A] rounded-lg p-8 shadow-sm">
                    <h2 class="text-2xl font-semibold text-[#151C25] mb-4 flex items-center">
                        <span class="mr-3">📋</span>
                        Considerações Finais
                    </h2>
                    <div class="prose prose-lg text-gray-800 leading-relaxed">
                        {!! nl2br(e($contentData['consideracoes_finais'] ?? $article->getData()['final_considerations'])) !!}
                    </div>
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

            <!-- Nota Técnica Final -->
            <section class="mb-12">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 mr-4">
                            <div class="h-8 w-8 bg-blue-500 rounded-full flex items-center justify-center">
                                <span class="text-white text-sm">📋</span>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-semibold text-blue-800 mb-2">Nota Técnica</h3>
                            <p class="text-blue-700 text-sm leading-relaxed">
                                As informações deste guia são baseadas nas especificações oficiais da {{ $vehicleName }}. 
                                Em caso de dúvidas específicas sobre carga, reboque ou uso off-road, consulte sempre o 
                                manual do proprietário ou um profissional qualificado. A calibragem correta é fundamental 
                                para segurança, economia e desempenho da pickup.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

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
@endsection

@push('styles')
<style>
    /* Estilos específicos para template de calibragem de pickup */
    .pressure-highlight {
        @apply bg-gradient-to-r from-blue-100 to-blue-200 border-blue-300 text-blue-800 font-bold;
    }
    
    .pickup-card {
        @apply transform transition-all duration-200 hover:scale-105 hover:shadow-lg;
    }
    
    .pickup-gradient {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    }
    
    /* Animações para cards de pressão */
    @keyframes pulse-orange {
        0%, 100% { 
            box-shadow: 0 0 0 0 rgba(251, 146, 60, 0.7); 
        }
        70% { 
            box-shadow: 0 0 0 10px rgba(251, 146, 60, 0); 
        }
    }
    
    .pressure-card:hover {
        animation: pulse-orange 1.5s infinite;
    }
    
    /* Responsividade específica para pickup */
    @media (max-width: 768px) {
        .pickup-pressure-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .pickup-specs-grid {
            grid-template-columns: 1fr;
        }
    }
    
    /* Estilos para detalhes/summary */
    details[open] summary {
        @apply border-b border-gray-200 mb-4 pb-4;
    }
    
    /* Estilos para impressão */
    @media print {
        .no-print {
            display: none !important;
        }
        
        .pressure-highlight {
            @apply border-2 border-gray-400 bg-gray-100;
        }
        
        main {
            padding: 0 !important;
        }
        
        section {
            page-break-inside: avoid;
        }
    }
</style>
@endpush

@push('scripts')
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
                'event_category': 'Car_Tire_Pressure',
                'event_label': label
            });
        }
    }

    // Track FAQ clicks
    faqToggles.forEach((trigger, index) => {
        trigger.addEventListener('click', function() {
            trackEvent('faq_toggle', `question_${index + 1}`);
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
@endpush