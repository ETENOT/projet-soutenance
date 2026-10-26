@extends('layouts.master')

@section('title')
    Historique de quiz — {{ $cours->titre }}
@endsection

@section('content')
@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('cours.show', $cours) }}">{{ $cours->titre }}</a>
    @endslot
    @slot('title')
        Historique de quiz
    @endslot
@endcomponent

<div class="container py-4 nv-quiz-container-800">
    <a href="{{ route('cours.show', $cours) }}" class="text-black-50 text-decoration-none fw-medium">
        <i class="ri-arrow-left-line align-middle me-1"></i>Retour au cours
    </a>

    <h2 class="my-3">Historique de quiz — {{ $cours->titre }}</h2>

    {{-- Graphique de progression : évolution du score au fil des tentatives --}}
    @if ($resultats->isNotEmpty())
        <div class="card mb-3">
            <div class="card-body p-3 pb-0 pt-0">
                @include('quiz.partials.progression', ['progression' => $progression])
            </div>
        </div>
    @endif

    @forelse ($resultats as $resultat)
        @php
            $bareme = $resultat->quiz->bareme;
            // Moyenne = la moitié du barème (10/20, 15/30...). Au-dessus ou égal : vert, sinon rouge.
            $reussi = $bareme > 0 && $resultat->score >= $bareme / 2;
        @endphp

        <a href="{{ route('quiz.resultat', $resultat) }}" class="text-decoration-none text-reset">
            <div class="card mb-2">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-medium">
                            Quiz du {{ \Carbon\Carbon::parse($resultat->quiz->date)->format('d/m/Y') }}
                        </div>
                        <small class="text-muted">
                            Terminé à {{ $resultat->created_at->format('H:i') }} — voir la correction
                        </small>
                    </div>
                    <span class="fs-5 fw-bold {{ $reussi ? 'text-success' : 'text-danger' }}">
                        {{ $resultat->score }} / {{ $bareme }}
                    </span>
                </div>
            </div>
        </a>
    @empty
        <p class="text-muted">Vous n'avez pas encore terminé de quiz pour ce cours.</p>
    @endforelse
</div>
@endsection
@section('script')
    @include('quiz.partials.progression-script')
@endsection