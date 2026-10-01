@extends(Auth::check() ? 'layouts.master' : 'layouts.master-without-nav')

@section('title')
Catalogue des cours
@endsection

@section('content')

<style>
    /* =========================================================
       HERO
    ========================================================= */

    .catalogue-hero {
        position: relative;
        overflow: hidden;
        border-radius: 1.25rem;
        padding: 2.75rem 3rem;
        background: linear-gradient(135deg, #344b8e 0%, #6979ae 100%);
        color: white;
        margin-bottom: 2.5rem;
    }

    .catalogue-hero::before {
        content: '';
        position: absolute;
        width: 320px;
        height: 320px;
        border: 1px solid rgba(255,255,255,.12);
        border-radius: 50%;
        right: -90px;
        top: -150px;
    }

    .catalogue-hero::after {
        content: '';
        position: absolute;
        width: 320px;
        height: 320px;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 50%;
        right: -40px;
        top: -100px;
    }

    .catalogue-hero-content {
        position: relative;
        z-index: 2;
        max-width: 850px;
    }

    .catalogue-hero .badge {
        display: inline-flex;
        align-items: center;
        background: rgba(255,255,255,.14);
        color: #ffffff;
        border: 1px solid rgba(255,255,255,.20);
        padding: .5rem .85rem;
        font-weight: 500;
    }

    .catalogue-hero h1 {
        color: #ffffff !important;
        font-size: 2.2rem;
        line-height: 1.2;
    }

    .catalogue-hero p {
        color: #ffffff !important;
        max-width: 650px;
        line-height: 1.6;
    }


    /* =========================================================
       RECHERCHE
       ========================================================= */

    .catalogue-search-wrapper {
        max-width: 760px;
        margin-top: 1.5rem;
    }

    .catalogue-search {
        background: #ffffff;
        border-radius: .75rem;
        padding: .35rem;
        box-shadow: 0 .6rem 1.5rem rgba(0,0,0,.12);
    }

    .catalogue-search .input-group {
        align-items: center;
    }

    .catalogue-search .input-group-text {
        border: 0;
        background: transparent;
        color: #6979ae;
        padding-left: .85rem;
        padding-right: .45rem;
    }

    .catalogue-search .form-control {
        border: 0;
        box-shadow: none !important;
        height: 44px;
        font-size: .9rem;
    }

    .catalogue-search .form-control:focus {
        border: 0;
        box-shadow: none !important;
    }

    .catalogue-search .btn {
        border-radius: .55rem;
        height: 44px;
        padding-left: 1.2rem;
        padding-right: 1.2rem;
        font-weight: 600;
    }


    /* =========================================================
       ENTETE DES FORMATIONS
       ========================================================= */

    .catalogue-section-title {
        color: #1f2d3d;
        font-weight: 700;
    }

    .catalogue-section-subtitle {
        color: #6a7488;
        font-size: .85rem;
    }


    /* =========================================================
       CARTE COURS
       ========================================================= */

    .course-card {
        height: 100%;
        border: 1px solid #eef0f6;
        border-radius: 1rem;
        background: #ffffff;
        overflow: hidden;
        transition: all .2s ease;
        box-shadow: 0 .3rem 1rem rgba(31,45,61,.05);
    }

    .course-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 .8rem 1.8rem rgba(31,45,61,.10);
    }


    /* =========================================================
       IMAGE DU COURS
       ========================================================= */

    .course-card-top {
        height: 180px;
        overflow: hidden;
        position: relative;
        background: #eef2ff;
    }

    .course-card-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform .3s ease;
    }

    .course-card:hover .course-card-image {
        transform: scale(1.04);
    }

    .course-card-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .course-card-placeholder::before {
        content: '';
        position: absolute;
        width: 170px;
        height: 170px;
        border: 1px solid rgba(255,255,255,.55);
        border-radius: 50%;
    }

    .course-card-placeholder::after {
        content: '';
        position: absolute;
        width: 115px;
        height: 115px;
        border: 1px solid rgba(255,255,255,.40);
        border-radius: 50%;
    }

    .course-card-icon {
        width: 4.5rem;
        height: 4.5rem;
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.15rem;
        position: relative;
        z-index: 2;
    }


    /* =========================================================
       CONTENU CARTE
       ========================================================= */

    .course-card-body {
        padding: 1.3rem;
    }

    .course-category {
        font-size: .7rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        font-weight: 600;
    }

    .course-title {
        color: #1f2d3d;
        font-size: 1.05rem;
        font-weight: 700;
        line-height: 1.35;
        min-height: 2.85rem;
    }

    .course-description {
        color: #6a7488;
        font-size: .82rem;
        line-height: 1.5;
        min-height: 2.5rem;

        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }


    /* =========================================================
       INFORMATIONS
       ========================================================= */

    .course-info {
        border-top: 1px solid #eef0f6;
        border-bottom: 1px solid #eef0f6;
        padding: .8rem 0;
        margin: 1rem 0;
    }

    .course-info-item {
        color: #6a7488;
        font-size: .75rem;
    }


    /* =========================================================
       PRIX + BOUTON
       ========================================================= */

    .course-price {
        font-size: 1.1rem;
        font-weight: 700;
    }

    .course-button {
        border-radius: .6rem;
        font-weight: 600;
        padding: .6rem 1rem;
        white-space: nowrap;
    }


    /* =========================================================
       AUCUN RESULTAT
       ========================================================= */

    .empty-catalogue {
        padding: 4rem 1rem;
    }


    /* =========================================================
       RESPONSIVE
       ========================================================= */

    @media (max-width: 768px) {

        .catalogue-hero {
            padding: 2rem 1.5rem;
        }

        .catalogue-hero h1 {
            font-size: 1.7rem;
        }

        .catalogue-search .input-group {
            flex-wrap: nowrap;
        }

        .catalogue-search .btn {
            padding-left: .8rem;
            padding-right: .8rem;
        }

        .course-card-top {
            height: 165px;
        }
    }
