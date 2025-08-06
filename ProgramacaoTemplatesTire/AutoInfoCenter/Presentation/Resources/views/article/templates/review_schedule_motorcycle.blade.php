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
        <meta itemprop="category" content="Manutenção de Motocicletas" />

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
                    🏍️ Cronograma de Revisões Programadas
                </h2>

                <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse">
                            <thead>
                                <tr class="bg-[#0E368A] text-white">
                                    <th class="py-3 px-4 text-left font-medium text-sm">Revisão</th>
                                    <th class="py-3 px-4 text-left font-medium text-sm">Quilometragem / Tempo</th>
                                    <th class="py-3 px-4 text-left font-medium text-sm">Principais Serviços</th>
                                    <th class="py-3 px-4 text-left font-medium text-sm">Estimativa de Custo*</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($article->overview_schedule as $index => $schedule)
                                <tr class="border-b border-gray-200 {{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }}">
                                    <td class="py-3 px-4 text-sm font-medium">{{ $schedule['revisao'] ?? '-' }}</td>
                                    <td class="py-3 px-4 text-sm">{{ $schedule['intervalo'] ?? '-' }}</td>
                                    <td class="py-3 px-4 text-sm">{{ $schedule['principais_servicos'] ?? '-' }}</td>
                                    <td class="py-3 px-4 text-sm">{{ $schedule['estimativa_custo'] ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 bg-gray-50 text-sm text-gray-700">
                        <span class="font-medium">*Custos estimados para motocicletas:</span> Valores de referência em {{ \Carbon\Carbon::now()->locale('pt_BR')->isoFormat('MMMM [de] YYYY') }} para
                        concessionárias e oficinas especializadas em capitais brasileiras. Os valores podem variar conforme a região,
                        inflação e promoções.
                    </div>
                </div>
            </section>
            @endif

            <!-- Timeline de Revisões Detalhadas -->
            @if(!empty($article->detailed_schedule) && is_array($article->detailed_schedule) && count($article->detailed_schedule) > 0)
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    🔧 Detalhamento das Revisões
                </h2>

                <div class="relative">
                    <!-- Linha vertical da timeline -->
                    <div class="absolute left-8 md:left-12 top-0 bottom-0 w-0.5 bg-gray-200"></div>

                    @foreach($article->detailed_schedule as $index => $revision)
                    <div class="relative mb-10 pl-20 md:pl-28">
                        <div class="absolute left-0 top-0 h-16 w-16 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/15 border border-gray-200 flex items-center justify-center z-10">
                            <div class="h-10 w-10 rounded-full bg-[#0E368A] flex items-center justify-center text-white font-semibold">
                                {{ $revision['km'] ?? '?' }}
                            </div>
                        </div>

                        <div class="bg-white rounded-lg border border-gray-200 p-5">
                            <h3 class="text-lg font-medium text-[#151C25] mb-3">
                                {{ $revision['numero_revisao'] ?? '?' }}ª Revisão ({{ $revision['intervalo'] ?? 'N/A' }})
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                @if(!empty($revision['servicos_principais']) && is_array($revision['servicos_principais']) && count($revision['servicos_principais']) > 0)
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900 mb-2">🔩 Procedimentos Principais:</h4>
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
                    🔧 Manutenção Preventiva Para Motocicletas
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Verificações mensais -->
                    @if(!empty($article->preventive_maintenance['verificacoes_mensais']) && is_array($article->preventive_maintenance['verificacoes_mensais']) && count($article->preventive_maintenance['verificacoes_mensais']) > 0)
                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center mb-4">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/15 flex items-center justify-center mr-3">
                                <span class="text-2xl">📅</span>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Verificações Mensais</h3>
                        </div>

                        <ul class="space-y-2">
                            @foreach($article->preventive_maintenance['verificacoes_mensais'] as $item)
                            @if(!empty($item))
                            <li class="flex items-start">
                                <div class="h-5 w-5 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-2 flex-shrink-0 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-[#0E368A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center mb-4">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/15 flex items-center justify-center mr-3">
                                <span class="text-2xl">📋</span>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Verificações Trimestrais</h3>
                        </div>

                        <ul class="space-y-2">
                            @foreach($article->preventive_maintenance['verificacoes_trimestrais'] as $item)
                            @if(!empty($item))
                            <li class="flex items-start">
                                <div class="h-5 w-5 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-2 flex-shrink-0 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-[#0E368A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center mb-4">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/15 flex items-center justify-center mr-3">
                                <span class="text-2xl">🔧</span>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Verificações Anuais</h3>
                        </div>

                        <ul class="space-y-2">
                            @foreach($article->preventive_maintenance['verificacoes_anuais'] as $item)
                            @if(!empty($item))
                            <li class="flex items-start">
                                <div class="h-5 w-5 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-2 flex-shrink-0 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-[#0E368A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
            </section>
            @endif

            <!-- Peças que Exigem Atenção -->
            @if(!empty($article->critical_parts) && is_array($article->critical_parts) && count($article->critical_parts) > 0)
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    ⚠️ Componentes Críticos em Motocicletas
                </h2>

                <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($article->critical_parts as $part)
                        @if(!empty($part['componente']))
                        <div class="flex items-start">
                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-[#E06600]/5 to-[#E06600]/15 flex items-center justify-center mr-3 flex-shrink-0 mt-1">
                                <span class="text-lg">⚙️</span>
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
                    🛡️ Garantia e Cuidados Especiais
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Informações de Garantia -->
                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center mb-4">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/15 flex items-center justify-center mr-3">
                                <span class="text-2xl">🛡️</span>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Prazo de Garantia</h3>
                        </div>

                        <div class="space-y-4">
                            @if(!empty($article->warranty_info['prazo_garantia']))
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-sm text-gray-700">
                                    <span class="font-semibold">Garantia Contratual:</span> {{ $article->warranty_info['prazo_garantia'] }}
                                </p>
                            </div>
                            @endif

                            @if(!empty($article->warranty_info['garantia_itens_desgaste']))
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-sm text-gray-700">
                                    <span class="font-semibold">Garantia para Itens de Desgaste:</span> {{ $article->warranty_info['garantia_itens_desgaste'] }}
                                </p>
                            </div>
                            @endif

                            @if(!empty($article->warranty_info['garantia_anticorrosao']))
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-sm text-gray-700">
                                    <span class="font-semibold">Garantia Anticorrosão:</span> {{ $article->warranty_info['garantia_anticorrosao'] }}
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
                    @if(!empty($article->warranty_info['dicas_vida_util']) && is_array($article->warranty_info['dicas_vida_util']) && count($article->warranty_info['dicas_vida_util']) > 0)
                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center mb-4">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/15 flex items-center justify-center mr-3">
                                <span class="text-2xl">🏍️</span>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Dicas para Motociclistas</h3>
                        </div>

                        <ul class="space-y-3">
                            @foreach($article->warranty_info['dicas_vida_util'] as $dica)
                            @if(!empty($dica))
                            <li class="flex items-start">
                                <div class="h-5 w-5 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-2 flex-shrink-0 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-[#0E368A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                <div class="bg-[#E06600]/5 border border-[#E06600]/20 rounded-lg p-5">
                    <div class="flex">
                        <div class="flex-shrink-0 mr-4">
                            <span class="text-3xl">⚠️</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-[#E06600] mb-2">🏍️ Atenção Especial para Motocicletas</h3>
                            <p class="text-gray-700 mb-3">
                                Motocicletas como a {{ $article->vehicle_full_name }} requerem cuidados específicos devido à maior exposição aos elementos.
                                A corrente de transmissão, em particular, exige atenção constante com lubrificação e ajuste de tensão.
                            </p>
                            <p class="text-gray-700">
                                ⚡ <strong>Dica importante:</strong> Sempre verifique a corrente antes de viagens longas e mantenha-a sempre limpa e lubrificada. 
                                A negligência com este componente pode causar acidentes graves.
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
                    ❓ Perguntas Frequentes
                </h2>

                <div class="space-y-4">
                    @foreach($article->faq as $question)
                    @if(!empty($question['pergunta']) && !empty($question['resposta']))
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                        <div class="p-5">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">🤔 {{ $question['pergunta'] }}</h3>
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
            <section class="mb-12 bg-gray-50 rounded-lg p-6">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-4">🏁 Considerações Finais</h2>
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
    /* Estilos específicos para template de cronograma de revisões de motocicletas */
    .timeline-icon {
        @apply text-[#0E368A] mr-2;
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

    /* Destaque especial para componentes críticos de motos */
    .motorcycle-critical {
        background: linear-gradient(135deg, rgba(224, 102, 0, 0.1), rgba(224, 102, 0, 0.05));
        border-left: 4px solid #E06600;
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

        // Animação suave para cards de revisão
        const revisionCards = document.querySelectorAll('.revision-card');
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

        // Destaque especial para componentes críticos de motocicletas
        const criticalParts = document.querySelectorAll('[data-motorcycle-critical]');
        criticalParts.forEach(part => {
            part.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.02)';
                this.style.transition = 'transform 0.2s ease';
            });
            
            part.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
            });
        });
    });
</script>
@endpush