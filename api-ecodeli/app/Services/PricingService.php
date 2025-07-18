<?php

namespace App\Services;

class PricingService
{
    protected int $base;
    protected int $perKg;
    protected int $perM3;
    protected int $perKm;

    public function __construct()
    {
        $cfg         = config('pricing');
        $this->base  = $cfg['base']    ?? 500;
        $this->perKg = $cfg['per_kg']  ?? 100;
        $this->perM3 = $cfg['per_m3']  ?? 200;
        $this->perKm = $cfg['per_km']  ?? 50;
    }

    /**
     * Calcule le prix en centimes.
     *
     * @param  float  $weight   Poids en kg
     * @param  float  $volume   Volume total en m³
     * @param  float  $distance Distance en km
     * @return int
     */
    public function calculate(float $weight, float $volume, float $distance): int
    {
        return (int) round(
            $this->base
            + $weight   * $this->perKg
            + $volume   * $this->perM3
            + $distance * $this->perKm
        );
    }
}
