<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Observers;

use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleMake;

/**
 * GuideObserver
 * 
 * Sincroniza automaticamente a logo da marca no guia
 */
class GuideObserver
{
    /**
     * Handle the Guide "creating" event.
     */
    public function creating(Guide $guide): void
    {
        $this->syncMakeLogo($guide);
    }

    /**
     * Handle the Guide "updating" event.
     */
    public function updating(Guide $guide): void
    {
        // Re-sincroniza se vehicle_make_id mudou
        if ($guide->isDirty('vehicle_make_id')) {
            $this->syncMakeLogo($guide);
        }
    }

    /**
     * Sincroniza logo da marca
     */
    private function syncMakeLogo(Guide $guide): void
    {
        if (!$guide->vehicle_make_id) {
            $guide->make_logo_url = null;
            return;
        }

        $make = VehicleMake::find($guide->vehicle_make_id);
        
        $guide->make_logo_url = $make?->logo_url;
    }
}