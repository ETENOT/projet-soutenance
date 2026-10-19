@extends('layouts.master')

@section('title')
    Mon espace
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/jsvectormap/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')

    {{-- Fil d'Ariane --}}
    @component('components.breadcrumb')
        @slot('li_1')
            Dashboards
        @endslot
        @slot('title')
            Espace particulier
        @endslot
    @endcomponent

    {{-- 6 cartes : 3 par ligne, toutes de la même hauteur --}}
    <div class="row g-3">
        @include('dashboards.partials.carte-stat', [
            'titre' => 'Sessions inscrites',
            'icone' => 'calendar',
            'couleur' => 'info',
            'lien' => route('cours.mes'),
            'valeur' => $sessionsPayees,
        ])

        @include('dashboards.partials.carte-stat', [
            'titre' => 'Sessions en cours',
            'icone' => 'book-open',
            'couleur' => 'primary',
            'lien' => route('cours.mes'),
            'valeur' => $SessionEnCours,
        ])

        @include('dashboards.partials.carte-stat', [
            'titre' => 'Prochaine session',
            'icone' => 'clock',
            'couleur' => 'warning',
            'texte' => $prochaineClasse,
        ])

        @include('dashboards.partials.carte-stat', [
            'titre' => 'Notifications non lues',
            'icone' => 'bell',
            'couleur' => 'danger',
            'lien' => route('notifications.index'),
            'valeur' => $notificationsNonLues,
        ])

        @include('dashboards.partials.carte-stat', [
            'titre' => 'Statut paiement',
            'icone' => 'credit-card',
            'couleur' => 'secondary',
            'lien' => route('paiements.statut_paiement'),
            'texte' => $sessionsPayees . ' payée(s)',
            'texteClasse' => 'text-success',
            'detail' => $sessionsImpayees > 0 ? $sessionsImpayees . ' impayée(s)' : 'Aucun impayé',
            'detailClasse' => $sessionsImpayees > 0 ? 'text-danger' : 'text-muted',
        ])

        @include('dashboards.partials.carte-stat', [
            'titre' => 'Historique de quiz',
            'icone' => 'check-circle',
            'couleur' => 'success',
            'lien' => route('quiz.historique.cours'),
            'valeur' => $totalQuizEffectues,
            'suffixe' => 'quiz effectué(s)',
        ])
    </div>

    {{-- Calendrier des classes : toutes les classes à venir, les miennes en rouge --}}
    <div class="row">
        <div class="col-12">
            @include('partials.calendrier-classes', [
                'evenements' => $evenementsCalendrier,
                'titre' => 'Calendrier des classes',
                'legende' => [['nv-cal-inscrit', 'Mes classes'], ['nv-cal-a-venir', 'À venir'], ['nv-cal-en-cours', 'En cours']],
            ])
        </div>
    </div>

@endsection

@section('script')
    <!-- apexcharts -->
    <script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/jsvectormap/jsvectormap.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/jsvectormap/maps/world-merc.js') }}"></script>

    <!-- dashboard init -->
    <script src="{{ URL::asset('build/js/pages/dashboard-analytics.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection