@extends('layouts.master')

@section('title', 'Entreprise')

@section('content')

@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('dashboard') }}">Accueil</a>
    @endslot
    @slot('title')
        Entreprise
    @endslot
@endcomponent

<div class="container-fluid pb-4">
    <div class="card border shadow-sm">
        <div class="card-header"><h5 class="card-title mb-0">Mon entreprise</h5></div>
        <div class="card-body">
            @if ($entreprise)
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-success-subtle text-success fs-4"><i class="ri-building-line"></i></span>
                    </span>
                    <div>
                        <h5 class="mb-1">{{ $entreprise->raison_sociale }}</h5>
                        @if ($entreprise->secteur_activite)
                            <p class="text-muted mb-0">{{ $entreprise->secteur_activite }}</p>
                        @endif
                    </div>
                </div>

                <dl class="row mb-0">
                    <dt class="col-sm-3 text-muted fw-normal">Siège social</dt>
                    <dd class="col-sm-9">{{ $entreprise->adresse }}</dd>

                    <dt class="col-sm-3 text-muted fw-normal">Contact principal</dt>
                    <dd class="col-sm-9 mb-0">{{ $entreprise->contact_principal }}</dd>
                </dl>
            @else
                <p class="text-muted mb-0">
                    Vous n'êtes membre d'aucune entreprise pour le moment. Le rattachement à une entreprise
                    est effectué par l'administrateur de la plateforme.
                </p>
            @endif
        </div>
    </div>
</div>

@endsection