@extends('layouts.master')

@section('title', 'Évaluations')

@section('content')

@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('dashboard') }}">Administration</a>
    @endslot
    @slot('title')
        Évaluations
    @endslot
@endcomponent

<div class="container-fluid pb-4">

    <div class="row g-2 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0"><span class="avatar-title rounded-3 bg-primary-subtle text-primary fs-4"><i class="ri-user-star-line"></i></span></span>
                    <div><h5 class="fw-bold mb-0">{{ $stats['total_avis'] }}</h5><p class="text-muted mb-0 fs-12">Avis formateurs</p></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0"><span class="avatar-title rounded-3 bg-warning-subtle text-warning fs-4"><i class="ri-star-line"></i></span></span>
                    <div><h5 class="fw-bold mb-0">{{ $stats['moyenne_avis'] !== null ? number_format($stats['moyenne_avis'], 1, ',', '') . ' / 5' : '—' }}</h5><p class="text-muted mb-0 fs-12">Note formateurs</p></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0"><span class="avatar-title rounded-3 bg-success-subtle text-success fs-4"><i class="ri-file-list-3-line"></i></span></span>
                    <div><h5 class="fw-bold mb-0">{{ $stats['total_evaluations'] }}</h5><p class="text-muted mb-0 fs-12">Fiches de formation</p></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0"><span class="avatar-title rounded-3 bg-info-subtle text-info fs-4"><i class="ri-bar-chart-line"></i></span></span>
                    <div><h5 class="fw-bold mb-0">{{ $stats['moyenne_evaluations'] !== null ? number_format($stats['moyenne_evaluations'], 1, ',', '') . ' / 5' : '—' }}</h5><p class="text-muted mb-0 fs-12">Note formations</p></div>
                </div>
            </div>
        </div>
    </div>

    @php
        $sans = fn (array $cles) => route('admin.evaluations.index', request()->except(array_merge($cles, ['page'])));
        $etiquettes = [];

        if (request()->filled('formateur')) {
            $etiquettes[] = ['Formateur : ' . ($formateursListe->firstWhere('id', (int) request('formateur'))?->name ?? '—'), ['formateur'], 'ri-user-star-line'];
        }
        if (request()->filled('classe')) {
            $etiquettes[] = ['Classe : ' . ($classesListe->firstWhere('id', (int) request('classe'))?->nom ?? '—'), ['classe'], 'ri-group-line'];
        }
        if (request()->filled('date_du') || request()->filled('date_au')) {
            $du = request()->filled('date_du') ? \Carbon\Carbon::parse(request('date_du'))->format('d/m/Y') : '…';
            $au = request()->filled('date_au') ? \Carbon\Carbon::parse(request('date_au'))->format('d/m/Y') : '…';
            $etiquettes[] = ['Période : ' . $du . ' → ' . $au, ['date_du', 'date_au'], 'ri-calendar-line'];
        }
    @endphp

    <form method="GET" action="{{ route('admin.evaluations.index') }}" id="formRecherche">
        <input type="hidden" name="onglet" id="ongletActif" value="{{ $onglet }}">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
            <ul class="nav nav-pills gap-1 mb-0 flex-shrink-0" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $onglet === 'formateurs' ? 'active' : '' }}" type="button"
                            data-bs-toggle="tab" data-bs-target="#ongletFormateurs" data-onglet="formateurs" role="tab">
                        <i class="ri-user-star-line me-1"></i>Formateurs
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $onglet === 'formations' ? 'active' : '' }}" type="button"
                            data-bs-toggle="tab" data-bs-target="#ongletFormations" data-onglet="formations" role="tab">
                        <i class="ri-book-open-line me-1"></i>Formations
                    </button>
                </li>
            </ul>

            <div class="d-flex justify-content-lg-end flex-grow-1">
                <div class="d-flex gap-2 align-items-start w-100" style="max-width: 650px;">
                    <div class="form-control h-auto d-flex flex-wrap align-items-center gap-1 py-1 shadow-sm flex-grow-1">
                        <i class="ri-search-line text-primary me-1"></i>
                        @foreach ($etiquettes as [$texte, $retirer, $icone])
                            <span class="badge bg-primary-subtle text-primary d-inline-flex align-items-center gap-1 fs-12 py-2">
                                <i class="{{ $icone }}"></i><span>{{ $texte }}</span>
                                <a href="{{ $sans($retirer) }}" class="text-primary ms-1" title="Retirer ce filtre" aria-label="Retirer le filtre {{ $texte }}"><i class="ri-close-line"></i></a>
                            </span>
                        @endforeach
                        <input type="search" name="q" value="{{ request('q') }}" class="form-control border-0 shadow-none flex-grow-1 w-auto p-1"
                               placeholder="Rechercher un stagiaire, formateur, cours ou classe…" aria-label="Rechercher dans les évaluations">
                    </div>
                    <button type="button" class="btn btn-light border shadow-sm flex-shrink-0" data-bs-toggle="modal" data-bs-target="#modalFiltres" aria-label="Ouvrir les filtres">
                        <i class="ri-equalizer-line text-primary me-1"></i>Filtres
                        @if (count($etiquettes))<span class="badge bg-primary ms-1">{{ count($etiquettes) }}</span>@endif
                    </button>
                </div>
            </div>
            <button type="submit" class="visually-hidden" tabindex="-1" aria-hidden="true">Rechercher</button>
        </div>

        <div class="modal fade" id="modalFiltres" tabindex="-1" aria-labelledby="titreFiltres" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-semibold" id="titreFiltres"><i class="ri-equalizer-line text-primary me-2"></i>Filtres des évaluations</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <label for="filtre-formateur" class="form-label fw-semibold">Formateur</label>
                        <select name="formateur" id="filtre-formateur" class="form-select mb-3">
                            <option value="">Tous les formateurs</option>
                            @foreach ($formateursListe as $formateur)
                                <option value="{{ $formateur->id }}" @selected((string) request('formateur') === (string) $formateur->id)>{{ $formateur->name }}</option>
                            @endforeach
                        </select>

                        <label for="filtre-classe" class="form-label fw-semibold">Classe</label>
                        <select name="classe" id="filtre-classe" class="form-select mb-3">
                            <option value="">Toutes les classes</option>
                            @foreach ($classesListe as $classe)
                                <option value="{{ $classe->id }}" @selected((string) request('classe') === (string) $classe->id)>{{ $classe->nom }} · {{ $classe->cours->titre }}</option>
                            @endforeach
                        </select>

                        <label class="form-label fw-semibold">Période des évaluations</label>
                        <div class="row g-2">
                            <div class="col-6"><input type="date" name="date_du" value="{{ request('date_du') }}" class="form-control" aria-label="Date de début"></div>
                            <div class="col-6"><input type="date" name="date_au" value="{{ request('date_au') }}" class="form-control" aria-label="Date de fin"></div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <a href="{{ route('admin.evaluations.index', ['onglet' => $onglet]) }}" class="btn btn-outline-secondary"><i class="ri-refresh-line me-1"></i>Réinitialiser</a>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary"><i class="ri-check-line me-1"></i>Appliquer</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="tab-content">

        {{-- ====================== FORMATEURS ====================== --}}
        <div class="tab-pane fade {{ $onglet === 'formateurs' ? 'show active' : '' }}" id="ongletFormateurs" role="tabpanel">

            <div class="card border shadow-sm">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="card-title mb-0">
                        Avis reçus ({{ $avis->count() }})
                        @if ($formateurFiltre)
                            <span class="text-muted fs-13 fw-normal">· {{ $formateurFiltre->name }}</span>
                        @endif
                    </h5>
                    @if ($formateurFiltre)
                        <a href="{{ route('admin.evaluations.index') }}" class="btn btn-light btn-sm">
                            <i class="ri-close-line me-1"></i>Retirer le filtre
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Stagiaire</th>
                                    <th>Formateur</th>
                                    <th>Formation / classe</th>
                                    <th>Note moyenne</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($avis as $unAvis)
                                    <tr>
                                        <td class="text-nowrap">{{ $unAvis->created_at->format('d/m/Y') }}</td>
                                        <td class="fw-semibold">{{ $unAvis->user?->name ?? 'Utilisateur supprimé' }}</td>
                                        <td>{{ $unAvis->classe->formateur?->name ?? '—' }}</td>
                                        <td>
                                            {{ $unAvis->classe->cours->titre }}
                                            <div class="text-muted fs-13">{{ $unAvis->classe->nom }}</div>
                                        </td>
                                        <td class="text-nowrap">
                                            @include('cours.partials.etoiles', ['note' => $unAvis->note, 'taille' => 'fs-6'])
                                            <span class="ms-1">{{ number_format($unAvis->note, 1, ',', '') }} / 5</span>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-soft-primary" data-bs-toggle="modal"
                                                    data-bs-target="#modalAvis{{ $unAvis->id }}" title="Voir les détails de l’avis"
                                                    aria-label="Voir l’avis de {{ $unAvis->user?->name ?? 'l’utilisateur' }}">
                                                <i class="ri-eye-line"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">Aucun avis à afficher.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @foreach ($avis as $unAvis)
                        <div class="modal fade" id="modalAvis{{ $unAvis->id }}" tabindex="-1" aria-labelledby="titreAvis{{ $unAvis->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <div>
                                            <h5 class="modal-title fw-semibold" id="titreAvis{{ $unAvis->id }}">Avis de {{ $unAvis->user?->name ?? 'Utilisateur supprimé' }}</h5>
                                            <div class="text-muted fs-13">{{ $unAvis->classe->formateur?->name ?? '—' }} · {{ $unAvis->classe->cours->titre }} · {{ $unAvis->classe->nom }}</div>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                    </div>
                                    <div class="modal-body">
                                        @include('evaluations.partials.lignes-notes', [
                                            'criteres' => \App\Models\AvisFormateur::CRITERES,
                                            'source' => $unAvis,
                                        ])
                                        @if ($unAvis->commentaire)
                                            <hr>
                                            <h6 class="fw-semibold">Commentaire</h6>
                                            <p class="text-muted mb-0">{!! nl2br(e($unAvis->commentaire)) !!}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ====================== FORMATIONS ====================== --}}
        <div class="tab-pane fade {{ $onglet === 'formations' ? 'show active' : '' }}" id="ongletFormations" role="tabpanel">

            <div class="card border shadow-sm">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="card-title mb-0">
                        Fiches reçues ({{ $evaluations->count() }})
                        @if ($classeFiltre)
                            <span class="text-muted fs-13 fw-normal">· {{ $classeFiltre->nom }}</span>
                        @endif
                    </h5>
                    @if ($classeFiltre)
                        <a href="{{ route('admin.evaluations.index', ['onglet' => 'formations']) }}" class="btn btn-light btn-sm">
                            <i class="ri-close-line me-1"></i>Retirer le filtre
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Stagiaire</th>
                                    <th>Formation / classe</th>
                                    <th>Note moyenne</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($evaluations as $evaluation)
                                    <tr>
                                        <td class="text-nowrap">{{ $evaluation->created_at->format('d/m/Y') }}</td>
                                        <td class="fw-semibold">{{ $evaluation->user?->name ?? 'Utilisateur supprimé' }}</td>
                                        <td>
                                            {{ $evaluation->classe->cours->titre }}
                                            <div class="text-muted fs-13">{{ $evaluation->classe->nom }}</div>
                                        </td>
                                        <td class="text-nowrap">
                                            @include('cours.partials.etoiles', ['note' => $evaluation->note, 'taille' => 'fs-6'])
                                            <span class="ms-1">{{ number_format($evaluation->note, 1, ',', '') }} / 5</span>
                                        </td>
                                        <td class="text-end">
                                                <button type="button" class="btn btn-sm btn-soft-primary" data-bs-toggle="modal"
                                                    data-bs-target="#modalEvaluation{{ $evaluation->id }}" title="Voir la fiche d’évaluation"
                                                    aria-label="Voir la fiche de {{ $evaluation->user?->name ?? 'l’utilisateur' }}">
                                                <i class="ri-eye-line"></i>
                                                </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">Aucune fiche à afficher.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @foreach ($evaluations as $evaluation)
                        <div class="modal fade" id="modalEvaluation{{ $evaluation->id }}" tabindex="-1" aria-labelledby="titreEvaluation{{ $evaluation->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <div>
                                            <h5 class="modal-title fw-semibold" id="titreEvaluation{{ $evaluation->id }}">Fiche de {{ $evaluation->user?->name ?? 'Utilisateur supprimé' }}</h5>
                                            <div class="text-muted fs-13">{{ $evaluation->classe->cours->titre }} · {{ $evaluation->classe->nom }}</div>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
                                            <span><strong>Formateur :</strong> {{ $evaluation->classe->formateur?->name ?? '—' }}</span>
                                            <span><strong>Reçue le :</strong> {{ $evaluation->created_at->format('d/m/Y à H:i') }}</span>
                                        </div>
                                        @foreach (\App\Models\EvaluationFormation::CRITERES as $section => $criteres)
                                            <h6 class="fw-semibold mt-3">{{ $section }}</h6>
                                            @include('evaluations.partials.lignes-notes', ['criteres' => $criteres, 'source' => $evaluation])
                                        @endforeach
                                        <hr>
                                        <h6 class="fw-semibold">Remarques</h6>
                                        @foreach (\App\Models\EvaluationFormation::TEXTES as $champ => $titre)
                                            <div class="mb-2">
                                                <div class="fw-medium">{{ $titre }}</div>
                                                @if ($evaluation->{$champ})
                                                    <p class="text-muted mb-0">{!! nl2br(e($evaluation->{$champ})) !!}</p>
                                                @else
                                                    <p class="text-muted fst-italic mb-0">Non renseigné</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="tab"][data-onglet]').forEach(function (onglet) {
        onglet.addEventListener('shown.bs.tab', function () {
            document.getElementById('ongletActif').value = onglet.dataset.onglet;
        });
    });
});
</script>
@endsection