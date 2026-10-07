@extends('layouts.master')

@section('title', 'Modifier le chapitre')

@section('content')

<div class="chapter-edit-page">

    {{-- Fil d'Ariane --}}
    <div class="mb-4">
        <div class="d-flex align-items-center flex-wrap gap-2 text-muted small">

            <a href="{{ route('admin.cours.index') }}"
               class="text-decoration-none text-muted">
                Cours
            </a>

            <span>/</span>

            <a href="{{ route('admin.cours.show', $cours) }}"
               class="text-decoration-none text-muted">
                {{ $cours->titre }}
            </a>

            <span>/</span>

            <a href="{{ route('admin.cours.contenus', $cours) }}"
               class="text-decoration-none text-muted">
                Contenus pédagogiques
            </a>

            <span>/</span>

            <span class="text-dark">
                Modifier le chapitre
            </span>

        </div>
    </div>


    {{-- En-tête --}}
    <div class="page-header mb-4">

        <div>
            <h1 class="page-title">
                Modifier le chapitre
            </h1>

            <p class="page-subtitle">
                Modifiez les informations de ce chapitre pédagogique.
            </p>
        </div>

        <a href="{{ route('admin.cours.contenus', [$cours, $chapitre]) }}"
           class="btn btn-outline-secondary back-button">
            <i class="bi bi-arrow-left me-1"></i>
            Retour
        </a>

    </div>


    {{-- Erreurs --}}
    @if ($errors->any())

        <div class="alert alert-danger shadow-sm border-0 rounded-3 mb-4">

            <div class="fw-semibold mb-2">
                <i class="bi bi-exclamation-triangle me-1"></i>
                Vérifiez les informations saisies.
            </div>

            <ul class="mb-0 ps-3">

                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach

            </ul>

        </div>

    @endif


    {{-- Contenu --}}
    <div class="edit-layout">

        {{-- FORMULAIRE --}}
        <div class="main-column">

            <div class="content-card">

                <div class="card-header-custom">

                    <div>
                        <h5>
                            Informations du chapitre
                        </h5>

                        <p>
                            Modifiez le titre, le contenu et la position du chapitre.
                        </p>
                    </div>

                </div>


                <div class="card-body-custom">

                    <form method="POST"
                          action="{{ route('admin.cours.chapitres.update', [$cours, $chapitre]) }}">

                        @csrf
                        @method('PUT')


                        {{-- TITRE --}}
                        <div class="form-group-custom">

                            <label for="titre">
                                Titre du chapitre
                                <span class="required">*</span>
                            </label>

                            <input
                                type="text"
                                id="titre"
                                name="titre"
                                value="{{ old('titre', $chapitre->titre) }}"
                                class="form-control-custom @error('titre') input-error @enderror"
                                placeholder="Exemple : Introduction à Laravel"
                                required
                            >

                            @error('titre')
                                <div class="error-message">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        {{-- CONTENU --}}
                        <div class="form-group-custom">

                            <label for="contenu">
                                Contenu du chapitre
                            </label>

                            <textarea
                                id="contenu"
                                name="contenu"
                                rows="11"
                                class="form-control-custom textarea-custom @error('contenu') input-error @enderror"
                                placeholder="Saisissez ici le contenu pédagogique du chapitre..."
                            >{{ old('contenu', $chapitre->contenu) }}</textarea>

                            @error('contenu')
                                <div class="error-message">
                                    {{ $message }}
                                </div>
                            @enderror

                            <div class="help-text">
                                Le texte pédagogique principal du chapitre.
                                Les documents, vidéos, liens et images sont gérés depuis
                                la section des ressources.
                            </div>

                        </div>


                        {{-- ORDRE --}}
                        <div class="form-group-custom order-group">

                            <label for="ordre">
                                Ordre du chapitre
                                <span class="required">*</span>
                            </label>

                            <input
                                type="number"
                                id="ordre"
                                name="ordre"
                                min="1"
                                value="{{ old('ordre', $chapitre->ordre) }}"
                                class="form-control-custom order-input @error('ordre') input-error @enderror"
                                required
                            >

                            @error('ordre')
                                <div class="error-message">
                                    {{ $message }}
                                </div>
                            @enderror

                            <div class="help-text">
                                Détermine la position du chapitre dans le cours.
                            </div>

                        </div>


                        {{-- ACTIONS --}}
                        <div class="form-actions">

                            <a href="{{ route('admin.cours.contenus', [$cours, $chapitre]) }}"
                               class="btn-cancel">
                                Annuler
                            </a>

                            <button type="submit"
                                    class="btn-save">
                                <i class="bi bi-check-lg"></i>
                                Enregistrer les modifications
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>


        {{-- SIDEBAR DROITE --}}
        <div class="side-column">

            {{-- Informations --}}
            <div class="side-card">

                <div class="side-card-header">

                    <div class="info-icon">
                        <i class="bi bi-info-lg"></i>
                    </div>

                    <h5>
                        À propos du chapitre
                    </h5>

                </div>


                <p class="side-description">
                    Le contenu textuel permet de présenter les notions principales
                    du chapitre directement aux apprenants.
                </p>


                <div class="feature-list">

                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-file-text"></i>
                        </div>

                        <span>
                            Contenu pédagogique
                        </span>
                    </div>


                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-paperclip"></i>
                        </div>

                        <span>
                            Documents et fichiers
                        </span>
                    </div>


                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-camera-video"></i>
                        </div>

                        <span>
                            Vidéos
                        </span>
                    </div>


                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-link-45deg"></i>
                        </div>

                        <span>
                            Liens externes
                        </span>
                    </div>

                </div>

            </div>


            {{-- Aperçu --}}
            <div class="side-card">

                <div class="preview-title">
                    Aperçu
                </div>

                <div class="preview-box">

                    <div class="preview-content">

                        <div class="chapter-number">
                            {{ $chapitre->ordre }}
                        </div>

                        <div class="preview-text">

                            <div class="preview-chapter-title">
                                {{ $chapitre->titre }}
                            </div>

                            <div class="preview-order">
                                Chapitre {{ $chapitre->ordre }}
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<style>

    /*
    |--------------------------------------------------------------------------
    | PAGE
    |--------------------------------------------------------------------------
    */

    .chapter-edit-page {
        width: 100%;
        max-width: 1400px;
        margin: 0 auto;
        padding: 10px 24px 40px;
        box-sizing: border-box;
    }


    /*
    |--------------------------------------------------------------------------
    | HEADER
    |--------------------------------------------------------------------------
    */

    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
    }

    .page-title {
        margin: 0;
        font-size: 28px;
        font-weight: 700;
        color: #1f2937;
    }

    .page-subtitle {
        margin: 6px 0 0;
        color: #6b7280;
        font-size: 14px;
    }

    .back-button {
        white-space: nowrap;
    }


    /*
    |--------------------------------------------------------------------------
    | LAYOUT
    |--------------------------------------------------------------------------
    */

    .edit-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 310px;
        gap: 24px;
        align-items: start;
    }

    .main-column {
        min-width: 0;
    }

    .side-column {
        min-width: 0;
    }


    /*
    |--------------------------------------------------------------------------
    | CARTES
    |--------------------------------------------------------------------------
    */

    .content-card,
    .side-card {
        background: #ffffff;
        border: 1px solid #e9edf2;
        border-radius: 16px;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
    }

    .card-header-custom {
        padding: 24px 26px 18px;
        border-bottom: 1px solid #edf0f3;
    }

    .card-header-custom h5 {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: #1f2937;
    }

    .card-header-custom p {
        margin: 5px 0 0;
        color: #6b7280;
        font-size: 13px;
    }

    .card-body-custom {
        padding: 26px;
    }


    /*
    |--------------------------------------------------------------------------
    | FORMULAIRE
    |--------------------------------------------------------------------------
    */

    .form-group-custom {
        margin-bottom: 24px;
    }

    .form-group-custom label {
        display: block;
        margin-bottom: 8px;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
    }

    .required {
        color: #dc3545;
    }

    .form-control-custom {
        display: block;
        width: 100%;
        min-width: 0;
        padding: 12px 14px;
        border: 1px solid #dfe4ea;
        border-radius: 10px;
        background: #ffffff;
        color: #1f2937;
        font-size: 14px;
        outline: none;
        transition: all 0.2s ease;
        box-sizing: border-box;
    }

    .form-control-custom:focus {
        border-color: #0f47ad;
        box-shadow: 0 0 0 3px rgba(15, 71, 173, 0.10);
    }

    .textarea-custom {
        resize: vertical;
        min-height: 240px;
        line-height: 1.6;
    }

    .order-input {
        max-width: 180px;
    }

    .help-text {
        margin-top: 7px;
        font-size: 12px;
        color: #8a94a3;
        line-height: 1.5;
    }

    .input-error {
        border-color: #dc3545;
    }

    .error-message {
        margin-top: 6px;
        color: #dc3545;
        font-size: 12px;
    }


    /*
    |--------------------------------------------------------------------------
    | ACTIONS
    |--------------------------------------------------------------------------
    */

    .form-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 10px;
        padding-top: 22px;
        border-top: 1px solid #edf0f3;
    }

    .btn-cancel,
    .btn-save {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 42px;
        padding: 0 18px;
        border-radius: 9px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-cancel {
        background: #f4f5f7;
        color: #4b5563;
        border: 1px solid #e5e7eb;
    }

    .btn-cancel:hover {
        background: #e9ebee;
        color: #374151;
    }

    .btn-save {
        border: none;
        background: #0f47ad;
        color: #ffffff;
    }

    .btn-save:hover {
        background: #0c3b91;
    }


    /*
    |--------------------------------------------------------------------------
    | SIDEBAR DROITE
    |--------------------------------------------------------------------------
    */

    .side-card {
        padding: 20px;
        margin-bottom: 20px;
    }

    .side-card:last-child {
        margin-bottom: 0;
    }

    .side-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 14px;
    }

    .side-card-header h5 {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        color: #1f2937;
    }

    .info-icon {
        width: 38px;
        height: 38px;
        min-width: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(15, 71, 173, 0.10);
        color: #0f47ad;
    }

    .side-description {
        margin: 0 0 18px;
        color: #6b7280;
        font-size: 13px;
        line-height: 1.6;
    }

    .feature-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .feature-item {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13px;
        color: #4b5563;
    }

    .feature-icon {
        width: 30px;
        height: 30px;
        min-width: 30px;
        border-radius: 8px;
        background: #f1f5fb;
        color: #0f47ad;
        display: flex;
        align-items: center;
        justify-content: center;
    }


    /*
    |--------------------------------------------------------------------------
    | APERÇU
    |--------------------------------------------------------------------------
    */

    .preview-title {
        margin-bottom: 14px;
        font-size: 15px;
        font-weight: 700;
        color: #1f2937;
    }

    .preview-box {
        padding: 14px;
        border: 1px solid #edf0f3;
        border-radius: 12px;
        background: #f8f9fb;
    }

    .preview-content {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .chapter-number {
        width: 38px;
        height: 38px;
        min-width: 38px;
        border-radius: 10px;
        background: #0f47ad;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 700;
    }

    .preview-text {
        min-width: 0;
    }

    .preview-chapter-title {
        color: #1f2937;
        font-size: 13px;
        font-weight: 600;
        word-break: break-word;
    }

    .preview-order {
        margin-top: 3px;
        color: #8a94a3;
        font-size: 11px;
    }


    /*
    |--------------------------------------------------------------------------
    | RESPONSIVE
    |--------------------------------------------------------------------------
    */

    @media (max-width: 1100px) {

        .edit-layout {
            grid-template-columns: minmax(0, 1fr) 270px;
            gap: 18px;
        }

        .chapter-edit-page {
            padding-left: 18px;
            padding-right: 18px;
        }

    }


    @media (max-width: 900px) {

        .edit-layout {
            grid-template-columns: 1fr;
        }

        .side-column {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .side-card {
            margin-bottom: 0;
        }

    }


    @media (max-width: 650px) {

        .chapter-edit-page {
            padding: 10px 14px 30px;
        }

        .page-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .page-title {
            font-size: 23px;
        }

        .card-body-custom {
            padding: 18px;
        }

        .card-header-custom {
            padding: 18px;
        }

        .side-column {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column-reverse;
            align-items: stretch;
        }

        .btn-cancel,
        .btn-save {
            width: 100%;
        }

        .order-input {
            max-width: 100%;
        }

    }

</style>

@endsection