</style>

@php


$role = Auth::user()?->role?->nom;

$palette = [
    [
        'background' => '#eef2ff',
        'color' => '#405189',
        'icon' => 'ri-computer-line'
    ],
    [
        'background' => '#e8faf6',
        'color' => '#0ab39c',
        'icon' => 'ri-code-s-slash-line'
    ],
    [
        'background' => '#fff0ed',
        'color' => '#f06548',
        'icon' => 'ri-briefcase-4-line'
    ],
    [
        'background' => '#fff7e6',
        'color' => '#f7b84b',
        'icon' => 'ri-calculator-line'
    ],
    [
        'background' => '#eaf6ff',
        'color' => '#299cdb',
        'icon' => 'ri-book-open-line'
    ],
];


@endphp

{{-- =========================================================
BREADCRUMB
========================================================= --}}

@auth


@component('components.breadcrumb')

    @slot('li_1')
        Cours
    @endslot

    @slot('title')
        Catalogue des cours
    @endslot

@endcomponent


@endauth

{{-- =========================================================
BOUTON CONNEXION POUR VISITEUR
========================================================= --}}

@guest


<div class="container py-4">

    <div class="d-flex justify-content-end mb-3">

        <a
            href="{{ route('login') }}"
            class="btn btn-primary"
        >
            Se connecter
        </a>

    </div>

</div>


@endguest

<div class="container-fluid">


{{-- =====================================================
     HERO
     ===================================================== --}}

<div class="catalogue-hero">

    <div class="catalogue-hero-content">

        <span class="badge rounded-pill mb-3">

            <i class="ri-graduation-cap-line me-1"></i>

            Formations professionnelles

        </span>


        <h1 class="fw-bold mb-2">

            Découvrez nos formations

        </h1>


        <p class="mb-0">

            Développez vos compétences grâce à nos formations
            conçues pour vous accompagner dans votre progression.

        </p>


        {{-- =================================================
             RECHERCHE
             ================================================= --}}

        <div class="catalogue-search-wrapper">

            <form
                method="GET"
                action="{{ route('cours.catalogue') }}"
                class="catalogue-search"
            >

                <div class="input-group">

                    <span class="input-group-text">

                        <i class="ri-search-line"></i>

                    </span>


                    <input
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        class="form-control"
                        placeholder="Rechercher une formation..."
                    >


                    <button
                        type="submit"
                        class="btn btn-primary"
                    >

                        Rechercher

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


{{-- =====================================================
     ENTETE DES RESULTATS
     ===================================================== --}}

<div class="d-flex justify-content-between align-items-center mb-3">

    <div>

        <h4 class="catalogue-section-title mb-1">

            Nos formations

        </h4>


        <p class="catalogue-section-subtitle mb-0">

            @if($search !== '')

                {{ $cours->count() }}
                résultat(s) pour « {{ $search }} »

            @else

                {{ $cours->count() }}
                formation(s) disponible(s)

            @endif

        </p>

    </div>


    @if($search !== '')

        <a
            href="{{ route('cours.catalogue') }}"
            class="btn btn-outline-secondary btn-sm"
        >

            <i class="ri-close-line me-1"></i>

            Réinitialiser

        </a>

    @endif

</div>


{{-- =====================================================
     LISTE DES COURS
     ===================================================== --}}

