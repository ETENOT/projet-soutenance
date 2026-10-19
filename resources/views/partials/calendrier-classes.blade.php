{{--
    Petit calendrier des classes (FullCalendar), réutilisé sur l'accueil et les dashboards.
    Variables : $evenements (tableau d'évènements, une entrée par classe), $titre (facultatif),
                $legende (facultatif) : liste de [classe CSS, libellé],
                $aide (facultatif) : phrase d'explication sous la légende
    L'initialisation est dans public/build/js/pages/calendrier-classes.init.js
--}}
<div class="card nv-calendrier mb-0">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="ri-calendar-2-line text-danger me-2"></i>{{ $titre ?? 'Calendrier des classes' }}
        </h5>
    </div>

    <div class="card-body">
        @if (! empty($legende))
            <div class="d-flex flex-wrap gap-3 small mb-2">
                @foreach ($legende as [$classeCss, $libelle])
                    <span class="d-inline-flex align-items-center gap-1">
                        <span class="nv-cal-pastille {{ $classeCss }}"></span>{{ $libelle }}
                    </span>
                @endforeach
            </div>
        @endif

        @if (! empty($aide))
            <p class="small text-muted mb-3">
                <i class="ri-information-line me-1"></i>{{ $aide }}
            </p>
        @endif

        <div class="js-calendrier-classes" data-evenements="{{ json_encode($evenements ?? [], JSON_UNESCAPED_UNICODE) }}"></div>
    </div>
</div>

<script src="{{ URL::asset('build/libs/fullcalendar/index.global.min.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/calendrier-classes.init.js') }}"></script>