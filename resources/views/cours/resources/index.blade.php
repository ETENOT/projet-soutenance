@extends('layouts.master')

@section('title', 'Ressources du chapitre')

@section('content')

<style>
    .resources-page {
        width: 100%;
        max-width: 1400px;
        margin: 0 auto;
        padding: 10px 24px 40px;
        box-sizing: border-box;
    }

    /* =========================
       EN-TÊTE
    ========================= */

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        margin-bottom: 28px;
    }

    .page-header-left {
        min-width: 0;
    }

    .breadcrumb-custom {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 12px;
        font-size: 14px;
        color: #7b8794;
    }

    .breadcrumb-custom a {
        color: #0f47ad;
        text-decoration: none;
        font-weight: 500;
    }

    .breadcrumb-custom a:hover {
        text-decoration: underline;
    }

    .breadcrumb-separator {
        color: #b8c1cc;
    }

    .page-title {
        margin: 0;
        font-size: 28px;
        font-weight: 700;
        color: #172033;
    }

    .page-subtitle {
        margin: 7px 0 0;
        color: #718096;
        font-size: 15px;
    }

    .btn-add-resource {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 18px;
        background: #0f47ad;
        color: #fff;
        text-decoration: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        white-space: nowrap;
        transition: 0.2s ease;
    }

    .btn-add-resource:hover {
        background: #0b398d;
        color: #fff;
        transform: translateY(-1px);
    }

    /* =========================
       CARTE CHAPITRE
    ========================= */

    .chapter-card {
        background: #fff;
        border: 1px solid #e5eaf0;
        border-radius: 12px;
        padding: 20px 22px;
        margin-bottom: 22px;
        box-shadow: 0 2px 8px rgba(15, 71, 173, 0.04);
    }

    .chapter-card-label {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #0f47ad;
        margin-bottom: 8px;
    }

    .chapter-card-title {
        margin: 0;
        font-size: 21px;
        font-weight: 700;
        color: #172033;
    }

    .chapter-card-info {
        margin-top: 7px;
        color: #718096;
        font-size: 14px;
    }

    /* =========================
       CONTENU PRINCIPAL
    ========================= */

    .resources-card {
        background: #fff;
        border: 1px solid #e5eaf0;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(15, 71, 173, 0.04);
    }

    .resources-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        padding: 20px 22px;
        border-bottom: 1px solid #e9edf2;
    }

    .resources-card-title {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: #172033;
    }

    .resources-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 30px;
        height: 30px;
        padding: 0 9px;
        background: #eef4ff;
        color: #0f47ad;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
    }

    /* =========================
       LISTE DES RESSOURCES
    ========================= */

    .resource-list {
        display: flex;
        flex-direction: column;
    }

    .resource-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 18px 22px;
        border-bottom: 1px solid #edf0f4;
        transition: background 0.2s ease;
    }

    .resource-item:last-child {
        border-bottom: none;
    }

    .resource-item:hover {
        background: #fafcff;
    }

    .resource-main {
        display: flex;
        align-items: center;
        gap: 15px;
        min-width: 0;
        flex: 1;
    }

    .resource-icon {
        width: 46px;
        height: 46px;
        flex: 0 0 46px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        background: #eef4ff;
        color: #0f47ad;
    }

    .resource-icon.video {
        background: #fff0f0;
        color: #d83a3a;
    }

    .resource-icon.link {
        background: #eefaf3;
        color: #16834b;
    }

    .resource-info {
        min-width: 0;
    }

    .resource-title {
        margin: 0 0 5px;
        color: #202938;
        font-size: 15px;
        font-weight: 600;
        word-break: break-word;
    }

    .resource-meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        color: #8491a3;
        font-size: 13px;
    }

    .resource-type {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border-radius: 5px;
        background: #f1f4f8;
        color: #667386;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .resource-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    .btn-resource {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 36px;
        padding: 8px 12px;
        border-radius: 7px;
        border: 1px solid #dfe5ec;
        background: #fff;
        color: #445066;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s ease;
    }

    .btn-resource:hover {
        background: #f7f9fc;
        color: #0f47ad;
        border-color: #cdd8e6;
    }

    .btn-delete {
        color: #c62828;
        border-color: #f0d2d2;
    }

    .btn-delete:hover {
        background: #fff5f5;
        color: #b71c1c;
        border-color: #e9baba;
    }

    .delete-form {
        margin: 0;
    }

    /* =========================
       ÉTAT VIDE
    ========================= */

    .empty-state {
        text-align: center;
        padding: 55px 25px;
    }

    .empty-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 17px;
        border-radius: 50%;
        background: #eef4ff;
        color: #0f47ad;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 27px;
    }

    .empty-title {
        margin: 0 0 8px;
        font-size: 18px;
        font-weight: 700;
        color: #263247;
    }

    .empty-text {
        margin: 0 auto 20px;
        max-width: 470px;
        color: #7b8794;
        font-size: 14px;
        line-height: 1.6;
    }

    /* =========================
       MESSAGE SUCCÈS
    ========================= */

    .success-alert {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 13px 16px;
        margin-bottom: 20px;
        border-radius: 8px;
        background: #ecf9f1;
        border: 1px solid #c9ead7;
        color: #187341;
        font-size: 14px;
    }

    /* =========================
       RESPONSIVE
    ========================= */

    @media (max-width: 900px) {

        .resources-page {
            padding: 10px 18px 35px;
        }

        .page-header {
            flex-direction: column;
            align-items: stretch;
        }

        .btn-add-resource {
            width: fit-content;
        }

        .resource-item {
            align-items: flex-start;
            flex-direction: column;
        }

        .resource-actions {
            width: 100%;
            padding-left: 61px;
        }
    }

    @media (max-width: 600px) {

        .resources-page {
            padding: 10px 12px 30px;
        }

        .page-title {
            font-size: 23px;
        }

        .chapter-card {
            padding: 17px;
        }

        .resources-card-header {
            padding: 17px;
        }

        .resource-item {
            padding: 17px;
        }

        .resource-actions {
            padding-left: 0;
            flex-wrap: wrap;
        }

        .btn-resource {
            flex: 1;
        }
    }
