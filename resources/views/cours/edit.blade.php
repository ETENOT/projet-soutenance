@extends('layouts.master')

@section('title')
Modifier le cours
@endsection

@section('content')

@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('admin.cours.index') }}">Gestion des cours</a>
    @endslot
    @slot('title')
        Modifier le cours
    @endslot
@endcomponent

{{-- ==========================================
     FORMULAIRE PRINCIPAL
=========================================== --}}
<form
    action="{{ route('admin.cours.update', $cours) }}"
    method="POST"
    enctype="multipart/form-data"
>

    @csrf
    @method('PUT')


    <div class="row">

        {{-- ==========================================
             COLONNE PRINCIPALE
        =========================================== --}}
        <div class="col-xl-8">

            {{-- ==========================================
                 INFORMATIONS GÉNÉRALES
            =========================================== --}}
            <div class="card">

                <div class="card-header">

                    <div class="d-flex align-items-center">

                        <div class="flex-shrink-0">
                            <div
                                class="avatar-sm rounded bg-primary-subtle d-flex align-items-center justify-content-center"
                            >
                                <i class="ri-book-open-line fs-20 text-primary"></i>
                            </div>
                        </div>

                        <div class="ms-3">
                            <h5 class="card-title mb-1">
                                Informations générales
                            </h5>

                            <p class="text-muted mb-0">
                                Informations principales du cours.
                            </p>
                        </div>

                    </div>

                </div>


                <div class="card-body">

                    {{-- TITRE + CATÉGORIE --}}
                    <div class="row">

                        <div class="col-md-8 mb-3">

                            <label for="titre" class="form-label">
                                Nom du cours
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="text"
                                name="titre"
                                id="titre"
                                class="form-control @error('titre') is-invalid @enderror"
                                value="{{ old('titre', $cours->titre) }}"
                                placeholder="Ex. Microsoft Excel"
                            >

                            @error('titre')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        <div class="col-md-4 mb-3">

                            <label for="categorie" class="form-label">
                                Catégorie
                                <span class="text-danger">*</span>
                            </label>

                            <select
                                name="categorie_id"
                                id="categorie"
                                class="form-select @error('categorie_id') is-invalid @enderror"
                                required
                            >
                                <option value="">Choisir une catégorie</option>
                                @foreach ($categories as $categorie)
                                    <option value="{{ $categorie->id }}"
                                        @selected(old('categorie_id', $cours->categorie_id) == $categorie->id)>
                                        {{ $categorie->nom }}
                                    </option>
                                @endforeach
                            </select>

                            @error('categorie_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                    </div>


                    {{-- DESCRIPTION --}}
                    <div class="mb-0">

                        <label for="description" class="form-label">
                            Description du cours
                            <span class="text-danger">*</span>
                        </label>

                        <textarea
                            name="description"
                            id="description"
                            rows="6"
                            class="form-control @error('description') is-invalid @enderror"
                            placeholder="Présentez brièvement ce cours..."
                        >{{ old('description', $cours->description) }}</textarea>

                        @error('description')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="form-text">
                            Cette description peut être affichée dans le catalogue des cours.
                        </div>

                    </div>

                </div>

            </div>


            {{-- ==========================================
                 TARIFICATION
            =========================================== --}}
            <div class="card">

                <div class="card-header">

                    <div class="d-flex align-items-center">

                        <div class="flex-shrink-0">
                            <div
                                class="avatar-sm rounded bg-warning-subtle d-flex align-items-center justify-content-center"
                            >
                                <i class="ri-money-dollar-circle-line fs-20 text-warning"></i>
                            </div>
                        </div>

                        <div class="ms-3">
                            <h5 class="card-title mb-1">
                                Tarification
                            </h5>

                            <p class="text-muted mb-0">
                                Tarifs appliqués selon le type de client.
                            </p>
                        </div>

                    </div>

                </div>


                <div class="card-body">

                    <div class="row">

                        {{-- PARTICULIER --}}
                        <div class="col-md-6">

                            <div class="border rounded p-3 h-100">

                                <div class="d-flex align-items-center mb-3">

                                    <div
                                        class="avatar-sm rounded bg-primary-subtle d-flex align-items-center justify-content-center"
                                    >
                                        <i class="ri-user-line text-primary"></i>
                                    </div>

                                    <div class="ms-3">
                                        <h6 class="mb-1">
                                            Tarif particulier
                                        </h6>

                                        <small class="text-muted">
                                            Prix destiné aux particuliers
                                        </small>
                                    </div>

                                </div>

                                <label
                                    for="prix_particulier"
                                    class="form-label"
                                >
                                    Prix (FCFA)
                                </label>

                                <div class="input-group">

                                    <input
                                        type="number"
                                        step="0.01"
                                        name="prix_particulier"
                                        id="prix_particulier"
                                        class="form-control @error('prix_particulier') is-invalid @enderror"
                                        value="{{ old('prix_particulier', $cours->prix_particulier) }}"
                                    >

                                    <span class="input-group-text">
                                        FCFA
                                    </span>

                                </div>

                                @error('prix_particulier')
                                    <div class="text-danger small mt-1">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>


                        {{-- ENTREPRISE --}}
                        <div class="col-md-6">

                            <div class="border rounded p-3 h-100">

                                <div class="d-flex align-items-center mb-3">

                                    <div
                                        class="avatar-sm rounded bg-success-subtle d-flex align-items-center justify-content-center"
                                    >
                                        <i class="ri-building-line text-success"></i>
                                    </div>

                                    <div class="ms-3">
                                        <h6 class="mb-1">
                                            Tarif entreprise
                                        </h6>

                                        <small class="text-muted">
                                            Prix destiné aux entreprises
                                        </small>
                                    </div>

                                </div>

                                <label
                                    for="prix_entreprise"
                                    class="form-label"
                                >
                                    Prix (FCFA)
                                </label>

                                <div class="input-group">

                                    <input
                                        type="number"
                                        step="0.01"
                                        name="prix_entreprise"
                                        id="prix_entreprise"
                                        class="form-control @error('prix_entreprise') is-invalid @enderror"
                                        value="{{ old('prix_entreprise', $cours->prix_entreprise) }}"
                                    >

                                    <span class="input-group-text">
                                        FCFA
                                    </span>

                                </div>

                                @error('prix_entreprise')
                                    <div class="text-danger small mt-1">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- ==========================================
                 ACTIONS
            =========================================== --}}
            <div class="card">

                <div class="card-body">

                    <div class="d-flex justify-content-end gap-2">

                        <a
                            href="{{ route('admin.cours.index') }}"
                            class="btn btn-light"
                        >
                            <i class="ri-arrow-left-line me-1"></i>
                            Annuler
                        </a>

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            <i class="ri-save-3-line me-1"></i>
                            Enregistrer les modifications
                        </button>

                    </div>

                </div>

            </div>

        </div>


        {{-- ==========================================
             COLONNE LATÉRALE
        =========================================== --}}
        <div class="col-xl-4">

            {{-- IMAGE DE LA FORMATION --}}
            @include('cours.partials.champ-image')

            {{-- CONTENU PÉDAGOGIQUE --}}
            <div class="card">

                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-folder-open-line me-1 text-primary"></i>
                        Contenu pédagogique
                    </h5>
                </div>

                <div class="card-body">

                    <p class="text-muted">
                        Gérez les chapitres et les ressources pédagogiques
                        associés à ce cours.
                    </p>

                    <a
                        href="{{ route('admin.cours.contenus', $cours) }}"
                        class="btn btn-outline-primary w-100"
                    >
                        <i class="ri-book-open-line me-1"></i>
                        Gérer les chapitres et contenus
                    </a>

                </div>

            </div>


            {{-- RÉSUMÉ --}}
            <div class="card">

                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-file-info-line me-1 text-primary"></i>
                        Résumé
                    </h5>
                </div>

                <div class="card-body">

                    <div class="text-center mb-4">

                        <div
                            class="avatar-lg rounded-circle bg-primary-subtle d-inline-flex align-items-center justify-content-center"
                        >
                            <i class="ri-book-open-line text-primary fs-28"></i>
                        </div>

                        <h5 class="mt-3 mb-1">
                            {{ $cours->titre }}
                        </h5>

                        <p class="text-muted mb-0">
                            {{ $cours->categorie?->nom }}
                        </p>

                    </div>


                    <div class="border-top pt-3">

                        <div class="d-flex justify-content-between mb-3">

                            <span class="text-muted">
                                Chapitres
                            </span>

                            <span class="fw-semibold">
                                {{ $cours->chapitres->count() }}
                            </span>

                        </div>


                        <div class="d-flex justify-content-between mb-3">

                            <span class="text-muted">
                                Ressources
                            </span>

                            <span class="fw-semibold">
                                {{ $cours->resources->count() }}
                            </span>

                        </div>


                        <div class="d-flex justify-content-between">

                            <span class="text-muted">
                                Catégorie
                            </span>

                            <span class="text-muted">
                                {{ $cours->categorie?->nom }}
                            </span>

                        </div>

                    </div>

                </div>

            </div>


            {{-- TARIFS RAPIDES --}}
            <div class="card">

                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-money-dollar-circle-line me-1 text-warning"></i>
                        Tarifs actuels
                    </h5>
                </div>

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <span class="text-muted">
                            Particulier
                        </span>

                        <strong>
                            {{ number_format($cours->prix_particulier, 0, ',', ' ') }} FCFA
                        </strong>

                    </div>


                    <div class="d-flex justify-content-between align-items-center">

                        <span class="text-muted">
                            Entreprise
                        </span>

                        <strong>
                            {{ number_format($cours->prix_entreprise, 0, ',', ' ') }} FCFA
                        </strong>

                    </div>

                </div>

            </div>


            {{-- INFORMATION CONTENU --}}
            <div class="card">

                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-information-line me-1 text-info"></i>
                        Organisation du contenu
                    </h5>
                </div>

                <div class="card-body">

                    <div class="d-flex align-items-start mb-3">

                        <i class="ri-book-open-line text-primary fs-18 me-2"></i>

                        <div>
                            <h6 class="mb-1">
                                Chapitres
                            </h6>

                            <p class="text-muted small mb-0">
                                Les chapitres structurent le contenu pédagogique du cours.
                            </p>
                        </div>

                    </div>


                    <div class="d-flex align-items-start">

                        <i class="ri-folder-video-line text-info fs-18 me-2"></i>

                        <div>
                            <h6 class="mb-1">
                                Ressources
                            </h6>

                            <p class="text-muted small mb-0">
                                Les fichiers, vidéos et liens sont associés directement à un chapitre.
                            </p>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</form>

@endsection
