<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RoadService
{
    /**
     * Distance routière OSRM en kilomètres.
     *
     * @param  array{lat:float,lon:float} $a
     * @param  array{lat:float,lon:float} $b
     * @return float|null
     */
    public function distanceKm(array $a, array $b): ?float
    {
        $coords = "{$a['lon']},{$a['lat']};{$b['lon']},{$b['lat']}";
        $url    = "https://router.project-osrm.org/route/v1/driving/{$coords}";
        $res    = Http::get($url, ['overview' => 'false']);

        if (! $res->ok() || empty($res['routes'][0]['distance'])) {
            return null;
        }

        // OSRM renvoie la distance en mètres
        return $res['routes'][0]['distance'] / 1000;
    }
}