</style>

<div class="resources-page">

    {{-- =========================
         EN-TÊTE
    ========================== --}}

    <div class="page-header">

        <div class="page-header-left">

            <div class="breadcrumb-custom">

                <a href="{{ route('admin.cours.index') }}">
                    Cours
                </a>

                <span class="breadcrumb-separator">›</span>

                <a href="{{ route('admin.cours.show', $cours) }}">
                    {{ $cours->titre }}
                </a>

                <span class="breadcrumb-separator">›</span>

                <a href="{{ route('admin.cours.contenus', [$cours, $chapitre]) }}">
                    Contenu pédagogique
                </a>

                <span class="breadcrumb-separator">›</span>

                <span>Ressources</span>

            </div>

            <h1 class="page-title">
                Ressources du chapitre
            </h1>

            <p class="page-subtitle">
                Gérez les documents, vidéos et liens associés à ce chapitre.
            </p>

        </div>

        <a
            href="{{ route('admin.cours.chapitres.resources.create', [$cours, $chapitre]) }}"
            class="btn-add-resource"
        >
            <span>＋</span>
            Ajouter une ressource
        </a>

    </div>


    {{-- =========================
         MESSAGE DE SUCCÈS
    ========================== --}}

    @if(session('success'))

        <div class="success-alert">
            <span>✓</span>
            <span>{{ session('success') }}</span>
        </div>

    @endif


    {{-- =========================
         INFORMATIONS DU CHAPITRE
    ========================== --}}

    <div class="chapter-card">

        <div class="chapter-card-label">
            <span>▣</span>
            Chapitre
        </div>

        <h2 class="chapter-card-title">
            {{ $chapitre->ordre }}. {{ $chapitre->titre }}
        </h2>

        <div class="chapter-card-info">
            Cours : {{ $cours->titre }}
        </div>

    </div>


    {{-- =========================
         LISTE DES RESSOURCES
    ========================== --}}

    <div class="resources-card">

        <div class="resources-card-header">

            <h2 class="resources-card-title">
                Ressources pédagogiques
            </h2>

            <span class="resources-count">
                {{ $resources->count() }}
            </span>

        </div>


        @if($resources->count() > 0)

            <div class="resource-list">

                @foreach($resources as $resource)

                    <div class="resource-item">

                        <div class="resource-main">

                            {{-- Icône selon le type --}}
                            <div class="resource-icon
                                @if($resource->type === 'video')
                                    video
                                @elseif($resource->type === 'lien')
                                    link
                                @endif
                            ">

                                @if($resource->type === 'fichier')
                                    📄
                                @elseif($resource->type === 'video')
                                    ▶
                                @elseif($resource->type === 'lien')
                                    🔗
                                @endif

                            </div>


                            <div class="resource-info">

                                <p class="resource-title">
                                    {{ $resource->titre }}
                                </p>

                                <div class="resource-meta">

                                    <span class="resource-type">

                                        @if($resource->type === 'fichier')
                                            Document
                                        @elseif($resource->type === 'video')
                                            Vidéo
                                        @elseif($resource->type === 'lien')
                                            Lien
                                        @endif

                                    </span>


                                    @if($resource->type === 'fichier')

                                        @if($resource->extension)
                                            <span>
                                                {{ strtoupper($resource->extension) }}
                                            </span>
                                        @endif

                                        @if($resource->taille_lisible)
                                            <span>•</span>
                                            <span>
                                                {{ $resource->taille_lisible }}
                                            </span>
                                        @endif

                                    @elseif($resource->type === 'video')

                                        @if($resource->chemin)
                                            <span>
                                                Vidéo uploadée
                                            </span>
                                        @elseif($resource->url)
                                            <span>
                                                Vidéo externe
                                            </span>
                                        @endif

                                    @elseif($resource->type === 'lien')

                                        <span>
                                            Lien externe
                                        </span>

                                    @endif

                                </div>

                            </div>

                        </div>


                        {{-- ACTIONS --}}
                        <div class="resource-actions">

                            @if($resource->type === 'fichier')

                                <a
                                    href="{{ route('cours.resources.voir', [$cours, $resource]) }}"
                                    class="btn-resource"
                                    target="_blank"
                                >
                                    👁 Consulter
                                </a>

                                <a
                                    href="{{ route('cours.resources.download', [$cours, $resource]) }}"
                                    class="btn-resource"
                                >
                                    ↓ Télécharger
                                </a>

                            @elseif($resource->type === 'video')

                                <button
                                    type="button"
                                    class="btn-resource"
                                    data-bs-toggle="modal"
                                    data-bs-target="#videoModal{{ $resource->id }}"
                                >
                                    👁 Consulter
                                </button>

                            @elseif($resource->type === 'lien')

                                <a
                                    href="{{ $resource->url }}"
                                    class="btn-resource"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    ↗ Ouvrir
                                </a>

                            @endif


                            <form
                                action="{{ route('admin.cours.chapitres.resources.destroy', [$cours, $chapitre, $resource]) }}"
                                method="POST"
                                class="delete-form"
                                onsubmit="return confirm('Voulez-vous vraiment supprimer cette ressource ?');"
                            >

                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="btn-resource btn-delete"
                                >
                                    🗑 Supprimer
                                </button>

                            </form>

                        </div>

                    </div>

                    {{-- =========================
     MODALE APERÇU VIDÉO
========================= --}}

