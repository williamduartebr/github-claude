{{-- resources/views/components/ad-slot-wrapper.blade.php --}}
<section class="ads-expanded-banner max-w-7xl">

    <div class="relative flex py-4 items-center my-4">
        <div class="flex-grow border-t border-gray-300"></div>
        <span class="flex-shrink mx-4 text-gray-500 uppercase font-light text-sm">Continua
            depois da publicidade</span>
        <div class="flex-grow border-t border-gray-300"></div>
    </div>

    <div class="relative w-full">
        {{--
            Este é o slot onde o conteúdo específico de cada adense-article-X.blade.php
            será injetado quando este componente for usado.
        --}}
        {{ $slot }}
    </div>

    <div class="relative flex py-5 items-center my-8">
        <div class="flex-grow border-t border-gray-300"></div>
        <div class="flex-grow border-t border-gray-300"></div>
    </div>
</section>