@extends('layouts.master')

@section('title', 'Détail du cours')

@section('content')

<style>

    /* =========================================================
       PAGE
    ========================================================= */

    .admin-course-page {
        padding-bottom: 40px;
    }


    /* =========================================================
       EN-TÊTE
    ========================================================= */

    .admin-page-header {
        background: #ffffff;
        border: 1px solid #e8edf5;
        border-radius: 16px;
        padding: 24px 26px;
        margin-bottom: 24px;
        box-shadow: 0 4px 16px rgba(15, 71, 173, 0.05);
    }

    .admin-breadcrumb {
        font-size: 13px;
        color: #98a2b3;
        margin-bottom: 8px;
    }

    .admin-page-header h1 {
        color: #172b4d;
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .admin-page-header p {
        color: #667085;
        margin-bottom: 0;
        font-size: 14px;
    }


    /* =========================================================
       BOUTONS
    ========================================================= */

    .admin-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border-radius: 9px;
        font-weight: 600;
        font-size: 13px;
        padding: 9px 15px;
    }

    .admin-btn i {
        font-size: 16px;
    }


    /* =========================================================
       CARTES D'ACCÈS
    ========================================================= */

    .course-access-card {
        display: block;
        height: 100%;
        padding: 20px;
        background: #ffffff;
        border: 1px solid #e8edf5;
        border-radius: 15px;
        text-decoration: none;
        box-shadow: 0 4px 16px rgba(15, 71, 173, 0.05);
        transition: all 0.2s ease;
    }

    .course-access-card:hover {
        transform: translateY(-3px);
        border-color: #cbdaf4;
        box-shadow: 0 8px 22px rgba(15, 71, 173, 0.10);
    }

    .course-access-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
    }

    .course-access-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 21px;
    }

    .course-access-icon.blue {
        background: #eaf2ff;
        color: #0f47ad;
    }

    .course-access-icon.green {
        background: #e9f8ef;
        color: #198754;
    }

    .course-access-icon.orange {
        background: #fff4df;
        color: #d88900;
    }

    .course-access-arrow {
        color: #98a2b3;
        font-size: 18px;
        transition: transform 0.2s ease;
    }

    .course-access-card:hover .course-access-arrow {
        transform: translateX(4px);
        color: #0f47ad;
    }

    .course-access-title {
        color: #172b4d;
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .course-access-description {
        color: #98a2b3;
        font-size: 12px;
        line-height: 1.5;
        margin-bottom: 13px;
    }

    .course-access-count {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        color: #667085;
        font-size: 12px;
        font-weight: 600;
    }


    /* =========================================================
       CARTES PRINCIPALES
    ========================================================= */

    .admin-card {
        background: #ffffff;
        border: 1px solid #e8edf5;
        border-radius: 16px;
        box-shadow: 0 4px 16px rgba(15, 71, 173, 0.05);
        height: 100%;
    }

    .admin-card-header {
        padding: 22px 24px;
        border-bottom: 1px solid #edf0f5;
    }

    .admin-card-body {
        padding: 24px;
    }

    .admin-card-title {
        color: #172b4d;
        font-size: 17px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .admin-card-subtitle {
        color: #98a2b3;
        font-size: 13px;
        margin-bottom: 0;
    }


    /* =========================================================
       INFORMATIONS DU COURS
    ========================================================= */

    .course-info-item {
        padding: 14px 0;
        border-bottom: 1px solid #edf0f5;
    }

    .course-info-item:last-child {
        border-bottom: none;
    }

    .course-info-label {
        color: #98a2b3;
        font-size: 12px;
        margin-bottom: 5px;
    }

    .course-info-value {
        color: #172b4d;
        font-size: 14px;
        font-weight: 600;
    }

    .course-description-box {
        background: #f8fafc;
        border: 1px solid #edf0f5;
        border-radius: 12px;
        padding: 17px;
        color: #667085;
        line-height: 1.7;
        font-size: 14px;
    }


    /* =========================================================
       TARIFS
    ========================================================= */

    .price-box {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        padding: 14px 16px;
        border-radius: 11px;
        margin-bottom: 10px;
    }

    .price-box:last-child {
        margin-bottom: 0;
    }

    .price-box.particulier {
        background: #f0f6ff;
        border: 1px solid #dce9ff;
    }

    .price-box.entreprise {
        background: #f2faf5;
        border: 1px solid #dcefe1;
    }

    .price-label {
        color: #667085;
        font-size: 13px;
    }

    .price-value {
        font-size: 15px;
        font-weight: 700;
    }

    .price-box.particulier .price-value {
        color: #0f47ad;
    }

    .price-box.entreprise .price-value {
        color: #198754;
    }


    /* =========================================================
       ACTIONS
    ========================================================= */

    .admin-bottom-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        margin-top: 25px;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 768px) {

        .admin-page-header {
            padding: 20px;
        }

        .admin-page-header .header-actions {
            margin-top: 15px;
        }

        .admin-bottom-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .admin-bottom-actions > div {
            display: flex;
            flex-direction: column;
        }

        .admin-bottom-actions .admin-btn {
            width: 100%;
        }

    }

</style>

<div class="container-fluid admin-course-page">

{{-- =========================================================
     EN-TÊTE
========================================================== --}}

<div class="admin-page-header">

    <div class="row align-items-center">

        <div class="col-lg-8">

            <div class="admin-breadcrumb">
                Administration
                <span class="mx-1">/</span>
                Cours
                <span class="mx-1">/</span>
                Détail
            </div>

            <h1>
                {{ $cours->titre }}
            </h1>

            <p>
                Consultez les informations et accédez directement
                aux différents éléments de cette formation.
            </p>

        </div>


        <div class="col-lg-4">

            <div class="d-flex justify-content-lg-end gap-2 header-actions">

                <a
                    href="{{ route('admin.cours.edit', $cours) }}"
                    class="btn btn-outline-primary admin-btn"
                >
                    <i class="ri-edit-line"></i>
                    Modifier
                </a>

                <a
                    href="{{ route('admin.cours.contenus', $cours) }}"
                    class="btn btn-primary admin-btn"
                >
                    <i class="ri-folder-open-line"></i>
                    Gérer les contenus
                </a>

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
     CARTES D'ACCÈS RAPIDE
========================================================== --}}

