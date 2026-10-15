{{--
    Graphique de progression des quiz d'un cours (particulier).
    Variable attendue : $progression = tableau de points renvoyé par
    ResultatQuiz::progressionPour() (ordre chronologique).
    Le script d'affichage est dans quiz/partials/progression-script.blade.php,
    à inclure dans @section('script') de la page.
--}}
@php
    $points = $progression ?? [];
    $nbPoints = count($points);
@endphp

<div class="mt-4">
    <h5 class="fw-semibold mb-3">
        <i class="ri-line-chart-line text-primary me-2"></i>Ma progression
    </h5>

    @if ($nbPoints >= 2)
        @php
            $premier  = $points[0]['pourcentage'];
            $dernier  = $points[$nbPoints - 1]['pourcentage'];
            $evolution = round($dernier - $premier, 1);
            $meilleur = max(array_column($points, 'pourcentage'));
        @endphp

        <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="badge bg-primary-subtle text-primary fs-12">
                {{ $nbPoints }} tentatives
            </span>
            <span class="badge bg-success-subtle text-success fs-12">
                Meilleur score : {{ rtrim(rtrim(number_format($meilleur, 1, ',', ''), '0'), ',') }} %
            </span>
            <span class="badge {{ $evolution >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} fs-12">
                <i class="{{ $evolution >= 0 ? 'ri-arrow-up-line' : 'ri-arrow-down-line' }} align-middle"></i>
                {{ $evolution >= 0 ? '+' : '−' }}{{ rtrim(rtrim(number_format(abs($evolution), 1, ',', ''), '0'), ',') }} % depuis la 1re tentative
            </span>
        </div>

        <div class="js-progression-chart"
             data-points="{{ json_encode($points) }}"
             style="min-height: 300px;"></div>
    @elseif ($nbPoints === 1)
        <p class="text-muted mb-0">
            Terminez au moins un deuxième quiz pour voir l'évolution de vos résultats au fil du temps.
        </p>
    @else
        <p class="text-muted mb-0">
            Votre courbe de progression apparaîtra ici dès que vous aurez terminé vos premiers quiz.
        </p>
    @endif
</div>