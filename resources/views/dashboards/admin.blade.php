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

    {{-- Compteurs : volume à gérer sur la plateforme (4 cartes de même hauteur) --}}
    <div class="row g-3">
        @include('dashboards.partials.carte-stat', [
            'largeur' => 'col-xl-3 col-sm-6',
            'titre' => 'Utilisateurs',
            'icone' => 'users',
            'couleur' => 'info',
            'lien' => route('admin.users.index'),
            'valeur' => $totalUtilisateurs,
        ])

        @include('dashboards.partials.carte-stat', [
            'largeur' => 'col-xl-3 col-sm-6',
            'titre' => 'Cours',
            'icone' => 'book-open',
            'couleur' => 'success',
            'lien' => route('admin.cours.index'),
            'valeur' => $totalCours,
        ])

        @include('dashboards.partials.carte-stat', [
            'largeur' => 'col-xl-3 col-sm-6',
            'titre' => 'Classes',
            'icone' => 'calendar',
            'couleur' => 'warning',
            'lien' => route('admin.classes.index'),
            'valeur' => $totalClasses,
        ])

        @include('dashboards.partials.carte-stat', [
            'largeur' => 'col-xl-3 col-sm-6',
            'titre' => 'Quiz',
            'icone' => 'help-circle',
            'couleur' => 'danger',
            'valeur' => $totalQuiz,
        ])
    </div>

    {{-- Accès rapide à la gestion : 4 tuiles de même taille --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Gestion de la plateforme</h4>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-xl-3 col-sm-6">
                            <a href="{{ route('admin.cours.index') }}" class="d-block h-100 text-decoration-none">
                                <div class="border rounded p-3 text-center h-100">
                                    <i data-feather="book-open" class="text-success mb-2"></i>
                                    <p class="mb-0 fw-medium text-body">Gérer les cours</p>
                                </div>
                            </a>
                        </div>

                        <div class="col-xl-3 col-sm-6">
                            <a href="{{ route('admin.classes.index') }}" class="d-block h-100 text-decoration-none">
                                <div class="border rounded p-3 text-center h-100">
                                    <i data-feather="calendar" class="text-warning mb-2"></i>
                                    <p class="mb-0 fw-medium text-body">Gérer les classes</p>
                                </div>
                            </a>
                        </div>

                        <div class="col-xl-3 col-sm-6">
                            <a href="{{ route('admin.users.index') }}" class="d-block h-100 text-decoration-none">
                                <div class="border rounded p-3 text-center h-100">
                                    <i data-feather="users" class="text-primary mb-2"></i>
                                    <p class="mb-0 fw-medium text-body">Gérer les utilisateurs</p>
                                </div>
                            </a>
                        </div>

                        <div class="col-xl-3 col-sm-6">
                            <a href="#" class="d-block h-100 text-decoration-none">
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
@endsection

@section('script')
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection