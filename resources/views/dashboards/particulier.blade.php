@extends('layouts.master')
@section('title')
    Mon espace
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
            Mon espace
        @endslot
    @endcomponent

    <div class="row">
    <div class="col-xl-4 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="fw-medium text-muted mb-0">Sessions inscrites</p>
                        <h2 class="mt-4 ff-secondary fw-semibold"><span class="counter-value" data-target="{{ $sessionsInscrites }}">0</span></h2>
                    </div>
                    <div class="avatar-sm flex-shrink-0"><span class="avatar-title bg-info-subtle rounded-circle fs-2"><i data-feather="calendar" class="text-info"></i></span></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="fw-medium text-muted mb-0">Cours en cours</p>
                        <h2 class="mt-4 ff-secondary fw-semibold"><span class="counter-value" data-target="{{ $coursEnCours }}">0</span></h2>
                    </div>
                    <div class="avatar-sm flex-shrink-0"><span class="avatar-title bg-primary-subtle rounded-circle fs-2"><i data-feather="book-open" class="text-primary"></i></span></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="fw-medium text-muted mb-0">Prochaine session</p>
                        <h2 class="mt-4 ff-secondary fw-semibold fs-20">{{ $prochaineSession }}</h2>
                    </div>
                    <div class="avatar-sm flex-shrink-0"><span class="avatar-title bg-warning-subtle rounded-circle fs-2"><i data-feather="clock" class="text-warning"></i></span></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="fw-medium text-muted mb-0">Moyenne aux quiz</p>
                        <h2 class="mt-4 ff-secondary fw-semibold">
                            @if($moyenneQuiz !== null)
                                <span class="counter-value" data-target="{{ $moyenneQuiz }}">0</span>/20
                            @else
                                <span class="fs-14 text-muted">Aucun quiz passé</span>
                            @endif
                        </h2>
                    </div>
                    <div class="avatar-sm flex-shrink-0"><span class="avatar-title bg-success-subtle rounded-circle fs-2"><i data-feather="check-circle" class="text-success"></i></span></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="fw-medium text-muted mb-0">Notifications non lues</p>
                        <h2 class="mt-4 ff-secondary fw-semibold"><span class="counter-value" data-target="{{ $notificationsNonLues }}">0</span></h2>
                    </div>
                    <div class="avatar-sm flex-shrink-0"><span class="avatar-title bg-danger-subtle rounded-circle fs-2"><i data-feather="bell" class="text-danger"></i></span></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="fw-medium text-muted mb-0">Statut paiement</p>
                        <h2 class="mt-4 ff-secondary fw-semibold fs-16">
                            <span class="text-success">{{ $sessionsPayees }} payée(s)</span>
                            @if($sessionsImpayees > 0)
                                <br><span class="text-danger fs-14">{{ $sessionsImpayees }} impayée(s)</span>
                            @endif
                        </h2>
                    </div>
                    <div class="avatar-sm flex-shrink-0"><span class="avatar-title bg-secondary-subtle rounded-circle fs-2"><i data-feather="credit-card" class="text-secondary"></i></span></div>
                </div>
            </div>
        </div>
    </div>
</div><!-- end row -->

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
