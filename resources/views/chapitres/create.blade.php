@extends('layouts.master')

@section('title', 'Ajouter un chapitre')

@section('content')

<div class="container-fluid">


{{-- =========================================================
     BREADCRUMB
========================================================== --}}
<div class="mb-4">

    <div class="d-flex align-items-center gap-2 small text-muted">

        <a
            href="{{ route('admin.cours.index') }}"
            class="breadcrumb-link"
        >
            Cours
        </a>

        <i class="ri-arrow-right-s-line"></i>

        <a
            href="{{ route('admin.cours.show', $cours) }}"
            class="breadcrumb-link"
        >
            {{ $cours->titre }}
        </a>

        <i class="ri-arrow-right-s-line"></i>

        <a
            href="{{ route('admin.cours.contenus', $cours) }}"
            class="breadcrumb-link"
        >
            Contenus pédagogiques
        </a>

        <i class="ri-arrow-right-s-line"></i>

        <span>
            Ajouter un chapitre
        </span>

    </div>

</div>


{{-- =========================================================
     EN-TÊTE
========================================================== --}}
<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h4 class="fw-bold mb-1">
            Ajouter un chapitre
        </h4>

        <p class="text-muted mb-0">
            Créez un nouveau chapitre pour organiser votre formation.
        </p>

    </div>

    <a
        href="{{ route('admin.cours.contenus', $cours) }}"
        class="btn btn-outline-secondary"
    >
        <i class="ri-arrow-left-line me-1"></i>
        Retour aux contenus
    </a>

</div>


