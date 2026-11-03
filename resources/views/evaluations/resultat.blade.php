@extends('layouts.master')

@section('title')
    Résultat — {{ $tentative->titre() }}
@endsection

@section('content')
@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('evaluations.parcours', $tentative->cours) }}">Quiz des chapitres</a>
    @endslot
    @slot('title')
        Résultat
    @endslot
@endcomponent

@php
    // Moyenne = la moitié du barème : vert au-dessus ou égal, rouge en dessous
    $reussi = $tentative->bareme > 0 && $tentative->score * 2 >= $tentative->bareme;
@endphp

<div class="container py-4 nv-quiz-container-800">
    <h2 class="mb-1">{{ $tentative->titre() }}</h2>
    <p class="text-muted">{{ $tentative->cours->titre }} · terminé le {{ $tentative->termine_le->format('d/m/Y à H:i') }}</p>

    <div class="alert {{ $reussi ? 'alert-success' : 'alert-danger' }} fs-5">
        Votre note : <strong>{{ $tentative->score }} / {{ $tentative->bareme }}</strong>
    </div>

    {{-- Correction : ✅ bonne réponse, ❌ option choisie et fausse --}}
    @foreach ($reponses as $reponse)
        <div class="card mb-3">
            <div class="card-body">
                <p class="fw-semibold">{{ $loop->iteration }}. {{ $reponse->question->enonce }}</p>

                @foreach ($reponse->question->options as $option)
                    @if ($option->est_correct)
                        <div class="text-success fw-bold">✅ {{ $option->libelle }}</div>
                    @elseif ((int) $reponse->option_id === (int) $option->id)
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

    <div class="d-flex flex-wrap gap-2">
        {{-- Repasser = nouvelle tentative (celle-ci est terminée) --}}
        <a href="{{ $urlRepasser }}" class="btn btn-outline-secondary">Repasser le quiz</a>
        <a href="{{ route('evaluations.parcours', $tentative->cours) }}" class="btn btn-primary">Retour aux quiz des chapitres</a>
    </div>
</div>
@endsection