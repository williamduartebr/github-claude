@extends('auto-info-center::layouts.app')

@section('title', $vehicle['full_name'])

@section('meta')
    <meta name="description" content="Ficha técnica completa do {{ $vehicle['full_name'] }}">
    <link rel="canonical" href="{{ $vehicle['url'] }}">
@endsection

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <nav class="text-sm mb-4">
            <a href="/veiculos" class="text-blue-600 hover:underline">Veículos</a>
            <span class="mx-2">/</span>
            <a href="/veiculos/{{ $vehicle['make']['slug'] }}" class="text-blue-600 hover:underline">
                {{ $vehicle['make']['name'] }}
            </a>
            <span class="mx-2">/</span>
            <a href="/veiculos/{{ $vehicle['make']['slug'] }}/{{ $vehicle['model']['slug'] }}" class="text-blue-600 hover:underline">
                {{ $vehicle['model']['name'] }}
            </a>
            <span class="mx-2">/</span>
            <span class="text-gray-600">{{ $vehicle['version']['year'] }}</span>
        </nav>
        
        <h1 class="text-4xl font-bold mb-2">{{ $vehicle['full_name'] }}</h1>
        
        @if($vehicle['version']['price_msrp'])
            <p class="text-2xl text-green-600 font-semibold">
                R$ {{ number_format($vehicle['version']['price_msrp'], 2, ',', '.') }}
            </p>
        @endif
    </div>

    <!-- Quick Info -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-600 text-sm">Potência</p>
            <p class="text-xl font-semibold">
                {{ $vehicle['specs']['power_hp'] ?? 'N/A' }} cv
            </p>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-600 text-sm">Consumo Médio</p>
            <p class="text-xl font-semibold">
                {{ $vehicle['specs']['fuel_consumption_mixed'] ?? 'N/A' }} km/l
            </p>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-600 text-sm">Combustível</p>
            <p class="text-xl font-semibold capitalize">
                {{ $vehicle['version']['fuel_type'] ?? 'N/A' }}
            </p>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-600 text-sm">Transmissão</p>
            <p class="text-xl font-semibold capitalize">
                {{ $vehicle['version']['transmission'] ?? 'N/A' }}
            </p>
        </div>
    </div>

    <!-- Detailed Specs -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-bold mb-6">Especificações Técnicas</h2>
        
        <!-- Performance -->
        <div class="mb-6">
            <h3 class="text-xl font-semibold mb-4 border-b pb-2">Desempenho</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Potência</span>
                    <span class="font-semibold">{{ $vehicle['specs']['power_hp'] ?? 'N/A' }} cv</span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Torque</span>
                    <span class="font-semibold">{{ $vehicle['specs']['torque_nm'] ?? 'N/A' }} Nm</span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">0-100 km/h</span>
                    <span class="font-semibold">{{ $vehicle['specs']['acceleration_0_100'] ?? 'N/A' }} s</span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Velocidade Máxima</span>
                    <span class="font-semibold">{{ $vehicle['specs']['top_speed_kmh'] ?? 'N/A' }} km/h</span>
                </div>
            </div>
        </div>

        <!-- Consumption -->
        <div class="mb-6">
            <h3 class="text-xl font-semibold mb-4 border-b pb-2">Consumo</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Cidade</span>
                    <span class="font-semibold">{{ $vehicle['specs']['fuel_consumption_city'] ?? 'N/A' }} km/l</span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Estrada</span>
                    <span class="font-semibold">{{ $vehicle['specs']['fuel_consumption_highway'] ?? 'N/A' }} km/l</span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Misto</span>
                    <span class="font-semibold">{{ $vehicle['specs']['fuel_consumption_mixed'] ?? 'N/A' }} km/l</span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Tanque</span>
                    <span class="font-semibold">{{ $vehicle['specs']['fuel_tank_capacity'] ?? 'N/A' }} litros</span>
                </div>
            </div>
        </div>

        <!-- Capacity -->
        <div class="mb-6">
            <h3 class="text-xl font-semibold mb-4 border-b pb-2">Capacidade</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Lugares</span>
                    <span class="font-semibold">{{ $vehicle['specs']['seating_capacity'] ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Portas</span>
                    <span class="font-semibold">{{ $vehicle['specs']['doors'] ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Porta-Malas</span>
                    <span class="font-semibold">{{ $vehicle['specs']['trunk_capacity_liters'] ?? 'N/A' }} litros</span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Peso</span>
                    <span class="font-semibold">{{ $vehicle['specs']['weight_kg'] ?? 'N/A' }} kg</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Vehicles -->
    <div class="mt-8">
        <h2 class="text-2xl font-bold mb-4">Outras Versões</h2>
        <p class="text-gray-600">
            <a href="/veiculos/{{ $vehicle['make']['slug'] }}/{{ $vehicle['model']['slug'] }}/{{ $vehicle['version']['year'] }}" class="text-blue-600 hover:underline">
                Ver todas as versões do {{ $vehicle['model']['name'] }} {{ $vehicle['version']['year'] }}
            </a>
        </p>
    </div>
</div>
@endsection
