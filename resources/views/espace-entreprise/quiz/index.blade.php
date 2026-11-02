@extends('layouts.master')

@section('title', 'Suivi des quiz')

@section('content')

@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ route('dashboard') }}">Accueil</a>
    @endslot
    @slot('title')
        Suivi des quiz
    @endslot
@endcomponent

@php
    // 12.5 -> « 12,5 % » ; 50.0 -> « 50 % »
    $pct = fn ($v) => $v === null ? '—' : rtrim(rtrim(number_format($v, 1, ',', ''), '0'), ',') . ' %';
@endphp

<div class="container-fluid pb-4">

    <p class="text-muted mb-3">
        Résultats aux quiz des membres que <strong>{{ $entreprise->raison_sociale }}</strong> a inscrits à des classes.
        Seul le quiz du cours de chaque classe, passé pendant la période de la classe, est pris en compte.
    </p>

    {{-- Filtres : session + recherche (GET, la page se recharge) --}}
    <form method="GET" action="{{ route('entreprise.quiz.index') }}" class="card border shadow-sm mb-3">
        <div class="card-body row g-2 align-items-end">
            {{-- On garde le filtre « classe » quand on arrive depuis la page d'une classe --}}
            @if (request()->filled('classe_id'))
                <input type="hidden" name="classe_id" value="{{ request('classe_id') }}">
            @endif

            <div class="col-md-4">
                <label for="session_id" class="form-label mb-1">Session</label>
                <select name="session_id" id="session_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Toutes les sessions</option>
                    @foreach ($sessions as $session)
                        <option value="{{ $session->id }}" @selected((int) request('session_id') === $session->id)>{{ $session->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label for="q" class="form-label mb-1">Membre</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent"><i class="ri-search-line text-primary"></i></span>
                    <input type="text" name="q" id="q" value="{{ request('q') }}" class="form-control" placeholder="Nom ou e-mail…">
                </div>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                @if (request()->hasAny(['session_id', 'classe_id', 'q']))
                    <a href="{{ route('entreprise.quiz.index') }}" class="btn btn-outline-secondary" title="Effacer les filtres">
                        <i class="ri-close-line"></i>
                    </a>
                @endif
            </div>
        </div>
    </form>

    @if ($classeFiltree)
        <div class="alert alert-info py-2">
            <i class="ri-filter-3-line me-1"></i>
            Classe : <strong>{{ $classeFiltree->cours->titre }} — {{ $classeFiltree->nom }}</strong>
        </div>
    @endif

    <div class="card border shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle table-nowrap mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Membre</th>
                        <th>Cours / classe</th>
                        <th class="text-center">Quiz passés</th>
                        <th>Dernière note</th>
                        <th class="text-center">Meilleur</th>
                        <th class="text-center">Moyenne</th>
                        <th class="text-center">Évolution</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lignes as $ligne)
                        @php
                            $inscription = $ligne['inscription'];
                            $stats = $ligne['stats'];
                            $classe = $inscription->classe;
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-medium">{{ $inscription->user->name }}</div>
                                <div class="text-muted small">{{ $inscription->user->email }}</div>
                                {{-- Le particulier a pu quitter l'entreprise depuis son inscription --}}
                                @if ((int) $inscription->user->particulier?->entreprise_id !== (int) $entreprise->id)
                                    <span class="badge bg-secondary-subtle text-secondary">N'est plus membre</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-medium">{{ $classe->cours->titre }}</div>
                                <div class="text-muted small">
                                    {{ $classe->nom }} · {{ $classe->date_debut->format('d/m/Y') }} → {{ $classe->date_fin->format('d/m/Y') }}
                                </div>
                                <span class="badge bg-{{ $classe->etat_couleur }}-subtle text-{{ $classe->etat_couleur }}">{{ $classe->etat_libelle }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary-subtle text-primary fs-12">{{ $stats['nombre'] }}</span>
                            </td>
                            <td>
                                @if ($stats['nombre'] > 0)
                                    {{-- Rouge sous la moyenne (50 %), vert sinon : même code couleur que l'historique du particulier --}}
                                    <span class="fw-semibold {{ $stats['derniere_pct'] < 50 ? 'text-danger' : 'text-success' }}">
                                        {{ $stats['derniere_note'] }}
                                    </span>
                                    <div class="text-muted small">le {{ $stats['derniere_date']->format('d/m/Y') }}</div>
                                @else
                                    <span class="text-muted">Aucun quiz</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $pct($stats['meilleure_pct']) }}</td>
                            <td class="text-center">{{ $pct($stats['moyenne_pct']) }}</td>
                            <td class="text-center">
                                {{-- Évolution = dernier score − premier score (en points de %) ; « — » s'il y a moins de 2 quiz --}}
                                @if ($stats['evolution'] === null)
                                    <span class="text-muted">—</span>
                                @else
                                    <span class="badge {{ $stats['evolution'] >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                        <i class="{{ $stats['evolution'] >= 0 ? 'ri-arrow-up-line' : 'ri-arrow-down-line' }}"></i>
                                        {{ $stats['evolution'] >= 0 ? '+' : '−' }}{{ $pct(abs($stats['evolution'])) }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('entreprise.quiz.show', $inscription) }}" class="btn btn-sm btn-primary">
                                    <i class="ri-line-chart-line me-1"></i> Voir l'évolution
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-5">
                            <i class="ri-questionnaire-line display-5 d-block mb-2"></i>
                            Aucun membre inscrit par votre entreprise ne correspond à ces critères.
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection