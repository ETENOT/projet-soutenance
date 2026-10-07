@extends('layouts.master')

@section('title', 'Contenus pédagogiques')

@section('content')

<style>
    .contenus-page {
        padding: 28px;
        background: #f6f8fb;
        min-height: calc(100vh - 80px);
    }

    .breadcrumb-custom {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 24px;
        font-size: 13px;
        color: #8a94a3;
    }

    .breadcrumb-custom a {
        color: #0f47ad;
        text-decoration: none;
        font-weight: 600;
    }

    .breadcrumb-custom i {
        font-size: 10px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .page-title {
        margin: 0;
        color: #1f2937;
        font-size: 25px;
        font-weight: 700;
    }

    .page-subtitle {
        margin: 6px 0 0;
        color: #8a94a3;
        font-size: 13px;
    }

    .btn-primary-custom {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 11px 17px;
        border: 0;
        border-radius: 9px;
        background: #0f47ad;
        color: #fff;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-primary-custom:hover {
        background: #0c3b91;
        color: #fff;
    }

    .contenus-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 350px;
        gap: 22px;
        align-items: start;
    }

    .card-custom {
        background: #fff;
        border: 1px solid #e9edf3;
        border-radius: 14px;
        box-shadow: 0 3px 15px rgba(31, 41, 55, 0.04);
    }

    /* =========================
       COLONNE PRINCIPALE
       ========================= */

    .chapitres-card {
        overflow: hidden;
    }

    .card-header-custom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 18px 20px;
        border-bottom: 1px solid #edf0f4;
    }

    .card-header-title {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #1f2937;
        font-size: 15px;
        font-weight: 700;
    }

    .header-icon {
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        background: rgba(15, 71, 173, 0.10);
        color: #0f47ad;
    }

    .chapitres-list {
        padding: 10px;
    }

    .chapitre-item {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 14px;
        margin-bottom: 5px;
        border: 0;
        border-radius: 10px;
        background: transparent;
        text-decoration: none;
        transition: all .2s ease;
        cursor: pointer;
        text-align: left;
    }

    .chapitre-item:hover {
        background: #f5f7fb;
    }

    .chapitre-item.active {
        background: #eef3ff;
    }

    .chapitre-number {
        width: 36px;
        height: 36px;
        min-width: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        background: #f0f2f5;
        color: #697586;
        font-size: 12px;
        font-weight: 700;
    }

    .chapitre-item.active .chapitre-number {
        background: #0f47ad;
        color: #fff;
    }

    .chapitre-info {
        min-width: 0;
        flex: 1;
    }

    .chapitre-name {
        color: #374151;
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .chapitre-item.active .chapitre-name {
        color: #0f47ad;
    }

    .chapitre-meta {
        margin-top: 3px;
        color: #9aa3af;
        font-size: 11px;
    }

    .chapitre-arrow {
        color: #b2bac5;
        font-size: 11px;
    }

    /* =========================
       ÉDITION DU CHAPITRE
       ========================= */

    .editor-card {
        margin-top: 20px;
        padding: 22px;
    }

    .editor-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 22px;
    }

    .editor-title {
        margin: 0;
        color: #1f2937;
        font-size: 17px;
        font-weight: 700;
    }

    .editor-subtitle {
        margin-top: 5px;
        color: #8a94a3;
        font-size: 12px;
    }

    .form-group-custom {
        margin-bottom: 18px;
    }

    .form-label-custom {
        display: block;
        margin-bottom: 7px;
        color: #374151;
        font-size: 12px;
        font-weight: 600;
    }

    .form-control-custom {
        width: 100%;
        padding: 11px 13px;
        border: 1px solid #dfe4ea;
        border-radius: 8px;
        outline: none;
        color: #374151;
        background: #fff;
        font-size: 13px;
        transition: border-color .2s, box-shadow .2s;
    }

    .form-control-custom:focus {
        border-color: #0f47ad;
        box-shadow: 0 0 0 3px rgba(15, 71, 173, .08);
    }

    textarea.form-control-custom {
        min-height: 130px;
        resize: vertical;
    }

    .editor-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 5px;
    }

    .btn-save {
        border: 0;
        border-radius: 8px;
        padding: 10px 17px;
        background: #0f47ad;
        color: #fff;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-save:hover {
        background: #0c3b91;
    }

    .btn-delete {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 14px;
        border: 1px solid #f1caca;
        border-radius: 8px;
        background: #fff;
        color: #d64545;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-delete:hover {
        background: #fff5f5;
    }

    /* =========================
       COLONNE DROITE
       ========================= */

    .resource-side {
        position: sticky;
        top: 20px;
    }

    .side-card {
        padding: 20px;
        margin-bottom: 20px;
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

    .chapter-info {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        border-radius: 10px;
        background: #f8f9fb;
    }

    .chapter-number-side {
        width: 38px;
        height: 38px;
        min-width: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: #0f47ad;
        color: #fff;
        font-size: 14px;
        font-weight: 700;
    }

    .chapter-details {
        min-width: 0;
    }

    .chapter-name-side {
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

    /* =========================
       RESSOURCES
       ========================= */

    .resources-list {
        display: flex;
        flex-direction: column;
        gap: 9px;

        /*
        |--------------------------------------------------------------------------
        | Limite la hauteur de la liste
        |--------------------------------------------------------------------------
        */

        max-height: 390px;
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 5px;
    }

    /*
    |--------------------------------------------------------------------------
    | Barre de défilement des ressources
    |--------------------------------------------------------------------------
    */

    .resources-list::-webkit-scrollbar {
        width: 5px;
    }

    .resources-list::-webkit-scrollbar-track {
        background: #f4f6f8;
        border-radius: 10px;
    }

    .resources-list::-webkit-scrollbar-thumb {
        background: #cbd2dc;
        border-radius: 10px;
    }

    .resources-list::-webkit-scrollbar-thumb:hover {
        background: #aeb7c4;
    }

    .resource-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 11px;
        border: 1px solid #edf0f4;
        border-radius: 10px;
        background: #fff;
        flex-shrink: 0;
    }

    .resource-icon {
        width: 36px;
        height: 36px;
        min-width: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        font-size: 15px;
    }

    .resource-document {
        background: #eef3ff;
        color: #0f47ad;
    }

    .resource-video {
        background: #f1ecff;
        color: #6842c2;
    }

    .resource-link {
        background: #eaf8f1;
        color: #21895a;
    }

    .resource-info {
        min-width: 0;
        flex: 1;
    }

    .resource-name {
        color: #374151;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .resource-type-label {
        margin-top: 3px;
        color: #9aa3af;
        font-size: 10px;
    }

    .resource-actions {
        display: flex;
        align-items: center;
        gap: 5px;
        flex-shrink: 0;
    }

    .resource-action {
        width: 29px;
        height: 29px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 7px;
        background: #f5f6f8;
        color: #697586;
        text-decoration: none;
        cursor: pointer;
        transition: all .2s;
    }

    .resource-action:hover {
        background: #eef3ff;
        color: #0f47ad;
    }

    .resource-action.delete:hover {
        background: #fff0f0;
        color: #d64545;
    }

    .empty-resources {
        padding: 20px 10px;
        text-align: center;
        border: 1px dashed #dfe4ea;
        border-radius: 10px;
        color: #9aa3af;
        font-size: 12px;
    }

    .empty-resources i {
        display: block;
        margin-bottom: 8px;
        font-size: 20px;
        color: #c4cad2;
    }

    /* =========================
       TYPES DISPONIBLES
       ========================= */

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
        color: #9aa3af;
        font-size: 10px;
    }

    /* =========================
       AJOUT RESSOURCE
       ========================= */

    .add-resource-form {
        margin-top: 18px;
        padding-top: 18px;
        border-top: 1px solid #edf0f4;
    }

    .resource-input {
        width: 100%;
        padding: 10px 11px;
        border: 1px solid #dfe4ea;
        border-radius: 8px;
        outline: none;
        color: #374151;
        font-size: 12px;
    }

    .resource-input:focus {
        border-color: #0f47ad;
        box-shadow: 0 0 0 3px rgba(15, 71, 173, .08);
    }

    .resource-select {
        width: 100%;
        padding: 10px 11px;
        border: 1px solid #dfe4ea;
        border-radius: 8px;
        outline: none;
        background: #fff;
        color: #374151;
        font-size: 12px;
    }

    .resource-field {
        margin-bottom: 12px;
    }

    .resource-field label {
        display: block;
        margin-bottom: 6px;
        color: #374151;
        font-size: 11px;
        font-weight: 600;
    }

    .resource-submit {
        width: 100%;
        padding: 10px 14px;
        border: 0;
        border-radius: 8px;
        background: #0f47ad;
        color: #fff;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
    }

    .resource-submit:hover {
        background: #0c3b91;
    }

    .file-field,
    .url-field {
        display: none;
    }

    .alert-custom {
        padding: 12px 14px;
        margin-bottom: 18px;
        border-radius: 9px;
        font-size: 12px;
    }

    .alert-success-custom {
        background: #eaf8f1;
        color: #21895a;
    }

    .alert-error-custom {
        background: #fff0f0;
        color: #c03939;
    }

    .alert-error-custom ul {
        margin: 5px 0 0;
        padding-left: 18px;
    }

    .chapter-panel {
        display: none;
    }

    .chapter-panel.active {
        display: block;
    }

    @media (max-width: 1100px) {

        .contenus-layout {
            grid-template-columns: 1fr;
        }

        .resource-side {
            position: static;
        }

        .resources-list {
            max-height: 450px;
        }
    }

    @media (max-width: 700px) {

        .contenus-page {
            padding: 16px;
        }

        .page-header {
            align-items: flex-start;
            gap: 15px;
            flex-direction: column;
        }

        .resource-actions {
            gap: 3px;
        }

        .resource-action {
            width: 27px;
            height: 27px;
        }
    }
