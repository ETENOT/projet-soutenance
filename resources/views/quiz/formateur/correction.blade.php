@extends('layouts.master')

@section('title')
    Correction du quiz — {{ $stagiaire->name }}
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('quiz.formateur.index', $classe) }}">Résultats au quiz — {{ $classe->nom }}</a>
        @endslot
        @slot('title')
            Correction
        @endslot
    @endcomponent

    @php
        $score = rtrim(rtrim(number_format($resultatQuiz->score, 2, ',', ''), '0'), ',');
        $reussi = $quiz->bareme > 0 && ($resultatQuiz->score / $quiz->bareme) >= 0.5;
    @endphp

    {{-- En-tête : stagiaire, quiz, note --}}
    <div class="card">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-1">{{ $stagiaire->name }}</h5>
                <p class="text-muted mb-0 fs-12">
                    {{ $stagiaire->email }} · Quiz « {{ $cours->titre }} » du
                    {{ \Carbon\Carbon::parse($quiz->date)->format('d/m/Y') }}
                </p>
            </div>
            <span class="badge fs-5 bg-{{ $reussi ? 'success' : 'danger' }}-subtle text-{{ $reussi ? 'success' : 'danger' }}">
                {{ $score }} / {{ $quiz->bareme }}
            </span>
        </div>
    </div>

    {{-- Une carte par question : bonne réponse en vert, réponse fausse du stagiaire en rouge --}}
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

    <a href="{{ route('quiz.formateur.index', $classe) }}" class="btn btn-outline-secondary">
        Retour aux résultats
    </a>
@endsection