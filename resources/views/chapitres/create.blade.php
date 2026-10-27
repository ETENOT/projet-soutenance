@extends('layouts.master')

@section('title', 'Ajouter un chapitre')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('admin.cours.contenus', $cours) }}">{{ $cours->titre }}</a>
        @endslot
        @slot('title')
            Ajouter un chapitre
        @endslot
    @endcomponent

    <div class="container-fluid pb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h4 class="mb-1">Ajouter un chapitre</h4>
                <p class="text-muted mb-0">Créez un chapitre pour organiser le contenu de ce cours.</p>
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
                        <h5 class="card-title mb-0">Informations du chapitre</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.cours.chapitres.store', $cours) }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label for="titre" class="form-label">Titre du chapitre <span class="text-danger">*</span></label>
                                <input type="text" id="titre" name="titre" value="{{ old('titre') }}"
                                       class="form-control @error('titre') is-invalid @enderror"
                                       placeholder="Ex. Introduction à Laravel" maxlength="255" required>
                                @error('titre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Donnez un titre clair et facilement identifiable.</div>
                            </div>

                            <div class="mb-4">
                                <label for="contenu" class="form-label">Contenu du chapitre</label>
                                <textarea id="contenu" name="contenu" rows="8"
                                          class="form-control @error('contenu') is-invalid @enderror"
                                          placeholder="Présentez le contenu ou les informations générales du chapitre...">{{ old('contenu') }}</textarea>
                                @error('contenu')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Vous pourrez ensuite ajouter des documents, vidéos ou liens.</div>
                            </div>

                            <div class="mb-4">
                                <label for="ordre" class="form-label">Position du chapitre <span class="text-danger">*</span></label>
                                <input type="number" id="ordre" name="ordre" value="{{ old('ordre', $prochainOrdre) }}"
                                       min="1" class="form-control @error('ordre') is-invalid @enderror" required>
                                @error('ordre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Le prochain numéro disponible est <strong>{{ $prochainOrdre }}</strong>.</div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 border-top pt-3">
                                <a href="{{ route('admin.cours.contenus', $cours) }}" class="btn btn-light">Annuler</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-check-line me-1"></i>Créer le chapitre
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">À propos du cours</h5>
                    </div>
                    <div class="card-body">
                        <span class="badge bg-primary-subtle text-primary mb-2">Cours</span>
                        <h6 class="fw-semibold">{{ $cours->titre }}</h6>
                        <p class="text-muted mb-0">
                            Un chapitre regroupe le contenu pédagogique et les ressources associées.
                        </p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Types de ressources</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-3 mb-3">
                            <i class="ri-file-text-line text-primary fs-4"></i>
                            <div><strong>Documents</strong><div class="text-muted small">PDF, Word, Excel et images.</div></div>
                        </div>
                        <div class="d-flex gap-3 mb-3">
                            <i class="ri-video-line text-success fs-4"></i>
                            <div><strong>Vidéos</strong><div class="text-muted small">Fichiers vidéo ou liens externes.</div></div>
                        </div>
                        <div class="d-flex gap-3">
                            <i class="ri-links-line text-info fs-4"></i>
                            <div><strong>Liens</strong><div class="text-muted small">Documentation et sites utiles.</div></div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted small mb-2">APERÇU</div>
                        <h6 class="fw-bold">{{ $cours->titre }}</h6>
                        <div class="d-flex align-items-center gap-3 p-3 bg-light rounded">
                            <span class="avatar-sm">
                                <span class="avatar-title rounded bg-primary text-white">{{ $prochainOrdre }}</span>
                            </span>
                            <div>
                                <small class="text-muted">Nouveau chapitre</small>
                                <div class="fw-semibold">{{ old('titre', 'Titre du chapitre') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
