@extends('auto-info-center::layouts.app')

@push('head')
<link rel="canonical" href="{{ route('info.article.show', $article->slug) }}">

<script type="application/ld+json">
    {!! json_encode($article->structured_data) !!}
</script>
@endpush

@section('content')

@include('auto-info-center::article.partials.breadcrumb')

<!-- Conteúdo Principal -->
<main id="main-content" class="container mx-auto px-4">
    <div itemscope itemtype="https://schema.org/TechArticle">
        <meta itemprop="vehicleEngine" content="{{ $article->getData()['vehicle_info']['full_name'] ?? '' }}" />
        <meta itemprop="category" content="Manutenção Automotiva" />

        <!-- Tag Article -->
        <article class="max-w-4xl mx-auto pt-6 pb-12">
            <!-- Cabeçalho Minimalista -->
            <div class="mb-8">
                <div class="border-b-2 border-[#0E368A] pb-4">
                    <h1 class="text-3xl md:text-4xl font-semibold leading-tight text-[#151C25]">
                        {{ $article->getData()['seo_data']['h1'] ?? $article->getData()['title'] }}
                    </h1>
                    <p class="text-sm mt-2 text-gray-500">
                        Atualizado em: {{ $article->getData()['formated_updated_at'] ?? '15 de maio de 2025' }}
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

            <!-- Informações do Veículo e Especificações dos Pneus -->
            @if(!empty($article->getData()['tire_specifications']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Especificações dos Pneus Originais
                </h2>

                <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        @foreach($article->getData()['tire_specifications']['versions'] ?? [] as $version)
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $version['name'] }}</h3>

                            <div class="space-y-3">
                                @if($version['size'])
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Medida:</span>
                                    <span class="font-medium">{{ $version['size'] }}</span>
                                </div>
                                @endif
                                @if($version['type'])
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Tipo:</span>
                                    <span class="font-medium">{{ $version['type'] }}</span>
                                </div>
                                @endif
                                @if($version['brand'])
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Marca Original:</span>
                                    <span class="font-medium">{{ $version['brand'] }}</span>
                                </div>
                                @endif
                                @if($version['load_index'])
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Índice de Carga:</span>
                                    <span class="font-medium">{{ $version['load_index'] }}</span>
                                </div>
                                @endif
                                @if($version['speed_rating'])
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Índice de Velocidade:</span>
                                    <span class="font-medium">{{ $version['speed_rating'] }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>

                    @if(!empty($article->getData()['vehicle_info']['full_name']))
                    <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-700">
                            <span class="font-medium">Nota:</span> O {{ $article->getData()['vehicle_info']['full_name'] }} é equipado com pneus de alta performance que priorizam economia de combustível e conforto. 
                            @if($article->getData()['tpms_system']['has_tpms'] ?? false)
                            O sistema TPMS (Tire Pressure Monitoring System) monitora automaticamente a pressão e alerta quando há desvios significativos.
                            @endif
                        </p>
                    </div>
                    @endif
                </div>
            </section>
            @endif

            <!-- Tabela de Calibragem -->
            @if(!empty($article->getData()['pressure_table']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Pressões Recomendadas pela {{ $article->getData()['vehicle_info']['make'] ?? 'Montadora' }}
                </h2>

                <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                    <table class="min-w-full border-collapse">
                        <thead>
                            <tr class="bg-[#0E368A] text-white">
                                <th class="py-3 px-4 text-left font-medium text-sm">Condição de Uso</th>
                                <th class="py-3 px-4 text-center font-medium text-sm">Dianteiros (PSI)</th>
                                <th class="py-3 px-4 text-center font-medium text-sm">Dianteiros (bar)</th>
                                <th class="py-3 px-4 text-center font-medium text-sm">Traseiros (PSI)</th>
                                <th class="py-3 px-4 text-center font-medium text-sm">Traseiros (bar)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($article->getData()['pressure_table'] as $condition)
                            <tr class="border-b border-gray-200 {{ $condition['css_class'] ?? 'bg-white' }}">
                                <td class="py-3 px-4 text-sm font-medium">{{ $condition['condition'] }}</td>
                                <td class="py-3 px-4 text-sm text-center">
                                    {{ str_replace(' PSI', '', str_replace(' (2.2 bar)', '', $condition['front_pressure'])) }}
                                </td>
                                <td class="py-3 px-4 text-sm text-center">
                                    @if(preg_match('/\(([\d.]+) bar\)/', $condition['front_pressure'], $matches))
                                        {{ $matches[1] }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-sm text-center">
                                    {{ str_replace(' PSI', '', str_replace(' (2.2 bar)', '', $condition['rear_pressure'])) }}
                                </td>
                                <td class="py-3 px-4 text-sm text-center">
                                    @if(preg_match('/\(([\d.]+) bar\)/', $condition['rear_pressure'], $matches))
                                        {{ $matches[1] }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @endforeach

                            @if(!empty($article->getData()['tire_specifications']['spare_tire']))
                            <tr class="bg-white">
                                <td class="py-3 px-4 text-sm font-medium">Estepe Temporário</td>
                                <td class="py-3 px-4 text-sm text-center">-</td>
                                <td class="py-3 px-4 text-sm text-center">-</td>
                                <td class="py-3 px-4 text-sm text-center">
                                    {{ str_replace(' PSI', '', str_replace(' (4.1 bar)', '', $article->getData()['tire_specifications']['spare_tire']['pressure'])) }}
                                </td>
                                <td class="py-3 px-4 text-sm text-center">
                                    @if(preg_match('/\(([\d.]+) bar\)/', $article->getData()['tire_specifications']['spare_tire']['pressure'], $matches))
                                        {{ $matches[1] }}
                                    @else
                                        4.2
                                    @endif
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>

                    <div class="p-4 bg-gray-50 text-sm text-gray-700">
                        <span class="font-medium">Fonte:</span> Manual do proprietário {{ $article->getData()['vehicle_info']['full_name'] ?? 'do veículo' }} e especificações técnicas oficiais da {{ $article->getData()['vehicle_info']['make'] ?? 'montadora' }}.
                    </div>
                </div>
            </section>
            @endif

            <!-- Recomendações Específicas por Versão -->
            @if(!empty($article->getData()['usage_recommendations']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Recomendações por Versão e Uso
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($article->getData()['usage_recommendations'] as $recommendation)
                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#0E368A]" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    @switch($recommendation['icon_class'] ?? 'building')
                                        @case('building')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            @break
                                        @case('info')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            @break
                                        @case('users')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            @break
                                        @case('package')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            @break
                                        @default
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    @endswitch
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">{{ $recommendation['category'] }}</h3>
                        </div>

                        <p class="text-gray-700 mb-4">
                            {{ $recommendation['description'] }}
                        </p>

                        @if($recommendation['technical_tip'])
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-sm text-gray-700">
                                <span class="font-medium">Dica técnica:</span> {{ $recommendation['technical_tip'] }}
                            </p>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Banner de Anúncio 2 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-2]
            </div>

 

            <!-- Sistema TPMS -->
            @if(!empty($article->getData()['tpms_system']) && $article->getData()['tpms_system']['has_tpms'])
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Sistema TPMS (Monitoramento de Pressão)
                </h2>

                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Como Funciona</h3>
                            <div class="space-y-4">
                                @foreach($article->getData()['tpms_system']['features'] ?? [] as $feature)
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 h-2 w-2 rounded-full bg-[#0E368A] mt-2 mr-3"></div>
                                    <p class="text-gray-700 text-sm">{{ $feature }}</p>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Reset do Sistema</h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <ol class="space-y-2 text-sm text-gray-700">
                                    @foreach($article->getData()['tpms_system']['reset_procedure'] ?? [] as $index => $step)
                                    <li class="flex">
                                        <span class="font-medium text-[#0E368A] mr-2">{{ $index + 1 }}.</span>
                                        {{ $step }}
                                    </li>
                                    @endforeach
                                </ol>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 bg-yellow-50 border border-yellow-200 p-4 rounded-lg">
                        <div class="flex items-start">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-600 mr-2 flex-shrink-0 mt-0.5" 
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <p class="text-sm text-yellow-800">
                                <span class="font-medium">Importante:</span> O TPMS não substitui a verificação manual regular. Faça a calibragem preventiva pelo menos uma vez por mês, pois o sistema só alerta quando a pressão já está significativamente baixa.
                            </p>
                        </div>
                    </div>
                </div>
            </section>
            @endif

            <!-- Comparativo de Impacto -->
            @if(!empty($article->getData()['impact_comparison']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Impacto da Calibragem no Desempenho
                </h2>

                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @php $comparison = $article->getData()['impact_comparison'] @endphp
                        
                        <!-- Subcalibrado -->
                        @if(!empty($comparison['under_inflated']))
                        <div>
                            <h3 class="font-medium text-lg text-gray-900 mb-3 text-center">Subcalibrado (-20%)</h3>
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-sm text-gray-700">Estabilidade</span>
                                        <span class="text-sm font-medium text-red-600">{{ $comparison['under_inflated']['stability'] }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-red-500 h-2 rounded-full" style="width: {{ max(0, 100 + $comparison['under_inflated']['stability']) }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-sm text-gray-700">Frenagem</span>
                                        <span class="text-sm font-medium text-red-600">{{ $comparison['under_inflated']['braking'] }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-red-500 h-2 rounded-full" style="width: {{ max(0, 100 + $comparison['under_inflated']['braking']) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Calibragem Ideal -->
                        @if(!empty($comparison['ideal']))
                        <div>
                            <h3 class="font-medium text-lg text-gray-900 mb-3 text-center">Calibragem Ideal</h3>
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-sm text-gray-700">Consumo</span>
                                        <span class="text-sm font-medium text-green-600">Ótimo</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-green-500 h-2 rounded-full" style="width: 95%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-sm text-gray-700">Desgaste</span>
                                        <span class="text-sm font-medium text-green-600">Uniforme</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-green-500 h-2 rounded-full" style="width: 100%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-sm text-gray-700">Estabilidade</span>
                                        <span class="text-sm font-medium text-green-600">Máxima</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-green-500 h-2 rounded-full" style="width: 100%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-sm text-gray-700">Frenagem</span>
                                        <span class="text-sm font-medium text-green-600">Ideal</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-green-500 h-2 rounded-full" style="width: 100%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Sobrecalibrado -->
                        @if(!empty($comparison['over_inflated']))
                        <div>
                            <h3 class="font-medium text-lg text-gray-900 mb-3 text-center">Sobrecalibrado (+20%)</h3>
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-sm text-gray-700">Consumo</span>
                                        <span class="text-sm font-medium text-yellow-600">{{ $comparison['over_inflated']['consumption'] }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-yellow-500 h-2 rounded-full" style="width: 75%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-sm text-gray-700">Desgaste</span>
                                        <span class="text-sm font-medium text-red-600">+{{ $comparison['over_inflated']['wear'] }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-red-500 h-2 rounded-full" style="width: 70%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-sm text-gray-700">Estabilidade</span>
                                        <span class="text-sm font-medium text-yellow-600">Rígida</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-yellow-500 h-2 rounded-full" style="width: 60%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-sm text-gray-700">Frenagem</span>
                                        <span class="text-sm font-medium text-red-600">{{ $comparison['over_inflated']['braking'] }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-red-500 h-2 rounded-full" style="width: 50%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-700">
                            <span class="font-medium">Dados baseados em testes:</span> Estudos da {{ $article->getData()['vehicle_info']['make'] ?? 'montadora' }} mostram que pneus 20% abaixo da pressão ideal podem reduzir a vida útil em até 16.000 km e aumentar o consumo em 8%. A sobrecalibração reduz a área de contato, prejudicando a frenagem em piso molhado em até 10%.
                        </p>
                    </div>
                </div>
            </section>
            @endif

            <!-- Banner de Anúncio 3 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-3]
            </div>

            <!-- Procedimento de Calibragem -->
            @if(!empty($article->getData()['calibration_procedure']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Como Calibrar Corretamente
                </h2>

                <div class="relative bg-white rounded-lg border border-gray-200 p-6">
                    <div class="absolute left-6 inset-y-0 w-0.5 bg-[#0E368A]/20"></div>

                    <div class="space-y-8">
                        @foreach($article->getData()['calibration_procedure'] as $step)
                        <div class="relative pl-8">
                            <div class="absolute left-0 -translate-x-1/2 w-6 h-6 rounded-full bg-[#0E368A] flex items-center justify-center text-white font-medium">
                                {{ $step['number'] }}
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ $step['title'] }}</h3>
                            <p class="text-gray-700 mb-3">{{ $step['description'] }}</p>
                            
                            @if(!empty($step['tips']))
                            <div class="bg-blue-50 border-l-4 border-blue-400 p-3 mt-3">
                                <ul class="space-y-1 text-sm text-blue-800">
                                    @foreach($step['tips'] as $tip)
                                    <li class="flex items-start">
                                        <span class="text-blue-500 mr-2">•</span>
                                        <span>{{ $tip }}</span>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-700">
                            <span class="font-medium">Frequência recomendada:</span> Verifique a pressão semanalmente e sempre antes de viagens longas. O {{ $article->getData()['vehicle_info']['model'] ?? 'veículo' }} perde naturalmente 1-2 PSI por mês, mas variações climáticas podem acelerar essa perda. Em caso de mudança brusca de temperatura (±10°C), faça uma verificação extra.
                        </p>
                    </div>
                </div>
            </section>
            @endif

            <!-- Sinais de Problemas -->
            @if(!empty($article->getData()['calibration_impacts']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Identificando Problemas na Calibragem
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if(!empty($article->getData()['calibration_impacts']['under_inflated']))
                    <div class="bg-white p-5 border-l-4 border-red-500 rounded-r shadow-sm">
                        <h3 class="font-medium text-lg text-gray-900 mb-3 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500 mr-2" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            Pressão Baixa (Subcalibrado)
                        </h3>
                        <ul class="space-y-2 text-gray-700 text-sm">
                            @php $underInflated = $article->getData()['calibration_impacts']['under_inflated'] @endphp
                            @if($underInflated['fuel_consumption'])
                            <li class="flex items-start">
                                <span class="text-red-500 mr-2">•</span>
                                <span>{{ $underInflated['fuel_consumption'] }}</span>
                            </li>
                            @endif
                            @if($underInflated['wear_pattern'])
                            <li class="flex items-start">
                                <span class="text-red-500 mr-2">•</span>
                                <span>{{ $underInflated['wear_pattern'] }}</span>
                            </li>
                            @endif
                            @if($underInflated['handling'])
                            <li class="flex items-start">
                                <span class="text-red-500 mr-2">•</span>
                                <span>{{ $underInflated['handling'] }}</span>
                            </li>
                            @endif
                            @if($underInflated['temperature'])
                            <li class="flex items-start">
                                <span class="text-red-500 mr-2">•</span>
                                <span>{{ $underInflated['temperature'] }}</span>
                            </li>
                            @endif
                        </ul>
                    </div>
                    @endif

                    @if(!empty($article->getData()['calibration_impacts']['over_inflated']))
                    <div class="bg-white p-5 border-l-4 border-orange-500 rounded-r shadow-sm">
                        <h3 class="font-medium text-lg text-gray-900 mb-3 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-500 mr-2" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            Pressão Alta (Sobrecalibrado)
                        </h3>
                        <ul class="space-y-2 text-gray-700 text-sm">
                            @php $overInflated = $article->getData()['calibration_impacts']['over_inflated'] @endphp
                            @if($overInflated['fuel_consumption'])
                            <li class="flex items-start">
                                <span class="text-orange-500 mr-2">•</span>
                                <span>{{ $overInflated['fuel_consumption'] }}</span>
                            </li>
                            @endif
                            @if($overInflated['wear_pattern'])
                            <li class="flex items-start">
                                <span class="text-orange-500 mr-2">•</span>
                                <span>{{ $overInflated['wear_pattern'] }}</span>
                            </li>
                            @endif
                            @if($overInflated['handling'])
                            <li class="flex items-start">
                                <span class="text-orange-500 mr-2">•</span>
                                <span>{{ $overInflated['handling'] }}</span>
                            </li>
                            @endif
                            @if($overInflated['comfort'])
                            <li class="flex items-start">
                                <span class="text-orange-500 mr-2">•</span>
                                <span>{{ $overInflated['comfort'] }}</span>
                            </li>
                            @endif
                        </ul>
                    </div>
                    @endif
                </div>

                <div class="mt-6 bg-blue-50 border border-blue-200 p-4 rounded-lg">
                    <div class="flex items-start">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 mr-2 flex-shrink-0 mt-0.5" 
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="text-sm text-blue-800 mb-2">
                                <span class="font-medium">Teste visual rápido:</span> Observe o "pé" do pneu quando o carro está parado. Um pneu bem calibrado deve ter uma pequena área plana em contato com o solo.
                            </p>
                            <p class="text-sm text-blue-800">
                                Se a área de contato for muito grande (pneu "achatado"), está subcalibrado. Se for muito pequena ou quase inexistente, está sobrecalibrado.
                            </p>
                        </div>
                    </div>
                </div>
            </section>
            @endif

            <!-- Perguntas Frequentes -->
            @if(!empty($article->getData()['faq']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Perguntas Frequentes sobre o {{ $article->getData()['vehicle_info']['full_name'] ?? 'Veículo' }}
                </h2>

                <div class="space-y-4">
                    @foreach($article->getData()['faq'] as $faq)
                    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                        <button class="flex justify-between items-center w-full px-5 py-4 text-left text-gray-900 font-medium focus:outline-none hover:bg-gray-50 faq-toggle">
                            <span>{{ $faq['pergunta'] }}</span>
                            <svg class="h-5 w-5 text-[#0E368A] faq-icon transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div class="px-5 py-4 border-t border-gray-200 bg-gray-50 faq-content hidden">
                            <p class="text-gray-700 mb-3">{{ $faq['resposta'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Dicas de Economia -->
            @if(!empty($article->getData()['maintenance_tips']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Dicas para Maximizar Economia e Durabilidade
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($article->getData()['maintenance_tips'] as $tipGroup)
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <div class="flex items-center mb-4">
                            <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    @if($tipGroup['icon_class'] == 'clock')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    @elseif($tipGroup['icon_class'] == 'tool')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    @endif
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">{{ $tipGroup['category'] }}</h3>
                        </div>

                        <ul class="space-y-3 text-gray-700">
                            @foreach($tipGroup['items'] as $item)
                            <li class="flex items-start">
                                <span class="text-green-500 mr-2 flex-shrink-0">•</span>
                                <span class="text-sm">{{ $item }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endforeach
                </div>

                <div class="mt-6 bg-gray-50 p-6 rounded-lg">
                    <div class="text-center">
                        <h4 class="text-lg font-medium text-gray-900 mb-2">Calculadora de Economia</h4>
                        <p class="text-sm text-gray-700 mb-4">
                            Com calibragem correta, você pode economizar até <span class="font-bold text-green-600">10% no combustível</span> 
                            e estender a vida dos pneus em <span class="font-bold text-green-600">40%</span>
                        </p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                            <div class="bg-white p-3 rounded border">
                                <div class="text-2xl font-bold text-[#0E368A]">R$ 1.200</div>
                                <div class="text-xs text-gray-600">Economia anual estimada em combustível</div>
                            </div>
                            <div class="bg-white p-3 rounded border">
                                <div class="text-2xl font-bold text-[#0E368A]">20.000 km</div>
                                <div class="text-xs text-gray-600">Quilometragem extra dos pneus</div>
                            </div>
                            <div class="bg-white p-3 rounded border">
                                <div class="text-2xl font-bold text-[#0E368A]">15%</div>
                                <div class="text-xs text-gray-600">Melhoria na frenagem</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            @endif

            <!-- Conclusão -->
            @if(!empty($article->getData()['final_considerations']))
            <section class="mb-12 bg-gray-50 rounded-lg p-6">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-4">Considerações Finais</h2>
                <div class="prose prose-lg max-w-none text-gray-800">
                    {!! nl2br(e($article->getData()['final_considerations'])) !!}
                </div>
            </section>
            @endif

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
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto text-[#0E368A]"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        @if(str_contains($topic['title'], 'Pneus'))
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        @elseif(str_contains($topic['title'], 'TPMS'))
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        @elseif(str_contains($topic['title'], 'Economia'))
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        @endif
                                    </svg>
                                </div>
                                <span class="font-medium text-[#151C25]">{{ str_replace(['Pneus', 'TPMS', 'Economia', 'Suspensão'], ['Pneus', 'TPMS', 'Economia', 'Suspensão'], explode(' ', $topic['title'])[0]) }}</span>
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

<!-- JavaScript para FAQ Toggle -->
<script>
document.addEventListener('DOMContentLoaded', function() {
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
});
</script>

@endsection