@extends('layouts.master')

@section('title', 'Ressources du cours')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('admin.cours.contenus', $cours) }}">{{ $cours->titre }}</a>
        @endslot
        @slot('title')
            Ressources du cours
        @endslot
    @endcomponent

    <div class="container-fluid pb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h4 class="mb-1">Ressources du cours</h4>
                <p class="text-muted mb-0">Gérez les documents, vidéos et liens pédagogiques de ce cours.</p>
            </div>
            <a href="{{ route('admin.cours.contenus', $cours) }}" class="btn btn-primary">
                <i class="ri-add-line me-1"></i>Ajouter une ressource
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ri-checkbox-circle-line me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="card-title mb-1">Ressources pédagogiques</h5>
                    <span class="text-muted small">Cours : {{ $cours->titre }}</span>
                </div>
                <span class="badge bg-primary-subtle text-primary">{{ $resources->count() }} ressource(s)</span>
            </div>
            <div class="card-body">
                @forelse ($resources as $resource)
                    <div class="d-flex flex-wrap align-items-center gap-3 border-bottom py-3">
                        <span class="avatar-sm bg-{{ $resource->type === 'video' ? 'danger' : ($resource->type === 'lien' ? 'success' : 'primary') }}-subtle text-{{ $resource->type === 'video' ? 'danger' : ($resource->type === 'lien' ? 'success' : 'primary') }} rounded d-inline-flex align-items-center justify-content-center fs-5">
                            <i class="{{ $resource->type === 'video' ? 'ri-video-line' : ($resource->type === 'lien' ? 'ri-links-line' : 'ri-file-text-line') }}"></i>
                        </span>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold">{{ $resource->titre }}</div>
                            <div class="small text-muted">
                                {{ $resource->type === 'fichier' ? 'Document' : ($resource->type === 'video' ? 'Vidéo' : 'Lien') }}
                                @if ($resource->extension)
                                    · {{ strtoupper($resource->extension) }}
                                @endif
                                @if ($resource->taille_lisible)
                                    · {{ $resource->taille_lisible }}
                                @endif
                                @if ($resource->type === 'video' && $resource->chemin)
                                    · Vidéo importée
                                @elseif ($resource->type === 'video' && $resource->url)
                                    · Vidéo externe
                                @elseif ($resource->type === 'lien')
                                    · Lien externe
                                @endif
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-1">
                            @if ($resource->type === 'fichier')
                                <a href="{{ route('cours.resources.download', [$cours, $resource]) }}"
                                   class="btn btn-sm btn-soft-secondary" title="Télécharger la ressource">
                                    <i class="ri-download-line"></i>
                                </a>
                                <a href="{{ route('cours.resources.voir', [$cours, $resource]) }}"
                                   class="btn btn-sm btn-soft-primary" target="_blank" rel="noopener noreferrer"
                                   title="Consulter la ressource">
                                    <i class="ri-eye-line"></i>
                                </a>
                            @elseif ($resource->type === 'video')
                                <button type="button" class="btn btn-sm btn-soft-primary"
                                        data-bs-toggle="modal" data-bs-target="#videoModal{{ $resource->id }}"
                                        title="Consulter la vidéo">
                                    <i class="ri-eye-line"></i>
                                </button>
                            @elseif ($resource->url)
                                <a href="{{ $resource->url }}" class="btn btn-sm btn-soft-primary"
                                   target="_blank" rel="noopener noreferrer" title="Ouvrir la ressource">
                                    <i class="ri-external-link-line"></i>
                                </a>
                            @endif

                            <form action="{{ route('admin.cours.resources.destroy', [$cours, $resource]) }}"
                                  method="POST" onsubmit="return confirm('Supprimer cette ressource ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-soft-danger" title="Supprimer la ressource">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    @if ($resource->type === 'video')
                        <div class="modal fade" id="videoModal{{ $resource->id }}" tabindex="-1"
                             aria-labelledby="videoModalLabel{{ $resource->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-xl modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="videoModalLabel{{ $resource->id }}">{{ $resource->titre }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                    </div>
                                    <div class="modal-body p-0 bg-dark">
                                        @if ($resource->chemin)
                                            <video controls playsinline preload="metadata" class="w-100 nv-video-preview">
                                                <source src="{{ asset('storage/' . $resource->chemin) }}"
                                                        type="video/{{ strtolower($resource->extension ?? 'mp4') }}">
                                                Votre navigateur ne prend pas en charge la lecture vidéo.
                                            </video>
                                        @elseif ($resource->url)
                                            <div class="ratio ratio-16x9">
                                                <iframe src="{{ $resource->url }}" title="{{ $resource->titre }}"
                                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                        allowfullscreen></iframe>
                                            </div>
                                        @else
                                            <div class="p-5 text-center text-white">
                                                <i class="ri-error-warning-line fs-1 d-block mb-3"></i>
                                                <p class="mb-0">Cette vidéo n'est pas disponible.</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="text-center py-5">
                        <i class="ri-folder-open-line fs-1 text-muted"></i>
                        <h5 class="mt-3">Aucune ressource pour le moment</h5>
                        <p class="text-muted mb-3">Ce cours ne contient encore aucune ressource pédagogique.</p>
                        <a href="{{ route('admin.cours.contenus', $cours) }}" class="btn btn-primary">
                            <i class="ri-add-line me-1"></i>Ajouter une ressource
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
