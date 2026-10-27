@extends('layouts.master')

@section('title', 'Ajouter une ressource')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('admin.cours.contenus', $cours) }}">{{ $cours->titre }}</a>
        @endslot
        @slot('title')
            Ajouter une ressource
        @endslot
    @endcomponent

    <div class="container-fluid pb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h4 class="mb-1">Ajouter une ressource</h4>
                <p class="text-muted mb-0">Ajoutez un document, une vidéo ou un lien à ce cours.</p>
            </div>
            <a href="{{ route('admin.cours.contenus', $cours) }}" class="btn btn-soft-secondary">
                <i class="ri-arrow-left-line me-1"></i>Retour aux contenus
            </a>
        </div>

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
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-1">Informations de la ressource</h5>
                        <p class="text-muted mb-0">Cours : <strong>{{ $cours->titre }}</strong></p>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.cours.resources.store', $cours) }}"
                              enctype="multipart/form-data" id="resourceForm">
                            @csrf

                            <div class="mb-4">
                                <label for="titre" class="form-label">Titre de la ressource <span class="text-danger">*</span></label>
                                <input type="text" id="titre" name="titre" value="{{ old('titre') }}"
                                       class="form-control @error('titre') is-invalid @enderror"
                                       placeholder="Exemple : Support de cours Laravel" maxlength="255" required>
                                @error('titre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="type" class="form-label">Type de ressource <span class="text-danger">*</span></label>
                                <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="">Sélectionnez un type</option>
                                    <option value="fichier" {{ old('type') === 'fichier' ? 'selected' : '' }}>Document / Image</option>
                                    <option value="video" {{ old('type') === 'video' ? 'selected' : '' }}>Vidéo</option>
                                    <option value="lien" {{ old('type') === 'lien' ? 'selected' : '' }}>Lien externe</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4 d-none" id="fichierField">
                                <label for="fichier" class="form-label">Fichier <span class="text-danger">*</span></label>
                                <input type="file" id="fichier" name="fichier"
                                       class="form-control @error('fichier') is-invalid @enderror"
                                       accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png">
                                <div class="form-text">Documents, images ou supports de cours. Taille maximale : 1 Go.</div>
                                @error('fichier')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4 d-none" id="videoSourceField">
                                <fieldset>
                                    <legend class="form-label">Source de la vidéo <span class="text-danger">*</span></legend>
                                    <div class="d-flex flex-wrap gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" id="videoUpload"
                                                   name="video_source" value="upload"
                                                   {{ old('video_source', 'upload') === 'upload' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="videoUpload">Importer une vidéo</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" id="videoUrl"
                                                   name="video_source" value="url"
                                                   {{ old('video_source') === 'url' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="videoUrl">Utiliser une URL</label>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>

                            <div class="mb-4 d-none" id="videoFileField">
                                <label for="videoFile" class="form-label">Vidéo <span class="text-danger">*</span></label>
                                <input type="file" id="videoFile" name="fichier"
                                       class="form-control @error('fichier') is-invalid @enderror"
                                       accept="video/mp4,video/webm,video/quicktime,.mov,.avi,.mkv,.ogg">
                                <div class="form-text">Formats vidéo compatibles. Taille maximale : 1 Go.</div>
                                @error('fichier')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4 d-none" id="urlField">
                                <label for="url" class="form-label">URL de la ressource <span class="text-danger">*</span></label>
                                <input type="url" id="url" name="url" value="{{ old('url') }}"
                                       class="form-control @error('url') is-invalid @enderror" placeholder="https://...">
                                <div class="form-text" id="urlHelp">Entrez l'adresse URL de la ressource.</div>
                                @error('url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end gap-2 border-top pt-3">
                                <a href="{{ route('admin.cours.contenus', $cours) }}" class="btn btn-light">Annuler</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-add-line me-1"></i>Ajouter la ressource
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Chapitre concerné</h5>
                    </div>
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="avatar-sm bg-primary-subtle text-primary rounded d-inline-flex align-items-center justify-content-center fs-5">
                            {{ $chapitre->ordre }}
                        </span>
                        <div>
                            <div class="fw-semibold">{{ $chapitre->titre }}</div>
                            <div class="small text-muted">Chapitre {{ $chapitre->ordre }}</div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Types disponibles</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <i class="ri-file-text-line fs-4 text-primary"></i>
                            <div><strong class="d-block">Document / Image</strong><span class="small text-muted">PDF, Word, Excel, JPG, PNG...</span></div>
                        </div>
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <i class="ri-video-line fs-4 text-danger"></i>
                            <div><strong class="d-block">Vidéo</strong><span class="small text-muted">Importée ou accessible par URL.</span></div>
                        </div>
                        <div class="d-flex align-items-start gap-3">
                            <i class="ri-links-line fs-4 text-success"></i>
                            <div><strong class="d-block">Lien externe</strong><span class="small text-muted">Documentation, site web...</span></div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info mb-0" role="note">
                    <i class="ri-lightbulb-line me-2"></i>
                    Utilisez un titre clair pour aider les apprenants à identifier la ressource.
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const typeSelect = document.getElementById('type');
            const fichierField = document.getElementById('fichierField');
            const videoSourceField = document.getElementById('videoSourceField');
            const videoFileField = document.getElementById('videoFileField');
            const urlField = document.getElementById('urlField');
            const fichierInput = document.getElementById('fichier');
            const videoFileInput = document.getElementById('videoFile');
            const urlInput = document.getElementById('url');
            const urlHelp = document.getElementById('urlHelp');
            const videoUploadRadio = document.getElementById('videoUpload');
            const videoUrlRadio = document.getElementById('videoUrl');

            function hideConditionalFields() {
                [fichierField, videoSourceField, videoFileField, urlField].forEach(field => field.classList.add('d-none'));
                fichierInput.disabled = true;
                videoFileInput.disabled = true;
                urlInput.disabled = true;
                fichierInput.required = false;
                videoFileInput.required = false;
                urlInput.required = false;
            }

            function updateVideoSource() {
                videoFileField.classList.add('d-none');
                urlField.classList.add('d-none');
                videoFileInput.disabled = true;
                urlInput.disabled = true;
                videoFileInput.required = false;
                urlInput.required = false;

                if (videoUploadRadio.checked) {
                    videoFileField.classList.remove('d-none');
                    videoFileInput.disabled = false;
                    videoFileInput.required = true;
                } else if (videoUrlRadio.checked) {
                    urlField.classList.remove('d-none');
                    urlInput.disabled = false;
                    urlInput.required = true;
                    urlHelp.textContent = "Entrez l'URL de la vidéo, par exemple une vidéo YouTube ou Vimeo.";
                }
            }

            function updateResourceFields() {
                hideConditionalFields();

                if (typeSelect.value === 'fichier') {
                    fichierField.classList.remove('d-none');
                    fichierInput.disabled = false;
                    fichierInput.required = true;
                } else if (typeSelect.value === 'video') {
                    videoSourceField.classList.remove('d-none');
                    updateVideoSource();
                } else if (typeSelect.value === 'lien') {
                    urlField.classList.remove('d-none');
                    urlInput.disabled = false;
                    urlInput.required = true;
                    urlHelp.textContent = "Entrez l'URL du site ou de la documentation.";
                }
            }

            typeSelect.addEventListener('change', updateResourceFields);
            videoUploadRadio.addEventListener('change', updateVideoSource);
            videoUrlRadio.addEventListener('change', updateVideoSource);
            updateResourceFields();
        });
    </script>
@endsection
