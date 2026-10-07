@extends('layouts.master')

@section('title', 'Gestion des cours')

@section('content')

<div class="container-fluid">

    {{-- =========================================================
         EN-TÊTE
    ========================================================== --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>
            <h4 class="fw-bold mb-1">
                Gestion des cours
            </h4>

            <p class="text-muted mb-0">
                Gérez vos formations, vos classes et vos contenus pédagogiques.
            </p>
        </div>

        <div>
            <a href="{{ route('admin.cours.create') }}"
               class="btn btn-primary">
                <i class="ri-add-line me-1"></i>
                Nouveau cours
            </a>
        </div>

    </div>


    {{-- =========================================================
         MESSAGE DE SUCCÈS
    ========================================================== --}}
    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show mb-4"
             role="alert">

            <i class="ri-checkbox-circle-line me-2"></i>

            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    @endif


    {{-- =========================================================
         STATISTIQUES
    ========================================================== --}}
    @php

        $nombreCours = $cours->count();

        $nombreChapitres = $cours->sum(function ($unCours) {
            return $unCours->chapitres_count;
        });

        $nombreClasses = $cours->sum(function ($unCours) {
            return $unCours->classes_count;
        });

    @endphp


    <div class="row g-3 mb-4">

        {{-- Nombre de cours --}}
        <div class="col-md-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div class="rounded-circle bg-primary-subtle p-3 me-3">
                            <i class="ri-book-open-line fs-4 text-primary"></i>
                        </div>

                        <div>

                            <p class="text-muted mb-1">
                                Cours
                            </p>

                            <h4 class="fw-bold mb-0">
                                {{ $nombreCours }}
                            </h4>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Nombre de chapitres --}}
        <div class="col-md-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div class="rounded-circle bg-info-subtle p-3 me-3">
                            <i class="ri-list-check-2 fs-4 text-info"></i>
                        </div>

                        <div>

                            <p class="text-muted mb-1">
                                Chapitres
                            </p>

                            <h4 class="fw-bold mb-0">
                                {{ $nombreChapitres }}
                            </h4>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Nombre de classes --}}
        <div class="col-md-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div class="rounded-circle bg-success-subtle p-3 me-3">
                            <i class="ri-group-line fs-4 text-success"></i>
                        </div>

                        <div>

                            <p class="text-muted mb-1">
                                Classes
                            </p>

                            <h4 class="fw-bold mb-0">
                                {{ $nombreClasses }}
                            </h4>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         BARRE DE RECHERCHE / FILTRE
    ========================================================== --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <div class="row g-3 align-items-center">

                <div class="col-lg-8">

                    <div class="input-group">

                        <span class="input-group-text bg-white">
                            <i class="ri-search-line"></i>
                        </span>

                        <input
                            type="text"
                            id="rechercheCours"
                            class="form-control"
                            placeholder="Rechercher un cours..."
                        >

                    </div>

                </div>

                <div class="col-lg-4">

                    <select id="filtreCategorie"
                            class="form-select">

                        <option value="">
                            Toutes les catégories
                        </option>

                        @foreach($cours->pluck('categorie')->filter()->unique()->sort() as $categorie)

                            <option value="{{ strtolower($categorie) }}">
                                {{ $categorie }}
                            </option>

                        @endforeach

                    </select>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         LISTE DES COURS
    ========================================================== --}}
    <div id="listeCours">

        @forelse($cours as $unCours)

            <div
                class="card cours-card border-0 shadow-sm mb-3"
                data-titre="{{ strtolower($unCours->titre) }}"
                data-categorie="{{ strtolower($unCours->categorie) }}"
            >

                <div class="card-body p-4">

                    <div class="row align-items-center">

                        {{-- -------------------------------------------------
                             INFORMATIONS PRINCIPALES
                        -------------------------------------------------- --}}
                        <div class="col-lg-6">

                            <div class="d-flex align-items-start">

                                <div class="cours-icon me-3">

                                    <i class="ri-book-2-line"></i>

                                </div>

                                <div>

                                    <h5 class="fw-bold mb-1">
                                        {{ $unCours->titre }}
                                    </h5>

                                    @if($unCours->categorie)

                                        <span class="badge bg-primary-subtle text-primary mb-2">
                                            {{ $unCours->categorie }}
                                        </span>

                                    @endif

                                    @if($unCours->description)

                                        <p class="text-muted mb-0 small">
                                            {{ \Illuminate\Support\Str::limit($unCours->description, 120) }}
                                        </p>

                                    @endif

                                </div>

                            </div>

                        </div>


                        {{-- -------------------------------------------------
                             STATISTIQUES DU COURS
                        -------------------------------------------------- --}}
                        <div class="col-lg-3 mt-3 mt-lg-0">

                            <div class="d-flex flex-wrap gap-3">

                                <div>

                                    <div class="small text-muted">
                                        Chapitres
                                    </div>

                                    <div class="fw-semibold">
                                        <i class="ri-list-check-2 me-1 text-info"></i>
                                        {{ $unCours->chapitres_count }}
                                    </div>

                                </div>


                                <div>

                                    <div class="small text-muted">
                                        Classes
                                    </div>

                                    <div class="fw-semibold">
                                        <i class="ri-group-line me-1 text-success"></i>
                                        {{ $unCours->classes_count }}
                                    </div>

                                </div>

                            </div>

                        </div>


                        {{-- -------------------------------------------------
                             PRIX
                        -------------------------------------------------- --}}
                        <div class="col-lg-1 mt-3 mt-lg-0">

                            <div class="small text-muted">
                                Particulier
                            </div>

                            <div class="fw-bold">
                                {{ number_format($unCours->prix_particulier, 0, ',', ' ') }}
                            </div>

                            <div class="small text-muted">
                                FCFA
                            </div>

                        </div>


                        {{-- -------------------------------------------------
                             ACTIONS
                        -------------------------------------------------- --}}
                        <div class="col-lg-2 mt-3 mt-lg-0">

                            <div class="d-flex justify-content-lg-end gap-2 flex-wrap">

                                {{-- Gérer le contenu --}}
                                <a
                                    href="{{ route('admin.cours.show', $unCours) }}"
                                    class="btn btn-primary btn-sm"
                                    title="Voir le cours"
                                >
                                    <i class="ri-eye-line me-1"></i>
                                    Voir
                                </a>


                                {{-- Modifier --}}
                                <a
                                    href="{{ route('admin.cours.edit', $unCours) }}"
                                    class="btn btn-outline-secondary btn-sm"
                                    title="Modifier le cours"
                                >
                                    <i class="ri-edit-line"></i>
                                </a>


                                {{-- Suppression --}}
                                <form
                                    action="{{ route('admin.cours.destroy', $unCours) }}"
                                    method="POST"
                                    onsubmit="return confirm('Voulez-vous vraiment supprimer ce cours ?');"
                                >

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="btn btn-outline-danger btn-sm"
                                        title="Supprimer"
                                    >
                                        <i class="ri-delete-bin-line"></i>
                                    </button>

                                </form>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        @empty

            <div class="card border-0 shadow-sm">

                <div class="card-body text-center py-5">

                    <div class="mb-3">

                        <i class="ri-book-open-line display-5 text-muted"></i>

                    </div>

                    <h5 class="fw-bold">
                        Aucun cours
                    </h5>

                    <p class="text-muted mb-3">
                        Vous n'avez encore créé aucun cours.
                    </p>

                    <a
                        href="{{ route('admin.cours.create') }}"
                        class="btn btn-primary"
                    >
                        <i class="ri-add-line me-1"></i>
                        Créer le premier cours
                    </a>

                </div>

            @if($cours->hasPages())
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 gap-3">
                    <small class="text-muted">
                        Affichage de {{ $cours->firstItem() }} à {{ $cours->lastItem() }}
                        sur {{ $cours->total() }} cours
                    </small>

                    {{ $cours->links() }}
                </div>
            @endif
        </div>{{-- fin .card-body --}}

        @endforelse

    </div>

