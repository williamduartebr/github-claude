<?php

namespace Src\VehicleDataCenter\Presentation\ViewModels;

class VehicleListViewModel
{
    private $makes;

    public function __construct($makes)
    {
        $this->makes = $makes;
    }

    public function getMakes(): array
    {
        return $this->makes->map(function ($make) {
            return [
                'id' => $make->id,
                'name' => $make->name,
                'slug' => $make->slug,
                'logo' => $make->logo_url,
                'models_count' => $make->models->count(),
                'url' => url("/veiculos/{$make->slug}")
            ];
        })->toArray();
    }
}
