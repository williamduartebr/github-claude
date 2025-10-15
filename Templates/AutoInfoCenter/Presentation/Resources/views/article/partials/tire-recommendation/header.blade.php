@php
$imageDefault = \Str::slug( sprintf("%s-%s", $article->category['slug'] ?? 'pneus',
$article->vehicle_info['vehicle_type'] ?? 'recomendacao'));
@endphp

<div class="relative rounded-lg overflow-hidden mb-8 mt-2 hidden md:block">
    <img src="https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/tire_recommendation.png"
        alt="{{ $article->title }}" class="w-full h-64 object-cover"
        onerror="this.src='https:\/\/mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/tire_recommendation.png'">
    <div class="absolute inset-0 bg-gradient-to-t from-black/100 to-transparent"></div>
    <div class="absolute bottom-0 left-0 p-6 text-white">
        <h1 class="text-3xl md:text-4xl font-semibold leading-tight">{{ $article->title }}</h1>
        @if(!empty($article->formated_updated_at))
        <p class="text-sm mt-2 opacity-90">Atualizado em: {{ $article->formated_updated_at }}</p>
        @endif
    </div>
</div>