<div class="row g-3 mb-4">


    {{-- CHAPITRES --}}

    <div class="col-md-4">

        <a
            href="{{ route('admin.cours.contenus', $cours) }}"
            class="course-access-card"
        >

            <div class="course-access-top">

                <div class="course-access-icon blue">
                    <i class="ri-book-open-line"></i>
                </div>

                <i class="ri-arrow-right-line course-access-arrow"></i>

            </div>


            <div class="course-access-title">
                Chapitres
            </div>

            <div class="course-access-description">
                Gérez les chapitres et les ressources pédagogiques
                de cette formation.
            </div>

            <div class="course-access-count">
                <i class="ri-folder-2-line"></i>
                {{ $cours->chapitres->count() }} chapitre(s)
            </div>

        </a>

    </div>


    {{-- CLASSES --}}

    <div class="col-md-4">

        <a
            href="{{ route('admin.cours.classes.index', $cours) }}"
            class="course-access-card"
        >

            <div class="course-access-top">

                <div class="course-access-icon green">
                    <i class="ri-group-line"></i>
                </div>

                <i class="ri-arrow-right-line course-access-arrow"></i>

            </div>


            <div class="course-access-title">
                Classes
            </div>

            <div class="course-access-description">
                Consultez et gérez les sessions associées
                à cette formation.
            </div>

            <div class="course-access-count">
                <i class="ri-calendar-line"></i>
                {{ $cours->classes->count() }} classe(s)
            </div>

        </a>

    </div>


    {{-- QUIZ --}}

    <div class="col-md-4">

        <a
            href="{{ route('admin.cours.contenus', $cours) }}"
            class="course-access-card"
        >

            <div class="course-access-top">

                <div class="course-access-icon orange">
                    <i class="ri-questionnaire-line"></i>
                </div>

                <i class="ri-arrow-right-line course-access-arrow"></i>

            </div>


            <div class="course-access-title">
                Quiz
            </div>

            <div class="course-access-description">
                Consultez et gérez les évaluations et quiz
                de cette formation.
            </div>

            <div class="course-access-count">
                <i class="ri-question-line"></i>
                {{ $cours->quizzes->count() }} quiz
            </div>

        </a>

    </div>

