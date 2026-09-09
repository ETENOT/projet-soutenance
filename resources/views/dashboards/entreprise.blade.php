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
        Formation_neovision
    @endslot

    @slot('title')
        Espace entreprise
    @endslot
@endcomponent

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="fs-16 mb-1">Bonjour {{ $utilisateur->name }}, bienvenue sur Formation_neovison.</h4>
                    <p class="text-muted mb-0">Pilotez les collaborateurs, les sessions et le budget formation de votre entreprise.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
       
        <div class="col-xl-4 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="fw-medium text-muted mb-0">Devis en cours</p>
                            <h2 class="mt-4 ff-secondary fw-semibold"><span class="counter-value" data-target="{{ $devisEnCours }}">0</span></h2>
                            <p class="mb-0 text-muted"><span class="badge bg-light text-warning mb-0"><i class="ri-time-line align-middle"></i> À traiter</span> en attente de décision</p>
                        </div>
                        <div class="avatar-sm flex-shrink-0"><span class="avatar-title bg-warning-subtle rounded-circle fs-2"><i data-feather="file-text" class="text-warning"></i></span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="fw-medium text-muted mb-0">Sessions réservées</p>
                            <h2 class="mt-4 ff-secondary fw-semibold"><span class="counter-value" data-target="{{ $sessionsReservees }}">0</span></h2>
                            <p class="mb-0 text-muted"><span class="badge bg-light text-primary mb-0"><i class="ri-calendar-check-line align-middle"></i> Planifiées</span> pour l'entreprise</p>
                        </div>
                        <div class="avatar-sm flex-shrink-0"><span class="avatar-title bg-primary-subtle rounded-circle fs-2"><i data-feather="calendar" class="text-primary"></i></span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="fw-medium text-muted mb-0">Budget formation engagé</p>
                            <h2 class="mt-4 ff-secondary fw-semibold fs-20">{{ $budgetFormation }}</h2>
                            <p class="mb-0 text-muted"><span class="badge bg-light text-success mb-0"><i class="ri-arrow-up-line align-middle"></i> 68 %</span> du budget annuel</p>
                        </div>
                        <div class="avatar-sm flex-shrink-0"><span class="avatar-title bg-success-subtle rounded-circle fs-2"><i data-feather="briefcase" class="text-success"></i></span></div>
                    </div>
                </div>
            </div>
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
