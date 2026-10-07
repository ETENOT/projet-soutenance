@extends('layouts.master')

@section('title', 'Contenus pédagogiques')

@section('content')

<div class="container-fluid">

    {{-- =========================================================
         BREADCRUMB
    ========================================================== --}}
    <div class="mb-4">

        <div class="d-flex align-items-center gap-2 small text-muted">

            <a
                href="{{ route('admin.cours.index') }}"
                class="text-muted text-decoration-none"
            >
                Cours
            </a>

            <i class="ri-arrow-right-s-line"></i>

            <a
                href="{{ route('admin.cours.show', $cours) }}"
                class="text-muted text-decoration-none"
            >
                {{ $cours->titre }}
            </a>

            <i class="ri-arrow-right-s-line"></i>

            <span>
                Contenus pédagogiques
            </span>

        </div>

    </div>


    {{-- =========================================================
         EN-TÊTE
    ========================================================== --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h4 class="fw-bold mb-1">
                Gestion des contenus pédagogiques
            </h4>

            <p class="text-muted mb-0">
                Organisez les chapitres et les ressources de votre formation.
            </p>

        </div>

        <div>

            <a
                href="{{ route('admin.cours.show', $cours) }}"
                class="btn btn-outline-secondary"
            >
                <i class="ri-arrow-left-line me-1"></i>
                Retour au cours
            </a>

        </div>

    </div>


    {{-- =========================================================
         MESSAGE DE SUCCÈS
    ========================================================== --}}
    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show"
             role="alert">

            <i class="ri-checkbox-circle-line me-2"></i>

            {{ session('success') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    @endif


    {{-- =========================================================
         INTERFACE PRINCIPALE
    ========================================================== --}}
    <div class="row g-4">

        {{-- =====================================================
             COLONNE GAUCHE : CHAPITRES
        ====================================================== --}}
        <div class="col-lg-4">

            <div class="card border-0 shadow-sm">

                {{-- Header --}}
                <div class="card-header bg-white border-0 p-4">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <h5 class="fw-bold mb-1">
                                Chapitres
                            </h5>

                            <span class="text-muted small">
                                {{ $chapitres->count() }} chapitre(s)
                            </span>

                        </div>

                        <a
                            href="{{ route('admin.cours.chapitres.create', $cours) }}"
                            class="btn btn-primary btn-sm"
                        >
                            <i class="ri-add-line me-1"></i>
                            Ajouter
                        </a>

                    </div>

                </div>


                {{-- Liste --}}
                <div class="card-body p-2">

                    @forelse($chapitres as $unChapitre)

                        @php

                            $estSelectionne = false;

                            if ($chapitreSelectionne) {

                                $estSelectionne =
                                    $chapitreSelectionne->id === $unChapitre->id;

                            }

                        @endphp


                        <a
                            href="{{ route(
                                'admin.cours.contenus',
                                [
                                    'cours' => $cours,
                                    'chapitre' => $unChapitre
                                ]
                            ) }}"
                            class="chapitre-link {{ $estSelectionne ? 'active' : '' }}"
                        >

                            <div class="chapitre-numero">
                                {{ $unChapitre->ordre }}
                            </div>

                            <div class="chapitre-info">

                                <div class="chapitre-titre">
                                    {{ $unChapitre->titre }}
                                </div>

                                <div class="chapitre-meta">

                                    {{ $unChapitre->resources->count() }}

                                    ressource(s)

                                </div>

                            </div>

                            <i class="ri-arrow-right-s-line chapitre-arrow"></i>

                        </a>

                    @empty

                        <div class="text-center py-5 px-3">

                            <div class="empty-icon mb-3">

                                <i class="ri-book-open-line"></i>

                            </div>

                            <h6 class="fw-bold">
                                Aucun chapitre
                            </h6>

                            <p class="text-muted small mb-3">
                                Commencez par créer le premier chapitre de cette formation.
                            </p>

                            <a
                                href="{{ route('admin.cours.chapitres.create', $cours) }}"
                                class="btn btn-primary btn-sm"
                            >
                                <i class="ri-add-line me-1"></i>
                                Ajouter un chapitre
                            </a>

                        </div>

                    @endforelse

                </div>

            </div>

        </div>


        {{-- =====================================================
             COLONNE DROITE : CHAPITRE SÉLECTIONNÉ
        ====================================================== --}}
        <div class="col-lg-8">

            @if($chapitreSelectionne)

                <div class="card border-0 shadow-sm">

                    {{-- -------------------------------------------------
                         EN-TÊTE DU CHAPITRE
                    -------------------------------------------------- --}}
                    <div class="card-header bg-white border-0 p-4">

                        <div class="d-flex justify-content-between align-items-start gap-3">

                            <div>

                                <span class="badge bg-primary-subtle text-primary mb-2">

                                    Chapitre
                                    {{ $chapitreSelectionne->ordre }}

                                </span>

                                <h5 class="fw-bold mb-1">

                                    {{ $chapitreSelectionne->titre }}

                                </h5>

                                <p class="text-muted small mb-0">

                                    Gestion du contenu de ce chapitre.

                                </p>

                            </div>


                            {{-- Actions chapitre --}}
                            <div class="d-flex gap-2">

                                <a
                                    href="{{ route(
                                        'admin.cours.chapitres.edit',
                                        [
                                            $cours,
                                            $chapitreSelectionne
                                        ]
                                    ) }}"
                                    class="btn btn-outline-secondary btn-sm"
                                    title="Modifier le chapitre"
                                >
                                    <i class="ri-edit-line"></i>
                                </a>


                                <form
                                    action="{{ route(
                                        'admin.cours.chapitres.destroy',
                                        [
                                            $cours,
                                            $chapitreSelectionne
                                        ]
                                    ) }}"
                                    method="POST"
                                    onsubmit="return confirm('Voulez-vous vraiment supprimer ce chapitre ?');"
                                >

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="btn btn-outline-danger btn-sm"
                                        title="Supprimer le chapitre"
                                    >
                                        <i class="ri-delete-bin-line"></i>
                                    </button>

                                </form>

                            </div>

                        </div>

                    </div>


                    {{-- -------------------------------------------------
                         CONTENU DU CHAPITRE
                    -------------------------------------------------- --}}
                    <div class="card-body p-4">

                        {{-- Description --}}
                        <div class="content-section mb-4">

                            <div class="section-title">

                                <i class="ri-file-text-line"></i>

                                <span>
                                    Description
                                </span>

                            </div>


                            @if($chapitreSelectionne->description)

                                <div class="description-box">

                                    {{ $chapitreSelectionne->description }}

                                </div>

                            @else

                                <div class="description-box text-muted">

                                    Aucune description renseignée pour ce chapitre.

                                </div>

                            @endif

                        </div>


                        {{-- Ressources --}}
                        <div class="content-section">

                            <div class="d-flex justify-content-between align-items-center mb-3">

                                <div class="section-title mb-0">

                                    <i class="ri-folder-3-line"></i>

                                    <span>
                                        Contenu du chapitre
                                    </span>

                                </div>

                                <a
                                    href="{{ route(
                                        'admin.cours.chapitres.resources.create',
                                        [
                                            $cours,
                                            $chapitreSelectionne
                                        ]
                                    ) }}"
                                    class="btn btn-primary btn-sm"
                                >
                                    <i class="ri-add-line me-1"></i>
                                    Ajouter une ressource
                                </a>

                            </div>


                            @forelse($chapitreSelectionne->resources as $resource)

                                <div class="resource-row">

                                    {{-- Icône --}}
                                    <div class="resource-icon
                                        @if($resource->type === 'video')
                                            resource-video
                                        @elseif($resource->type === 'lien')
                                            resource-link
                                        @else
                                            resource-document
                                        @endif
                                    ">

                                        @if($resource->type === 'video')

                                            <i class="ri-video-line"></i>

                                        @elseif($resource->type === 'lien')

                                            <i class="ri-links-line"></i>

                                        @else

                                            <i class="ri-file-text-line"></i>

                                        @endif

                                    </div>


                                    {{-- Informations --}}
                                    <div class="resource-info">

                                        <div class="resource-title">

                                            {{ $resource->titre }}

                                        </div>

                                        <div class="resource-type">

                                            @if($resource->type === 'video')

                                                Vidéo

                                            @elseif($resource->type === 'lien')

                                                Lien

                                            @else

                                                Document

                                                @if($resource->extension)

                                                    · {{ strtoupper($resource->extension) }}

                                                @endif

                                            @endif

                                        </div>

                                    </div>


                                    {{-- Actions --}}
                                    <div class="resource-actions">

                                        @if($resource->type === 'fichier')

                                            <a
                                                href="{{ route(
                                                    'cours.resources.voir',
                                                    [
                                                        $cours,
                                                        $resource
                                                    ]
                                                ) }}"
                                                target="_blank"
                                                class="btn btn-sm btn-light"
                                                title="Voir"
                                            >
                                                <i class="ri-eye-line"></i>
                                            </a>

                                        @elseif($resource->url)

                                            <a
                                                href="{{ $resource->url }}"
                                                target="_blank"
                                                class="btn btn-sm btn-light"
                                                title="Ouvrir"
                                            >
                                                <i class="ri-external-link-line"></i>
                                            </a>

                                        @endif


                                        <form
                                            action="{{ route(
                                                'admin.cours.chapitres.resources.destroy',
                                                [
                                                    $cours,
                                                    $chapitreSelectionne,
                                                    $resource
                                                ]
                                            ) }}"
                                            method="POST"
                                            onsubmit="return confirm('Voulez-vous supprimer cette ressource ?');"
                                        >

                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-light text-danger"
                                                title="Supprimer"
                                            >
                                                <i class="ri-delete-bin-line"></i>
                                            </button>

                                        </form>

                                    </div>

                                </div>

                            @empty

                                <div class="empty-resources">

                                    <i class="ri-folder-open-line"></i>

                                    <p class="mb-2">
                                        Aucune ressource dans ce chapitre.
                                    </p>

                                    <a
                                        href="{{ route(
                                            'admin.cours.chapitres.resources.create',
                                            [
                                                $cours,
                                                $chapitreSelectionne
                                            ]
                                        ) }}"
                                        class="btn btn-outline-primary btn-sm"
                                    >
                                        <i class="ri-add-line me-1"></i>
                                        Ajouter la première ressource
                                    </a>

                                </div>

                            @endforelse

                        </div>

                    </div>

                </div>

            @else

                {{-- Aucun chapitre --}}
                <div class="card border-0 shadow-sm">

                    <div class="card-body text-center py-5">

                        <div class="empty-large-icon mb-3">

                            <i class="ri-book-open-line"></i>

                        </div>

                        <h5 class="fw-bold">
                            Aucun chapitre sélectionné
                        </h5>

                        <p class="text-muted mb-3">
                            Créez un chapitre pour commencer à construire le contenu pédagogique.
                        </p>

                        <a
                            href="{{ route('admin.cours.chapitres.create', $cours) }}"
                            class="btn btn-primary"
                        >
                            <i class="ri-add-line me-1"></i>
                            Ajouter un chapitre
                        </a>

                    </div>

                </div>

            @endif

        </div>

    </div>

