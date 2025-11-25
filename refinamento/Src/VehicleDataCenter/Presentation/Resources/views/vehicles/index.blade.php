@extends('auto-info-center::layouts.app')

@section('title', 'Catálogo de Veículos')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-4xl font-bold mb-8">Catálogo de Veículos</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @foreach($makes as $make)
            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
                <a href="{{ $make['url'] }}" class="block">
                    @if($make['logo'])
                        <img src="{{ $make['logo'] }}" alt="{{ $make['name'] }}" class="h-16 mx-auto mb-4">
                    @endif
                    
                    <h3 class="text-xl font-semibold text-center mb-2">
                        {{ $make['name'] }}
                    </h3>
                    
                    <p class="text-gray-600 text-center text-sm">
                        {{ $make['models_count'] }} modelos
                    </p>
                </a>
            </div>
        @endforeach
    </div>
</div>
@endsection
