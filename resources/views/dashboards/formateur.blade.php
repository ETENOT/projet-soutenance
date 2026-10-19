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

    {{-- 4 cartes : mes classes, classes en cours, stagiaires, prochaine classe --}}
    <div class="row g-3">
        @include('dashboards.partials.carte-stat', [
            'largeur' => 'col-xl-3 col-md-6',
            'titre' => 'Mes classes',
            'icone' => 'users',
            'couleur' => 'info',
            'lien' => route('classes.formateur.index'),
            'valeur' => $nombreClasses,
        ])

        @include('dashboards.partials.carte-stat', [
            'largeur' => 'col-xl-3 col-md-6',
            'titre' => 'Classes en cours',
            'icone' => 'play-circle',
            'couleur' => 'success',
            'valeur' => $classesEnCours,
        ])

        @include('dashboards.partials.carte-stat', [
            'largeur' => 'col-xl-3 col-md-6',
            'titre' => 'Stagiaires',
            'icone' => 'check-circle',
            'couleur' => 'warning',
            'valeur' => $apprenantsPayes,
        ])

        {{-- Prochaine classe : toute la carte mène à la classe quand il y en a une --}}
        @include('dashboards.partials.carte-stat', [
            'largeur' => 'col-xl-3 col-md-6',
            'titre' => 'Prochaine classe',
            'icone' => 'calendar',
            'couleur' => 'primary',
            'lien' => $prochaineClasse ? route('classes.formateur.show', $prochaineClasse) : null,
            'texte' => $prochaineClasse ? $prochaineClasse->date_debut->format('d/m/Y') : 'Aucune classe à venir',
            'texteClasse' => $prochaineClasse ? '' : 'text-muted',
            'detail' => $prochaineClasse?->nom,
        ])
    </div>

    {{-- Calendrier : mes classes à venir et en cours --}}
    <div class="row">
        <div class="col-12">
            @include('partials.calendrier-classes', [
                'evenements' => $evenementsCalendrier,
                'titre' => 'Calendrier de mes classes',
                'legende' => [['nv-cal-a-venir', 'À venir'], ['nv-cal-en-cours', 'En cours']],
            ])
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection