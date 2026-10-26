@extends('layouts.master')

@section('title', 'Ajouter une ressource')

@section('content')

<div class="resource-create-page">

    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('admin.cours.contenus', [$cours, $chapitre]) }}">{{ $cours->titre }}</a>
        @endslot
        @slot('title')
            Ajouter une ressource
        @endslot
    @endcomponent


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
                            class="resource-field d-none"
                            id="fichierField"
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
                            class="resource-field d-none"
                            id="videoSourceField"
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
                            class="resource-field d-none"
                            id="videoFileField"
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
                            class="resource-field d-none"
                            id="urlField"
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

    fichierField.classList.add('d-none');
    videoSourceField.classList.add('d-none');
    videoFileField.classList.add('d-none');
    urlField.classList.add('d-none');


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

        fichierField.classList.remove('d-none');

        fichierInput.disabled = false;
        fichierInput.setAttribute('required', 'required');

    }


    /*
    |--------------------------------------------------------------------------
    | VIDEO
    |--------------------------------------------------------------------------
    */

    if (type === 'video') {

        videoSourceField.classList.remove('d-none');

        updateVideoSource();

    }


    /*
    |--------------------------------------------------------------------------
    | LIEN
    |--------------------------------------------------------------------------
    */

    if (type === 'lien') {

        urlField.classList.remove('d-none');

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

            videoFileField.classList.remove('d-none');
            urlField.classList.add('d-none');

            videoFileInput.disabled = false;
            videoFileInput.setAttribute('required', 'required');

        }


    // -------------------------------------------------
    // UTILISER UNE URL
    // -------------------------------------------------

    if (videoUrlRadio.checked) {

        videoFileField.classList.add('d-none');
        urlField.classList.remove('d-none');

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