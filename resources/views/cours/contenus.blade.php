@extends('layouts.master')

@section('title', 'Contenus pédagogiques')

@section('content')

    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('admin.cours.show', $cours) }}">{{ $cours->titre }}</a>
        @endslot
        @slot('title')
            Contenus pédagogiques
        @endslot
    @endcomponent


<div class="contenus-page">

    {{-- =========================
         EN-TÊTE
         ========================= --}}

    <div class="page-header">

        <div>

            <h1 class="page-title">
                Contenus pédagogiques
            </h1>

            <p class="page-subtitle">
                Organisez les chapitres et les ressources de cette formation.
            </p>

        </div>


        <button type="button"
                class="btn-primary-custom"
                data-bs-toggle="modal"
                data-bs-target="#modalAjouterChapitre">

            <i class="fas fa-plus"></i>

            Ajouter un chapitre

        </button>

    </div>


    {{-- =========================
         MESSAGES
         ========================= --}}

    @if(session('success'))

        <div class="alert-custom alert-success-custom">

            <i class="fas fa-check-circle me-1"></i>

            {{ session('success') }}

        </div>

    @endif


    @if($errors->any())

        <div class="alert-custom alert-error-custom">

            <strong>

                <i class="fas fa-exclamation-circle me-1"></i>

                Une erreur est survenue.

            </strong>

            <ul>

                @foreach($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif


    <div class="contenus-layout">

        {{-- =========================================================
             COLONNE PRINCIPALE
             ========================================================= --}}

        <div>

            {{-- =========================
                 LISTE DES CHAPITRES
                 ========================= --}}

            <div class="card-custom chapitres-card">

                <div class="card-header-custom">

                    <div class="card-header-title">

                        <div class="header-icon">
                            <i class="fas fa-layer-group"></i>
                        </div>

                        <span>Chapitres</span>

                    </div>


                    <span class="nv-fs-11">

                        {{ $chapitres->count() }} chapitre(s)

                    </span>

                </div>


                <div class="chapitres-list">

                    @forelse($chapitres as $index => $item)

                        <button type="button"
                                class="chapitre-item {{ $index === 0 ? 'active' : '' }}"
                                data-chapter-id="{{ $item->id }}"
                                data-chapter-number="{{ $index + 1 }}"
                                data-chapter-title="{{ $item->titre }}">

                            <div class="chapitre-number">
                                {{ $index + 1 }}
                            </div>


                            <div class="chapitre-info">

                                <div class="chapitre-name">
                                    {{ $item->titre }}
                                </div>

                                <div class="chapitre-meta">
                                    Chapitre {{ $index + 1 }}
                                </div>

                            </div>


                            <div class="chapitre-arrow">

                                <i class="fas fa-chevron-right"></i>

                            </div>

                        </button>

                    @empty

                        <div class="nv-empty-chapters">

                            <i class="fas fa-layer-group nv-empty-chapters-icon"
                              ></i>

                            Aucun chapitre n'a encore été créé.

                        </div>

                    @endforelse

                </div>

            </div>


            {{-- =====================================================
                 ÉDITION DES CHAPITRES
                 ===================================================== --}}

            @if($chapitres->count())

                <div class="card-custom editor-card">

                    <div class="editor-header">

                        <div>

                            <h2 class="editor-title">
                                Modifier le chapitre
                            </h2>

                            <div class="editor-subtitle">
                                Modifiez les informations du chapitre sélectionné.
                            </div>

                        </div>

                    </div>


                    @foreach($chapitres as $index => $item)

                        <div class="chapter-panel {{ $index === 0 ? 'active' : '' }}"
                             data-chapter-panel="{{ $item->id }}">

                            <form method="POST"
                                  action="{{ route('admin.cours.chapitres.update', [$cours, $item]) }}"
                                  class="chapter-update-form">

                                @csrf

                                @method('PUT')


                                <div class="form-group-custom">

                                    <label class="form-label-custom">
                                        Titre du chapitre
                                    </label>

                                    <input type="text"
                                           name="titre"
                                           class="form-control-custom"
                                           value="{{ $item->titre }}"
                                           required>

                                </div>


                                <div class="form-group-custom">

                                    <label class="form-label-custom">
                                        Contenu / description
                                    </label>

                                    <textarea name="contenu"
                                              class="form-control-custom"
                                              placeholder="Décrivez le contenu de ce chapitre...">{{ $item->contenu }}</textarea>

                                </div>


                                <div class="editor-actions">

                                    <button type="submit"
                                            class="btn-save">

                                        <i class="fas fa-save me-1"></i>

                                        Enregistrer les modifications

                                    </button>

                                </div>

                            </form>


                            <div class="nv-chapter-delete-wrapper">

                                <form method="POST"
                                      action="{{ route('admin.cours.chapitres.destroy', [$cours, $item]) }}"
                                      onsubmit="return confirm('Voulez-vous vraiment supprimer ce chapitre ? Les ressources du cours ne seront pas supprimées.');">

                                    @csrf

                                    @method('DELETE')


                                    <button type="submit"
                                            class="btn-delete">

                                        <i class="fas fa-trash"></i>

                                        Supprimer ce chapitre

                                    </button>

                                </form>

                            </div>

                        </div>

                    @endforeach

                </div>

            @else

                <div class="card-custom nv-empty-chapters-state"
                    >

                    <i class="fas fa-hand-pointer nv-empty-state-icon"
                      ></i>

                    <h3 class="nv-empty-state-title">
                        Aucun chapitre
                    </h3>

                    <p class="nv-empty-state-description">
                        Créez un chapitre pour commencer à organiser le contenu de cette formation.
                    </p>

                </div>

            @endif

        </div>


        {{-- =========================================================
             COLONNE DROITE
             ========================================================= --}}

        <aside class="resource-side">

            {{-- =========================
                 CHAPITRE CONCERNÉ
                 ========================= --}}

            <div class="card-custom side-card">

                <div class="side-title">

                    <div class="side-icon">
                        <i class="fas fa-book-open"></i>
                    </div>

                    <span>Chapitre concerné</span>

                </div>


                @if($chapitres->count())

                    <div class="chapter-info">

                        <div class="chapter-number-side"
                             id="selectedChapterNumber">

                            1

                        </div>


                        <div class="chapter-details">

                            <div class="chapter-name-side"
                                 id="selectedChapterName">

                                {{ $chapitres->first()->titre }}

                            </div>


                            <div class="chapter-label">
                                Chapitre sélectionné
                            </div>

                        </div>

                    </div>

                @else

                    <div class="nv-no-chapter-selected">

                        Aucun chapitre sélectionné.

                    </div>

                @endif

            </div>


            {{-- =========================
                 RESSOURCES DU COURS
                 ========================= --}}

            <div class="card-custom side-card">

                <div class="side-title">

                    <div class="side-icon">
                        <i class="fas fa-paperclip"></i>
                    </div>

                    <span>Ressources du cours</span>

                </div>


                @if($resources->count())

                    <div class="resources-list">

                        @foreach($resources as $resource)

                            @php

                                if ($resource->type === 'video') {

                                    $resourceIcon = 'fa-play';
                                    $resourceClass = 'resource-video';
                                    $resourceType = 'Vidéo';

                                } elseif ($resource->type === 'lien') {

                                    $resourceIcon = 'fa-link';
                                    $resourceClass = 'resource-link';
                                    $resourceType = 'Lien';

                                } else {

                                    $resourceIcon = 'fa-file-alt';
                                    $resourceClass = 'resource-document';
                                    $resourceType = 'Document';

                                }

                            @endphp


                            <div class="resource-item">

                                <div class="resource-icon {{ $resourceClass }}">

                                    <i class="fas {{ $resourceIcon }}"></i>

                                </div>


                                <div class="resource-info">

                                    <div class="resource-name"
                                         title="{{ $resource->titre }}">

                                        {{ $resource->titre }}

                                    </div>


                                    <div class="resource-type-label">

                                        {{ $resourceType }}

                                        @if($resource->extension)

                                            · {{ strtoupper($resource->extension) }}

                                        @endif

                                        @if($resource->taille_lisible)

                                            · {{ $resource->taille_lisible }}

                                        @endif

                                    </div>

                                </div>


                                <div class="resource-actions">

                                    {{-- VOIR --}}

                                    <a href="{{ route('cours.resources.voir', [$cours, $resource]) }}"
                                       class="resource-action"
                                       title="Consulter"
                                       target="_blank">

                                        <i class="fas fa-eye"></i>

                                    </a>


                                    {{-- TÉLÉCHARGER --}}

                                    @if($resource->chemin)

                                        <a href="{{ route('cours.resources.download', [$cours, $resource]) }}"
                                           class="resource-action"
                                           title="Télécharger">

                                            <i class="fas fa-download"></i>

                                        </a>

                                    @endif


                                    {{-- SUPPRIMER --}}

                                    <form method="POST"
                                          action="{{ route('admin.cours.resources.destroy', [$cours, $resource]) }}"
                                          onsubmit="return confirm('Voulez-vous supprimer cette ressource ?');">

                                        @csrf

                                        @method('DELETE')


                                        <button type="submit"
                                                class="resource-action delete"
                                                title="Supprimer">

                                            <i class="fas fa-trash"></i>

                                        </button>

                                    </form>

                                </div>

                            </div>

                        @endforeach

                    </div>

                @else

                    <div class="empty-resources">

                        <i class="fas fa-folder-open"></i>

                        Aucune ressource dans ce cours.

                    </div>

                @endif

            </div>


            {{-- =========================
                 TYPES DISPONIBLES
                 ========================= --}}

            <div class="card-custom side-card">

                <div class="side-title">

                    <div class="side-icon">
                        <i class="fas fa-shapes"></i>
                    </div>

                    <span>Types disponibles</span>

                </div>


                <div class="resource-types">

                    <div class="resource-type">

                        <div class="type-icon document-icon">

                            <i class="fas fa-file-alt"></i>

                        </div>


                        <div>

                            <strong>
                                Document
                            </strong>

                            <small>
                                PDF, Word, Excel, PowerPoint...
                            </small>

                        </div>

                    </div>


                    <div class="resource-type">

                        <div class="type-icon video-icon">

                            <i class="fas fa-video"></i>

                        </div>


                        <div>

                            <strong>
                                Vidéo
                            </strong>

                            <small>
                                MP4, WebM, MOV, AVI...
                            </small>

                        </div>

                    </div>


                    <div class="resource-type">

                        <div class="type-icon link-icon">

                            <i class="fas fa-link"></i>

                        </div>


                        <div>

                            <strong>
                                Lien
                            </strong>

                            <small>
                                Lien vers une ressource externe
                            </small>

                        </div>

                    </div>

                </div>


                {{-- =========================
                     AJOUTER UNE RESSOURCE
                     ========================= --}}

                <div class="add-resource-form">

                    <div class="nv-add-resource-heading">

                        Ajouter une ressource

                    </div>


                    <form method="POST"
                          action="{{ route('admin.cours.resources.store', $cours) }}"
                          enctype="multipart/form-data">

                        @csrf


                        <div class="resource-field">

                            <label>
                                Titre
                            </label>

                            <input type="text"
                                   name="titre"
                                   class="resource-input"
                                   placeholder="Ex : Support du cours"
                                   value="{{ old('titre') }}"
                                   required>

                        </div>


                        <div class="resource-field">

                            <label>
                                Type
                            </label>

                            <select name="type"
                                    id="resourceType"
                                    class="resource-select"
                                    required>

                                <option value="fichier"
                                        {{ old('type', 'fichier') === 'fichier' ? 'selected' : '' }}>

                                    Document

                                </option>


                                <option value="video"
                                        {{ old('type') === 'video' ? 'selected' : '' }}>

                                    Vidéo

                                </option>


                                <option value="lien"
                                        {{ old('type') === 'lien' ? 'selected' : '' }}>

                                    Lien

                                </option>

                            </select>

                        </div>


                        {{-- =========================
                             CHAMP FICHIER
                             ========================= --}}

                        <div class="resource-field file-field d-none"
                             id="fileField">

                            <label id="fileLabel">
                                Fichier
                            </label>


                            <input type="file"
                                   name="fichier"
                                   id="resourceFile"
                                   class="resource-input">


                            <small class="nv-file-help" id="fileHelp"
                                  >

                                PDF, DOC, DOCX, PPT, PPTX, XLS ou XLSX

                            </small>

                        </div>


                        {{-- =========================
                             CHAMP URL
                             ========================= --}}

                        <div class="resource-field url-field d-none"
                             id="urlField">

                            <label>
                                URL
                            </label>


                            <input type="url"
                                   name="url"
                                   id="resourceUrl"
                                   class="resource-input"
                                   placeholder="https://..."
                                   value="{{ old('url') }}">

                        </div>


                        <button type="submit"
                                class="resource-submit">

                            <i class="fas fa-plus me-1"></i>

                            Ajouter la ressource

                        </button>

                    </form>

                </div>

            </div>

        </aside>

    </div>

</div>


{{-- =============================================================
     MODAL AJOUTER CHAPITRE
     ============================================================= --}}

<div class="modal fade"
     id="modalAjouterChapitre"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content nv-chapters-modal-content"
            >

            <div class="modal-header nv-chapters-modal-header"
                >

                <h5 class="modal-title nv-chapters-modal-title"
                   >

                    <i class="fas fa-plus-circle me-2 text-primary"
                      ></i>

                    Ajouter un chapitre

                </h5>


                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Fermer">
                </button>

            </div>


            <form method="POST"
                  action="{{ route('admin.cours.chapitres.store', $cours) }}">

                @csrf


                <div class="modal-body nv-chapters-modal-body"
                    >

                    <div class="form-group-custom">

                        <label class="form-label-custom">
                            Titre du chapitre
                        </label>


                        <input type="text"
                               name="titre"
                               class="form-control-custom"
                               placeholder="Ex : Introduction au module"
                               required>

                    </div>


                    <div class="form-group-custom mb-0"
                        >

                        <label class="form-label-custom">
                            Contenu / description
                        </label>


                        <textarea name="contenu"
                                  class="form-control-custom"
                                  placeholder="Décrivez brièvement le contenu du chapitre..."></textarea>

                    </div>

                </div>


                <div class="modal-footer nv-chapters-modal-footer"
                    >

                    <button type="button"
                            class="btn btn-light"
                            data-bs-dismiss="modal">

                        Annuler

                    </button>


                    <button type="submit"
                            class="btn-primary-custom">

                        <i class="fas fa-plus"></i>

                        Créer le chapitre

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | SÉLECTION DES CHAPITRES
    |--------------------------------------------------------------------------
    */

    const chapitreItems =
        document.querySelectorAll('.chapitre-item');

    const chapitrePanels =
        document.querySelectorAll('.chapter-panel');

    const selectedChapterNumber =
        document.getElementById('selectedChapterNumber');

    const selectedChapterName =
        document.getElementById('selectedChapterName');


    chapitreItems.forEach(function (item) {

        item.addEventListener('click', function () {

            const chapterId =
                this.dataset.chapterId;

            const chapterNumber =
                this.dataset.chapterNumber;

            const chapterTitle =
                this.dataset.chapterTitle;


            /*
            |--------------------------------------------------------------------------
            | Active le chapitre sélectionné
            |--------------------------------------------------------------------------
            */

            chapitreItems.forEach(function (element) {

                element.classList.remove('active');

            });


            this.classList.add('active');


            /*
            |--------------------------------------------------------------------------
            | Affiche le bon formulaire
            |--------------------------------------------------------------------------
            */

            chapitrePanels.forEach(function (panel) {

                panel.classList.remove('active');

            });


            const panelActif =
                document.querySelector(
                    '.chapter-panel[data-chapter-panel="' +
                    chapterId +
                    '"]'
                );


            if (panelActif) {

                panelActif.classList.add('active');

            }


            /*
            |--------------------------------------------------------------------------
            | Met à jour le chapitre sélectionné
            |--------------------------------------------------------------------------
            */

            if (selectedChapterNumber) {

                selectedChapterNumber.textContent =
                    chapterNumber;

            }


            if (selectedChapterName) {

                selectedChapterName.textContent =
                    chapterTitle;

            }

        });

    });


    /*
    |--------------------------------------------------------------------------
    | GESTION DU TYPE DE RESSOURCE
    |--------------------------------------------------------------------------
    */

    const resourceType =
        document.getElementById('resourceType');

    const fileField =
        document.getElementById('fileField');

    const urlField =
        document.getElementById('urlField');

    const resourceFile =
        document.getElementById('resourceFile');

    const resourceUrl =
        document.getElementById('resourceUrl');

    const fileLabel =
        document.getElementById('fileLabel');

    const fileHelp =
        document.getElementById('fileHelp');


    function updateResourceFields() {

        if (!resourceType) {

            return;

        }


        const type =
            resourceType.value;


        /*
        |--------------------------------------------------------------------------
        | On masque d'abord les deux champs
        |--------------------------------------------------------------------------
        */

        if (fileField) {

            fileField.classList.add('d-none');

        }


        if (urlField) {

            urlField.classList.add('d-none');

        }


        if (resourceFile) {

            resourceFile.required = false;

        }


        if (resourceUrl) {

            resourceUrl.required = false;

        }


        /*
        |--------------------------------------------------------------------------
        | DOCUMENT
        |--------------------------------------------------------------------------
        */

        if (type === 'fichier') {

            if (fileField) {

                fileField.classList.remove('d-none');

            }


            if (resourceFile) {

                resourceFile.accept =
                    '.pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx';

                resourceFile.required = true;

            }


            if (fileLabel) {

                fileLabel.textContent =
                    'Document';

            }


            if (fileHelp) {

                fileHelp.textContent =
                    'PDF, DOC, DOCX, PPT, PPTX, XLS ou XLSX';

            }

        }


        /*
        |--------------------------------------------------------------------------
        | VIDÉO
        |--------------------------------------------------------------------------
        */

        else if (type === 'video') {

            if (fileField) {

                fileField.classList.remove('d-none');

            }


            if (resourceFile) {

                resourceFile.accept =
                    'video/mp4,video/webm,video/ogg,video/quicktime,video/x-msvideo,video/x-matroska';

                resourceFile.required = true;

            }


            if (fileLabel) {

                fileLabel.textContent =
                    'Fichier vidéo';

            }


            if (fileHelp) {

                fileHelp.textContent =
                    'MP4, WebM, OGG, MOV, AVI ou MKV';

            }

        }


        /*
        |--------------------------------------------------------------------------
        | LIEN
        |--------------------------------------------------------------------------
        */

        else if (type === 'lien') {

            if (urlField) {

                urlField.classList.remove('d-none');

            }


            if (resourceUrl) {

                resourceUrl.required = true;

            }

        }

    }


    if (resourceType) {

        resourceType.addEventListener(
            'change',
            updateResourceFields
        );


        updateResourceFields();

    }

});
</script>

@endsection