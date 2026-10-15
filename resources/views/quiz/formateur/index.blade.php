@extends('layouts.master')

@section('title')
    Résultats au quiz — {{ $classe->nom }}
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('classes.formateur.show', $classe) }}">{{ $classe->nom }}</a>
        @endslot
        @slot('title')
            Résultats au quiz
        @endslot
    @endcomponent

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Quiz « {{ $cours->titre }} »</h5>
            <p class="text-muted mb-0 fs-12">
                Quiz terminés par vos stagiaires payés entre le {{ $classe->date_debut->format('d/m/Y') }}
                et le {{ $classe->date_fin->format('d/m/Y') }}.
            </p>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Stagiaire</th>
                            <th>Email</th>
                            <th>Date du quiz</th>
                            <th class="text-center">Note</th>
                            <th class="text-end">Correction</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tentatives as $quiz)
                            @php
                                // Une tentative terminée a exactement un résultat
                                $resultat = $quiz->resultats->first();
                                // Rouge sous la moyenne (50 % du barème), vert sinon : même logique que l'historique côté stagiaire
                                $reussi = $resultat && $quiz->bareme > 0 && ($resultat->score / $quiz->bareme) >= 0.5;
                            @endphp
                            <tr>
                                <td class="fw-medium">{{ $quiz->user->name }}</td>
                                <td>{{ $quiz->user->email }}</td>
                                <td>{{ \Carbon\Carbon::parse($quiz->date)->format('d/m/Y') }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $reussi ? 'success' : 'danger' }}-subtle text-{{ $reussi ? 'success' : 'danger' }}">
                                        {{ rtrim(rtrim(number_format($resultat->score, 2, ',', ''), '0'), ',') }} / {{ $quiz->bareme }}
                                    </span>
                                </td>
                                {{-- Colonne « Correction » : DANS la ligne du quiz, car $resultat n'existe qu'ici --}}
                                <td class="text-end">
                                    <a href="{{ route('quiz.formateur.correction', [$classe, $resultat]) }}"
                                    class="btn btn-sm btn-outline-primary">
                                        Voir la correction
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                {{-- 5 colonnes maintenant (Stagiaire, Email, Date, Note, Correction) --}}
                                <td colspan="5" class="text-center text-muted py-4">
                                    Aucun résultat de quiz pour cette classe pendant sa période.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection