@extends('layouts.master')

@section('title')
    Notification
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Notifications
        @endslot
        @slot('title')
            Détail de la notification
        @endslot
    @endcomponent

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted fs-12 text-uppercase mb-3">
                        <i class="ri-time-line me-1"></i>
                        Reçue le {{ $notification->created_at->format('d/m/Y à H:i') }}
                    </p>

                    <p class="fs-15 mb-4">{{ $notification->message }}</p>

                    <a href="{{ route('notifications.index') }}" class="btn btn-primary">
                        <i class="ri-arrow-left-line align-middle me-1"></i> Toutes mes notifications
                    </a>
                    <a href="{{ route('dashboard') }}" class="btn btn-soft-secondary ms-2">
                        Tableau de bord
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection