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

    {{-- ===== Onglets : Formateurs / Formations (l'onglet actif est choisi par le contrôleur) ===== --}}
    <ul class="nav nav-pills gap-1 mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $onglet === 'formateurs' ? 'active' : '' }}" type="button"
                    data-bs-toggle="tab" data-bs-target="#ongletFormateurs" role="tab">
                <i class="ri-user-star-line me-1"></i>Formateurs
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $onglet === 'formations' ? 'active' : '' }}" type="button"
                    data-bs-toggle="tab" data-bs-target="#ongletFormations" role="tab">
                <i class="ri-book-open-line me-1"></i>Formations
            </button>
        </li>
    </ul>

    <div class="tab-content">

        {{-- ====================== FORMATEURS ====================== --}}
        <div class="tab-pane fade {{ $onglet === 'formateurs' ? 'show active' : '' }}" id="ongletFormateurs" role="tabpanel">

            <div class="card border shadow-sm mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Synthèse par formateur</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Formateur</th>
                                    <th class="text-center">Avis</th>
                                    <th>Note moyenne</th>
                                    <th class="text-end"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($synthesesFormateurs as $ligne)
                                    <tr>
                                        <td class="fw-medium">{{ $ligne['formateur']->name }}</td>
                                        <td class="text-center">{{ $ligne['nombre'] }}</td>
                                        <td class="text-nowrap">
                                            @include('cours.partials.etoiles', ['note' => $ligne['moyennes']['globale'], 'taille' => 'fs-6'])
                                            <span class="ms-1">{{ number_format($ligne['moyennes']['globale'], 1, ',', '') }}</span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.evaluations.index', ['formateur' => $ligne['formateur']->id]) }}"
                                               class="btn btn-soft-primary btn-sm">Voir ses avis</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Aucun avis reçu pour le moment.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

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
                    @forelse ($avis as $unAvis)
                        <div class="border rounded-3 p-3 mb-3">
                            <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                                <div>
                                    <span class="fw-semibold">{{ $unAvis->user?->name ?? 'Utilisateur supprimé' }}</span>
                                    <span class="text-muted"> → {{ $unAvis->classe->formateur?->name ?? '—' }}</span>
                                    <div class="text-muted small">{{ $unAvis->classe->cours->titre }} · {{ $unAvis->classe->nom }}</div>
                                </div>
                                <small class="text-muted text-nowrap">{{ $unAvis->created_at->format('d/m/Y') }}</small>
                            </div>

                            @include('evaluations.partials.lignes-notes', [
                                'criteres' => \App\Models\AvisFormateur::CRITERES,
                                'source' => $unAvis,
                            ])

                            @if ($unAvis->commentaire)
                                <p class="text-muted mt-2 mb-0">{!! nl2br(e($unAvis->commentaire)) !!}</p>
                            @endif
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">Aucun avis à afficher.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ====================== FORMATIONS ====================== --}}
        <div class="tab-pane fade {{ $onglet === 'formations' ? 'show active' : '' }}" id="ongletFormations" role="tabpanel">

            <div class="card border shadow-sm mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Synthèse par classe</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Classe</th>
                                    <th>Formation</th>
                                    <th class="text-center">Fiches</th>
                                    <th>Note moyenne</th>
                                    <th class="text-end"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($synthesesClasses as $ligne)
                                    <tr>
                                        <td class="fw-medium">{{ $ligne['classe']->nom }}</td>
                                        <td>{{ $ligne['classe']->cours->titre }}</td>
                                        <td class="text-center">{{ $ligne['nombre'] }}</td>
                                        <td class="text-nowrap">
                                            @include('cours.partials.etoiles', ['note' => $ligne['moyennes']['globale'], 'taille' => 'fs-6'])
                                            <span class="ms-1">{{ number_format($ligne['moyennes']['globale'], 1, ',', '') }}</span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.evaluations.index', ['classe' => $ligne['classe']->id]) }}"
                                               class="btn btn-soft-primary btn-sm">Voir les fiches</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">Aucune évaluation reçue pour le moment.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

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
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Stagiaire</th>
                                    <th>Formation / Classe</th>
                                    <th>Note moyenne</th>
                                    <th class="text-end"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($evaluations as $evaluation)
                                    <tr>
                                        <td class="text-nowrap">{{ $evaluation->created_at->format('d/m/Y') }}</td>
                                        <td class="fw-medium">{{ $evaluation->user?->name ?? 'Utilisateur supprimé' }}</td>
                                        <td>
                                            {{ $evaluation->classe->cours->titre }}
                                            <div class="text-muted fs-13">{{ $evaluation->classe->nom }}</div>
                                        </td>
                                        <td class="text-nowrap">
                                            @include('cours.partials.etoiles', ['note' => $evaluation->note, 'taille' => 'fs-6'])
                                            <span class="ms-1">{{ number_format($evaluation->note, 1, ',', '') }}</span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.evaluations.show', $evaluation) }}" class="btn btn-primary btn-sm">
                                                <i class="ri-eye-line me-1"></i>Voir la fiche
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">Aucune fiche à afficher.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection