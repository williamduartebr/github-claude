@php
$tireSpecs = $article->getData()['tire_specifications'] ?? [];
$vehicleInfo = $article->getData()['vehicle_info'] ?? [];
@endphp

@if(!empty($tireSpecs))
<section class="mb-12" id="tire-specifications">
    <div class="bg-gradient-to-r from-gray-800 to-gray-900 text-white rounded-t-lg p-6">
        <div class="flex items-center">
            <span class="text-3xl mr-4">🏍️</span>
            <div>
                <h2 class="text-2xl font-bold mb-2">
                    Especificações dos Pneus
                </h2>
                <p class="text-gray-300 text-sm">
                    Medidas e especificações da {{ $vehicleInfo['full_name'] ?? 'motocicleta' }}
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-b-lg border-l border-r border-b border-gray-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            @if(!empty($tireSpecs['front_tire']))
            <!-- Pneu Dianteiro -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Pneu Dianteiro</h3>
                </div>
                <div class="p-6 space-y-4">
                    @php $frontTire = $tireSpecs['front_tire'] @endphp
                    
                    @if(!empty($frontTire['tire_size']))
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 text-base">Medida:</span>
                        <span class="text-lg font-bold text-gray-900">{{ $frontTire['tire_size'] }}</span>
                    </div>
                    @endif
                    
                    @if(!empty($frontTire['load_speed_index']))
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 text-base">Índice Carga/Velocidade:</span>
                        <span class="text-lg font-bold text-gray-900">{{ $frontTire['load_speed_index'] }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif
            
            @if(!empty($tireSpecs['rear_tire']))
            <!-- Pneu Traseiro -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Pneu Traseiro</h3>
                </div>
                <div class="p-6 space-y-4">
                    @php $rearTire = $tireSpecs['rear_tire'] @endphp
                    
                    @if(!empty($rearTire['tire_size']))
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 text-base">Medida:</span>
                        <span class="text-lg font-bold text-gray-900">{{ $rearTire['tire_size'] }}</span>
                    </div>
                    @endif
                    
                    @if(!empty($rearTire['load_speed_index']))
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 text-base">Índice Carga/Velocidade:</span>
                        <span class="text-lg font-bold text-gray-900">{{ $rearTire['load_speed_index'] }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif
            
        </div>
        
        @if(!empty($tireSpecs['observation']))
        <div class="mt-6 bg-gray-50 border border-gray-200 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-gray-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <div>
                    <p class="font-medium text-gray-900 mb-1">Importante:</p>
                    <p class="text-gray-700">{{ $tireSpecs['observation'] }}</p>
                </div>
            </div>
        </div>
        @endif
    </div>
</section>
@endif