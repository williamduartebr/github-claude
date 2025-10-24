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
        <meta itemprop="category" content="Manutenção de Motocicletas" />

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
                        @if(!empty($article->getData()['tire_specifications']['front_tire']))
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Pneu Dianteiro</h3>

                            <div class="space-y-3">
                                @php $frontTire = $article->getData()['tire_specifications']['front_tire'] @endphp
                                @if($frontTire['size'])
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Medida:</span>
                                    <span class="font-medium">{{ $frontTire['size'] }}</span>
                                </div>
                                @endif
                                @if($frontTire['type'])
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Tipo:</span>
                                    <span class="font-medium">{{ $frontTire['type'] }}</span>
                                </div>
                                @endif
                                @if($frontTire['brand'])
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Marca Original:</span>
                                    <span class="font-medium">{{ $frontTire['brand'] }}</span>
                                </div>
                                @endif
                                @if($frontTire['load_index'])
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Índice de Carga:</span>
                                    <span class="font-medium">{{ $frontTire['load_index'] }}</span>
                                </div>
                                @endif
                                @if($frontTire['speed_rating'])
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Índice de Velocidade:</span>
                                    <span class="font-medium">{{ $frontTire['speed_rating'] }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        @if(!empty($article->getData()['tire_specifications']['rear_tire']))
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Pneu Traseiro</h3>

                            <div class="space-y-3">
                                @php $rearTire = $article->getData()['tire_specifications']['rear_tire'] @endphp
                                @if($rearTire['size'])
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Medida:</span>
                                    <span class="font-medium">{{ $rearTire['size'] }}</span>
                                </div>
                                @endif
                                @if($rearTire['type'])
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Tipo:</span>
                                    <span class="font-medium">{{ $rearTire['type'] }}</span>
                                </div>
                                @endif
                                @if($rearTire['brand'])
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Marca Original:</span>
                                    <span class="font-medium">{{ $rearTire['brand'] }}</span>
                                </div>
                                @endif
                                @if($rearTire['load_index'])
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Índice de Carga:</span>
                                    <span class="font-medium">{{ $rearTire['load_index'] }}</span>
                                </div>
                                @endif
                                @if($rearTire['speed_rating'])
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Índice de Velocidade:</span>
                                    <span class="font-medium">{{ $rearTire['speed_rating'] }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>

                    @if(!empty($article->getData()['vehicle_info']['full_name']))
                    <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-700">
                            <span class="font-medium">Nota:</span> A {{ $article->getData()['vehicle_info']['full_name'] }} é equipada com pneus de alta performance que oferecem excelente aderência e estabilidade. As medidas dos pneus são específicas para este modelo, contribuindo para seu comportamento característico em diferentes condições de pilotagem.
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
                    Pressões Recomendadas
                </h2>

                <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                    <table class="min-w-full border-collapse">
                        <thead>
                            <tr class="bg-[#0E368A] text-white">
                                <th class="py-3 px-4 text-left font-medium text-sm">Condição de Uso</th>
                                <th class="py-3 px-4 text-center font-medium text-sm">Dianteiro (PSI)</th>
                                <th class="py-3 px-4 text-center font-medium text-sm">Dianteiro (bar)</th>
                                <th class="py-3 px-4 text-center font-medium text-sm">Traseiro (PSI)</th>
                                <th class="py-3 px-4 text-center font-medium text-sm">Traseiro (bar)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($article->getData()['pressure_table'] as $condition)
                            <tr class="border-b border-gray-200 {{ $condition['css_class'] ?? 'bg-white' }}">
                                <td class="py-3 px-4 text-sm font-medium">{{ $condition['condition'] }}</td>
                                <td class="py-3 px-4 text-sm text-center">
                                    {{ str_replace(' PSI', '', str_replace(' (2.5 bar)', '', $condition['front_pressure'])) }}
                                </td>
                                <td class="py-3 px-4 text-sm text-center">
                                    @if(preg_match('/\(([\d.]+) bar\)/', $condition['front_pressure'], $matches))
                                        {{ $matches[1] }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-sm text-center">
                                    {{ str_replace(' PSI', '', str_replace(' (2.9 bar)', '', $condition['rear_pressure'])) }}
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
                        </tbody>
                    </table>

                    <div class="p-4 bg-gray-50 text-sm text-gray-700">
                        <span class="font-medium">Fonte:</span> Valores baseados no manual do proprietário da {{ $article->getData()['vehicle_info']['full_name'] ?? 'motocicleta' }} e recomendações técnicas para condições brasileiras.
                    </div>
                </div>
            </section>
            @endif

            <!-- Recomendações Específicas -->
            @if(!empty($article->getData()['usage_recommendations']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Recomendações por Tipo de Uso
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
                                                d="M3 15a4 4 0 004 4h9a5 5 0 10-4.5-6.875" />
                                            @break
                                        @case('info')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                            @break
                                        @case('zap')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                                            @break
                                        @case('luggage')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                                            @break
                                        @default
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 15a4 4 0 004 4h9a5 5 0 10-4.5-6.875" />
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

            <!-- Gráfico de Impacto da Calibragem -->
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
                            <h3 class="font-medium text-lg text-gray-900 mb-3 text-center">Subcalibrado</h3>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-center text-sm font-medium text-gray-700 mb-1">Desgaste</p>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                        <div class="bg-red-500 h-1.5 rounded-full" style="width: {{ $comparison['under_inflated']['wear'] }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-center text-sm font-medium text-gray-700 mb-1">Consumo</p>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                        <div class="bg-red-500 h-1.5 rounded-full" style="width: {{ $comparison['under_inflated']['consumption'] }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-center text-sm font-medium text-gray-700 mb-1">Estabilidade (Asfalto)</p>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                        <div class="bg-red-500 h-1.5 rounded-full" style="width: {{ $comparison['under_inflated']['stability_asphalt'] }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-center text-sm font-medium text-gray-700 mb-1">Conforto</p>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                        <div class="bg-green-500 h-1.5 rounded-full" style="width: {{ $comparison['under_inflated']['comfort'] }}%"></div>
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
                                    <p class="text-center text-sm font-medium text-gray-700 mb-1">Desgaste</p>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                        <div class="bg-green-500 h-1.5 rounded-full" style="width: {{ $comparison['ideal']['wear'] }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-center text-sm font-medium text-gray-700 mb-1">Consumo</p>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                        <div class="bg-green-500 h-1.5 rounded-full" style="width: {{ $comparison['ideal']['consumption'] }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-center text-sm font-medium text-gray-700 mb-1">Estabilidade (Asfalto)</p>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                        <div class="bg-green-500 h-1.5 rounded-full" style="width: {{ $comparison['ideal']['stability_asphalt'] }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-center text-sm font-medium text-gray-700 mb-1">Conforto</p>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                        <div class="bg-green-500 h-1.5 rounded-full" style="width: {{ $comparison['ideal']['comfort'] }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Sobrecalibrado -->
                        @if(!empty($comparison['over_inflated']))
                        <div>
                            <h3 class="font-medium text-lg text-gray-900 mb-3 text-center">Sobrecalibrado</h3>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-center text-sm font-medium text-gray-700 mb-1">Desgaste</p>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                        <div class="bg-red-500 h-1.5 rounded-full" style="width: {{ $comparison['over_inflated']['wear'] }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-center text-sm font-medium text-gray-700 mb-1">Consumo</p>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                        <div class="bg-green-500 h-1.5 rounded-full" style="width: {{ $comparison['over_inflated']['consumption'] }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-center text-sm font-medium text-gray-700 mb-1">Estabilidade (Asfalto)</p>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                        <div class="bg-yellow-500 h-1.5 rounded-full" style="width: {{ $comparison['over_inflated']['stability_asphalt'] }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-center text-sm font-medium text-gray-700 mb-1">Conforto</p>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                        <div class="bg-red-500 h-1.5 rounded-full" style="width: {{ $comparison['over_inflated']['comfort'] }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-700">
                            <span class="font-medium">Impacto em números:</span> Pneus com 30% de pressão abaixo do recomendado podem reduzir sua vida útil em até 45% e aumentar o consumo de combustível em cerca de 10%. Já a sobrecalibração em 30% pode reduzir o conforto significativamente e aumentar a vibração transmitida ao piloto.
                        </p>
                    </div>
                </div>
            </section>
            @endif

            <!-- Adaptações para Pneus Alternativos -->
            @if(!empty($article->getData()['alternative_tires']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Adaptações para Pneus Alternativos
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($article->getData()['alternative_tires'] as $alternative)
                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <div class="flex flex-col items-center mb-4">
                            <div class="h-16 w-16 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/10 flex items-center justify-center mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#0E368A]" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    @switch($alternative['icon_class'] ?? 'wheel')
                                        @case('zap')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                                            @break
                                        @case('navigation')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                            @break
                                        @case('building')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            @break
                                        @default
                                            <circle cx="12" cy="12" r="3"></circle>
                                            <path d="M12 1v6m0 6v6m11-7h-6m-6 0H1"></path>
                                    @endswitch
                                </svg>
                            </div>
                            <h3 class="font-medium text-lg text-gray-900 text-center">{{ $alternative['category'] }}</h3>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4 mb-4">
                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Dianteiro:</span>
                                    <span class="text-sm font-medium">{{ $alternative['front_pressure'] }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Traseiro:</span>
                                    <span class="text-sm font-medium">{{ $alternative['rear_pressure'] }}</span>
                                </div>
                            </div>
                        </div>

                        <p class="text-sm text-gray-700 mb-4">{{ $alternative['description'] }}</p>

                        <div class="flex flex-wrap gap-2">
                            @foreach($alternative['tags'] as $tag)
                            <span class="inline-block bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                {{ $tag }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-700">
                        <span class="font-medium">Importante:</span> Ao trocar por pneus diferentes dos originais, considere não apenas a pressão, mas também o composto da borracha e o desenho da banda de rodagem. Um pneu mais macio geralmente requer pressão ligeiramente mais elevada, enquanto um pneu com borracha mais dura pode funcionar com pressões um pouco mais baixas.
                    </p>
                </div>
            </section>
            @endif

            <!-- Banner de Anúncio 3 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-3]
            </div>

            <!-- Método de Calibragem Correta -->
            @if(!empty($article->getData()['calibration_procedure']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Procedimento de Calibragem Correto
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
                            <span class="font-medium">Frequência recomendada:</span> Verifique a pressão dos pneus da sua {{ $article->getData()['vehicle_info']['model'] ?? 'motocicleta' }} semanalmente e sempre antes de viagens longas. A pequena perda de pressão natural (cerca de 1-2 PSI por mês) pode ser significativa para o comportamento da moto.
                        </p>
                    </div>
                </div>
            </section>
            @endif

            <!-- Sinais de Alerta -->
            @if(!empty($article->getData()['problem_signs']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Sinais de Problemas na Calibragem
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($article->getData()['problem_signs'] as $problem)
                    <div class="bg-white p-5 border-l-4 {{ $problem['severity_class'] }} rounded-r shadow-sm">
                        <h3 class="font-medium text-lg text-gray-900 mb-2">{{ $problem['title'] }}</h3>
                        <ul class="space-y-2 text-gray-700">
                            @foreach($problem['signs'] as $sign)
                            <li class="flex items-start">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5 text-red-500 mr-2 flex-shrink-0 mt-0.5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span>{{ $sign }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endforeach
                </div>

                <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-700">
                        <span class="font-medium">Verificação visual simples:</span> Com a moto no cavalete central, observe a "pegada" do pneu. Um pneu corretamente calibrado deve ter uma pequena área plana em contato com o solo. Se esta área for muito grande, o pneu está subcalibrado; se for muito pequena, está sobrecalibrado.
                    </p>
                </div>
            </section>
            @endif

            <!-- Perguntas Frequentes -->
            @if(!empty($article->getData()['faq']))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Perguntas Frequentes
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
                            <p class="text-gray-700">{{ $faq['resposta'] }}</p>
                        </div>
                    </div>
                    @endforeach
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
                                        @elseif(str_contains($topic['title'], 'Suspensão'))
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                        @elseif(str_contains($topic['title'], 'Vida Útil'))
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        @endif
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