</div>


{{-- =========================================================
     INFORMATIONS PRINCIPALES
========================================================== --}}

<div class="row g-4 mb-4">


    {{-- INFORMATIONS DU COURS --}}

    <div class="col-lg-8">

        <div class="admin-card">

            <div class="admin-card-header">

                <h5 class="admin-card-title">
                    Informations du cours
                </h5>

                <p class="admin-card-subtitle">
                    Informations générales de la formation.
                </p>

            </div>


            <div class="admin-card-body">

                <div class="row">

                    <div class="col-md-6">

                        <div class="course-info-item">

                            <div class="course-info-label">
                                Titre
                            </div>

                            <div class="course-info-value">
                                {{ $cours->titre }}
                            </div>

                        </div>

                    </div>


                    <div class="col-md-6">

                        <div class="course-info-item">

                            <div class="course-info-label">
                                Catégorie
                            </div>

                            <div class="course-info-value">
                                {{ $cours->categorie }}
                            </div>

                        </div>

                    </div>


                    @if($cours->niveau)

                        <div class="col-md-6">

                            <div class="course-info-item">

                                <div class="course-info-label">
                                    Niveau
                                </div>

                                <div class="course-info-value">
                                    {{ $cours->niveau }}
                                </div>

                            </div>

                        </div>

                    @endif


                    @if($cours->duree)

                        <div class="col-md-6">

                            <div class="course-info-item">

                                <div class="course-info-label">
                                    Durée
                                </div>

                                <div class="course-info-value">
                                    {{ $cours->duree }}
                                </div>

                            </div>

                        </div>

                    @endif

                </div>


                @if($cours->description)

                    <div class="mt-4">

                        <div class="course-info-label mb-2">
                            Description
                        </div>

                        <div class="course-description-box">
                            {!! nl2br(e($cours->description)) !!}
                        </div>

                    </div>

                @endif

            </div>

        </div>

    </div>


    {{-- TARIFICATION --}}

    <div class="col-lg-4">

        <div class="admin-card">

            <div class="admin-card-header">

                <h5 class="admin-card-title">
                    Tarification
                </h5>

                <p class="admin-card-subtitle">
                    Tarifs appliqués selon le profil.
                </p>

            </div>


            <div class="admin-card-body">

                <div class="price-box particulier">

                    <span class="price-label">
                        Particulier
                    </span>

                    <span class="price-value">
                        {{ number_format($cours->prix_particulier, 0, ',', ' ') }}
                        FCFA
                    </span>

                </div>


                <div class="price-box entreprise">

                    <span class="price-label">
                        Entreprise
                    </span>

                    <span class="price-value">
                        {{ number_format($cours->prix_entreprise, 0, ',', ' ') }}
                        FCFA
                    </span>

                </div>

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
     ACTIONS FINALES
========================================================== --}}

<div class="admin-bottom-actions">

    <a
        href="{{ route('admin.cours.index') }}"
        class="btn btn-light admin-btn"
    >
        <i class="ri-arrow-left-line"></i>
        Retour aux formations
    </a>


    <div class="d-flex gap-2">

        <a
            href="{{ route('admin.cours.edit', $cours) }}"
            class="btn btn-outline-primary admin-btn"
        >
            <i class="ri-edit-line"></i>
            Modifier la formation
        </a>


        <a
            href="{{ route('admin.cours.contenus', $cours) }}"
            class="btn btn-primary admin-btn"
        >
            <i class="ri-folder-open-line"></i>
            Gérer les contenus
        </a>

    </div>

</div>

</div>

@endsection
