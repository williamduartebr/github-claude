<?php

namespace Src\AutoInfoCenter\Presentation\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CategoryIcon extends Component
{
    public $title;
    public $url;
    public $iconSvg;
    public $bgColor;
    public $textColor;
    public $toFollow;

    /**
     * Create a new component instance.
     */
    public function __construct($title, $url, $iconSvg, $bgColor = 'bg-blue-100', $textColor = 'text-blue-600', $toFollow = false)
    {
        $this->title = $title;
        $this->url = $url;
        // Decodificar as entidades HTML do SVG
        $this->iconSvg = html_entity_decode($iconSvg);
        $this->bgColor = $bgColor;
        $this->textColor = $textColor;
        $this->toFollow = $toFollow;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('auto-info-center::components.category-icon');
    }
}
