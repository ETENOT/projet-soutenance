@extends('layouts.master')

@section('title')
    Quiz des chapitres — {{ $cours->titre }}
@endsection

@section('content')
@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('cours.show', $cours) }}">{{ $cours->titre }}</a>
    @endslot
    @slot('title')
        Quiz des chapitres
    @endslot
@endcomponent

<div class="container py-4 nv-quiz-container-800">
    <a href="{{ route('cours.show', $cours) }}" class="text-black-50 text-decoration-none fw-medium">
        <i class="ri-arrow-left-line align-middle me-1"></i>Retour au cours
    </a>

    <h2 class="my-3">Quiz des chapitres — {{ $cours->titre }}</h2>

    @foreach (['success' => 'success', 'error' => 'danger'] as $cle => $couleur)
        @if (session($cle))
            <div class="alert alert-{{ $couleur }}">{{ session($cle) }}</div>
        @endif
    @endforeach

    <p class="text-muted">
        Terminez le quiz d'un chapitre pour débloquer le suivant, jusqu'au quiz final qui reprend les questions
        de tous les chapitres. Un quiz débloqué peut être repassé autant de fois que vous le souhaitez.
    </p>

    @unless ($inscrit)
        <div class="alert alert-secondary">
            <i class="ri-lock-line me-1"></i>
            Ces quiz sont réservés aux participants inscrits à une classe de ce cours.
            <a href="{{ route('cours.show', $cours) }}#classes" class="alert-link">Voir les classes</a>
        </div>
    @endunless

    {{-- Étapes du parcours --}}
    <div class="card mb-4">
        <div class="card-body">
            @include('evaluations.partials.parcours', ['cours' => $cours, 'etapes' => $etapes])
        </div>
    </div>

    {{-- Historique : toutes les tentatives terminées de chaque étape --}}
    <h4 class="mb-3">Historique</h4>

    @php $aucun = true; @endphp
    @foreach ($etapes as $etape)
        @php
            $cle = $etape['type'] === 'final' ? '' : (string) $etape['chapitre']->id;
            $tentatives = $historique->get($cle, collect());
        @endphp

        @if ($tentatives->isNotEmpty())
            @php $aucun = false; @endphp
            <h6 class="text-muted mt-3">{{ $etape['titre'] }}</h6>

            @foreach ($tentatives as $tentative)
                @php $reussi = $tentative->bareme > 0 && $tentative->score * 2 >= $tentative->bareme; @endphp

                <a href="{{ route('evaluations.tentative.resultat', $tentative) }}" class="text-decoration-none text-reset">
                    <div class="card mb-2">
                        <div class="card-body d-flex justify-content-between align-items-center py-2">
                            <div>
                                <div class="fw-medium">Quiz du {{ $tentative->termine_le->format('d/m/Y') }}</div>
                                <small class="text-muted">Terminé à {{ $tentative->termine_le->format('H:i') }} — voir la correction</small>
                            </div>
                            <span class="fs-5 fw-bold {{ $reussi ? 'text-success' : 'text-danger' }}">
                                {{ $tentative->score }} / {{ $tentative->bareme }}
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        @endif
    @endforeach

    @if ($aucun)
        <p class="text-muted">Vous n'avez pas encore terminé de quiz de chapitre pour ce cours.</p>
    @endif
</div>
@endsection