</style>


<div class="contenus-page">

    {{-- =========================
         BREADCRUMB
         ========================= --}}

    <div class="breadcrumb-custom">

        <a href="{{ route('admin.cours.index') }}">
            Cours
        </a>

        <i class="fas fa-chevron-right"></i>

        <a href="{{ route('admin.cours.show', $cours) }}">
            {{ $cours->titre }}
        </a>

        <i class="fas fa-chevron-right"></i>

        <span>Contenus pédagogiques</span>

    </div>


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


                    <span style="font-size:11px;color:#9aa3af;">

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

                        <div style="padding:30px;text-align:center;color:#9aa3af;font-size:13px;">

                            <i class="fas fa-layer-group"
                               style="display:block;font-size:25px;margin-bottom:10px;color:#c7ccd4;"></i>

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


                            <div style="margin-top:20px;padding-top:18px;border-top:1px solid #edf0f4;">

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

                <div class="card-custom"
                     style="margin-top:20px;padding:45px;text-align:center;">

                    <i class="fas fa-hand-pointer"
                       style="font-size:28px;color:#c5cbd4;margin-bottom:14px;"></i>

                    <h3 style="margin:0;color:#374151;font-size:15px;">
                        Aucun chapitre
                    </h3>

                    <p style="margin:7px 0 0;color:#9aa3af;font-size:12px;">
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

                    <div style="padding:15px;border-radius:10px;background:#f8f9fb;color:#9aa3af;font-size:12px;text-align:center;">

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

                    <div style="font-size:13px;font-weight:700;color:#374151;margin-bottom:14px;">

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

                        <div class="resource-field file-field"
                             id="fileField">

                            <label id="fileLabel">
                                Fichier
                            </label>


                            <input type="file"
                                   name="fichier"
                                   id="resourceFile"
                                   class="resource-input">


                            <small id="fileHelp"
                                   style="display:block;margin-top:5px;color:#9aa3af;font-size:10px;">

                                PDF, DOC, DOCX, PPT, PPTX, XLS ou XLSX

                            </small>

                        </div>


                        {{-- =========================
                             CHAMP URL
                             ========================= --}}

                        <div class="resource-field url-field"
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

        <div class="modal-content"
             style="border:0;border-radius:14px;overflow:hidden;">

            <div class="modal-header"
                 style="border-bottom:1px solid #edf0f4;padding:18px 20px;">

                <h5 class="modal-title"
                    style="font-size:16px;font-weight:700;color:#1f2937;">

                    <i class="fas fa-plus-circle me-2"
                       style="color:#0f47ad;"></i>

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


                <div class="modal-body"
                     style="padding:20px;">

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


                    <div class="form-group-custom"
                         style="margin-bottom:0;">

                        <label class="form-label-custom">
                            Contenu / description
                        </label>


                        <textarea name="contenu"
                                  class="form-control-custom"
                                  placeholder="Décrivez brièvement le contenu du chapitre..."></textarea>

                    </div>

                </div>


                <div class="modal-footer"
                     style="border-top:1px solid #edf0f4;padding:15px 20px;">

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

            fileField.style.display = 'none';

        }


        if (urlField) {

            urlField.style.display = 'none';

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

                fileField.style.display = 'block';

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

                fileField.style.display = 'block';

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

                urlField.style.display = 'block';

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