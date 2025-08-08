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
        border-bottom: 2px solid #0E368A;
        color: #151C25;
        position: relative;
    }
    
    h2:after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 50px;
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
        padding: 8px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    
    /* Ad containers */
    .ad-container {
        text-align: center;
        margin: 32px 0;
        padding: 16px;
        background-color: #f8fafc;
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
    
    /* Especificações dos Pneus */
    .tire-specs-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        background-color: #fff;
    }
    
    .tire-specs-header {
        background: linear-gradient(135deg, #0E368A, #1a4da8);
        color: white;
        padding: 16px;
        font-weight: 600;
        font-size: 16px;
    }
    
    .tire-specs-body {
        padding: 20px;
    }
    
    .spec-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        padding: 8px 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 14px;
    }
    
    .spec-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }
    
    .spec-label {
        color: #718096;
        font-weight: 500;
    }
    
    .spec-value {
        font-weight: 600;
        color: #151C25;
        background-color: #f8fafc;
        padding: 4px 8px;
        border-radius: 4px;
    }
    
    /* Tabela de pressões */
    .pressure-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        margin-bottom: 24px;
    }
    
    .pressure-table th {
        background: linear-gradient(135deg, #0E368A, #1a4da8);
        color: white;
        text-align: left;
        padding: 12px;
        font-weight: 600;
        font-size: 13px;
    }
    
    .pressure-table td {
        padding: 12px;
        border-bottom: 1px solid #e2e8f0;
        background-color: #fff;
    }
    
    .pressure-table tr:nth-child(even) td {
        background-color: #f8fafc;
    }
    
    .pressure-table tr:last-child td {
        border-bottom: none;
    }
    
    /* Cards de recomendação */
    .recommendation-card {
        background-color: #f8fafc;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid #e2e8f0;
        position: relative;
    }
    
    .recommendation-title {
        font-weight: 600;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        color: #151C25;
        font-size: 16px;
    }
    
    .recommendation-icon {
        width: 24px;
        height: 24px;
        margin-right: 10px;
        flex-shrink: 0;
    }
    
    /* Sistema TPMS */
    .tpms-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        background-color: #fff;
    }
    
    .tpms-header {
        background: linear-gradient(135deg, #059669, #10b981);
        color: white;
        padding: 16px;
        font-weight: 600;
        font-size: 16px;
    }
    
    .tpms-body {
        padding: 20px;
    }
    
    .tpms-feature {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
        padding: 8px 0;
    }
    
    .tpms-feature:last-child {
        margin-bottom: 0;
    }
    
    .tpms-bullet {
        width: 8px;
        height: 8px;
        background-color: #059669;
        border-radius: 50%;
        margin-top: 6px;
        margin-right: 12px;
        flex-shrink: 0;
    }
    
    /* Comparativo de impacto */
    .impact-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }
    
    .impact-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        background-color: #fff;
        text-align: center;
    }
    
    .impact-card.subcalibrado {
        border-left: 4px solid #ef4444;
    }
    
    .impact-card.ideal {
        border-left: 4px solid #10b981;
    }
    
    .impact-card.sobrecalibrado {
        border-left: 4px solid #f59e0b;
    }
    
    .impact-title {
        font-weight: 600;
        margin-bottom: 16px;
        font-size: 16px;
    }
    
    .impact-metric {
        margin-bottom: 12px;
    }
    
    .impact-metric-label {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 4px;
    }
    
    .impact-bar {
        width: 100%;
        height: 6px;
        background-color: #e5e7eb;
        border-radius: 3px;
        overflow: hidden;
    }
    
    .impact-bar-fill {
        height: 100%;
        border-radius: 3px;
        transition: width 0.3s ease;
    }
    
    .impact-bar-fill.red { background-color: #ef4444; }
    .impact-bar-fill.green { background-color: #10b981; }
    .impact-bar-fill.yellow { background-color: #f59e0b; }
    
    /* Procedimento de calibragem */
    .procedure-step {
        display: flex;
        margin-bottom: 24px;
        align-items: flex-start;
    }
    
    .step-number {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #0E368A, #1a4da8);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 16px;
        flex-shrink: 0;
        font-weight: 700;
        font-size: 16px;
    }
    
    .step-content {
        flex: 1;
    }
    
    .step-content h3 {
        margin: 0 0 8px;
        font-size: 16px;
        font-weight: 600;
        color: #151C25;
    }
    
    .step-content p {
        margin-bottom: 8px;
        color: #4a5568;
        line-height: 1.6;
    }
    
    .step-tips {
        background-color: #eff6ff;
        border-left: 4px solid #3b82f6;
        padding: 12px;
        margin-top: 12px;
        border-radius: 4px;
    }
    
    .step-tips ul {
        margin: 0;
        padding-left: 16px;
    }
    
    .step-tips li {
        font-size: 14px;
        color: #1e40af;
        margin-bottom: 4px;
    }
    
    /* Calculadora de economia */
    .economy-calculator {
        background: linear-gradient(135deg, #10b981, #059669);
        border-radius: 12px;
        padding: 24px;
        color: white;
        margin-bottom: 24px;
    }
    
    .calculator-title {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 16px;
        text-align: center;
    }
    
    .calculator-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 16px;
        margin-bottom: 16px;
    }
    
    .calculator-item {
        text-align: center;
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        padding: 16px;
    }
    
    .calculator-value {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 4px;
    }
    
    .calculator-label {
        font-size: 12px;
        opacity: 0.9;
    }
    
    /* FAQs com accordion */
    amp-accordion {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 24px;
    }
    
    amp-accordion section {
        border-bottom: 1px solid #e2e8f0;
    }
    
    amp-accordion section:last-child {
        border-bottom: none;
    }
    
    amp-accordion h4 {
        font-size: 16px;
        padding: 16px;
        margin: 0;
        background-color: #f8fafc;
        font-weight: 600;
        color: #151C25;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }
    
    amp-accordion h4:hover {
        background-color: #e2e8f0;
    }
    
    amp-accordion .faq-content {
        padding: 16px;
        background-color: #fff;
        color: #4a5568;
        line-height: 1.6;
    }
    
    /* Alertas de segurança */
    .safety-alert {
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 20px;
        border-left: 4px solid;
    }
    
    .safety-alert.critical {
        background-color: #fef2f2;
        border-color: #ef4444;
    }
    
    .safety-alert.warning {
        background-color: #fffbeb;
        border-color: #f59e0b;
    }
    
    .safety-alert.info {
        background-color: #eff6ff;
        border-color: #3b82f6;
    }
    
    .alert-title {
        font-weight: 600;
        margin-bottom: 8px;
        color: #151C25;
        font-size: 16px;
    }
    
    /* Nota informativa */
    .info-note {
        background: linear-gradient(135deg, rgba(14, 54, 138, 0.05), rgba(14, 54, 138, 0.1));
        padding: 16px;
        border-radius: 8px;
        font-size: 14px;
        margin: 24px 0;
        border-left: 4px solid #0E368A;
        color: #1e40af;
    }
    
    /* Footer */
    .article-footer {
        font-size: 12px;
        color: #718096;
        margin-top: 32px;
        padding-top: 16px;
        border-top: 1px solid #e2e8f0;
        background-color: #f8fafc;
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
        
        .tire-specs-body,
        .recommendation-card {
            padding: 16px;
        }
        
        .pressure-table {
            font-size: 13px;
        }
        
        .pressure-table th,
        .pressure-table td {
            padding: 8px;
        }
        
        .step-number {
            width: 32px;
            height: 32px;
            font-size: 14px;
        }
        
        .impact-grid {
            grid-template-columns: 1fr;
        }
        
        .calculator-grid {
            grid-template-columns: 1fr 1fr;
        }
        
        .ad-container {
            margin: 24px 0;
            padding: 12px;
        }
    }
    
    @media (max-width: 480px) {
        h1 {
            font-size: 22px;
        }
        
        h2 {
            font-size: 18px;
        }
        
        .spec-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
        }
        
        .spec-value {
            align-self: flex-end;
        }
        
        .calculator-grid {
            grid-template-columns: 1fr;
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
        
        <!-- 🥇 ANÚNCIO 1: Após introdução -->
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
        
        <!-- Especificações dos Pneus -->
        @if(!empty($article->tire_specifications) && !empty($article->tire_specifications['versions']))
        <h2>📋 Especificações dos Pneus Originais</h2>
        
        @foreach($article->tire_specifications['versions'] as $version)
        <div class="tire-specs-card">
            <div class="tire-specs-header">🚗 {{ $version['name'] }}</div>
            <div class="tire-specs-body">
                @if(!empty($version['size']))
                <div class="spec-item">
                    <span class="spec-label">Medida:</span>
                    <span class="spec-value">{{ $version['size'] }}</span>
                </div>
                @endif
                @if(!empty($version['type']))
                <div class="spec-item">
                    <span class="spec-label">Tipo:</span>
                    <span class="spec-value">{{ $version['type'] }}</span>
                </div>
                @endif
                @if(!empty($version['brand']))
                <div class="spec-item">
                    <span class="spec-label">Marca Original:</span>
                    <span class="spec-value">{{ $version['brand'] }}</span>
                </div>
                @endif
                @if(!empty($version['load_index']))
                <div class="spec-item">
                    <span class="spec-label">Índice de Carga:</span>
                    <span class="spec-value">{{ $version['load_index'] }}</span>
                </div>
                @endif
                @if(!empty($version['speed_rating']))
                <div class="spec-item">
                    <span class="spec-label">Índice de Velocidade:</span>
                    <span class="spec-value">{{ $version['speed_rating'] }}</span>
                </div>
                @endif
            </div>
        </div>
        @endforeach
        
        <!-- Estepe -->
        @if(!empty($article->tire_specifications['spare_tire']))
        <div class="tire-specs-card">
            <div class="tire-specs-header">🔧 Pneu Estepe</div>
            <div class="tire-specs-body">
                @php $spare = $article->tire_specifications['spare_tire'] @endphp
                @if(!empty($spare['size']))
                <div class="spec-item">
                    <span class="spec-label">Medida:</span>
                    <span class="spec-value">{{ $spare['size'] }}</span>
                </div>
                @endif
                @if(!empty($spare['pressure']))
                <div class="spec-item">
                    <span class="spec-label">Pressão:</span>
                    <span class="spec-value">{{ $spare['pressure'] }}</span>
                </div>
                @endif
                @if(!empty($spare['max_speed']))
                <div class="spec-item">
                    <span class="spec-label">Velocidade Máxima:</span>
                    <span class="spec-value">{{ $spare['max_speed'] }}</span>
                </div>
                @endif
                @if(!empty($spare['max_distance']))
                <div class="spec-item">
                    <span class="spec-label">Distância Máxima:</span>
                    <span class="spec-value">{{ $spare['max_distance'] }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif
        @endif
        
        <!-- Tabela de Pressões -->
        @if(!empty($article->pressure_table))
        <h2>⚖️ Pressões Recomendadas pela {{ $article->vehicle_info['make'] ?? 'Montadora' }}</h2>
        
        <table class="pressure-table">
            <tr>
                <th>Condição de Uso</th>
                <th>Ocupantes</th>
                <th>Dianteiros (PSI)</th>
                <th>Traseiros (PSI)</th>
                <th>Observação</th>
            </tr>
            @foreach($article->pressure_table as $condition)
            <tr>
                <td><strong>{{ $condition['condition'] }}</strong></td>
                <td>{{ $condition['occupants'] ?? '' }}</td>
                <td class="spec-value">{{ str_replace(' PSI', '', str_replace(' (2.2 bar)', '', $condition['front_pressure'])) }}</td>
                <td class="spec-value">{{ str_replace(' PSI', '', str_replace(' (2.2 bar)', '', $condition['rear_pressure'])) }}</td>
                <td>{{ $condition['observation'] ?? '' }}</td>
            </tr>
            @endforeach
        </table>
        @endif
        
        <!-- Sistema TPMS -->
        @if(!empty($article->tpms_system) && $article->tpms_system['has_tpms'])
        <h2>📡 Sistema TPMS (Monitoramento de Pressão)</h2>
        
        <div class="tpms-card">
            <div class="tpms-header">✅ {{ $article->tpms_system['type'] ?? 'Sistema TPMS Ativo' }}</div>
            <div class="tpms-body">
                <h3>Como Funciona:</h3>
                @if(!empty($article->tpms_system['features']))
                @foreach($article->tpms_system['features'] as $feature)
                <div class="tpms-feature">
                    <div class="tpms-bullet"></div>
                    <p>{{ $feature }}</p>
                </div>
                @endforeach
                @endif
                
                @if(!empty($article->tpms_system['reset_procedure']))
                <h3>Procedimento de Reset:</h3>
                @foreach($article->tpms_system['reset_procedure'] as $index => $step)
                <div class="tpms-feature">
                    <div class="tpms-bullet"></div>
                    <p><strong>{{ $index + 1 }}.</strong> {{ $step }}</p>
                </div>
                @endforeach
                @endif
            </div>
        </div>
        @endif
        
        <!-- 🥇 ANÚNCIO 2: Após TPMS -->
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
        
        <!-- Recomendações de Uso -->
        @if(!empty($article->usage_recommendations))
        <h2>💡 Recomendações por Versão e Uso</h2>
        
        @foreach($article->usage_recommendations as $recommendation)
        <div class="recommendation-card">
            <div class="recommendation-title">
                <amp-img class="recommendation-icon" 
                    src="https://mercadoveiculos.s3.us-east-1.amazonaws.com/statics/icons/icon-{{ $recommendation['icon_class'] ?? 'info' }}.svg" 
                    width="24" height="24" alt="{{ $recommendation['category'] }}"></amp-img>
                {{ $recommendation['category'] }}
            </div>
            <p><strong>Pressão:</strong> {{ $recommendation['recommended_pressure'] ?? 'Conforme tabela' }}</p>
            <p>{{ $recommendation['description'] ?? '' }}</p>
            @if(!empty($recommendation['technical_tip']))
            <div class="info-note">
                <strong>💡 Dica técnica:</strong> {{ $recommendation['technical_tip'] }}
            </div>
            @endif
        </div>
        @endforeach
        @endif
        
        <!-- Comparativo de Impacto -->
        @if(!empty($article->impact_comparison))
        <h2>📊 Impacto da Calibragem no Desempenho</h2>
        
        <div class="impact-grid">
            @php $comparison = $article->impact_comparison @endphp
            
            <!-- Subcalibrado -->
            @if(!empty($comparison['under_inflated']))
            <div class="impact-card subcalibrado">
                <h3 class="impact-title">Subcalibrado (-20%)</h3>
                <div class="impact-metric">
                    <div class="impact-metric-label">Estabilidade</div>
                    <div class="impact-bar">
                        <div class="impact-bar-fill red" style="width: {{ max(0, 100 + ($comparison['under_inflated']['stability'] ?? 0)) }}%"></div>
                    </div>
                </div>
                <div class="impact-metric">
                    <div class="impact-metric-label">Frenagem</div>
                    <div class="impact-bar">
                        <div class="impact-bar-fill red" style="width: {{ max(0, 100 + ($comparison['under_inflated']['braking'] ?? 0)) }}%"></div>
                    </div>
                </div>
                <div class="impact-metric">
                    <div class="impact-metric-label">Consumo</div>
                    <div class="impact-bar">
                        <div class="impact-bar-fill red" style="width: {{ ($comparison['under_inflated']['consumption'] ?? 0) * 10 }}%"></div>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Calibragem Ideal -->
            @if(!empty($comparison['ideal']))
            <div class="impact-card ideal">
                <h3 class="impact-title">Calibragem Ideal</h3>
                <div class="impact-metric">
                    <div class="impact-metric-label">Estabilidade</div>
                    <div class="impact-bar">
                        <div class="impact-bar-fill green" style="width: 100%"></div>
                    </div>
                </div>
                <div class="impact-metric">
                    <div class="impact-metric-label">Frenagem</div>
                    <div class="impact-bar">
                        <div class="impact-bar-fill green" style="width: 100%"></div>
                    </div>
                </div>
                <div class="impact-metric">
                    <div class="impact-metric-label">Consumo</div>
                    <div class="impact-bar">
                        <div class="impact-bar-fill green" style="width: 95%"></div>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Sobrecalibrado -->
            @if(!empty($comparison['over_inflated']))
            <div class="impact-card sobrecalibrado">
                <h3 class="impact-title">Sobrecalibrado (+20%)</h3>
                <div class="impact-metric">
                    <div class="impact-metric-label">Estabilidade</div>
                    <div class="impact-bar">
                        <div class="impact-bar-fill yellow" style="width: {{ max(0, 100 + ($comparison['over_inflated']['stability'] ?? 0)) }}%"></div>
                    </div>
                </div>
                <div class="impact-metric">
                    <div class="impact-metric-label">Frenagem</div>
                    <div class="impact-bar">
                        <div class="impact-bar-fill yellow" style="width: {{ max(0, 100 + ($comparison['over_inflated']['braking'] ?? 0)) }}%"></div>
                    </div>
                </div>
                <div class="impact-metric">
                    <div class="impact-metric-label">Conforto</div>
                    <div class="impact-bar">
                        <div class="impact-bar-fill yellow" style="width: 40%"></div>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endif
        
        <!-- Calculadora de Economia -->
        <div class="economy-calculator">
            <h3 class="calculator-title">💰 Calculadora de Economia</h3>
            <p style="text-align: center; margin-bottom: 20px;">Com calibragem correta, você pode economizar:</p>
            <div class="calculator-grid">
                <div class="calculator-item">
                    <div class="calculator-value">R$ 1.200</div>
                    <div class="calculator-label">Economia anual em combustível</div>
                </div>
                <div class="calculator-item">
                    <div class="calculator-value">30%</div>
                    <div class="calculator-label">Aumento da vida útil dos pneus</div>
               </div>
               <div class="calculator-item">
                   <div class="calculator-value">15%</div>
                   <div class="calculator-label">Melhoria na frenagem</div>
               </div>
               <div class="calculator-item">
                   <div class="calculator-value">10%</div>
                   <div class="calculator-label">Redução no consumo</div>
               </div>
           </div>
           <p style="text-align: center; font-size: 14px; opacity: 0.9;">
               *Valores baseados em uso médio de 15.000 km/ano
           </p>
       </div>
       
       <!-- Procedimento de Calibragem -->
       @if(!empty($article->calibration_procedure))
       <h2>🔧 Como Calibrar Corretamente</h2>
       
       @foreach($article->calibration_procedure as $step)
       <div class="procedure-step">
           <div class="step-number">{{ $step['number'] }}</div>
           <div class="step-content">
               <h3>{{ $step['title'] }}</h3>
               <p>{{ $step['description'] }}</p>
               @if(!empty($step['tips']))
               <div class="step-tips">
                   <ul>
                       @foreach($step['tips'] as $tip)
                       <li>{{ $tip }}</li>
                       @endforeach
                   </ul>
               </div>
               @endif
           </div>
       </div>
       @endforeach
       @endif
       
       <!-- 🥈 ANÚNCIO 3: Após procedimento -->
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
       
       <!-- Alertas de Segurança -->
       @if(!empty($article->safety_alerts))
       <h2>⚠️ Alertas de Segurança</h2>
       
       @foreach($article->safety_alerts as $alert)
       <div class="safety-alert {{ strtolower($alert['type']) }}">
           <div class="alert-title">
               @switch(strtolower($alert['type']))
                   @case('crítico')
                       🚨 {{ $alert['title'] }}
                       @break
                   @case('importante')
                       ⚠️ {{ $alert['title'] }}
                       @break
                   @default
                       ℹ️ {{ $alert['title'] }}
               @endswitch
           </div>
           <p>{{ $alert['description'] ?? '' }}</p>
           @if(!empty($alert['consequences']))
           <p><strong>Consequências:</strong> {{ $alert['consequences'] }}</p>
           @endif
           @if(!empty($alert['immediate_action']))
           <p><strong>Ação imediata:</strong> {{ $alert['immediate_action'] }}</p>
           @endif
       </div>
       @endforeach
       @endif
       
       <!-- Impactos da Calibragem -->
       @if(!empty($article->calibration_impacts))
       <h2>🎯 Identificando Problemas na Calibragem</h2>
       
       @if(!empty($article->calibration_impacts['under_inflated']))
       <div class="safety-alert critical">
           <div class="alert-title">🔻 Pressão Baixa (Subcalibrado)</div>
           @php $underInflated = $article->calibration_impacts['under_inflated'] @endphp
           @if($underInflated['fuel_consumption'])
           <p><strong>Consumo:</strong> {{ $underInflated['fuel_consumption'] }}</p>
           @endif
           @if($underInflated['wear_pattern'])
           <p><strong>Desgaste:</strong> {{ $underInflated['wear_pattern'] }}</p>
           @endif
           @if($underInflated['handling'])
           <p><strong>Dirigibilidade:</strong> {{ $underInflated['handling'] }}</p>
           @endif
           @if($underInflated['temperature'])
           <p><strong>Temperatura:</strong> {{ $underInflated['temperature'] }}</p>
           @endif
       </div>
       @endif
       
       @if(!empty($article->calibration_impacts['over_inflated']))
       <div class="safety-alert warning">
           <div class="alert-title">🔺 Pressão Alta (Sobrecalibrado)</div>
           @php $overInflated = $article->calibration_impacts['over_inflated'] @endphp
           @if($overInflated['fuel_consumption'])
           <p><strong>Consumo:</strong> {{ $overInflated['fuel_consumption'] }}</p>
           @endif
           @if($overInflated['wear_pattern'])
           <p><strong>Desgaste:</strong> {{ $overInflated['wear_pattern'] }}</p>
           @endif
           @if($overInflated['handling'])
           <p><strong>Dirigibilidade:</strong> {{ $overInflated['handling'] }}</p>
           @endif
           @if($overInflated['comfort'])
           <p><strong>Conforto:</strong> {{ $overInflated['comfort'] }}</p>
           @endif
       </div>
       @endif
       
       @if(!empty($article->calibration_impacts['ideal_calibration']))
       <div class="safety-alert info">
           <div class="alert-title">✅ Calibragem Ideal</div>
           @php $ideal = $article->calibration_impacts['ideal_calibration'] @endphp
           @if($ideal['fuel_consumption'])
           <p><strong>Consumo:</strong> {{ $ideal['fuel_consumption'] }}</p>
           @endif
           @if($ideal['wear_pattern'])
           <p><strong>Desgaste:</strong> {{ $ideal['wear_pattern'] }}</p>
           @endif
           @if($ideal['handling'])
           <p><strong>Dirigibilidade:</strong> {{ $ideal['handling'] }}</p>
           @endif
           @if($ideal['safety'])
           <p><strong>Segurança:</strong> {{ $ideal['safety'] }}</p>
           @endif
       </div>
       @endif
       @endif
       
       <!-- Dicas de Manutenção -->
       @if(!empty($article->maintenance_tips))
       <h2>🛠️ Dicas para Maximizar Economia e Durabilidade</h2>
       
       @foreach($article->maintenance_tips as $tipGroup)
       <div class="recommendation-card">
           <div class="recommendation-title">
               <amp-img class="recommendation-icon" 
                   src="https://mercadoveiculos.s3.us-east-1.amazonaws.com/statics/icons/icon-{{ $tipGroup['icon_class'] ?? 'tool' }}.svg" 
                   width="24" height="24" alt="{{ $tipGroup['category'] }}"></amp-img>
               {{ $tipGroup['category'] }}
           </div>
           @if(!empty($tipGroup['items']))
           <ul style="margin: 16px 0; padding-left: 20px;">
               @foreach($tipGroup['items'] as $item)
               <li style="margin-bottom: 8px; color: #4a5568;">{{ $item }}</li>
               @endforeach
           </ul>
           @endif
       </div>
       @endforeach
       @endif
       
       <!-- Perguntas Frequentes -->
       @if(!empty($article->faq))
       <h2>❓ Perguntas Frequentes sobre o {{ $article->vehicle_info['full_name'] ?? 'Veículo' }}</h2>
       
       <amp-accordion>
           @foreach($article->faq as $faq)
           <section>
               <h4>{{ $faq['pergunta'] }}</h4>
               <div class="faq-content">
                   <p>{{ $faq['resposta'] }}</p>
               </div>
           </section>
           @endforeach
       </amp-accordion>
       @endif
       
       <!-- Considerações Finais -->
       @if(!empty($article->final_considerations))
       <h2>📝 Considerações Finais</h2>
       <div style="background-color: #f8fafc; padding: 20px; border-radius: 8px; border-left: 4px solid #0E368A;">
           <p style="color: #4a5568; line-height: 1.7;">{{ $article->final_considerations }}</p>
       </div>
       @endif
       
       <!-- Footer info -->
       <div class="article-footer">
           @if(!empty($article->formated_updated_at))
           <p><strong>Atualizado em:</strong> {{ $article->formated_updated_at }}</p>
           @endif
           <p><strong>Por:</strong> Equipe Editorial Mercado Veículos</p>
           <p><a href="{{ route('info.article.show', $article->slug) }}">Ver versão completa do artigo</a></p>
       </div>
   </article>
</div>
@endsection