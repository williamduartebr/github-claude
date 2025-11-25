@extends('auto-info-center::layouts.app')

@section('title', 'Catálogo de Veículos')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-4xl font-bold mb-8">Catálogo de Veículos</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @dump($make)
    </div>
</div>
@endsection
