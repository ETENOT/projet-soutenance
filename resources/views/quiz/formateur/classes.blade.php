@extends('layouts.master')

@section('title')
    Quiz des stagiaires
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Espace formateur
        @endslot
        @slot('title')
            Quiz des stagiaires
        @endslot
    @endcomponent

    <div class="card">
        <div class="card-header">
            <p class="text-muted mb-0 fs-12">
                Choisissez une classe pour voir les notes et la correction détaillée des quiz de vos stagiaires.
            </p>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Classe</th>
                            <th>Cours</th>
                            <th>Dates</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($classes as $classe)
                            <tr>
                                <td class="fw-medium">{{ $classe->nom }}</td>
                                <td>{{ $classe->cours->titre ?? '—' }}</td>
                                <td>{{ $classe->date_debut->format('d/m/Y') }} → {{ $classe->date_fin->format('d/m/Y') }}</td>
                                <td class="text-end">
                                    {{-- Mène à la liste des résultats, d'où l'on ouvre la correction de chaque quiz --}}
                                    <a href="{{ route('quiz.formateur.index', $classe) }}" class="btn btn-sm btn-soft-primary">
                                        Voir les résultats
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    Aucune classe ne vous est affectée pour l'instant.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection