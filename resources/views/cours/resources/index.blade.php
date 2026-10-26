@extends('layouts.master')

@section('title', 'Ressources de la formation')

@section('content')



<div class="resources-page">

    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('admin.cours.contenus', [$cours, $chapitre]) }}">{{ $cours->titre }}</a>
        @endslot
        @slot('title')
            Ressources de la formation
        @endslot
    @endcomponent

    {{-- =========================
         EN-TÊTE
    ========================== --}}

    <div class="page-header">

        <div class="page-header-left">
            <h1 class="page-title">
                Ressources de la formation
            </h1>

            <p class="page-subtitle">
                Gérez les documents, vidéos et liens pédagogiques de cette formation.
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
                            class="w-100 nv-video-preview"
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

                            <div class="mb-3 fs-1">
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
                    Cette formation ne contient encore aucune ressource pédagogique.
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