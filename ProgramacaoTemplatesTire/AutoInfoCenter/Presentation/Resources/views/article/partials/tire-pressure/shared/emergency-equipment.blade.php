{{-- 
Partial Master: partials/tire-pressure/shared/emergency-equipment.blade.php
Lógica condicional que decide entre estepe ou kit de reparo
--}}

@php
    $emergencyEquipment = $article->getData()['emergency_equipment'] ?? [];
    $vehicleInfo = $article->getData()['vehicle_info'] ?? [];
    $hasSpareTire = $emergencyEquipment['has_spare'] ?? false;
@endphp

{{-- 🎯 LÓGICA CONDICIONAL PRINCIPAL --}}
@if($hasSpareTire)
    {{-- 
    ESTEPE: Quando pressure_spare > 0
    Exemplos: Toyota Corolla Hybrid (60 PSI), Peugeot 3008 (60 PSI), VW Polo (36 PSI)
    --}}
    @include('auto-info-center::article.partials.tire-pressure.car.spare-tire-section')
@else
    {{-- 
    KIT DE REPARO: Quando pressure_spare = 0  
    Exemplos: Mercedes EQA 2025 (elétrico), outros premium sem estepe
    --}}
    @include('auto-info-center::article.partials.tire-pressure.car.repair-kit-section')
@endif

{{-- 
========================================
📝 COMO USAR NO TEMPLATE PRINCIPAL:
========================================

No arquivo ideal_tire_pressure_car.blade.php, adicionar:

@include('auto-info-center::article.partials.tire-pressure.shared.emergency-equipment')

========================================
🎯 LÓGICA DE DECISÃO:
========================================

Mercedes EQA 2025:     pressure_spare: 0  → Kit de Reparo ✅
Toyota Corolla Hybrid: pressure_spare: 60 → Estepe Temporário ✅  
Peugeot 3008 2024:     pressure_spare: 60 → Estepe Temporário ✅
VW Polo 2023:          pressure_spare: 36 → Estepe Compacto ✅

========================================
--}}