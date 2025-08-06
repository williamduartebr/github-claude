<?php

namespace Src\AutoInfoCenter\Presentation\Components;

use Illuminate\View\Component;
use Illuminate\Support\Facades\Cache;

class SearchButton extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('auto-info-center::components.search-button');
    }

    /**
     * Get popular search terms.
     *
     * @return array
     */
    public function getPopularSearches()
    {
        // Aqui você pode implementar a lógica para buscar os termos mais populares
        // Por exemplo, buscando do cache ou do banco de dados
        return Cache::remember('popular_searches', 3600, function () {
            // Em produção, aqui você buscaria do banco de dados
            // return Search::popular()->take(5)->pluck('term')->toArray();

            // Por enquanto, retornamos valores estáticos
            return ['SUV', 'Sedan', 'Hatch', 'Troca de óleo'];
        });
    }
}
