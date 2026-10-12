@extends('layouts.master')

@section('title', $cours->titre)

@section('content')

{{-- ===== Fil d'Ariane ===== --}}
@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('admin.cours.index') }}">Cours</a>
    @endslot
    @slot('title')
        {{ $cours->titre }}
    @endslot
@endcomponent

<div class="container-fluid pb-4">

    {{-- ===== En-tête ===== --}}
    <div class="bg-primary bg-gradient text-white rounded-4 p-4 p-md-5 mb-4">
        <div class="row align-items-center g-3">

            <div class="col-lg-8">
                <span class="badge rounded-pill bg-white bg-opacity-25 text-white px-3 py-2 mb-3">
                    <i class="ri-settings-3-line me-1"></i> Administration · Détail du cours
                </span>
                <h3 class="fw-bold text-white mb-2">{{ $cours->titre }}</h3>
                <p class="text-white opacity-75 mb-0">
                    Consultez les informations et accédez directement aux différents éléments de cette formation.
                </p>
            </div>

            <div class="col-lg-4">
                <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                    <a href="{{ route('admin.cours.edit', $cours) }}" class="btn btn-light">
                        <i class="ri-edit-line me-1"></i> Modifier
                    </a>
                    <a href="{{ route('admin.cours.contenus', $cours) }}" class="btn btn-danger">
                        <i class="ri-folder-open-line me-1"></i> Gérer les contenus
                    </a>
                </div>
            </div>

        </div>
    </div>

    {{-- ===== Cartes d'accès rapide ===== --}}
    <div class="row g-3 mb-4">

        {{-- Chapitres --}}
        <div class="col-md-6 col-xl-3">
            <a href="{{ route('admin.cours.contenus', $cours) }}"
               class="card card-animate border shadow-sm h-100 mb-0 text-reset text-decoration-none">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="avatar-md">
                            <span class="avatar-title rounded-3 bg-primary-subtle text-primary fs-2">
                                <i class="ri-book-open-line"></i>
                            </span>
                        </span>
                        <i class="ri-arrow-right-line text-muted fs-4"></i>
                    </div>
                    <h5 class="fw-bold mb-1">Chapitres</h5>
                    <p class="text-muted small mb-3">Gérez le programme détaillé de cette formation.</p>
                    <div class="fw-semibold">
                        <i class="ri-folder-2-line text-danger me-1"></i>
                        {{ $cours->chapitres->count() }} chapitre(s)
                    </div>
                </div>
            </a>
        </div>

        {{-- Ressources --}}
        <div class="col-md-6 col-xl-3">
            <a href="{{ route('admin.cours.contenus', $cours) }}"
               class="card card-animate border shadow-sm h-100 mb-0 text-reset text-decoration-none">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="avatar-md">
                            <span class="avatar-title rounded-3 bg-danger-subtle text-danger fs-2">
                                <i class="ri-folder-open-line"></i>
                            </span>
                        </span>
                        <i class="ri-arrow-right-line text-muted fs-4"></i>
                    </div>
                    <h5 class="fw-bold mb-1">Ressources</h5>
                    <p class="text-muted small mb-3">Documents, vidéos et liens mis à disposition des apprenants.</p>
                    <div class="fw-semibold">
                        <i class="ri-attachment-2 text-danger me-1"></i>
                        {{ $cours->resources->count() }} ressource(s)
                    </div>
                </div>
            </a>
        </div>

        {{-- Classes --}}
        <div class="col-md-6 col-xl-3">
            <a href="{{ route('admin.cours.classes.index', $cours) }}"
               class="card card-animate border shadow-sm h-100 mb-0 text-reset text-decoration-none">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="avatar-md">
                            <span class="avatar-title rounded-3 bg-success-subtle text-success fs-2">
                                <i class="ri-group-line"></i>
                            </span>
                        </span>
                        <i class="ri-arrow-right-line text-muted fs-4"></i>
                    </div>
                    <h5 class="fw-bold mb-1">Classes</h5>
                    <p class="text-muted small mb-3">Consultez et gérez les sessions associées à cette formation.</p>
                    <div class="fw-semibold">
                        <i class="ri-calendar-line text-danger me-1"></i>
                        {{ $cours->classes->count() }} classe(s)
                    </div>
                </div>
            </a>
        </div>

        {{-- Quiz --}}
        <div class="col-md-6 col-xl-3">
            <a href="{{ route('admin.cours.contenus', $cours) }}"
               class="card card-animate border shadow-sm h-100 mb-0 text-reset text-decoration-none">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="avatar-md">
                            <span class="avatar-title rounded-3 bg-warning-subtle text-warning fs-2">
                                <i class="ri-questionnaire-line"></i>
                            </span>
                        </span>
                        <i class="ri-arrow-right-line text-muted fs-4"></i>
                    </div>
                    <h5 class="fw-bold mb-1">Quiz</h5>
                    <p class="text-muted small mb-3">Consultez et gérez les évaluations de cette formation.</p>
                    <div class="fw-semibold">
                        <i class="ri-question-line text-danger me-1"></i>
                        {{ $cours->quizzes->count() }} quiz
                    </div>
                </div>
            </a>
        </div>

    </div>

    {{-- ===== Informations principales ===== --}}
    <div class="row g-4 mb-4">

        {{-- Informations du cours --}}
        <div class="col-lg-8">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-1">
                        <i class="ri-information-line text-danger me-2"></i>Informations du cours
                    </h5>
                    <p class="text-muted small mb-4">Informations générales de la formation.</p>

                    <table class="table table-borderless align-middle mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted"><i class="ri-text text-danger me-2"></i>Titre</td>
                                <td class="text-end fw-bold">{{ $cours->titre }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted"><i class="ri-bookmark-line text-danger me-2"></i>Catégorie</td>
                                <td class="text-end fw-bold">{{ $cours->categorie }}</td>
                            </tr>
                            @if($cours->niveau)
                                <tr>
                                    <td class="text-muted"><i class="ri-bar-chart-line text-danger me-2"></i>Niveau</td>
                                    <td class="text-end fw-bold">{{ $cours->niveau }}</td>
                                </tr>
                            @endif
                            @if($cours->duree)
                                <tr>
                                    <td class="text-muted"><i class="ri-time-line text-danger me-2"></i>Durée</td>
                                    <td class="text-end fw-bold">{{ $cours->duree }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>

                    @if($cours->description)
                        <div class="mt-4">
                            <div class="text-muted mb-2">
                                <i class="ri-file-text-line text-danger me-2"></i>Description
                            </div>
                            <div class="bg-light rounded-3 p-3">
                                {!! nl2br(e($cours->description)) !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Tarification --}}
        <div class="col-lg-4">
            <div class="card border shadow-sm h-100 mb-0">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-1">
                        <i class="ri-price-tag-3-line text-danger me-2"></i>Tarification
                    </h5>
                    <p class="text-muted small mb-4">Tarifs appliqués selon le profil.</p>

                    <div class="d-flex justify-content-between align-items-center bg-primary-subtle rounded-3 p-3 mb-3">
                        <span class="fw-semibold text-primary">
                            <i class="ri-user-line me-1"></i>Particulier
                        </span>
                        <span class="fw-bold">
                            {{ number_format($cours->prix_particulier, 0, ',', ' ') }} FCFA
                        </span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center bg-danger-subtle rounded-3 p-3">
                        <span class="fw-semibold text-danger">
                            <i class="ri-building-line me-1"></i>Entreprise
                        </span>
                        <span class="fw-bold">
                            {{ number_format($cours->prix_entreprise, 0, ',', ' ') }} FCFA
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ===== Actions finales ===== --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <a href="{{ route('admin.cours.index') }}" class="btn btn-light">
            <i class="ri-arrow-left-line me-1"></i> Retour aux formations
        </a>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.cours.edit', $cours) }}" class="btn btn-outline-primary">
                <i class="ri-edit-line me-1"></i> Modifier la formation
            </a>
            <a href="{{ route('admin.cours.contenus', $cours) }}" class="btn btn-danger">
                <i class="ri-folder-open-line me-1"></i> Gérer les contenus
            </a>
        </div>
    </div>

</div>

@endsection