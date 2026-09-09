@extends('layouts.master')

@section('title')
    Tableau de bord administrateur
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Dashboards
        @endslot
        @slot('title')
            Administration
        @endslot
    @endcomponent

    {{-- Compteurs bruts : contexte du volume à gérer sur la plateforme --}}
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="fw-medium text-muted mb-0">Utilisateurs</p>
                            <h2 class="mt-4 ff-secondary fw-semibold">
                                <span class="counter-value" data-target="{{ $totalUtilisateurs }}">0</span>
                            </h2>
                        </div>
                        <div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-info-subtle rounded-circle fs-2">
                                    <i data-feather="users" class="text-info"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="fw-medium text-muted mb-0">Cours</p>
                            <h2 class="mt-4 ff-secondary fw-semibold">
                                <span class="counter-value" data-target="{{ $totalCours }}">0</span>
                            </h2>
                        </div>
                        <div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-success-subtle rounded-circle fs-2">
                                    <i data-feather="book-open" class="text-success"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="fw-medium text-muted mb-0">Classes</p>
                            <h2 class="mt-4 ff-secondary fw-semibold">
                                <span class="counter-value" data-target="{{ $totalClasses }}">0</span>
                            </h2>
                        </div>
                        <div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-warning-subtle rounded-circle fs-2">
                                    <i data-feather="calendar" class="text-warning"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="fw-medium text-muted mb-0">Quiz</p>
                            <h2 class="mt-4 ff-secondary fw-semibold">
                                <span class="counter-value" data-target="{{ $totalQuiz }}">0</span>
                            </h2>
                        </div>
                        <div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-danger-subtle rounded-circle fs-2">
                                    <i data-feather="help-circle" class="text-danger"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

    {{-- Accès rapide aux actions de gestion (cas d'utilisation admin du diagramme) --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Gestion de la plateforme</h4>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3 col-sm-6">
                            <a href="#" class="text-decoration-none">
                                <div class="border rounded p-3 text-center h-100">
                                    <i data-feather="book-open" class="text-success mb-2"></i>
                                    <p class="mb-1 fw-medium text-body">Gérer les cours</p>
                                    <span class="badge bg-secondary-subtle text-secondary">Bientôt disponible</span>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <a href="#" class="text-decoration-none">
                                <div class="border rounded p-3 text-center h-100">
                                    <i data-feather="calendar" class="text-warning mb-2"></i>
                                    <p class="mb-1 fw-medium text-body">Gérer les classes</p>
                                    <span class="badge bg-secondary-subtle text-secondary">Bientôt disponible</span>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <a href="#" class="text-decoration-none">
                                <div class="border rounded p-3 text-center h-100">
                                    <i data-feather="users" class="text-info mb-2"></i>
                                    <p class="mb-1 fw-medium text-body">Gérer les utilisateurs</p>
                                    <span class="badge bg-secondary-subtle text-secondary">Bientôt disponible</span>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <a href="#" class="text-decoration-none">
                                <div class="border rounded p-3 text-center h-100">
                                    <i data-feather="help-circle" class="text-danger mb-2"></i>
                                    <p class="mb-1 fw-medium text-body">Gérer les quiz</p>
                                    <span class="badge bg-secondary-subtle text-secondary">Bientôt disponible</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->
@endsection

@section('script')
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection