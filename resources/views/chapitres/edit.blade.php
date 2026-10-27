@extends('layouts.master')

@section('title', 'Modifier le chapitre')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('admin.cours.contenus', $cours) }}">{{ $cours->titre }}</a>
        @endslot
        @slot('title')
            Modifier le chapitre
        @endslot
    @endcomponent

    <div class="container-fluid pb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h4 class="mb-1">Modifier le chapitre</h4>
                <p class="text-muted mb-0">Modifiez le titre, le contenu et la position du chapitre.</p>
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
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Informations du chapitre</h5>
                        <span class="badge bg-primary-subtle text-primary">Chapitre {{ $chapitre->ordre }}</span>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.cours.chapitres.update', [$cours, $chapitre]) }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-4">
                                <label for="titre" class="form-label">Titre du chapitre <span class="text-danger">*</span></label>
                                <input type="text" id="titre" name="titre" value="{{ old('titre', $chapitre->titre) }}"
                                       class="form-control @error('titre') is-invalid @enderror"
                                       placeholder="Exemple : Introduction à Laravel" maxlength="255" required>
                                @error('titre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="contenu" class="form-label">Contenu du chapitre</label>
                                <textarea id="contenu" name="contenu" rows="11"
                                          class="form-control @error('contenu') is-invalid @enderror"
                                          placeholder="Saisissez le contenu pédagogique du chapitre...">{{ old('contenu', $chapitre->contenu) }}</textarea>
                                @error('contenu')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Les documents, vidéos et liens sont gérés depuis les ressources du cours.</div>
                            </div>

                            <div class="mb-4">
                                <label for="ordre" class="form-label">Ordre du chapitre <span class="text-danger">*</span></label>
                                <input type="number" id="ordre" name="ordre" min="1"
                                       value="{{ old('ordre', $chapitre->ordre) }}"
                                       class="form-control @error('ordre') is-invalid @enderror" required>
                                @error('ordre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Détermine la position du chapitre dans le cours.</div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 border-top pt-3">
                                <a href="{{ route('admin.cours.contenus', $cours) }}" class="btn btn-light">Annuler</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-check-line me-1"></i>Enregistrer les modifications
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">À propos du chapitre</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            Le contenu textuel présente les notions principales directement aux apprenants.
                        </p>
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <i class="ri-file-text-line text-primary fs-4"></i>
                            <span>Contenu pédagogique</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <i class="ri-paperclip text-success fs-4"></i>
                            <span>Documents et fichiers</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="ri-links-line text-info fs-4"></i>
                            <span>Liens externes</span>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Cours associé</h5>
                    </div>
                    <div class="card-body">
                        <span class="badge bg-primary-subtle text-primary mb-2">Cours</span>
                        <div class="fw-semibold">{{ $cours->titre }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
