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

    {{-- ===== Mon entreprise actuelle ===== --}}
    <div class="card border shadow-sm mb-4">
        <div class="card-header"><h5 class="card-title mb-0">Mon entreprise</h5></div>
        <div class="card-body">
            @if ($enrolementValide)
                <div class="d-flex align-items-center gap-3">
                    <span class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-3 bg-success-subtle text-success fs-4"><i class="ri-building-line"></i></span>
                    </span>
                    <div>
                        <h5 class="mb-1">{{ $enrolementValide->entreprise->raison_sociale }}</h5>
                        <p class="text-muted mb-0">
                            @if ($enrolementValide->entreprise->secteur_activite)
                                {{ $enrolementValide->entreprise->secteur_activite }}
                            @endif
                        </p>
                    </div>
                </div>
            @else
                <p class="text-muted mb-0">Vous n’êtes membre d’aucune entreprise pour le moment.</p>
            @endif
        </div>
    </div>
</div>

@endsection