@if($resource->type === 'video')

    <div
        class="modal fade"
        id="videoModal{{ $resource->id }}"
        tabindex="-1"
        aria-hidden="true"
    >

        <div class="modal-dialog modal-xl modal-dialog-centered">

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title fw-bold">
                        {{ $resource->titre }}
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Fermer"
                    ></button>

                </div>

                <div class="modal-body p-0 bg-dark">

                    @if($resource->chemin)

                        {{-- Vidéo uploadée --}}
                        <video
                            controls
                            playsinline
                            preload="metadata"
                            class="w-100"
                            style="max-height: 70vh; display: block; background: #000;"
                        >

                            <source
                                src="{{ asset('storage/' . $resource->chemin) }}"
                                type="video/{{ strtolower($resource->extension ?? 'mp4') }}"
                            >

                            Votre navigateur ne prend pas en charge la lecture vidéo.

                        </video>

                    @elseif($resource->url)

                        {{-- Vidéo externe --}}
                        <div class="ratio ratio-16x9">

                            <iframe
                                src="{{ $resource->url }}"
                                title="{{ $resource->titre }}"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                            ></iframe>

                        </div>

                    @else

                        <div class="p-5 text-center text-white">

                            <div class="mb-3" style="font-size: 40px;">
                                ⚠
                            </div>

                            <p class="mb-0">
                                Cette vidéo n'est pas disponible.
                            </p>

                        </div>

                    @endif

                </div>

            </div>

        </div>

    </div>

@endif

                @endforeach

            </div>

        @else

            {{-- =========================
                 AUCUNE RESSOURCE
            ========================== --}}

            <div class="empty-state">

                <div class="empty-icon">
                    📚
                </div>

                <h3 class="empty-title">
                    Aucune ressource pour le moment
                </h3>

                <p class="empty-text">
                    Ce chapitre ne contient encore aucune ressource pédagogique.
                    Vous pouvez ajouter un document, une vidéo ou un lien externe.
                </p>

                <a
                    href="{{ route('admin.cours.chapitres.resources.create', [$cours, $chapitre]) }}"
                    class="btn-add-resource"
                >
                    <span>＋</span>
                    Ajouter une ressource
                </a>

            </div>

        @endif

    </div>

</div>

@endsection