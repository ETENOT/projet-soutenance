@extends('layouts.master')

@section('title')
    Tableau de bord administrateur
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Dashboards
        @endslot
        @slot('title')
            Administration
        @endslot
    @endcomponent

    <div class="container-fluid pb-4">

        {{-- ===== Chiffres clés : chaque carte mène à sa liste ===== --}}
        <div class="row g-2 mb-3">
            @foreach ([
                ['ri-user-line', 'primary', $totalUtilisateurs, 'Utilisateurs', route('admin.users.index')],
                ['ri-book-open-line', 'primary', $totalCours, 'Cours', route('admin.cours.index')],
                ['ri-group-line', 'primary', $totalClasses, 'Classes', route('admin.classes.index')],
                ['ri-calendar-schedule-line', 'info', $totalSessions, 'Sessions d\'entreprise', route('admin.sessions.index')],
                ['ri-user-add-line', 'success', $totalInscriptions, 'Inscriptions', route('admin.inscriptions.index')],
                ['ri-money-dollar-circle-line', 'success', number_format($encaisseCeMois, 0, ',', ' '), 'Encaissé ce mois (fcfa)', route('admin.inscriptions.index')],
            ] as [$icone, $teinte, $valeur, $libelle, $lien])
                <div class="col-sm-6 col-xl-2">
                    <a href="{{ $lien }}" class="text-reset d-block h-100">
                        <div class="card border shadow-sm h-100 mb-0">
                            <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                                <span class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title rounded-3 bg-{{ $teinte }}-subtle text-{{ $teinte }} fs-4"><i class="{{ $icone }}"></i></span>
                                </span>
                                <div class="overflow-hidden">
                                    <h5 class="fw-bold mb-0 text-truncate">{{ $valeur }}</h5>
                                    <p class="text-muted mb-0 fs-12 text-truncate" title="{{ $libelle }}">{{ $libelle }}</p>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        {{-- ===== Évolution des inscriptions + ce qui demande une action ===== --}}
        <div class="row g-3 mb-3">
            <div class="col-xl-8">
                <div class="card border shadow-sm h-100 mb-0">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0">Inscriptions par mois</h5>
                        <p class="text-muted fs-13 mb-0">Les 9 derniers mois : inscriptions et inscriptions payées</p>
                    </div>
                    <div class="card-body">
                        @if ($inscriptionsSurPeriode > 0)
                            <div data-nv-dash-inscriptions
                                 data-labels="{{ json_encode($inscriptionsParMois['labels']) }}"
                                 data-inscriptions="{{ json_encode($inscriptionsParMois['inscriptions']) }}"
                                 data-payees="{{ json_encode($inscriptionsParMois['payees']) }}"></div>
                        @else
                            <div class="text-center text-muted py-5">
                                <i class="ri-bar-chart-line display-5 d-block mb-2"></i>
                                Aucune inscription enregistrée sur cette période.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card border shadow-sm h-100 mb-0">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0">À traiter</h5>
                        <p class="text-muted fs-13 mb-0">Ce qui demande une action de votre part</p>
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach ([
                            ['ri-bank-card-line', 'Inscriptions en attente de paiement', $nombreInscriptionsEnAttente, route('admin.inscriptions.index', ['statut_paiement' => ['en_attente']])],
                            ['ri-user-star-line', 'Classes à venir ou en cours sans formateur', $classesSansFormateur, route('admin.classes.index', ['sans_formateur' => 1])],
                            ['ri-lock-line', 'Classes terminées à fermer', $classesAFermer, route('admin.classes.index', ['etat' => ['terminee']])],
                        ] as [$icone, $libelle, $nombre, $lien])
                            <a href="{{ $lien }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-3">
                                <i class="{{ $icone }} text-primary fs-5 flex-shrink-0"></i>
                                <span class="flex-grow-1 overflow-hidden text-truncate" title="{{ $libelle }}">{{ $libelle }}</span>
                                @if ($nombre > 0)
                                    <span class="badge bg-warning-subtle text-warning flex-shrink-0">{{ $nombre }}</span>
                                @else
                                    <span class="badge bg-success-subtle text-success flex-shrink-0"><i class="ri-check-line"></i></span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Prochaines classes + inscriptions à encaisser ===== --}}
        <div class="row g-3 mb-3">
            <div class="col-xl-7">
                <div class="card border shadow-sm h-100 mb-0">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center gap-2">
                        <div>
                            <h5 class="card-title mb-0">Prochaines classes</h5>
                            <p class="text-muted fs-13 mb-0">Les 5 prochaines dates de formation</p>
                        </div>
                        <a href="{{ route('admin.classes.index') }}" class="text-primary flex-shrink-0">Tout voir</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0 table-liste">
                            <thead>
                                <tr class="table-light">
                                    <th>Cours</th>
                                    <th>Classe</th>
                                    <th class="col-date">Début</th>
                                    <th class="col-nombre">Inscrits</th>
                                    <th class="text-end col-coche"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($prochainesClasses as $classe)
                                    <tr>
                                        <td class="fw-semibold" title="{{ $classe->cours->titre }}">{{ $classe->cours->titre }}</td>
                                        <td title="{{ $classe->nom }}">
                                            {{ $classe->nom }}
                                            @if ($classe->session_id)
                                                <span class="badge bg-info-subtle text-info ms-1">Entreprise</span>
                                            @endif
                                        </td>
                                        <td>{{ $classe->date_debut->format('d/m/Y') }}</td>
                                        <td><span class="badge bg-primary-subtle text-primary">{{ $classe->inscriptions_count }} / {{ $classe->capacite_max }}</span></td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.cours.classes.gerer', [$classe->cours, $classe]) }}"
                                               class="btn btn-sm btn-soft-primary" title="Gérer la classe" aria-label="Gérer {{ $classe->nom }}">
                                                <i class="ri-team-line"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-5">
                                            <i class="ri-calendar-line display-5 d-block mb-2"></i>
                                            Aucune classe à venir n'est planifiée.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-xl-5">
                <div class="card border shadow-sm h-100 mb-0">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center gap-2">
                        <div>
                            <h5 class="card-title mb-0">À encaisser</h5>
                            <p class="text-muted fs-13 mb-0">{{ $nombreInscriptionsEnAttente }} inscription(s) en attente de paiement</p>
                        </div>
                        <a href="{{ route('admin.inscriptions.index', ['statut_paiement' => ['en_attente']]) }}" class="text-primary flex-shrink-0">Tout voir</a>
                    </div>
                    <div class="list-group list-group-flush">
                        @forelse ($inscriptionsEnAttente as $inscription)
                            <div class="list-group-item d-flex justify-content-between align-items-center gap-2">
                                <div class="overflow-hidden">
                                    <div class="fw-medium text-truncate" title="{{ $inscription->user->name }}">{{ $inscription->user->name }}</div>
                                    <div class="text-muted fs-12 text-truncate" title="{{ $inscription->classe->cours->titre }} · {{ $inscription->classe->nom }}">
                                        {{ $inscription->classe->cours->titre }} · {{ $inscription->classe->nom }}
                                    </div>
                                </div>
                                <form action="{{ route('paiements.store', $inscription) }}" method="POST" class="m-0 flex-shrink-0">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-soft-success" title="Enregistrer le paiement">
                                        <i class="ri-bank-card-line me-1"></i>Encaisser
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="text-center text-muted py-5">
                                <i class="ri-checkbox-circle-line display-5 d-block mb-2"></i>
                                Aucune inscription n'attend de paiement.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Calendrier ===== --}}
        @include('partials.calendrier-classes', [
            'evenements' => $evenementsCalendrier,
            'titre' => 'Calendrier des classes',
            'simple' => true,
        ])
    </div>
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/dashboard-admin.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection