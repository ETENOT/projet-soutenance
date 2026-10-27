@extends('layouts.master')

@section('title')
    Créer un cours
@endsection

@section('content')

@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('admin.cours.index') }}">Gestion des cours</a>
    @endslot
    @slot('title')
        Créer un cours
    @endslot
@endcomponent

    {{-- ==========================================
         FORMULAIRE
    =========================================== --}}
    <form action="{{ route('admin.cours.store') }}" method="POST" enctype="multipart/form-data">

        @csrf

        <div class="row">

            {{-- ==========================================
                 COLONNE PRINCIPALE
            =========================================== --}}
            <div class="col-xl-8">

                {{-- INFORMATIONS GÉNÉRALES --}}
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
                                    Présentez les informations principales du cours.
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
                                    value="{{ old('titre') }}"
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
                                        <option value="{{ $categorie->id }}" @selected(old('categorie_id') == $categorie->id)>
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
                        <div class="mb-3">

                            <label for="description" class="form-label">
                                Description du cours
                                <span class="text-danger">*</span>
                            </label>

                            <textarea
                                name="description"
                                id="description"
                                rows="6"
                                class="form-control @error('description') is-invalid @enderror"
                                placeholder="Présentez brièvement ce cours, ses objectifs et le public auquel il s'adresse..."
                            >{{ old('description') }}</textarea>

                            @error('description')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                            <div class="form-text">
                                Cette description pourra être affichée sur le catalogue des cours.
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
                                    Définissez les tarifs selon le type de client.
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
                                            value="{{ old('prix_particulier') }}"
                                            placeholder="90000"
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
                                            value="{{ old('prix_entreprise') }}"
                                            placeholder="150000"
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

            </div>


            {{-- ==========================================
                 COLONNE LATÉRALE
            =========================================== --}}
            <div class="col-xl-4">

                {{-- IMAGE DE LA FORMATION --}}
                @include('cours.partials.champ-image')

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
                                Nouveau cours
                            </h5>

                            <p class="text-muted mb-0">
                                Les informations saisies ici serviront à créer le cours.
                            </p>

                        </div>


                        <div class="border-top pt-3">

                            <div class="d-flex justify-content-between mb-3">

                                <span class="text-muted">
                                    Statut
                                </span>

                                <span class="badge bg-warning-subtle text-warning">
                                    Brouillon
                                </span>

                            </div>


                            <div class="d-flex justify-content-between mb-3">

                                <span class="text-muted">
                                    Contenu
                                </span>

                                <span class="text-muted">
                                    À définir
                                </span>

                            </div>


                            <div class="d-flex justify-content-between">

                                <span class="text-muted">
                                    Accès
                                </span>

                                <span class="text-muted">
                                    Particuliers / Entreprises
                                </span>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- CONSEILS --}}
                <div class="card">

                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ri-lightbulb-line me-1 text-warning"></i>
                            Conseils
                        </h5>
                    </div>

                    <div class="card-body">

                        <div class="d-flex mb-3">

                            <div class="flex-shrink-0">
                                <i class="ri-check-line text-success fs-18"></i>
                            </div>

                            <div class="ms-2">
                                <p class="text-muted mb-0">
                                    Utilisez un titre court et explicite.
                                </p>
                            </div>

                        </div>


                        <div class="d-flex mb-3">

                            <div class="flex-shrink-0">
                                <i class="ri-check-line text-success fs-18"></i>
                            </div>

                            <div class="ms-2">
                                <p class="text-muted mb-0">
                                    Présentez clairement les objectifs du cours.
                                </p>
                            </div>

                        </div>


                        <div class="d-flex">

                            <div class="flex-shrink-0">
                                <i class="ri-check-line text-success fs-18"></i>
                            </div>

                            <div class="ms-2">
                                <p class="text-muted mb-0">
                                    Les chapitres détaillés se créent après l'enregistrement du cours.
                                </p>
                            </div>

                        </div>

                    </div>

                </div>


                {{-- ACTIONS --}}
                <div class="card">

                    <div class="card-body">

                        <button
                            type="submit"
                            class="btn btn-primary w-100 mb-2"
                        >
                            <i class="ri-add-line me-1"></i>
                            Créer le cours
                        </button>

                        <a
                            href="{{ route('admin.cours.index') }}"
                            class="btn btn-light w-100"
                        >
                            <i class="ri-arrow-left-line me-1"></i>
                            Annuler
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </form>

@endsection