<div class="row g-4">

    @forelse($cours as $unCours)

        @php

            $theme = $palette[$loop->index % count($palette)];

            $categorie = strtolower(
                (string) $unCours->categorie
            );

            $icon = $theme['icon'];


            /*
             * Icône de secours selon la catégorie.
             */

            if (str_contains($categorie, 'excel')) {

                $icon = 'ri-file-excel-2-line';

            }
            elseif (str_contains($categorie, 'word')) {

                $icon = 'ri-file-word-2-line';

            }
            elseif (str_contains($categorie, 'powerpoint')) {

                $icon = 'ri-file-ppt-2-line';

            }
            elseif (
                str_contains($categorie, 'web') ||
                str_contains($categorie, 'développement') ||
                str_contains($categorie, 'informatique')
            ) {

                $icon = 'ri-code-s-slash-line';

            }
            elseif (str_contains($categorie, 'gestion')) {

                $icon = 'ri-briefcase-4-line';

            }

        @endphp


        <div class="col-xl-4 col-md-6">


            <div class="course-card">


                {{-- =================================================
                     IMAGE DU COURS
                     ================================================= --}}

                <div class="course-card-top">


                    @if(!empty($unCours->image))

                        <img
                            src="{{ asset('storage/' . $unCours->image) }}"
                            alt="{{ $unCours->titre }}"
                            class="course-card-image"
                        >

                    @else

                        {{-- Image de secours si aucune image n'est définie --}}

                        <div
                            class="course-card-placeholder"
                            style="
                                background: {{ $theme['background'] }};
                                color: {{ $theme['color'] }};
                            "
                        >

                            <div
                                class="course-card-icon"
                                style="
                                    background: rgba(255,255,255,.9);
                                    color: {{ $theme['color'] }};
                                    box-shadow: 0 .3rem .8rem rgba(0,0,0,.05);
                                "
                            >

                                <i class="{{ $icon }}"></i>

                            </div>

                        </div>

                    @endif


                </div>


                {{-- =================================================
                     CONTENU DE LA CARTE
                     ================================================= --}}

                <div class="course-card-body">


                    {{-- CATEGORIE --}}

                    @if(!empty($unCours->categorie))

                        <div
                            class="course-category mb-2"
                            style="color: {{ $theme['color'] }};"
                        >

                            {{ $unCours->categorie }}

                        </div>

                    @endif


                    {{-- TITRE --}}

                    <h5 class="course-title mb-2">

                        {{ $unCours->titre }}

                    </h5>


                    {{-- DESCRIPTION --}}

                    <p class="course-description mb-0">

                        {{ $unCours->description ?: 'Description à venir pour cette formation.' }}

                    </p>


                    {{-- =================================================
                         INFORMATIONS
                         ================================================= --}}

                    <div class="course-info">

                        <div class="row">


                            <div class="col-6">

                                <div class="course-info-item">

                                    <i class="ri-book-open-line me-1"></i>

                                    {{ $unCours->chapitres_count }}

                                    chapitre(s)

                                </div>

                            </div>


                            <div class="col-6">

                                <div class="course-info-item">

                                    <i class="ri-calendar-2-line me-1"></i>

                                    {{ $unCours->classes_count }}

                                    classe(s)

                                </div>

                            </div>


                        </div>

                    </div>


                    {{-- =================================================
                         PRIX + BOUTON
                         ================================================= --}}

                    <div class="d-flex justify-content-between align-items-center gap-2">


                        <div>

                            <small class="text-muted d-block">

                                À partir de

                            </small>


                            <div
                                class="course-price"
                                style="color: {{ $theme['color'] }};"
                            >

                                @if($role === 'entreprise')

                                    {{ number_format($unCours->prix_entreprise, 0, ',', ' ') }}
                                    FCFA

                                @else

                                    {{ number_format($unCours->prix_particulier, 0, ',', ' ') }}
                                    FCFA

                                @endif

                            </div>

                        </div>


                        <a
                            href="{{ route('cours.show', $unCours) }}"
                            class="btn btn-primary course-button"
                        >

                            Découvrir

                            <i class="ri-arrow-right-line ms-1"></i>

                        </a>


                    </div>


                </div>

            </div>


        </div>


    @empty


        {{-- =================================================
             AUCUN COURS
             ================================================= --}}

        <div class="col-12">

            <div class="empty-catalogue text-center">


                <i
                    class="ri-inbox-line text-muted"
                    style="font-size: 56px;"
                ></i>


                <h5 class="mt-3">

                    Aucune formation trouvée

                </h5>


                <p class="text-muted">

                    Essayez avec un autre terme de recherche.

                </p>


                @if($search !== '')

                    <a
                        href="{{ route('cours.catalogue') }}"
                        class="btn btn-primary"
                    >

                        Voir toutes les formations

                    </a>

                @endif


            </div>

        </div>


    @endforelse


</div>


</div>

@guest


</div>


@endguest

@endsection
