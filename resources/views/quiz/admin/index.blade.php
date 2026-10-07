@extends('layouts.master')

@section('title')
    Suivi des quiz
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Administration
        @endslot
        @slot('title')
            Suivi des quiz
        @endslot
    @endcomponent

    {{-- Chiffres clés --}}
    <div class="row">
        <div class="col-md-4">
            <div class="card"><div class="card-body">
                <p class="text-muted mb-1">Tentatives</p>
                <h3 class="mb-0">{{ $stats['total'] }}</h3>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card"><div class="card-body">
                <p class="text-muted mb-1">Terminées</p>
                <h3 class="mb-0">{{ $stats['termines'] }}</h3>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card"><div class="card-body">
                <p class="text-muted mb-1">Moyenne générale</p>
                <h3 class="mb-0">{{ $stats['moyenne'] !== null ? number_format($stats['moyenne'], 1, ',', ' ') . ' %' : '—' }}</h3>
            </div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Tentatives de quiz ({{ $quizzes->total() }})</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.quiz.reglages') }}" class="btn btn-soft-success">
                    <i class="ri-settings-3-line me-1"></i>Réglages de notation
                </a>
                <a href="{{ route('admin.questions.index') }}" class="btn btn-soft-primary">Banque de questions</a>
            </div>
        </div>

        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-3">
                    <select name="cours_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Tous les cours</option>
                        @foreach ($cours as $c)
                            <option value="{{ $c->id }}" @selected((string) request('cours_id') === (string) $c->id)>{{ $c->titre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="statut" class="form-select" onchange="this.form.submit()">
                        <option value="">Tous les statuts</option>
                        <option value="termine" @selected(request('statut') === 'termine')>Terminés</option>
                        <option value="en_cours" @selected(request('statut') === 'en_cours')>En cours</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Nom ou e-mail du participant">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-soft-primary">Filtrer</button>
                    <a href="{{ route('admin.quiz.index') }}" class="btn btn-light">Réinitialiser</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Cours</th>
                            <th>Participant</th>
                            <th>Statut</th>
                            <th>Note</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($quizzes as $quiz)
                            @php $resultat = $quiz->resultats->first(); @endphp
                            <tr>
                                <td class="text-nowrap">
                                    {{ \Carbon\Carbon::parse($quiz->date)->format('d/m/Y') }}
                                    <small class="text-muted">{{ substr($quiz->heure_debut, 0, 5) }}</small>
                                </td>
                                <td>{{ $quiz->cours->titre }}</td>
                                <td>
                                    {{-- user_id null = visiteur non inscrit : on affiche l'e-mail saisi avant le quiz --}}
                                    @if ($quiz->user)
                                        {{ $quiz->user->name }}<br><small class="text-muted">{{ $quiz->user->email }}</small>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">Visiteur</span><br>
                                        <small class="text-muted">{{ $quiz->email_visiteur }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if ($resultat)
                                        <span class="badge bg-success-subtle text-success">Terminé</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning">En cours</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($resultat)
                                        {{-- Rouge sous la moyenne (50 %), vert sinon --}}
                                        @php $reussi = $quiz->bareme > 0 && ($resultat->score / $quiz->bareme) >= 0.5; @endphp
                                        <span class="fw-semibold {{ $reussi ? 'text-success' : 'text-danger' }}">
                                            {{ (float) $resultat->score }} / {{ $quiz->bareme }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('admin.quiz.show', $quiz) }}" class="btn btn-sm btn-soft-primary">Voir</a>
                                    <form action="{{ route('admin.quiz.destroy', $quiz) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Supprimer cette tentative et son résultat définitivement ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-soft-danger">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Aucune tentative trouvée.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $quizzes->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection