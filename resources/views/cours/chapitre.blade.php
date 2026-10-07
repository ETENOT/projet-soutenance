@extends(Auth::check() ? 'layouts.master' : 'layouts.master-without-nav')

@section('title', $chapitre->titre)

@section('content')

<style>

    .list-group-item.active {
        background-color: #eef6ff;
        border-color: #d7e8ff;
        color: #0f47ad;
        font-weight: 600;
    }

    .resource-item {
        cursor: pointer;
    }

    .resource-item:hover {
        background-color: #f8fbff;
    }

    .video-player {
        width: 100%;
        max-height: 600px;
        background: #0b1437;
        border-radius: 12px;
        display: block;
    }

    .resource-viewer {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
    }

    .document-viewer {
    width: 100%;
    height: 700px;
    border: none;
    border-radius: 10px;
    background: #fff;
    }

    .resource-empty {
        min-height: 350px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

</style>

<div class="container-fluid">

@auth

@component('components.breadcrumb')

    @slot('li_1')
        <a href="{{ route('cours.espace', $cours) }}">
            {{ $cours->titre }}
        </a>
    @endslot

    @slot('title')
        {{ $chapitre->titre }}
    @endslot

@endcomponent

@endauth


<div class="row g-4">


    {{-- =================================================
         COLONNE GAUCHE
         Chapitres / Ressources / Quiz
    ================================================== --}}
    <div class="col-lg-3">

        <div class="card shadow-sm">

            {{-- Chapitres --}}
            <div class="card-header bg-white">
                <strong>Contenu du chapitre</strong>
            </div>

            <div class="list-group list-group-flush">

                @foreach($cours->chapitres as $item)

                    <a href="{{ route('cours.chapitre', [$cours, $item]) }}"
                       class="list-group-item list-group-item-action d-flex justify-content-between align-items-center
                       {{ $item->id == $chapitre->id ? 'active' : '' }}">

                        <span>
                            <small>{{ $item->ordre }}.</small>
                            {{ $item->titre }}
                        </span>

                        <i class="bi bi-chevron-right small"></i>

                    </a>

                @endforeach

            </div>


            {{-- Ressources --}}
            <div class="card-header bg-white border-top">

                <strong>
                    <i class="bi bi-folder me-1"></i>
                    Ressources
                </strong>

            </div>


            <div class="list-group list-group-flush">

                @forelse($chapitre->resources as $resource)

                    @if($resource->type === 'video')

                        <a href="#"
                           onclick="afficherVideo({{ $resource->id }}); return false;"
                           class="list-group-item list-group-item-action resource-item">

                            <i class="bi bi-play-circle text-primary me-2"></i>

                            {{ $resource->titre }}

                        </a>

                    @elseif($resource->type === 'fichier')

                    @if(strtolower($resource->extension ?? '') === 'pdf')

                        <a href="#"
                        onclick="afficherDocument({{ $resource->id }}); return false;"
                        class="list-group-item list-group-item-action resource-item">

                            <i class="bi bi-file-earmark-pdf text-danger me-2"></i>

                            {{ $resource->titre }}

                        </a>

                    @else

                        <a href="{{ route('cours.resources.voir', [$cours, $resource]) }}"
                        class="list-group-item list-group-item-action">

                            <i class="bi bi-file-earmark-text text-primary me-2"></i>

                            {{ $resource->titre }}

                        </a>

                    @endif

                    @elseif($resource->type === 'lien')

                        <a href="{{ $resource->url }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="list-group-item list-group-item-action">

                            <i class="bi bi-link-45deg text-primary me-2"></i>

                            {{ $resource->titre }}

                        </a>

                    @endif

                @empty

                    <div class="list-group-item text-muted small">

                        Aucune ressource

                    </div>

                @endforelse

            </div>


            {{-- Quiz --}}
            <div class="card-header bg-white border-top">

                <strong>
                    <i class="bi bi-question-circle me-1"></i>
                    Quiz
                </strong>

            </div>

            <div class="list-group-item d-flex justify-content-between align-items-center text-muted">

                Quiz du chapitre

                <i class="bi bi-lock small"></i>

            </div>

        </div>

    </div>



    {{-- =================================================
         COLONNE DROITE
         CONTENU DU CHAPITRE
    ================================================== --}}
    <div class="col-lg-9">

        <div class="card shadow-sm mb-4">

            <div class="card-body p-4">

                <h5 class="fw-bold mb-3">

                    {{ $chapitre->titre }}

                </h5>


                {{-- =================================================
                     LECTEUR VIDÉO
                ================================================== --}}

                @php
                    $video = $chapitre->resources
                        ->where('type', 'video')
                        ->first();
                @endphp


                @if($video)

                    <div id="resourceContainer" class="resource-viewer mb-4">

                        @if($video->chemin)

                            <video
                                id="videoPlayer"
                                class="video-player"
                                controls
                                preload="metadata"
                            >

                                <source
                                    src="{{ asset('storage/' . $video->chemin) }}"
                                    type="video/{{ strtolower($video->extension ?? 'mp4') }}"
                                >

                                Votre navigateur ne prend pas en charge la lecture vidéo.

                            </video>

                        @elseif($video->url)

                            <div class="ratio ratio-16x9">

                                <iframe
                                    id="videoIframe"
                                    src="{{ $video->url }}"
                                    title="{{ $video->titre }}"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen>
                                </iframe>

                            </div>

                        @endif

                    </div>

                @else

                    <div class="alert alert-light border text-muted mb-4">

                        <i class="bi bi-play-circle me-2"></i>

                        Aucune vidéo disponible pour ce chapitre.

                    </div>

                @endif



                {{-- =================================================
                     À RETENIR
                ================================================== --}}

                <div class="p-3 rounded"
                     style="background:#eef6ff;">

                    <div class="fw-bold text-primary mb-2">

                        <i class="bi bi-lightbulb me-1"></i>

                        À retenir

                    </div>


                    <ul class="mb-0">

                        @foreach(explode("\n", trim($chapitre->contenu ?? '')) as $ligne)

                            @continue(trim($ligne) === '')

                            <li>
                                {{ trim($ligne) }}
                            </li>

                        @endforeach

                    </ul>

                </div>

            </div>

        </div>



        {{-- =================================================
             NAVIGATION
        ================================================== --}}

        <div class="d-flex justify-content-between">

            @php

                $chapitrePrecedent = $cours->chapitres
                    ->where('ordre', '<', $chapitre->ordre)
                    ->sortByDesc('ordre')
                    ->first();

                $chapitreSuivant = $cours->chapitres
                    ->where('ordre', '>', $chapitre->ordre)
                    ->sortBy('ordre')
                    ->first();

            @endphp


            @if($chapitrePrecedent)

                <a href="{{ route('cours.chapitre', [$cours, $chapitrePrecedent]) }}"
                   class="btn btn-outline-secondary">

                    <i class="bi bi-arrow-left"></i>

                    Chapitre précédent

                </a>

            @else

                <span></span>

            @endif


            @if($chapitreSuivant)

                <a href="{{ route('cours.chapitre', [$cours, $chapitreSuivant]) }}"
                   class="btn btn-primary">

                    Chapitre suivant

                    <i class="bi bi-arrow-right"></i>

                </a>

            @endif

        </div>

    </div>

</div>

</div>


{{-- =========================================================
     DONNÉES DES VIDÉOS
========================================================= --}}

<script>

    const videos = {

        @foreach($chapitre->resources->where('type', 'video') as $resource)

            {{ $resource->id }}: {

                titre: @json($resource->titre),

                chemin: @json($resource->chemin),

                url: @json($resource->url),

                extension: @json($resource->extension)

            },

        @endforeach

    };

    const documents = {

        @foreach($chapitre->resources->where('type', 'fichier') as $resource)

            {{ $resource->id }}: {

                titre: @json($resource->titre),

                chemin: @json($resource->chemin),

                extension: @json($resource->extension)

            },

        @endforeach

    };


    function afficherVideo(resourceId) {

        const video = videos[resourceId];

        if (!video) {
            return;
        }


        const container = document.getElementById('resourceContainer');

        if (!container) {
            return;
        }


        // Vidéo uploadée localement
        if (video.chemin) {

            container.innerHTML = `

                <video
                    id="videoPlayer"
                    class="video-player"
                    controls
                    preload="metadata"
                >

                    <source
                        src="/storage/${video.chemin}"
                        type="video/${video.extension || 'mp4'}"
                    >

                    Votre navigateur ne prend pas en charge la lecture vidéo.

                </video>

            `;

        }

        // Vidéo externe
        else if (video.url) {

            container.innerHTML = `

                <div class="ratio ratio-16x9">

                    <iframe
                        src="${video.url}"
                        title="${video.titre}"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen>
                    </iframe>

                </div>

            `;

        }


        // Remonte automatiquement vers le lecteur
        container.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });

    }

    function afficherDocument(resourceId) {

        const document = documents[resourceId];

        if (!document) {
            return;
        }

        const container = document.getElementById('resourceContainer');

        if (!container) {
            return;
        }

        if (
            document.extension &&
            document.extension.toLowerCase() === 'pdf'
        ) {

            container.innerHTML = `

                <div class="mb-3 d-flex justify-content-between align-items-center">

                    <div>
                        <h6 class="fw-bold mb-1">
                            ${document.titre}
                        </h6>

                        <small class="text-muted">
                            Document PDF
                        </small>
                    </div>

                    <a
                        href="/storage/${document.chemin}"
                        target="_blank"
                        class="btn btn-sm btn-outline-primary"
                    >
                        <i class="bi bi-box-arrow-up-right me-1"></i>
                        Ouvrir
                    </a>

                </div>

                <iframe
                    src="/storage/${document.chemin}"
                    class="document-viewer"
                    title="${document.titre}"
                ></iframe>

            `;

        }

        container.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });

    }

</script>

@endsection