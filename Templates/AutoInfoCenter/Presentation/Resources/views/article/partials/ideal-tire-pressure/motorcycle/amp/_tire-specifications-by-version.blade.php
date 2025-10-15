{{--
AMP Section: Especificações dos Pneus por Versão - Layout Limpo
Include para template AMP de calibragem de motocicletas
--}}

@php
$tireSpecsByVersion = $article->getData()['tire_specifications_by_version'] ?? [];
$vehicleInfo = $article->getData()['vehicle_info'] ?? [];
$motorcycleCategory = $vehicleInfo['main_category'] ?? 'motorcycle_street';

// Determinar categoria display
$categoryDisplay = match($motorcycleCategory) {
'motorcycle_sport' => 'Sport',
'motorcycle_touring' => 'Touring',
'motorcycle_adventure' => 'Adventure',
'motorcycle_scooter' => 'Scooter',
'motorcycle_street' => 'Street',
default => 'Standard'
};
@endphp

@if(!empty($tireSpecsByVersion))
<div class="content-section">
    <div class="section-header">
        <span class="section-icon">🏍️</span>
        <span class="section-title">Especificações dos Pneus por Versão</span>
    </div>

    <div class="tire-specs-table-container" style="margin: 20px 0; border-radius: 8px; position: relative;">
        <table class="pressure-table" style="width: 100%;">
            <thead>
                <tr>
                    <th style="text-align: left; min-width: 150px;">Versão</th>
                    <th style="min-width: 120px;">Pneu Dianteiro</th>
                    <th style="min-width: 120px;">Pneu Traseiro</th>
                    <th style="min-width: 100px;">Apenas o Piloto</th>
                    <th style="min-width: 100px;">Com Garupa</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tireSpecsByVersion as $index => $spec)
                <tr class="{{ ($spec['is_main_version'] ?? $index === 0) ? 'main-version' : '' }}"
                    style="background: {{ ($spec['is_main_version'] ?? $index === 0) ? '#eff6ff' : ($loop->even ? '#f8fafc' : 'white') }};">

                    <!-- Versão -->
                    <td style="text-align: left; padding: 16px 12px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            @if($spec['is_main_version'] ?? $index === 0)
                            <div style="width: 8px; height: 8px; background: #dc2626; border-radius: 50%; flex-shrink: 0;"
                                title="Versão principal"></div>
                            @else
                            <div
                                style="width: 8px; height: 8px; background: #9ca3af; border-radius: 50%; flex-shrink: 0;">
                            </div>
                            @endif
                            <span style="font-weight: 600; color: #1f2937; font-size: 14px;">
                                {{ strtoupper($spec['version'] ?? 'Padrão') }}
                            </span>
                        </div>

                        @if($spec['is_main_version'] ?? $index === 0)
                        <div style="font-size: 11px; color: #dc2626; font-weight: 500; margin-top: 4px;">Versão
                            principal</div>
                        @endif
                    </td>

                    <!-- Pneu Dianteiro -->
                    <td style="text-align: center; padding: 16px 8px;">
                        <span
                            style="background: #f1f5f9; color: #1e293b; padding: 6px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; border: 1px solid #cbd5e1; display: inline-block;">
                            {{ $spec['front_tire_size'] ?? 'Consulte manual' }}
                        </span>
                        @if(!empty($spec['load_speed_index']) && $spec['load_speed_index'] !== 'Consulte manual')
                        <div style="font-size: 10px; color: #6b7280; margin-top: 4px;">
                            {{ $spec['load_speed_index'] }}
                        </div>
                        @endif
                    </td>

                    <!-- Pneu Traseiro -->
                    <td style="text-align: center; padding: 16px 8px;">
                        <span
                            style="background: #f1f5f9; color: #1e293b; padding: 6px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; border: 1px solid #cbd5e1; display: inline-block;">
                            {{ $spec['rear_tire_size'] ?? 'Consulte manual' }}
                        </span>
                        @if(!empty($spec['load_speed_index']) && $spec['load_speed_index'] !== 'Consulte manual')
                        <div style="font-size: 10px; color: #6b7280; margin-top: 4px;">
                            {{ $spec['load_speed_index'] }}
                        </div>
                        @endif
                    </td>

                    <!-- Pressão Apenas o Piloto -->
                    <td style="text-align: center; padding: 16px 8px;">
                        <div style="display: flex; flex-direction: column; gap: 4px; align-items: center;">
                            <div style="font-size: 13px; font-weight: 700; color: #059669;">
                                D: {{ $spec['front_solo'] ?? 'N/A' }}
                            </div>
                            <div style="font-size: 13px; font-weight: 700; color: #2563eb;">
                                T: {{ $spec['rear_solo'] ?? 'N/A' }}
                            </div>
                        </div>
                    </td>

                    <!-- Pressão Com Garupa -->
                    <td style="text-align: center; padding: 16px 8px;">
                        <div style="display: flex; flex-direction: column; gap: 4px; align-items: center;">
                            <div style="font-size: 13px; font-weight: 700; color: #ea580c;">
                                D: {{ $spec['front_passenger'] ?? 'N/A' }}
                            </div>
                            <div style="font-size: 13px; font-weight: 700; color: #dc2626;">
                                T: {{ $spec['rear_passenger'] ?? 'N/A' }}
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Legendas -->
    <div
        style="background: #f8fafc; padding: 16px; border-radius: 8px; border-top: 1px solid #e2e8f0; margin-top: 16px;">
        <div
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 12px; font-size: 12px;">
            <div style="display: flex; align-items: center; gap: 6px;">
                <div style="width: 8px; height: 8px; background: #059669; border-radius: 50%;"></div>
                <span style="color: #374151;"><strong>D:</strong> Dianteiro</span>
            </div>
            <div style="display: flex; align-items: center; gap: 6px;">
                <div style="width: 8px; height: 8px; background: #2563eb; border-radius: 50%;"></div>
                <span style="color: #374151;"><strong>T:</strong> Traseiro</span>
            </div>
            <div style="display: flex; align-items: center; gap: 6px;">
                <div style="width: 8px; height: 8px; background: #dc2626; border-radius: 50%;"></div>
                <span style="color: #374151;">Pressões em PSI</span>
            </div>
        </div>
    </div>

    <!-- Dicas específicas por categoria de motocicleta -->
    @if($motorcycleCategory === 'motorcycle_sport')
    <div
        style="background: linear-gradient(135deg, #fef2f2, #fee2e2); border: 1px solid #f87171; border-radius: 8px; padding: 16px; margin-top: 16px;">
        <div style="display: flex; align-items: flex-start; gap: 12px;">
            <div
                style="width: 24px; height: 24px; background: #fca5a5; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <span style="font-size: 12px;">⚡</span>
            </div>
            <div>
                <h4 style="font-size: 14px; font-weight: 600; color: #991b1b; margin: 0 0 8px;">Dica para Motos
                    Esportivas</h4>
                <p style="font-size: 13px; color: #7f1d1d; margin: 0; line-height: 1.5;">
                    Para track days ou uso esportivo, considere reduzir 2-3 PSI para pneus frios. Sempre aqueça os pneus
                    gradualmente.
                </p>
            </div>
        </div>
    </div>
    @elseif($motorcycleCategory === 'motorcycle_touring')
    <div
        style="background: linear-gradient(135deg, #eff6ff, #dbeafe); border: 1px solid #60a5fa; border-radius: 8px; padding: 16px; margin-top: 16px;">
        <div style="display: flex; align-items: flex-start; gap: 12px;">
            <div
                style="width: 24px; height: 24px; background: #93c5fd; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <span style="font-size: 12px;">🛣️</span>
            </div>
            <div>
                <h4 style="font-size: 14px; font-weight: 600; color: #1e3a8a; margin: 0 0 8px;">Dica para Motos Touring
                </h4>
                <p style="font-size: 13px; color: #1e40af; margin: 0; line-height: 1.5;">
                    Para viagens longas com bagagem, use sempre as pressões "com garupa" mesmo viajando sozinho.
                </p>
            </div>
        </div>
    </div>
    @elseif($motorcycleCategory === 'motorcycle_adventure')
    <div
        style="background: linear-gradient(135deg, #ecfdf5, #d1fae5); border: 1px solid #34d399; border-radius: 8px; padding: 16px; margin-top: 16px;">
        <div style="display: flex; align-items: flex-start; gap: 12px;">
            <div
                style="width: 24px; height: 24px; background: #6ee7b7; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <span style="font-size: 12px;">🏔️</span>
            </div>
            <div>
                <h4 style="font-size: 14px; font-weight: 600; color: #064e3b; margin: 0 0 8px;">Dica para Motos
                    Adventure</h4>
                <p style="font-size: 13px; color: #065f46; margin: 0; line-height: 1.5;">
                    Para uso off-road, reduza 3-5 PSI do traseiro para melhor tração na terra. Recalibre para asfalto.
                </p>
            </div>
        </div>
    </div>
    @elseif($motorcycleCategory === 'motorcycle_scooter')
    <div
        style="background: linear-gradient(135deg, #f3e8ff, #e9d5ff); border: 1px solid #c084fc; border-radius: 8px; padding: 16px; margin-top: 16px;">
        <div style="display: flex; align-items: flex-start; gap: 12px;">
            <div
                style="width: 24px; height: 24px; background: #c4b5fd; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <span style="font-size: 12px;">🛴</span>
            </div>
            <div>
                <h4 style="font-size: 14px; font-weight: 600; color: #581c87; margin: 0 0 8px;">Dica para Scooters</h4>
                <p style="font-size: 13px; color: #6b21a8; margin: 0; line-height: 1.5;">
                    Scooters urbanos são sensíveis à pressão. Verifique semanalmente para máxima economia de
                    combustível.
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Nota importante -->
    <div style="background: #fff7ed; border: 1px solid #fb923c; border-radius: 8px; padding: 16px; margin-top: 16px;">
        <div style="display: flex; align-items: flex-start; gap: 12px;">
            <div
                style="width: 20px; height: 20px; background: #fdba74; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <span style="font-size: 10px;">⚠️</span>
            </div>
            <div>
                <p style="font-size: 13px; color: #9a3412; margin: 0; line-height: 1.5;">
                    <strong>Importante:</strong> As pressões listadas são para pneus frios. Durante a pilotagem,
                    especialmente no calor brasileiro, as pressões podem aumentar 4-6 PSI. Sempre verifique pela manhã.
                </p>
            </div>
        </div>
    </div>
</div>

@endif