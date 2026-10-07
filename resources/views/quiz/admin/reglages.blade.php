@extends('layouts.master')

@section('title')
    Réglages de notation des quiz
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('admin.quiz.index') }}">Suivi des quiz</a>
        @endslot
        @slot('title')
            Réglages de notation
        @endslot
    @endcomponent

    <div class="alert alert-info">
        Choisissez, pour chaque cours, <strong>sur combien</strong> est noté le quiz. Ce nombre est celui des questions
        tirées <strong>au hasard</strong> dans la banque du cours (une même question n'est jamais posée deux fois),
        chaque question valant 1 point. Exemple : banque de 30 questions, noté sur 20 → 20 questions au hasard, et 7 bonnes réponses donnent 7 / 20.
        Laissez vide pour poser toute la banque.
        Les quiz déjà commencés ne sont pas modifiés.
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.quiz.reglages.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="table-responsive">
                    <table class="table align-middle mb-3">
                        <thead class="table-light">
                            <tr>
                                <th>Cours</th>
                                <th class="text-center">Questions dans la banque</th>
                                <th style="width: 180px;">Noté sur</th>
                                <th>Quiz obtenu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cours as $c)
                                <tr data-banque="{{ $c->questions_count }}">
                                    <td>{{ $c->titre }}</td>
                                    <td class="text-center">{{ $c->questions_count }}</td>
                                    <td>
                                        {{-- old() garde la saisie en cas d'erreur de validation --}}
                                        <input type="number" min="1" max="100" step="1" name="note_sur[{{ $c->id }}]"
                                               class="form-control champ-note @error('note_sur.' . $c->id) is-invalid @enderror"
                                               placeholder="Toutes"
                                               value="{{ old('note_sur.' . $c->id, $c->note_sur) }}">
                                        @error('note_sur.' . $c->id)
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td class="resume-quiz small"></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Aucun cours.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Enregistrer les réglages</button>
                    <a href="{{ route('admin.quiz.index') }}" class="btn btn-light">Annuler</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
<script>
    // Affiche en direct ce que donnera le quiz de chaque cours, selon la valeur saisie
    document.querySelectorAll('tbody tr[data-banque]').forEach(function (ligne) {
        const banque = parseInt(ligne.dataset.banque, 10);
        const champ = ligne.querySelector('.champ-note');
        const resume = ligne.querySelector('.resume-quiz');

        function actualiser() {
            const note = parseInt(champ.value, 10);

            if (banque === 0) {
                resume.innerHTML = '<span class="text-danger">Banque vide : aucun quiz possible</span>';
            } else if (!note) {
                resume.innerHTML = 'Toute la banque : ' + banque + ' questions, noté sur ' + banque;
            } else if (note > banque) {
                resume.innerHTML = '<span class="text-warning">Banque insuffisante : ' + banque
                    + ' questions seulement, noté sur ' + banque + ' (ajoutez des questions)</span>';
            } else {
                resume.innerHTML = '<span class="text-success">' + note + ' questions au hasard sur ' + banque + ', noté sur ' + note + '</span>';
            }
        }

        champ.addEventListener('input', actualiser);
        actualiser();
    });
</script>
@endsection