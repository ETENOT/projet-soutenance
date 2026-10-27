@extends('layouts.master')

@section('title', 'Contenus pédagogiques')

@section('content')
    @php($chapitreSelectionne = $chapitreSelectionne ?? $chapitres->first())

    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('admin.cours.show', $cours) }}">{{ $cours->titre }}</a>
        @endslot
        @slot('title')
            Chapitres
        @endslot
    @endcomponent

    <div class="container-fluid pb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h4 class="mb-1">Gestion des contenus pédagogiques</h4>
                <p class="text-muted mb-0">Organisez les chapitres et les ressources de votre cours.</p>
            </div>
            <a href="{{ route('admin.cours.show', $cours) }}" class="btn btn-soft-secondary">
                <i class="ri-arrow-left-line me-1"></i>Retour au cours
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ri-checkbox-circle-line me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong>Vérifiez les informations saisies.</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">Chapitres</h5>
                            <span class="text-muted small">{{ $chapitres->count() }} chapitre(s)</span>
                        </div>
                        <a href="{{ route('admin.cours.chapitres.create', $cours) }}" class="btn btn-primary btn-sm">
                            <i class="ri-add-line me-1"></i>Ajouter
                        </a>
                    </div>
                    <div class="card-body p-2">
                        @forelse ($chapitres as $unChapitre)
                            @php($estSelectionne = $chapitreSelectionne && $chapitreSelectionne->id === $unChapitre->id)
                            <a href="{{ route('admin.cours.contenus', ['cours' => $cours, 'chapitre' => $unChapitre]) }}"
                               class="d-flex align-items-center gap-3 rounded p-3 text-reset text-decoration-none {{ $estSelectionne ? 'bg-primary-subtle text-primary' : '' }}">
                                <span class="avatar-xs bg-light rounded d-inline-flex align-items-center justify-content-center fw-semibold">
                                    {{ $unChapitre->ordre }}
                                </span>
                                <span class="flex-grow-1 min-w-0">
                                    <span class="d-block fw-semibold text-truncate">{{ $unChapitre->titre }}</span>
                                    <span class="d-block small text-muted">Chapitre {{ $unChapitre->ordre }}</span>
                                </span>
                                <i class="ri-arrow-right-s-line"></i>
                            </a>
                        @empty
                            <div class="text-center py-5 px-3">
                                <i class="ri-book-open-line fs-1 text-muted"></i>
                                <h6 class="mt-3">Aucun chapitre</h6>
                                <p class="text-muted small mb-3">Commencez par créer le premier chapitre de ce cours.</p>
                                <a href="{{ route('admin.cours.chapitres.create', $cours) }}" class="btn btn-primary btn-sm">
                                    <i class="ri-add-line me-1"></i>Ajouter un chapitre
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                @if ($chapitreSelectionne)
                    <div class="card">
                        <div class="card-header d-flex flex-wrap justify-content-between align-items-start gap-3">
                            <div>
                                <span class="badge bg-primary-subtle text-primary mb-2">Chapitre {{ $chapitreSelectionne->ordre }}</span>
                                <h5 class="card-title mb-1">{{ $chapitreSelectionne->titre }}</h5>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.cours.chapitres.edit', [$cours, $chapitreSelectionne]) }}"
                                   class="btn btn-soft-secondary btn-sm" title="Modifier le chapitre">
                                    <i class="ri-edit-line"></i>
                                </a>
                                <form action="{{ route('admin.cours.chapitres.destroy', [$cours, $chapitreSelectionne]) }}"
                                      method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer ce chapitre ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-soft-danger btn-sm" title="Supprimer le chapitre">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="mb-3"><i class="ri-file-text-line text-primary me-2"></i>Contenu du chapitre</h6>
                            @if ($chapitreSelectionne->contenu)
                                <div class="bg-light rounded p-3 mb-4">{!! nl2br(e($chapitreSelectionne->contenu)) !!}</div>
                            @else
                                <p class="text-muted mb-4">Aucun contenu renseigné pour ce chapitre.</p>
                            @endif

                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 border-top pt-3">
                                <h6 class="mb-0"><i class="ri-folder-3-line text-primary me-2"></i>Ressources du cours</h6>
                                <a href="{{ route('admin.cours.contenus', $cours) }}" class="btn btn-primary btn-sm">
                                    <i class="ri-add-line me-1"></i>Ajouter une ressource
                                </a>
                            </div>

                            @forelse ($cours->resources as $resource)
                                <div class="d-flex flex-wrap align-items-center gap-3 border-bottom py-3">
                                    <span class="avatar-sm bg-{{ $resource->type === 'video' ? 'danger' : ($resource->type === 'lien' ? 'success' : 'primary') }}-subtle text-{{ $resource->type === 'video' ? 'danger' : ($resource->type === 'lien' ? 'success' : 'primary') }} rounded d-inline-flex align-items-center justify-content-center fs-5">
                                        <i class="{{ $resource->type === 'video' ? 'ri-video-line' : ($resource->type === 'lien' ? 'ri-links-line' : 'ri-file-text-line') }}"></i>
                                    </span>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="fw-semibold text-truncate">{{ $resource->titre }}</div>
                                        <div class="small text-muted">
                                            {{ $resource->type === 'video' ? 'Vidéo' : ($resource->type === 'lien' ? 'Lien' : 'Document') }}
                                            @if ($resource->extension)
                                                · {{ strtoupper($resource->extension) }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        @if ($resource->type === 'fichier')
                                            <a href="{{ route('cours.resources.voir', [$cours, $resource]) }}" target="_blank"
                                               class="btn btn-soft-primary btn-sm" title="Consulter">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                            <a href="{{ route('cours.resources.download', [$cours, $resource]) }}"
                                               class="btn btn-soft-secondary btn-sm" title="Télécharger">
                                                <i class="ri-download-line"></i>
                                            </a>
                                        @elseif ($resource->url)
                                            <a href="{{ $resource->url }}" target="_blank" rel="noopener noreferrer"
                                               class="btn btn-soft-primary btn-sm" title="Ouvrir">
                                                <i class="ri-external-link-line"></i>
                                            </a>
                                        @endif
                                        <form action="{{ route('admin.cours.resources.destroy', [$cours, $resource]) }}"
                                              method="POST" onsubmit="return confirm('Voulez-vous supprimer cette ressource ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-soft-danger btn-sm" title="Supprimer">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4">
                                    <i class="ri-folder-open-line fs-1 text-muted"></i>
                                    <p class="text-muted mt-2 mb-3">Aucune ressource pour ce cours.</p>
                                    <a href="{{ route('admin.cours.contenus', $cours) }}" class="btn btn-soft-primary btn-sm">
                                        <i class="ri-add-line me-1"></i>Ajouter la première ressource
                                    </a>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @else
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="ri-book-open-line fs-1 text-muted"></i>
                            <h5 class="mt-3">Aucun chapitre sélectionné</h5>
                            <p class="text-muted mb-3">Créez un chapitre pour commencer à construire le contenu pédagogique.</p>
                            <a href="{{ route('admin.cours.chapitres.create', $cours) }}" class="btn btn-primary">
                                <i class="ri-add-line me-1"></i>Ajouter un chapitre
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
