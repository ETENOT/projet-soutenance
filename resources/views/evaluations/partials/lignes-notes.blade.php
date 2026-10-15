{{--
    Affiche des critères notés en étoiles, en lecture seule.
    Variables : $criteres (colonne => libellé), $source (modèle ou tableau de moyennes)
--}}
@foreach ($criteres as $colonne => $libelle)
    @php($valeur = (float) data_get($source, $colonne))
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 py-1">
        <span>{{ $libelle }}</span>
        <span class="text-nowrap">
            @include('cours.partials.etoiles', ['note' => $valeur, 'taille' => 'fs-6'])
            <span class="ms-1 text-muted small">{{ number_format($valeur, 1, ',', '') }}</span>
        </span>
    </div>
@endforeach