@php
$hasTpms = $vehicleInfo['has_tpms'] ?? false;
@endphp

@if($hasTpms)
<section class="mb-12">
    <div class="bg-gradient-to-br from-blue-50 to-indigo-100 border-2 border-blue-200 rounded-2xl p-8">
        <div class="flex items-center mb-6">
            <div class="h-12 w-12 bg-blue-500 rounded-xl flex items-center justify-center mr-4">
                <span class="text-white text-2xl">📡</span>
            </div>
            <div>
                <h2 class="text-xl font-bold text-blue-800">Sistema TPMS Disponível</h2>
                <p class="text-blue-700">Monitoramento automático da pressão dos pneus</p>
            </div>
        </div>

        <p class="text-blue-800 mb-6 leading-relaxed">
            Esta pickup possui sistema TPMS que monitora automaticamente a pressão dos pneus e
            alerta no painel quando há variações críticas. Especialmente importante para pickups
            com variações constantes de carga.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white/70 rounded-xl p-4">
                <h3 class="font-semibold text-blue-800 mb-3">Vantagens do TPMS:</h3>
                <ul class="space-y-2 text-sm text-blue-700">
                    <li class="flex items-center">
                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                        Alerta em tempo real
                    </li>
                    <li class="flex items-center">
                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                        Maior segurança com carga
                    </li>
                    <li class="flex items-center">
                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                        Prevenção de acidentes
                    </li>
                    <li class="flex items-center">
                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                        Economia de combustível
                    </li>
                </ul>
            </div>

            <div class="bg-white/70 rounded-xl p-4">
                <h3 class="font-semibold text-blue-800 mb-3">Importante Lembrar:</h3>
                <ul class="space-y-2 text-sm text-blue-700">
                    <li class="flex items-center">
                        <span class="w-2 h-2 bg-orange-500 rounded-full mr-2"></span>
                        Não substitui verificação manual
                    </li>
                    <li class="flex items-center">
                        <span class="w-2 h-2 bg-orange-500 rounded-full mr-2"></span>
                        Alerta apenas quedas críticas
                    </li>
                    <li class="flex items-center">
                        <span class="w-2 h-2 bg-orange-500 rounded-full mr-2"></span>
                        Verificar {{ $isPremium ? 'semanalmente' : 'quinzenalmente' }} mesmo assim
                    </li>
                    <li class="flex items-center">
                        <span class="w-2 h-2 bg-orange-500 rounded-full mr-2"></span>
                        Recalibrar após reset
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>
@endif