</div>


{{-- =============================================================
     STYLE
============================================================= --}}
<style>

    .cours-card {
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .cours-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08) !important;
    }

    .cours-icon {
        width: 48px;
        height: 48px;
        min-width: 48px;
        border-radius: 12px;
        background: rgba(15, 71, 173, 0.10);
        color: #0f47ad;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }

    .cours-card .btn {
        white-space: nowrap;
    }

    @media (max-width: 991.98px) {

        .cours-card .col-lg-1,
        .cours-card .col-lg-2,
        .cours-card .col-lg-3 {
            margin-top: 15px;
        }

    }

</style>


{{-- =============================================================
     RECHERCHE ET FILTRE
============================================================= --}}
<script>

document.addEventListener('DOMContentLoaded', function () {

    const recherche = document.getElementById('rechercheCours');
    const filtre = document.getElementById('filtreCategorie');
    const cartes = document.querySelectorAll('.cours-card');

    function filtrerCours() {

        const texteRecherche = recherche.value.toLowerCase().trim();
        const categorieSelectionnee = filtre.value.toLowerCase();

        cartes.forEach(function (carte) {

            const titre = carte.dataset.titre;
            const categorie = carte.dataset.categorie;

            const correspondRecherche =
                titre.includes(texteRecherche) ||
                categorie.includes(texteRecherche);

            const correspondCategorie =
                categorieSelectionnee === '' ||
                categorie === categorieSelectionnee;

            if (correspondRecherche && correspondCategorie) {

                carte.style.display = '';

            } else {

                carte.style.display = 'none';

            }

        });

    }

    recherche.addEventListener('input', filtrerCours);

    filtre.addEventListener('change', filtrerCours);

});

</script>

@endsection