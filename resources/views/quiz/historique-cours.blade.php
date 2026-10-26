@extends('layouts.master')

@section('title')
    Historique de quiz
@endsection

@section('content')
@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('cours.mes') }}">Mes classes</a>
    @endslot
    @slot('title')
        Historique de quiz
    @endslot
@endcomponent

<div class="container py-4 nv-quiz-container-800">
    <a href="{{ route('dashboard') }}" class="text-black-50 text-decoration-none fw-medium">
        <i class="ri-arrow-left-line align-middle me-1"></i>Retour au tableau de bord
    </a>

    <h2 class="my-3">Historique de quiz</h2>

    @forelse ($cours as $c)
        <a href="{{ route('quiz.historique', $c) }}" class="text-decoration-none text-reset">
            <div class="card card-animate mb-2">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-medium">{{ $c->titre }}</div>
                        <small class="text-muted">
                            Dernier quiz le {{ \Carbon\Carbon::parse($c->dernier_quiz_le)->format('d/m/Y à H:i') }}
                        </small>
                    </div>

                    <span class="badge bg-success-subtle text-success fs-13">
                        {{ $c->nombre_quiz }} quiz
                    </span>
                </div>
            </div>
        </a>
    @empty
        <p class="text-muted">Vous n'avez pas encore terminé de quiz.</p>
    @endforelse
</div>
@endsection