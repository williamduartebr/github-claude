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
        @if(!empty($article->vehicle_full_name))
        <meta itemprop="vehicleEngine" content="{{ $article->vehicle_full_name }}" />
        @endif
        <meta itemprop="category" content="Manutenção de Veículos Elétricos" />

        <article class="max-w-4xl mx-auto pt-6 pb-12">
            @php
            $imageDefault = \Str::slug(sprintf("%s-%s", $article->category['slug'] ?? 'revisoes', 
                $article->vehicle_info['vehicle_type'] ?? 'cronograma'));
            @endphp

            <div class="relative rounded-lg overflow-hidden mb-8 mt-2 hidden md:block">
                <img src="https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/review_schedule.png"
                    alt="{{ $article->title }}" class="w-full h-64 object-cover"
                    onerror="this.src='https:\/\/mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/review_schedule.png'">
                <div class="absolute inset-0 bg-gradient-to-t from-black/100 to-transparent"></div>
                <div class="absolute bottom-0 left-0 p-6 text-white">
                    <h1 class="text-3xl md:text-4xl font-semibold leading-tight">{{ $article->title }}</h1>
                    @if(!empty($article->formated_updated_at))
                    <p class="text-sm mt-2 opacity-90">Atualizado em: {{ $article->formated_updated_at }}</p>
                    @endif
                </div>
                </div>

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

            <!-- Resumo das Revisões -->
            @if(!empty($article->overview_schedule) && is_array($article->overview_schedule) && count($article->overview_schedule) > 0)
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    🔋 Cronograma de Revisões para Veículos Elétricos
                </h2>

                <div class="bg-white rounded-lg border shadow-sm overflow-hidden border-l-4 border-blue-500">
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse">
                            <thead>
                                <tr class="bg-blue-600 text-white">
                                    <th class="py-3 px-4 text-left font-medium text-sm">Revisão</th>
                                    <th class="py-3 px-4 text-left font-medium text-sm">Quilometragem / Tempo</th>
                                    <th class="py-3 px-4 text-left font-medium text-sm">Principais Serviços</th>
                                    <th class="py-3 px-4 text-left font-medium text-sm">Estimativa de Custo*</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($article->overview_schedule as $index => $schedule)
                                <tr class="border-b border-gray-200 {{ $index % 2 == 0 ? 'bg-white' : 'bg-blue-50' }}">
                                    <td class="py-3 px-4 text-sm font-medium">{{ $schedule['revisao'] ?? '-' }}</td>
                                    <td class="py-3 px-4 text-sm">{{ $schedule['intervalo'] ?? '-' }}</td>
                                    <td class="py-3 px-4 text-sm">{{ $schedule['principais_servicos'] ?? '-' }}</td>
                                    <td class="py-3 px-4 text-sm">{{ $schedule['estimativa_custo'] ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 bg-blue-50 text-sm text-gray-700 border-l-4 border-blue-500">
                        <span class="font-medium">*Custos estimados para veículos elétricos:</span> Valores de referência em {{ \Carbon\Carbon::now()->locale('pt_BR')->isoFormat('MMMM [de] YYYY') }} para
                        concessionárias especializadas em capitais brasileiras. Veículos elétricos geralmente têm custos de manutenção menores
                        devido à menor complexidade mecânica.
                    </div>
                </div>
            </section>
            @endif

            <!-- Timeline de Revisões Detalhadas -->
            @if(!empty($article->detailed_schedule) && is_array($article->detailed_schedule) && count($article->detailed_schedule) > 0)
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    ⚡ Detalhamento das Revisões Elétricas
                </h2>

                <div class="relative">
                    <!-- Linha vertical da timeline -->
                    <div class="absolute left-8 md:left-12 top-0 bottom-0 w-0.5 bg-blue-300"></div>

                    @foreach($article->detailed_schedule as $index => $revision)
                    <div class="relative mb-10 pl-20 md:pl-28">
                        <div class="absolute left-0 top-0 h-16 w-16 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/15 border border-gray-200 flex items-center justify-center z-10">
                            <div class="h-10 w-10 rounded-full bg-[#0E368A] flex items-center justify-center text-white font-semibold">
                                {{ $revision['km'] ?? '?' }}
                            </div>
                        </div>

                        <div class="bg-white rounded-lg border p-5 shadow-sm">
                            <h3 class="text-lg font-medium text-[#151C25] mb-3">
                                🔋 {{ $revision['numero_revisao'] ?? '?' }}ª Revisão ({{ $revision['intervalo'] ?? 'N/A' }})
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                @if(!empty($revision['servicos_principais']) && is_array($revision['servicos_principais']) && count($revision['servicos_principais']) > 0)
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900 mb-2">⚡ Procedimentos Principais:</h4>
                                    <ul class="space-y-1">
                                        @foreach($revision['servicos_principais'] as $servico)
                                        @if(!empty($servico))
                                        <li class="flex items-center text-sm text-gray-700">
                                            <div class="h-4 w-4 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-2 flex-shrink-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5 text-[#0E368A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                            <span>{{ $servico }}</span>
                                        </li>
                                        @endif
                                        @endforeach
                                    </ul>
                                </div>
                                @endif

                                @if(!empty($revision['verificacoes_complementares']) && is_array($revision['verificacoes_complementares']) && count($revision['verificacoes_complementares']) > 0)
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900 mb-2">🔍 Verificações Complementares:</h4>
                                    <ul class="space-y-1">
                                        @foreach($revision['verificacoes_complementares'] as $verificacao)
                                        @if(!empty($verificacao))
                                        <li class="flex items-center text-sm text-gray-700">
                                            <div class="h-4 w-4 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-2 flex-shrink-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5 text-[#0E368A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                            <span>{{ $verificacao }}</span>
                                        </li>
                                        @endif
                                        @endforeach
                                    </ul>
                                </div>
                                @endif
                            </div>

                            @if(!empty($revision['observacoes']))
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                <div class="flex-shrink-0 mr-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#E06600]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-700">
                                    <span class="font-semibold">⚠️ Importante:</span> {{ $revision['observacoes'] }}
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Banner de Anúncio 2 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-2]
            </div>

            <!-- Manutenção Preventiva -->
            @if(!empty($article->preventive_maintenance) && is_array($article->preventive_maintenance))
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    🔧 Manutenção Preventiva para Veículos Elétricos
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Verificações mensais -->
                    @if(!empty($article->preventive_maintenance['verificacoes_mensais']) && is_array($article->preventive_maintenance['verificacoes_mensais']) && count($article->preventive_maintenance['verificacoes_mensais']) > 0)
                    <div class="bg-white rounded-lg border p-5 border-l-4 border-blue-400">
                        <div class="flex items-center mb-4">
                            <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                                <span class="text-2xl">📅</span>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Verificações Mensais</h3>
                        </div>

                        <ul class="space-y-2">
                            @foreach($article->preventive_maintenance['verificacoes_mensais'] as $item)
                            @if(!empty($item))
                            <li class="flex items-start">
                                <div class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mr-2 flex-shrink-0 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-700">{{ $item }}</p>
                            </li>
                            @endif
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <!-- Verificações trimestrais -->
                    @if(!empty($article->preventive_maintenance['verificacoes_trimestrais']) && is_array($article->preventive_maintenance['verificacoes_trimestrais']) && count($article->preventive_maintenance['verificacoes_trimestrais']) > 0)
                    <div class="bg-white rounded-lg border p-5 border-l-4 border-blue-500">
                        <div class="flex items-center mb-4">
                            <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                                <span class="text-2xl">🔋</span>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Verificações Trimestrais</h3>
                        </div>

                        <ul class="space-y-2">
                            @foreach($article->preventive_maintenance['verificacoes_trimestrais'] as $item)
                            @if(!empty($item))
                            <li class="flex items-start">
                                <div class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mr-2 flex-shrink-0 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-700">{{ $item }}</p>
                            </li>
                            @endif
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <!-- Verificações anuais -->
                    @if(!empty($article->preventive_maintenance['verificacoes_anuais']) && is_array($article->preventive_maintenance['verificacoes_anuais']) && count($article->preventive_maintenance['verificacoes_anuais']) > 0)
                    <div class="bg-white rounded-lg border  p-5 border-l-4 border-blue-600">
                        <div class="flex items-center mb-4">
                            <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                                <span class="text-2xl">⚡</span>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Verificações Anuais</h3>
                        </div>

                        <ul class="space-y-2">
                            @foreach($article->preventive_maintenance['verificacoes_anuais'] as $item)
                            @if(!empty($item))
                            <li class="flex items-start">
                                <div class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mr-2 flex-shrink-0 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-700">{{ $item }}</p>
                            </li>
                            @endif
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>

                <!-- Cuidados Especiais -->
                @if(!empty($article->preventive_maintenance['cuidados_especiais']) && is_array($article->preventive_maintenance['cuidados_especiais']) && count($article->preventive_maintenance['cuidados_especiais']) > 0)
                <div class="mt-6 bg-blue-50 rounded-lg border border-blue-200 p-5">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                        <span class="text-2xl mr-2">🔒</span>
                        Cuidados Especiais para Veículos Elétricos
                    </h3>
                    <ul class="space-y-2">
                        @foreach($article->preventive_maintenance['cuidados_especiais'] as $cuidado)
                        @if(!empty($cuidado))
                        <li class="flex items-start">
                            <div class="h-5 w-5 rounded-full bg-blue-200 flex items-center justify-center mr-2 flex-shrink-0 mt-0.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <p class="text-sm text-gray-700">{{ $cuidado }}</p>
                        </li>
                        @endif
                        @endforeach
                    </ul>
                </div>
                @endif
            </section>
            @endif

            <!-- Peças que Exigem Atenção -->
            @if(!empty($article->critical_parts) && is_array($article->critical_parts) && count($article->critical_parts) > 0)
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    ⚠️ Componentes Críticos em Veículos Elétricos
                </h2>

                <div class="bg-white rounded-lg border shadow-sm p-6 border-l-4 border-blue-500">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($article->critical_parts as $part)
                        @if(!empty($part['componente']))
                        <div class="flex items-start p-4 bg-blue-50 rounded-lg border border-blue-200">
                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center mr-3 flex-shrink-0 mt-1">
                                <span class="text-lg">🔋</span>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 mb-1">{{ $part['componente'] }}</h3>
                                @if(!empty($part['intervalo_recomendado']))
                                <p class="text-sm text-gray-700 mb-2">
                                    <span class="font-medium">🔧 Recomendação:</span> {{ $part['intervalo_recomendado'] }}
                                </p>
                                @endif
                                @if(!empty($part['observacao']))
                                <p class="text-sm text-gray-600">{{ $part['observacao'] }}</p>
                                @endif
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
            </section>
            @endif

            <!-- Banner de Anúncio 3 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-3]
            </div>

            <!-- Garantia e Recomendações -->
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
                                    <span class="font-semibold">Garantia Geral:</span> {{ $article->warranty_info['prazo_garantia_geral'] }}
                                </p>
                            </div>
                            @endif

                            @if(!empty($article->warranty_info['garantia_bateria']))
                            <div class="bg-blue-50 p-3 rounded-lg border-l-4 border-blue-400">
                                <p class="text-sm text-gray-700">
                                    <span class="font-semibold">🔋 Garantia da Bateria:</span> {{ $article->warranty_info['garantia_bateria'] }}
                                </p>
                            </div>
                            @endif

                            @if(!empty($article->warranty_info['garantia_motor_eletrico']))
                            <div class="bg-blue-50 p-3 rounded-lg border-l-4 border-blue-400">
                                <p class="text-sm text-gray-700">
                                    <span class="font-semibold">⚡ Garantia do Motor Elétrico:</span> {{ $article->warranty_info['garantia_motor_eletrico'] }}
                                </p>
                            </div>
                            @endif
                        </div>

                        @if(!empty($article->warranty_info['observacoes_importantes']))
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <p class="text-sm text-gray-700">
                                <span class="font-semibold">💡 Importante:</span> {{ $article->warranty_info['observacoes_importantes'] }}
                            </p>
                        </div>
                        @endif
                    </div>

                    <!-- Dicas para Prolongar a Vida Útil -->
                    @if(!empty($article->warranty_info['dicas_preservacao']) && is_array($article->warranty_info['dicas_preservacao']) && count($article->warranty_info['dicas_preservacao']) > 0)
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
                                <div class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mr-2 flex-shrink-0 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                                componentes básicos como pneus e freios. Isso resulta em custos operacionais significativamente menores.
                            </p>
                        </div>
                    </div>
                </div>
                @endif
            </section>
            @endif

            <!-- Perguntas Frequentes -->
            @if(!empty($article->faq) && is_array($article->faq) && count($article->faq) > 0)
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    ❓ Perguntas Frequentes sobre Veículos Elétricos
                </h2>

                <div class="space-y-4">
                    @foreach($article->faq as $question)
                    @if(!empty($question['pergunta']) && !empty($question['resposta']))
                    <div class="bg-white rounded-lg border shadow-sm overflow-hidden border-l-4 border-blue-400">
                        <div class="p-5">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">🔋 {{ $question['pergunta'] }}</h3>
                            <p class="text-gray-700">{{ $question['resposta'] }}</p>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Conclusão -->
            @if(!empty($article->final_considerations))
            <section class="mb-12 bg-blue-50 rounded-lg p-6 border-l-4 border-blue-500">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-4">🌱 Considerações Finais</h2>
                <p class="text-gray-800 mb-4">{{ $article->final_considerations }}</p>
            </section>
            @endif

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
    /* Estilos específicos para template de cronograma de revisões de veículos elétricos */
    .timeline-icon {
        @apply text-blue-600 mr-2;
    }

    /* Timeline elétrica simples */
    .electric-timeline {
        background: rgba(59, 130, 246, 0.05);
    }

    /* Timeline responsive */
    @media (max-width: 768px) {
        .timeline-container {
            padding-left: 3rem;
        }
        
        .timeline-marker {
            left: 0.5rem;
            width: 3rem;
            height: 3rem;
        }
        
        .timeline-marker .inner {
            width: 2rem;
            height: 2rem;
        }
    }

    /* Destaque para componentes elétricos */
    .electric-critical {
        background: rgba(59, 130, 246, 0.05);
        border-left: 4px solid #3b82f6;
    }

    /* Animações sutis para elementos elétricos */
    .electric-card {
        transition: all 0.3s ease;
    }

    .electric-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
    }

    /* Estilos para impressão */
    @media print {
        .no-print {
            display: none !important;
        }

        main {
            padding: 0 !important;
        }

        section {
            page-break-inside: avoid;
        }
    }

    /* Indicadores visuais para sistemas elétricos */
    .electric-indicator {
        position: relative;
    }

    .electric-indicator::before {
        content: '⚡';
        position: absolute;
        top: -5px;
        right: -5px;
        font-size: 12px;
        background: #3b82f6;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
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

        // Animação suave para cards de revisão elétrica
        const revisionCards = document.querySelectorAll('.electric-card, .revision-card');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        });

        revisionCards.forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });

        // Destaque especial para componentes críticos de elétricos
        const criticalParts = document.querySelectorAll('[data-electric-critical]');
        criticalParts.forEach(part => {
            part.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.02)';
                this.style.transition = 'transform 0.2s ease';
                this.style.boxShadow = '0 8px 25px rgba(59, 130, 246, 0.15)';
            });
            
            part.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
                this.style.boxShadow = '0 2px 4px rgba(0,0,0,0.05)';
            });
        });

        // Indicador visual para sistemas elétricos
        const electricElements = document.querySelectorAll('.electric-indicator');
        electricElements.forEach(element => {
            element.style.position = 'relative';
            
            // Adiciona efeito sutil para indicar sistema elétrico
            setInterval(() => {
                element.style.boxShadow = '0 0 15px rgba(59, 130, 246, 0.2)';
                setTimeout(() => {
                    element.style.boxShadow = '0 2px 4px rgba(0,0,0,0.05)';
                }, 1000);
            }, 4000);
        });

        // Alerta especial para alta tensão
        const highVoltageElements = document.querySelectorAll('[data-high-voltage]');
        highVoltageElements.forEach(element => {
            element.style.border = '2px solid #3b82f6';
            element.style.backgroundColor = 'rgba(59, 130, 246, 0.05)';
            
            element.addEventListener('click', function() {
                alert('⚠️ ATENÇÃO: Este componente envolve alta tensão. Manutenção deve ser realizada apenas por técnicos especializados em veículos elétricos!');
            });
        });
    });
</script>
@endpush