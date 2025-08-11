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
    
    /* Typography */
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
        padding-bottom: 8px;
        border-bottom: 3px solid;
        border-image: linear-gradient(45deg, #0E368A, #10b981) 1;
        color: #151C25;
        position: relative;
    }
    
    h2:after {
        content: '';
        position: absolute;
        bottom: -3px;
        left: 0;
        width: 50px;
        height: 3px;
        background: linear-gradient(45deg, #0E368A, #10b981);
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
        padding: 8px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    
    /* Ad containers */
    .ad-container {
        text-align: center;
        margin: 32px 0;
        padding: 16px;
        background: linear-gradient(135deg, rgba(14, 54, 138, 0.05), rgba(16, 185, 129, 0.05));
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }
    
    .ad-label {
        font-size: 11px;
        color: #999;
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 500;
    }
    
    /* Tabela principal de resumo para híbridos */
    .schedule-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        margin-bottom: 24px;
        background-color: #fff;
        border: 2px solid transparent;
        background-image: linear-gradient(white, white), linear-gradient(45deg, #0E368A, #10b981);
        background-origin: border-box;
        background-clip: content-box, border-box;
    }
    
    .schedule-table th {
        background: linear-gradient(135deg, #0E368A, #10b981);
        color: white;
        text-align: left;
        padding: 10px;
        font-weight: 600;
        font-size: 12px;
    }
    
    .schedule-table td {
        padding: 10px;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: top;
    }
    
    .schedule-table tr:nth-child(even) td {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.05), rgba(14, 54, 138, 0.05));
    }
    
    .schedule-table tr:last-child td {
        border-bottom: none;
    }
    
    /* Cards de revisão detalhada para híbridos */
    .revision-card {
        border: 1px solid #e2e8f0;
        overflow: hidden;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        background-color: #fff;
        border-left: 4px solid;
        border-image: linear-gradient(45deg, #0E368A, #10b981) 1;
    }
    
    .revision-header {
        background: linear-gradient(135deg, #0E368A, #10b981);
        color: white;
        padding: 16px;
        font-weight: 600;
        font-size: 16px;
        position: relative;
    }
    
    .revision-number {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        width: 40px;
        height: 40px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
    }
    
    .revision-body {
        padding: 20px;
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.02), rgba(14, 54, 138, 0.02));
    }
    
    .service-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 16px;
        margin-bottom: 16px;
    }
    
    .service-section h4 {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 8px;
        color: #151C25;
    }
    
    .service-list {
        margin: 0;
        padding: 0;
        list-style: none;
    }
    
    .service-list li {
        margin-bottom: 6px;
        color: #4a5568;
        position: relative;
        padding-left: 20px;
        font-size: 13px;
    }
    
    .service-list li:before {
        content: '⚡';
        position: absolute;
        left: 0;
        font-weight: bold;
        color: #10b981;
    }
    
    .revision-note {
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.1), rgba(255, 193, 7, 0.05));
        padding: 12px;
        border-radius: 6px;
        border-left: 4px solid #ffc107;
        font-size: 13px;
        color: #4a5568;
        margin-top: 16px;
    }
    
    /* Cards de manutenção preventiva para híbridos */
    .maintenance-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 16px;
        background-color: #fff;
        border-left: 4px solid #10b981;
        box-shadow: 0 2px 4px rgba(16, 185, 129, 0.1);
    }
    
    .maintenance-header {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        padding: 12px 16px;
        font-weight: 600;
        font-size: 14px;
        display: flex;
        align-items: center;
    }
    
    .maintenance-icon {
        width: 20px;
        height: 20px;
        margin-right: 8px;
    }
    
    .maintenance-body {
        padding: 16px;
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.05), rgba(16, 185, 129, 0.02));
    }
    
    .maintenance-list {
        margin: 0;
        padding: 0;
        list-style: none;
    }
    
    .maintenance-list li {
        margin-bottom: 8px;
        color: #4a5568;
        position: relative;
        padding-left: 24px;
        font-size: 13px;
    }
    
    .maintenance-list li:before {
        content: '🔄';
        position: absolute;
        left: 0;
        font-weight: bold;
    }
    
    /* Cards de peças críticas para híbridos */
    .critical-part {
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.1), rgba(255, 152, 0, 0.1));
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 16px;
        border-left: 4px solid #ff9800;
        box-shadow: 0 2px 4px rgba(255, 152, 0, 0.1);
        border: 1px solid rgba(255, 152, 0, 0.2);
    }
    
    .critical-part-title {
        font-weight: 600;
        margin-bottom: 8px;
        color: #151C25;
        font-size: 15px;
        display: flex;
        align-items: center;
    }
    
    .critical-part-title:before {
        content: '🔋';
        margin-right: 8px;
        font-size: 16px;
    }
    
    .critical-part-interval {
        font-size: 13px;
        color: #ff9800;
        font-weight: 500;
        margin-bottom: 8px;
    }
    
    .critical-part-note {
        font-size: 13px;
        color: #4a5568;
        line-height: 1.5;
    }
    
    /* Seção de garantia para híbridos */
    .warranty-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 16px;
        margin-bottom: 20px;
    }
    
    .warranty-card {
        background: linear-gradient(135deg, rgba(14, 54, 138, 0.05), rgba(16, 185, 129, 0.05));
        padding: 16px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        border-left: 4px solid #0E368A;
    }
    
    .warranty-title {
        font-weight: 600;
        margin-bottom: 12px;
        color: #151C25;
        font-size: 16px;
        display: flex;
        align-items: center;
    }
    
    .warranty-icon {
        width: 20px;
        height: 20px;
        margin-right: 8px;
        color: #0E368A;
    }
    
    .warranty-item {
        background-color: #fff;
        padding: 10px;
        margin-bottom: 8px;
        border-radius: 4px;
        font-size: 13px;
        border-left: 3px solid #0E368A;
    }
    
    .warranty-item.hybrid-special {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.05));
        border-left: 3px solid #10b981;
    }
    
    .warranty-tips {
        margin: 0;
        padding: 0;
        list-style: none;
    }
    
    .warranty-tips li {
        margin-bottom: 6px;
        color: #4a5568;
        position: relative;
        padding-left: 24px;
        font-size: 13px;
    }
    
    .warranty-tips li:before {
        content: '⚡';
        position: absolute;
        left: 0;
        font-weight: bold;
        color: #10b981;
    }
    
    /* Alerta especial para híbridos */
    .critical-alert {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(14, 54, 138, 0.1));
        border: 1px solid rgba(16, 185, 129, 0.2);
        border-radius: 8px;
        padding: 16px;
        margin: 24px 0;
        display: flex;
        align-items: flex-start;
        border-left: 4px solid #10b981;
    }
    
    .alert-icon {
        width: 24px;
        height: 24px;
        color: #10b981;
        margin-right: 12px;
        flex-shrink: 0;
        margin-top: 2px;
    }
    
    .alert-content h3 {
        font-weight: 600;
        color: #0E368A;
        margin-bottom: 8px;
        font-size: 16px;
    }
    
    .alert-content p {
        color: #4a5568;
        font-size: 14px;
        line-height: 1.5;
        margin-bottom: 8px;
    }
    
    /* FAQs com accordion para híbridos */
    amp-accordion {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 24px;
        border-left: 4px solid #10b981;
    }
    
    amp-accordion section {
        border-bottom: 1px solid #e2e8f0;
    }
    
    amp-accordion section:last-child {
        border-bottom: none;
    }
    
    amp-accordion h4 {
        font-size: 15px;
        padding: 16px;
        margin: 0;
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.05), rgba(14, 54, 138, 0.05));
        font-weight: 600;
        color: #151C25;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }
    
    amp-accordion h4:hover {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(14, 54, 138, 0.1));
    }
    
    amp-accordion .faq-content {
        padding: 16px;
        background-color: #fff;
        color: #4a5568;
        line-height: 1.6;
        font-size: 14px;
    }

    /* Nota Manual */
    .info-note-manual {
        background: linear-gradient(135deg, rgba(220, 38, 38, 0.05), rgba(220, 38, 38, 0.1));
        padding: 16px;
        border-radius: 8px;
        font-size: 14px;
        margin: 24px 0;
        border-left: 4px solid #DC2626;
        color: #991b1b;
    }
    
    /* Footer */
    .article-footer {
        font-size: 12px;
        color: #718096;
        margin-top: 32px;
        padding-top: 16px;
        border-top: 1px solid #e2e8f0;
        background: linear-gradient(135deg, rgba(14, 54, 138, 0.05), rgba(16, 185, 129, 0.05));
        padding: 16px;
        border-radius: 8px;
    }
    
    .article-footer p {
        margin-bottom: 4px;
    }
    
    .article-footer a {
        color: #0E368A;
        text-decoration: none;
    }
    
    .article-footer a:hover {
        text-decoration: underline;
    }
    
    /* Responsivo */
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
        .maintenance-body {
            padding: 16px;
        }
        
        .schedule-table {
            font-size: 12px;
        }
        
        .schedule-table th,
        .schedule-table td {
            padding: 8px;
        }
        
        .ad-container {
            margin: 24px 0;
            padding: 12px;
        }
        
        .warranty-grid {
            grid-template-columns: 1fr;
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
            font-size: 14px;
        }
        
        .revision-number {
            width: 32px;
            height: 32px;
            font-size: 12px;
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
            <p>Atualizado em: {{ $article->formated_updated_at }}</p>
            @endif
        </div>
        
        <!-- Introdução -->
        @if(!empty($article->introduction))
        <p>{{ $article->introduction }}</p>
        @endif
        
        <!-- 🔄 ANÚNCIO 1: Após introdução -->
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
        <h2>🔄 Cronograma de Revisões para Veículos Híbridos</h2>
        
        <table class="schedule-table">
            <thead>
                <tr>
                    <th>Revisão</th>
                    <th>Quilometragem / Tempo</th>
                    <th>Principais Serviços</th>
                    <th>Estimativa de Custo*</th>
                </tr>
            </thead>
            <tbody>
                @foreach($article->overview_schedule as $schedule)
                <tr>
                    <td><strong>{{ $schedule['revisao'] ?? '-' }}</strong></td>
                    <td>{{ $schedule['intervalo'] ?? '-' }}</td>
                    <td>{{ $schedule['principais_servicos'] ?? '-' }}</td>
                    <td>{{ $schedule['estimativa_custo'] ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(14, 54, 138, 0.1)); padding: 12px; border-radius: 6px; border: 1px solid rgba(16, 185, 129, 0.2); font-size: 12px; color: #4a5568; margin-bottom: 24px; border-left: 4px solid #10b981;">
            <strong>*Custos estimados para veículos híbridos:</strong> Valores de referência em {{ \Carbon\Carbon::now()->locale('pt_BR')->isoFormat('MMMM [de] YYYY') }} para concessionárias especializadas em capitais brasileiras. Veículos híbridos podem ter custos ligeiramente superiores devido à complexidade dos sistemas duais.
        </div>
        @endif
        
        <!-- Cronograma Detalhado -->
        @if(!empty($article->detailed_schedule) && is_array($article->detailed_schedule) && count($article->detailed_schedule) > 0)
        <h2>🔧 Detalhamento das Revisões Híbridas</h2>
        
        @foreach($article->detailed_schedule as $revision)
        <div class="revision-card">
            <div class="revision-header">
                🔄 {{ $revision['numero_revisao'] ?? '?' }}ª Revisão ({{ $revision['intervalo'] ?? 'N/A' }})
                <div class="revision-number">{{ $revision['km'] ?? '?' }}</div>
            </div>
            <div class="revision-body">
                <div class="service-grid">
                    @if(!empty($revision['servicos_principais']) && is_array($revision['servicos_principais']) && count($revision['servicos_principais']) > 0)
                    <div class="service-section">
                        <h4>⚡ Procedimentos Híbridos Principais:</h4>
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
                        <h4>🔍 Verificações do Sistema Dual:</h4>
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
                    <strong>⚠️ Importante para Híbridos:</strong> {{ $revision['observacoes'] }}
                </div>
                @endif
            </div>
        </div>
        @endforeach
        @endif
        
        <!-- 🔄 ANÚNCIO 2: Após cronograma detalhado -->
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
        <h2>🔧 Manutenção Preventiva Especial para Híbridos</h2>
        
        @if(!empty($article->preventive_maintenance['verificacoes_mensais']) && is_array($article->preventive_maintenance['verificacoes_mensais']) && count($article->preventive_maintenance['verificacoes_mensais']) > 0)
        <div class="maintenance-card">
            <div class="maintenance-header">
                <span class="maintenance-icon">📅</span>
                Verificações Mensais
            </div>
            <div class="maintenance-body">
                <ul class="maintenance-list">
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
            <div class="maintenance-header">
                <span class="maintenance-icon">🔄</span>
                Verificações Trimestrais
            </div>
            <div class="maintenance-body">
                <ul class="maintenance-list">
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
            <div class="maintenance-header">
                <span class="maintenance-icon">⚡</span>
                Verificações Anuais
            </div>
            <div class="maintenance-body">
                <ul class="maintenance-list">
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
        
        <!-- Peças Críticas para Híbridos -->
        @if(!empty($article->critical_parts) && is_array($article->critical_parts) && count($article->critical_parts) > 0)
        <h2>⚠️ Componentes Críticos em Veículos Híbridos</h2>
        
        @foreach($article->critical_parts as $part)
        @if(!empty($part['componente']))
        <div class="critical-part">
            <div class="critical-part-title">{{ $part['componente'] }}</div>
            @if(!empty($part['intervalo_recomendado']))
            <div class="critical-part-interval">🔧 Recomendação: {{ $part['intervalo_recomendado'] }}</div>
            @endif
            @if(!empty($part['observacao']))
            <div class="critical-part-note">{{ $part['observacao'] }}</div>
            @endif
        </div>
        @endif
        @endforeach
        @endif
        
        <!-- 🔄 ANÚNCIO 3: Após peças críticas -->
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
        <h2>🛡️ Garantia e Cuidados Especiais para Híbridos</h2>
        
        <div class="warranty-grid">
            <div class="warranty-card">
                <div class="warranty-title">
                    <span class="warranty-icon">🛡️</span>
                    Garantias Híbridas
                </div>
                
                @if(!empty($article->warranty_info['prazo_garantia']))
                <div class="warranty-item">
                    <strong>Garantia Contratual:</strong> {{ $article->warranty_info['prazo_garantia'] }}
                </div>
                @endif
                
                @if(!empty($article->warranty_info['garantia_bateria_hibrida']))
                <div class="warranty-item hybrid-special">
                    <strong>🔋 Garantia da Bateria Híbrida:</strong> {{ $article->warranty_info['garantia_bateria_hibrida'] }}
                </div>
                @endif
                
                @if(!empty($article->warranty_info['garantia_sistemas_hibridos']))
                <div class="warranty-item hybrid-special">
                    <strong>⚡ Garantia dos Sistemas Híbridos:</strong> {{ $article->warranty_info['garantia_sistemas_hibridos'] }}
                </div>
                @endif
                
                @if(!empty($article->warranty_info['garantia_itens_desgaste']))
                <div class="warranty-item">
                    <strong>Garantia para Itens de Desgaste:</strong> {{ $article->warranty_info['garantia_itens_desgaste'] }}
                </div>
                @endif
                
                @if(!empty($article->warranty_info['observacoes_importantes']))
                <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e2e8f0; font-size: 13px; color: #4a5568;">
                    <strong>💡 Importante:</strong> {{ $article->warranty_info['observacoes_importantes'] }}
                </div>
                @endif
            </div>
            
            <div class="warranty-card">
                <div class="warranty-title">
                    <span class="warranty-icon">🔄</span>
                    Dicas para Híbridos
                </div>
                
                @if(!empty($article->warranty_info['dicas_vida_util']) && is_array($article->warranty_info['dicas_vida_util']) && count($article->warranty_info['dicas_vida_util']) > 0)
                <ul class="warranty-tips">
                    @foreach($article->warranty_info['dicas_vida_util'] as $dica)
                    @if(!empty($dica))
                    <li>{{ $dica }}</li>
                    @endif
                    @endforeach
                </ul>
                @else
                <p style="font-size: 13px; color: #718096; padding: 16px;">Nenhuma dica disponível para este modelo híbrido.</p>
                @endif
            </div>
        </div>
        
        <!-- Alerta de Revisões Críticas para Híbridos -->
        @if(!empty($article->vehicle_full_name))
        <div class="critical-alert">
            <span class="alert-icon">🔋</span>
            <div class="alert-content">
                <h3>⚡ Sistemas Híbridos Requerem Cuidado Especial</h3>
                <p>O {{ $article->vehicle_full_name }} possui sistemas de alta tensão que exigem manutenção especializada. A bateria híbrida e os sistemas de gerenciamento de energia devem ser inspecionados apenas por técnicos qualificados.</p>
                <p><strong>💡 Importante:</strong> Nunca tente realizar manutenção nos componentes de alta tensão por conta própria. Sempre procure oficinas especializadas em veículos híbridos para garantir sua segurança e a integridade do sistema.</p>
            </div>
        </div>
        @endif
        @endif
        
        <!-- Perguntas Frequentes -->
        @if(!empty($article->faq) && is_array($article->faq) && count($article->faq) > 0)
        <h2>❓ Perguntas Frequentes sobre Híbridos</h2>
        
        <amp-accordion>
            @foreach($article->faq as $question)
            @if(!empty($question['pergunta']) && !empty($question['resposta']))
            <section>
                <h4>🔋 {{ $question['pergunta'] }}</h4>
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
        <h2>🌱 Considerações Finais</h2>
        <p>{{ $article->final_considerations }}</p>
        @endif

        <!-- Nota informativa -->
        @include('auto-info-center::article.partials.info_note_manual')
        
        <!-- Footer do artigo -->
        @include('auto-info-center::article.partials.article_footer')
    </article>
</div>
@endsection