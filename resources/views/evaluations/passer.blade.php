@extends('layouts.master')

@section('title')
    {{ $tentative->titre() }} — {{ $tentative->cours->titre }}
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('evaluations.parcours', $tentative->cours) }}">Quiz des chapitres</a>
        @endslot
        @slot('title')
            {{ $tentative->titre() }}
        @endslot
    @endcomponent

<div class="container py-4 nv-quiz-container-800">
    {{-- Sortir sans terminer : la tentative reste « en cours » et pourra être reprise --}}
    <a href="{{ route('evaluations.parcours', $tentative->cours) }}" class="text-black-50 text-decoration-none fw-medium">
        <i class="ri-arrow-left-line align-middle me-1"></i>Sortir du quiz (vos réponses sont gardées)
    </a>

    <div class="d-flex justify-content-between align-items-center my-3">
        <h2 class="mb-0">{{ $tentative->titre() }}</h2>
        <span class="badge bg-primary fs-6">Noté sur {{ $tentative->bareme }}</span>
    </div>

    @if ($tentative->estFinal())
        <div class="alert alert-info">Le quiz final reprend des questions de tous les chapitres du cours.</div>
    @endif

    {{-- Soumis seulement par « Terminer le quiz ». Chaque réponse est enregistrée en AJAX au clic (voir le script). --}}
    <form id="form-terminer" action="{{ route('evaluations.tentative.terminer', $tentative) }}" method="POST">
        @csrf

        @foreach ($reponses as $reponse)
            <div class="card mb-3">
                <div class="card-body">
                    <p class="fw-semibold">{{ $loop->iteration }}. {{ $reponse->question->enonce }}</p>

                    @foreach ($reponse->question->options as $option)
                        <div class="form-check">
                            {{-- checked = réponse déjà enregistrée (reprise de la tentative) --}}
                            <input class="form-check-input reponse-radio" type="radio"
                                   id="opt_{{ $option->id }}"
                                   name="q_{{ $reponse->question_id }}"
                                   data-question-id="{{ $reponse->question_id }}"
                                   data-option-id="{{ $option->id }}"
                                   @checked((int) $reponse->option_id === (int) $option->id)>
                            <label class="form-check-label" for="opt_{{ $option->id }}">{{ $option->libelle }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"
                    onclick="return confirm('Terminer ce quiz ? Les questions sans réponse vaudront 0 point.');">
                Terminer le quiz
            </button>
            <button type="submit" form="form-annuler" class="btn btn-outline-danger">Annuler le quiz</button>
        </div>
    </form>

    <form id="form-annuler" action="{{ route('evaluations.tentative.annuler', $tentative) }}" method="POST"
          onsubmit="return confirm('Annuler ce quiz ? Il sera supprimé et ne comptera pas.');">
        @csrf
    </form>
</div>
@endsection

@section('script')
<script>
// À chaque clic sur une option, la réponse est envoyée au serveur sans recharger la page :
// rien n'est perdu si le particulier quitte la page, il reprendra là où il en était.
document.querySelectorAll('.reponse-radio').forEach(function (radio) {
    radio.addEventListener('change', function () {
        fetch(@json(route('evaluations.tentative.repondre', $tentative)), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': @json(csrf_token()),
            },
            body: JSON.stringify({
                question_id: this.dataset.questionId,
                option_id: this.dataset.optionId,
            }),
        }).then(function (r) {
            // 409 = tentative déjà terminée (autre onglet) : on recharge pour afficher le résultat
            if (r.status === 409) window.location.reload();
        });
    });
});
</script>
@endsection