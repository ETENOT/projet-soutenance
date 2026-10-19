@extends('layouts.master')

@section('title', 'Gestion des formateurs')

@section('content')

@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('dashboard') }}">Accueil</a>
    @endslot
    @slot('title')
        Formateurs
    @endslot
@endcomponent

<div class="container-fluid pb-4">

    {{-- ===== En-tête ===== --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <p class="text-muted mb-0 col-lg-8 px-0">
            Gérez les comptes des formateurs, leurs affectations aux classes et consultez les avis anonymes laissés par les stagiaires.
        </p>

        <a href="{{ route('admin.formateurs.create') }}" class="btn btn-primary fw-semibold">
            <i class="ri-add-line me-1"></i> Ajouter un formateur
        </a>
    </div>

    {{-- ===== Statistiques ===== --}}
    <div class="row g-2 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-primary-subtle text-primary fs-4"><i class="ri-user-star-line"></i></span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $totalFormateurs }}</h5>
                        <p class="text-muted mb-0 fs-12">Formateurs</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-warning-subtle text-warning fs-4"><i class="ri-group-line"></i></span>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $classesSansFormateur }}</h5>
                        <p class="text-muted mb-0 fs-12">Classes ouvertes sans formateur</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Recherche ===== --}}
    <div class="card border shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.formateurs.index') }}" class="row g-2 align-items-center">
                <div class="col-lg-6">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent"><i class="ri-search-line text-primary"></i></span>
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                               placeholder="Rechercher un formateur (nom ou e-mail)..." aria-label="Rechercher un formateur">
                    </div>
                </div>
                <div class="col-lg-6 d-flex gap-2 justify-content-lg-end">
                    <button type="submit" class="btn btn-primary"><i class="ri-filter-3-line me-1"></i> Rechercher</button>
                    <a href="{{ route('admin.formateurs.index') }}" class="btn btn-outline-secondary">
                        <i class="ri-refresh-line me-1"></i> Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== Tableau ===== --}}
    <div class="card border shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle table-nowrap mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nom</th>
                        <th>E-mail</th>
                        <th>Classes</th>
                        <th>En cours</th>
                        <th>Avis reçus</th>
                        <th>Note moyenne</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($formateurs as $formateur)
                        {{-- Statistiques d'avis de ce formateur (calculées en une requête dans le contrôleur) --}}
                        @php $stats = $statsAvis->get($formateur->id); @endphp
                        <tr>
                            <td class="fw-medium">
                                <a href="{{ route('admin.formateurs.show', $formateur) }}" class="text-reset">{{ $formateur->name }}</a>
                            </td>
                            <td>{{ $formateur->email }}</td>
                            <td>{{ $formateur->classes_animes_count }}</td>
                            <td>
                                @if ($formateur->classes_en_cours_count > 0)
                                    <span class="badge bg-success-subtle text-success">{{ $formateur->classes_en_cours_count }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td>{{ $stats->nb ?? 0 }}</td>
                            <td>
                                @if ($stats)
                                    @include('cours.partials.etoiles', ['note' => $stats->moyenne, 'taille' => 'fs-6'])
                                    <span class="ms-1 text-muted small">{{ number_format($stats->moyenne, 1, ',', '') }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    {{-- Voir : classes affectées + avis --}}
                                    <a href="{{ route('admin.formateurs.show', $formateur) }}" class="btn btn-sm btn-soft-primary" title="Classes et avis">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                    {{-- Modifier --}}
                                    <a href="{{ route('admin.formateurs.edit', $formateur) }}" class="btn btn-sm btn-soft-secondary" title="Modifier">
                                        <i class="ri-edit-line"></i>
                                    </a>
                                    {{-- Supprimer (confirmation JS ; le contrôleur refuse si des classes ouvertes lui sont affectées) --}}
                                    <form action="{{ route('admin.formateurs.destroy', $formateur) }}" method="POST"
                                          onsubmit="return confirm('Supprimer ce formateur ? Ses classes fermées seront conservées sans formateur.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-soft-danger" title="Supprimer">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Aucun formateur trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination (10 par page) --}}
        @if ($formateurs->hasPages())
            <div class="card-footer">{{ $formateurs->links() }}</div>
        @endif
    </div>
</div>

@endsection