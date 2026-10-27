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

    <div class="nv-dash">
        <div class="row g-3">
            @include('dashboards.partials.carte-stat', [
                'largeur' => 'col-xl-3 col-sm-6',
                'titre' => 'Utilisateurs',
                'icone' => 'users',
                'couleur' => 'primary',
                'lien' => route('admin.users.index'),
                'valeur' => $totalUtilisateurs,
            ])

            @include('dashboards.partials.carte-stat', [
                'largeur' => 'col-xl-3 col-sm-6',
                'titre' => 'Cours',
                'icone' => 'book-open',
                'couleur' => 'primary',
                'lien' => route('admin.cours.index'),
                'valeur' => $totalCours,
            ])

            @include('dashboards.partials.carte-stat', [
                'largeur' => 'col-xl-3 col-sm-6',
                'titre' => 'Classes',
                'icone' => 'calendar',
                'couleur' => 'primary',
                'lien' => route('admin.classes.index'),
                'valeur' => $totalClasses,
            ])

            @include('dashboards.partials.carte-stat', [
                'largeur' => 'col-xl-3 col-sm-6',
                'titre' => 'Inscriptions',
                'icone' => 'user-check',
                'couleur' => 'danger',
                'lien' => route('admin.inscriptions.index'),
                'valeur' => $totalInscriptions,
                'detail' => $nombreInscriptionsEnAttente . ' en attente',
            ])
        </div>

        <div class="row g-3 mb-4">
            <div class="col-xl-8">
                <div class="card h-100 mb-0">
                    <div class="card-header d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="card-title mb-1">Inscriptions par mois</h5>
                            <p class="text-muted mb-0">Évolution sur les 9 derniers mois</p>
                        </div>
                        <i class="ri-bar-chart-grouped-line text-primary fs-4"></i>
                    </div>
                    <div class="card-body">
                        @if ($inscriptionsSurPeriode > 0)
                            <div data-nv-dash-inscriptions
                                 data-labels="{{ json_encode($inscriptionsParMois['labels']) }}"
                                 data-inscriptions="{{ json_encode($inscriptionsParMois['inscriptions']) }}"
                                 data-payees="{{ json_encode($inscriptionsParMois['payees']) }}"></div>
                        @else
                            <div class="text-center text-muted py-5">
                                <i class="ri-bar-chart-line display-5 d-block mb-2 text-primary"></i>
                                Aucune inscription enregistrée sur cette période.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card h-100 mb-0">
                    <div class="card-header d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="card-title mb-1">Paiements</h5>
                            <p class="text-muted mb-0">Statut des inscriptions</p>
                        </div>
                        <i class="ri-pie-chart-2-line text-primary fs-4"></i>
                    </div>
                    <div class="card-body">
                        @if ($totalInscriptions > 0)
                            <div data-nv-dash-paiements
                                 data-payees="{{ $nombreInscriptionsPayees }}"
                                 data-attente="{{ $nombreInscriptionsEnAttente }}"
                                 data-pourcentage="{{ $pourcentageInscriptionsPayees }}"></div>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="ri-pie-chart-line display-5 d-block mb-2 text-primary"></i>
                                Aucune inscription à représenter.
                            </div>
                        @endif

                        <div class="d-grid gap-2 mt-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="ri-checkbox-blank-circle-fill text-primary me-2"></i>Payées</span>
                                <strong>{{ $nombreInscriptionsPayees }}</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="ri-checkbox-blank-circle-fill text-danger me-2"></i>En attente</span>
                                <strong>{{ $nombreInscriptionsEnAttente }}</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="ri-checkbox-blank-circle-fill text-primary me-2"></i>Classes en cours</span>
                                <strong>{{ $classesEnCours }}</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="ri-checkbox-blank-circle-fill text-primary me-2"></i>Classes à venir</span>
                                <strong>{{ $classesAVenir }}</strong>
                            </div>
                        </div>

                        <a href="{{ route('admin.inscriptions.index') }}" class="btn btn-danger rounded-pill w-100 mt-3">
                            Voir les inscriptions
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-xl-7">
                @include('partials.calendrier-classes', [
                    'evenements' => $evenementsCalendrier,
                    'titre' => 'Calendrier des classes',
                    'simple' => true,
                ])
            </div>

            <div class="col-xl-5">
                <div class="card h-100 mb-0">
                    <div class="card-header d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="card-title mb-1">Inscriptions en attente de paiement</h5>
                            <p class="text-muted mb-0">{{ $nombreInscriptionsEnAttente }} en attente</p>
                        </div>
                        <a href="{{ route('admin.inscriptions.index', ['statut_paiement' => ['en_attente']]) }}"
                           class="text-primary">Tout voir</a>
                    </div>
                    <div class="card-body">
                        @forelse ($inscriptionsEnAttente as $inscription)
                            <div class="d-flex justify-content-between align-items-center gap-2 {{ $loop->last ? '' : 'border-bottom pb-3 mb-3' }}">
                                <div class="min-w-0">
                                    <h6 class="mb-1">{{ $inscription->user->name }}</h6>
                                    <p class="text-muted mb-1 text-break">
                                        {{ $inscription->classe->cours->titre }} · {{ $inscription->classe->nom }}
                                    </p>
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($inscription->date_inscription)->format('d/m/Y') }}
                                    </small>
                                </div>
                                <form action="{{ route('paiements.store', $inscription) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger rounded-pill">
                                        Encaisser
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                <i class="ri-checkbox-circle-line display-5 d-block mb-2 text-primary"></i>
                                Aucune inscription n’attend de paiement.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-xl-8">
                <div class="card h-100 mb-0">
                    <div class="card-header d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="card-title mb-1">Prochaines classes</h5>
                            <p class="text-muted mb-0">Les 5 prochaines dates de formation</p>
                        </div>
                        <i class="ri-calendar-event-line text-primary fs-4"></i>
                    </div>
                    <div class="card-body">
                        @forelse ($prochainesClasses as $classe)
                            <div class="d-flex justify-content-between align-items-center gap-3 {{ $loop->last ? '' : 'border-bottom pb-3 mb-3' }}">
                                <div class="min-w-0">
                                    <h6 class="mb-1">{{ $classe->cours->titre }} · {{ $classe->nom }}</h6>
                                    <p class="text-muted mb-1 text-break">
                                        {{ $classe->date_debut->format('d/m/Y') }} – {{ $classe->date_fin->format('d/m/Y') }}
                                        · {{ $classe->lieu }}
                                    </p>
                                    <small class="text-muted">
                                        Formateur : {{ $classe->formateur?->name ?? 'Non affecté' }}
                                        · {{ $classe->inscriptions_count }} / {{ $classe->capacite_max }} inscrits
                                    </small>
                                </div>
                                <a href="{{ route('admin.cours.classes.show', [$classe->cours, $classe]) }}"
                                   class="btn btn-sm btn-soft-primary flex-shrink-0" title="Voir la classe"
                                   aria-label="Voir {{ $classe->nom }}">
                                    <i class="ri-eye-line"></i>
                                </a>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                <i class="ri-calendar-line display-5 d-block mb-2 text-primary"></i>
                                Aucune classe à venir n’est planifiée.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card h-100 mb-0">
                    <div class="card-header d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="card-title mb-1">Dernières inscriptions</h5>
                            <p class="text-muted mb-0">Les 5 plus récentes</p>
                        </div>
                        <i class="ri-user-add-line text-primary fs-4"></i>
                    </div>
                    <div class="card-body">
                        @forelse ($dernieresInscriptions as $inscription)
                            <div class="d-flex justify-content-between align-items-center gap-2 {{ $loop->last ? '' : 'border-bottom pb-3 mb-3' }}">
                                <div class="min-w-0">
                                    <h6 class="mb-1">{{ $inscription->user->name }}</h6>
                                    <p class="text-muted mb-1 text-break">
                                        {{ $inscription->classe->cours->titre }} · {{ $inscription->classe->nom }}
                                    </p>
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($inscription->date_inscription)->format('d/m/Y') }}
                                    </small>
                                </div>
                                @if ($inscription->paiement?->statut === 'valide')
                                    <span class="badge bg-primary">Payé</span>
                                @else
                                    <span class="badge bg-danger">En attente</span>
                                @endif
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                <i class="ri-user-add-line display-5 d-block mb-2 text-primary"></i>
                                Aucune inscription enregistrée.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/dashboard-admin.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
