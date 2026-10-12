@extends('layouts.master')

@section('title', 'Gestion des classes')

@section('content')

{{-- ===== Fil d'Ariane ===== --}}
@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('dashboard') }}">Accueil</a>
    @endslot
    @slot('title')
        Classes
    @endslot
@endcomponent

<div class="container-fluid pb-4">

    {{-- ===== En-tête ===== --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <p class="text-muted mb-0 col-lg-8 px-0">
            Gérez les classes (sessions) de vos formations : créez, modifiez, consultez les détails et suivez leur état.
        </p>

        {{-- Une classe appartient toujours à une formation : on choisit d'abord laquelle --}}
        <div class="dropdown">
            <button class="btn btn-primary fw-semibold dropdown-toggle" type="button"
                    data-bs-toggle="dropdown" aria-expanded="false">
                <i class="ri-add-line me-1"></i> Créer une classe
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><h6 class="dropdown-header">Pour quelle formation ?</h6></li>
                @forelse($tousLesCours as $unCours)
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.cours.classes.create', $unCours) }}">
                            {{ $unCours->titre }}
                        </a>
                    </li>
                @empty
                    <li><span class="dropdown-item-text text-muted">Aucune formation créée.</span></li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- ===== Statistiques ===== --}}
    <div class="row g-3 mb-4">

        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar-md flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-success-subtle text-success fs-2">
                            <i class="ri-play-circle-line"></i>
                        </span>
                    </span>
                    <div>
                        <h4 class="fw-bold mb-0">{{ $stats['en_cours'] }}</h4>
                        <p class="text-muted mb-0">Classes en cours</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar-md flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-warning-subtle text-warning fs-2">
                            <i class="ri-calendar-event-line"></i>
                        </span>
                    </span>
                    <div>
                        <h4 class="fw-bold mb-0">{{ $stats['a_venir'] }}</h4>
                        <p class="text-muted mb-0">Classes à venir</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar-md flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-info-subtle text-info fs-2">
                            <i class="ri-hourglass-line"></i>
                        </span>
                    </span>
                    <div>
                        <h4 class="fw-bold mb-0">{{ $stats['terminees'] }}</h4>
                        <p class="text-muted mb-0">Classes terminées</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar-md flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-primary-subtle text-primary fs-2">
                            <i class="ri-book-open-line"></i>
                        </span>
                    </span>
                    <div>
                        <h4 class="fw-bold mb-0">{{ $stats['formations'] }}</h4>
                        <p class="text-muted mb-0">Formations concernées</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ===== Filtres ===== --}}
    <div class="card border shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.classes.index') }}">
                <div class="row g-3 align-items-center">

                    <div class="col-lg-4">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent">
                                <i class="ri-search-line text-primary"></i>
                            </span>
                            <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                                   placeholder="Rechercher une classe..." aria-label="Rechercher une classe">
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <select name="cours_id" class="form-select" aria-label="Filtrer par formation"
                                onchange="this.form.submit()">
                            <option value="">Toutes les formations</option>
                            @foreach($tousLesCours as $unCours)
                                <option value="{{ $unCours->id }}" @selected(request('cours_id') == $unCours->id)>
                                    {{ $unCours->titre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-6 col-lg-2">
                        <select name="etat" class="form-select" aria-label="Filtrer par statut"
                                onchange="this.form.submit()">
                            <option value="">Tous les statuts</option>
                            <option value="en_cours" @selected(request('etat') === 'en_cours')>En cours</option>
                            <option value="a_venir" @selected(request('etat') === 'a_venir')>À venir</option>
                            <option value="terminee" @selected(request('etat') === 'terminee')>Terminée</option>
                        </select>
                    </div>

                    <div class="col-lg-3 d-flex gap-2 justify-content-lg-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-filter-3-line me-1"></i> Filtrer
                        </button>
                        <a href="{{ route('admin.classes.index') }}" class="btn btn-outline-secondary">
                            <i class="ri-refresh-line me-1"></i> Réinitialiser
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- ===== Tableau des classes ===== --}}
    <div class="card border shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle table-nowrap mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Formation</th>
                        <th>Classe</th>
                        <th>Dates</th>
                        <th>Lieu</th>
                        <th>Inscrits</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($classes as $classe)
                        @php
                            $etat = $classe->etat;
                            $badge = match ($etat) {
                                'en_cours' => ['bg-success-subtle text-success', 'En cours'],
                                'a_venir'  => ['bg-warning-subtle text-warning', 'À venir'],
                                default    => ['bg-info-subtle text-info', 'Terminée'],
                            };
                            $complete = $classe->inscriptions_count >= $classe->capacite_max;
                        @endphp

                        <tr>
                            <td class="text-muted">CL-{{ str_pad($classe->id, 3, '0', STR_PAD_LEFT) }}</td>

                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    @if($classe->cours?->image)
                                        <img src="{{ asset('storage/' . $classe->cours->image) }}"
                                             alt="{{ $classe->cours->titre }}"
                                             class="avatar-sm rounded-3 object-fit-cover flex-shrink-0">
                                    @else
                                        <span class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title rounded-3 bg-primary-subtle text-primary fs-3">
                                                <i class="ri-book-open-line"></i>
                                            </span>
                                        </span>
                                    @endif

                                    <div>
                                        <div class="fw-semibold">{{ $classe->cours?->titre ?? 'Formation supprimée' }}</div>
                                        @if($classe->cours?->categorie)
                                            <small class="text-muted">{{ $classe->cours->categorie }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="fw-medium">{{ $classe->nom }}</td>

                            <td>
                                <div>
                                    {{ $classe->date_debut->format('d/m/Y') }}
                                    <i class="ri-arrow-right-line text-muted mx-1"></i>
                                    {{ $classe->date_fin->format('d/m/Y') }}
                                </div>
                                <small class="text-muted">
                                    {{ max(1, (int) ceil($classe->date_debut->diffInDays($classe->date_fin) / 7)) }} semaine(s)
                                </small>
                            </td>

                            <td>
                                <i class="ri-map-pin-line text-danger me-1"></i>{{ $classe->lieu }}
                            </td>

                            <td>
                                <span class="fw-semibold {{ $complete ? 'text-danger' : '' }}">
                                    {{ $classe->inscriptions_count }} / {{ $classe->capacite_max }}
                                </span>
                                @if($complete)
                                    <span class="badge bg-danger-subtle text-danger ms-1">Complète</span>
                                @endif
                            </td>

                            <td>
                                <span class="badge {{ $badge[0] }}">{{ $badge[1] }}</span>
                            </td>

                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    @if($classe->cours)
                                        <a href="{{ route('admin.cours.show', $classe->cours) }}"
                                           class="btn btn-sm btn-soft-primary" title="Voir la formation">
                                            <i class="ri-eye-line"></i>
                                        </a>

                                        <a href="{{ route('admin.cours.classes.edit', [$classe->cours, $classe]) }}"
                                           class="btn btn-sm btn-soft-secondary" title="Gérer la classe et ses inscrits">
                                            <i class="ri-edit-line"></i>
                                        </a>

                                        <form action="{{ route('admin.cours.classes.destroy', [$classe->cours, $classe]) }}"
                                              method="POST"
                                              onsubmit="return confirm('Supprimer cette classe ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-soft-danger" title="Supprimer">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="ri-group-line display-5 d-block mb-2"></i>
                                Aucune classe ne correspond à votre recherche.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($classes->total() > 0)
            <div class="card-footer bg-transparent d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <small class="text-muted">
                    Affichage de {{ $classes->firstItem() }} à {{ $classes->lastItem() }}
                    sur {{ $classes->total() }} résultat(s)
                </small>
                {{ $classes->links() }}
            </div>
        @endif
    </div>

</div>

@endsection