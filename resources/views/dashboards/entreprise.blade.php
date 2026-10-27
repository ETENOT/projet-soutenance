@extends('layouts.master')

@section('title')
    Espace entreprise
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/jsvectormap/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Dashboards
        @endslot
        @slot('title')
            Espace entreprise
        @endslot
    @endcomponent

    {{-- Message de bienvenue --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="fs-16 mb-1">Bonjour {{ $utilisateur->name }}, bienvenue sur Formation_neovision.</h4>
                    <p class="text-muted mb-0">Pilotez les collaborateurs, les sessions et le budget formation de votre entreprise.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- 3 cartes de même hauteur --}}
    <div class="row g-3">
        @include('dashboards.partials.carte-stat', [
            'titre' => 'Devis en cours',
            'icone' => 'file-text',
            'couleur' => 'warning',
            'valeur' => $devisEnCours,
            'detail' => 'À traiter, en attente de décision',
        ])

        @include('dashboards.partials.carte-stat', [
            'titre' => 'Sessions réservées',
            'icone' => 'calendar',
            'couleur' => 'primary',
            'valeur' => $sessionsReservees,
            'detail' => 'Planifiées pour l\'entreprise',
        ])

        @include('dashboards.partials.carte-stat', [
            'titre' => 'Budget formation engagé',
            'icone' => 'briefcase',
            'couleur' => 'success',
            'texte' => number_format($budgetFormation, 0, ',', ' ') . ' fcfa',
            'detail' => 'Total des paiements des collaborateurs',
        ])
    </div>

    {{-- Calendrier de toutes les classes à venir --}}
    <div class="row">
        <div class="col-12">
            @include('partials.calendrier-classes', [
                'evenements' => $evenementsCalendrier,
                'titre' => 'Calendrier des classes',
                'simple' => true,
                'legende' => [['nv-cal-a-venir', 'À venir'], ['nv-cal-en-cours', 'En cours']],
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