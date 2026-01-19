<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FacebookEventService
{
    public function getEventStats(string $facebookEventId): array
    {
        // ⚠️ IDE jön majd később a valódi FB API hívásod
        // MOST tesztként ezek dummy adatok lesznek

        return [
            'interested' => rand(50, 200),
            'attending'  => rand(20, 150),
        ];
    }
}