</div>


{{-- =============================================================
     STYLE
============================================================= --}}
<style>

    /*
     * Liste des chapitres
     */
    .chapitre-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 14px;
        margin: 4px 0;
        border-radius: 10px;
        text-decoration: none;
        color: inherit;
        transition: all 0.15s ease;
    }

    .chapitre-link:hover {
        background: #f5f7fa;
        color: inherit;
    }

    .chapitre-link.active {
        background: rgba(15, 71, 173, 0.08);
        color: #0f47ad;
    }

    .chapitre-numero {
        width: 34px;
        height: 34px;
        min-width: 34px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f3f5;
        font-size: 13px;
        font-weight: 600;
    }

    .chapitre-link.active .chapitre-numero {
        background: #0f47ad;
        color: #fff;
    }

    .chapitre-info {
        flex: 1;
        min-width: 0;
    }

    .chapitre-titre {
        font-size: 14px;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .chapitre-meta {
        margin-top: 3px;
        font-size: 11px;
        color: #8a94a6;
    }

    .chapitre-arrow {
        color: #adb5bd;
    }

    .chapitre-link.active .chapitre-arrow {
        color: #0f47ad;
    }


    /*
     * Sections
     */
    .content-section {
        padding-bottom: 24px;
    }

    .content-section + .content-section {
        border-top: 1px solid #edf0f3;
        padding-top: 24px;
    }

    .section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        margin-bottom: 12px;
    }

    .section-title i {
        color: #0f47ad;
        font-size: 18px;
    }

    .description-box {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 14px 16px;
        line-height: 1.7;
        font-size: 14px;
    }


    /*
     * Ressources
     */
    .resource-row {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 13px 0;
        border-bottom: 1px solid #edf0f3;
    }

    .resource-row:last-child {
        border-bottom: 0;
    }

    .resource-icon {
        width: 42px;
        height: 42px;
        min-width: 42px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 19px;
    }

    .resource-document {
        background: rgba(15, 71, 173, 0.08);
        color: #0f47ad;
    }

    .resource-video {
        background: rgba(220, 53, 69, 0.08);
        color: #dc3545;
    }

    .resource-link {
        background: rgba(25, 135, 84, 0.08);
        color: #198754;
    }

    .resource-info {
        flex: 1;
        min-width: 0;
    }

    .resource-title {
        font-size: 14px;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .resource-type {
        font-size: 11px;
        color: #8a94a6;
        margin-top: 3px;
    }

    .resource-actions {
        display: flex;
        align-items: center;
        gap: 5px;
    }


    /*
     * États vides
     */
    .empty-icon,
    .empty-large-icon {
        width: 55px;
        height: 55px;
        margin-left: auto;
        margin-right: auto;
        border-radius: 50%;
        background: #f1f3f5;
        color: #8a94a6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 25px;
    }

    .empty-large-icon {
        width: 70px;
        height: 70px;
        font-size: 32px;
    }

    .empty-resources {
        text-align: center;
        padding: 35px 20px;
        border: 1px dashed #dfe3e8;
        border-radius: 10px;
        color: #8a94a6;
    }

    .empty-resources > i {
        display: block;
        font-size: 32px;
        margin-bottom: 8px;
    }


    /*
     * Responsive
     */
    @media (max-width: 767.98px) {

        .resource-row {
            align-items: flex-start;
        }

        .resource-actions {
            flex-direction: column;
        }

        .chapitre-titre {
            white-space: normal;
        }

    }

</style>

@endsection