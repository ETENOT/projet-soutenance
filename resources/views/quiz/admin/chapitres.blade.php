@extends('layouts.master')

@section('title')
    Quiz des chapitres — {{ $cours->titre }}
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('admin.cours.contenus', $cours) }}">{{ $cours->titre }}</a>
        @endslot
        @slot('title')
            Quiz des chapitres
        @endslot
    @endcomponent

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="alert alert-info">
        Chaque chapitre a son quiz, construit avec les questions de la banque <strong>rattachées à ce chapitre</strong>
        (menu « Chapitre » de la <a href="{{ route('admin.questions.index', ['cours_id' => $cours->id]) }}" class="alert-link">banque de questions</a>).
        Le <strong>quiz final</strong> pioche dans les questions de tous les chapitres.
        Les particuliers inscrits débloquent les quiz dans l'ordre des chapitres, puis le quiz final.
        « Noté sur » = nombre de questions tirées au hasard ; laissez vide pour poser toutes les questions.
        Le quiz du cours (page <a href="{{ route('admin.quiz.reglages') }}" class="alert-link">Réglages de notation</a>) n'est pas concerné.
    </div>

    @if ($questionsGenerales > 0)
        <div class="alert alert-light border">
            <i class="ri-information-line me-1"></i>
            {{ $questionsGenerales }} question(s) de ce cours ne sont rattachées à aucun chapitre :
            elles servent uniquement au quiz du cours.
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.cours.quiz-chapitres.update', $cours) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="table-responsive">
                    <table class="table align-middle mb-3">
                        <thead class="table-light">
                            <tr>
                                <th>Quiz</th>
                                <th class="text-center">Questions disponibles</th>
                                <th class="nv-score-column">Noté sur</th>
                                <th>Quiz obtenu</th>
                                <th class="text-center">Tentatives</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($chapitres as $chapitre)
                                <tr data-banque="{{ $chapitre->questions_count }}">
                                    <td class="text-break">
                                        <span class="badge bg-danger-subtle text-danger me-1">{{ $chapitre->ordre }}</span>
                                        {{ $chapitre->titre }}
                                    </td>
                                    <td class="text-center">
                                        {{-- Lien vers la banque filtrée sur ce cours pour rattacher des questions --}}
                                        <a href="{{ route('admin.questions.index', ['cours_id' => $cours->id]) }}">{{ $chapitre->questions_count }}</a>
                                    </td>
                                    <td>
                                        <input type="number" min="1" max="100" step="1" name="note_sur[{{ $chapitre->id }}]"
                                               class="form-control champ-note @error('note_sur.' . $chapitre->id) is-invalid @enderror"
                                               placeholder="Toutes"
                                               value="{{ old('note_sur.' . $chapitre->id, $chapitre->quiz_note_sur) }}">
                                        @error('note_sur.' . $chapitre->id)
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td class="resume-quiz small text-break"></td>
                                    <td class="text-center">{{ $chapitre->tentatives_evaluation_count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        Ce cours n'a pas encore de chapitre.
                                        <a href="{{ route('admin.cours.contenus', $cours) }}">Créer des chapitres</a>
                                    </td>
                                </tr>
                            @endforelse

                            {{-- Quiz final : questions de tous les chapitres --}}
                            <tr class="table-warning" data-banque="{{ $questionsFinal }}">
                                <td><i class="ri-trophy-line me-1"></i><strong>Quiz final</strong></td>
                                <td class="text-center">{{ $questionsFinal }}</td>
                                <td>
                                    <input type="number" min="1" max="100" step="1" name="note_sur_final"
                                           class="form-control champ-note @error('note_sur_final') is-invalid @enderror"
                                           placeholder="Toutes"
                                           value="{{ old('note_sur_final', $cours->note_sur_final) }}">
                                    @error('note_sur_final')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>
                                <td class="resume-quiz small text-break"></td>
                                <td class="text-center">{{ $tentativesFinal }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Enregistrer les réglages</button>
                    <a href="{{ route('admin.cours.contenus', $cours) }}" class="btn btn-light">Retour aux chapitres</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
<script>
    // Affiche en direct ce que donnera chaque quiz selon la valeur saisie (même logique que les réglages du cours)
    document.querySelectorAll('tbody tr[data-banque]').forEach(function (ligne) {
        const banque = parseInt(ligne.dataset.banque, 10);
        const champ = ligne.querySelector('.champ-note');
        const resume = ligne.querySelector('.resume-quiz');

        function actualiser() {
            const note = parseInt(champ.value, 10);

            if (banque === 0) {
                resume.innerHTML = '<span class="text-muted">Aucune question : pas de quiz (étape sautée)</span>';
            } else if (!note) {
                resume.innerHTML = 'Toutes les questions : ' + banque + ', noté sur ' + banque;
            } else if (note > banque) {
                resume.innerHTML = '<span class="text-warning">Pas assez de questions : ' + banque
                    + ' seulement, noté sur ' + banque + '</span>';
            } else {
                resume.innerHTML = '<span class="text-success">' + note + ' questions au hasard sur ' + banque + ', noté sur ' + note + '</span>';
            }
        }

        champ.addEventListener('input', actualiser);
        actualiser();
    });
</script>
@endsection