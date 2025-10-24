@php
$imageDefault = \Str::slug( sprintf("%s-%s", $article->category['slug'] ?? 'oleo',
$article->vehicle_info['vehicle_type'] ?? 'tabela'));
@endphp

<div class="relative rounded-lg overflow-hidden mb-8 mt-2 hidden md:block">
    <img src="https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/oil_table.png"
        alt="{{ $article->title }}" class="w-full h-64 object-cover"
        onerror="this.src='https:\/\/mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/oil_table.png'">
    <div class="absolute inset-0 bg-gradient-to-t from-black/100 to-transparent"></div>
    <div class="absolute bottom-0 left-0 p-6 text-white">
        <h1 class="text-3xl md:text-4xl font-semibold leading-tight">{{ $article->title }}</h1>
        @if(!empty($article->formated_updated_at))
        <p class="text-sm mt-2 opacity-90">Atualizado em: {{ $article->formated_updated_at }}</p>
        @endif
    </div>
</div>

<div class="mb-8 mt-2 block md:hidden">
    <h1 class="text-3xl font-semibold leading-tight text-gray-900">{{ $article->title }}</h1>
    @if(!empty($article->formated_updated_at))
    <p class="text-sm mt-2 text-gray-600">Atualizado em: {{ $article->formated_updated_at }}</p>
    @endif
</div>