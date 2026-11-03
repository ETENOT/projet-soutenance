@extends('layouts.master')

@section('title', 'Attestations')

@section('content')

@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('dashboard') }}">Accueil</a>
    @endslot
    @slot('title')
        Attestations
    @endslot
@endcomponent

<div class="container-fluid pb-4">

    {{-- ===== Statistique ===== --}}
    <div class="row g-2 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-success-subtle text-success fs-4"><i class="ri-award-line"></i></span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $totalAttestations }}</h5>
                        <p class="text-muted mb-0 fs-12">Attestations délivrées</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Erreurs de filtre (période à l'envers...) ===== --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $erreur)
                    <li>{{ $erreur }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        // URL de la liste sans certains paramètres (sert au "×" de chaque étiquette)
        $sans = fn (array $cles) => route('admin.attestations.index', request()->except(array_merge($cles, ['page'])));

        // Étiquettes des filtres actifs : [texte, paramètres à retirer, icône]
        $etiquettes = [];
        if (request()->filled('cours_id')) {
            $etiquettes[] = ['Cours : ' . ($tousLesCours->firstWhere('id', (int) request('cours_id'))->titre ?? '—'), ['cours_id'], 'ri-book-open-line'];
        }
        if (request()->filled('date_du') || request()->filled('date_au')) {
            $du = request()->filled('date_du') ? \Carbon\Carbon::parse(request('date_du'))->format('d/m/Y') : '…';
            $au = request()->filled('date_au') ? \Carbon\Carbon::parse(request('date_au'))->format('d/m/Y') : '…';
            $etiquettes[] = ['Délivrée : ' . $du . ' → ' . $au, ['date_du', 'date_au'], 'ri-calendar-line'];
        }
    @endphp

    {{-- ===== Recherche et filtres (à droite : les attestations se délivrent à la fermeture d'une classe, pas ici) ===== --}}
    <div class="row g-2 justify-content-end align-items-start mb-3">
        <div class="col-12 col-lg-8 col-xl-6">
            <form method="GET" action="{{ route('admin.attestations.index') }}">
                <div class="d-flex gap-2 align-items-start">
                    <div class="form-control h-auto d-flex flex-wrap align-items-center gap-1 py-1 shadow-sm flex-grow-1">
                        <i class="ri-search-line text-primary me-1"></i>

                        @foreach ($etiquettes as [$texte, $retirer, $icone])
                            <span class="badge bg-primary-subtle text-primary d-inline-flex align-items-center gap-1 fs-12 py-2">
                                <i class="{{ $icone }}"></i>
                                <span class="text-truncate" title="{{ $texte }}">{{ $texte }}</span>
                                <a href="{{ $sans($retirer) }}" class="text-primary ms-1" title="Retirer ce filtre" aria-label="Retirer le filtre {{ $texte }}">
                                    <i class="ri-close-line"></i>
                                </a>
                            </span>
                        @endforeach

                        <input type="text" name="q" value="{{ request('q') }}"
                               class="form-control border-0 shadow-none flex-grow-1 w-auto p-1"
                               placeholder="Rechercher (stagiaire, cours ou numéro)…" aria-label="Rechercher une attestation">
                    </div>

                    <button type="button" class="btn btn-light border shadow-sm flex-shrink-0"
                            data-bs-toggle="modal" data-bs-target="#modalFiltres" aria-label="Ouvrir les filtres">
                        <i class="ri-equalizer-line text-primary me-1"></i>Filtres
                        @if (count($etiquettes))
                            <span class="badge bg-primary ms-1">{{ count($etiquettes) }}</span>
                        @endif
                    </button>
                </div>

                {{-- ===== Fenêtre des filtres ===== --}}
                <div class="modal fade" id="modalFiltres" tabindex="-1" aria-labelledby="titreFiltres" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title fw-semibold" id="titreFiltres">
                                    <i class="ri-equalizer-line text-primary me-2"></i>Filtres des attestations
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                            </div>

                            <div class="modal-body">
                                <label for="filtre-cours" class="form-label fw-semibold">
                                    <i class="ri-book-open-line text-primary me-1"></i>Cours
                                </label>
                                <select name="cours_id" id="filtre-cours" class="form-select mb-3" aria-label="Filtrer par cours">
                                    <option value="">Tous les cours</option>
                                    @foreach ($tousLesCours as $unCours)
                                        <option value="{{ $unCours->id }}" @selected((string) request('cours_id') === (string) $unCours->id)>{{ $unCours->titre }}</option>
                                    @endforeach
                                </select>

                                <label class="form-label fw-semibold">
                                    <i class="ri-calendar-line text-primary me-1"></i>Période de délivrance
                                </label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="date" name="date_du" value="{{ request('date_du') }}" class="form-control" aria-label="Délivrée à partir du">
                                    </div>
                                    <div class="col-6">
                                        <input type="date" name="date_au" value="{{ request('date_au') }}" class="form-control" aria-label="Délivrée jusqu'au">
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer justify-content-between">
                                <a href="{{ route('admin.attestations.index') }}" class="btn btn-outline-secondary">
                                    <i class="ri-refresh-line me-1"></i>Réinitialiser
                                </a>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-check-line me-1"></i>Appliquer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="visually-hidden" tabindex="-1" aria-hidden="true">Rechercher</button>
            </form>
        </div>
    </div>

    {{-- ===== Tableau : les plus récentes en premier ===== --}}
    <div class="card border shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0 table-liste">
                <thead>
                    <tr class="table-light">
                        <th style="width: 10rem;">Délivrée le</th>
                        <th style="width: 10rem;">N°</th>
                        <th>Stagiaire</th>
                        <th>Cours</th>
                        <th>Classe</th>
                        <th class="col-nombre">Présence</th>
                        <th class="text-end" style="width: 6.5rem;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attestations as $attestation)
                        <tr>
                            {{-- Date et heure de délivrance (= création de l'attestation) --}}
                            <td>{{ $attestation->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-muted" title="{{ $attestation->numero }}">{{ $attestation->numero }}</td>
                            {{-- Nom figé sur le document (celui qui est imprimé sur le PDF) --}}
                            <td class="fw-medium" title="{{ $attestation->beneficiaire }}">{{ $attestation->beneficiaire }}</td>
                            <td title="{{ $attestation->intitule }}">{{ $attestation->intitule }}</td>
                            <td class="text-muted" title="{{ $attestation->classe?->nom }}">{{ $attestation->classe?->nom ?? '—' }}</td>
                            <td>{{ rtrim(rtrim(number_format($attestation->taux_presence, 2, ',', ''), '0'), ',') }} %</td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    {{-- Voir dans un nouvel onglet --}}
                                    <a href="{{ route('attestations.voir', $attestation) }}" target="_blank" rel="noopener"
                                       class="btn btn-sm btn-soft-primary" title="Voir l’attestation">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                    {{-- Télécharger le PDF --}}
                                    <a href="{{ route('attestations.telecharger', $attestation) }}"
                                       class="btn btn-sm btn-soft-success" title="Télécharger l’attestation">
                                        <i class="ri-download-2-line"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="ri-award-line display-5 d-block mb-2"></i>
                                Aucune attestation ne correspond à votre recherche.
                                @if ($etiquettes || request()->filled('q'))
                                    <a href="{{ route('admin.attestations.index') }}" class="d-block mt-2">Réinitialiser les filtres</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($attestations->total() > 0)
            <div class="card-footer bg-transparent d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <small class="text-muted">
                    Affichage de {{ $attestations->firstItem() }} à {{ $attestations->lastItem() }}
                    sur {{ $attestations->total() }} résultat(s)
                </small>
                {{ $attestations->links() }}
            </div>
        @endif
    </div>
</div>

@endsection
