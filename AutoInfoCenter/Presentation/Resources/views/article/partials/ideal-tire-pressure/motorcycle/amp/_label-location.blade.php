@php
$informationLocation = $article->getData()['information_location'] ?? [];
@endphp

@if(!empty($informationLocation) || !empty($contentData['owner_manual']) || !empty($contentData['motorcycle_label']))
<div class="content-section">
    <div class="section-header">
        <span class="section-icon">📍</span>
        <span class="section-title">Onde Encontrar as Especificações de Pressão</span>
    </div>

    <p style="color: #4b5563; margin-bottom: 24px; font-size: 15px; line-height: 1.6;">
        Em motocicletas, as informações de pressão dos pneus podem estar em diferentes locais.
        Aqui estão os principais pontos onde verificar:
    </p>

    <!-- Manual do Proprietário -->
    @if(!empty($informationLocation['owner_manual']) || !empty($contentData['owner_manual']))
    @php $ownerManual = $informationLocation['owner_manual'] ?? $contentData['owner_manual'] ?? []; @endphp
    <div
        style="background: linear-gradient(135deg, #eff6ff, #dbeafe); border: 1px solid #3b82f6; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; margin-bottom: 16px;">
            <span style="font-size: 24px; margin-right: 12px;">📖</span>
            <div>
                <div
                    style="background: #3b82f6; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; display: inline-block; margin-bottom: 8px;">
                    Principal
                </div>
                <h4 style="margin: 0; font-size: 16px; font-weight: 600; color: #1e40af;">Manual do Proprietário</h4>
            </div>
        </div>

        <div style="background: rgba(255,255,255,0.7); border-radius: 6px; padding: 16px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; font-size: 14px;">
                <div>
                    <span style="color: #6b7280; font-weight: 500;">Localização:</span><br>
                    <span style="color: #1f2937; font-weight: 600;">{{ $ownerManual['location'] ?? 'Especificações
                        Técnicas' }}</span>
                </div>
                <div>
                    <span style="color: #6b7280; font-weight: 500;">Seção:</span><br>
                    <span style="color: #1f2937; font-weight: 600;">{{ $ownerManual['section'] ?? 'Rodas e Pneus'
                        }}</span>
                </div>
            </div>
            <div style="margin-top: 12px; font-size: 14px;">
                <span style="color: #6b7280; font-weight: 500;">Página:</span>
                <span style="color: #1f2937; font-weight: 600;">{{ $ownerManual['approximate_page'] ?? 'Consulte índice'
                    }}</span>
            </div>
        </div>
    </div>
    @endif

    <!-- Etiqueta na Motocicleta -->
    @if(!empty($informationLocation['motorcycle_label']) || !empty($contentData['motorcycle_label']))
    @php $motorcycleLabel = $informationLocation['motorcycle_label'] ?? $contentData['motorcycle_label'] ?? []; @endphp
    <div
        style="background: linear-gradient(135deg, #fff7ed, #fed7aa); border: 1px solid #f97316; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; margin-bottom: 16px;">
            <span style="font-size: 24px; margin-right: 12px;">🏍️</span>
            <div>
                <div
                    style="background: #f97316; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; display: inline-block; margin-bottom: 8px;">
                    Alternativo
                </div>
                <h4 style="margin: 0; font-size: 16px; font-weight: 600; color: #ea580c;">Etiqueta na Moto</h4>
            </div>
        </div>

        <div style="background: rgba(255,255,255,0.7); border-radius: 6px; padding: 16px;">
            @if(!empty($motorcycleLabel['common_locations']) && is_array($motorcycleLabel['common_locations']))
            @foreach($motorcycleLabel['common_locations'] as $location)
            <div style="display: flex; align-items: center; margin-bottom: 8px; font-size: 14px; color: #7c2d12;">
                <span
                    style="width: 6px; height: 6px; background: #f97316; border-radius: 50%; margin-right: 12px; flex-shrink: 0;"></span>
                <span>{{ $location }}</span>
            </div>
            @endforeach
            @else
            <div style="display: flex; align-items: center; margin-bottom: 8px; font-size: 14px; color: #7c2d12;">
                <span
                    style="width: 6px; height: 6px; background: #f97316; border-radius: 50%; margin-right: 12px; flex-shrink: 0;"></span>
                <span>Chassi (lado direito)</span>
            </div>
            <div style="display: flex; align-items: center; margin-bottom: 8px; font-size: 14px; color: #7c2d12;">
                <span
                    style="width: 6px; height: 6px; background: #f97316; border-radius: 50%; margin-right: 12px; flex-shrink: 0;"></span>
                <span>Balança traseira</span>
            </div>
            <div style="display: flex; align-items: center; margin-bottom: 8px; font-size: 14px; color: #7c2d12;">
                <span
                    style="width: 6px; height: 6px; background: #f97316; border-radius: 50%; margin-right: 12px; flex-shrink: 0;"></span>
                <span>Garfo dianteiro</span>
            </div>
            @endif

            @if(!empty($motorcycleLabel['note']) || !empty($motorcycleLabel['observacao']))
            <div style="background: rgba(251, 146, 60, 0.1); padding: 12px; border-radius: 6px; margin-top: 12px;">
                <p style="margin: 0; font-size: 13px; font-weight: 500; color: #7c2d12;">{{ $motorcycleLabel['note'] ??
                    $motorcycleLabel['observacao'] }}</p>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Outras Fontes -->
    <div
        style="background: linear-gradient(135deg, #f9fafb, #f3f4f6); border: 1px solid #6b7280; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; margin-bottom: 16px;">
            <span style="font-size: 24px; margin-right: 12px;">🔍</span>
            <div>
                <div
                    style="background: #6b7280; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; display: inline-block; margin-bottom: 8px;">
                    Outros
                </div>
                <h4 style="margin: 0; font-size: 16px; font-weight: 600; color: #374151;">Outras Fontes</h4>
            </div>
        </div>

        <div style="background: rgba(255,255,255,0.7); border-radius: 6px; padding: 16px;">
            <div style="display: flex; align-items: center; margin-bottom: 8px; font-size: 14px; color: #4b5563;">
                <span
                    style="width: 6px; height: 6px; background: #6b7280; border-radius: 50%; margin-right: 12px; flex-shrink: 0;"></span>
                <span>Concessionária autorizada</span>
            </div>
            <div style="display: flex; align-items: center; margin-bottom: 8px; font-size: 14px; color: #4b5563;">
                <span
                    style="width: 6px; height: 6px; background: #6b7280; border-radius: 50%; margin-right: 12px; flex-shrink: 0;"></span>
                <span>Site oficial da marca</span>
            </div>
            <div style="display: flex; align-items: center; margin-bottom: 8px; font-size: 14px; color: #4b5563;">
                <span
                    style="width: 6px; height: 6px; background: #6b7280; border-radius: 50%; margin-right: 12px; flex-shrink: 0;"></span>
                <span>Ficha técnica do veículo</span>
            </div>
            <div style="display: flex; align-items: center; font-size: 14px; color: #4b5563;">
                <span
                    style="width: 6px; height: 6px; background: #6b7280; border-radius: 50%; margin-right: 12px; flex-shrink: 0;"></span>
                <span>Aplicativos da fabricante</span>
            </div>
        </div>
    </div>

    <!-- Dica Importante -->
    @if(!empty($informationLocation['important_tip']) || !empty($contentData['important_tip']))
    <div class="alert-box alert-warning" style="margin-bottom: 20px;">
        <h4 style="margin: 0 0 8px; font-weight: 600; display: flex; align-items: center;">
            <span style="margin-right: 8px;">💡</span>
            Dica Importante
        </h4>
        <p style="margin: 0; font-size: 14px;">
            {{ $informationLocation['important_tip'] ?? $contentData['important_tip'] ?? 'Use sempre PSI como referência
            padrão brasileiro. Em caso de dúvida, consulte sempre o manual do proprietário da sua motocicleta.' }}
        </p>
    </div>
    @endif

    <!-- Alerta Específico para Motocicletas -->
    <div class="alert-box alert-critical">
        <h4 style="margin: 0 0 8px; font-weight: 600; display: flex; align-items: center;">
            <span style="margin-right: 8px;">⚠️</span>
            Atenção Especial
        </h4>
        <p style="margin: 0; font-size: 14px;">
            Em motocicletas, a pressão incorreta dos pneus pode afetar drasticamente a estabilidade e segurança.
            <strong>Nunca "chute" a pressão</strong> - sempre consulte as especificações oficiais da sua moto.
            A diferença de pressão entre pneu dianteiro e traseiro é normal e necessária.
        </p>
    </div>
</div>
@endif