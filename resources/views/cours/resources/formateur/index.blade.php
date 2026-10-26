@extends('layouts.master')

@section('title')
    Supports de cours — {{ $cours->titre }}
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('classes.formateur.index') }}">Mes classes</a>
        @endslot
        @slot('title')
            Supports de cours
        @endslot
    @endcomponent

    {{-- Erreurs de validation du formulaire de dépôt (les messages succès/erreur sont affichés par master.blade.php) --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $erreur)
                    <li>{{ $erreur }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">

        {{-- ====== SUPPORTS DÉJÀ DÉPOSÉS ====== --}}
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ $cours->titre }} — supports</h5>
                </div>
                <div class="card-body">
                    @forelse ($resources as $resource)
                        <div class="d-flex align-items-center justify-content-between border rounded p-2 mb-2">
                            <div class="me-2">
                                <div class="fw-medium">{{ $resource->titre }}</div>
                                <div class="text-muted fs-12">
                                    {{ ['video' => 'Vidéo', 'lien' => 'Lien'][$resource->type] ?? 'Document' }}
                                    @if ($resource->extension) · {{ strtoupper($resource->extension) }} @endif
                                    @if ($resource->taille_lisible) · {{ $resource->taille_lisible }} @endif
                                </div>
                            </div>
                            <div class="d-flex gap-1">
                                <a href="{{ route('cours.resources.voir', [$cours, $resource]) }}" target="_blank"
                                   class="btn btn-sm btn-soft-primary" title="Consulter">
                                    <i class="ri-eye-line"></i>
                                </a>
                                @if ($resource->chemin)
                                    <a href="{{ route('cours.resources.download', [$cours, $resource]) }}"
                                       class="btn btn-sm btn-soft-secondary" title="Télécharger">
                                        <i class="ri-download-line"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Aucun support pour ce cours.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ====== DÉPOSER UN SUPPORT ====== --}}
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Déposer un support</h5>
                </div>
                <div class="card-body">
                    {{-- Même contrôleur que l'admin (CoursResourceController::store) : mêmes types et mêmes limites --}}
                    <form method="POST" action="{{ route('cours.resources.formateur.store', $cours) }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="titre" class="form-label">Titre</label>
                            <input type="text" name="titre" id="titre" class="form-control"
                                   value="{{ old('titre') }}" placeholder="Ex : Support du module 1" required>
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">Type</label>
                            <select name="type" id="type" class="form-select" required>
                                <option value="fichier" @selected(old('type', 'fichier') === 'fichier')>Document</option>
                                <option value="video" @selected(old('type') === 'video')>Vidéo</option>
                                <option value="lien" @selected(old('type') === 'lien')>Lien</option>
                            </select>
                        </div>

                        {{-- Vidéo : fichier importé OU lien externe (le contrôleur exige ce choix) --}}
                        <div class="mb-3 d-none" id="blocSource">
                            <label for="video_source" class="form-label">Source de la vidéo</label>
                            <select name="video_source" id="video_source" class="form-select">
                                <option value="upload" @selected(old('video_source', 'upload') === 'upload')>Importer un fichier</option>
                                <option value="url" @selected(old('video_source') === 'url')>Lien externe (YouTube, Vimeo…)</option>
                            </select>
                        </div>

                        <div class="mb-3" id="blocFichier">
                            <label for="fichier" class="form-label">Fichier</label>
                            <input type="file" name="fichier" id="fichier" class="form-control">
                            <div class="form-text" id="aideFichier">PDF, Word, PowerPoint, Excel, JPG ou PNG</div>
                        </div>

                        <div class="mb-3 d-none" id="blocUrl">
                            <label for="url" class="form-label">URL</label>
                            <input type="url" name="url" id="url" class="form-control"
                                   value="{{ old('url') }}" placeholder="https://...">
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ri-upload-2-line me-1"></i>Déposer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Affiche seulement les champs utiles selon le type choisi (document / vidéo / lien).
        document.addEventListener('DOMContentLoaded', function () {
            const type = document.getElementById('type');
            const source = document.getElementById('video_source');
            const blocSource = document.getElementById('blocSource');
            const blocFichier = document.getElementById('blocFichier');
            const blocUrl = document.getElementById('blocUrl');
            const aide = document.getElementById('aideFichier');

            function maj() {
                const t = type.value;
                const videoUrl = t === 'video' && source.value === 'url';

                blocSource.classList.toggle('d-none', t !== 'video');
                // Un fichier est demandé pour un document ou une vidéo importée ; une URL pour un lien ou une vidéo externe
                blocFichier.classList.toggle('d-none', !(t === 'fichier' || (t === 'video' && !videoUrl)));
                blocUrl.classList.toggle('d-none', !(t === 'lien' || videoUrl));
                aide.textContent = t === 'video'
                    ? 'MP4, WebM, OGG, MOV, AVI ou MKV'
                    : 'PDF, Word, PowerPoint, Excel, JPG ou PNG';
            }

            type.addEventListener('change', maj);
            source.addEventListener('change', maj);
            maj();
        });
    </script>
@endsection