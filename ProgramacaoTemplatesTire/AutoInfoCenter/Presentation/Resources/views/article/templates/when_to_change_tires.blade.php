@extends('auto-info-center::layouts.app')

@push('head')
<link rel="amphtml" href="{{ route('info.article.show.amp', $article->slug) }}">
<link rel="canonical" href="{{ route('info.article.show', $article->slug) }}">

@if(!empty($article->structured_data))
<script type="application/ld+json">
    {!! json_encode($article->structured_data) !!}
</script>
@endif
@endpush

@section('content')

@include('auto-info-center::article.partials.breadcrumb')

<main id="main-content" class="container mx-auto px-4">
    <div itemscope itemtype="https://schema.org/TechArticle">
        @if(!empty($article->vehicle_info))
        <meta itemprop="vehicleEngine"
            content="{{ $article->vehicle_info['make'] ?? '' }} {{ $article->vehicle_info['model'] ?? '' }} {{ $article->vehicle_info['year'] ?? '' }}" />
        @endif
        <meta itemprop="category" content="{{ $article->category['name'] ?? 'Manutenção Automotiva' }}" />

        <article class="max-w-4xl mx-auto pt-6 pb-12">

            @php
            $imageDefault = \Str::slug( sprintf("%s-%s", $article->category['slug'] ?? 'quando-trocar-pneus',
            $article->vehicle_info['vehicle_type'] ?? 'carro'));
            @endphp

            <!-- Hero Image - Desktop -->
            <div class="relative rounded-lg overflow-hidden mb-8 mt-2 hidden md:block">
                <img src="https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/when_to_change_tires.png"
                    alt="{{ $article->title }}" class="w-full h-64 object-cover"
                    onerror="this.src='https:\/\/mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/when_to_change_tires.png'">
                <div class="absolute inset-0 bg-gradient-to-t from-black/100 to-transparent"></div>
                <div class="absolute bottom-0 left-0 p-6 text-white">
                    <h1 class="text-3xl md:text-4xl font-semibold leading-tight">{{ $article->title }}</h1>
                    @if(!empty($article->formated_updated_at))
                    <p class="text-sm mt-2 opacity-90">Atualizado em: {{ $article->formated_updated_at }}</p>
                    @endif
                </div>
            </div>

            <!-- Title - Mobile -->
            <div class="mb-8 mt-2 block md:hidden">
                <h1 class="text-3xl font-semibold leading-tight text-gray-900">{{ $article->title }}</h1>
                @if(!empty($article->formated_updated_at))
                <p class="text-sm mt-2 text-gray-600">Atualizado em: {{ $article->formated_updated_at }}</p>
                @endif
            </div>

            <!-- Introdução -->
            @if(!empty($article->introduction))
            <div class="mb-10">
                <p class="text-lg text-gray-800 leading-relaxed">
                    {{ $article->introduction }}
                </p>
            </div>
            @endif

            <!-- Banner de Anúncio 1 -->
            <div class="my-8">
                [ADSENSE-ARTICLE-1]
            </div>

            <!-- Sintomas de Desgaste dos Pneus -->
            @if(!empty($article->wear_symptoms) && is_array($article->wear_symptoms) && count($article->wear_symptoms) > 0)
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Sintomas de Pneus que Precisam de Substituição
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($article->wear_symptoms as $symptom)
                    @if(!empty($symptom['title']))
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center mb-4">
                                <div class="flex-shrink-0 h-12 w-12 rounded-full bg-red-100 flex items-center justify-center mr-4">
                                    @if($symptom['severity'] === 'alta')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    @endif
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $symptom['title'] }}</h3>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $symptom['severity'] === 'alta' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        Severidade {{ ucfirst($symptom['severity']) }}
                                    </span>
                                </div>
                            </div>
                            <p class="text-gray-600 mb-3">{{ $symptom['description'] ?? '' }}</p>
                            @if(!empty($symptom['action']))
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-sm text-gray-700"><strong>Ação recomendada:</strong> {{ $symptom['action'] }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Fatores que Afetam a Durabilidade -->
            @if(!empty($article->durability_factors) && is_array($article->durability_factors) && count($article->durability_factors) > 0)
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Fatores que Afetam a Durabilidade dos Pneus
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($article->durability_factors as $factor)
                    @if(!empty($factor['title']))
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">{{ $factor['title'] }}</h3>
                        
                        @if(!empty($factor['impact']))
                        <div class="flex items-center mb-3">
                            <span class="text-sm font-medium text-gray-700 w-32">Impacto na vida útil:</span>
                            <div class="flex-1 bg-gray-200 rounded-full h-2 mx-3">
                                @php
                                $impactValue = (int) filter_var($factor['impact'], FILTER_SANITIZE_NUMBER_INT);
                                $width = abs($impactValue);
                                $color = $factor['is_positive'] ? 'bg-green-500' : 'bg-red-500';
                                @endphp
                                <div class="{{ $color }} h-2 rounded-full" style="width: {{ min($width, 100) }}%"></div>
                            </div>
                            <span class="text-sm font-medium {{ $factor['is_positive'] ? 'text-green-600' : 'text-red-600' }}">
                                {{ $factor['impact'] }}
                            </span>
                        </div>
                        @endif

                        <p class="text-gray-600 mb-3">{{ $factor['description'] ?? '' }}</p>
                        
                        @if(!empty($factor['recommendation']))
                        <div class="bg-blue-50 p-3 rounded-lg">
                            <p class="text-sm text-blue-800"><strong>Recomendação:</strong> {{ $factor['recommendation'] }}</p>
                        </div>
                        @endif

                        @if(!empty($factor['pressure_recommendation']))
                        <div class="mt-2 text-sm text-gray-600">
                            <strong>Pressão recomendada:</strong> {{ $factor['pressure_recommendation'] }}
                        </div>
                        @endif
                    </div>
                    @endif
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Banner de Anúncio 2 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-2]
            </div>

            <!-- Cronograma de Verificação -->
            @if(!empty($article->verification_schedule) && is_array($article->verification_schedule) && count($article->verification_schedule) > 0)
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Cronograma de Verificação e Manutenção
                </h2>

                <div class="space-y-4">
                    @foreach($article->verification_schedule as $index => $schedule)
                    {{-- @if(!empty($schedule['title'])) --}}
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 rounded-full bg-[#0E368A] flex items-center justify-center text-white text-sm font-medium">
                                {{ $index + 1 }}
                            </div>
                        </div>
                        <div class="flex-1 bg-white rounded-lg border border-gray-200 shadow-sm p-5">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $schedule['title'] }}</h3>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $schedule['importance'] === 'alta' || $schedule['importance'] === 'essencial' || $schedule['importance'] === 'obrigatória' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ ucfirst($schedule['importance']) }}
                                </span>
                            </div>
                            <p class="text-gray-600">{{ $schedule['description'] ?? '' }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Tipos de Pneus e Quilometragem -->
            @if(!empty($article->tire_types) && is_array($article->tire_types) && count($article->tire_types) > 0)
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Tipos de Pneus e Quilometragem Esperada
                </h2>

                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                        <thead>
                            <tr class="bg-[#0E368A] text-white">
                                <th class="py-3 px-4 text-left text-sm font-medium">Tipo de Pneu</th>
                                <th class="py-3 px-4 text-left text-sm font-medium">Quilometragem Esperada</th>
                                <th class="py-3 px-4 text-left text-sm font-medium">Aplicação</th>
                                <th class="py-3 px-4 text-left text-sm font-medium">Observações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($article->tire_types as $index => $tire)
                            @if(!empty($tire['type']))
                            <tr class="border-b border-gray-200 {{ $index % 2 == 0 ? 'bg-gray-50' : 'bg-white' }}">
                                <td class="py-3 px-4 text-sm font-medium text-gray-900">{{ $tire['type'] }}</td>
                                <td class="py-3 px-4 text-sm text-gray-700">{{ $tire['expected_mileage'] ?? 'N/A' }}</td>
                                <td class="py-3 px-4 text-sm text-gray-700">{{ $tire['application'] ?? 'N/A' }}</td>
                                <td class="py-3 px-4 text-sm text-gray-700">{{ $tire['observations'] ?? 'N/A' }}</td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
            @endif

            <!-- Sinais Críticos para Substituição -->
            @if(!empty($article->critical_signs) && is_array($article->critical_signs) && count($article->critical_signs) > 0)
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Sinais Críticos para Substituição Imediata
                </h2>

                <div class="space-y-6">
                    @foreach($article->critical_signs as $sign)
                    @if(!empty($sign['title']))
                    <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-red-100 flex items-center justify-center mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-red-900">{{ $sign['title'] }}</h3>
                        </div>

                        @if(!empty($sign['legal_limit']) || !empty($sign['recommended_limit']))
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            @if(!empty($sign['legal_limit']))
                            <div class="bg-white p-3 rounded border">
                                <span class="text-sm font-medium text-gray-700">Limite Legal:</span>
                                <span class="text-lg font-bold text-red-600 ml-2">{{ $sign['legal_limit'] }}</span>
                            </div>
                            @endif
                            @if(!empty($sign['recommended_limit']))
                            <div class="bg-white p-3 rounded border">
                                <span class="text-sm font-medium text-gray-700">Limite Recomendado:</span>
                                <span class="text-lg font-bold text-orange-600 ml-2">{{ $sign['recommended_limit'] }}</span>
                            </div>
                            @endif
                        </div>
                        @endif

                        @if(!empty($sign['test']))
                        <div class="mb-4">
                            <p class="text-sm text-gray-700"><strong>Como testar:</strong> {{ $sign['test'] }}</p>
                        </div>
                        @endif

                        @if(!empty($sign['types']) && is_array($sign['types']) && count($sign['types']) > 0)
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-700 mb-2">Tipos de danos estruturais:</p>
                            <ul class="space-y-1">
                                @foreach($sign['types'] as $type)
                                @if(!empty($type))
                                <li class="flex items-center text-sm text-gray-600">
                                    <span class="text-red-500 mr-2">•</span>
                                    {{ $type }}
                                </li>
                                @endif
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        @if(!empty($sign['patterns']) && is_array($sign['patterns']) && count($sign['patterns']) > 0)
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-700 mb-2">Padrões de desgaste irregular:</p>
                            <ul class="space-y-1">
                                @foreach($sign['patterns'] as $pattern)
                                @if(!empty($pattern))
                                <li class="flex items-center text-sm text-gray-600">
                                    <span class="text-orange-500 mr-2">•</span>
                                    {{ $pattern }}
                                </li>
                                @endif
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        @if(!empty($sign['action']))
                        <div class="bg-white border border-red-200 p-3 rounded">
                            <p class="text-sm text-red-800"><strong>Ação obrigatória:</strong> {{ $sign['action'] }}</p>
                        </div>
                        @endif
                    </div>
                    @endif
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Banner de Anúncio 3 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-3]
            </div>

            <!-- Manutenção Preventiva -->
            @if(!empty($article->preventive_maintenance) && is_array($article->preventive_maintenance))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    🔧 Manutenção Preventiva dos Pneus
                </h2>

                <!-- Grid de Cards de Manutenção -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    
                    <!-- Verificação de Pressão -->
                    @if(!empty($article->preventive_maintenance['verificacao_pressao']))
                    @php $pressao = $article->preventive_maintenance['verificacao_pressao']; @endphp
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 maintenance-card">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Verificação de Pressão</h3>
                        </div>

                        @if(!empty($pressao['frequencia']))
                        <p class="text-sm text-gray-600 mb-2"><strong>Frequência:</strong> {{ $pressao['frequencia'] }}</p>
                        @endif

                        @if(!empty($pressao['momento']))
                        <p class="text-sm text-gray-600 mb-2"><strong>Quando:</strong> {{ $pressao['momento'] }}</p>
                        @endif

                        @if(!empty($pressao['tolerancia']))
                        <p class="text-sm text-gray-600 mb-3"><strong>Tolerância:</strong> {{ $pressao['tolerancia'] }}</p>
                        @endif

                        <div class="bg-blue-50 p-3 rounded-lg">
                            <p class="text-sm text-blue-800"><strong>Importância:</strong> A pressão correta garante segurança, economia e durabilidade dos pneus.</p>
                        </div>
                    </div>
                    @endif

                    <!-- Rodízio -->
                    @if(!empty($article->preventive_maintenance['rodizio']))
                    @php $rodizio = $article->preventive_maintenance['rodizio']; @endphp
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 maintenance-card">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-green-100 flex items-center justify-center mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Rodízio de Pneus</h3>
                        </div>

                        @if(!empty($rodizio['frequencia']))
                        <p class="text-sm text-gray-600 mb-2"><strong>Frequência:</strong> {{ $rodizio['frequencia'] }}</p>
                        @endif

                        @if(!empty($rodizio['padrao']))
                        <p class="text-sm text-gray-600 mb-2"><strong>Padrão:</strong> {{ $rodizio['padrao'] }}</p>
                        @endif

                        @if(!empty($rodizio['beneficio']))
                        <div class="bg-green-50 p-3 rounded-lg">
                            <p class="text-sm text-green-800"><strong>Benefício:</strong> {{ $rodizio['beneficio'] }}</p>
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- Alinhamento e Balanceamento -->
                    @if(!empty($article->preventive_maintenance['alinhamento_balanceamento']))
                    @php $alinhamento = $article->preventive_maintenance['alinhamento_balanceamento']; @endphp
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 maintenance-card">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-orange-100 flex items-center justify-center mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Alinhamento e Balanceamento</h3>
                        </div>

                        @if(!empty($alinhamento['frequencia']))
                        <p class="text-sm text-gray-600 mb-2"><strong>Frequência:</strong> {{ $alinhamento['frequencia'] }}</p>
                        @endif

                        @if(!empty($alinhamento['sinais']))
                        <p class="text-sm text-gray-600 mb-2"><strong>Sinais:</strong> {{ $alinhamento['sinais'] }}</p>
                        @endif

                        @if(!empty($alinhamento['importancia']))
                        <div class="bg-yellow-50 p-3 rounded-lg">
                            <p class="text-sm text-yellow-800"><strong>Importância:</strong> {{ $alinhamento['importancia'] }}</p>
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- Tasks antigas (compatibilidade) -->
                    @if(!empty($article->preventive_maintenance['tasks']) && is_array($article->preventive_maintenance['tasks']))
                    @foreach($article->preventive_maintenance['tasks'] as $task)
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 maintenance-card">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">{{ $task['frequency'] }}</h3>
                        </div>

                        @if(!empty($task['moment']))
                        <p class="text-sm text-gray-600 mb-2"><strong>Quando:</strong> {{ $task['moment'] }}</p>
                        @endif

                        @if(!empty($task['pattern']))
                        <p class="text-sm text-gray-600 mb-2"><strong>Padrão:</strong> {{ $task['pattern'] }}</p>
                        @endif

                        @if(!empty($task['tolerance']))
                        <p class="text-sm text-gray-600 mb-2"><strong>Tolerância:</strong> {{ $task['tolerance'] }}</p>
                        @endif

                        @if(!empty($task['signs']))
                        <p class="text-sm text-gray-600 mb-2"><strong>Sinais:</strong> {{ $task['signs'] }}</p>
                        @endif

                        @if(!empty($task['benefit']))
                        <div class="bg-green-50 p-3 rounded-lg mt-3">
                            <p class="text-sm text-green-800"><strong>Benefício:</strong> {{ $task['benefit'] }}</p>
                        </div>
                        @endif

                        @if(!empty($task['importance']))
                        <div class="bg-yellow-50 p-3 rounded-lg mt-3">
                            <p class="text-sm text-yellow-800"><strong>Importância:</strong> {{ $task['importance'] }}</p>
                        </div>
                        @endif
                    </div>
                    @endforeach
                    @endif
                </div>

                <!-- Cuidados Gerais -->
                @if(!empty($article->preventive_maintenance['cuidados_gerais']) && is_array($article->preventive_maintenance['cuidados_gerais']))
                <div class="bg-blue-100/50 border border-blue-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Cuidados Gerais
                    </h3>
                    <ul class="space-y-2">
                        @foreach($article->preventive_maintenance['cuidados_gerais'] as $care)
                        @if(!empty($care))
                        <li class="flex items-start">
                            <span class="text-[#0E368A] mr-2 mt-1">•</span>
                            <span class="text-gray-700">{{ $care }}</span>
                        </li>
                        @endif
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Manter compatibilidade com general_care antiga -->
                @if(!empty($article->preventive_maintenance['general_care']) && is_array($article->preventive_maintenance['general_care']))
                <div class="bg-blue-100/50 border border-blue-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Cuidados Gerais</h3>
                    <ul class="space-y-2">
                        @foreach($article->preventive_maintenance['general_care'] as $care)
                        @if(!empty($care))
                        <li class="flex items-start">
                            <span class="text-[#0E368A] mr-2 mt-1">•</span>
                            <span class="text-gray-700">{{ $care }}</span>
                        </li>
                        @endif
                        @endforeach
                    </ul>
                </div>
                @endif
            </section>
            @endif

            

            <!-- Procedimento de Verificação -->
            @if(!empty($article->verification_procedure) && is_array($article->verification_procedure) && count($article->verification_procedure) > 0)
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
                                <span class="bg-white text-[#0E368A] rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold mr-3">
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
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
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

            <!-- Dados do Veículo -->
            @if(!empty($article->vehicle_data) && is_array($article->vehicle_data))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Especificações do {{ $article->vehicle_full_name ?? 'Veículo' }}
                </h2>

                <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-gray-200">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações Básicas</h3>
                            <div class="space-y-3">
                                @if(!empty($article->vehicle_data['tire_size']))
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Medida dos Pneus:</span>
                                    <span class="font-medium text-gray-900">{{ $article->vehicle_data['tire_size'] }}</span>
                                </div>
                                @endif
                                @if(!empty($article->vehicle_data['vehicle_category']))
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Categoria:</span>
                                    <span class="font-medium text-gray-900">{{ translate_vehicle_category($article->vehicle_data['vehicle_category']) }}</span>
                                </div>
                                @endif

                                @if(!empty($article->vehicle_data['vehicle_type']))
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Tipo:</span>
                                    <span class="font-medium text-gray-900">{{ translate_vehicle_type($article->vehicle_data['vehicle_type']) }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Pressões Recomendadas</h3>
                            <div class="space-y-3">
                                @if(!empty($article->vehicle_data['pressure_display']))
                                <div class="bg-[#0E368A]/5 p-3 rounded-lg text-center">
                                    <span class="text-sm text-gray-600">Veículo Vazio</span>
                                    <div class="text-2xl font-bold text-[#0E368A]">{{ $article->vehicle_data['pressure_display'] }}</div>
                                </div>
                                @endif
                                @if(!empty($article->vehicle_data['pressure_loaded_display']))
                                <div class="bg-gray-100 p-3 rounded-lg text-center">
                                    <span class="text-sm text-gray-600">Com Carga</span>
                                    <div class="text-xl font-semibold text-gray-700">{{ $article->vehicle_data['pressure_loaded_display'] }}</div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            @endif

            <!-- Perguntas Frequentes -->
            @if(!empty($article->faq) && is_array($article->faq) && count($article->faq) > 0)
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Perguntas Frequentes
                </h2>

                <div class="space-y-4">
                    @foreach($article->faq as $question)
                    @if(!empty($question['pergunta']) && !empty($question['resposta']))
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                        <div class="p-5">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ $question['pergunta'] }}</h3>
                            <p class="text-gray-700">{{ $question['resposta'] }}</p>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Considerações Finais -->
            @if(!empty($article->final_considerations))
            <section class="mb-12 bg-gray-50 rounded-lg p-6">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-4">Considerações Finais</h2>
                <p class="text-gray-800 mb-4">{{ $article->final_considerations }}</p>
            </section>
            @endif

            <!-- Artigos Relacionados -->
            {{-- @include('auto-info-center::article.partials.related_content') --}}

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
    /* Estilos específicos para template quando trocar pneus */
    .tire-wear-icon {
        @apply text-[#0E368A] mr-2;
    }

    /* Cards de sintomas com animações */
    .symptom-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    /* Barras de progresso para fatores de durabilidade */
    .durability-bar {
        height: 8px;
        border-radius: 4px;
        transition: all 0.3s ease;
    }

    .durability-bar.positive {
        background: linear-gradient(90deg, #10b981, #34d399);
    }

    .durability-bar.negative {
        background: linear-gradient(90deg, #ef4444, #f87171);
    }

    /* Timeline para cronograma */
    .verification-timeline {
        position: relative;
    }

    .verification-timeline::before {
        content: '';
        position: absolute;
        left: 16px;
        top: 40px;
        bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom, #0E368A, transparent);
    }

    .verification-timeline .timeline-item:last-child::after {
        display: none;
    }

    /* Tabelas responsivas */
    .tire-specs-table {
        font-size: 14px;
    }

    .tire-specs-table th,
    .tire-specs-table td {
        padding: 12px 8px;
        vertical-align: top;
    }

    /* Cards de sinais críticos */
    .critical-sign-card {
        border-left: 4px solid #dc2626;
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    }

    .critical-sign-card:hover {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        transition: all 0.2s ease;
    }

    /* Manutenção preventiva com ícones */
    .maintenance-card:hover {
        background-color: #f8fafc;
        border-color: #0E368A;
        transition: all 0.2s ease;
    }

    /* Procedimento com steps numerados */
    .procedure-step {
        counter-increment: step-counter;
    }

    .procedure-step::before {
        content: counter(step-counter);
        background: #0E368A;
        color: white;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
        margin-right: 8px;
    }

    /* Especificações do veículo */
    .vehicle-specs-card {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border: 1px solid #cbd5e1;
    }

    .pressure-display {
        background: linear-gradient(135deg, rgba(14, 54, 138, 0.1) 0%, rgba(14, 54, 138, 0.05) 100%);
        border: 2px solid rgba(14, 54, 138, 0.2);
    }

    /* FAQ com efeitos hover */
    .faq-card {
        transition: all 0.2s ease;
    }

    .faq-card:hover {
        box-shadow: 0 4px 12px rgba(14, 54, 138, 0.1);
        border-color: #0E368A;
    }

    /* Badges de severidade e importância */
    .severity-high {
        @apply bg-red-100 text-red-800;
    }

    .severity-medium {
        @apply bg-yellow-100 text-yellow-800;
    }

    .severity-low {
        @apply bg-green-100 text-green-800;
    }

    .importance-high {
        @apply bg-red-100 text-red-800;
    }

    .importance-medium {
        @apply bg-blue-100 text-blue-800;
    }

    .importance-low {
        @apply bg-gray-100 text-gray-800;
    }

    /* Estilos para impressão */
    @media print {
        .no-print {
            display: none !important;
        }

        main {
            padding: 0 !important;
        }

        /* Força quebras de página apropriadas */
        section {
            page-break-inside: avoid;
        }

        .symptom-card,
        .critical-sign-card,
        .maintenance-card {
            page-break-inside: avoid;
            margin-bottom: 1rem;
        }

        /* Cores em impressão */
        .bg-\[#0E368A\] {
            background-color: #0E368A !important;
            -webkit-print-color-adjust: exact;
        }

        .text-\[#0E368A\] {
            color: #0E368A !important;
            -webkit-print-color-adjust: exact;
        }

        /* Remover sombras e efeitos em impressão */
        .shadow-sm,
        .shadow-md {
            box-shadow: none !important;
        }

        /* Ajustar tamanhos para impressão */
        h1 { font-size: 24px !important; }
        h2 { font-size: 20px !important; }
        h3 { font-size: 18px !important; }
    }

    /* Responsividade específica */
    @media (max-width: 768px) {
        .tire-specs-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .tire-specs-table {
            font-size: 12px;
        }

        .tire-specs-table th,
        .tire-specs-table td {
            padding: 8px 4px;
        }

        /* Stack cards em mobile */
        .grid-cols-1.md\:grid-cols-2 {
            grid-template-columns: 1fr;
        }

        /* Ajustar procedure steps em mobile */
        .procedure-step::before {
            width: 20px;
            height: 20px;
            font-size: 10px;
        }

        /* Pressure display responsivo */
        .pressure-display {
            font-size: 18px;
        }
    }

    /* Animações para entrada dos elementos */
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .symptom-card,
    .critical-sign-card,
    .maintenance-card {
        animation: slideInUp 0.6s ease-out;
    }

    /* Delay progressivo para cards */
    .symptom-card:nth-child(1) { animation-delay: 0.1s; }
    .symptom-card:nth-child(2) { animation-delay: 0.2s; }
    .symptom-card:nth-child(3) { animation-delay: 0.3s; }
    .symptom-card:nth-child(4) { animation-delay: 0.4s; }

    /* Scroll suave para tabelas */
    .overflow-x-auto {
        scrollbar-width: thin;
        scrollbar-color: #0E368A #f1f5f9;
    }

    .overflow-x-auto::-webkit-scrollbar {
        height: 8px;
    }

    .overflow-x-auto::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: #0E368A;
        border-radius: 4px;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb:hover {
        background: #0A2868;
    }

    /* Estados de loading para imagens */
    img[loading="lazy"] {
        transition: opacity 0.3s ease;
    }

    img[loading="lazy"]:not([src]) {
        opacity: 0;
    }

    /* Highlights interativos */
    .interactive-highlight:hover {
        background-color: #f0f9ff;
        border-color: #0EA5E9;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    /* Tooltips para especificações técnicas */
    .spec-tooltip {
        position: relative;
    }

    .spec-tooltip:hover .tooltip-content {
        visibility: visible;
        opacity: 1;
    }

    .tooltip-content {
        visibility: hidden;
        opacity: 0;
        position: absolute;
        z-index: 50;
        bottom: 125%;
        left: 50%;
        margin-left: -80px;
        background-color: #1f2937;
        color: white;
        text-align: center;
        border-radius: 6px;
        padding: 8px;
        font-size: 12px;
        transition: opacity 0.3s;
        width: 160px;
    }

    .tooltip-content::after {
        content: "";
        position: absolute;
        top: 100%;
        left: 50%;
        margin-left: -5px;
        border-width: 5px;
        border-style: solid;
        border-color: #1f2937 transparent transparent transparent;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar formatação de tabelas responsivas
        const tables = document.querySelectorAll('table');
        tables.forEach(table => {
            table.classList.add('w-full');
            table.querySelectorAll('th, td').forEach(cell => {
                cell.classList.add('px-4', 'py-2');
            });
        });

        // Adicionar indicador de scroll para tabelas em mobile
        const tableContainers = document.querySelectorAll('.overflow-x-auto');
        tableContainers.forEach(container => {
            const table = container.querySelector('table');
            if (table && table.scrollWidth > container.clientWidth) {
                container.classList.add('relative');
                const indicator = document.createElement('div');
                indicator.className = 'absolute right-0 top-0 bottom-0 w-8 bg-gradient-to-l from-white to-transparent pointer-events-none';
                container.appendChild(indicator);

                container.addEventListener('scroll', function() {
                    if (container.scrollLeft + container.clientWidth >= container.scrollWidth - 5) {
                        indicator.style.display = 'none';
                    } else {
                        indicator.style.display = 'block';
                    }
                });
            }
        });

        // Highlight de linha da tabela no hover (apenas desktop)
        if (window.innerWidth > 768) {
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = '#f8fafc';
                    this.style.transform = 'scale(1.005)';
                    this.style.transition = 'all 0.2s ease';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = '';
                    this.style.transform = '';
                });
            });
        }

        // Smooth scroll para âncoras internas
        const anchorLinks = document.querySelectorAll('a[href^="#"]');
        anchorLinks.forEach(link => {
            link.addEventListener('click', function(e) {
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

        // Animação para barras de durabilidade
        const durabilityBars = document.querySelectorAll('.durability-bar div');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const bar = entry.target;
                    const width = bar.style.width;
                    bar.style.width = '0%';
                    setTimeout(() => {
                        bar.style.width = width;
                        bar.style.transition = 'width 1s ease-out';
                    }, 200);
                }
            });
        });

        durabilityBars.forEach(bar => observer.observe(bar));

        // Copy to clipboard para pressões de pneus
        const pressureDisplays = document.querySelectorAll('[data-copy-pressure]');
        pressureDisplays.forEach(display => {
            display.style.cursor = 'pointer';
            display.title = 'Clique para copiar pressão';
            
            display.addEventListener('click', function() {
                const pressure = this.textContent.trim();
                navigator.clipboard.writeText(pressure).then(() => {
                    const originalText = this.textContent;
                    this.textContent = 'Copiado!';
                    this.style.color = '#10b981';
                    
                    setTimeout(() => {
                        this.textContent = originalText;
                        this.style.color = '';
                    }, 1500);
                });
            });
        });

        // Contador para timeline de verificação
        const timelineItems = document.querySelectorAll('.verification-timeline .timeline-item');
        timelineItems.forEach((item, index) => {
            item.style.opacity = '0';
            item.style.transform = 'translateX(-20px)';
            
            setTimeout(() => {
                item.style.opacity = '1';
                item.style.transform = 'translateX(0)';
                item.style.transition = 'all 0.5s ease-out';
            }, index * 200);
        });

        // Expandir/recolher cards de sinais críticos
        const criticalCards = document.querySelectorAll('.critical-sign-card');
        criticalCards.forEach(card => {
            const header = card.querySelector('h3');
            if (header) {
                header.style.cursor = 'pointer';
                header.addEventListener('click', function() {
                    const content = card.querySelector('.critical-content');
                    if (content) {
                        content.style.display = content.style.display === 'none' ? 'block' : 'none';
                    }
                });
            }
        });

        // Tooltips para especificações técnicas
        const specItems = document.querySelectorAll('.spec-tooltip');
        specItems.forEach(item => {
            const tooltip = item.querySelector('.tooltip-content');
            if (tooltip) {
                item.addEventListener('mouseenter', function() {
                    tooltip.style.visibility = 'visible';
                    tooltip.style.opacity = '1';
                });
                
                item.addEventListener('mouseleave', function() {
                    tooltip.style.visibility = 'hidden';
                    tooltip.style.opacity = '0';
                });
            }
        });

        // Progress indicator para leitura do artigo
        const progressBar = document.createElement('div');
        progressBar.className = 'fixed top-0 left-0 h-1 bg-[#0E368A] z-50 transition-all duration-300';
        progressBar.style.width = '0%';
        document.body.appendChild(progressBar);

        window.addEventListener('scroll', function() {
            const scrollTop = window.pageYOffset;
            const docHeight = document.body.offsetHeight - window.innerHeight;
            const scrollPercent = (scrollTop / docHeight) * 100;
            progressBar.style.width = scrollPercent + '%';
        });

        // Quick actions para verificação rápida
        const quickActions = document.createElement('div');
        quickActions.className = 'fixed bottom-4 right-4 space-y-2 z-40';
        quickActions.innerHTML = `
            <div class="bg-[#0E368A] text-white p-3 rounded-full shadow-lg cursor-pointer hover:bg-[#0A2868] transition-colors duration-200" title="Verificação Rápida">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        `;
        
        // Mostrar apenas em desktop
        if (window.innerWidth > 768) {
            document.body.appendChild(quickActions);
            
            quickActions.addEventListener('click', function() {
                const procedureSection = document.querySelector('section:has(h2:contains("Procedimento"))');
                if (procedureSection) {
                    procedureSection.scrollIntoView({ behavior: 'smooth' });
                }
            });
        }

        // Lazy loading melhorado para imagens
        const images = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    img.style.opacity = '1';
                    imageObserver.unobserve(img);
                }
            });
        }, {
            rootMargin: '50px 0px'
        });

        images.forEach(img => {
            img.style.opacity = '0';
            imageObserver.observe(img);
        });

        // Validação de pressão em tempo real (se houver inputs)
        const pressureInputs = document.querySelectorAll('input[data-pressure]');
        pressureInputs.forEach(input => {
            input.addEventListener('input', function() {
                const value = parseFloat(this.value);
                const min = parseFloat(this.dataset.min || 0);
                const max = parseFloat(this.dataset.max || 100);
                
                if (value < min || value > max) {
                    this.style.borderColor = '#ef4444';
                    this.style.backgroundColor = '#fef2f2';
                } else {
                    this.style.borderColor = '#10b981';
                    this.style.backgroundColor = '#f0fdf4';
                }
            });
        });

        // Analytics tracking para interações importantes
        const trackEvent = (action, category, label) => {
            if (typeof gtag !== 'undefined') {
                gtag('event', action, {
                    event_category: category,
                    event_label: label
                });
            }
        };

        // Track clicks em pressões recomendadas
        pressureDisplays.forEach(display => {
            display.addEventListener('click', () => {
                trackEvent('copy_pressure', 'tire_maintenance', display.textContent.trim());
            });
        });

        // Track scroll para seções importantes
        const importantSections = document.querySelectorAll('section h2');
        const sectionObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const sectionTitle = entry.target.textContent;
                    trackEvent('section_view', 'tire_guide', sectionTitle);
                }
            });
        }, { threshold: 0.5 });

        importantSections.forEach(section => sectionObserver.observe(section));
    });
</script>
@endpush