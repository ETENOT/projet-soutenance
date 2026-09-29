@extends('layouts.master')

@section('title')
    Résultat du quiz
@endsection

@section('content')
<div class="container py-4" style="max-width: 800px;">
    <h2 class="mb-3">Résultat</h2>

    <div class="alert alert-info fs-5">
        Votre note : <strong>{{ $resultatQuiz->score }} / {{ $resultatQuiz->quiz->bareme }}</strong>
    </div>

    @foreach ($reponses as $reponse)
        <div class="card mb-3">
            <div class="card-body">
                <p class="fw-semibold">{{ $loop->iteration }}. {{ $reponse->question->enonce }}</p>

                @foreach ($reponse->question->options as $option)
                    @if ($option->est_correct)
                        <div class="text-success fw-bold">✅ {{ $option->libelle }}</div>
                    @elseif ($reponse->option_id === $option->id)
                        <div class="text-danger fw-bold">❌ {{ $option->libelle }}</div>
                    @else
                        <div>{{ $option->libelle }}</div>
                    @endif
                @endforeach

                @if ($reponse->option_id === null)
                    <small class="text-danger">Question sans réponse</small>
                @endif
            </div>
        </div>
    @endforeach

    <div>
        {{-- Repasser le quiz = recréer une nouvelle tentative : comme cette dernière
            a déjà un résultat, show() va automatiquement en démarrer une nouvelle --}}
        <a href="{{ route('quiz.tentative.show', $resultatQuiz->quiz->cours) }}" class="btn btn-outline-secondary">
            Repasser le quiz
        </a>
            <a href="{{ route('cours.show', $resultatQuiz->quiz->cours) }}" class="btn btn-primary">
            sortir du quiz
        </a>
    </div>
</div>
@endsection