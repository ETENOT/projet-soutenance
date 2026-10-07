@extends('layouts.master')

@section('title', 'Ajouter une ressource')

@section('content')

<div class="resource-create-page">

    {{-- Fil d'Ariane --}}
    <div class="mb-4">
        <div class="breadcrumb-custom">

            <a href="{{ route('admin.cours.index') }}">
                Cours
            </a>

            <span>/</span>

            <a href="{{ route('admin.cours.show', $cours) }}">
                {{ $cours->titre }}
            </a>

            <span>/</span>

            <a href="{{ route('admin.cours.contenus', [$cours, $chapitre]) }}">
                Contenus pédagogiques
            </a>

            <span>/</span>

            <span class="current">
                Ajouter une ressource
            </span>

        </div>
    </div>


    {{-- En-tête --}}
    <div class="page-header mb-4">

        <div>
            <h1 class="page-title">
                Ajouter une ressource
            </h1>

            <p class="page-subtitle">
                Ajoutez un document, une vidéo ou un lien à ce chapitre.
            </p>
        </div>

        <a href="{{ route('admin.cours.contenus', [$cours, $chapitre]) }}"
           class="btn-back">
            <i class="bi bi-arrow-left"></i>
            Retour
        </a>

    </div>


    {{-- Erreurs --}}
    @if ($errors->any())

        <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4">

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


    <div class="resource-layout">

        {{-- FORMULAIRE --}}
        <div class="resource-main">

            <div class="resource-card">

                <div class="resource-card-header">

                    <div>
                        <h5>
                            Informations de la ressource
                        </h5>

                        <p>
                            Ressource pour :
                            <strong>{{ $chapitre->titre }}</strong>
                        </p>
                    </div>

                </div>


                <div class="resource-card-body">

                    <form
                        method="POST"
                        action="{{ route('admin.cours.chapitres.resources.store', [$cours, $chapitre]) }}"
                        enctype="multipart/form-data"
                        id="resourceForm"
                    >

                        @csrf


                        {{-- TITRE --}}
                        <div class="form-group">

                            <label for="titre">
                                Titre de la ressource
                                <span class="required">*</span>
                            </label>

                            <input
                                type="text"
                                id="titre"
                                name="titre"
                                value="{{ old('titre') }}"
                                class="form-control-custom @error('titre') input-error @enderror"
                                placeholder="Exemple : Support de cours Laravel"
                                required
                            >

                            @error('titre')
                                <div class="error-message">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        {{-- TYPE --}}
                        <div class="form-group">

                            <label for="type">
                                Type de ressource
                                <span class="required">*</span>
                            </label>

                            <select
                                id="type"
                                name="type"
                                class="form-control-custom @error('type') input-error @enderror"
                                required
                            >

                                <option value="">
                                    Sélectionnez un type
                                </option>

                                <option value="fichier"
                                    {{ old('type') === 'fichier' ? 'selected' : '' }}>
                                    📄 Document / Image
                                </option>

                                <option value="video"
                                    {{ old('type') === 'video' ? 'selected' : '' }}>
                                    🎥 Vidéo
                                </option>

                                <option value="lien"
                                    {{ old('type') === 'lien' ? 'selected' : '' }}>
                                    🔗 Lien externe
                                </option>

                            </select>

                            @error('type')
                                <div class="error-message">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        {{-- ================================
                             FICHIER DOCUMENT / IMAGE
                        ================================= --}}

                        <div
                            class="resource-field"
                            id="fichierField"
                            style="display: none;"
                        >

                            <label for="fichier">
                                Fichier
                                <span class="required">*</span>
                            </label>

                            <div class="file-upload-box">

                                <div class="file-icon">
                                    <i class="bi bi-cloud-arrow-up"></i>
                                </div>

                                <div class="file-content">

                                    <div class="file-title">
                                        Sélectionner un fichier
                                    </div>

                                    <div class="file-description">
                                        PDF, Word, Excel, PowerPoint, JPG, PNG, etc.
                                    </div>

                                    <input
                                        type="file"
                                        id="fichier"
                                        name="fichier"
                                        class="form-control-custom mt-3 @error('fichier') input-error @enderror"
                                    >

                                </div>

                            </div>

                            <div class="help-text">
                                Taille maximale : 1 Go.
                            </div>

                            @error('fichier')
                                <div class="error-message">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        {{-- ================================
                             SOURCE VIDEO
                        ================================= --}}

                        <div
                            class="resource-field"
                            id="videoSourceField"
                            style="display: none;"
                        >

                            <label>
                                Source de la vidéo
                                <span class="required">*</span>
                            </label>


                            <div class="video-source-options">

                                {{-- IMPORTER --}}
                                <label
                                    class="video-source-option"
                                    for="videoUpload"
                                >

                                    <input
                                        type="radio"
                                        id="videoUpload"
                                        name="video_source"
                                        value="upload"
                                        {{ old('video_source', 'upload') === 'upload' ? 'checked' : '' }}
                                    >

                                    <div class="video-option-content">

                                        <div class="video-option-icon">
                                            <i class="bi bi-cloud-arrow-up"></i>
                                        </div>

                                        <div>
                                            <strong>
                                                Importer une vidéo
                                            </strong>

                                            <small>
                                                Envoyer une vidéo depuis votre ordinateur.
                                            </small>
                                        </div>

                                    </div>

                                </label>


                                {{-- URL --}}
                                <label
                                    class="video-source-option"
                                    for="videoUrl"
                                >

                                    <input
                                        type="radio"
                                        id="videoUrl"
                                        name="video_source"
                                        value="url"
                                        {{ old('video_source') === 'url' ? 'checked' : '' }}
                                    >

                                    <div class="video-option-content">

                                        <div class="video-option-icon">
                                            <i class="bi bi-link-45deg"></i>
                                        </div>

                                        <div>
                                            <strong>
                                                Utiliser une URL
                                            </strong>

                                            <small>
                                                YouTube, Vimeo ou autre vidéo externe.
                                            </small>
                                        </div>

                                    </div>

                                </label>

                            </div>

                        </div>


                        {{-- ================================
                             UPLOAD VIDEO
                        ================================= --}}

                        <div
                            class="resource-field"
                            id="videoFileField"
                            style="display: none;"
                        >

                            <label for="videoFile">
                                Vidéo
                                <span class="required">*</span>
                            </label>

                            <div class="file-upload-box video-upload-box">

                                <div class="file-icon video-file-icon">
                                    <i class="bi bi-camera-video"></i>
                                </div>

                                <div class="file-content">

                                    <div class="file-title">
                                        Sélectionner une vidéo
                                    </div>

                                    <div class="file-description">
                                        MP4, WebM ou MOV.
                                    </div>

                                    <input
                                        type="file"
                                        id="videoFile"
                                        name="fichier"
                                        accept="video/mp4,video/webm,video/quicktime"
                                        class="form-control-custom mt-3 @error('fichier') input-error @enderror"
                                    >

                                </div>

                            </div>

                            <div class="help-text">
                                Taille maximale : 1 Go.
                            </div>

                            @error('fichier')
                                <div class="error-message">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        {{-- ================================
                             URL
                        ================================= --}}

                        <div
                            class="resource-field"
                            id="urlField"
                            style="display: none;"
                        >

                            <label for="url">
                                URL de la ressource
                                <span class="required">*</span>
                            </label>

                            <input
                                type="url"
                                id="url"
                                name="url"
                                value="{{ old('url') }}"
                                class="form-control-custom @error('url') input-error @enderror"
                                placeholder="https://..."
                            >

                            <div class="help-text" id="urlHelp">
                                Entrez l'adresse URL de la ressource.
                            </div>

                            @error('url')
                                <div class="error-message">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        {{-- ACTIONS --}}
                        <div class="form-actions">

                            <a
                                href="{{ route('admin.cours.contenus', [$cours, $chapitre]) }}"
                                class="btn-cancel"
                            >
                                Annuler
                            </a>

                            <button
                                type="submit"
                                class="btn-save"
                            >
                                <i class="bi bi-plus-lg"></i>
                                Ajouter la ressource
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>


        {{-- ================================
             COLONNE DROITE
        ================================= --}}

        <div class="resource-side">

            {{-- Chapitre --}}
            <div class="side-card">

                <div class="side-title">

                    <div class="side-icon">
                        <i class="bi bi-journal-text"></i>
                    </div>

                    <span>
                        Chapitre concerné
                    </span>

                </div>

                <div class="chapter-info">

                    <div class="chapter-number">
                        {{ $chapitre->ordre }}
                    </div>

                    <div class="chapter-details">

                        <div class="chapter-name">
                            {{ $chapitre->titre }}
                        </div>

                        <div class="chapter-label">
                            Chapitre {{ $chapitre->ordre }}
                        </div>

                    </div>

                </div>

            </div>


            {{-- Types de ressources --}}
            <div class="side-card">

                <div class="side-title">

                    <div class="side-icon">
                        <i class="bi bi-collection"></i>
                    </div>

                    <span>
                        Types disponibles
                    </span>

                </div>


                <div class="resource-types">

                    <div class="resource-type">

                        <div class="type-icon document-icon">
                            <i class="bi bi-file-earmark"></i>
                        </div>

                        <div>
                            <strong>
                                Document / Image
                            </strong>

                            <small>
                                PDF, Word, Excel, JPG, PNG...
                            </small>
                        </div>

                    </div>


                    <div class="resource-type">

                        <div class="type-icon video-icon">
                            <i class="bi bi-play-circle"></i>
                        </div>

                        <div>
                            <strong>
                                Vidéo
                            </strong>

                            <small>
                                Importée ou URL externe
                            </small>
                        </div>

                    </div>


                    <div class="resource-type">

                        <div class="type-icon link-icon">
                            <i class="bi bi-link-45deg"></i>
                        </div>

                        <div>
                            <strong>
                                Lien externe
                            </strong>

                            <small>
                                Documentation, site web...
                            </small>
                        </div>

                    </div>

                </div>

            </div>


            {{-- Information --}}
            <div class="info-card">

                <i class="bi bi-lightbulb"></i>

                <div>

                    <strong>
                        Conseil
                    </strong>

                    <p>
                        Utilisez un titre clair pour permettre aux apprenants
                        d'identifier facilement la ressource.
                    </p>

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

    .resource-create-page {
        width: 100%;
        max-width: 1400px;
        margin: 0 auto;
        padding: 10px 24px 40px;
        box-sizing: border-box;
    }


    /*
    |--------------------------------------------------------------------------
    | BREADCRUMB
    |--------------------------------------------------------------------------
    */

    .breadcrumb-custom {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        font-size: 13px;
        color: #8a94a3;
    }

    .breadcrumb-custom a {
        color: #6b7280;
        text-decoration: none;
    }

    .breadcrumb-custom a:hover {
        color: #0f47ad;
    }

    .breadcrumb-custom .current {
        color: #374151;
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
        color: #1f2937;
        font-size: 28px;
        font-weight: 700;
    }

    .page-subtitle {
        margin: 6px 0 0;
        color: #6b7280;
        font-size: 14px;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 10px 16px;
        border: 1px solid #dfe4ea;
        border-radius: 9px;
        background: #ffffff;
        color: #4b5563;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        white-space: nowrap;
    }

    .btn-back:hover {
        background: #f7f8fa;
        color: #0f47ad;
    }


    /*
    |--------------------------------------------------------------------------
    | LAYOUT
    |--------------------------------------------------------------------------
    */

    .resource-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 310px;
        gap: 24px;
        align-items: start;
    }

    .resource-main,
    .resource-side {
        min-width: 0;
    }


    /*
    |--------------------------------------------------------------------------
    | CARTES
    |--------------------------------------------------------------------------
    */

    .resource-card,
    .side-card {
        background: #ffffff;
        border: 1px solid #e9edf2;
        border-radius: 16px;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
    }

    .resource-card-header {
        padding: 24px 26px 18px;
        border-bottom: 1px solid #edf0f3;
    }

    .resource-card-header h5 {
        margin: 0;
        color: #1f2937;
        font-size: 18px;
        font-weight: 700;
    }

    .resource-card-header p {
        margin: 5px 0 0;
        color: #6b7280;
        font-size: 13px;
    }

    .resource-card-header strong {
        color: #374151;
    }

    .resource-card-body {
        padding: 26px;
    }


    /*
    |--------------------------------------------------------------------------
    | FORMULAIRE
    |--------------------------------------------------------------------------
    */

    .form-group,
    .resource-field {
        margin-bottom: 24px;
    }

    .form-group > label,
    .resource-field > label {
        display: block;
        margin-bottom: 8px;
        color: #374151;
        font-size: 14px;
        font-weight: 600;
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
        box-sizing: border-box;
        transition: all 0.2s ease;
    }

    .form-control-custom:focus {
        border-color: #0f47ad;
        box-shadow: 0 0 0 3px rgba(15, 71, 173, 0.10);
    }

    select.form-control-custom {
        cursor: pointer;
    }

    .input-error {
        border-color: #dc3545;
    }

    .error-message {
        margin-top: 6px;
        color: #dc3545;
        font-size: 12px;
    }

    .help-text {
        margin-top: 7px;
        color: #8a94a3;
        font-size: 12px;
        line-height: 1.5;
    }


    /*
    |--------------------------------------------------------------------------
    | CHOIX SOURCE VIDEO
    |--------------------------------------------------------------------------
    */

    .video-source-options {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .video-source-option {
        display: block;
        padding: 15px;
        border: 1px solid #dfe4ea;
        border-radius: 12px;
        background: #ffffff;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .video-source-option:hover {
        border-color: #b8c8e4;
        background: #fafcff;
    }

    .video-source-option:has(input:checked) {
        border-color: #0f47ad;
        background: #f5f8ff;
        box-shadow: 0 0 0 2px rgba(15, 71, 173, 0.08);
    }

    .video-source-option > input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .video-option-content {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .video-option-icon {
        width: 42px;
        height: 42px;
        min-width: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: #eef3ff;
        color: #0f47ad;
        font-size: 18px;
    }

    .video-option-content strong {
        display: block;
        color: #374151;
        font-size: 13px;
    }

    .video-option-content small {
        display: block;
        margin-top: 3px;
        color: #8a94a3;
        font-size: 11px;
        line-height: 1.4;
    }


    /*
    |--------------------------------------------------------------------------
    | UPLOAD
    |--------------------------------------------------------------------------
    */

    .file-upload-box {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 18px;
        border: 1px dashed #cbd3df;
        border-radius: 12px;
        background: #fafbfc;
    }

    .file-icon {
        width: 48px;
        height: 48px;
        min-width: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: rgba(15, 71, 173, 0.10);
        color: #0f47ad;
        font-size: 22px;
    }

    .video-file-icon {
        background: #f1ecff;
        color: #6842c2;
    }

    .file-content {
        flex: 1;
        min-width: 0;
    }

    .file-title {
        color: #1f2937;
        font-size: 14px;
        font-weight: 600;
    }

    .file-description {
        margin-top: 3px;
        color: #8a94a3;
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
    }

    .btn-cancel {
        border: 1px solid #e5e7eb;
        background: #f4f5f7;
        color: #4b5563;
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
    | COLONNE DROITE
    |--------------------------------------------------------------------------
    */

    .side-card {
        padding: 20px;
        margin-bottom: 20px;
    }

    .side-card:last-child {
        margin-bottom: 0;
    }

    .side-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 16px;
        color: #1f2937;
        font-size: 15px;
        font-weight: 700;
    }

    .side-icon {
        width: 36px;
        height: 36px;
        min-width: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        background: rgba(15, 71, 173, 0.10);
        color: #0f47ad;
    }


    /*
    |--------------------------------------------------------------------------
    | CHAPITRE
    |--------------------------------------------------------------------------
    */

    .chapter-info {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        border-radius: 10px;
        background: #f8f9fb;
    }

    .chapter-number {
        width: 38px;
        height: 38px;
        min-width: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: #0f47ad;
        color: #ffffff;
        font-size: 14px;
        font-weight: 700;
    }

    .chapter-details {
        min-width: 0;
    }

    .chapter-name {
        color: #1f2937;
        font-size: 13px;
        font-weight: 600;
        word-break: break-word;
    }

    .chapter-label {
        margin-top: 3px;
        color: #8a94a3;
        font-size: 11px;
    }


    /*
    |--------------------------------------------------------------------------
    | TYPES
    |--------------------------------------------------------------------------
    */

    .resource-types {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .resource-type {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .type-icon {
        width: 36px;
        height: 36px;
        min-width: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        font-size: 16px;
    }

    .document-icon {
        background: #eef3ff;
        color: #0f47ad;
    }

    .video-icon {
        background: #f1ecff;
        color: #6842c2;
    }

    .link-icon {
        background: #eaf8f1;
        color: #21895a;
    }

    .resource-type strong {
        display: block;
        color: #374151;
        font-size: 13px;
    }

    .resource-type small {
        display: block;
        margin-top: 2px;
        color: #8a94a3;
        font-size: 11px;
    }


    /*
    |--------------------------------------------------------------------------
    | INFO
    |--------------------------------------------------------------------------
    */

    .info-card {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 15px;
        border: 1px solid #e4ebf8;
        border-radius: 12px;
        background: #f5f8fe;
        color: #0f47ad;
    }

    .info-card > i {
        margin-top: 2px;
        font-size: 17px;
    }

    .info-card strong {
        display: block;
        margin-bottom: 4px;
        color: #1f2937;
        font-size: 13px;
    }

    .info-card p {
        margin: 0;
        color: #6b7280;
        font-size: 12px;
        line-height: 1.5;
    }


    /*
    |--------------------------------------------------------------------------
    | RESPONSIVE
    |--------------------------------------------------------------------------
    */

    @media (max-width: 1100px) {

        .resource-layout {
            grid-template-columns: minmax(0, 1fr) 270px;
            gap: 18px;
        }

        .resource-create-page {
            padding-left: 18px;
            padding-right: 18px;
        }

    }


    @media (max-width: 900px) {

        .resource-layout {
            grid-template-columns: 1fr;
        }

        .resource-side {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .side-card {
            margin-bottom: 0;
        }

        .info-card {
            grid-column: 1 / -1;
        }

    }


    @media (max-width: 700px) {

        .video-source-options {
            grid-template-columns: 1fr;
        }

    }


    @media (max-width: 650px) {

        .resource-create-page {
            padding: 10px 14px 30px;
        }

        .page-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .page-title {
            font-size: 23px;
        }

        .resource-card-header,
        .resource-card-body {
            padding: 18px;
        }

        .resource-side {
            display: flex;
            flex-direction: column;
        }

        .side-card {
            margin-bottom: 0;
        }

        .form-actions {
            flex-direction: column-reverse;
            align-items: stretch;
        }

        .btn-cancel,
        .btn-save {
            width: 100%;
        }

        .file-upload-box {
            align-items: flex-start;
            flex-direction: column;
        }

    }

</style>


<script>

document.addEventListener('DOMContentLoaded', function () {

    const typeSelect = document.getElementById('type');

    const fichierField = document.getElementById('fichierField');

    const videoSourceField = document.getElementById('videoSourceField');

    const videoFileField = document.getElementById('videoFileField');

    const urlField = document.getElementById('urlField');

    const fichierInput = document.getElementById('fichier');

    const videoFileInput = document.getElementById('videoFile');

    const urlInput = document.getElementById('url');

    const urlHelp = document.getElementById('urlHelp');

    const videoUploadRadio = document.getElementById('videoUpload');

    const videoUrlRadio = document.getElementById('videoUrl');


    /*
    |--------------------------------------------------------------------------
    | Gestion de l'affichage
    |--------------------------------------------------------------------------
    */

    function updateResourceFields() {

    const type = typeSelect.value;

    /*
    |--------------------------------------------------------------------------
    | Masquer tous les champs
    |--------------------------------------------------------------------------
    */

    fichierField.style.display = 'none';
    videoSourceField.style.display = 'none';
    videoFileField.style.display = 'none';
    urlField.style.display = 'none';


    /*
    |--------------------------------------------------------------------------
    | Désactiver tous les champs inutilisés
    |--------------------------------------------------------------------------
    */

    fichierInput.disabled = true;
    videoFileInput.disabled = true;
    urlInput.disabled = true;


    /*
    |--------------------------------------------------------------------------
    | Retirer les required
    |--------------------------------------------------------------------------
    */

    fichierInput.removeAttribute('required');
    videoFileInput.removeAttribute('required');
    urlInput.removeAttribute('required');


    /*
    |--------------------------------------------------------------------------
    | DOCUMENT / IMAGE
    |--------------------------------------------------------------------------
    */

    if (type === 'fichier') {

        fichierField.style.display = 'block';

        fichierInput.disabled = false;
        fichierInput.setAttribute('required', 'required');

    }


    /*
    |--------------------------------------------------------------------------
    | VIDEO
    |--------------------------------------------------------------------------
    */

    if (type === 'video') {

        videoSourceField.style.display = 'block';

        updateVideoSource();

    }


    /*
    |--------------------------------------------------------------------------
    | LIEN
    |--------------------------------------------------------------------------
    */

    if (type === 'lien') {

        urlField.style.display = 'block';

        urlInput.disabled = false;
        urlInput.setAttribute('required', 'required');

        urlHelp.textContent =
            'Entrez l URL du site ou de la documentation.';

    }

}


        /*
    |--------------------------------------------------------------------------
    | Événements
    |--------------------------------------------------------------------------
    */

    typeSelect.addEventListener(
        'change',
        updateResourceFields
    );


    /*
    |--------------------------------------------------------------------------
    | Gestion de la source vidéo
    |--------------------------------------------------------------------------
    */

    function updateVideoSource() {

        // Désactiver les deux champs au départ
        videoFileInput.disabled = true;
        urlInput.disabled = true;

        // Retirer les required
        videoFileInput.removeAttribute('required');
        urlInput.removeAttribute('required');


        // -------------------------------------------------
        // IMPORTER UNE VIDÉO
        // -------------------------------------------------

        if (videoUploadRadio.checked) {

            videoFileField.style.display = 'block';
            urlField.style.display = 'none';

            videoFileInput.disabled = false;
            videoFileInput.setAttribute('required', 'required');

        }


    // -------------------------------------------------
    // UTILISER UNE URL
    // -------------------------------------------------

    if (videoUrlRadio.checked) {

        videoFileField.style.display = 'none';
        urlField.style.display = 'block';

        urlInput.disabled = false;
        urlInput.setAttribute('required', 'required');

        urlHelp.textContent =
            'Entrez l URL de la vidéo, par exemple une vidéo YouTube ou Vimeo.';
    }
}


/*
|--------------------------------------------------------------------------
| Changement de source vidéo
|--------------------------------------------------------------------------
*/

videoUploadRadio.addEventListener(
    'change',
    updateVideoSource
);

videoUrlRadio.addEventListener(
    'change',
    updateVideoSource
);

    /*
    |--------------------------------------------------------------------------
    | Initialisation
    |--------------------------------------------------------------------------
    |
    | Permet de conserver les champs après une erreur
    | de validation Laravel.
    |
    */

    updateResourceFields();

});

</script>

@endsection