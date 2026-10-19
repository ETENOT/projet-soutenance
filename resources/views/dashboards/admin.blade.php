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
@endsection

@section('script')
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection