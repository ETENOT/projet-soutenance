@extends('layouts.master')

@section('title')
    Banque de questions
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Administration
        @endslot
        @slot('title')
            Banque de questions
        @endslot
    @endcomponent

    {{-- Cours dont la banque est vide : un particulier ne pourrait pas passer de quiz --}}
    @php $coursSansQuestion = $cours->where('questions_count', 0); @endphp
    @if ($coursSansQuestion->isNotEmpty())
        <div class="alert alert-warning">
            <strong>Cours sans question :</strong>
            {{ $coursSansQuestion->pluck('titre')->join(', ') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h4 class="card-title mb-0">Questions ({{ $questions->total() }})</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.quiz.reglages') }}" class="btn btn-soft-secondary">
                    <i class="ri-settings-3-line me-1"></i>Noté sur…
                </a>
                {{-- On garde le filtre de cours : la nouvelle question sera pré-associée à ce cours --}}
                <a href="{{ route('admin.questions.create', request()->only('cours_id')) }}" class="btn btn-primary">
                    <i class="ri-add-line me-1"></i>Ajouter une question
                </a>
            </div>
        </div>

        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-4">
                    <select name="cours_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Tous les cours</option>
                        @foreach ($cours as $c)
                            <option value="{{ $c->id }}" @selected((string) request('cours_id') === (string) $c->id)>
                                {{ $c->titre }} ({{ $c->questions_count }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Rechercher dans les énoncés…">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-soft-primary">Filtrer</button>
                    <a href="{{ route('admin.questions.index') }}" class="btn btn-light">Réinitialiser</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Question</th>
                            <th>Cours</th>
                            <th>Bonne réponse</th>
                            <th class="text-center">Options</th>
                            <th class="text-center">Posée</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($questions as $question)
                            @php
                                // Message de confirmation adapté : plus sévère si la question a déjà servi
                                $texteSuppression = $question->reponses_count > 0
                                    ? "Cette question a déjà été posée dans {$question->reponses_count} tentative(s). La supprimer modifiera leur correction. Continuer ?"
                                    : 'Supprimer cette question ?';
                            @endphp
                            <tr>
                                <td style="min-width: 280px; white-space: normal;">{{ \Illuminate\Support\Str::limit($question->enonce, 120) }}</td>
                                <td><span class="badge bg-primary-subtle text-primary">{{ $question->cours->titre }}</span></td>
                                <td class="text-success">{{ $question->options->firstWhere('est_correct', true)?->libelle ?? '—' }}</td>
                                <td class="text-center">{{ $question->options->count() }}</td>
                                <td class="text-center">{{ $question->reponses_count }}×</td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('admin.questions.edit', $question) }}" class="btn btn-sm btn-soft-primary">Modifier</a>
                                    <form action="{{ route('admin.questions.destroy', $question) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm(@js($texteSuppression));">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-soft-danger">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Aucune question trouvée.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $questions->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection