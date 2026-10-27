@extends('layouts.master')

@section('title', 'Mes classes')

@section('content')

{{-- ===== Fil d'Ariane ===== --}}
@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('dashboard') }}">Cours</a>
    @endslot
    @slot('title')
        Mes classes
    @endslot
@endcomponent

<div class="container-fluid pb-4">

    {{-- ===== En-tête ===== --}}
    <div class="bg-primary bg-gradient text-white rounded-4 p-4 p-md-5 mb-4">
        <span class="badge rounded-pill bg-white bg-opacity-25 text-white px-3 py-2 mb-3">
            <i class="ri-graduation-cap-line me-1"></i> Mon parcours
        </span>
        <h3 class="fw-bold text-white mb-2">Mes classes</h3>
        <p class="text-white opacity-75 mb-0 col-lg-8 px-0">
            Retrouvez vos classes en cours, celles à venir et votre historique.
        </p>
    </div>

    {{-- ===== Onglets ===== --}}
    <div class="card border shadow-sm mb-3">
        <div class="card-body p-2">
            <ul class="nav nav-pills flex-nowrap overflow-auto gap-1" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active text-nowrap" type="button"
                            data-bs-toggle="tab" data-bs-target="#encours" role="tab">
                        <i class="ri-book-open-line me-1"></i> En cours
                        <span class="badge bg-danger ms-1">{{ $inscriptionsEnCours->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-nowrap" type="button"
                            data-bs-toggle="tab" data-bs-target="#prochaines" role="tab">
                        <i class="ri-time-line me-1"></i> À venir
                        <span class="badge bg-danger ms-1">{{ $prochainesFormations->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-nowrap" type="button"
                            data-bs-toggle="tab" data-bs-target="#historique" role="tab">
                        <i class="ri-archive-line me-1"></i> Terminées
                        <span class="badge bg-danger ms-1">{{ $anciensCours->count() }}</span>
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <div class="tab-content">

        {{-- ---------- En cours ---------- --}}
        <div class="tab-pane fade show active" id="encours" role="tabpanel">
            @forelse($inscriptionsEnCours as $inscription)
                <div class="card border shadow-sm mb-3">
                    <div class="card-body p-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

                            <div class="d-flex align-items-center gap-3">
                                <span class="avatar-md flex-shrink-0">
                                    <span class="avatar-title rounded-3 bg-primary-subtle text-primary fs-2">
                                        <i class="ri-book-open-line"></i>
                                    </span>
                                </span>
                                <div>
                                    <h5 class="fw-bold mb-1">{{ $inscription->classe->cours->titre }}</h5>
                                    <div class="text-muted small">
                                        <i class="ri-graduation-cap-line text-danger me-1"></i>
                                        Classe : {{ $inscription->classe->cours->titre }} — {{ $inscription->classe->nom }}
                                    </div>
                                    <div class="text-muted small">
                                        <i class="ri-calendar-event-line text-danger me-1"></i>
                                        Du {{ $inscription->classe->date_debut->format('d/m/Y') }}
                                        au {{ $inscription->classe->date_fin->format('d/m/Y') }}
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex flex-md-column align-items-md-end gap-2">
                                <span class="badge bg-success-subtle text-success px-3 py-2">
                                    <i class="ri-checkbox-circle-line me-1"></i>Cours en cours
                                </span>
                                <a href="{{ route('cours.espace', $inscription->classe->cours) }}"
                                   class="btn btn-danger btn-sm text-nowrap">
                                    Accéder au cours <i class="ri-arrow-right-line ms-1"></i>
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            @empty
                <div class="card border shadow-sm">
                    <div class="card-body text-center text-muted py-5">
                        <i class="ri-book-open-line display-5 d-block mb-2"></i>
                        Aucune classe en cours.
                    </div>
                </div>
            @endforelse
        </div>

        {{-- ---------- À venir ---------- --}}
        <div class="tab-pane fade" id="prochaines" role="tabpanel">
            @forelse($prochainesFormations as $inscription)
                <div class="card border shadow-sm mb-3">
                    <div class="card-body p-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

                            <div class="d-flex align-items-center gap-3">
                                <span class="avatar-md flex-shrink-0">
                                    <span class="avatar-title rounded-3 bg-warning-subtle text-warning fs-2">
                                        <i class="ri-calendar-line"></i>
                                    </span>
                                </span>
                                <div>
                                    <h5 class="fw-bold mb-1">{{ $inscription->classe->cours->titre }}</h5>
                                    <div class="text-muted small">
                                        <i class="ri-graduation-cap-line text-danger me-1"></i>
                                        Classe : {{ $inscription->classe->cours->titre }} — {{ $inscription->classe->nom }}
                                    </div>
                                    <div class="text-muted small">
                                        <i class="ri-calendar-event-line text-danger me-1"></i>
                                        Début : {{ $inscription->classe->date_debut->format('d/m/Y') }}
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex flex-md-column align-items-md-end gap-2">
                                <span class="badge bg-warning-subtle text-warning px-3 py-2">
                                    <i class="ri-time-line me-1"></i>À venir
                                </span>
                                <a href="{{ route('cours.show', $inscription->classe->cours) }}"
                                   class="btn btn-primary btn-sm text-nowrap">
                                    Voir le cours <i class="ri-arrow-right-line ms-1"></i>
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            @empty
                <div class="card border shadow-sm">
                    <div class="card-body text-center text-muted py-5">
                        <i class="ri-calendar-line display-5 d-block mb-2"></i>
                        Aucune prochaine classe.
                    </div>
                </div>
            @endforelse
        </div>

        {{-- ---------- Terminées ---------- --}}
        <div class="tab-pane fade" id="historique" role="tabpanel">
            @forelse($anciensCours as $inscription)
                <div class="card border shadow-sm mb-3">
                    <div class="card-body p-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

                            <div class="d-flex align-items-center gap-3">
                                <span class="avatar-md flex-shrink-0">
                                    <span class="avatar-title rounded-3 bg-secondary-subtle text-secondary fs-2">
                                        <i class="ri-archive-line"></i>
                                    </span>
                                </span>
                                <div>
                                    <h5 class="fw-bold mb-1">{{ $inscription->classe->cours->titre }}</h5>
                                    <div class="text-muted small">
                                        <i class="ri-graduation-cap-line text-danger me-1"></i>
                                        Classe : {{ $inscription->classe->cours->titre }} — {{ $inscription->classe->nom }}
                                    </div>
                                    <div class="text-muted small">
                                        <i class="ri-calendar-event-line text-danger me-1"></i>
                                        Du {{ $inscription->classe->date_debut->format('d/m/Y') }}
                                        au {{ $inscription->classe->date_fin->format('d/m/Y') }}
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex flex-md-column align-items-md-end gap-2">
                                
                                <a href="{{ route('cours.show', $inscription->classe->cours) }}"
                                   class="btn btn-outline-primary btn-sm text-nowrap">
                                    Revoir le cours <i class="ri-arrow-right-line ms-1"></i>
                                </a>

                                {{-- Attestation de formation : téléchargeable si éligible, sinon explication --}}
                                @if ($inscription->attestation)
                                {{-- Œil (voir dans un nouvel onglet) + bouton de téléchargement, côte à côte --}}
                                <div class="d-flex align-items-center gap-2">
                                    <a href="{{ route('attestations.voir', $inscription->attestation) }}"
                                    target="_blank" rel="noopener"
                                    class="btn btn-sm btn-soft-primary"
                                    title="Voir mon attestation" aria-label="Voir mon attestation">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                    <a href="{{ route('attestations.telecharger', $inscription->attestation) }}"
                                    class="btn btn-success btn-sm text-nowrap">
                                        <i class="ri-download-2-line me-1"></i>Télécharger mon attestation
                                    </a>
                                </div>
                                @elseif ($inscription->attestation_eligible === false)
                                    <span class="badge bg-warning-subtle text-warning px-3 py-2"
                                        title="Un taux de présence d'au moins {{ \App\Models\Classe::SEUIL_ATTESTATION }} % est requis">
                                        <i class="ri-close-circle-line me-1"></i>Attestation non délivrée
                                        ({{ rtrim(rtrim(number_format($inscription->taux_presence, 2, ',', ''), '0'), ',') }} % de présence)
                                    </span>
                                @else
                                    <span class="badge bg-info-subtle text-info px-3 py-2">
                                        <i class="ri-time-line me-1"></i>Attestation après clôture de la classe
                                    </span>
                                @endif

                                {{-- Évaluation de fin de formation : remplir, puis modifier si déjà envoyée --}}
                                @if (in_array($inscription->classe_id, $classesEvaluees))
                                    <a href="{{ route('evaluations.create', $inscription->classe) }}"
                                       class="btn btn-outline-success btn-sm text-nowrap">
                                        <i class="ri-edit-line me-1"></i>Modifier mon évaluation
                                    </a>
                                @else
                                    <a href="{{ route('evaluations.create', $inscription->classe) }}"
                                       class="btn btn-danger btn-sm text-nowrap">
                                        <i class="ri-star-line me-1"></i>Évaluer la formation
                                    </a>
                                @endif

                            </div>

                        </div>
                    </div>
                </div>
            @empty
                <div class="card border shadow-sm">
                    <div class="card-body text-center text-muted py-5">
                        <i class="ri-archive-line display-5 d-block mb-2"></i>
                        Aucune classe terminée.
                    </div>
                </div>
            @endforelse
        </div>

    </div>
</div>

@endsection