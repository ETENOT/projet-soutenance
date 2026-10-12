@extends('layouts.master')

@section('title')
    Tableau de bord formateur
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Dashboards
        @endslot
        @slot('title')
            Espace formateur
        @endslot
    @endcomponent

    {{-- Les 4 indicateurs demandés : classes, classes en cours, apprenants payés, prochaine classe --}}
    <div class="row">

        {{-- Nombre de classes --}}
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <a href="{{ route('classes.formateur.index') }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="fw-medium text-muted mb-0">Mes classes</p>
                                <h2 class="mt-4 ff-secondary fw-semibold">
                                    <span class="counter-value" data-target="{{ $nombreClasses }}">0</span>
                                </h2>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-info-subtle rounded-circle fs-2">
                                    <i data-feather="users" class="text-info"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        {{-- Classes en cours --}}
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="fw-medium text-muted mb-0">Classes en cours</p>
                            <h2 class="mt-4 ff-secondary fw-semibold">
                                <span class="counter-value" data-target="{{ $classesEnCours }}">0</span>
                            </h2>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-success-subtle rounded-circle fs-2">
                                <i data-feather="play-circle" class="text-success"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Apprenants payés --}}
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="fw-medium text-muted mb-0">Stagaires</p>
                            <h2 class="mt-4 ff-secondary fw-semibold">
                                <span class="counter-value" data-target="{{ $apprenantsPayes }}">0</span>
                            </h2>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-warning-subtle rounded-circle fs-2">
                                <i data-feather="check-circle" class="text-warning"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Prochaine classe --}}
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="me-2">
                            <p class="fw-medium text-muted mb-0">Prochaine classe</p>
                            @if ($prochaineClasse)
                                <h5 class="mt-4 mb-1">
                                    <a href="{{ route('classes.formateur.show', $prochaineClasse) }}">
                                        {{ $prochaineClasse->date_debut->format('d/m/Y') }}
                                    </a>
                                </h5>
                                <p class="text-muted mb-0 fs-12">{{ $prochaineClasse->nom }}</p>
                            @else
                                <h5 class="mt-4 text-muted">Aucune classe à venir</h5>
                            @endif
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-primary-subtle rounded-circle fs-2">
                                <i data-feather="calendar" class="text-primary"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection