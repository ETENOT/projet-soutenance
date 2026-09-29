@extends('layouts.master')

@section('title')
    Historique de quiz — {{ $cours->titre }}
@endsection

@section('content')
<div class="container py-4" style="max-width: 800px;">
    <a href="{{ route('cours.show', $cours) }}" class="text-black-50 text-decoration-none fw-medium">
        <i class="ri-arrow-left-line align-middle me-1"></i>Retour au cours
    </a>

    <h2 class="my-3">Historique de quiz — {{ $cours->titre }}</h2>

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