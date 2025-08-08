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
        <meta itemprop="category" content="Manutenção Automotiva" />

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
            <div class="mb-10">
                <p class="text-lg text-gray-800 leading-relaxed">
                    {{ $article->introduction }}
                </p>
            </div>

            <!-- Banner de Anúncio 1 -->
            <div class="my-8">
                [ADSENSE-ARTICLE-1]
            </div>

            <!-- Resumo das Revisões -->
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Visão Geral das Revisões Programadas
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
                                    <td class="py-3 px-4 text-sm font-medium">{{ $schedule['revisao'] }}</td>
                                    <td class="py-3 px-4 text-sm">{{ $schedule['intervalo'] }}</td>
                                    <td class="py-3 px-4 text-sm">{{ $schedule['principais_servicos'] }}</td>
                                    <td class="py-3 px-4 text-sm">{{ $schedule['estimativa_custo'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 bg-gray-50 text-sm text-gray-700">
                        <span class="font-medium">*Custos estimados:</span> Valores de referência em {{ \Carbon\Carbon::now()->locale('pt_BR')->isoFormat('MMMM [de] YYYY') }} para
                        concessionárias em capitais brasileiras. Os valores podem variar conforme a região,
                        inflação e promoções.
                    </div>
                </div>
            </section>

            <!-- Timeline de Revisões Detalhadas -->
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Detalhamento das Revisões
                </h2>

                <div class="relative">
                    <!-- Linha vertical da timeline -->
                    <div class="absolute left-8 md:left-12 top-0 bottom-0 w-0.5 bg-gray-200"></div>

                    @foreach($article->detailed_schedule as $index => $revision)
                    <div class="relative mb-10 pl-20 md:pl-28">
                        <div class="absolute left-0 top-0 h-16 w-16 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/15 border border-gray-200 flex items-center justify-center z-10">
                            <div class="h-10 w-10 rounded-full bg-[#0E368A] flex items-center justify-center text-white font-semibold">
                                {{ $revision['km'] }}
                            </div>
                        </div>

                        <div class="bg-white rounded-lg border border-gray-200 p-5">
                            <h3 class="text-lg font-medium text-[#151C25] mb-3">
                                {{ $revision['numero_revisao'] }}ª Revisão ({{ $revision['intervalo'] }})
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900 mb-2">Procedimentos Principais:</h4>
                                    <ul class="space-y-1">
                                        @foreach($revision['servicos_principais'] as $servico)
                                        <li class="flex items-center text-sm text-gray-700">
                                            <div class="h-4 w-4 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-2 flex-shrink-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5 text-[#0E368A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                            <span>{{ $servico }}</span>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>

                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900 mb-2">Verificações Complementares:</h4>
                                    <ul class="space-y-1">
                                        @foreach($revision['verificacoes_complementares'] as $verificacao)
                                        <li class="flex items-center text-sm text-gray-700">
                                            <div class="h-4 w-4 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-2 flex-shrink-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5 text-[#0E368A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                            <span>{{ $verificacao }}</span>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>

                            @if($revision['observacoes'])
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                <div class="flex-shrink-0 mr-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#E06600]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-700">
                                    <span class="font-semibold">Importante:</span> {{ $revision['observacoes'] }}
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>

            <!-- Banner de Anúncio 2 -->
            <div class="my-10">
                [ADSENSE-ARTICLE-2]
            </div>

            <!-- Manutenção Preventiva -->
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Manutenção Preventiva Entre Revisões
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Verificações mensais -->
                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center mb-4">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/15 flex items-center justify-center mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0E368A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Verificações Mensais</h3>
                        </div>

                        <ul class="space-y-2">
                            @foreach($article->preventive_maintenance['verificacoes_mensais'] as $item)
                            <li class="flex items-start">
                                <div class="h-5 w-5 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-2 flex-shrink-0 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-[#0E368A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-700">{{ $item }}</p>
                            </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Verificações trimestrais -->
                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center mb-4">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/15 flex items-center justify-center mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0E368A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Verificações Trimestrais</h3>
                        </div>

                        <ul class="space-y-2">
                            @foreach($article->preventive_maintenance['verificacoes_trimestrais'] as $item)
                            <li class="flex items-start">
                                <div class="h-5 w-5 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-2 flex-shrink-0 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-[#0E368A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-700">{{ $item }}</p>
                            </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Verificações anuais -->
                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center mb-4">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/15 flex items-center justify-center mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0E368A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Verificações Anuais</h3>
                        </div>

                        <ul class="space-y-2">
                            @foreach($article->preventive_maintenance['verificacoes_anuais'] as $item)
                            <li class="flex items-start">
                                <div class="h-5 w-5 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-2 flex-shrink-0 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-[#0E368A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-700">{{ $item }}</p>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Peças que Exigem Atenção -->
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Peças que Exigem Atenção Especial
                </h2>

                <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($article->critical_parts as $part)
                        <div class="flex items-start">
                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-[#E06600]/5 to-[#E06600]/15 flex items-center justify-center mr-3 flex-shrink-0 mt-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#E06600]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 mb-1">{{ $part['componente'] }}</h3>
                                <p class="text-sm text-gray-700 mb-2">
                                    <span class="font-medium">Recomendação de troca:</span> {{ $part['intervalo_recomendado'] }}
                                </p>
                                <p class="text-sm text-gray-600">{{ $part['observacao'] }}</p>
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

            <!-- Garantia e Recomendações -->
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Garantia e Recomendações Adicionais
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Informações de Garantia -->
                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center mb-4">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/15 flex items-center justify-center mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0E368A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Prazo de Garantia</h3>
                        </div>

                        <div class="space-y-4">
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-sm text-gray-700">
                                    <span class="font-semibold">Garantia Contratual:</span> {{ $article->warranty_info['prazo_garantia'] }}
                                </p>
                            </div>

                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-sm text-gray-700">
                                    <span class="font-semibold">Garantia para Itens de Desgaste:</span> {{ $article->warranty_info['garantia_itens_desgaste'] }}
                                </p>
                            </div>

                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-sm text-gray-700">
                                    <span class="font-semibold">Garantia Anticorrosão:</span> {{ $article->warranty_info['garantia_anticorrosao'] }}
                                </p>
                            </div>
                        </div>

                        @if($article->warranty_info['observacoes_importantes'])
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <p class="text-sm text-gray-700">
                                <span class="font-semibold">Importante:</span> {{ $article->warranty_info['observacoes_importantes'] }}
                            </p>
                        </div>
                        @endif
                    </div>

                    <!-- Dicas para Prolongar a Vida Útil -->
                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <div class="flex items-center mb-4">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-[#0E368A]/5 to-[#0E368A]/15 flex items-center justify-center mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0E368A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Dicas para Prolongar a Vida Útil</h3>
                        </div>

                        <ul class="space-y-3">
                            @foreach($article->warranty_info['dicas_vida_util'] as $dica)
                            <li class="flex items-start">
                                <div class="h-5 w-5 rounded-full bg-[#0E368A]/10 flex items-center justify-center mr-2 flex-shrink-0 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-[#0E368A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-700">{{ $dica }}</p>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- Alerta de Importância -->
                <div class="bg-[#E06600]/5 border border-[#E06600]/20 rounded-lg p-5">
                    <div class="flex">
                        <div class="flex-shrink-0 mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#E06600]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-[#E06600] mb-2">Atenção às Revisões Críticas</h3>
                            <p class="text-gray-700 mb-3">
                                As revisões de 20.000 km e 60.000 km são consideradas críticas para o {{ $article->vehicle_full_name }},
                                pois incluem a verificação e/ou substituição de componentes fundamentais para a
                                longevidade do motor.
                            </p>
                            <p class="text-gray-700">
                                A revisão de 60.000 km, em particular, inclui a troca da correia dentada, componente
                                crítico cuja falha pode causar sérios danos ao motor. Não postergue esta revisão e
                                sempre utilize peças originais ou homologadas pela fabricante.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Perguntas Frequentes -->
            <section class="mb-12">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-6 pb-2 border-b border-gray-200">
                    Perguntas Frequentes
                </h2>

                <div class="space-y-4">
                    @foreach($article->faq as $question)
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                        <div class="p-5">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ $question['pergunta'] }}</h3>
                            <p class="text-gray-700">{{ $question['resposta'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>

            <!-- Conclusão -->
            <section class="mb-12 bg-gray-50 rounded-lg p-6">
                <h2 class="text-2xl font-semibold text-[#151C25] mb-4">Considerações Finais</h2>
                <p class="text-gray-800 mb-4">{{ $article->final_considerations }}</p>
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
    /* Estilos específicos para template de cronograma de revisões */
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
    });
</script>
@endpush