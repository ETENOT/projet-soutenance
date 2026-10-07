@extends('layouts.master')

@section('title')
    Détail d'une tentative
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('admin.quiz.index') }}">Suivi des quiz</a>
        @endslot
        @slot('title')
            Détail d'une tentative
        @endslot
    @endcomponent

    <div class="card">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h5 class="mb-1">{{ $quiz->cours->titre }}</h5>
                <div class="text-muted">
                    {{ \Carbon\Carbon::parse($quiz->date)->format('d/m/Y') }} à {{ substr($quiz->heure_debut, 0, 5) }}
                    —
                    @if ($quiz->user)
                        {{ $quiz->user->name }} ({{ $quiz->user->email }})
                    @else
                        Visiteur non inscrit ({{ $quiz->email_visiteur }})
                    @endif
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                @if ($resultat)
                    @php $reussi = $quiz->bareme > 0 && ($resultat->score / $quiz->bareme) >= 0.5; @endphp
                    <h3 class="mb-0 {{ $reussi ? 'text-success' : 'text-danger' }}">{{ (float) $resultat->score }} / {{ $quiz->bareme }}</h3>
                @else
                    <span class="badge bg-warning-subtle text-warning fs-12">En cours</span>
                @endif

                <form action="{{ route('admin.quiz.destroy', $quiz) }}" method="POST"
                      onsubmit="return confirm('Supprimer cette tentative et son résultat définitivement ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-soft-danger">Supprimer</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Correction : bonne réponse en vert (✅), réponse fausse choisie en rouge (❌) --}}
    @foreach ($reponses as $i => $reponse)
        <div class="card">
            <div class="card-body">
                <h6 class="mb-3">{{ $i + 1 }}. {{ $reponse->question->enonce }}</h6>

                <ul class="list-unstyled mb-0">
                    @foreach ($reponse->question->options as $option)
                        @php $choisie = $reponse->option_id === $option->id; @endphp
                        <li class="mb-1 {{ $option->est_correct ? 'text-success fw-semibold' : ($choisie ? 'text-danger' : '') }}">
                            @if ($option->est_correct) ✅ @elseif ($choisie) ❌ @else <span class="ms-4"></span> @endif
                            {{ $option->libelle }}
                            @if ($choisie)
                                <small class="text-muted">(réponse donnée)</small>
                            @endif
                        </li>
                    @endforeach
                </ul>

                @if ($reponse->option_id === null)
                    <span class="badge bg-secondary-subtle text-secondary mt-2">Sans réponse</span>
                @endif
            </div>
        </div>
    @endforeach
@endsection