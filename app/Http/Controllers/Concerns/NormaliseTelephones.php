<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

// Les numéros saisis à la main (« +241 77 12 34 56 ») sont nettoyés avant la validation du format international.
trait NormaliseTelephones
{
    protected function normaliserTelephones(Request $request, array $champs): void
    {
        foreach ($champs as $champ) {
            if ($request->filled($champ)) {
                // On retire espaces, points, tirets et parenthèses : « +24177123456 »
                $request->merge([$champ => preg_replace('/[\s.\-()]/', '', $request->input($champ))]);
            }
        }
    }
}