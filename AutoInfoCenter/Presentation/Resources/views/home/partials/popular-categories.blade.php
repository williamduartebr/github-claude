<section aria-labelledby="categorias-populares">
    <h2 id="categorias-populares" class="text-2xl md:text-3xl font-bold mb-6 text-gray-800 font-montserrat">Categorias
        Populares</h2>

    @if(count($categories) > 0)
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        @foreach($categories as $category)
        <x-info::category-icon title="{{ $category->name }}" url="{{ route('info.category.show', $category->slug) }}"
            iconSvg="{{ $category->icon_svg }}" bgColor="{{ $category->icon_bg_color }}"
            textColor="{{ $category->icon_text_color }}" 
            toFollow="{{ $category->to_follow }}"  />
        @endforeach
    </div>
    @else
    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4">
        Nenhuma categoria encontrada.
    </div>
    @endif

</section>