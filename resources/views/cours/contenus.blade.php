@extends('layouts.master')

@section('title', 'Contenus pédagogiques')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('admin.cours.show', $cours) }}">Cours</a>
        @endslot
        @slot('title')
            {{ $cours->titre }}
        @endslot
    @endcomponent

    <div class="container-fluid pb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h4 class="mb-1">Contenus pédagogiques</h4>
                <p class="text-muted mb-0">Organisez les chapitres et les ressources de ce cours.</p>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAjouterChapitre">
                <i class="ri-add-line me-1"></i>Ajouter un chapitre
            </button>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ri-checkbox-circle-line me-1"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong>Une erreur est survenue.</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Chapitres</h5>
                        <span class="badge bg-primary-subtle text-primary">{{ $chapitres->count() }}</span>
                    </div>
                    <div class="list-group list-group-flush">
                        @forelse ($chapitres as $index => $chapitre)
                            <button type="button"
                                    class="list-group-item list-group-item-action d-flex align-items-center gap-3 chapitre-item {{ $index === 0 ? 'active' : '' }}"
                                    data-chapter-id="{{ $chapitre->id }}"
                                    data-chapter-number="{{ $chapitre->ordre }}"
                                    data-chapter-title="{{ $chapitre->titre }}">
                                <span class="badge {{ $index === 0 ? 'bg-light text-primary' : 'bg-primary-subtle text-primary' }}">
                                    {{ $chapitre->ordre }}
                                </span>
                                <span class="flex-grow-1 text-start">
                                    <span class="d-block fw-medium">{{ $chapitre->titre }}</span>
                                    <small class="{{ $index === 0 ? 'text-white-50' : 'text-muted' }}">Chapitre {{ $chapitre->ordre }}</small>
                                </span>
                                <i class="ri-arrow-right-s-line"></i>
                            </button>
                        @empty
                            <div class="card-body text-center py-5">
                                <i class="ri-book-open-line fs-1 text-muted"></i>
                                <p class="text-muted mb-3">Aucun chapitre n'a encore été créé.</p>
                                <button type="button" class="btn btn-primary btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#modalAjouterChapitre">
                                    <i class="ri-add-line me-1"></i>Ajouter un chapitre
                                </button>
                            </div>
                        @endforelse
                    </div>
                </div>

                @if ($chapitres->isNotEmpty())
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Chapitre sélectionné</h5>
                        </div>
                        <div class="card-body d-flex align-items-center gap-3">
                            <span class="avatar-sm">
                                <span class="avatar-title rounded bg-primary-subtle text-primary" id="selectedChapterNumber">
                                    {{ $chapitres->first()->ordre }}
                                </span>
                            </span>
                            <div>
                                <div class="fw-medium" id="selectedChapterName">{{ $chapitres->first()->titre }}</div>
                                <small class="text-muted">Chapitre sélectionné</small>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-xl-8">
                @forelse ($chapitres as $index => $chapitre)
                    <div class="card chapter-panel {{ $index === 0 ? '' : 'd-none' }}"
                         data-chapter-panel="{{ $chapitre->id }}">
                        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <span class="badge bg-primary-subtle text-primary mb-2">Chapitre {{ $chapitre->ordre }}</span>
                                <h5 class="card-title mb-0">{{ $chapitre->titre }}</h5>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.cours.chapitres.edit', [$cours, $chapitre]) }}"
                                   class="btn btn-sm btn-soft-secondary" title="Modifier le chapitre">
                                    <i class="ri-edit-line"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.cours.chapitres.destroy', [$cours, $chapitre]) }}"
                                      onsubmit="return confirm('Voulez-vous vraiment supprimer ce chapitre ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-soft-danger" title="Supprimer le chapitre">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.cours.chapitres.update', [$cours, $chapitre]) }}">
                                @csrf
                                @method('PUT')
                                <div class="mb-3">
                                    <label for="titre-{{ $chapitre->id }}" class="form-label">Titre du chapitre</label>
                                    <input type="text" id="titre-{{ $chapitre->id }}" name="titre"
                                           class="form-control" value="{{ $chapitre->titre }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="contenu-{{ $chapitre->id }}" class="form-label">Contenu / description</label>
                                    <textarea id="contenu-{{ $chapitre->id }}" name="contenu" class="form-control"
                                              rows="4" placeholder="Décrivez le contenu de ce chapitre...">{{ $chapitre->contenu }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-line me-1"></i>Enregistrer les modifications
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="ri-folder-open-line fs-1 text-muted"></i>
                            <h5 class="mt-3">Aucun chapitre</h5>
                            <p class="text-muted">Créez un chapitre pour organiser le contenu de ce cours.</p>
                        </div>
                    </div>
                @endforelse

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Ressources du cours</h5>
                        <span class="badge bg-primary-subtle text-primary">{{ $resources->count() }}</span>
                    </div>
                    <div class="card-body">
                        @forelse ($resources as $resource)
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 py-2 border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    @php
                                        $resourceIcon = match ($resource->type) {
                                            'video' => 'ri-video-line',
                                            'lien' => 'ri-links-line',
                                            default => 'ri-file-text-line',
                                        };
                                    @endphp
                                    <span class="avatar-xs">
                                        <span class="avatar-title rounded bg-primary-subtle text-primary">
                                            <i class="{{ $resourceIcon }}"></i>
                                        </span>
                                    </span>
                                    <div>
                                        <div class="fw-medium">{{ $resource->titre }}</div>
                                        <small class="text-muted text-capitalize">
                                            {{ $resource->type }}
                                            @if ($resource->extension) · {{ strtoupper($resource->extension) }} @endif
                                            @if ($resource->taille_lisible) · {{ $resource->taille_lisible }} @endif
                                        </small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    <a href="{{ route('cours.resources.voir', [$cours, $resource]) }}"
                                       class="btn btn-sm btn-soft-primary" title="Consulter" target="_blank">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                    @if ($resource->chemin)
                                        <a href="{{ route('cours.resources.download', [$cours, $resource]) }}"
                                           class="btn btn-sm btn-soft-success" title="Télécharger">
                                            <i class="ri-download-line"></i>
                                        </a>
                                    @endif
                                    <form method="POST" action="{{ route('admin.cours.resources.destroy', [$cours, $resource]) }}"
                                          onsubmit="return confirm('Voulez-vous supprimer cette ressource ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-soft-danger" title="Supprimer">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted text-center py-3 mb-0">Aucune ressource dans ce cours.</p>
                        @endforelse
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Ajouter une ressource</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.cours.resources.store', $cours) }}"
                              enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="resourceTitre" class="form-label">Titre</label>
                                <input type="text" id="resourceTitre" name="titre" class="form-control"
                                       placeholder="Ex. Support du cours" value="{{ old('titre') }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="resourceType" class="form-label">Type</label>
                                <select name="type" id="resourceType" class="form-select" required>
                                    <option value="fichier" @selected(old('type', 'fichier') === 'fichier')>Document</option>
                                    <option value="video" @selected(old('type') === 'video')>Vidéo</option>
                                    <option value="lien" @selected(old('type') === 'lien')>Lien</option>
                                </select>
                            </div>
                            <div class="mb-3 d-none" id="fileField">
                                <label for="resourceFile" id="fileLabel" class="form-label">Fichier</label>
                                <input type="file" id="resourceFile" name="fichier" class="form-control">
                                <div class="form-text" id="fileHelp">PDF, DOC, DOCX, PPT, PPTX, XLS ou XLSX</div>
                            </div>
                            <div class="mb-3 d-none" id="videoSourceField">
                                <label class="form-label">Source de la vidéo</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" id="videoUpload" name="video_source"
                                           value="upload" @checked(old('video_source', 'upload') === 'upload')>
                                    <label class="form-check-label" for="videoUpload">Importer une vidéo</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" id="videoUrl" name="video_source"
                                           value="url" @checked(old('video_source') === 'url')>
                                    <label class="form-check-label" for="videoUrl">Utiliser une URL</label>
                                </div>
                            </div>
                            <div class="mb-3 d-none" id="urlField">
                                <label for="resourceUrl" class="form-label">URL</label>
                                <input type="url" id="resourceUrl" name="url" class="form-control"
                                       placeholder="https://" value="{{ old('url') }}">
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-add-line me-1"></i>Ajouter la ressource
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalAjouterChapitre" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ri-add-circle-line me-2 text-primary"></i>Ajouter un chapitre</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <form method="POST" action="{{ route('admin.cours.chapitres.store', $cours) }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nouveauTitre" class="form-label">Titre du chapitre</label>
                            <input type="text" id="nouveauTitre" name="titre" class="form-control" required>
                        </div>
                        <div>
                            <label for="nouveauContenu" class="form-label">Contenu / description</label>
                            <textarea id="nouveauContenu" name="contenu" class="form-control" rows="4"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-add-line me-1"></i>Créer le chapitre
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chapitreItems = document.querySelectorAll('.chapitre-item');
            const chapitrePanels = document.querySelectorAll('.chapter-panel');
            const selectedChapterNumber = document.getElementById('selectedChapterNumber');
            const selectedChapterName = document.getElementById('selectedChapterName');

            chapitreItems.forEach(function (item) {
                item.addEventListener('click', function () {
                    chapitreItems.forEach(function (element) {
                        element.classList.remove('active');
                        element.querySelector('.badge')?.classList.replace('bg-light', 'bg-primary-subtle');
                        element.querySelector('.badge')?.classList.replace('text-primary', 'text-primary');
                        element.querySelector('small')?.classList.replace('text-white-50', 'text-muted');
                    });
                    this.classList.add('active');
                    this.querySelector('.badge')?.classList.replace('bg-primary-subtle', 'bg-light');
                    this.querySelector('small')?.classList.replace('text-muted', 'text-white-50');
                    chapitrePanels.forEach(function (panel) {
                        panel.classList.toggle('d-none', panel.dataset.chapterPanel !== this.dataset.chapterId);
                    }, this);
                    if (selectedChapterNumber) selectedChapterNumber.textContent = this.dataset.chapterNumber;
                    if (selectedChapterName) selectedChapterName.textContent = this.dataset.chapterTitle;
                });
            });

            const resourceType = document.getElementById('resourceType');
            const fileField = document.getElementById('fileField');
            const videoSourceField = document.getElementById('videoSourceField');
            const urlField = document.getElementById('urlField');
            const resourceFile = document.getElementById('resourceFile');
            const resourceUrl = document.getElementById('resourceUrl');
            const videoUpload = document.getElementById('videoUpload');
            const videoUrl = document.getElementById('videoUrl');
            const fileLabel = document.getElementById('fileLabel');
            const fileHelp = document.getElementById('fileHelp');

            function mettreAJourSourceVideo() {
                if (!resourceType || resourceType.value !== 'video') return;
                const utiliserFichier = videoUpload.checked;
                fileField.classList.toggle('d-none', !utiliserFichier);
                urlField.classList.toggle('d-none', utiliserFichier);
                resourceFile.disabled = !utiliserFichier;
                resourceFile.required = utiliserFichier;
                resourceUrl.disabled = utiliserFichier;
                resourceUrl.required = !utiliserFichier;
                if (!utiliserFichier) resourceUrl.placeholder = 'URL de la vidéo';
            }

            function mettreAJourTypeRessource() {
                if (!resourceType) return;
                const type = resourceType.value;
                fileField.classList.toggle('d-none', type !== 'fichier');
                videoSourceField.classList.toggle('d-none', type !== 'video');
                urlField.classList.toggle('d-none', type !== 'lien');

                resourceFile.disabled = type !== 'fichier';
                resourceFile.required = type === 'fichier';
                resourceUrl.disabled = type !== 'lien';
                resourceUrl.required = type === 'lien';

                if (type === 'fichier') {
                    fileLabel.textContent = 'Document';
                    fileHelp.textContent = 'PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, JPG ou PNG';
                } else if (type === 'video') {
                    fileLabel.textContent = 'Fichier vidéo';
                    fileHelp.textContent = 'MP4, WebM, OGG, MOV, AVI ou MKV';
                    mettreAJourSourceVideo();
                }
            }

            if (resourceType) resourceType.addEventListener('change', mettreAJourTypeRessource);
            if (videoUpload) videoUpload.addEventListener('change', mettreAJourSourceVideo);
            if (videoUrl) videoUrl.addEventListener('change', mettreAJourSourceVideo);
            mettreAJourTypeRessource();
        });
    </script>
@endsection
