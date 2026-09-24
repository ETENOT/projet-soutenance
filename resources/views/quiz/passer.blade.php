@extends('layouts.master')

@section('title')
    Quiz — {{ $quiz->cours->titre }}
@endsection

@section('content')
<div>
            <a href="{{ route('cours.show', $quiz->cours) }}"
        class="top-0 start-0 m-3 m-sm-4 z-3 text-black-50 text-decoration-none fw-medium">
            <i class="ri-arrow-left-line align-middle me-1"></i>
            {{ __("Sortir du quiz") }}
        </a>
    <div class="container py-4" style="max-width: 800px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Quiz — {{ $quiz->cours->titre }}</h2>
            {{-- data-restant : temps restant en secondes, calculé côté serveur, lu par le JS ci-dessous --}}
            <div id="chrono" data-restant="{{ $secondesRestantes }}" class="badge bg-primary fs-6"></div>
        </div>

        {{-- Ce formulaire n'est soumis QUE quand on clique sur "Terminer" ou quand le minuteur arrive à 0.
            Les réponses individuelles, elles, sont envoyées en AJAX au fur et à mesure (voir le JS plus bas) --}}
        <form id="form-terminer" action="{{ route('quiz.tentative.terminer', $quiz) }}" method="POST">
            @csrf

            @foreach ($reponses as $reponse)
                <div class="card mb-3">
                    <div class="card-body">
                        <p class="fw-semibold">{{ $loop->iteration }}. {{ $reponse->question->enonce }}</p>

                        @foreach ($reponse->question->options as $option)
                            <div class="form-check">
                                {{-- data-question-id / data-option-id : lus par le JS pour savoir quoi envoyer en AJAX --}}
                                {{-- checked si c'est l'option déjà enregistrée pour cette question (reprise d'une tentative) --}}
                                <input class="form-check-input reponse-radio" type="radio"
                                    name="q_{{ $reponse->question_id }}"
                                    data-question-id="{{ $reponse->question_id }}"
                                    data-option-id="{{ $option->id }}"
                                    {{ $reponse->option_id === $option->id ? 'checked' : '' }}>
                                <label class="form-check-label">{{ $option->libelle }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <button type="submit" class="btn btn-primary">Terminer le quiz</button>
        </form>
    </div>
</div>

<script>
// À chaque changement de sélection, on envoie discrètement la réponse au serveur
// (pas de rechargement de page), pour que rien ne soit perdu si l'utilisateur quitte
document.querySelectorAll('.reponse-radio').forEach(radio => {
    radio.addEventListener('change', function () {
        fetch("{{ route('quiz.tentative.repondre', $quiz) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({
                question_id: this.dataset.questionId,
                option_id: this.dataset.optionId,
            }),
        }).then(r => r.json()).then(data => {
            // Le serveur a détecté que le temps est écoulé entre-temps :
            // on redirige directement vers le résultat
            if (data.expire) window.location = data.redirect;
        });
    });
});

// Décompte affiché à l'écran, purement visuel — la vraie vérification
// du temps écoulé se fait côté serveur (voir QuizFinalisationService)
let restant = parseInt(document.getElementById('chrono').dataset.restant, 10);
const chrono = document.getElementById('chrono');

function formaterChrono(secondes) {
    const m = Math.floor(secondes / 60);
    const s = secondes % 60;
    return `${m}:${String(s).padStart(2, '0')}`;
}

chrono.textContent = formaterChrono(Math.max(restant, 0));

const interval = setInterval(() => {
    restant--;
    chrono.textContent = formaterChrono(Math.max(restant, 0));
    if (restant <= 0) {
        clearInterval(interval);
        // Soumission automatique du formulaire à 0 -> appelle terminer()
        document.getElementById('form-terminer').submit();
    }
}, 1000);
</script>
@endsection