@php
$extractedEntities = $article->getData()['extracted_entities'] ?? [];
@endphp

@if(!empty($extractedEntities))
<div class="content-section">
    <div class="section-header">
        <span class="section-icon">🚗</span>
        <span class="section-title">Dados Técnicos do Veículo</span>
    </div>

    <div class="vehicle-specs-simple">
        @if(!empty($extractedEntities['marca']))
        <div class="spec-row">
            <span class="spec-label">Marca:</span>
            <span class="spec-value-simple brand-{{ strtolower($extractedEntities['marca']) }}">
                {{ $extractedEntities['marca'] }}
            </span>
        </div>
        @endif

        @if(!empty($extractedEntities['modelo']))
        <div class="spec-row">
            <span class="spec-label">Modelo:</span>
            <span class="spec-value-simple">{{ $extractedEntities['modelo'] }}</span>
        </div>
        @endif

        @if(!empty($extractedEntities['categoria']))
        <div class="spec-row">
            <span class="spec-label">Categoria:</span>
            <span class="spec-value-simple category-{{ strtolower($extractedEntities['categoria']) }}">
                {{ ucfirst($extractedEntities['categoria']) }}
            </span>
        </div>
        @endif

        @if(!empty($extractedEntities['tipo_veiculo']))
        <div class="spec-row">
            <span class="spec-label">Tipo:</span>
            <span class="spec-value-simple">{{ $extractedEntities['tipo_veiculo'] }}</span>
        </div>
        @endif
    </div>

    @if(!empty($extractedEntities['pneus']))
    @php
        $pneus = explode(' ', $extractedEntities['pneus']);
        $dianteiro = '';
        $traseiro = '';
        
        foreach($pneus as $index => $pneu) {
            if(str_contains($pneu, '(DIANTEIRO)')) {
                $dianteiro = $pneus[$index - 1] ?? '';
            }
            if(str_contains($pneu, '(TRASEIRO)')) {
                $traseiro = $pneus[$index - 1] ?? '';
            }
        }
    @endphp

    @if($dianteiro && $traseiro)
        <div class="tire-section">
            <div class="tire-label">PNEU DIANTEIRO</div>
            <div class="tire-value">{{ $dianteiro }}</div>
        </div>
        
        <div class="tire-section">
            <div class="tire-label">PNEU TRASEIRO</div>
            <div class="tire-value">{{ $traseiro }}</div>
        </div>
    @else
        <div class="tire-section-single">
            <div class="tire-label">MEDIDA DOS PNEUS</div>
            <div class="tire-value">{{ $extractedEntities['pneus'] }}</div>
        </div>
    @endif
    @endif

    <!-- Informações Complementares -->
    <div class="vehicle-summary">
        <p style="text-align: center; font-size: 14px; color: #4b5563; margin-top: 16px;">
            <strong>{{ $extractedEntities['marca'] ?? '' }} {{ $extractedEntities['modelo'] ?? '' }}</strong>
            - Especificações oficiais para calibragem de pneus
        </p>
    </div>
</div>
@endif