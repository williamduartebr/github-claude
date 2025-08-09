@extends('auto-info-center::layouts.amp')

@section('amp-head')
<script async custom-element="amp-accordion" src="https://cdn.ampproject.org/v0/amp-accordion-0.1.js"></script>
<script async custom-element="amp-form" src="https://cdn.ampproject.org/v0/amp-form-0.1.js"></script>
<style amp-custom>
    /* Reset e base */
    * {
        box-sizing: border-box;
    }
    
    body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        line-height: 1.6;
        color: #333;
        margin: 0;
        padding: 0;
        background-color: #fff;
    }
    
    .container {
        max-width: 800px;
        margin: 0 auto;
        padding: 16px;
    }
    
    /* Typography melhorada */
    h1 {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 16px;
        color: #151C25;
        line-height: 1.3;
    }
    
    h2 {
        font-size: 22px;
        font-weight: 600;
        margin: 32px 0 16px;
        padding-bottom: 12px;
        border-bottom: 2px solid #e5e7eb;
        color: #151C25;
        position: relative;
    }
    
    h2:after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 60px;
        height: 2px;
        background-color: #0E368A;
    }
    
    h3 {
        font-size: 18px;
        font-weight: 600;
        margin: 20px 0 12px;
        color: #151C25;
    }
    
    p {
        margin-bottom: 16px;
        line-height: 1.7;
    }
    
    /* Meta info */
    .article-meta {
        color: #718096;
        font-size: 14px;
        margin-bottom: 20px;
        padding: 12px 0;
        border-bottom: 1px solid #e5e7eb;
    }
    
    /* Ad containers melhorados */
    .ad-container {
        text-align: center;
        margin: 32px 0;
        padding: 20px;
        background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        border-radius: 12px;
        border: 1px solid #e5e7eb;
    }
    
    .ad-label {
        font-size: 11px;
        color: #64748b;
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 500;
    }
    
    /* Tabela principal moderna */
    .schedule-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        margin-bottom: 24px;
        background-color: #fff;
        border: 1px solid #e5e7eb;
    }
    
    .schedule-table-header {
        background: #f8fafc;
        padding: 16px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
    }
    
    .schedule-table-icon {
        width: 32px;
        height: 32px;
        background: #0E368A;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        font-size: 14px;
    }
    
    .schedule-table th {
        background: #0E368A;
        color: white;
        text-align: left;
        padding: 16px 12px;
        font-weight: 600;
        font-size: 12px;
    }
    
    .schedule-table td {
        padding: 16px 12px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: top;
    }
    
    .schedule-table tr:nth-child(even) td {
        background-color: #f8fafc;
    }
    
    .schedule-table tr:last-child td {
        border-bottom: none;
    }
    
    .schedule-table tr:hover td {
        background-color: #f1f5f9;
    }
    
    .table-row-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        background: #e5e7eb;
        border-radius: 50%;
        font-size: 10px;
        font-weight: bold;
        margin-right: 8px;
    }
    
    .table-cost {
        font-weight: 600;
        color: #0E368A;
    }
    
    /* Cards de revisão modernos */
    .revision-card {
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        background-color: #fff;
    }
    
    .revision-header {
        background: #0E368A;
        color: white;
        padding: 20px;
        position: relative;
    }
    
    .revision-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 4px;
    }
    
    .revision-interval {
        background: rgba(255,255,255,0.2);
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 12px;
        display: inline-block;
    }
    
    .revision-number {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        width: 48px;
        height: 48px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 16px;
        border: 2px solid rgba(255,255,255,0.3);
    }
    
    .revision-body {
        padding: 24px;
    }
    
    .service-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .service-section {
        background: #f8fafc;
        padding: 16px;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
    }
    
    .service-section h4 {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 12px;
        color: #151C25;
        display: flex;
        align-items: center;
    }
    
    .service-icon {
        width: 24px;
        height: 24px;
        background: #0E368A;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 8px;
        font-size: 10px;
        color: white;
    }
    
    .service-list {
        margin: 0;
        padding: 0;
        list-style: none;
    }
    
    .service-list li {
        margin-bottom: 8px;
        color: #4a5568;
        position: relative;
        padding-left: 16px;
        font-size: 13px;
        line-height: 1.5;
    }
    
    .service-list li:before {
        content: '';
        position: absolute;
        left: 0;
        top: 8px;
        width: 6px;
        height: 6px;
        background: #0E368A;
        border-radius: 50%;
    }
    
    .revision-note {
        background: linear-gradient(135deg, #fef3cd, #fef3cd);
        padding: 16px;
        border-radius: 12px;
        border-left: 4px solid #f59e0b;
        font-size: 13px;
        color: #92400e;
        margin-top: 20px;
    }
    
    .revision-note-title {
        font-weight: 600;
        margin-bottom: 4px;
        color: #92400e;
    }
    
    /* Cards de manutenção preventiva modernos */
    .maintenance-card {
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 20px;
        background-color: #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .maintenance-header {
        padding: 16px 20px;
        font-weight: 600;
        font-size: 14px;
        display: flex;
        align-items: center;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .maintenance-header.monthly {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        border-bottom: none;
    }
    
    .maintenance-header.quarterly {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
        border-bottom: none;
    }
    
    .maintenance-header.annual {
        background: linear-gradient(135deg, #0E368A, #1e40af);
        color: white;
        border-bottom: none;
    }
    
    .maintenance-icon {
        width: 32px;
        height: 32px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        font-size: 16px;
    }
    
    .maintenance-body {
        padding: 20px;
    }
    
    .maintenance-list {
        margin: 0;
        padding: 0;
        list-style: none;
    }
    
    .maintenance-list li {
        margin-bottom: 12px;
        color: #4a5568;
        position: relative;
        padding-left: 20px;
        font-size: 13px;
        line-height: 1.5;
    }
    
    .maintenance-list li:before {
        content: '';
        position: absolute;
        left: 0;
        top: 8px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }
    
    .maintenance-list.monthly li:before {
        background: #10b981;
    }
    
    .maintenance-list.quarterly li:before {
        background: #f59e0b;
    }
    
    .maintenance-list.annual li:before {
        background: #0E368A;
    }
    
    /* Cards de peças críticas */
    .critical-part {
        background: linear-gradient(135deg, #fef2f2, #fef2f2);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 16px;
        border-left: 4px solid #ef4444;
        box-shadow: 0 2px 4px rgba(239, 68, 68, 0.1);
        border: 1px solid #fecaca;
    }
    
    .critical-part-title {
        font-weight: 600;
        margin-bottom: 8px;
        color: #151C25;
        font-size: 15px;
        display: flex;
        align-items: center;
    }
    
    .critical-part-icon {
        width: 32px;
        height: 32px;
        background: #fecaca;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        font-size: 16px;
    }
    
    .critical-part-interval {
        font-size: 13px;
        color: #dc2626;
        font-weight: 500;
        margin-bottom: 8px;
    }
    
    .critical-part-note {
        font-size: 13px;
        color: #4a5568;
        line-height: 1.5;
    }
    
    /* Seção de garantia moderna */
    .warranty-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
        margin-bottom: 24px;
    }
    
    .warranty-card {
        background-color: #fff;
        padding: 0;
        border-radius: 16px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    
    .warranty-header {
        padding: 16px 20px;
        font-weight: 600;
        margin-bottom: 0;
        color: white;
        font-size: 16px;
        display: flex;
        align-items: center;
    }
    
    .warranty-header.warranty {
        background: linear-gradient(135deg, #0E368A, #1e40af);
    }
    
    .warranty-header.tips {
        background: linear-gradient(135deg, #10b981, #059669);
    }
    
    .warranty-icon {
        width: 32px;
        height: 32px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        font-size: 16px;
    }
    
    .warranty-body {
        padding: 20px;
    }
    
    .warranty-item {
        background-color: #f8fafc;
        padding: 12px;
        margin-bottom: 12px;
        border-radius: 8px;
        font-size: 13px;
        border-left: 3px solid #0E368A;
    }
    
    .warranty-tips {
        margin: 0;
        padding: 0;
        list-style: none;
    }
    
    .warranty-tips li {
        margin-bottom: 12px;
        color: #4a5568;
        position: relative;
        padding-left: 20px;
        font-size: 13px;
        line-height: 1.5;
    }
    
    .warranty-tips li:before {
        content: '';
        position: absolute;
        left: 0;
        top: 8px;
        width: 8px;
        height: 8px;
        background: #10b981;
        border-radius: 50%;
    }
    
    /* Alerta especial moderno */
    .critical-alert {
        background: linear-gradient(135deg, #fef3cd, #fef3cd);
        border: 1px solid #fde68a;
        border-radius: 16px;
        padding: 24px;
        margin: 32px 0;
        display: flex;
        align-items: flex-start;
        border-left: 4px solid #f59e0b;
        box-shadow: 0 4px 6px -1px rgba(245, 158, 11, 0.1);
    }
    
    .alert-icon {
        width: 48px;
        height: 48px;
        background: #fde68a;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 16px;
        flex-shrink: 0;
        font-size: 20px;
    }
    
    .alert-content h3 {
        font-weight: 600;
        color: #92400e;
        margin-bottom: 8px;
        font-size: 16px;
    }
    
    .alert-content p {
        color: #92400e;
        font-size: 14px;
        line-height: 1.5;
        margin-bottom: 8px;
    }
    
    /* FAQs com accordion melhorado */
    amp-accordion {
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 24px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    amp-accordion section {
        border-bottom: 1px solid #f1f5f9;
    }
    
    amp-accordion section:last-child {
        border-bottom: none;
    }
    
    amp-accordion h4 {
        font-size: 15px;
        padding: 20px;
        margin: 0;
        background-color: #fff;
        font-weight: 600;
        color: #151C25;
        cursor: pointer;
        transition: background-color 0.2s ease;
        display: flex;
        align-items: center;
    }
    
    amp-accordion h4:hover {
        background-color: #f8fafc;
    }
    
    .faq-icon {
        width: 24px;
        height: 24px;
        background: #0E368A;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        color: white;
        font-size: 12px;
        flex-shrink: 0;
    }
    
    amp-accordion .faq-content {
        padding: 20px;
        padding-top: 0;
        background-color: #fff;
        color: #4a5568;
        line-height: 1.6;
        font-size: 14px;
        margin-left: 36px;
    }

    /* Footer melhorado */
    .article-footer {
        font-size: 12px;
        color: #718096;
        margin-top: 40px;
        padding: 20px;
        background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        border-radius: 16px;
        border: 1px solid #e5e7eb;
    }
    
    .article-footer p {
        margin-bottom: 6px;
    }
    
    .article-footer a {
        color: #0E368A;
        text-decoration: none;
        font-weight: 500;
    }
    
    .article-footer a:hover {
        text-decoration: underline;
    }
    
    /* Responsivo melhorado */
    @media (max-width: 768px) {
        .container {
            padding: 12px;
        }
        
        h1 {
            font-size: 24px;
        }
        
        h2 {
            font-size: 20px;
        }
        
        .revision-body,
        .maintenance-body,
        .warranty-body {
            padding: 16px;
        }
        
        .schedule-table {
            font-size: 12px;
        }
        
        .schedule-table th,
        .schedule-table td {
            padding: 12px 8px;
        }
        
        .ad-container {
            margin: 24px 0;
            padding: 16px;
        }
        
        .warranty-grid {
            grid-template-columns: 1fr;
        }
        
        .revision-number {
            width: 40px;
            height: 40px;
            font-size: 14px;
        }
        
        .critical-alert {
            padding: 16px;
        }
        
        .alert-icon {
            width: 40px;
            height: 40px;
            margin-right: 12px;
            font-size: 16px;
        }
    }
    
    @media (min-width: 768px) {
        .service-grid {
            grid-template-columns: 1fr 1fr;
        }
        
        .warranty-grid {
            grid-template-columns: 1fr 1fr;
        }
    }
    
    @media (max-width: 480px) {
        h1 {
            font-size: 22px;
        }
        
        h2 {
            font-size: 18px;
        }
        
        .revision-header {
            padding: 16px;
        }
        
        .revision-title {
            font-size: 16px;
        }
        
        .revision-number {
            width: 36px;
            height: 36px;
            font-size: 12px;
            right: 16px;
        }
        
        .maintenance-header,
        .warranty-header {
            padding: 12px 16px;
        }
    }
</style>
@endsection

@section('content')
<div class="container">
    <article>
        <!-- Cabeçalho -->
        <h1>{{ $article->title }}</h1>
        <div class="article-meta">
            @if(!empty($article->formated_updated_at))
            <p>📅 Atualizado em: {{ $article->formated_updated_at }}</p>
            @endif
        </div>
        
        <!-- Introdução -->
        @if(!empty($article->introduction))
        <p>{{ $article->introduction }}</p>
        @endif
        
        <!-- 🚗 ANÚNCIO 1: Após introdução -->
        <div class="ad-container">
            <div class="ad-label">Publicidade</div>
            <amp-ad width="100vw" height="320"
                type="adsense"
                data-ad-client="{{ Config::get('services.google_adsense.id') }}"
                data-ad-slot="7414648059"   
                data-auto-format="rspv"
                data-full-width>
                <div overflow></div>
            </amp-ad>
        </div>
        
        <!-- Resumo das Revisões -->
        @if(!empty($article->overview_schedule) && is_array($article->overview_schedule) && count($article->overview_schedule) > 0)
        <h2>🚗 Cronograma de Revisões Programadas</h2>
        
        <div class="schedule-table">
            <div class="schedule-table-header">
                <div class="schedule-table-icon">📋</div>
                <div>
                    <strong>Tabela Resumo de Manutenções</strong>
                    <div style="font-size: 12px; color: #64748b; margin-top: 2px;">Planejamento completo para seu veículo</div>
                </div>
            </div>
            
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th>Revisão</th>
                        <th>Quilometragem / Tempo</th>
                        <th>Principais Serviços</th>
                        <th>Estimativa de Custo*</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($article->overview_schedule as $index => $schedule)
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center;">
                                <span class="table-row-number">{{ $index + 1 }}</span>
                                <strong>{{ $schedule['revisao'] ?? '-' }}</strong>
                            </div>
                        </td>
                        <td>{{ $schedule['intervalo'] ?? '-' }}</td>
                        <td>{{ $schedule['principais_servicos'] ?? '-' }}</td>
                        <td class="table-cost">{{ $schedule['estimativa_custo'] ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div style="background: linear-gradient(135deg, #fef3cd, #fef3cd); padding: 16px; border-top: 1px solid #fde68a; font-size: 12px; color: #92400e;">
                <strong>⚠️ Custos estimados:</strong> Valores de referência em {{ \Carbon\Carbon::now()->locale('pt_BR')->isoFormat('MMMM [de] YYYY') }} para concessionárias em capitais brasileiras. Os valores podem variar conforme a região, inflação e promoções.
            </div>
        </div>
        @endif
        
        <!-- Cronograma Detalhado -->
        @if(!empty($article->detailed_schedule) && is_array($article->detailed_schedule) && count($article->detailed_schedule) > 0)
        <h2>🔧 Detalhamento das Revisões</h2>
        
        @foreach($article->detailed_schedule as $revision)
        <div class="revision-card">
            <div class="revision-header">
                <div>
                    <div class="revision-title">{{ $revision['numero_revisao'] ?? '?' }}ª Revisão</div>
                    <span class="revision-interval">{{ $revision['intervalo'] ?? 'N/A' }}</span>
                </div>
                <div class="revision-number">{{ $revision['km'] ?? '?' }}</div>
            </div>
            <div class="revision-body">
                <div class="service-grid">
                    @if(!empty($revision['servicos_principais']) && is_array($revision['servicos_principais']) && count($revision['servicos_principais']) > 0)
                    <div class="service-section">
                        <h4>
                            <span class="service-icon">🔧</span>
                            Procedimentos Principais
                        </h4>
                        <ul class="service-list">
                            @foreach($revision['servicos_principais'] as $servico)
                            @if(!empty($servico))
                            <li>{{ $servico }}</li>
                            @endif
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    
                    @if(!empty($revision['verificacoes_complementares']) && is_array($revision['verificacoes_complementares']) && count($revision['verificacoes_complementares']) > 0)
                    <div class="service-section">
                        <h4>
                            <span class="service-icon">🔍</span>
                            Verificações Complementares
                        </h4>
                        <ul class="service-list">
                            @foreach($revision['verificacoes_complementares'] as $verificacao)
                            @if(!empty($verificacao))
                            <li>{{ $verificacao }}</li>
                            @endif
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
                
                @if(!empty($revision['observacoes']))
                <div class="revision-note">
                    <div class="revision-note-title">⚠️ Observação Importante</div>
                    {{ $revision['observacoes'] }}
                </div>
                @endif
            </div>
        </div>
        @endforeach
        @endif
        
        <!-- 🚗 ANÚNCIO 2: Após cronograma detalhado -->
        <div class="ad-container">
            <div class="ad-label">Publicidade</div>
            <amp-ad width="100vw" height="320"
                type="adsense"
                data-ad-client="{{ Config::get('services.google_adsense.id') }}"
                data-ad-slot="8344586349"
                data-auto-format="rspv"
                data-full-width>
                <div overflow></div>
            </amp-ad>
        </div>
       
        <!-- Manutenção Preventiva -->
        @if(!empty($article->preventive_maintenance) && is_array($article->preventive_maintenance))
        <h2>🛠️ Manutenção Preventiva Entre Revisões</h2>
        
        @if(!empty($article->preventive_maintenance['verificacoes_mensais']) && is_array($article->preventive_maintenance['verificacoes_mensais']) && count($article->preventive_maintenance['verificacoes_mensais']) > 0)
        <div class="maintenance-card">
            <div class="maintenance-header monthly">
                <div class="maintenance-icon">📅</div>
                Verificações Mensais
            </div>
            <div class="maintenance-body">
                <ul class="maintenance-list monthly">
                    @foreach($article->preventive_maintenance['verificacoes_mensais'] as $item)
                    @if(!empty($item))
                    <li>{{ $item }}</li>
                    @endif
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
        
        @if(!empty($article->preventive_maintenance['verificacoes_trimestrais']) && is_array($article->preventive_maintenance['verificacoes_trimestrais']) && count($article->preventive_maintenance['verificacoes_trimestrais']) > 0)
        <div class="maintenance-card">
            <div class="maintenance-header quarterly">
                <div class="maintenance-icon">📋</div>
                Verificações Trimestrais
            </div>
            <div class="maintenance-body">
                <ul class="maintenance-list quarterly">
                    @foreach($article->preventive_maintenance['verificacoes_trimestrais'] as $item)
                    @if(!empty($item))
                    <li>{{ $item }}</li>
                    @endif
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
        
        @if(!empty($article->preventive_maintenance['verificacoes_anuais']) && is_array($article->preventive_maintenance['verificacoes_anuais']) && count($article->preventive_maintenance['verificacoes_anuais']) > 0)
        <div class="maintenance-card">
            <div class="maintenance-header annual">
                <div class="maintenance-icon">🔧</div>
                Verificações Anuais
            </div>
            <div class="maintenance-body">
                <ul class="maintenance-list annual">
                    @foreach($article->preventive_maintenance['verificacoes_anuais'] as $item)
                    @if(!empty($item))
                    <li>{{ $item }}</li>
                    @endif
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
        @endif
        
        <!-- Peças Críticas -->
        @if(!empty($article->critical_parts) && is_array($article->critical_parts) && count($article->critical_parts) > 0)
        <h2>⚠️ Peças que Exigem Atenção Especial</h2>
        
        @foreach($article->critical_parts as $part)
        @if(!empty($part['componente']))
        <div class="critical-part">
            <div class="critical-part-title">
                <div class="critical-part-icon">⚙️</div>
                {{ $part['componente'] }}
            </div>
            @if(!empty($part['intervalo_recomendado']))
            <div class="critical-part-interval">📅 {{ $part['intervalo_recomendado'] }}</div>
            @endif
            @if(!empty($part['observacao']))
            <div class="critical-part-note">{{ $part['observacao'] }}</div>
            @endif
        </div>
        @endif
        @endforeach
        @endif
        
        <!-- 🚗 ANÚNCIO 3: Após peças críticas -->
        <div class="ad-container">
            <div class="ad-label">Publicidade</div>
            <amp-ad width="100vw" height="320"
                type="adsense"
                data-ad-client="{{ Config::get('services.google_adsense.id') }}"
                data-ad-slot="1402260703"
                data-auto-format="rspv"
                data-full-width>
                <div overflow></div>
            </amp-ad>
        </div>
       
        <!-- Garantia e Recomendações -->
        @if(!empty($article->warranty_info) && is_array($article->warranty_info))
        <h2>🛡️ Garantia e Recomendações Adicionais</h2>
        
        <div class="warranty-grid">
            <div class="warranty-card">
                <div class="warranty-header warranty">
                    <div class="warranty-icon">🛡️</div>
                    Prazo de Garantia
                </div>
                <div class="warranty-body">
                    @if(!empty($article->warranty_info['prazo_garantia']))
                    <div class="warranty-item">
                        <strong>Garantia Contratual:</strong> {{ $article->warranty_info['prazo_garantia'] }}
                    </div>
                    @endif
                    
                    @if(!empty($article->warranty_info['garantia_itens_desgaste']))
                    <div class="warranty-item">
                        <strong>Garantia para Itens de Desgaste:</strong> {{ $article->warranty_info['garantia_itens_desgaste'] }}
                    </div>
                    @endif
                    
                    @if(!empty($article->warranty_info['garantia_anticorrosao']))
                    <div class="warranty-item">
                        <strong>Garantia Anticorrosão:</strong> {{ $article->warranty_info['garantia_anticorrosao'] }}
                    </div>
                    @endif
                    
                    @if(!empty($article->warranty_info['observacoes_importantes']))
                    <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #e5e7eb; font-size: 13px; color: #4a5568;">
                        <strong>💡 Importante:</strong> {{ $article->warranty_info['observacoes_importantes'] }}
                    </div>
                    @endif
                </div>
            </div>
            
            <div class="warranty-card">
                <div class="warranty-header tips">
                    <div class="warranty-icon">💡</div>
                    Dicas para Prolongar a Vida Útil
                </div>
                <div class="warranty-body">
                    @if(!empty($article->warranty_info['dicas_vida_util']) && is_array($article->warranty_info['dicas_vida_util']) && count($article->warranty_info['dicas_vida_util']) > 0)
                    <ul class="warranty-tips">
                        @foreach($article->warranty_info['dicas_vida_util'] as $dica)
                        @if(!empty($dica))
                        <li>{{ $dica }}</li>
                        @endif
                        @endforeach
                    </ul>
                    @else
                    <p style="font-size: 13px; color: #718096; padding: 16px;">Nenhuma dica disponível para este modelo de veículo.</p>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Alerta de Revisões Críticas -->
        @if(!empty($article->vehicle_full_name))
        <div class="critical-alert">
            <div class="alert-icon">⚠️</div>
            <div class="alert-content">
                <h3>🚗 Atenção às Revisões Críticas</h3>
                <p>As revisões de 20.000 km e 60.000 km são consideradas críticas para o {{ $article->vehicle_full_name }}, pois incluem a verificação e/ou substituição de componentes fundamentais para a longevidade do motor.</p>
                <p>A revisão de 60.000 km, em particular, inclui a troca da correia dentada, componente crítico cuja falha pode causar sérios danos ao motor. Não postergue esta revisão e sempre utilize peças originais ou homologadas pela fabricante.</p>
            </div>
        </div>
        @endif
        @endif
        
        <!-- Perguntas Frequentes -->
        @if(!empty($article->faq) && is_array($article->faq) && count($article->faq) > 0)
        <h2>❓ Perguntas Frequentes</h2>
        
        <amp-accordion>
            @foreach($article->faq as $question)
            @if(!empty($question['pergunta']) && !empty($question['resposta']))
            <section>
                <h4>
                    <span class="faq-icon">?</span>
                    {{ $question['pergunta'] }}
                </h4>
                <div class="faq-content">
                    <p>{{ $question['resposta'] }}</p>
                </div>
            </section>
            @endif
            @endforeach
        </amp-accordion>
        @endif
        
        <!-- Considerações Finais -->
        @if(!empty($article->final_considerations))
        <h2>📝 Considerações Finais</h2>
        <p>{{ $article->final_considerations }}</p>
        @endif

        <!-- Nota informativa -->
        @include('auto-info-center::article.partials.info_note_manual')
        
        <!-- Footer do artigo -->
        @include('auto-info-center::article.partials.article_footer')
    </article>
</div>
@endsection