{{-- =========================================================
     ERREURS DE VALIDATION
========================================================== --}}
@if($errors->any())

    <div class="alert alert-danger alert-dismissible fade show mb-4"
         role="alert">

        <div class="d-flex align-items-start">

            <i class="ri-error-warning-line me-2 fs-5"></i>

            <div>

                <strong>
                    Vérifiez les informations saisies.
                </strong>

                <ul class="mb-0 mt-2">

                    @foreach($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        </div>

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
        ></button>

    </div>

@endif


{{-- =========================================================
     CONTENU PRINCIPAL
========================================================== --}}
<div class="row g-4">

    {{-- =====================================================
         FORMULAIRE
    ====================================================== --}}
    <div class="col-lg-8">

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white border-0 p-4">

                <div class="d-flex align-items-center gap-3">

                    <div class="form-icon">

                        <i class="ri-book-open-line"></i>

                    </div>

                    <div>

                        <h5 class="fw-bold mb-1">
                            Informations du chapitre
                        </h5>

                        <p class="text-muted small mb-0">
                            Définissez les informations principales de ce chapitre.
                        </p>

                    </div>

                </div>

            </div>


            <div class="card-body p-4">

                <form
                    action="{{ route('admin.cours.chapitres.store', $cours) }}"
                    method="POST"
                >

                    @csrf


                    {{-- Titre --}}
                    <div class="mb-4">

                        <label
                            for="titre"
                            class="form-label fw-semibold"
                        >
                            Titre du chapitre
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            id="titre"
                            name="titre"
                            value="{{ old('titre') }}"
                            class="form-control form-control-lg @error('titre') is-invalid @enderror"
                            placeholder="Ex. Introduction à Laravel"
                            maxlength="255"
                            required
                        >

                        @error('titre')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                        <div class="form-text">
                            Donnez un titre clair et facilement identifiable à votre chapitre.
                        </div>

                    </div>


                    {{-- Contenu --}}
                    <div class="mb-4">

                        <label
                            for="contenu"
                            class="form-label fw-semibold"
                        >
                            Contenu du chapitre
                        </label>

                        <textarea
                            id="contenu"
                            name="contenu"
                            rows="8"
                            class="form-control @error('contenu') is-invalid @enderror"
                            placeholder="Présentez ici le contenu ou les informations générales du chapitre..."
                        >{{ old('contenu') }}</textarea>

                        @error('contenu')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                        <div class="form-text">
                            Vous pourrez ensuite ajouter des documents, vidéos ou liens comme ressources.
                        </div>

                    </div>


                    {{-- Ordre --}}
                    <div class="mb-4">

                        <label
                            for="ordre"
                            class="form-label fw-semibold"
                        >
                            Position du chapitre
                            <span class="text-danger">*</span>
                        </label>

                        <div class="ordre-input">

                            <input
                                type="number"
                                id="ordre"
                                name="ordre"
                                value="{{ old('ordre', $prochainOrdre) }}"
                                min="1"
                                class="form-control @error('ordre') is-invalid @enderror"
                                required
                            >

                            <span class="ordre-indicator">
                                <i class="ri-list-ordered"></i>
                            </span>

                        </div>

                        @error('ordre')

                            <div class="text-danger small mt-1">
                                {{ $message }}
                            </div>

                        @enderror

                        <div class="form-text">
                            Le prochain numéro disponible est
                            <strong>{{ $prochainOrdre }}</strong>.
                        </div>

                    </div>


                    {{-- Actions --}}
                    <div class="form-actions">

                        <a
                            href="{{ route('admin.cours.contenus', $cours) }}"
                            class="btn btn-light"
                        >
                            Annuler
                        </a>

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            <i class="ri-check-line me-1"></i>
                            Créer le chapitre
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>


    {{-- =====================================================
         PANNEAU LATÉRAL
    ====================================================== --}}
    <div class="col-lg-4">

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-body p-4">

                <div class="side-icon mb-3">

                    <i class="ri-information-line"></i>

                </div>

                <h5 class="fw-bold mb-2">
                    À propos des chapitres
                </h5>

                <p class="text-muted small mb-4">
                    Les chapitres permettent de structurer votre formation
                    en différentes étapes pédagogiques.
                </p>


                <div class="info-item">

                    <div class="info-item-icon">
                        <i class="ri-file-text-line"></i>
                    </div>

                    <div>
                        <strong>Documents</strong>
                        <span>
                            Supports de cours et fichiers PDF.
                        </span>
                    </div>

                </div>


                <div class="info-item">

                    <div class="info-item-icon">
                        <i class="ri-video-line"></i>
                    </div>

                    <div>
                        <strong>Vidéos</strong>
                        <span>
                            Contenus vidéo accessibles aux apprenants.
                        </span>
                    </div>

                </div>


                <div class="info-item">

                    <div class="info-item-icon">
                        <i class="ri-links-line"></i>
                    </div>

                    <div>
                        <strong>Liens</strong>
                        <span>
                            Ressources externes utiles au chapitre.
                        </span>
                    </div>

                </div>

            </div>

        </div>


        {{-- Aperçu --}}
        <div class="card border-0 shadow-sm">

            <div class="card-body p-4">

                <div class="small text-muted mb-2">
                    FORMATION
                </div>

                <h6 class="fw-bold mb-3">
                    {{ $cours->titre }}
                </h6>

                <div class="preview-chapter">

                    <div class="preview-number">
                        {{ $prochainOrdre }}
                    </div>

                    <div>

                        <div class="small text-muted">
                            Nouveau chapitre
                        </div>

                        <div class="fw-semibold">
                            {{ old('titre', 'Titre du chapitre') }}
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


</div>

{{-- =============================================================
STYLE
============================================================= --}}

<style>

    /*
     * Breadcrumb
     */
    .breadcrumb-link {
        color: #6c757d;
        text-decoration: none;
        transition: color 0.15s ease;
    }

    .breadcrumb-link:hover {
        color: #0f47ad;
    }


    /*
     * Icône du formulaire
     */
    .form-icon {
        width: 44px;
        height: 44px;
        min-width: 44px;
        border-radius: 10px;
        background: rgba(15, 71, 173, 0.08);
        color: #0f47ad;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 21px;
    }


    /*
     * Champs
     */
    .form-label {
        color: #273142;
    }

    .form-control {
        border-color: #e2e6ea;
        border-radius: 9px;
        padding: 10px 13px;
    }

    .form-control:focus {
        border-color: #0f47ad;
        box-shadow: 0 0 0 0.2rem rgba(15, 71, 173, 0.08);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 160px;
    }


    /*
     * Ordre
     */
    .ordre-input {
        position: relative;
    }

    .ordre-input .form-control {
        padding-right: 45px;
    }

    .ordre-indicator {
        position: absolute;
        top: 50%;
        right: 14px;
        transform: translateY(-50%);
        color: #8a94a6;
        pointer-events: none;
    }


    /*
     * Actions
     */
    .form-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 10px;
        padding-top: 22px;
        border-top: 1px solid #edf0f3;
    }


    /*
     * Panneau d'information
     */
    .side-icon {
        width: 45px;
        height: 45px;
        border-radius: 10px;
        background: rgba(15, 71, 173, 0.08);
        color: #0f47ad;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 21px;
    }

    .info-item {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        padding: 13px 0;
        border-top: 1px solid #edf0f3;
    }

    .info-item-icon {
        width: 34px;
        height: 34px;
        min-width: 34px;
        border-radius: 8px;
        background: #f5f7fa;
        color: #0f47ad;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .info-item strong {
        display: block;
        font-size: 13px;
        margin-bottom: 2px;
    }

    .info-item span {
        display: block;
        font-size: 11px;
        line-height: 1.5;
        color: #8a94a6;
    }


    /*
     * Aperçu du chapitre
     */
    .preview-chapter {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px;
        background: #f8f9fa;
        border-radius: 10px;
    }

    .preview-number {
        width: 38px;
        height: 38px;
        min-width: 38px;
        border-radius: 9px;
        background: #0f47ad;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 13px;
    }


    /*
     * Responsive
     */
    @media (max-width: 991.98px) {

        .col-lg-4 {
            margin-top: 0;
        }

    }

    @media (max-width: 575.98px) {

        .form-actions {
            flex-direction: column-reverse;
            align-items: stretch;
        }

        .form-actions .btn {
            width: 100%;
        }

    }

</style>

@endsection
