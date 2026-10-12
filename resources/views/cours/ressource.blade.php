@extends(Auth::check() ? 'layouts.master' : 'layouts.master-without-nav')

@section('title', $resource->titre)

@section('content')

@php
    $iconeDe = fn ($r) => match (true) {
        $r->type === 'video' => 'ri-play-circle-line',
        $r->type === 'lien' => 'ri-links-line',
        strtolower($r->extension ?? '') === 'pdf' => 'ri-file-pdf-2-line',
        default => 'ri-file-text-line',
    };

    $estPdf = strtolower($resource->extension ?? '') === 'pdf';
@endphp

{{-- ===== Fil d'Ariane ===== --}}
@auth
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('cours.espace', $cours) }}">{{ $cours->titre }}</a>
        @endslot
        @slot('title')
            {{ $resource->titre }}
        @endslot
    @endcomponent
@endauth

<div class="container-fluid pb-4">
    <div class="row g-4">

        {{-- ===== Menu gauche : ressources du cours ===== --}}
        <div class="col-lg-3">
            <div class="card border shadow-sm mb-0">
                <div class="card-header bg-transparent border-bottom">
                    <h6 class="fw-bold mb-0">
                        <i class="ri-folder-open-line text-danger me-2"></i>Ressources du cours
                    </h6>
                </div>

                <div class="list-group list-group-flush">
                    @foreach($ressources as $item)
                        @if($item->type === 'lien')
                            <a href="{{ $item->url }}" target="_blank" rel="noopener noreferrer"
                               class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                                <i class="{{ $iconeDe($item) }}"></i>
                                <span class="flex-grow-1">{{ $item->titre }}</span>
                                <i class="ri-external-link-line small"></i>
                            </a>
                        @else
                            <a href="{{ route('cours.ressource', [$cours, $item]) }}"
                               class="list-group-item list-group-item-action d-flex align-items-center gap-2
                                      {{ $item->id === $resource->id ? 'active' : '' }}">
                                <i class="{{ $iconeDe($item) }}"></i>
                                <span class="flex-grow-1">{{ $item->titre }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>

                <div class="card-body border-top">
                    <a href="{{ route('cours.espace', $cours) }}" class="btn btn-outline-primary btn-sm w-100">
                        <i class="ri-arrow-left-line me-1"></i> Retour à l'espace formation
                    </a>
                </div>
            </div>
        </div>

        {{-- ===== Contenu de la ressource ===== --}}
        <div class="col-lg-9">
            <div class="card border shadow-sm mb-0">
                <div class="card-body p-4">

                    <div class="d-flex justify-content-between align-items-center gap-2 mb-4">
                        <h4 class="fw-bold mb-0">
                            <i class="{{ $iconeDe($resource) }} text-danger me-2"></i>{{ $resource->titre }}
                        </h4>

                        @if($resource->type === 'fichier' && $resource->chemin)
                            <a href="{{ route('cours.resources.download', [$cours, $resource]) }}"
                               class="btn btn-sm btn-outline-primary text-nowrap">
                                <i class="ri-download-2-line me-1"></i> Télécharger
                            </a>
                        @endif
                    </div>

                    @if($resource->type === 'video' && $resource->chemin)
                        <div class="ratio ratio-16x9">
                            <video controls preload="metadata" class="rounded-3 bg-dark">
                                <source src="{{ route('cours.resources.voir', [$cours, $resource]) }}"
                                        type="video/{{ strtolower($resource->extension ?? 'mp4') }}">
                                Votre navigateur ne prend pas en charge la lecture vidéo.
                            </video>
                        </div>

                    @elseif($resource->type === 'video' && $resource->url)
                        <div class="ratio ratio-16x9">
                            <iframe src="{{ $resource->url }}" title="{{ $resource->titre }}"
                                    class="rounded-3"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen></iframe>
                        </div>

                    @elseif($resource->type === 'fichier' && $resource->chemin && $estPdf)
                        <div class="ratio ratio-4x3">
                            <iframe src="{{ route('cours.resources.voir', [$cours, $resource]) }}"
                                    title="{{ $resource->titre }}" class="rounded-3 border"></iframe>
                        </div>

                    @elseif($resource->type === 'fichier' && $resource->chemin)
                        <div class="text-center text-muted py-5">
                            <i class="ri-file-text-line display-4 d-block mb-3"></i>
                            <p class="mb-3">Ce type de document ne peut pas être affiché directement.</p>
                            <a href="{{ route('cours.resources.download', [$cours, $resource]) }}" class="btn btn-primary">
                                <i class="ri-download-2-line me-1"></i> Télécharger le fichier
                            </a>
                        </div>

                    @elseif($resource->type === 'lien' && $resource->url)
                        <div class="text-center text-muted py-5">
                            <i class="ri-links-line display-4 d-block mb-3"></i>
                            <a href="{{ $resource->url }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary">
                                Ouvrir le lien <i class="ri-external-link-line ms-1"></i>
                            </a>
                        </div>

                    @else
                        <div class="text-center text-muted py-5">
                            <i class="ri-file-warning-line display-4 d-block mb-2"></i>
                            Aucun contenu disponible pour cette ressource.
                        </div>
                    @endif

                </div>
            </div>
        </div>

    </div>
</div>

@endsection